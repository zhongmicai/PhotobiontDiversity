#!/usr/bin/perl -w
#use warnings;
use strict;
use Bio::SeqIO;

my $filename = shift;
my $locus = shift;
my $cur_date = shift;
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
    my $journal = $references->{'location'};
    my $pubmed = " ";
    if (exists $references->{'pubmed'} ) {
      $pubmed =  $references->{'pubmed'};
    }
    for my $feat_object ($seq->get_SeqFeatures('source')) {       #search first for 'host' tag
      if ( $feat_object->has_tag('host') ) {
        my @values = $feat_object->get_tag_values('host');
        $voucher = $values[0];
        if ( $voucher =~ /^lichen specimen voucher/ ) { $voucher = ''; }  #host species not in value
      }
      if ( $feat_object->has_tag('country') ) {
        my @values = $feat_object->get_tag_values('country');
        $location = $values[0];
      }
      unless ( $voucher ) {
        if ( $feat_object->has_tag('note') and $feat_object->primary_tag =~ /source/i) {
          my @values = $feat_object->get_tag_values('note');
          $voucher = $values[0];
          if ( $voucher =~ /^lichen specimen voucher/ ) { $voucher = ''; }  #host species not in value
        }
      }
      unless ( $voucher ) {
        if ( $feat_object->has_tag('isolation_source') ) {
          my @values = $feat_object->get_tag_values('isolation_source');
          $voucher = $values[0];
          if ( $voucher =~ /^lichen specimen voucher/ ) { $voucher = ''; }  #host species not in value
        }
      }
      if ( $feat_object->has_tag('organism') ) {
        my @values = $feat_object->get_tag_values('organism');
        $species = $values[0];
        #print STDERR "Species: $species\n";
      }
      if ( $feat_object->has_tag('strain') ) {
        my @values = $feat_object->get_tag_values('strain');
        $strain = $values[0];
      }
    }
    if ( $species ) {
      unless ( $voucher ) {
        if ( $species =~ /(cyanobiont)|(phycobiont)|(photobiont)|(symbiont)/ ) { 
          if  ($species =~ /(\w+ sp.) '?cf\. (.*)/){
            $species = $1;
            $voucher = $2;
          }
          else {
            $voucher = $species;
        
          }
        }
        #print STDERR "Voucher: $voucher\n";
      }
      if ( $species =~ /(var.)|(subsp.)|(cf.)/ ) {
        $species =~ s/(\w+.? +\w+.? +\w+.? +\w+.?).*/$1/;
      }
      else {
        $species =~ s/(\w+.? +\w+.?).*/$1/;
      }
    }
    if ($voucher) {
      $voucher =~ s/(genotype:.*)|(authority:)//i;
      $voucher =~ s/(\bthall[ui]s?\b)|\b(lichen(ized)?)\b|\b(from)\b|\b(with)\b|\b(photobiont)\b|\b(phycobiont)\b|\b(cyanobiont)\b|\b(of)\b|\b(primary thallus)\b|\b(isolated)\b|\b(cultured)\b|\b(the)\b|\b(sandstone)\b|\b(microbial)\b|\b(biofilm)\b|\b(glacier)\b|\b(forefield)\b|\b(authority)\b|\b(\b(jan)\b|\b(febr)\buary)\b|\b(march)\b|\b(april)\b|\b(may)\b|\b(june)\b|\b(july)\b|\b(august)\b|\b((sept)|(octo)|(novem)|(decem)ber)\b|\d+\b//gi;
      $voucher =~ s/[,']//g;
      $voucher =~ s/ +/ /g;
      #print STDERR "Voucher: $voucher\n";
      #print STDERR "Species: $species\n";      
      foreach ( split(/ /, $species) ) {$voucher =~ s/$_//; }
      if ( $voucher =~ /(\S+ \S*)/ ) {
        $host = $1;
        #print STDERR "Host: $host\n";
        if ( $voucher =~ s/( var. \S+)// ) { $host .= $1; }
        if ( $voucher =~ s/( subsp. \S+)// ) { $host .= $1; }
        if ( $voucher =~ s/cf.( \S+)// ) { $host .= $1; }
        #if ( $voucher =~ s/( sp.)// ) { $host .= $1; }
        $host =~ s/\s+$//;
        $host =~ s/Trebouxia//gi;
        $host =~ s/;//;
        if ($host =~ /^\w+$/) { $host .= ' sp.'; }
        #print STDERR "$host\n";
      }
    }
    unless ( $host ) { $host = " "; }
    $species =~ s/uncultured\s+//;
    $species =~ s/((Trebouxia)|(Asterochloris)) photobiont/$1 sp./;
    $species =~ s/((Trebouxia)|(Asterochloris)) sp\..*/$1 sp./;
    print join("\t", ($accession, $host, $species, $strain, $location, $authors[0], $journal, $pubmed, $locus, $cur_date)), "\n";
  }
}