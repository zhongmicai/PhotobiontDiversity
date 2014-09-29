#!/Users/HeathOBrien/anaconda/bin/python

#count the number of host genus / photobiont clade associations and print matrix

import sys, getopt
import csv
import MySQLdb as mdb

def main(argv):
  metadatafile = ''
  try:
    opts, args = getopt.getopt(argv,"hm:",["tree=","metadata="])
  except getopt.GetoptError:
    print 'Type CountAssociations.py -h for options'
    sys.exit(2)
  for opt, arg in opts:
    if opt == "-h":
       print 'CountAssociations.py -m <metadata_file>'
       sys.exit()
    elif opt in ("-m", "--metadata"):
       metadatafile = arg
  clade_list =  ["T. arboricola", "T. asymmetrica", "T. corticola", "T. decolorans", "T. gelatinosa",
           "T. gigantea", "T. impressa", "T. incrustata", "T. jamesii", "T. showmanii",
           "T. sp. 1", "T. sp. 2", "T. sp. 3", "T. sp. 4", "T. sp. 5" ]
  print ', '.join(['Genus', 'Family'] + clade_list)
  try:
     con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity', unix_socket="/tmp/mysql.sock")
  except mdb.Error, e:
    print "Error %d: %s" % (e.args[0],e.args[1])
    sys.exit(1)   
  with con:
    cur = con.cursor()
    cur.execute("SELECT Genus, family FROM Taxonomy ORDER BY Genus")
    for (genus, family) in cur.fetchall():
      #print "SELECT Clade, COUNT(Clade) FROM Metadata Where Host LIKE '%s' AND Species LIKE '%s' GROUP BY Clade" % (genus + '%', 'Trebouxia%')
      cur.execute("SELECT Clade, COUNT(Clade) FROM Metadata Where Host LIKE %s AND Species LIKE %s GROUP BY Clade", (genus + '%', 'Trebouxia%'))
      associations = {}
      for (clade, count) in cur.fetchall():
        if clade:
          associations[clade] = count
      if len(associations) > 0:
        output = [genus, family]
        for column in clade_list:
          if column in associations:
            output.append(associations[column])
          else:
            output.append(0)
        print ", ".join(map(str, output))         

if __name__ == "__main__":
   main(sys.argv[1:])


