#!/bin/bash
#
#$ -cwd
#$ -j y
#$ -S /bin/bash
#
export PATH=$HOME/bin:$PATH
export BLASTDB=/c8000xd3/rnaseq-heath/DB
export PERL5LIB=$HOME/src/bioperl-live:$HOME/Bio-Root:$HOME/perl5/lib/perl5

phyml  --quiet --no_memory_check -i $1
