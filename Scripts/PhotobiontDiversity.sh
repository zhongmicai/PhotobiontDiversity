#!/bin/bash
#
#$ -cwd
#$ -j y
#$ -S /bin/bash
#
export BLASTDB="/c8000xd3/rnaseq-heath/DB"
cd /c8000xd3/rnaseq-heath/PhotobiontDiversity/160905
blastn -query ../ReferenceSeqs/Trebouxia_ITS.fa -db nt -evalue 1e-180 -max_target_seqs 10000 -out Trebouxia_ITs.bl -outfmt '6 qseqid qlen sacc slen pident length mismatch gapopen qstart qend qframe sstart send sframe evalue bitscore'
