#!/usr/local/bin/python



import sys, getopt, csv, warnings, re
import MySQLdb as mdb
from Bio import SeqIO
from os import path

  
con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity');
with con:
  cur = con.cursor()

  with open(sys.argv[1], 'rU') as f:
    reader=csv.reader(f,delimiter='\t')
    for row in reader:
      try:
        (Accession,Host,Species,Strain,Location,Author,Reference,Pubmed,Gene,Date) = row
      except ValueError:
        warnings.warn("row length %s does not match the expected number of columns (10). Double-check delimiter" % len(row))
        continue
      cur.execute("SELECT * FROM Metadata WHERE Accession = %s", (Accession))
      db_entries = cur.fetchall()
      if len(db_entries) == 0:
        cur.execute("INSERT INTO Metadata(Accession,Host,Species,Strain,Location,Author,Reference,Pubmed,Gene,Date) VALUES(%s, %s, %s, %s, %s,%s, %s, %s, %s, %s)", (Accession,Host,Species,Strain,Location,Author,Reference,Pubmed,Gene,Date))
      else:
        warnings.warn("Metadata already present in DB for %s" % Accession)
