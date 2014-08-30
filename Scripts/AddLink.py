#!/Users/HeathOBrien/anaconda/bin/python

import sys, getopt, string, warnings
import MySQLdb as mdb
from Bio import SeqIO


def main(seq_file):
  try:
     con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity', unix_socket="/tmp/mysql.sock")
  except mdb.Error, e:
    print "Error %d: %s" % (e.args[0],e.args[1])
    sys.exit(1)   
  cur = con.cursor()
  cur.execute("SELECT SeqID FROM Metadata")
  for result in cur.fetchall():
      seqID = result[0]
      Accession = "http://www.ncbi.nlm.nih.gov/nuccore/%s||%s" % (seqID, seqID)
      print "UPDATE Metadata SET Accession = %s WHERE SeqID = %s" % (Accession, seqID)
      cur.execute("UPDATE Metadata SET Accession = %s", (Accession))
      
if __name__ == "__main__":
   main(sys.argv)
   
   
