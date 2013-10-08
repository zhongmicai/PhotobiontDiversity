#!/usr/local/bin/python
"""
Extract the CDS sequence corresponding to the specified (gene) feature. Input sequences
must be in Genbank format. ID and description of the larger sequence are copied to the subsequence

For protein-coding markers, this will take care of all necessary trimming and reverse-complementing

If the specified gene is not present, a warning is printed

If an outfile is not specified, a default file is used (by replacing the file extension
with "_<gene>.fa"
"""
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
    try:    
      seq_list.append(gene_seq)
    except UnboundLocalError:
      print "no gene called %s found for %s!" % ( gene, seq.id )
  SeqIO.write(seq_list, outfilename, "fasta")  

if __name__ == "__main__":
   main(sys.argv[1:])


