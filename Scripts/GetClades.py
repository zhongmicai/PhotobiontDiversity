#!/opt/local/bin/python
#assign new sequences to pre-defined Trebouxia clades. Info is added to the 7th column of the metadata file
#this would be more robust if it used all of the sequences that have been assigned to clades to define the clades.
import sys, getopt
from ete2 import Tree
import csv

def main(argv):
  treefile = ''
  metadatafile = ''
  try:
    opts, args = getopt.getopt(argv,"hm:t:",["tree=","metadata="])
  except getopt.GetoptError:
    print 'Type GetClades.py -h for options'
    sys.exit(2)
  for opt, arg in opts:
    if opt == "-h":
       print 'GetClades.py -t <treefile> -m <metadata_file>'
       sys.exit()
    elif opt in ("-t", "--tree"):
       treefile = arg
    elif opt in ("-m", "--metadata"):
       metadatafile = arg


  clades = GetClade(treefile)
  groups = GetGroups(metadatafile)
  with open(metadatafile, 'rU') as f:
    reader=csv.reader(f,delimiter='\t')
    for accession, group, tree_status, host, name, strain, location, reference, pmid in reader:
      clade = "NA"
      if groups[accession] in clades:
        clade = clades[groups[accession]]
      print "\t".join([accession, group, tree_status, host, name, strain, clade, location, reference, pmid])

def GetClade(tree_file):
  species = { "T. arboricola": ["AJ969540", "AM159216"],
              "T. asymmetrica": ["DQ133497", "DQ133501"],
              "T. corticola": ["AJ293792", "AJ249566", "AB177834"],
              "T. decolorans": ["JQ993768", "FJ705191"],
              "T. gelatinosa": ["AM159211", "AM159214"],
              "T. gigantea": ["JQ993772", "AJ431579"], 
              "T. impressa": ["EU795064", "EU416219"],
              "T. incrustata": ["JQ004552", "DQ166590"],
              "T. jamesii": ["AF242465", "JX144656"],
              "T. showmanii": ["JQ004597", "AF242470"],
              "T. sp. 1": ["DQ166594", "DQ166611"],
              "T. sp. 2": ["EU551522", "EU551502"],
              "T. sp. 3": ["FJ792800", "JQ004598"],
              "T. sp. 4": ["AJ969505", "JX144654"],
              "T. sp. 5": ["AJ431583", "EF095233"] }

               
  tree = Tree(tree_file)
  root = tree.get_midpoint_outgroup()
  tree.set_outgroup(root)
  ancestor = tree.get_common_ancestor(species["T. gelatinosa"])
  clades = {}
  for leaf in ancestor:
    clades[leaf.name] = "T. gelatinosa";
 
  for key in species:
    ancestor = tree.get_common_ancestor(species[key])
    for leaf in ancestor:
      if not leaf.name in clades:
        clades[leaf.name] = key
  return clades  
   
def GetGroups(file):
  groups = {}
  representatives = {}
  with open(file, 'rU') as f:
    reader=csv.reader(f,delimiter='\t')
    for accession, group, tree_status, host, name, strain, location, reference, pmid in reader:
      if group == "UNIQUE" or tree_status == "NOT INCLUDED":
        groups[accession] = accession
      else:
        groups[accession] = group
        if tree_status == "IN TREE":
          representatives[group] = accession
  for key in groups:
    if groups[key] in representatives:
      groups[key] = representatives[groups[key]]
  return groups
  

if __name__ == "__main__":
   main(sys.argv[1:])


