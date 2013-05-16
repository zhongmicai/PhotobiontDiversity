#!/usr/bin/perl -w
use warnings;
use strict;
use autodie qw(open close);

my $treefilename = shift;
my $groupfilename = shift;
open(my $treefile, "<", $treefilename);

my $tree = <$treefile>;

open(my $groupfile, "<", $groupfilename);

my %new_groups;
my %group_size;
#First pass through groups to determine size so that ambiguous sequences can be assigned to the largest group
while (<$groupfile>) {
  chomp;
  $_ =~ s/,\s+$//;   #remove trailing comma and whitespace
  $_ =~ s/(Group \d+) : //;
  my $group = 'New ' . $1;
  $group_size{$group} = scalar(split(/,\s*/, $_));
  #print "group: $group, accession $_\n";
  $new_groups{$group} = $_;
}

my %membership;
foreach my $group ( keys %new_groups ) {
  my @seqs = split(/,\s*/, $new_groups{$group});
  foreach(@seqs) { 
    unless ( $membership{$_} and $group_size{$membership{$_}} > $group_size{$group}) {
      $membership{$_} = $group;
    }
  }
}

my $max_group = 0;
my %new_names;
my @seqs;

#first pass through metadata file to determine highest group number
while (<>){
  chomp;
  if ( $_ =~ /^\s*$/ ) { next; }  #skip blank lines
  $_ =~ s/,\s+$//;   #remove trailing comma and whitespace
  push(@seqs, $_);
  my @fields = split(/\s*\t\s*/, $_);
  my $accession = $fields[0];
  if ( $fields[1] =~ /^Group (\d+)/ ) { #sequence is already assigned a group name.
    if ( $1 > $max_group ) { $max_group = $1; }
    if ( $membership{$accession} ) { 
      $new_names{$membership{$accession}} = $fields[1]; #seq was part of nr set, assign this name to all group members. 
    }
  }
}

foreach ( @seqs ) {
  my @fields = split(/ *\t */, $_);
  my $accession = $fields[0];
  if ( @fields < 4 ) { 
    splice(@fields, 1, 0, ('', ''));
  }
  if ( $membership{$accession} ) {
    unless ( $new_names{$membership{$accession}} ) {
      $max_group ++;
      $new_names{$membership{$accession}} = 'Group ' . $max_group;
    }
    $fields[1] = $new_names{$membership{$accession}};
  }
  unless ( $fields[1] ) { $fields[1] = "UNIQUE"; }
  if ( $tree =~ /$accession/ ) { $fields[2] = "IN TREE"; }
  else { $fields[2] = "REDUNDANT"; }
  print join("\t", @fields), "\n";
}
