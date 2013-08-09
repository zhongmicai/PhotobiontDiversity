#!/usr/bin/perl -w
use warnings;
use strict;
use Bio::SeqIO;

my $infilename = shift;

my $infile = Bio::SeqIO->new('-file' => $infilename,
         '-format' => 'fasta') or die "could not open seq file $infilename\n";

#AJ632030 is the template sequence for the Nostoc_rbcX dataset
#JQ993758 is the template sequence for the Trebouxia_ITS dataset
while ( my $seq_obj = $infile->next_seq ) {
  if ( $seq_obj->display_id eq 'AJ632030' or $seq_obj->display_id eq 'JQ993758' or 
       $seq_obj->display_id eq 'AF345436' or $seq_obj->display_id eq 'AY293964' or
       $seq_obj->display_id eq 'JQ617958' or $seq_obj->display_id eq 'FJ534625') {
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
