#!/opt/local/bin/python
from Bio import SeqIO
import string
import sys

file = sys.argv[1]
for record in SeqIO.parse(file, "genbank"):
  info = [record.annotations["references"][0].authors.split(",")[0] ]
  info.append(record.annotations["references"][0].journal)
  info.append(record.annotations["references"][0].pubmed_id)
  info.append(record.name)
  print string.join(info, "\t")
  