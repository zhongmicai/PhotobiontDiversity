#!/usr/local/bin/python
#count the number of host genus / photobiont clade associations and print matrix

import sys, getopt
import csv

def main(argv):
  metadatafile = ''
  try:
    opts, args = getopt.getopt(argv,"hm:",["tree=","metadata="])
  except getopt.GetoptError:
    print 'Type CountAssociations.py -h for options'
    sys.exit(2)
  for opt, arg in opts:
    if opt == "-h":
       print 'CountAssociations.py -m <metadata_file>'
       sys.exit()
    elif opt in ("-m", "--metadata"):
       metadatafile = arg


  associations = {}
  with open(metadatafile, 'rU') as f:
    reader=csv.reader(f,delimiter='\t')
    for accession, group, tree_status, host, name, strain, clade, location, reference, pmid in reader:
      if host:
        genus = host.split()[0]
        if genus in associations:
          if clade in associations[genus]:
            associations[genus][clade] += 1
          else:
            associations[genus][clade] = 1
        else:
          associations[genus] = {}
          associations[genus][clade] = 1
    for genus in associations:
      print genus, "\t",
      #print associations[genus]
      for clade in ["T. arboricola", "T. asymmetrica", "T. corticola", "T. decolorans", "T. gelatinosa",
           "T. gigantea", "T. impressa", "T. incrustata", "T. jamesii", "T. showmanii",
           "T. sp. 1", "T. sp. 2", "T. sp. 3", "T. sp. 4", "T. sp. 5" ]:
        #print clade, 
        if clade in associations[genus]:
          print associations[genus][clade],
        if clade == "T. sp. 5":
          print
        else:
          print "\t",

if __name__ == "__main__":
   main(sys.argv[1:])


