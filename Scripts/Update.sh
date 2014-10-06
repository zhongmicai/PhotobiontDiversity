dataset=$1
ref_seq=$2 # Ncommune_rbcX.fa
clade=$(#echo $dataset | cut -d_ -f 1)
locus=$(#echo $dataset | cut -d_ -f 2)
cur_date=$(date +%y%m%d)
ref_seq=$(pwd)/$ref_seq
log_file=~/Documents/PhotobiontDiversity/$cur_date/log.txt
exec > $log_file
set -x
#echo $ref_seq
#create folder for current post and for log of commands
if ! test -d ~/Documents/PhotobiontDiversity/$cur_date
then
  mkdir ~/Documents/PhotobiontDiversity/$cur_date
fi

#create folder if not present
if ! test -d ~/Documents/PhotobiontDiversity/$dataset
then
  #echo "`date`: mkdir ~/Documents/PhotobiontDiversity/$dataset" >> .$log_file
  mkdir ~/Documents/PhotobiontDiversity/$dataset
fi

#echo "`date`: cd ~/Documents/PhotobiontDiversity/$dataset" >> $log_file
cd ~/Documents/PhotobiontDiversity/$dataset

#run blast (need all sequences even if updating because there may be new redundant sequences)
#echo "`date`: blastn -query $ref_seq -db nt -evalue 1e-180 -max_target_seqs 3000 -out ${dataset}.bl -outfmt '6 qseqid qlen sacc slen pident length mismatch gapopen qstart qend qframe sstart send sframe evalue bitscore'" >> $log_file
blastn -query $ref_seq -db nt -evalue 1e-100 -max_target_seqs 3000 -out ${dataset}.bl -outfmt '6 qseqid qlen sacc slen pident length mismatch gapopen qstart qend qframe sstart send sframe evalue bitscore'

#get all sequences that are on the correct strand and not part of a larger sequence
#echo "`date`: awk '{if($12<$13) print $0}' ${dataset}.bl | awk '{if ($4 < 3000) print $0}' |cut -f3 | sort | uniq > ${dataset}_acc.txt" >> $log_file
awk '{if($12<$13) print $0}' ${dataset}.bl | awk '{if ($4 < 3000) print $0}' |cut -f3 | sort | uniq > ${dataset}_acc.txt

#echo "`date`: blastdbcmd -db nt -entry_batch ${dataset}_acc.txt |../Scripts/GetRedundant.pl >${dataset}_all.fa" >> $log_file
blastdbcmd -db nt -entry_batch ${dataset}_acc.txt |../Scripts/GetRedundant.pl >${dataset}_all.fa

#get negative strand sequences that are not part of a larger sequence
#echo "`date`: awk '{if($12>$13) print $0}' ${dataset}.bl | awk '{if ($4 < 3000) print $0}' |cut -f3 | sort | uniq > ${dataset}_acc_rc.txt" >> $log_file
awk '{if($12>$13) print $0}' ${dataset}.bl | awk '{if ($4 < 3000) print $0}' |cut -f3 | sort | uniq > ${dataset}_acc_rc.txt

if test -s ${dataset}_acc_rc.txt
then
  #echo "`date`: blastdbcmd -db nt -entry_batch ${dataset}_acc_rc.txt |../Scripts/GetRedundant.pl | ../Scripts/rc.pl >>${dataset}_all.fa" >> $log_file
  blastdbcmd -db nt -entry_batch ${dataset}_acc_rc.txt |../Scripts/GetRedundant.pl | ../Scripts/rc.pl >>${dataset}_all.fa
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

#print number of sequences
#echo "`date`: wc -l ${dataset}_acc.txt" >> $log_file
wc -l ${dataset}_acc.txt

#filter out sequences that are already in the DB
#echo "`date`: python ../Scripts/GetNew.py ${dataset}_all.fa >${dataset}_new.fa" >> $log_file
python ../Scripts/GetNew.py ${dataset}_all.fa | perl -p -e 's/^>\s*gi\|\d+\|\w+\|(\w+)\.\d\|.*/>$1/' >${dataset}_new.fa

#Download genbank sequences and parse metadata and update DB
#echo "`date`: grep '>' ${dataset}_new.fa | perl -p -e 's/>//' | python ../Scripts/GetGB.py >${dataset}_new.gb" >> $log_file
grep '>' ${dataset}_new.fa | perl -p -e 's/>//' | python ../Scripts/GetGB.py >${dataset}_new.gb

#echo "`date`: ../Scripts/ParseHost.pl ${dataset}_new.gb $clade ${locus} ${cur_date} >${dataset}_metadata_new.txt" >> $log_file
../Scripts/ParseHost.pl ${dataset}_new.gb $clade ${locus} ${cur_date} >${dataset}_metadata_new.txt

#echo "`date`: python ../Scripts/UpdateDB.py ${dataset}_metadata_new.txt" >> ../$cur_date/log.txt
python ../Scripts/UpdateDB.py ${dataset}_metadata_new.txt

#Add new data to master datasets
#echo "`date`: cat ${dataset}_new.fa >> ${dataset}.fa" >> ../$cur_date/log.txt
cat ${dataset}_new.fa >> ${dataset}.fa

#echo "`date`:${dataset}_metadata_new.txt >> ${dataset}_metadata.txt" >> ../$cur_date/log.txt
cat ${dataset}_metadata_new.txt >> ${dataset}_metadata.txt

#echo "`date`: cat ${dataset}_new.gb >> ${dataset}.gb" >> ../$cur_date/log.txt
cat ${dataset}_new.gb >> ${dataset}.gb


#cluster sequences and assign groups
#echo "`date`: usearch -cluster_fast ${dataset}.fa -id 1 -centroids ${dataset}_nr.fa -uc ${dataset}_groups.txt" >> ../$cur_date/log.txt
usearch -cluster_fast ${dataset}.fa -id 1 -centroids ${dataset}_nr.fa -uc ${dataset}_groups.txt

#echo "`date`: python ../Scripts/GetGroups.py -g ${dataset}_groups.txt -l ${locus}" >> ../$cur_date/log.txt
python ../Scripts/GetGroups.py -g ${dataset}_groups.txt -l ${locus}


#create alignment and make tree, mapping on metadata
#echo "`date`: mafft ${dataset}_nr.fa  >${dataset}_aln.fa" >> ../$cur_date/log.txt
mafft ${dataset}_nr.fa  >${dataset}_aln.fa 

#echo "`date`: exset = ../Scripts/GetExcluded.pl ${dataset}_aln.fa" >> ../$cur_date/log.txt
exset=$(../Scripts/GetExcluded.pl ${dataset}_aln.fa)

#echo "`date`: trimal -in ${dataset}_aln.fa  -phylip -select $exset >${dataset}.phy" >> ../$cur_date/log.txt
trimal -in ${dataset}_aln.fa  -phylip -select $exset >${dataset}.phy

#echo "`date`: phyml --quiet --no_memory_check -i ${dataset}.phy" >> ../$cur_date/log.txt
phyml  --quiet --no_memory_check -i ${dataset}.phy

#echo "`date`: mv ${dataset}.phy_phyml_tree.txt ${dataset}.nwk" >> ../$cur_date/log.txt
mv ${dataset}.phy_phyml_tree.txt ${dataset}.nwk

if test $dataset == 'Trebouxia_ITS'
then
  #echo "`date`: python ../Scripts/FormatTree.py -t ${dataset}.nwk -l $locus -d $cur_date -f species -o ${dataset}.svg" >> ../$cur_date/log.txt
  python ../Scripts/FormatTree.py -t ${dataset}.nwk -l $locus -d $cur_date -f species  -o ${dataset}.svg

  #echo "`date`: python ../Scripts/FormatTree.py -t ${dataset}.nwk -l $locus -d $cur_date -f species  -o ${dataset}.pdf" >> ../$cur_date/log.txt
  python ../Scripts/FormatTree.py -t ${dataset}.nwk -l $locus -d $cur_date -f species  -o ${dataset}.pdf
else
  #echo "`date`: python ../Scripts/FormatTree.py -t ${dataset}.nwk -l $locus -d $cur_date -o ${dataset}.svg" >> ../$cur_date/log.txt
  python ../Scripts/FormatTree.py -t ${dataset}.nwk -l $locus -d $cur_date -o ${dataset}.svg

  #echo "`date`: python ../Scripts/FormatTree.py -t ${dataset}.nwk -l $locus -d $cur_date -o ${dataset}.pdf" >> ../$cur_date/log.txt
  python ../Scripts/FormatTree.py -t ${dataset}.nwk -l $locus -d $cur_date -o ${dataset}.pdf
fi

#copy new files to current post folder
#echo "`date`: cp ${dataset}.pdf ${dataset}.svg ${dataset}.nwk ${dataset}.phy ${dataset}_aln.fa ${dataset}_nr.fa ${dataset}_new.fa ${dataset}_metadata_new.txt ${dataset}_new.gb ../$cur_date" >> ../$cur_date/log.txt
cp ${dataset}.pdf ${dataset}.svg ${dataset}.nwk ${dataset}.phy ${dataset}_aln.fa ${dataset}_groups.txt ${dataset}_nr.fa ${dataset}_new.fa ${dataset}_metadata_new.txt ${dataset}_new.gb ../$cur_date



