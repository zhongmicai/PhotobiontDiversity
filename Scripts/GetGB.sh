#!/bin/bash
#
#$ -cwd
#$ -j y
#$ -S /bin/bash
#
export PATH=$HOME/bin:$PATH
export BLASTDB=/c8000xd3/rnaseq-heath/DB
export PERL5LIB=$HOME/src/bioperl-live:$HOME/Bio-Root:$HOME/perl5/lib/perl5

cd /c8000xd3/rnaseq-heath/PhotobiontDiversity/160905
dataset=TrebouxiaITS
echo "getting GB sequences for $dataset"
grep '>' ${dataset}_new.fa | perl -p -e 's/>//' | python ../Scripts/GetGB.py >${dataset}_new.gb
echo "finished getting GB sequences $dataset"
