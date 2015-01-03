dataset=$1
ref_seq=$2 # Ncommune_rbcX.fa

usearch -cluster_fast ${dataset}.fa -id 1 -centroids ${dataset}_nr.fa -uc ${dataset}_groups.txt


#create alignment and make tree, mapping on metadata
#echo "`date`: mafft ${dataset}_nr.fa  >${dataset}_aln.fa" >> ../$cur_date/log.txt
mafft ${dataset}_nr.fa  >${dataset}_aln.fa 

#echo "`date`: exset = ../Scripts/GetExcluded.pl ${dataset}_aln.fa" >> ../$cur_date/log.txt
exset=$(../Scripts/GetExcluded.pl ${dataset}_aln.fa)

#echo "`date`: trimal -in ${dataset}_aln.fa  -phylip -select $exset >${dataset}.phy" >> ../$cur_date/log.txt
trimal -in ${dataset}_aln.fa  -phylip -select $exset >${dataset}.phy

#echo "`date`: phyml --quiet --no_memory_check -i ${dataset}.phy" >> ../$cur_date/log.txt
phyml  --quiet --no_memory_check -i ${dataset}.phy
