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
      
      #Add group number info
      base = string.split(accession, ".")[0]  #strip off part after decimal indicating multiple samples with same haplotype
      if base in representatives.keys(): group = "Group " + format(representatives[base], "03d")
      elif base in groups.keys(): group = "Group " + format(groups[base], "03d")
      else: group = 'NA'
      
      tree_status = "REDUNDANT"
      if base in representatives.keys():
        if accession == base:
          tree_status = "IN TREE"  
          if representatives[accession] not in groups.values():
            group = "UNIQUE"
        elif accession == base + ".000":
          tree_status = "IN TREE"  
      print "\t".join([accession, group, tree_status, host, name, clade, strain, location, author, reference, pmid])
   
def GetGroups(file):
  groups = {}             #only groups with multiple seqs
  representatives = {}    #only seqs that are included in nr set
  with open(file, 'rU') as f:
    reader=csv.reader(f,delimiter='\t')
    for type, group, length, percent_id, strand, x1, x2, aln, query, hit in reader:
      if "|" in query:
        query = string.split(query,"|")[3]
      if "." in query:
        accession = string.split(query,".")[0]
      if type == 'S': representatives[accession] = int(group)
      elif type == 'H': groups[accession] = int(group)
  return groups, representatives
  

if __name__ == "__main__":
   main(sys.argv[1:])


