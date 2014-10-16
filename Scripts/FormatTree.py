#!/Users/HeathOBrien/anaconda/bin/python

#Add algal taxonomy info (species) or host info from metadata file to phylogeny.

#Usage: cat treefile | AddMetadata.pl metadatafile species|host > outfile

import sys, getopt, string, warnings
import MySQLdb as mdb
from ete2 import Tree, TreeStyle, TextFace, NodeStyle  
from os import system

def main(argv):
  treefilename = ''
  locus = ''
  outfilename = ''
  searchterm = ''
  date = ''
  debug = 0
  field = 'Host'
  usage = 'FormatTree.py -t <treefile> -l <locus> -o <outfile> -s <search> -d <date> -f <field> -c (debug clades)'
  try:
    opts, args = getopt.getopt(argv,"ht:l:o:s:d:f:c:",["tree=","locus=","out=", "search=", "date=", "field=", "clades="])
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
    elif opt in ("-d", "--date"):
       date = arg
    elif opt in ("-f", "--field"):
       field = arg
    elif opt in ("-c", "--clades"):
       debug = 1

  tree = Tree(treefilename)
  add_sig(tree)
  #tree = root_tree(tree)
  root_tree(tree, treefilename)
  try:
     con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity', unix_socket="/tmp/mysql.sock")
  except mdb.Error, e:
    print "Error %d: %s" % (e.args[0],e.args[1])
    sys.exit(1)   
  with con:
    cur = con.cursor()
    #tree = colour_clades(cur, tree, locus)
    colour_clades(cur, tree, locus, debug)
    groups = {}
    for leaf in tree:
      accession = leaf.name
      cur.execute("SELECT `Group`, Host, Species, Clade FROM Metadata WHERE SeqID LIKE %s AND Gene= %s", (accession + '%', locus,))
      try:
        (group, host, species, clade) = cur.fetchone()
      except TypeError:    
        warnings.warn("No database entry for %s" % leaf.name)
        (group, host, species) = ('','','')
      if group and group.find('Group') != -1:  #Group rep
        if group in groups:
          warnings.warn("%s and %s are both in the tree and both in %s" % (accession, groups[group], group))
        else:
          groups[group] = leaf.name
          cur.execute("SELECT Host, Species, Clade FROM Metadata WHERE `Group`= %s AND Gene= %s", (group, locus,))
          group_members = cur.fetchall()
          #leaf.name = " " + group + ':'
          label_info = [group] + combine_info(field, cur.fetchall())
      else:  #Singleton
        group_members = [[host, species, clade]]
      if len(group_members) == 1:
        #leaf.name =" " + accession + ':'
        if field == 'Host' and host and host != 'free-living':
          label_info = [accession, host]
        else:    
          label_info = [accession, species]
      else:
        label_info = [group] + combine_info(field, group_members)
      bg_colour = None
      #label = TextFace(leaf.name)
      if searchterm and ' '.join(label_info).find(searchterm) > -1:
        print "adding highlighting to node %s" % leaf.name
        #label.background.color = "Yellow"
        bg_colour = "DeepPink"
      elif date:
        if group == 'UNIQUE':
          cur.execute("SELECT SeqID FROM Metadata WHERE SeqID LIKE %s AND Gene= %s AND Date = %s", (accession, locus, date))
        else:
          cur.execute("SELECT SeqID FROM Metadata WHERE `Group`= %s AND Gene= %s AND Date = %s", (group, locus, date))
        if len(cur.fetchall()) > 0:
          print "adding highlighting to node %s" % leaf.name
          label.background.color = "Yellow"
          #bg_colour = "Yellow"
      #leaf.add_face(label, column = 0)                        #This will include the group names / accession numbers in the tree. This may or may not be useful
      add_faces(cur, field, leaf, label_info, bg_colour, outfilename)
  
  draw_tree(tree, outfilename) 
  if 'svg' in outfilename:
    add_header(outfilename, locus)  

def colour_clades(cur, tree, locus, debug):
  clades = {}
  colours = {}
  cur.execute("SELECT Colour, Taxon FROM Colours")
  for (colour, taxon) in cur.fetchall():
    colours[taxon] = colour
  
  for leaf in tree:
    accession = leaf.name
    cur.execute("SELECT Clade FROM Metadata WHERE SeqID LIKE %s AND Gene= %s", (accession + '%', locus,))
    try:
        clade = cur.fetchone()[0]
    except TypeError:    
        warnings.warn("No database entry for %s" % accession)
        continue
    if debug:
     try:
      label = TextFace(leaf.name)
      label.background.color = colours[clade.replace('T.', 'Trebouxia')]
      leaf.add_face(label, column = 0)                        
     except KeyError:
      pass
       
    if clade:
      if clade in clades:
        clades[clade].append(leaf) 
      else:
        clades[clade] = [leaf]
        
  for clade in clades:
    if 'URa2' in clade:
      continue
    cur.execute("SELECT Colour from Colours WHERE Taxon = %s", clade.replace('T.', 'Trebouxia'))
    try:
        colour = cur.fetchone()[0]
    except TypeError:    
        warnings.warn("No database entry for %s" % clade.replace('T.', 'Trebouxia'))
        continue
    print "setting clade %s to %s" % (clade, colour)

    #tree = colour_clade(tree, clades[clade], colour) 
    colour_clade(tree, clades[clade], colour) 
    label_clade(tree, clades[clade], colour, clade) 

  #return tree

