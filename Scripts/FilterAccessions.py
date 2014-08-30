#!/usr/local/bin/python

import sys, fileinput
import MySQLdb as mdb

try:
    con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity');
    cur = con.cursor()
    for line in fileinput.input():
        accession = line.strip()
        cur.execute("SELECT * FROM Metadata WHERE SeqID= %s", (accession,))
        if not cur.fetchone():
            print accession
except mdb.Error, e:
  
    print "Error %d: %s" % (e.args[0],e.args[1])
    sys.exit(1)
    
finally:    
        
    if con:    
        con.close()
        