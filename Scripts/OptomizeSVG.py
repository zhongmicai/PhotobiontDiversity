######### UNTESTED ##########

import sys
import xml.etree.ElementTree as ET

#Read in tree and set root
filename = sys.argv[1]
tree = ET.parse(filename)
root = tree.getroot()

#remove the thrice-damned "{http://www.w3.org/2000/svg}" gobbledygook from all the top-level tags
root.find('{http://www.w3.org/2000/svg}svg').tag = 'svg'
root.find('{http://www.w3.org/2000/svg}title').tag = 'title'
root.find('{http://www.w3.org/2000/svg}desc').tag = 'desc'
root.find('{http://www.w3.org/2000/svg}defs').tag = 'defs'
root.find('{http://www.w3.org/2000/svg}defs').g = 'g'

#remove gobbledygook from children
for child in root.iter(tag='{http://www.w3.org/2000/svg}polyline'):
  child.tag = 'polyline'
for child in root.iter(tag='{http://www.w3.org/2000/svg}path'):
  child.tag = 'path'
for child in root.iter(tag='{http://www.w3.org/2000/svg}text'):
  child.tag = 'text'
for child in root.iter(tag='{http://www.w3.org/2000/svg}g'):
  child.tag = 'g'

#remove empty children and extra formatting from remaining ones (only need the transform matrix)
g = root.find('g')
for child in g:
  if len(child) < 1:
    g.remove(child)   #for some mysterious reason, I had to iterate multiple times through this step
  else:   
    matrix = child.get('transform')
    sub = child[0]
    child.clear()
    child.set('transform', matrix)
    child.append(sub)
    child.tag = 'g'

"""Things left to do:

-remove formatting from lowest-level items so that it can be specified in css file
(need to provide separate classes for each lichen family to add color-coding with css)

-add empty boxes for each taxon, with class names matching accession number (can apply
fill colour to specific accession numbers in the css this way)

Print out result and make sure it will be read correctly as svg (probably need to
print custom header and discard high-level info here.

Then all I have to do is figure out how to modify the css dynamically when a search
term is entered, which will activate built in highlighting

Once that's done, I will need to figure out how to add links to my database interface and
read them into the tree viewing page

"""

