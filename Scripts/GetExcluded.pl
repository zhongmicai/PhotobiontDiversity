#!/usr/bin/perl -w
use warnings;
use strict;
use Bio::SeqIO;

# print the gap intervals of a designated sequence to act as an exclusion set for trimming
# using trimal

#Usage: GetExcluded.pl infile |pbcopy
#       trimal -in alignment_file -phylip -select `pbpaste` > outfile

my $infilename = shift;

my $infile = Bio::SeqIO->new('-file' => $infilename,
         '-format' => 'fasta') or die "could not open seq file $infilename\n";

while ( my $seq_obj = $infile->next_seq ) {
  if ( $seq_obj->display_id eq 'AJ632030'    # Nostoc rbcX ref seq
       or $seq_obj->display_id eq 'JQ993758' # Trebouxia ITS ref seq
       or $seq_obj->display_id eq 'AF345436' # Asterochloris ITS ref seq
       or $seq_obj->display_id eq 'AY293964' # Coccomyxa ITS ref seq
       or $seq_obj->display_id eq 'JQ617958' # Trentepohlia ITS ref seq 
       or $seq_obj->display_id eq 'FJ534625' # Trentepohlia rbcL ref seq
       or $seq_obj->display_id eq 'JQ007778' # Cyanobacteria 16S ref seq
      ) {
    my $seq = $seq_obj->seq;
    my @starts;
    my @ends;
    for ( my $x = 0; $x <length($seq); $x ++ ) {
      if ( substr($seq, $x, 1) =~ /-/ ) {
        if ( @ends and $x == $ends[-1] + 1 ) { $ends[-1] = $x; }
        else { 
          push(@starts, $x);
          push (@ends, $x);
        }
      }
    }
    my @intervals;
    foreach my $start ( @starts ) {
      my $end = shift(@ends);
      if ( $start == $end ) { push(@intervals, $start); }
      else { push(@intervals, "$start-$end"); }
    }
    print "{ ", join(",", @intervals), " }";
    last;
  }
}
