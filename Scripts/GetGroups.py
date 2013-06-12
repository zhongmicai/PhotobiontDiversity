#!/opt/local/bin/python
#assign new sequences to pre-defined Trebouxia clades. Info is added to the 7th column of the metadata file
#this would be more robust if it used all of the sequences that have been assigned to clades to define the clades.
import sys, getopt, string
from ete2 import Tree
import csv

def main(argv):
  groupfile = ''
  metadatafile = ''
  try:
    opts, args = getopt.getopt(argv,"hm:g:",["tree=","metadata="])
  except getopt.GetoptError:
    print 'Type GetClades.py -h for options'
    sys.exit(2)
  for opt, arg in opts:
    if opt == "-h":
       print 'GetClades.py -g <groupfile> -m <metadata_file>'
       sys.exit()
    elif opt in ("-g", "--group"):
       groupfile = arg
    elif opt in ("-m", "--metadata"):
       metadatafile = arg

  groups, representatives = GetGroups(groupfile)

  with open(metadatafile, 'rU') as f:
    reader=csv.reader(f,delimiter='\t')
    for row in reader:
      clade = ""
      if len(row) == 11:
        accession, group, tree_status, host, name, clade, strain, location, author, reference, pmid = row
      elif len(row) == 10:      
        accession, group, tree_status, host, name, strain, location, author, reference, pmid = row
      elif len(row) == 9:
        accession, host, name, clade, strain, location, author, reference, pmid = row
      elif len(row) == 8:
        accession, host, name, strain, location, author, reference, pmid = row
      else: raise ValueError("Metadata file does not have the right number of columns. Format should be 'accession, [group, tree_status,] host, name, [clade,] strain, location, author, reference, pmid'\n")              
      if accession in representatives.keys(): 
        tree_status = "IN TREE"
        if representatives[accession] in groups.values():
          group = "Group " + format(representatives[accession], "03d")
        else: group = "UNIQUE"
      else:
        group = "Group " + format(groups[accession], "03d")
        tree_status = "REDUNDANT"

      print "\t".join([accession, group, tree_status, host, name, clade, strain, location, author, reference, pmid])
   
def GetGroups(file):
  groups = {}
  representatives = {}
  with open(file, 'rU') as f:
    reader=csv.reader(f,delimiter='\t')
    for type, group, length, percent_id, strand, x1, x2, aln, query, hit in reader:
      accession = string.split(string.split(query,"|")[3], ".")[0]
      if type == 'S': representatives[accession] = int(group)
      elif type == 'H': groups[accession] = int(group)
  return groups, representatives
  

if __name__ == "__main__":
   main(sys.argv[1:])


