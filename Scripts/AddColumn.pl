#!/usr/bin/perl -w

=head1 NAME

AddColumn.pl -v 1 14 May 2013

=head1 SYNOPSIS

cat file1 | AddColumn.pl -i file2 -a column_to_add [-r file1_reference_column -q file2_reference_column -o output_column ] > out_file 

=head1 DESCRIPTION
Parses tablular file and builds hash with query_column as keys 
and search column as values then looks up hash values by the keys in query_column of dataset 

=head2 OPTIONS

-i input file to add column from
-a column to add from input file [1-based no default ]
-r reference_column in file1 [1-based, default = 1 ]
-q reference_column in file 2 [1-based, default = 1 ]
-x allows you to specify what column the search_tag will be written to (by default it will be the last)

=head2 NOTES


=head1 AUTHOR

 Heath E. O'Brien E<lt>heath.obrien-at-gmail-dot-comE<gt>

=cut
####################################################################################################
use strict;
use warnings;
use Getopt::Long;
use autodie qw(open close);

my $infilename;
my $add_column;
my $file1_ref = 1;
my $file2_ref = 1;
my $output_column;

Getopt::Long::Configure ("bundling");
GetOptions(
'i|input|infile:s' => \$infilename,
'a|add:s' => \$add_column,
'r:s' => \$file1_ref,
'q:s' => \$file2_ref,
'o:s' => \$output_column
);
unless ( $add_column) { die "No add column specified\n"; }
$add_column --;
if ( $output_column ) { $output_column --; }
else { $output_column = 'NONE'; }
$file1_ref --;
$file2_ref --;


my $delimitor = "\t";

my %tags;
open(my $infile, "<", $infilename); 
while (<$infile>) {
  chomp;
  $_ =~ s/[" ]*([\t,])[" ]*/$1/g;
  $_ =~ s/(^[" ]*)([" ]*$)//g;
  my @fields = split($delimitor, $_);
  unless ( $fields[$file2_ref] ) { print STDERR "$fields[0]: no ", $file2_ref + 1, " column\n"; next; }
  unless ( $fields[$add_column] ) { print STDERR "$fields[0]: no ", $add_column + 1, " column\n"; next; }
  $tags{$fields[$file2_ref]} = $fields[$add_column];
} 

while (<>) {
  chomp;
  $_ =~ s/[" ]*([\t,])[" ]*/$1/g;
  $_ =~ s/(^[" ]*)|([" ]*$)//g;
  my @fields = split($delimitor, $_);
  if ( $output_column eq 'NONE' ) { $output_column = @fields; }
  if ( $tags{$fields[$file1_ref]} ) { splice(@fields, $output_column, 0, $tags{$fields[$file1_ref]}); }
  else { splice(@fields, $output_column, 0, 'NA'); }
  print join("\t", @fields), "\n";
}