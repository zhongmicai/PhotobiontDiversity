#!/usr/bin/env python

#Add algal taxonomy info (species) or host info from metadata file to phylogeny.

import sys, getopt, string, warnings
import mysql.connector 
from mysql.connector import Error
from ete2 import Tree, TreeStyle, TextFace, NodeStyle  
from os import system

def main(argv):
  treefilename = ''
  locus = ''
  outfilename = ''
  searchterm = ''
  date = ''
  debug = 0
  global verbose
  verbose = 0
  field = 'Host'
  bootstrap = 0.9
  outgroup = ''
  usage = 'FormatTree.py -t <treefile> -l <locus> -o <outfile> -s <search> -d <date> -f <field> -g outgroup -b <bootstrap cutoff> -c (debug clades) -v (verbose)'
  try:
    opts, args = getopt.getopt(argv,"ht:l:o:s:d:f:g:b:cv",["tree=","locus=","out=", "search=", "date=", "field=", "outgroup=", "bootstrap=", "clades=", "verbose="])
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
    elif opt in ("-g", "--outgroup"):
       outgroup = arg
    elif opt in ("-b", "--bootstrap"):
       bootstrap = arg
    elif opt in ("-c", "--clades"):
       debug = 1
    elif opt in ("-v", "--verbose"):
        verbose = 1

  if not locus:
      sys.exit("please specify locus\n\n%s\n" % usage)
  if bootstrap > 1:
     bootstrap = float(bootstrap) / 100
  total_sequences = 0
  tree = Tree(treefilename)
  add_sig(tree, bootstrap, outfilename)
  #tree = root_tree(tree)
  if outgroup:
      outgroup = outgroup.split(',')
  elif 'Trebouxia_ITS' in treefilename:
     outgroup = ('AY842266','AJ249567')

  root_tree(tree, treefilename, outgroup)

  try:
    con = mysql.connector.connect(host='localhost',
                                       database='PhotobiontDiversity',
                                       user='root')
            
    cur = con.cursor(buffered=True)
    colour_clades(cur, tree, locus, outfilename, debug)
    groups = {}
    for leaf in tree:
      accession = leaf.name
      command = "SELECT `Group` FROM Metadata WHERE SeqID LIKE %s AND Gene= %s"
      options = (accession + '%', locus)
      execute_command(cur, command, options)
      try:
        (group,) = cur.fetchone()
      except TypeError:    
        warnings.warn("No database entry for %s" % leaf.name)
        (group, host, substrate, species, clade) = ('','','', '', '')
      if not group or group.find('Group') == -1:
          sys.exit("%s does not have a group name" % accession)
      if group in groups:
          warnings.warn("%s and %s are both in the tree and both in %s" % (accession, groups[group], group))
      groups[group] = leaf.name
      command = "SELECT Host, Substrate, Species, Clade FROM Metadata WHERE `Group`= %s AND Gene= %s"
      options = (group, locus)
      execute_command(cur, command, options)
      group_members = cur.fetchall()
      total_sequences += len(group_members)
      if len(group_members) == 1: #singleton
        (host, substrate, species, clade) = group_members[0]
        if field == 'Host' and host and host != ' ' and host != 'free-living' and host != "Free-living" and host != 'Unknown' and host != 'unknown':
          label_info = [accession, host]
        else:    
          label_info = [accession, species]
      else:
        label_info = [group] + combine_info(field, group_members)
      bg_colour = None
      if searchterm and (' '.join(label_info).find(searchterm) > -1 or searchterm == leaf.name):
        if verbose:
            print "adding highlighting to node %s" % leaf.name
        bg_colour = "Yellow"
      elif date:
        if group and 'Group' in group:
          command = "SELECT SeqID FROM Metadata WHERE `Group`= %s AND Gene= %s AND Date = %s"
          options = (group, locus, date)
        else:
          command = "SELECT SeqID FROM Metadata WHERE SeqID LIKE %s AND Gene= %s AND Date = %s"
          options = (accession, locus, date)
        execute_command(cur, command, options)
        
        if len(cur.fetchall()) > 0:
          if verbose:
              print "adding highlighting to node %s" % leaf.name
          bg_colour = "Yellow"
          #label.background.color = "Yellow"
          #bg_colour = "Yellow"
      #leaf.add_face(label, column = 0)                        #This will include the group names / accession numbers in the tree. This may or may not be useful
      add_faces(cur, field, leaf, label_info, bg_colour, outfilename)
      
  except Error as e:
        print(e)
  finally:
        cur.close()
        con.close()
  
  print "Drawing tree with %s sequences" % total_sequences
  draw_tree(tree, outfilename) 
  if 'svg' in outfilename:
    add_header(outfilename, locus)  
  
def colour_clades(cur, tree, locus, outfilename, debug):
  clades = {}
  colours = {}
  command = "SELECT Colour, Taxon FROM Colours"
  options = ()
  execute_command(cur, command, options)
  for (colour, taxon) in cur.fetchall():
    colours[taxon] = colour
  
  for leaf in tree:
    accession = leaf.name
    command = "SELECT Clade FROM Metadata WHERE SeqID LIKE %s AND Gene= %s"
    options = (accession + '%', locus)
    execute_command(cur, command, options)
    try:
        clade = cur.fetchone()[0]
    except TypeError:    
        warnings.warn("No database entry for %s" % accession)
        continue
    if debug:
     try:
      label = TextFace(leaf.name)  #This colours the taxon names
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
    command = "SELECT Colour from Colours WHERE Taxon = %s"
    options = (clade.replace('T.', 'Trebouxia'),)
    execute_command(cur, command, options)
    try:
        colour = cur.fetchone()[0]
    except TypeError:    
        warnings.warn("No database entry for %s" % clade.replace('T.', 'Trebouxia'))
        continue
    print "setting clade %s to %s" % (clade, colour)

    colour_clade(tree, clades[clade], colour, outfilename) #this colours the branches of the tree
    label_clade(tree, clades[clade], colour, clade) #this adds clade names to tree


