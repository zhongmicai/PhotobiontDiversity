#!/Users/HeathOBrien/anaconda/bin/python
import sys, warnings
from Bio import Entrez
import MySQLdb as mdb

def get_tax_id(species):
    """to get data from ncbi taxomomy, we need to have the taxid.  we can
    get that by passing the species name to esearch, which will return
    the tax id"""
    species = species.replace(" ", "+").strip()
    try:
      search = Entrez.esearch(term = species, db = "taxonomy", retmode = "xml")
      record = Entrez.read(search)
      id = record['IdList'][0]
    except:
      warnings.warn("No taxid for %s" % species)
      id = ''
    return id

def get_tax_data(taxid):
    """once we have the taxid, we can fetch the record"""
    search = Entrez.efetch(id = taxid, db = "taxonomy", retmode = "xml")
    return Entrez.read(search)

try:
  Entrez.email = sys.argv[1]
except IndexError:
    print "you must add your email address"
    sys.exit(2)

try:
  con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity', unix_socket="/tmp/mysql.sock")
except mdb.Error, e:
  print "Error %d: %s" % (e.args[0],e.args[1])
  sys.exit(1)   
with con:
    cur = con.cursor()
    cur.execute("SELECT Host, Species FROM Metadata", )
    genera = []
    for names in cur.fetchall():
      for name in names:
        if name and name != 'free-living':
          name = name.replace("cf. ", "")
          
          genus = name.split(" ")[0]
          if not genus in genera:
            genera.append(genus)

    for genus in genera:
      cur.execute("SELECT * FROM Taxonomy WHERE Genus = %s", genus)
      if len(cur.fetchall()) == 0:
        taxid = get_tax_id(genus)
        if taxid:
          try:
            cur.execute("INSERT INTO Taxonomy(id, genus) VALUES(%s, %s)", (taxid, genus,))
          except mdb.IntegrityError:
            warnings.warn("duplicate entry for %s taxid (%s)" % (genus, taxid))
            continue
          data = get_tax_data(taxid)
          lineage = {d['Rank']:d['ScientificName'] for d in 
            data[0]['LineageEx']}
          for rank in lineage.keys():
            if rank == 'family':
              cur.execute("UPDATE Taxonomy SET family = %s WHERE genus= %s", (lineage[rank], genus,))
              #cur.execute("SELECT Colour FROM Colours WHERE Taxon = %s", lineage[rank])
              #if len(cur.fetchall()) == 0:
              #  print "No colour in DB for %s" % lineage[rank]
            elif rank == 'tribe':
              cur.execute("UPDATE Taxonomy SET tribe = %s WHERE genus= %s", (lineage[rank], genus,))
            elif rank == 'subfamily':
              cur.execute("UPDATE Taxonomy SET subfamily = %s WHERE genus= %s", (lineage[rank], genus,))
            elif rank == 'superfamily':
              cur.execute("UPDATE Taxonomy SET superfamily = %s WHERE genus= %s", (lineage[rank], genus,))
            elif rank == 'superorder':
              cur.execute("UPDATE Taxonomy SET `superorder` = %s WHERE genus= %s", (lineage[rank], genus,))
            elif rank == 'order':
              cur.execute("UPDATE Taxonomy SET `order` = %s WHERE genus= %s", (lineage[rank], genus,))
            elif rank == 'suborder':
              cur.execute("UPDATE Taxonomy SET `suborder` = %s WHERE genus= %s", (lineage[rank], genus,))
            elif rank == 'infraorder':
              cur.execute("UPDATE Taxonomy SET `infraorder` = %s WHERE genus= %s", (lineage[rank], genus,))
            elif rank == 'subclass':
              cur.execute("UPDATE Taxonomy SET subclass = %s WHERE genus= %s", (lineage[rank], genus,))
            elif rank == 'infraclass':
              cur.execute("UPDATE Taxonomy SET infraclass = %s WHERE genus= %s", (lineage[rank], genus,))
            elif rank == 'class':
              cur.execute("UPDATE Taxonomy SET class = %s WHERE genus= %s", (lineage[rank], genus,))
            elif rank == 'superclass':
              cur.execute("UPDATE Taxonomy SET superclass = %s WHERE genus= %s", (lineage[rank], genus,))
            elif rank == 'subphylum':
              cur.execute("UPDATE Taxonomy SET subphylum = %s WHERE genus= %s", (lineage[rank], genus,))
            elif rank == 'phylum':
              cur.execute("UPDATE Taxonomy SET phylum = %s WHERE genus= %s", (lineage[rank], genus,))
            elif rank == 'superphylum':
              cur.execute("UPDATE Taxonomy SET superphylum = %s WHERE genus= %s", (lineage[rank], genus,))
            elif rank == 'subkingdom':
              cur.execute("UPDATE Taxonomy SET subkingdom = %s WHERE genus= %s", (lineage[rank], genus,))
            elif rank == 'kingdom':
              cur.execute("UPDATE Taxonomy SET kingdom = %s WHERE genus= %s", (lineage[rank], genus,))
            elif rank == 'superkingdom':
              cur.execute("UPDATE Taxonomy SET superkingdom = %s WHERE genus= %s", (lineage[rank], genus,))
            elif rank == 'no rank':
              cur.execute("UPDATE Taxonomy SET no_rank = %s WHERE genus= %s", (lineage[rank], genus,))
            else:
              warnings.warn("No %s field in database" % rank)
      #else:
        #print "%s already in taxonomyDB" % genus
