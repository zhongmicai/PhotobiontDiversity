#!/usr/bin/perl -w

=head1 NAME

GetRedundant.pl -v 1 11 June 2013

=head1 SYNOPSIS

GetRedundant.pl -i seq_file -o out_file
cat seq_file | GetRedundant.pl > out_file

=head1 DESCRIPTION
Separate out redundant sequences from blastdbcmd

=head2 NOTES

When blastdbcmd is run on the nr database, redundant sequences are collapsed into a 
single representative (duh). Fasta headers for each redundant sequence are concatinated together

This script splits the fasta header on "\s>" and writes duplicate sequences to file for 
each result

=head1 AUTHOR

 Heath E. O'Brien E<lt>heath.obrien-at-gmail-dot-comE<gt>

=cut
####################################################################################################


use strict;
use warnings;
use Bio::SeqIO;
use Getopt::Long;

my $infilename = "cat |";
my $outfilename = "| cat";

#Getopt::Long::Configure ("bundling");
GetOptions(
'infile:s' => \$infilename,
'outfile:s' => \$outfilename,
);

unless ( $outfilename eq "| cat" ) { $outfilename = ">" . $outfilename; }

my $seqin = Bio::SeqIO->new(
                            -file   => $infilename,
                            -format => 'fasta',
                            );

my $seqout = Bio::SeqIO->new(
                             -file => $outfilename,
                             -format => 'Fasta',
                             );

while ( my $seq = $seqin->next_seq ) {
  my $header = join(" ", ($seq->id, $seq->desc));
#  print $header, "\n";
  foreach my $desc (split(/>/, $header)  ) {
    $desc =~ s/(gi\|\d+\|\w+\|\w+\.\d\|) //;
#    print "\n\n $_ \n\n";
    my $new_seq = Bio::Seq->new(-seq => $seq->seq,
                         -alphabet => 'dna',
                         -id => $1,
                         -desc  => $desc);

    $seqout->write_seq($new_seq);
  }
}
