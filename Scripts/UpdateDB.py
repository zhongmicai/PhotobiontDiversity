#!/usr/local/bin/python



import sys, getopt, csv, warnings, re
import MySQLdb as mdb
from Bio import SeqIO
from os import path

  
con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity', unix_socket="/tmp/mysql.sock");
with con:
  cur = con.cursor()

  with open(sys.argv[1], 'rU') as f:
    reader=csv.reader(f,delimiter='\t')
    for row in reader:
      try:
        (SeqID,Host,Species,Strain,Location,Author,Reference,Pubmed,Clade,Gene,Date) = row
      except ValueError:
        warnings.warn("row length %s does not match the expected number of columns (11). Double-check delimiter" % len(row))
        continue
      Accession = "http://www.ncbi.nlm.nih.gov/nuccore/%s||%s" % (SeqID, SeqID)
      cur.execute("SELECT * FROM Metadata WHERE SeqID = %s", (SeqID))
      db_entries = cur.fetchall()
      if len(db_entries) == 0:
        cur.execute("INSERT INTO Metadata(SeqID,Host,Species,Strain,Location,Author,Reference,Pubmed,Clade,Gene,Date,Accession) VALUES(%s, %s, %s, %s, %s,%s, %s, %s, %s, %s, %s, %s)", (SeqID,Host,Species,Strain,Location,Author,Reference,Pubmed,Clade,Gene,Date,Accession))
      else:
        warnings.warn("Metadata already present in DB for %s" % SeqID)
