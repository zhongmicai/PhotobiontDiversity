#!/usr/bin/perl -w

# A little script to make sure that all sequence within a range of accession numbers are in a file

#Usage: grep "prefix" metadatafile | cut -f1 | sort | CheckNumbers.pl start end

use warnings;
use strict;

my $start = shift;
my $end = shift;
$start =~ s/([A-Z]+)//;
my $prefix = $1;
$end =~ s/([A-Z]+)//;
unless ($1 eq $prefix ) { die "Cannot expand accession numbers with different letters\n"; }

my %seqs;
while (<>) {
  $_ =~ /(\w+)/;
  $seqs{$1} = 1;
}

print "Missing Seqs:\n";
for ( my $x = $start; $x <= $end; $x ++ ) {
  my $accn = $prefix . $x;
  unless ( $seqs{$accn} ) { print "$accn, "; }
}
print "\n";