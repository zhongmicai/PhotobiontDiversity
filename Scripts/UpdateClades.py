#!/Users/HeathOBrien/anaconda/bin/python

"""Use phylogeny to update the "clade" information in the DB"""


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
  usage = 'UpdateClades.py -t <treefile> -l <locus>'
  try:
    opts, args = getopt.getopt(argv,"ht:l:",["tree=","locus="])
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

  tree = Tree(treefilename)
  root_tree(tree, treefilename)
  try:
     con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity', unix_socket="/tmp/mysql.sock")
  except mdb.Error, e:
    print "Error %d: %s" % (e.args[0],e.args[1])
    sys.exit(1)   
  with con:
    cur = con.cursor()
    #tree = colour_clades(cur, tree, locus)
    update_clades(cur, tree, locus)
  

def update_clades(cur, tree, locus):
  clades = {}
  
  for leaf in tree:
    accession = leaf.name
    cur.execute("SELECT Clade FROM Metadata WHERE SeqID LIKE %s AND Gene= %s", (accession + '%', locus,))
    try:
        clade = cur.fetchone()[0]
    except TypeError:    
        warnings.warn("No database entry for %s" % accession)
        continue
    if clade and clade != 'Trebouxia':
      if clade in clades:
        clades[clade].append(leaf) 
      else:
        clades[clade] = [leaf]
        
  for clade in clades:
    if 'URa2' in clade:
      continue
    leaves = clades[clade]
    if len(leaves) > 1:
      ancestor = tree.get_common_ancestor(leaves)
      for leaf in ancestor: #first pass to check for problems before any updating
        cur.execute("SELECT Clade from Metadata WHERE SeqID = %s", leaf.name)
        db_clade = cur.fetchone()[0]
        if db_clade and db_clade != 'Trebouxia' and db_clade != clade:
          sys.exit("clade for |%s| also contains members of |%s|" % (clade, db_clade))
      for leaf in ancestor:
        cur.execute("SELECT `Group` FROM Metadata WHERE SeqID = %s", (leaf.name))
        try:
          group = cur.fetchone()[0]
        except TypeError:    
          warnings.warn("No Group info for %s" % leaf.name)
          group = ''   
        if group and group.find('Group') != -1:
          cur.execute("SELECT SeqID FROM Metadata WHERE `Group` = %s AND Gene = %s", (group, locus))
          for result in cur.fetchall():
            cur.execute("UPDATE Metadata SET Clade = %s WHERE SeqID = %s", (clade, result[0]))
        else:
          cur.execute("UPDATE Metadata SET Clade = %s WHERE SeqID = %s", (clade, leaf.name))

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
  
  
if __name__ == "__main__":
   main(sys.argv[1:])