def label_clade(tree, leaves, colour, clade):
  if len(leaves) == 1:
    ancestor = leaves[0]   
  else:
    ancestor = tree.get_common_ancestor(leaves)  
  label = TextFace(clade, fsize=100, fgcolor = colour)
  ancestor.add_face(label, column=1, position = "float")


def colour_clade(tree, leaves, colour, outfilename):
  sig = NodeStyle()
  sig["vt_line_color"] = colour
  sig["hz_line_color"] = colour
  if 'svg' in outfilename:
    sig["vt_line_width"] = 6
    sig["hz_line_width"] = 6
  else:
    sig["vt_line_width"] = 2
    sig["hz_line_width"] = 2
  sig["fgcolor"] = colour
  sig["size"] = 10
  nonsig = NodeStyle()
  nonsig["vt_line_color"] = colour
  nonsig["hz_line_color"] = colour
  if 'svg' in outfilename:
    nonsig["vt_line_width"] = 6
    nonsig["hz_line_width"] = 6
  else:
    nonsig["vt_line_width"] = 2
    nonsig["hz_line_width"] = 2
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


def root_tree(tree, treefilename, outgroup):
  root = tree.get_midpoint_outgroup()
  try:
      tree.set_outgroup(root)
  except:
      pass
  if outgroup:
    leaves = []
    for taxon in outgroup:
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
  print label_info
  for label in label_info[1:]:
    print label
    genus = label.split(' ')[0]
    print genus
    taxon = ''
    #if genus.find('.') != -1:
    #  colours.append(colours[-1])
    if 0:
      pass
    else:
      if field == 'Host':
        try:
            command = "SELECT phylum FROM Taxonomy WHERE genus= %s"
            options = (genus,)
            execute_command(cur, command, options)
            taxon = cur.fetchone()[0]
        except TypeError:
          warnings.warn("No phylum entry for %s" % genus)
        if taxon and taxon == 'Ascomycota':
            try:
              command = "SELECT family FROM Taxonomy WHERE genus= %s"
              options = (genus,)
              execute_command(cur, command, options)
                   
              taxon = cur.fetchone()[0]
            except TypeError:
              warnings.warn("No family entry for %s" % genus,)
      else:
        taxon = ' '.join(label.split(' ')[:2])
      try:
        if 'letharii' in label:
          command ="SELECT Colour FROM Colours WHERE Taxon= %s"
          options = ('Trebouxia letharii',)
          execute_command(cur, command, options)          
        else:
            command ="SELECT Colour FROM Colours WHERE Taxon= %s"
            options = (str(taxon),)
            execute_command(cur, command, options)
        colour = cur.fetchone()      
        colours.append(colour[0])
      except TypeError:
        warnings.warn("No colour available for %s (%s)" % (genus, taxon,))
        colours.append('LightGray')
          
  print colours
  return colours
    
def execute_command(cur, command, options):
    if verbose:
        sys.stderr.write(PrintCommand(command, options))
    cur.execute(command, options)
    
def add_sig(tree, bootstrap, outfilename):
  non_sig = NodeStyle()
  non_sig["size"] = 0
  if 'svg' in outfilename:
    non_sig["vt_line_width"] = 6
    non_sig["hz_line_width"] = 6
  else:
    non_sig["vt_line_width"] = 2
    non_sig["hz_line_width"] = 2
  sig = NodeStyle()
  sig["size"] = 10
  sig["fgcolor"] = "black"
  if 'svg' in outfilename:
    sig["vt_line_width"] = 6
    sig["hz_line_width"] = 6
  else:
    sig["vt_line_width"] = 2
    sig["hz_line_width"] = 2
  for node in tree.traverse():
    if node.support < bootstrap or node.is_leaf() or node.is_root():
      node.set_style(non_sig)
    else:
      node.set_style(sig)
  
def combine_info(field, entries):
  host_counts = {}                   #Can include species names of free-living strains
  for (host, substrate, species, clade) in entries:
    if field == 'Species' or  field == 'species' or host == ' ' or host == 'free-living' or host == "Free-living" or host == 'Unknown' or host == 'unknown':
      info = species
    #elif host == ' ':
    #  info = 'Unknown'
    else:
      info = host
    #print "Host= %s, info = %s" % (host, info)
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
      if len(name.split(' ')) == 1:
          name = name + ' sp.'  #if only genus name is present add 'sp.' as species name
      genus = name.split(' ')[0]
      if genus in included_genera: #this will fail in cases where there are 2 genera that start with the same letter. I'm not sure I should fix it because it will be a problem with, eg, Parmelia and Parmeliopsis
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

def PrintCommand(command, options=()):
  if type(options) is str:
    if command.count("%s") != 1:
      sys.exit("Command requires %s options. %s supplied" % (command.count("%s"), 1))
    options = (options, "")
  elif command.count("%s") != len(options):
    sys.exit("Command requires %s options. %s supplied" % (command.count("%s"), len(options)))
  for param in options:
    command =command.replace("%s", "'" + param + "'", 1)
  return command + "\n"

def warning_on_one_line(message, category, filename, lineno, file=None, line=None):
    return ' %s:%s: %s: %s\n' % (filename, lineno, category.__name__, message)
  
if __name__ == "__main__":
    warnings.formatwarning = warning_on_one_line
    main(sys.argv[1:])
