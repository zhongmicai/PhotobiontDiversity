#!/usr/bin/perl -w
use warnings;
use strict;
use autodie qw(open close);

my $hostfilename = shift;

open(my $hostfile, "<", $hostfilename);

my %hosts;  # host species unless it is a group in which case it is the group name
my %group_info;  # counts of each species within a group (keyed by group name)
while (<$hostfile>) {
  chomp;
  my @fields = split(/ *\t\ */, $_);
  my $accession = $fields[0];
  my $group = $fields[1];
  my $in_tree = $fields[2];
  my $host;
  if ( $fields[4] ) { $host = $fields[4]; }
  else { $host = 'unknown'; }
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
    if ( $in_tree =~ /IN TREE/ ) { $hosts{$accession} = $group; } #representative sequence for a sequence type
  } 
}

my %groups; #groups names, keyed by accession
foreach my $accession (keys %hosts ) {
  my $group = $hosts{$accession};
  if ( $group_info{$group} ) {
    my @host_info;
    foreach my $host ( keys %{$group_info{$group} } ) {
      my $num_seq = $group_info{$group}{$host};
      foreach ( @host_info ) { 
        $_ =~ /(^\w+)/;
        if ( $host =~ /$1/ ) { $host =~ s/(^\w)\w*/$1./; last;}  #abbreviate genus names after first usage on branch (this will fail if there are multiple genera that start with the same letter)
      }
      push(@host_info, $host . '[' . $num_seq . ']');
    }
    $hosts{$accession} = join(" ", @host_info);
    $group_info{$accession} = $group;
  }
}


while (my $tree = <>){
  chomp($tree);
  foreach ( keys %hosts ) {
    if ( $group_info{$_} ) {
      $tree =~ s/$_/$group_info{$_} $hosts{$_}/;
    }
    else {
      $tree =~ s/$_/$_ $hosts{$_}/;
    }
  }
  print "$tree\n";
}