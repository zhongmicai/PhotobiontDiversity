use sqlite3


def add_lines(blastfile, db_name, gene):
  conn = sqlite3.connect(db_name)
  c = conn.cursor()
  #strand: 1 = pos, 0 = neg
  c.execute('''CREATE TABLE hits
                (accession text, group text, group_rep text, host text, species text, clade text, strain text, location text, author text, reference text, pubmed int, gene text)''')
  with open(blastfile, 'rU') as infile:
    reader=csv.reader(infile,delimiter='\t')
    prev = 'init'
    hit_num = 0
    for row in reader:
      row.append(gene)  #add strand info to row
      c.execute("INSERT INTO hits VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", row)
    conn.commit()
  conn.close()
  
if __name__ == "__main__":
   main(sys.argv[1:])
