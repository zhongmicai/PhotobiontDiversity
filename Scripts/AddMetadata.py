#!/usr/local/bin/python

#Add algal taxonomy info (species) or host info from metadata file to phylogeny.

#Usage: cat treefile | AddMetadata.pl metadatafile species|host > outfile

"""This is also going to require a major rewrite to make it compatible with the new version
of GetGroups.py. Basically, the problem is that I'm not going to bother keeping track of
which sequences are actually in the tree. This means I need to loop through the taxa in
the tree rather than the "IN TREE" entries in the database. This should be straightforward
with ETE

This is working great now, but I'd like to add the tree drawing into this script
because I can use text faces rather than taxon names, which will greatly increase flexibility

That's now working reasonably well, though I don't like how the root is drawn

Next I need to add colours based on taxonomy

I also have to fix the issue where, eg; Pannaria and Pseudocyphellaria are both abbreviated P.
I should also sort the host names to make things clearer
"""

import sys, getopt, string, warnings
import MySQLdb as mdb
from ete2 import Tree, TreeStyle, TextFace, NodeStyle  

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
  for leaf in tree:
    with con:
      cur = con.cursor()
      groups = {}
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
          leaf.name = " " + group + ':'
          label_info = combine_info(cur.fetchall())
      else:  #Singleton
        leaf.name =" " + leaf.name + ':'
        if host and host != 'free-living':
          label_info = [host]
        else:    
          label_info = [species]

    add_faces(leaf, label_info)
   
  draw_tree(tree, outfilename)   

def draw_tree(tree, file):
    root = tree.get_tree_root()
    root.dist = 0
    add_sig(tree)
    ts = TreeStyle()
    ts.branch_vertical_margin = 1
    ts.scale = 500
    tree.render(file, tree_style=ts, w=3000, units='mm')

def add_faces(leaf, label_info):
      colours = get_colours(label_info)
      y = 0
      for x in range(len(label_info)):
        if x < len(label_info) - 1:
          label_info[x] += ','
        label = TextFace(label_info[x])
        label.margin_left = 5
        label.fgcolor = colours[x]
        if x > 1 and x % 3 == 0:
          y += 3
        leaf.add_face(label, column=x-y+1, position="branch-right")
      
def get_colours(label_info):
  colours = []
  for label in label_info:
    genus = label.split(' ')[0]
    if genus.find('.') != -1:
      colours.append(colours[-1])
    else:
      con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity');
      with con:
        cur = con.cursor()
        try:
          cur.execute("SELECT phylum FROM Taxonomy WHERE genus= %s", (genus))
          taxon = cur.fetchone()
        except TypeError:
          warnings.warn("No phylum entry for %s" % genus)
        if taxon and taxon[0] == 'Ascomycota':
          try:
            cur.execute("SELECT family FROM Taxonomy WHERE genus= %s", (genus))
            taxon = cur.fetchone()
          except TypeError:
            warnings.warn("No family entry for %s" % genus)
        try:
          cur.execute("SELECT Colour FROM Colours WHERE Taxon= %s", (taxon[0]))
          colour = cur.fetchone()      
          colours.append(colour[0])
        except TypeError:
          warnings.warn("No colour available for %s (%s)" % (genus, taxon))
          colours.append('LightGray')
          
  return colours
    
def add_sig(tree):
  non_sig = NodeStyle()
  non_sig["size"] = 0
  sig = NodeStyle()
  sig["size"] = 5
  sig["fgcolor"] = "black"
  for node in tree.traverse():
    if node.support < 0.9 or node.is_leaf():
      node.set_style(non_sig)
    else:
      node.set_style(sig)

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
  out_list = []
  included_genera = []
  names = host_counts.keys()
  names.sort()
  for name in names:
      count = host_counts[name]
      genus = name.split(' ')[0]
      if genus in included_genera:
        name = name.replace(genus,  genus[0] + '.')
      else:
        included_genera.append(genus)
      if count > 1:  
        out_list.append("%s (%s)" % ( name, count))
      else:
        out_list.append(name)

  return out_list
  
if __name__ == "__main__":
   main(sys.argv[1:])
