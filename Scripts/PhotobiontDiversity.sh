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
echo "blasting $dataset"
blastn -query ../ReferenceSeqs/Trebouxia_ITS.fa -db nt -evalue 1e-180 -max_target_seqs 10000 -out $dataset.bl -outfmt '6 qseqid qlen sacc slen pident length mismatch gapopen qstart qend qframe sstart send sframe evalue bitscore'
echo "finished blasting $dataset"
awk '{if($12<$13) print $0}' ${dataset}.bl | awk '{if ($4 < 3000) print $0}' |cut -f3 | sort | uniq > ${dataset}_acc.txt

#echo "`date`: blastdbcmd -db nt -entry_batch ${dataset}_acc.txt |../Scripts/GetRedundant.pl >${dataset}_all.fa" >> $log_file
blastdbcmd -db nt -entry_batch ${dataset}_acc.txt | perl ../Scripts/GetRedundant.pl >${dataset}_all.fa

#get negative strand sequences that are not part of a larger sequence
#echo "`date`: awk '{if($12>$13) print $0}' ${dataset}.bl | awk '{if ($4 < 3000) print $0}' |cut -f3 | sort | uniq > ${dataset}_acc_rc.txt" >> $log_file
awk '{if($12>$13) print $0}' ${dataset}.bl | awk '{if ($4 < 3000) print $0}' |cut -f3 | sort | uniq > ${dataset}_acc_rc.txt

if test -s ${dataset}_acc_rc.txt
then
  #echo "`date`: blastdbcmd -db nt -entry_batch ${dataset}_acc_rc.txt |../Scripts/GetRedundant.pl | ../Scripts/rc.pl >>${dataset}_all.fa" >> $log_file
  blastdbcmd -db nt -entry_batch ${dataset}_acc_rc.txt | perl ../Scripts/GetRedundant.pl | perl ../Scripts/rc.pl >>${dataset}_all.fa
else
  #echo "`date`: rm ${dataset}_acc_rc.txt" >> $log_file
  rm ${dataset}_acc_rc.txt
fi

#echo "`date`: awk '{if ($4 > 3000) print $0}' ${dataset}.bl > ${dataset}_long.bl" >> $log_file
awk '{if ($4 > 3000) print $0}' ${dataset}.bl > ${dataset}_long.bl
if test -s ${dataset}_long.bl
then
  #echo "`date`: cut -f3 ${dataset}_long.bl | sort | uniq > ${dataset}_acc_long.txt" >> $log_file
  cut -f3 ${dataset}_long.bl | sort | uniq > ${dataset}_acc_long.txt
  #echo "`date`: blastdbcmd -db nt -entry_batch ${dataset}_acc_long.txt > ${dataset}_all_long.fa" >> $log_file
  blastdbcmd -db nt -entry_batch ${dataset}_acc_long.txt > ${dataset}_all_long.fa
  #echo "`date`: python ../Scripts/ExtractHitRegion.py ${dataset}_all_long.fa ${dataset}_long.bl >>${dataset}_all.fa" >> $log_file
  python ../Scripts/ExtractHitRegion.py ${dataset}_all_long.fa ${dataset}_long.bl >>${dataset}_all.fa
else
  #echo "`date`: rm ${dataset}_long.bl" >> $log_file
  rm ${dataset}_long.bl
fi

