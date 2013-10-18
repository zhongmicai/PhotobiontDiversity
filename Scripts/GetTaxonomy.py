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

Entrez.email = sys.argv[1]
if not Entrez.email:
    print "you must add your email address"
    sys.exit(2)

con = mdb.connect('localhost', 'root', '', 'PhotobiontDiversity');
with con:
    cur = con.cursor()
    cur.execute("SELECT Host, Species FROM Metadata")
    genera = []
    for names in cur.fetchall():
      for name in names:
        if name and name != 'free-living':
          name = name.replace("cf. ", "")
          
          genus = name.split(" ")[0]
          if not genus in genera:
            genera.append(genus)

    for genus in genera:
      taxid = get_tax_id(genus)
      if taxid:
        cur.execute("INSERT INTO Taxonomy(id, genus) VALUES(%s, %s)", (taxid, genus,))
        data = get_tax_data(taxid)
        lineage = {d['Rank']:d['ScientificName'] for d in 
          data[0]['LineageEx']}
        for rank in lineage.keys():
          if rank == 'family':
            cur.execute("UPDATE Taxonomy SET family = %s WHERE genus= %s", (lineage[rank], genus,))
          elif rank == 'order':
            cur.execute("UPDATE Taxonomy SET `order` = %s WHERE genus= %s", (lineage[rank], genus,))
          elif rank == 'subclass':
            cur.execute("UPDATE Taxonomy SET subclass = %s WHERE genus= %s", (lineage[rank], genus,))
          elif rank == 'class':
            cur.execute("UPDATE Taxonomy SET class = %s WHERE genus= %s", (lineage[rank], genus,))
          elif rank == 'subphylum':
            cur.execute("UPDATE Taxonomy SET subphylum = %s WHERE genus= %s", (lineage[rank], genus,))
          elif rank == 'phylum':
            cur.execute("UPDATE Taxonomy SET phylum = %s WHERE genus= %s", (lineage[rank], genus,))
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

