dataset=$1
ref_seq=$2 # Ncommune_rbcX.fa
clade=$(cut -d_ -f 1)
locus=$(cut -d_ -f 2)
cur_date=$(date +%y%m%d)
ref_seq=$(pwd)/$ref_seq
log_file=~/Documents/PhotobiontDiversity/$cur_date/log.txt
echo $ref_seq
#create folder for current post and for log of commands
if ! test -d ~/Documents/PhotobiontDiversity/$cur_date
then
  mkdir ~/Documents/PhotobiontDiversity/$cur_date
fi

#update blast database (this may take a while)
echo "`date`: cd ~/Database/nt" > $log_file
cd ~/Database/nt
echo "`date`: update_blastdb.pl nt" >> $log_file
update_blastdb.pl nt
echo "`date`: for file in `ls | grep "tar$"`; do tar -xvzf $file; rm $file; done;" >> $log_file
for file in `ls | grep "tar$"`; do tar -xvzf $file; rm $file; done;

#create folder if not present
if ! test -d ~/Documents/PhotobiontDiversity/$dataset
then
  echo "`date`: mkdir ~/Documents/PhotobiontDiversity/$dataset" >> .$log_file
  mkdir ~/Documents/PhotobiontDiversity/$dataset
fi

echo "`date`: cd ~/Documents/PhotobiontDiversity/$locus" >> $log_file
cd ~/Documents/PhotobiontDiversity/$locus

#run blast (need all sequences even if updating because there may be new redundant sequences)
echo "`date`: blastn -query $ref_seq -db nt -evalue 1e-180 -max_target_seqs 3000 -out ${locus}.bl -outfmt '6 qseqid qlen sacc slen pident length mismatch gapopen qstart qend qframe sstart send sframe evalue bitscore'" >> $log_file
blastn -query $ref_seq -db nt -evalue 1e-180 -max_target_seqs 3000 -out ${locus}.bl -outfmt '6 qseqid qlen sacc slen pident length mismatch gapopen qstart qend qframe sstart send sframe evalue bitscore'

#get all sequences that are on the correct strand and not part of a larger sequence
echo "`date`: awk '{if($12<$13) print $0}' ${locus}.bl | awk '{if ($4 < 3000) print $0}' |cut -f3 | sort | uniq > ${locus}_acc.txt" >> $log_file
awk '{if($12<$13) print $0}' ${locus}.bl | awk '{if ($4 < 3000) print $0}' |cut -f3 | sort | uniq > ${locus}_acc.txt

echo "`date`: blastdbcmd -db nt -entry_batch ${locus}_acc.txt |../Scripts/GetRedundant.pl >${locus}_all.fa" >> $log_file
blastdbcmd -db nt -entry_batch ${locus}_acc.txt |../Scripts/GetRedundant.pl >${locus}_all.fa

#get negative strand sequences that are not part of a larger sequence
echo "`date`: awk '{if($12>$13) print $0}' ${locus}.bl | awk '{if ($4 < 3000) print $0}' |cut -f3 | sort | uniq > ${locus}_acc_rc.txt" >> $log_file
awk '{if($12>$13) print $0}' ${locus}.bl | awk '{if ($4 < 3000) print $0}' |cut -f3 | sort | uniq > ${locus}_acc_rc.txt

if test -s ${locus}_acc_rc.txt
then
  echo "`date`: blastdbcmd -db nt -entry_batch ${locus}_acc_rc.txt |../Scripts/GetRedundant.pl | ../Scripts/rc.pl >>${locus}_all.fa" >> $log_file
  blastdbcmd -db nt -entry_batch ${locus}_acc_rc.txt |../Scripts/GetRedundant.pl | ../Scripts/rc.pl >>${locus}_all.fa
else
  echo "`date`: rm ${locus}_acc_rc.txt" >> $log_file
  rm ${locus}_acc_rc.txt
fi

echo "`date`: awk '{if ($4 > 3000) print $0}' ${locus}.bl > ${locus}_long.bl" >> $log_file
awk '{if ($4 > 3000) print $0}' ${locus}.bl > ${locus}_long.bl
if test -s ${locus}_long.bl
then
  echo "`date`: cut -f3 ${locus}_long.bl | sort | uniq > ${locus}_acc_long.txt" >> $log_file
  cut -f3 ${locus}_long.bl | sort | uniq > ${locus}_acc_long.txt
  echo "`date`: blastdbcmd -db nt -entry_batch ${locus}_acc_long.txt > ${locus}_all_long.fa" >> $log_file
  blastdbcmd -db nt -entry_batch ${locus}_acc_long.txt > ${locus}_all_long.fa
  echo "`date`: ../Scripts/ExtractHitRegion.py ${locus}_all_long.fa ${locus}_long.bl >>${locus}_all.fa" >> $log_file
  ../Scripts/ExtractHitRegion.py ${locus}_all_long.fa ${locus}_long.bl >>${locus}_all.fa
