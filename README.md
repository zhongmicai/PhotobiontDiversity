PhotobiontDiversity
===================

Data and scripts for PhotobiontDiversity.wordpress.com

Scripts:

RevCom.pl -reverse complements sequences that have blast hit on negative strand
trunc.pl -truncate sequence at specified coordinates
GetSeq.pl -index fasta file and retrieve specified sequence
FilterSeq.pl -remove sequence longer/shorter than specified values(s)
ConvertSeq.pl -convert among different sequence/alignment file formats

Datasets:
Ncommune_rbcX.fa -initial query sequence
Nostoc_rbcX_acc.txt -list of rbcX accession numbers
Nostoc_rbcX_aln.fa -MAFFT alignment in fasta format
Nostoc_rbcX_filtered.fa -sequencences shorter than 2600 bp
Nostoc_rbcX_revcom.fa -all sequences in correct orientation
Nostoc_rbcX_trim.nex -alignment with redundant sequences and ambiguous alignment positions removed
Nosotoc_rbcX.bl -megablast results
Nostoc_rbcX.fa -all unmodified rbcX sequences
Nostoc_rbcX.fa.inx -index of rbcX sequences
Nostoc_rbcX.nwk -rbcX phylogeny
Nostoc_rbcX.pdf -PDF of rbcX phylogeny

