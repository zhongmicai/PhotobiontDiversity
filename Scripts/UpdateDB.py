#!/usr/bin/env python



import sys, getopt, csv, warnings, re
import mysql.connector 
from mysql.connector import Error

from Bio import SeqIO
from os import path

  
con = mysql.connector.connect(host='localhost',
                                       database='PhotobiontDiversity',
                                       user='root')
            
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
      if re.match(r'^ANTLS?\d{3}-\d\d$', SeqID) or re.match(r'^G39YD5E04\w{5}_\w{3}$', SeqID):
        Accession = "http://www.photobiontdiversity/Sequences/%s||%s" % (SeqID, SeqID)
      else:
        Accession = "http://www.ncbi.nlm.nih.gov/nuccore/%s||%s" % (SeqID, SeqID)
      cur.execute("SELECT * FROM Metadata WHERE SeqID = %s", (SeqID))
      db_entries = cur.fetchall()
      if len(db_entries) == 0:
        cur.execute("INSERT INTO Metadata(SeqID,Host,Species,Strain,Location,Author,Reference,Pubmed,Clade,Gene,Date,Accession) VALUES(%s, %s, %s, %s, %s,%s, %s, %s, %s, %s, %s, %s)", (SeqID,Host,Species,Strain,Location,Author,Reference,Pubmed,Clade,Gene,Date,Accession))
      else:
        warnings.warn("Metadata already present in DB for %s" % SeqID)
