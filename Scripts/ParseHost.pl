#!/usr/bin/perl -w
#use warnings;
use strict;
use Bio::SeqIO;

my $filename = shift;

if ( $filename =~ /\.fa/ ) {
  open(my $infile, "<", $filename);
  while (<$infile>){
    chomp;
    if ( $_ =~ /gi\|\d+\|\w+\|(\w+)\.\d+\|[^']*'(\w+\s+[\w.() ]+)cyanobiont'/ ) { print "$1\t$2\n"; }
    elsif ( $_ =~ /^>/ ) { print STDERR "$_: could not parse host info\n"; }
  }
}

elsif ( $filename =~ /\.gb/ ) {
  my $host = ' ';
  my $accession = ' ';
  my $species = ' ';
  my $voucher = ' ';
  my $infile = Bio::SeqIO->new('-file' => $filename,
         '-format' => 'genbank') or die "could not open seq file $filename\n";
  while ( my $seq = $infile->next_seq ) {
    my $host;
    my $accession;
    my $species;
    my $voucher;
    $accession = $seq->accession;
    for my $feat_object ($seq->get_SeqFeatures('source')) {
      if ( $feat_object->has_tag('host') ) {
        my @values = $feat_object->get_tag_values('host');
        $voucher = $values[0];
      }
      unless ( $voucher ) {
        if ( $feat_object->has_tag('note') and $feat_object->primary_tag =~ /source/i) {
          my @values = $feat_object->get_tag_values('note');
          $voucher = $values[0];
        }
      }
      unless ( $voucher ) {
        if ( $feat_object->has_tag('isolation_source') ) {
          my @values = $feat_object->get_tag_values('isolation_source');
          $voucher = $values[0];
        }
      }
      if ( $feat_object->has_tag('organism') ) {
        my @values = $feat_object->get_tag_values('organism');
        $species = $values[0];
      }
    }
    if ($voucher) {
      $voucher =~ s/\b(lichen(ized)?)|(from)|(with)|(photobiont)|(of)|(cultured)|(the)|(sandstone)|(microbial)|(biofilm)|(glacier)|(forefield)\b//gi;
      $voucher =~ s/ +/ /g;
      if ( $voucher =~ s/(\S+ \S*)// ) {
        $host = $1;
        if ( $voucher =~ s/( var. \S+)// ) { $host .= $1; }
        if ( $voucher =~ s/( subsp. \S+)// ) { $host .= $1; }
        if ( $voucher =~ s/( sp. \S+)// ) { $host .= $1; }
        $host =~ s/;//;
      }
    }
    $species =~ s/uncultured\s+//;
    $species =~ s/Trebouxia photobiont/Trebouxia sp./;
    print join("\t", ($accession, $species, $host)), "\n";
  }
}