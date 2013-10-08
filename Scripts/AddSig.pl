#!/usr/bin/perl -w

#convert branch support values to binary (<.9 = 0; >=.9 = 1)
#These significant branches can be visualised with FigTree as node shapes

#Usage: AddSig.pl treefile >outfile

use warnings;
use strict;
use autodie qw(open close);


my $treefilename = shift;

open(my $treefile, "<", $treefilename );
while (my $line = <$treefile>){
  chomp($line);
  $line = AddSig($line);
  print "$line\n";
}
 
sub AddSig {
  my $line = shift;
  my @segments;
  foreach (split(/,/, $line)) {
    my @subsegments;
    foreach (split(/\)/, $_)) {
      my @branch = split(/:/, $_);
      if ( $branch[0] =~ /[01]\.\d+/ ) {
        if ( $branch[0] >= 0.9 ) { $branch[0] = 1; }
        else { $branch[0] = 0; }
      }
      push(@subsegments, join(':', @branch));
    }
    push(@segments, join(')', @subsegments));
  }
  return join(',', @segments);
}