def label_clade(tree, leaves, colour, clade):
  if len(leaves) == 1:
    ancestor = leaves[0]   
  else:
    ancestor = tree.get_common_ancestor(leaves)  
  label = TextFace(clade, fsize=100, fgcolor = colour)
  ancestor.add_face(label, column=1, position = "float")


def colour_clade(tree, leaves, colour):
  sig = NodeStyle()
  sig["vt_line_color"] = colour
  sig["hz_line_color"] = colour
  sig["vt_line_width"] = 6
  sig["hz_line_width"] = 6
  sig["fgcolor"] = colour
  sig["size"] = 10
  nonsig = NodeStyle()
  nonsig["vt_line_color"] = colour
  nonsig["hz_line_color"] = colour
  nonsig["vt_line_width"] = 6
  nonsig["hz_line_width"] = 6
  nonsig["fgcolor"] = colour
  nonsig["size"] = 0
  if len(leaves) == 1:
    leaves[0].set_style(nonsig)    
  else:
    ancestor = tree.get_common_ancestor(leaves)  
    if ancestor.img_style['size'] == 0:
      ancestor.set_style(nonsig)
    else:
      ancestor.set_style(sig)
    for node in ancestor.iter_descendants("postorder"):
      if node.img_style['size'] == 0:
        node.set_style(nonsig)
      else:
        node.set_style(sig)
    
  #return tree


def root_tree(tree, treefilename):
  root = tree.get_midpoint_outgroup()
  try:
      tree.set_outgroup(root)
  except:
      pass
  if 'Trebouxia_ITS' in treefilename:
    leaves = []
    for taxon in ('AY842266','AJ249567'):
      for leaf in  tree.get_leaves_by_name(taxon):
        leaves.append(leaf)
    outgroup = tree.get_common_ancestor(leaves)
    tree.set_outgroup(outgroup)
  root = tree.get_tree_root()
  root.dist = 0
    #return tree
    
def draw_tree(tree, file):
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
    
def add_faces(cur, field, leaf, label_info, bg_colour, outfile):
      colours = get_colours(cur, field, label_info)
      y = 0
      for x in range(len(label_info)):
        if x == 0:
          label_info[x] += ':'
        elif x < len(label_info) - 1:
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
        label.background.color = bg_colour
        if x > 1 and x % 3 == 0:
          y += 3
        leaf.add_face(label, column=x-y+1, position="branch-right")
      
def get_colours(cur, field, label_info):
  colours = ['black',]
  for label in label_info[1:]:
    genus = label.split(' ')[0]
    taxon = ''
    if genus.find('.') != -1:
      colours.append(colours[-1])
    else:
      if field == 'Host':
        try:
          cur.execute("SELECT phylum FROM Taxonomy WHERE genus= %s", (genus))
          taxon = cur.fetchone()[0]
        except TypeError:
          warnings.warn("No phylum entry for %s" % genus)
        if taxon and taxon == 'Ascomycota':
            try:
              cur.execute("SELECT family FROM Taxonomy WHERE genus= %s", (genus))
              taxon = cur.fetchone()[0]
            except TypeError:
              warnings.warn("No family entry for %s" % genus)
      else:
        taxon = ' '.join(label.split(' ')[:2])
      try:
        if 'letharii' in label:
          cur.execute("SELECT Colour FROM Colours WHERE Taxon= %s", ('Trebouxia letharii'))
        else:
          cur.execute("SELECT Colour FROM Colours WHERE Taxon= %s", (taxon))
        colour = cur.fetchone()      
        colours.append(colour[0])
      except TypeError:
        warnings.warn("No colour available for %s (%s)" % (genus, taxon))
        colours.append('LightGray')
          
  return colours
    
def add_sig(tree):
  non_sig = NodeStyle()
  non_sig["size"] = 0
  non_sig["vt_line_width"] = 6
  non_sig["hz_line_width"] = 6
  sig = NodeStyle()
  sig["size"] = 10
  sig["fgcolor"] = "black"
  sig["vt_line_width"] = 6
  sig["hz_line_width"] = 6
  for node in tree.traverse():
    if node.support < 0.9 or node.is_leaf() or node.is_root():
      node.set_style(non_sig)
    else:
      node.set_style(sig)
  
def combine_info(field, entries):
  host_counts = {}                   #Can include species names of free-living strains
  for (host, species, clade) in entries:
    if host == "free-living" or field != 'Host':
      info = species
    elif host == ' ':
      info = 'Unknown'
    else:
      info = host
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
  
def add_header(outfilename, locus):
  tempfile = open('tempfile', 'w')
  header = """<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"  version="1.2" baseProfile="tiny">
<script xlink:href="../SVGPan.js"/>
<title>%s Phylogeny</title>
<desc>Generated with ETE http://ete.cgenomics.org</desc>
<defs>
</defs>
<g id="viewport" transform="translate(200,50)">
""" % locus
  tempfile.write(header)
  svgfile = open(outfilename, 'r')

  node_text = 0 #set when one of the following lines has a internode label (need to change position)
  line_num = 0
  for line in svgfile.readlines():
    line = line.strip()
    line_num += 1
    if 'font-size="100pt"' in line:
      node_text = 1
    elif line == '</g>':
      node_text = 0
    if node_text:
      line = line.replace('x="0"', 'x="1200"')
    if line_num > 8:
      if line == '</svg>':
        tempfile.write('</g>\n')
      tempfile.write(line + '\n')
  svgfile.close()
  tempfile.close()
  system('mv tempfile %s' % outfilename)

  
if __name__ == "__main__":
   main(sys.argv[1:])
