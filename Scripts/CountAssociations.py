#!/Users/HeathOBrien/anaconda/bin/python

#count the number of host genus / photobiont clade associations and print matrix

import sys, getopt
import csv
import MySQLdb as mdb

def main(argv):
  usage = 'CountAssociations.py -t <table_file> -c <css_file> -i <table_id>'
  table_id = 2
  table_file = ''
  css_file = ''
  try:
    opts, args = getopt.getopt(argv,"ht:c:i:",["table=","css=", "id"])
  except getopt.GetoptError:
    print usage
    sys.exit(2)
  for opt, arg in opts:
    if opt == "-h":
       print usage
       sys.exit()
    elif opt in ("-t", "--table"):
       table_file = arg
    elif opt in ("-c", "--css"):
       css_file = arg
    elif opt in ("-i", "--id"):
       table_id = arg
  clade_list =  ["T. arboricola", "T. asymmetrica", "T. corticola", "T. decolorans", "T. gelatinosa",
           "T. gigantea", "T. impressa", "T. incrustata", "T. jamesii", "T. showmanii",
           "T. sp. 1", "T. sp. 2", "T. sp. 3", "T. sp. 4", "T. sp. 5" ]
  try:
     con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity', unix_socket="/tmp/mysql.sock")
  except mdb.Error, e:
    print "Error %d: %s" % (e.args[0],e.args[1])
    sys.exit(1)   
  with con:
    cur = con.cursor()
    colours = []
    for clade in clade_list:
      cur.execute("SELECT RGB.Hex FROM RGB, Colours WHERE RGB.Colour = Colours.Colour AND Colours.Taxon = %s", clade)
      colours.append(cur.fetchone()[0]
      
    table_fh = open(table_file, 'w')  
    table_fh.write(', '.join(['Genus', 'Family'] + clade_list), '\n')
    css_fh = open(css_file, 'w')  
    cur.execute("SELECT Genus, family FROM Taxonomy ORDER BY Genus")
    counter = 0
    for (genus, family) in cur.fetchall():
      #print "SELECT Clade, COUNT(Clade) FROM Metadata Where Host LIKE '%s' AND Species LIKE '%s' GROUP BY Clade" % (genus + '%', 'Trebouxia%')
      cur.execute("SELECT Clade, COUNT(Clade) FROM Metadata Where Host LIKE %s AND Species LIKE %s GROUP BY Clade", (genus + '%', 'Trebouxia%'))
      associations = {}
      for (clade, count) in cur.fetchall():
        if clade:
          associations[clade] = count
      if len(associations) > 0:
        counter += 1
        output = [genus, family]
        for (column_number, column) in enumerate(clade_list):
          if column in associations:
            output.append(associations[column])
            css_fh.write(".tablepress-id-%i .row-%i .column-%i {\n" % (table_id, counter, index))
            css_fh.write("	background-color: #%s;\n" % colours[index])
            css_fh.write( "}\n")
          else:
            output.append(0)
        table_fh.write(", ".join(map(str, output)), '\n')         
    table_fh.close()
    css_fh.close()
    
if __name__ == "__main__":
   main(sys.argv[1:])


