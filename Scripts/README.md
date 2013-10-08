Photobiont Diversity Scripts
============================

_Scripts used to manipulate data files for PhotobiontDiversity.wordpress.com_

* AddDuplicates.pl - Adds duplicate entries to metadata file for sequences that represent multiple specimens
* AddMetadata.pl -add host or algal species information to taxon names in a tree file
* AddSig.pl -modify aLRT values to be either 1 (>=0.9) or 0 (<0.9)
* CheckNumbers.pl - A little script to make sure that all sequence within a range of accession numbers are in a file
* ColourTree.pl -color taxa in trees according to host taxonomy
* ConvertSeq.pl -convert among different sequence/alignment file formats
* CountAssociations.py -produces a matrix of host genus / algal clade assocaitions
* FilterSeq.pl -remove sequence longer/shorter than specified values(s)
* GetClades.py -adds metadata info about clade membership using hard-coded clade definitions
* GetExcluded.pl -print the gap intervals of a designated sequence to act as an exclusion set for trimming
* GetFasta.pl -use NCBI Eutils to fetch fasta formated sequences by accession number
* GetGB.pl -use NCBI Eutils to fetch genbank formated sequences by accession number
* GetGroups.py - Add information about redundant sequence groupings to metadata file
* GetRedundant.pl - Duplicates fasta sequence for every redundant accession number returned by blastdbcmd
* GetSeq.pl -index fasta file and retrieve specified sequence
* ParseHost.pl -Extract host information from a genbank file
* RevCom.pl -reverse complements sequences that have blast hit on negative strand
* trunc.pl -truncate sequence at specified coordinates

Depreciated Scripts
-------------------

_Contains scripts used in older posts that are no longer needed for my current workflow_

* RemoveRedundant.pl -remove redundant sequences from a sequence alignemnt
    - this is now done with uclust
* GetRef.py -Extract author and reference info from a genbank file
    - this is now done with ParseHost.pl