else
  echo "`date`: rm ${locus}_long.bl" >> $log_file
  rm ${locus}_long.bl
fi

#print number of sequences
echo "`date`: wc -l ${locus}_acc.txt" >> $log_file
wc -l ${locus}_acc.txt

#filter out sequences that are already in the DB
echo "`date`: ../Scripts/GetNew.py ${locus}_all.fa >${locus}_new.fa" >> $log_file
../Scripts/GetNew.py ${locus}_all.fa | perl -p -e 's/^>\s*gi\|\d+\|\w+\|(\w+)\.\d\|.*/>$1/' >${locus}_new.fa

#Download genbank sequences and parse metadata and update DB
echo "`date`: grep '>' ${locus}_new.fa | perl -p -e 's/>//' | ../Scripts/GetGB.py >${locus}_new.gb" >> $log_file
grep '>' ${locus}_new.fa | perl -p -e 's/>//' | ../Scripts/GetGB.py >${locus}_new.gb

echo "`date`: ../Scripts/ParseHost.pl ${locus}_new.gb $clade ${locus} ${cur_date} >${locus}_metadata_new.txt" >> $log_file
../Scripts/ParseHost.pl ${locus}_new.gb $clade ${locus} ${cur_date} >${locus}_metadata_new.txt

echo "`date`: ../Scripts/UpdateDB.py ${locus}_metadata_new.txt" >> ../$cur_date/log.txt
../Scripts/UpdateDB.py ${locus}_metadata_new.txt

#Add new data to master datasets
echo "`date`: cat ${locus}_new.fa >> ${locus}.fa" >> ../$cur_date/log.txt
cat ${locus}_new.fa >> ${locus}.fa

echo "`date`: > ../$cur_date/log.txt" >> ../$cur_date/log.txt
cat ${locus}_metadata_new.txt >> ${locus}_metadata.txt

echo "`date`: cat ${locus}_new.gb >> ${locus}.gb" >> ../$cur_date/log.txt
cat ${locus}_new.gb >> ${locus}.gb


#cluster sequences and assign groups
echo "`date`: usearch -cluster_fast ${locus}.fa -id 1 -centroids ${locus}_nr.fa -uc ${locus}_groups.txt" >> ../$cur_date/log.txt
usearch -cluster_fast ${locus}.fa -id 1 -centroids ${locus}_nr.fa -uc ${locus}_groups.txt

echo "`date`: ../Scripts/GetGroups.py -g ${locus}_groups.txt -l ${locus}" >> ../$cur_date/log.txt
../Scripts/GetGroups.py -g ${locus}_groups.txt -l ${locus}


#create alignment and make tree, mapping on metadata
echo "`date`: mafft ${locus}_nr.fa  >${locus}_aln.fa" >> ../$cur_date/log.txt
mafft ${locus}_nr.fa  >${locus}_aln.fa 

echo "`date`: exset=$(../Scripts/GetExcluded.pl ${locus}_aln.fa)" >> ../$cur_date/log.txt
exset=$(../Scripts/GetExcluded.pl ${locus}_aln.fa)

echo "`date`: trimal -in ${locus}_aln.fa  -phylip -select $exset >${locus}.phy" >> ../$cur_date/log.txt
trimal -in ${locus}_aln.fa  -phylip -select $exset >${locus}.phy

echo "`date`: phyml --quiet --no_memory_check -i ${locus}.phy" >> ../$cur_date/log.txt
phyml  --quiet --no_memory_check -i ${locus}.phy

echo "`date`: mv ${locus}.phy_phyml_tree.txt ${locus}.nwk" >> ../$cur_date/log.txt
mv ${locus}.phy_phyml_tree.txt ${locus}.nwk

echo "`date`: ../Scripts/FormatTree.py -t ${locus}.nwk -l $locus -d $cur_date} -o ${locus}.svg" >> ../$cur_date/log.txt
../Scripts/FormatTree.py -t ${locus}.nwk -l $locus -d $cur_date} -o ${locus}.svg

echo "`date`: ../Scripts/FormatTree.py -t ${locus}.nwk -l $locus -d $cur_date} -o ${locus}.pdf" >> ../$cur_date/log.txt
../Scripts/FormatTree.py -t ${locus}.nwk -l $locus -d $cur_date} -o ${locus}.pdf

#copy new files to current post folder
echo "`date`: cp ${locus}.pdf ${locus}.svg ${locus}.nwk ${locus}.phy ${locus}_aln.fa ${locus}_nr.fa ${locus}_new.fa ${locus}_metadata_new.txt ${locus}_new.gb ../$cur_date" >> ../$cur_date/log.txt
cp ${locus}.pdf ${locus}.svg ${locus}.nwk ${locus}.phy ${locus}_aln.fa ${locus}_groups.txt ${locus}_nr.fa ${locus}_new.fa ${locus}_metadata_new.txt ${locus}_new.gb ../$cur_date



