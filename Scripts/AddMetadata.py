#!/Users/HeathOBrien/anaconda/bin/python

#Add algal taxonomy info (species) or host info from metadata file to phylogeny.

#Usage: cat treefile | AddMetadata.pl metadatafile species|host > outfile

"""This is now working to produce nice print-ready PDFs or (almost) web-ready SVGs

The following header needs to be added to the start of the SVG in order to permit
scrolling:

<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"  version="1.2" baseProfile="tiny">
<script xlink:href="SVGPan.js"/>
<title>Generated with ETE http://ete.cgenomics.org</title>
<desc>Generated with ETE http://ete.cgenomics.org</desc>
<defs>
</defs>
<g id="viewport" transform="translate(200,50)">

(this replaces all of the lines up to and including </defs> in the original file)

I also have close the <g at the end of the file ( <\g> )

At some point I need to write a script to make these modifications

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
  searchterm = ''
  usage = 'AddMetadata.py -t <treefile> -l <locus> -o <outfile> -s <search>'
  try:
    opts, args = getopt.getopt(argv,"ht:l:o:s:",["tree=","locus=","out=", "search="])
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
    elif opt in ("-s", "--search"):
       searchterm = arg

  tree = Tree(treefilename)

  try:
     con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity', unix_socket="/tmp/mysql.sock")
  except mdb.Error, e:
    print "Error %d: %s" % (e.args[0],e.args[1])
    sys.exit(1)   
  for leaf in tree:
    with con:
      cur = con.cursor()
      groups = {}
      cur.execute("SELECT `Group`, Host, Species FROM Metadata WHERE Accession LIKE %s AND Gene= %s", (leaf.name + '%', locus,))
      try:
        (group, host, species) = cur.fetchone()
      except TypeError:    
        warnings.warn("No database entry for %s" % leaf.name)
        (group, host, species) = ('','','')
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
    
    if searchterm and leaf.name.find(searchterm) > -1:
      print "adding highlighting to node %s" % leaf.name
      nst = NodeStyle()
      nst["bgcolor"] = "Yellow"
      leaf.set_style(nst)
      label = TextFace(leaf.name.replace("New ", ""))    #This replace statement will soon be a relic and will need to be removed
      label.background.color = "Yellow"
      leaf.add_face(label, column = 0, position="branch-right")
    else:                                                                 #This will include the group names / accession numbers in the tree. This may or may not be useful
      leaf.add_face(TextFace(leaf.name.replace("New ", "")), column = 0)  #This replace statement will soon be a relic and will need to be removed
    add_faces(leaf, label_info, outfilename)
   
  draw_tree(tree, outfilename) 
  if 'svg' in outfilename:
    add_header(outfilename)  

def draw_tree(tree, file):
    root = tree.get_tree_root()
    root.dist = 0
    add_sig(tree)
    ts = TreeStyle()
    ts.branch_vertical_margin = 1
    ts.show_leaf_name = False
    if '.svg' in file:
      ts.scale = 3000
      tree.render(file, tree_style=ts, h=300, units='mm')
    else:
      ts.scale = 1500 
      tree.render(file, tree_style=ts, w=3000, units='mm')
    
    #tree.show()
    
def add_faces(leaf, label_info, outfile):
      colours = get_colours(label_info)
      y = 0
      for x in range(len(label_info)):
        if x < len(label_info) - 1:
          label_info[x] += ','
          if '.svg' in outfile:
            padding = 1 + len(label_info[x]) /5  #this isn't 
            label_info[x] += ' ' * padding
        label = TextFace(label_info[x])
        if '.svg' in outfile:
          label.margin_left = 20
        else:
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
      try:
         con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity', unix_socket="/tmp/mysql.sock")
      except mdb.Error, e:
        print "Error %d: %s" % (e.args[0],e.args[1])
        sys.exit(1)
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
    if node.support > 1: #support values as percentages
      node.support = node.support / 100
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
  
def add_header(outfilename):
  tempfile = open('tempfile, 'w')
  header = """<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"  version="1.2" baseProfile="tiny">
<script xlink:href="SVGPan.js"/>
<title>Generated with ETE http://ete.cgenomics.org</title>
<desc>Generated with ETE http://ete.cgenomics.org</desc>
<defs>
</defs>
<g id="viewport" transform="translate(200,50)">
"""
  tempfile.write(header)
svgfile = open(outfilename, 'r')

line_num = 0
for line in svgfile.readlines():
  line = line.strip()
  line_num += 1
  if line_num > 8:
    if line == '</svg>':
      tempfile.write('</g>\n')
    tempfile.write(line, '\n')
  svgfile.close()
  tempfile.close()
  system('mv tempfile %s' % outfilename)

  
if __name__ == "__main__":
   main(sys.argv[1:])
