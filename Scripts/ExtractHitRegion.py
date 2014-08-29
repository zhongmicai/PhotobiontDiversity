#!/usr/local/bin/python



import sys, csv
from Bio import SeqIO
from Bio.SeqRecord import SeqRecord

def get_fasta_accession(name):
    """"Given a SeqRecord, return the accession number as a string.
  
    e.g. "gi|2765613|gb|Z78488.1|PTZ78488" -> "Z78488.1"
    """
    parts = name.split("|")
    try:
      assert len(parts) == 5 and parts[0] == "gi" and ( parts[2] == "gb" or parts[2] == "emb" )
    except AssertionError:
      sys.exit(parts)
    return parts[3].split('.')[0]



seq_records = {}
for seq_record in SeqIO.parse(sys.argv[1], 'fasta'):
  seqs = []
  for description in seq_record.description.split( '>'):
    name = description.split(' ')[0]
    seqs.append(SeqRecord(seq_record.seq, name = name, description = description))
  seq_records[get_fasta_accession(seq_record.name)] = seqs
     

with open(sys.argv[2], 'rU') as f:
    reader=csv.reader(f,delimiter='\t')
    for row in reader:
      try:
        (qseqid, qlen, sacc, slen, pident, length, mismatch, gapopen, qstart, qend, qframe, sstart, send, sframe, evalue, bitscore) = row
      except ValueError:
        warnings.warn("row length %s does not match the expected number of columns (16). Double-check delimiter" % len(row))
        continue
      sstart = int(sstart) -1
      send = int(send)
      for seq_record in seq_records[sacc]:
        if sstart < send:
          seq = seq_record.seq[sstart: send]
        else:
          seq = seq_record.seq[send - 1, sstart].reverse_complement()
        print '>', seq_record.description
        print seq
