#!/usr/local/bin/python

from Bio import Entrez
import fileinput
    
Entrez.email = "heath.obrien@gmail.com"     # Always tell NCBI who you are
for line in fileinput.input():
  handle = Entrez.efetch(db="nucleotide", id=line, rettype="gb", retmode="text")
  print(handle.read())
