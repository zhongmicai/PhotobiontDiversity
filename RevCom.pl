#!/usr/bin/perl -w

=head1 NAME

RevCom.pl -v 1 25 April 2013

=head1 SYNOPSIS

cat BLASTTABLE | RevCom.pl SeqFile > Outfile

=head1 DESCRIPTION
Filter sequences on length. Can specify min size and/or max size.
Can specify files for input and/or output or use STDIN/STDOUT

=head2 NOTES

=head1 AUTHOR

 Heath E. O'Brien E<lt>heath.obrien-at-gmail-dot-comE<gt>

=cut
####################################################################################################


use strict;
use warnings;
use Bio::SeqIO;
use Getopt::Long;

my $infilename = shift;

my %revcom;
while (<>){
  my %result = % {ParseBlast($_)};
  if ( $result{'hit_strand'} == -1 ) { $revcom{$result{'hit_name'}} =1;}
}

my $seqin = Bio::SeqIO->new(
                            -file   => $infilename,
                            -format => 'fasta',
                            );

my $seqout = Bio::SeqIO->new(
                             -file => "| cat",
                             -format => 'Fasta',
                             );

while ( my $seq = $seqin->next_seq ) {
#  print get_id($seq->primary_id), "\n";
  if ( $revcom{get_id($seq->primary_id)} ) { $seq = $seq->revcom; }
$seqout->write_seq($seq);
}
  

sub get_id {
   my $header = shift;
   $header =~ /^gi\|\d+\|\w+\|(\w+)\.\d\|/;
   $1;
}

sub ParseBlast {
  my $line = shift;
  chomp($line);
  my @fields = split(/\s+/, $line);
  #print scalar(@fields), "\n";
  my %tags;
  $tags{'query_name'} = shift(@fields);
  $tags{'query_length'} = shift(@fields);
  unless ( $tags{'query_length'} =~ /^\d+$/ ) { die "query_length $tags{'query_length'} not recognized. Must be a positive integer\n"; }
  $tags{'hit_name'} = shift(@fields);
  $tags{'hit_length'} = shift(@fields);
  unless ( $tags{'hit_length'} =~ /^\d+$/ ) { die "hit_length $tags{'hit_length'} not recognized. Must be a positive integer\n"; }
  $tags{'percent'} = shift(@fields);
  unless ( $tags{'percent'} =~ /^[+-]?(?=\.?\d)\d*\.?\d*(?:e[+-]?\d+)?\z/i ) { die "percent $tags{'percent'} not recognized. Must be a number\n"; }
  $tags{'aln_length'} = shift(@fields);
  unless ( $tags{'aln_length'} =~ /^\d+$/ ) { die "aln_length $tags{'aln_length'} not recognized. Must be a positive integer\n"; }
  $tags{'mismatch'} = shift(@fields);
  unless ( $tags{'mismatch'} =~ /^\d+$/ ) { die "mismatch count $tags{'mismatch'} not recognized. Must be a positive integer\n"; }
  $tags{'gap'} = shift(@fields);
  unless ( $tags{'gap'} =~ /^\d+$/ ) { die "gap count $tags{'gap'} not recognized. Must be a positive integer\n"; }
  my $start = shift(@fields);
  unless ( $start =~ /^\d+$/ ) { die "query start $start not recognized. Must be a positive integer\n"; }
  my $end = shift(@fields);
  unless ( $end =~ /^\d+$/ ) { die "query end $end not recognized. Must be a positive integer\n"; }
  if ( $start > $end ) {
    $tags{'query_strand'} = -1;
    $tags{'query_start'} = $end;
    $tags{'query_end'} = $start;
  }  
  else {
    $tags{'query_strand'} = 1;
    $tags{'query_start'} = $start;
    $tags{'query_end'} = $end;
  }
  $tags{'query_frame'} = shift(@fields);
  unless ( $tags{'query_frame'} =~ /^\-?[0123]$/ ) { die "query_frame $tags{'query_frame'} not recognized. Must be 1, 2, 3, -1, -2, -3 or 0\n"; }
  $start = shift(@fields);
  unless ( $start =~ /^\d+$/ ) { die "hit start $start not recognized. Must be a positive integer\n"; }
  $end = shift(@fields);
  unless ( $end =~ /^\d+$/ ) { die "hit end $end not recognized. Must be a positive integer\n"; }
  if ( $start > $end ) {
    $tags{'hit_strand'} = -1;
    $tags{'hit_start'} = $end;
    $tags{'hit_end'} = $start;
  }  
  else {
    $tags{'hit_strand'} = 1;
    $tags{'hit_start'} = $start;
    $tags{'hit_end'} = $end;
  }
  $tags{'hit_frame'} = shift(@fields);
  unless ( $tags{'hit_frame'} =~ /^\-?[0123]$/ ) { die "hit_frame $tags{'hit_frame'} not recognized. Must be 1, 2, 3, -1, -2, -3 or 0\n"; }
  $tags{'evalue'} = shift(@fields);
  unless ( $tags{'evalue'} =~ /^[+-]?(?=\.?\d)\d*\.?\d*(?:e[+-]?\d+)?\z/i ) { die "Evalue $tags{'evalue'} not recognized. Must be a number\n"; }
  $tags{'score'} = shift(@fields);
  unless ( $tags{'score'} =~ /^[+-]?(?=\.?\d)\d*\.?\d*(?:e[+-]?\d+)?\z/i ) { die "score $tags{'score'} not recognized. Must be a number\n"; }
  return \%tags;
}
