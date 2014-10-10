#!/usr/local/bin/python

"""
Filter counts by selected min and max
"""


import sys, getopt, csv, warnings
from os import path
import MySQLdb as mdb


def main(argv):
  usage = 'FilterCounts.py --min --max --in inputfile >outfile'
  minimum = 0
  maximum = sys.maxint
  infilename = ''
  rownames = 0
  try:
     opts, args = getopt.getopt(argv,"hi:m:x:r",["in=", "min=", "max=", "rownames="])
  except getopt.GetoptError:
     print usage
     sys.exit(2)
  for opt, arg in opts:
     if opt == '-h':
        print usage
        sys.exit()
     elif opt in ("-i", "--in"):
        infilename = arg
  try:
     con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity', unix_socket="/tmp/mysql.sock")
  except mdb.Error, e:
    print "Error %d: %s" % (e.args[0],e.args[1])
    sys.exit(1)   
  with con:
    cur = con.cursor()

    try:
     f = open(infilename, 'rU')
    except IOError:
      f = sys.stdin
    reader=csv.reader(f,delimiter='\t')
    for row in reader:
      cur.execute("SELECT Clade from Metadata WHERE SeqID = %s", row[1])
      try:
        clade = cur.fetchone()[0]
      except:
        clade = 'NA'
      print '\t'.join(row + [clade])  
          
if __name__ == "__main__":
   main(sys.argv[1:])


