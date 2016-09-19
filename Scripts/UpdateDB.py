#!/usr/bin/env python



import sys, getopt, csv, warnings, re
import mysql.connector 
from mysql.connector import Error

from Bio import SeqIO
from os import path

def main(file_name):
    try:
        conn = mysql.connector.connect(host='localhost',
                                       database='PhotobiontDiversity',
                                       user='root',
                                       buffered=True)
            
        cursor = conn.cursor()

        with open(file_name, 'rU') as f:
            reader=csv.reader(f,delimiter='\t')
            for row in reader:
                try:
                    (SeqID,Host,Species,Strain,Location,Author,Reference,Pubmed,Clade,Gene,Date,Substrate) = row
                except ValueError:
                    warnings.warn("row length %s does not match the expected number of columns (12). Double-check delimiter" % len(row))
                    continue
                if re.match(r'^ANTLS?\d{3}-\d\d$', SeqID) or re.match(r'^G39YD5E04\w{5}_\w{3}$', SeqID):
                    Accession = "http://www.photobiontdiversity/Sequences/%s||%s" % (SeqID, SeqID)
                else:
                    Accession = "http://www.ncbi.nlm.nih.gov/nuccore/%s||%s" % (SeqID, SeqID)
                cursor.execute("SELECT * FROM Metadata WHERE SeqID = %s", (SeqID,))
                db_entries = cursor.fetchall()
                if len(db_entries) == 0:
                    print "adding metadata for %s" % SeqID
                    cursor.execute("INSERT INTO Metadata(SeqID,Host,Species,Strain,Location,Author,Reference,Pubmed,Clade,Gene,Date,Accession,Substrate) VALUES(%s, %s, %s, %s, %s,%s, %s, %s, %s, %s, %s, %s, %s)", (SeqID,Host,Species,Strain,Location,Author,Reference,Pubmed,Clade,Gene,Date,Accession,Substrate))
                else:
                    warnings.warn("Metadata already present in DB for %s" % SeqID)
    except Error as e:
        print(e)
 
    finally:
        conn.commit()
        cursor.close()
        conn.close()

def warning_on_one_line(message, category, filename, lineno, file=None, line=None):
    return ' %s:%s: %s: %s\n' % (filename, lineno, category.__name__, message)
           
if __name__ == "__main__":
    warnings.formatwarning = warning_on_one_line
    file_name = sys.argv[1]
    main(file_name)
