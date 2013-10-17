#!/usr/local/bin/python

#Add algal taxonomy info (species) or host info from metadata file to phylogeny.

#Usage: cat treefile | AddMetadata.pl metadatafile species|host > outfile

"""This is also going to require a major rewrite to make it compatible with the new version
of GetGroups.py. Basically, the problem is that I'm not going to bother keeping track of
which sequences are actually in the tree. This means I need to loop through the taxa in
the tree rather than the "IN TREE" entries in the database. This should be straightforward
with ETE

Not least of which is that I'm going to switch from perl to python

"""

import sys, getopt, string, warnings
import MySQLdb as mdb
from ete2 import Tree  

def main(argv):
  treefilename = ''
  locus = ''
  outfilename = ''
  usage = 'AddMetadata.py -t <treefile> -l <locus> -o <outfile>'
  try:
    opts, args = getopt.getopt(argv,"ht:l:o:",["tree=","locus=","out="])
    if not opts:
      raise getopt.GetoptError('no opts')
  except getopt.GetoptError:
    print usage
    sys.exit(2)
  for opt, arg in opts:
    if opt == "-h":
       print usage
       sys.exit()
    elif opt in ("-t", "--tree"):
       treefilename = arg
    elif opt in ("-l", "--locus"):
       locus = arg
    elif opt in ("-o", "--out"):
       outfilename = arg

  tree = Tree(treefilename)

  con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity');
  with con:
    cur = con.cursor()
    groups = {}
    for leaf in tree:
      cur.execute("SELECT `Group`, Host, Species FROM Metadata WHERE Accession= %s AND Gene= %s", (leaf.name, locus,))
      try:
        (group, host, species) = cur.fetchone()
      except TypeError:
        warnings.warn("No database entry for %s" % leaf.name)
        
      if group and group.find('Group') != -1:  #Group rep
        if group in groups.keys():
          warnings.warn("%s and %s are both in the tree and both in %s" % (leaf.name, groups[group], group))
        else:
          groups[group] = leaf.name
          cur.execute("SELECT Host, Species FROM Metadata WHERE `Group`= %s AND Gene= %s", (group, locus,))
          leaf.name = ' '.join([group, combine_info(cur.fetchall())])
      else:  #Singleton
        if host and host != 'free-living':
          leaf.name = ' '.join([leaf.name, host])
        else:    
          leaf.name = ' '.join([leaf.name, species])

    tree.write(format=0, outfile=outfilename)
      
def combine_info(entries):
  host_counts = {}                   #Can include species names of free-living strains
  for (host, species) in entries:
    if host and host != "free-living":
      info = host
    else:
      info = species
    if info in host_counts.keys():
      host_counts[info] += 1
    else:
      host_counts[info] = 1
  out_string = ''
  included_genera = []
  for name in host_counts.keys():
      count = host_counts[name]
      genus = name.split(' ')[0]
      if genus in included_genera:
        name = name.replace(genus,  genus[0] + '.')
      else:
        included_genera.append(genus)
      if out_string:
        out_string += ", %s (%s)" % ( name, count)
      else:
         out_string = "%s (%s)" % ( name, count)
  return out_string
  
if __name__ == "__main__":
   main(sys.argv[1:])
