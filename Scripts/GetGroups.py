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

  groups = GetGroups(groupfile) #return dictionary with group name as key and array of seqIDs as values

  con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity', unix_socket="/tmp/mysql.sock");
  with con:
    cur = con.cursor()
    #remove saved group info from db
    cur.execute("UPDATE Metadata SET `Group` = NULL WHERE Gene= %s", (gene,))     
    for group in groups.keys():
        cur.execute("UPDATE Metadata SET `Group` = 'UNIQUE' WHERE SeqID LIKE %s AND Gene= %s", (groups[group][0]+'%', gene,))

         
        
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
  
    
def warning_on_one_line(message, category, filename, lineno, file=None, line=None):
    import warnings
    return ' %s:%s: %s: %s\n' % (filename, lineno, category.__name__, message)

if __name__ == "__main__":
   main(sys.argv[1:])


