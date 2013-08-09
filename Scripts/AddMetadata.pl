#!/usr/bin/perl -w
#Add algal taxonomy info (species) or host info from metadata file to phylogeny.
use warnings;
use strict;
use autodie qw(open close);

my $hostfilename = shift;
my $stat = shift;
my $column;
if ( $stat =~ /species/i ) { $column = 4; }
elsif ( $stat =~ /host/i ) { $column = 3; }
else { die "$stat not recognized. Please specify either 'host' or 'species'\n"; }

open(my $hostfile, "<", $hostfilename);

my %hosts;  # host species unless it is a group in which case it is the group name
my %group_info;  # counts of each species within a group (keyed by group name)
my %group_reps;
while (<$hostfile>) {
  chomp;
  my @fields = split(/ *\t\ */, $_);
  my $accession = $fields[0];
  $accession =~ s/\.000//;
  my $group = $fields[1];
  my $in_tree = $fields[2];
  my $host;
  if ( $fields[$column] ) { $host = $fields[$column]; }
  else { $host = 'unknown'; }
  if ( $host =~ /free[- ]living/i and $fields[4] ) { $host = $fields[4]; } #use species name for free-living strains
  if ( $group =~ /UNIQUE/ ) {    #Add host info for unique sequences
    $hosts{$accession} = $host;
  }
  else {
    if ( $group_info{$group}{$host} ) {  #increment the counter for this host species for this sequence type
    $group_info{$group}{$host} ++;
    }
    else {
      $group_info{$group}{$host} = 1;   #initialize counter for new species/sequence type combination
    }
    if ( $in_tree =~ /IN TREE/ ) {  #representative sequence for a sequence type
      if ( $group_reps{$group} ) {  warn "multiple representatives of $group in tree\n"; }
      else { 
        $hosts{$accession} = $group;
        $group_reps{$group} = $accession;
      }
    }
  } 
}

foreach my $group (sort keys %group_info ) {
  my @host_info;
  foreach my $host ( keys %{$group_info{$group} } ) {
    my $num_seq = $group_info{$group}{$host};
    foreach ( @host_info ) { 
      $_ =~ /(^\w+)/;
      if ( $host =~ /$1/ ) { $host =~ s/(^\w)\w*/$1./; last;}  #abbreviate genus names after first usage on branch (this will fail if there are multiple genera that start with the same letter)
    }
    push(@host_info, $host . '[' . $num_seq . ']');
  }
  if ( $group_reps{$group} ) {
    $hosts{$group_reps{$group}} = join(" ", @host_info);       # modify host info to contain info about all hosts/species
  }
  else { warn "no representatives sequences in tree for $group\n"; }
}


while (my $tree = <>){
  chomp($tree);
  foreach ( keys %hosts ) {
    unless ( $tree =~ s/$_/$_ $hosts{$_}/ ) { warn "Sequences $_ not in tree\n"; }
  }
  foreach (keys %group_reps ) {
    unless ( $tree =~ s/$group_reps{$_}/$_/ ) { warn "Representaitve seq for $_ not in tree\n"; }   
  } 
  print "$tree\n";
}