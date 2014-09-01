#!/usr/local/bin/python

"""Replaces 'Group' info in the database for the given gene with info from the specified
group file (output from usearch).

There are a bunch of complex functions included here that are no longer necessary, but
I don't have the heart to remove them because they aren't backed up anywhere

"""

import sys, getopt, string
import csv
import MySQLdb as mdb

def main(argv):
  groupfile = ''
  gene = ''
  usage = 'GetGroups.py -g groupfile> -l <locus>'
  try:
    opts, args = getopt.getopt(argv,"hl:g:",["group=","locus="])
  except getopt.GetoptError:
    print usage
    sys.exit(2)
  for opt, arg in opts:
    if opt == "-h":
       print usage
       sys.exit()
    elif opt in ("-g", "--group"):
       groupfile = arg
    elif opt in ("-l", "--locus"):
       gene = arg

  groups = GetGroups(groupfile)

  con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity', unix_socket="/tmp/mysql.sock");
  with con:
    cur = con.cursor()
    #remove saved group info from db
    cur.execute("UPDATE Metadata SET `Group` = NULL WHERE Gene= %s", (gene,))     
    for group in groups.keys():
      if len(groups[group]) == 1:
        cur.execute("UPDATE Metadata SET `Group` = 'UNIQUE' WHERE SeqID LIKE %s AND Gene= %s", (groups[group][0]+'%', gene,))
      else:        
        for accession in groups[group]:
          cur.execute("UPDATE Metadata SET `Group` = %s WHERE SeqID LIKE %s AND Gene= %s", (group, accession+'%', gene,))

         
def update_saved(group_list, gene):
  con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity');
  with con:
    cur = con.cursor()
    for group in group_list[1:]:
      print "setting %s to %s:" % (group, group_list[0])
      cur.execute("SELECT SeqID FROM Metadata WHERE `Group`= %s AND Gene= %s", (group,gene,))
      for row in cur.fetchall():
        cur.execute("UPDATE Metadata SET `Group` = %s WHERE SeqID= %s AND Gene= %s", (group_list[0], row[0], gene,))

  
def unique_group(gene):
  con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity');
  with con:
    cur = con.cursor()
    for group_num in range(10000):
      group = "Group " + format(group_num, "03d")
      cur.execute("SELECT * FROM Metadata WHERE `Group`= %s AND Gene= %s", (group,gene,))
      if not cur.fetchone():
        break
  return group
        
def GetGroups(file):
  """Parses usearch output and assignes each non-singleton sequence to a group.
  """
  groups = {}             #only groups with multiple seqs
  with open(file, 'rU') as f:
    reader=csv.reader(f,delimiter='\t')
    for type, group, length, percent_id, strand, x1, x2, aln, query, hit in reader:
      if "|" in query:
        query = string.split(query,"|")[3]
      if "." in query:
        accession = string.split(query,".")[0]
      else:
        accession = query
      if type == 'S' or type == 'H':
        group_name = "Group " + format(int(group), "03d")
        if group_name in groups.keys():
          groups[group_name] += [accession]
        else:
          groups[group_name] = [accession]
  
  return groups
  
def retrieve_groups(group, gene):
  """Looks up each sequence from each group in database and if it is assigned to a group 
  in the db, all sequences assigned to the same group are given that group number"""
  
  saved_groups = []
  con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity');
  with con:
    cur = con.cursor()
    for accession in group:   #Assigned saved group numbers from db to groups with representatives in db
      cur.execute("SELECT `Group` FROM Metadata WHERE SeqID= %s AND Gene= %s", (accession, gene,))
      db_group = cur.fetchone()[0]
      if db_group and db_group != 'UNIQUE':            #accession is already assigned to a group in the database
        if db_group not in saved_groups:
            saved_groups += [db_group]
          
  return saved_groups
    
def warning_on_one_line(message, category, filename, lineno, file=None, line=None):
    import warnings
    return ' %s:%s: %s: %s\n' % (filename, lineno, category.__name__, message)

if __name__ == "__main__":
   main(sys.argv[1:])


