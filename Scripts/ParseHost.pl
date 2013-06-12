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
  my $infile = Bio::SeqIO->new('-file' => $filename,
         '-format' => 'genbank') or die "could not open seq file $filename\n";
  while ( my $seq = $infile->next_seq ) {
    my $host;
    my $accession;
    my $species;
    my $voucher;
    my $location = " ";
    my $strain = ' ';
    $accession = $seq->accession;
    my $anno_col = $seq->annotation;
    my $references = ($seq->annotation->get_Annotations('reference'))[0];
    my @authors = split(/,/,$references->{'authors'});
    my $journal = $references->{'location'}, "\n";
    my $pubmed = " ";
    if (exists $references->{'pubmed'} ) {
      $pubmed =  $references->{'pubmed'};
    }
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
      if ( $feat_object->has_tag('strain') ) {
        my @values = $feat_object->get_tag_values('strain');
        $strain = $values[0];
      }
    }
    if ($voucher) {
      $voucher =~ s/(genotype:.*)|(authority:)//i;
      $voucher =~ s/\b(lichen(ized)?)|(from)|(with)|(photobiont)|(phycobiont)|(of)|(primary thallus)|(isolated)|(cultured)|(the)|(sandstone)|(microbial)|(biofilm)|(glacier)|(forefield)|(authority)|((jan)|(febr)uary)|(march)|(april)|(may)|(june)|(july)|(august)|((sept)|(octo)|(novem)|(decem)ber)|\d+\b//gi;
      $voucher =~ s/[,]//g;
      $voucher =~ s/ +/ /g;
      if ( $voucher =~ /(\S+ \S*)/ ) {
        $host = $1;
        foreach ( split(/ /, $species) ) {$host =~ s/$_//; }
        if ( $voucher =~ s/( var. \S+)// ) { $host .= $1; }
        if ( $voucher =~ s/( subsp. \S+)// ) { $host .= $1; }
        if ( $voucher =~ s/cf.( \S+)// ) { $host .= $1; }
        #if ( $voucher =~ s/( sp.)// ) { $host .= $1; }
        $host =~ s/Trebouxia//gi;
        $host =~ s/;//;
      }
    }
    unless ( $host ) { $host = " "; }
    $species =~ s/uncultured\s+//;
    $species =~ s/((Trebouxia)|(Asterochloris)) photobiont/$1 sp./;
    $species =~ s/((Trebouxia)|(Asterochloris)) sp\..*/$1 sp./;
    print join("\t", ($accession, $host, $species, $strain, $location, $authors[0], $journal, $pubmed)), "\n";
  }
}