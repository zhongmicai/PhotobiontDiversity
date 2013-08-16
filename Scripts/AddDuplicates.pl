#!/usr/bin/perl -w

=head1 NAME

AddDuplicates.pl version 1, 5 August 2013

=head1 SYNOPSIS

cat duplicates.txt | AddDuplicates.pl matadata_file.txt >metadata_file2.txt

=head1 DESCRIPTION

This script will add duplicate entries for genbank sequences that represent multiple specimens with the same haplotype

duplicates.txt should be a tab delimited file with accession number in 1st column, host in
the 2nd and number of specimens represented in the third.

metadata_file should be a tab delimited file with accession number in 1st column and host
should be the second. All other columns are duplicated.

Accession numbers are appended with a period and a sequential three digit number 
=head2 NOTES

=head1 AUTHOR

 Heath E. O'Brien E<lt>heath.obrien-at-gmail-dot-comE<gt>

=cut
####################################################################################################use warnings;
use strict;

my $metadatafile = shift;
my %dups;
while (<>){
  chomp;
  my @fields = split(/ *\t */, $_);
  my @species_list;
  if ($dups{$fields[0]}) {
    @species_list = @{$dups{$fields[0]}};
  }
  for ( my $x = $fields[2]; $x > 0; $x -- ) {
    push(@species_list, $fields[1]);
  }
  $dups{$fields[0]} = \@species_list;
}

open(my $metadata, "<", $metadatafile);
while (<$metadata>){
  chomp;
  my @fields = split(/ *\t */, $_);
  if ( $dups{$fields[0]} and scalar(@{$dups{$fields[0]}}) > 1 ) {
    my $counter = 0;
    my $base = $fields[0];
    foreach (@{$dups{$fields[0]}}) {
      $fields[0] = join(".", ($base, sprintf("%03d", $counter)));
      $fields[1] = $_;
      $counter ++;
      print join("\t", @fields), "\n";
    }
  }
  else {
    print join("\t", @fields), "\n";
  }
}