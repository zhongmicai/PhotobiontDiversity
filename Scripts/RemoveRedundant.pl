#!/usr/bin/perl -w
use warnings;
use strict;
use autodie qw(open close);
use Bio::SeqIO;

my $infilename = shift;

my %included;
while (<>) {
  chomp;
  if ( $_ =~ /^\s*$/ ) { next; }  #skip blank lines
  $_ =~ s/,\s+$//;   #remove trailing comma and whitespace
  my @fields = split(/\s*\t\s*/, $_);
  if ( $fields[2] =~ /IN TREE/ ) { $included{$fields[0]} = 1; }
}

my $infile = Bio::SeqIO->new('-file' => $infilename,
         '-format' => 'fasta') or die "could not open seq file $infilename\n";

my $outfile = Bio::SeqIO->new('-file' => "| cat",
         '-format' => 'fasta') or die "could not open seq file $infilename\n";

while ( my $seq = $infile->next_seq ) {
  if ( $included{$seq->display_id} ) { $outfile->write_seq($seq); }
}