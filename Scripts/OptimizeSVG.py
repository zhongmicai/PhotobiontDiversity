import sys
try:
    import xml.etree.cElementTree as ET
except ImportError:
    import xml.etree.ElementTree as ET
input_tree = ET.ElementTree(file="/Users/heo3/Documents/PhotobiontDiversity/Test/test.svg")
for elem in input_tree.iter():
  elem.tag = elem.tag.replace("{http://www.w3.org/2000/svg}", "")
input_root = input_tree.getroot()

input_main = input_root.find('g')

root = ET.Element('svg')
for key in input_root.keys():
  root.set(key, input_root.get(key))
root.set("xmlns", "http://www.w3.org/2000/svg")
root.set("xmlns:xlink", "http://www.w3.org/1999/xlink")

script = ET.Element('script')
script.set("xlink:href", "SVGPan.js")

title = ET.Element('title')
title.text = "rbcX phylogeny" #this will need to be set dynamically

description = ET.Element('description')
description.text = "Modified from output of ETE http://ete.cgenomics.org"

defs = ET.Element('defs')

viewport = ET.Element('g')
viewport.set("id", "viewport")
viewport.set("transform", "translate(200, 50)")

main = ET.Element('g')
for key in input_main.keys():
  main.set(key, input_main.get(key))

for child in input_main:
  if len(child.findall('polyline')) > 0 or \
     len(child.findall('path')) > 0 or \
     len(child.findall('text')) > 0:
    #print child.attrib
    main.append(child)
viewport.append(main)
root.extend((script, title, description, defs, viewport))
tree = ET.ElementTree(root)
tree.write(sys.stdout, encoding="utf-8", xml_declaration=True)
print ""