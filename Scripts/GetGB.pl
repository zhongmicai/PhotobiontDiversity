#!/usr/bin/perl -w

=head1 NAME

GetGB.pl version 2, 25 April 2013

=head1 SYNOPSIS

cat INFILE | GetGB.pl OUTFILE EMAIL 

=head1 DESCRIPTION

This will accept a accession numbers from STDIN and retrieves GenBank formated sequences

Sequences are written to OUTFILE

Options:

=head2 NOTES

=head1 AUTHOR

 Heath E. O'Brien E<lt>heath.obrien-at-gmail-dot-comE<gt>

=cut
####################################################################################################
use warnings;
use strict;
use Bio::DB::EUtilities;

my $file = shift;
my $email = shift;
my @ids;
while (<>) {
  chomp;
  push (@ids, $_);
  if ( @ids == 100 ) { 
    my $factory = Bio::DB::EUtilities->new(-eutil   => 'efetch',
                                           -db      => 'nucleotide',
                                           -rettype => 'gb',
                                           -email   => $email,
                                           -id      => \@ids);
 
 
    $factory->get_Response(-file => "temp");
    @ids = ();
    print `cat temp >> $file`;
  }
}

if ( @ids ) {
  my $factory = Bio::DB::EUtilities->new(-eutil   => 'efetch',
                                         -db      => 'nucleotide',
                                         -rettype => 'gb',
                                         -email   => $email,
                                         -id      => \@ids);
 
 
  $factory->get_Response(-file => "temp");
  print `cat temp >> $file`;
}
print `rm temp`
