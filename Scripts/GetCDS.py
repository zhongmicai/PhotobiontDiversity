#!/usr/local/bin/python
#assign new sequences to pre-defined Trebouxia clades. Info is added to the 7th column of the metadata file
#this would be more robust if it used all of the sequences that have been assigned to clades to define the clades.
import sys, getopt, string
from Bio import SeqIO

def main(argv):
  infilename = ''
  outfilename = ''
  gene = ''
  try:
    opts, args = getopt.getopt(argv,"hg:i:o:",["gene=","infile=", "outfile="])
  except getopt.GetoptError:
    print 'Type GetCDS.py -h for options'
    sys.exit(2)
  for opt, arg in opts:
    if opt == "-h":
       print 'GetCDS.py -g <gene_name> -i <infile> -o <outfile>'
       sys.exit()
    elif opt in ("-g", "--gene"):
       gene = arg
    elif opt in ("-i", "--infile"):
       infilename = arg
    elif opt in ("-o", "--outfile"):
       outfilename = arg

  if outfilename == '':
    outfilename = infilename.split('.')[-2] + '_' + gene + '.fa'
  
  seq_list = []
  for seq in SeqIO.parse(infilename, "genbank"):
    for feature in seq.features:
      try:
        if feature.type == 'CDS' and feature.qualifiers['gene'] and gene in feature.qualifiers['gene']:
          gene_seq = feature.extract(seq)
          gene_seq.id = seq.id
          gene_seq.description = seq.description
      except KeyError:
        pass
    seq_list.append(gene_seq)  
  SeqIO.write(seq_list, outfilename, "fasta")  

if __name__ == "__main__":
   main(sys.argv[1:])


