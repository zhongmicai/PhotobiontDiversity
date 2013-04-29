#!/usr/bin/perl -w
use warnings;
use strict;
use autodie qw(open close);

my $groupfilename = shift;
my $treefilename = shift;
open(my $treefile, "<", $treefilename);

my $tree = <$treefile>;
open(my $groupfile, "<", $groupfilename);

my %groups;
my %group_size;

#First pass through groups to determine size so that ambiguous sequences can be assigned to the largest group
while (<$groupfile>) {
  chomp;
  $_ =~ s/,\s+$//;   #remove trailing comma and whitespace
  $_ =~ s/(Group \d+) : //;
  my $group = $1;
  $group_size{$group} = scalar(split(/,\s*/, $_));
  $groups{$group} = $_;
}
my %membership;
foreach my $group ( keys %groups ) {
  my @seqs = split(/,\s*/, $groups{$group});
  foreach(@seqs) { 
    unless ( $membership{$_} and $group_size{$membership{$_}} > $group_size{$group}) { 
      $membership{$_} = $group;
    }
  }
}

while (<>){
  chomp;
  if ( $_ =~ /^\s*$/ ) {next; }
  (my $accession, my $host) = split(/\s*\t\s*/, $_);
  my $group;
  if ( $membership{$accession} ) { $group = $membership{$accession}; }
  else { $group = "UNIQUE"; }
  my $in_tree;
  if ( $tree =~ /$accession/ ) { $in_tree = "IN TREE"; }
  else { $in_tree = "REDUNDANT"; }
  print join("\t", ($accession, $group, $in_tree, $host)), "\n";	 
}
