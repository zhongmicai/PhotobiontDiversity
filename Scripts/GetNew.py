#!/Users/HeathOBrien/anaconda/bin/python

import sys, getopt, string, warnings
import MySQLdb as mdb
from Bio import SeqIO

def get_fasta_accession(record):
    """"Given a SeqRecord, return the accession number as a string.
  
    e.g. "gi|2765613|gb|Z78488.1|PTZ78488" -> "Z78488.1"
    """
    parts = record.id.split("|")
    try:
      assert len(parts) == 5 and parts[0] == "gi" and ( parts[2] == "gb" or parts[2] == "emb" )
    except AssertionError:
      sys.exit(parts)
    return parts[3].split('.')[0]

def main(seq_file):
  try:
     con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity', unix_socket="/tmp/mysql.sock")
  except mdb.Error, e:
    print "Error %d: %s" % (e.args[0],e.args[1])
    sys.exit(1)   
  cur = con.cursor()
  for seq_record in SeqIO.parse(seq_file, 'fasta'):
    name = get_fasta_accession(seq_record)
    seq_record.id = name
    seq_record.description = ''
    cur.execute("SELECT Gene FROM Metadata WHERE SeqID = %s", (name))
    db_info = cur.fetchall()
    if len(db_info) > 1:
      warnings.warn("multiple entries in DB for %s" % name)
    elif len(db_info) == 0:
      SeqIO.write(seq_record, sys.stdout, "fasta")
      
if __name__ == "__main__":
   main(sys.argv[1])
   
   
