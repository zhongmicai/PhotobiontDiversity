#!/usr/bin/perl -w

=head1 NAME

ConvertSeq.pl
2 Feb 2010, -modified 12 May 2010 to work on directories containing multiple files
modified 25 May 2011 to work with alignment files.

=head1 SYNOPSIS

ConvertSeqs.pl -i sequence file/folder -f outformat [options]

Options:
 -r replace existing outfile
 -x infile format (only necessary if it is not specified by the file extension)
 -o outfile name

=head1 DESCRIPTION
A simple script to convert sequence formats. 

If multiple sequences are present, each is written to the outfile

Infile can also be a directory, in which case all files in directory are used as input


=head2 NOTES

=head1 AUTHOR

 Heath E. O'Brien E<lt>heath.obrien-at-gmail-dot-comE<gt>

=cut
####################################################################################################


use strict;
use warnings;
use Bio::SeqIO;
use Bio::AlignIO;
use Bio::Align::Utilities qw(:all);
use File::Basename;
use Getopt::Long;
use List::Util qw(min max);

my $infilename;
my $outfilename;
my $informat;
my $outformat;
my $help = 0;
my $replace = 0;

GetOptions(
'infile:s' => \$infilename,
'format:s' => \$outformat,
'replace' => \$replace,
'xformat:s' => \$informat,
'outfile:s' => \$outfilename,
'help|?' => \$help,
);

my $usage = "type perldoc ConvertSeq.pl for help\n";
if( $help ) {
    die $usage;
}

$infilename = shift unless $infilename;

$outformat = shift unless $outformat;
unless ($outformat) { die "output format not specified\n$usage";}
if ( $outformat eq "aln" ){ $outformat = "clustalw"; }
if ( $outformat eq "aln" ){ $outformat = "clustalw"; }
if ( $outformat eq "phy" ){ $outformat = "phylip"; }

####################################################################################################

(my $name, my $path, my $in_ext) = fileparse($infilename, qr/\.[^.]*/);

unless ($informat) { $informat = GetFormat($in_ext);}
if ( $informat eq "aln" ){ $informat = "clustalw"; }
my $out_ext = GetExtension($outformat);
unless ( $outfilename ) { $outfilename = $path . $name . $out_ext; }
if (-f $outfilename ) { 
  if ($replace) { print `rm $outfilename\n`; }
  else {die "outfile $outfilename already exists!\n"; }
}
if ( -d $infilename ) {
  opendir(DIR, $infilename) or die "can't opendir $infilename: $!";
  while (defined(my $filename = readdir(DIR))) {
    if ($filename =~ /^\./) { next; }
    $filename = "$infilename/$filename";
    ConvertSeqs($filename, $outfilename, $informat, $outformat);
  }
}

else {
    ConvertSeqs($infilename, $outfilename, $informat, $outformat);
}
exit;

####################################################################################################
sub ConvertSeqs {
  my $in_name = shift;
  my $out_name = shift;
  my $in_format = shift;
  my $out_format = shift;
  #output individual sequences (fasta, fastq, genbank)
  if ( $out_format eq "fasta" or $out_format eq "fastq" or $out_format eq "genbank") {
    my $outfile = Bio::SeqIO->new(-file => ">>$out_name" ,
                             '-format' => $out_format);
    #input individual sequences                         
    if ( $in_format eq "fasta" or $in_format eq "fastq" or $in_format eq "genbank") {
      my $infile = new Bio::SeqIO(-format => $in_format, 
                           -file   => $in_name);
      while ( my $seq = $infile->next_seq() ) {
        $outfile->write_seq($seq);
      }
    }
    #input alignments (separate into individual sequences)
    else {
      my $infile = Bio::AlignIO->new(-format => $in_format, 
                           -file   => $in_name);
      while (my $aln = $infile->next_aln) {
        WriteSeqs($aln);
      } 
    } 
  }
#output is an alignment (input must be an alignment or snp table)
  else {
    my $infile = Bio::AlignIO->new(-format => $in_format, 
                           -file   => $in_name);
    my $outfile;
    if ( $out_format =~ /phy/i and $out_format =~ /ext/i ) { open(OUT, ">>$out_name"); } #extended phylip format
    elsif ( $out_format =~ /mega/i ) { open(OUT, ">>$out_name"); } #mega format
    else {  $outfile = Bio::AlignIO->new(-file => ">>$out_name",
                                        '-format' => $out_format);}
    while ( my $aln = $infile->next_aln ) {
      if ( $out_format =~ /phy/i and $out_format =~ /ext/i ) { WritePhylipExt($aln); }
      elsif ( $out_format =~ /mega/i ) { WriteMega($aln, $out_name); }
      else { $outfile->write_aln($aln); }
    }
  }
}
######################################################################################
# IF ADDING NEW SEQUENCE FORMATS THEY MUST BE ADDED TO CONDITIONAL AT THE START OF THE ConvertSeqs SUBROUTINE
sub GetExtension {
  my $format = shift;
  if ( $format eq "fasta" ) { return ".fa";}
  elsif ( $format eq "xmfa" ) { return ".xmfa";}
  elsif ( $format eq "fastq" ) { return ".fq";}
  elsif ( $format =~ /phy/ ) { return ".phy";}
  elsif ( $format eq "clustalw" ) { return ".aln";}
  elsif ( $format eq "genbank" ) { return ".gbk";}
  elsif ( $format eq "nexus" ) { return ".nex";}
  elsif ( $format eq "mega" ) { return ".meg";}
  else {die "file format not recognized!";}
}

sub GetFormat {
  my $ext = shift;
  my $format;
  if ( $ext =~ /f.*q/i ) { $format = "fastq";}
  elsif ( $ext =~ /^\.fa*/i ) { $format = "fasta";}
  elsif ( $ext =~ /xmfa/i ) { $format = "xmfa";}
  elsif ( $ext =~ /phy/i ) { $format = "phylip";}
  elsif ( $ext =~ /aln/i ) { $format = "clustalw";}
  elsif ( $ext =~ /gb/i ) { $format =  "genbank";}
  elsif ( $ext =~ /nxs/i ) { $format =  "nexus";}
  elsif ( $ext =~ /nex/i ) { $format =  "nexus";}
  elsif ( $ext =~ /maf/i ) { $format =  "maf";}
  elsif ( $ext =~ /txt/i ) { $format = "snp_tbl";}
  else {die "extension not recognized!";}
  return $format;
}



sub WriteSeqs {
  my $aln = shift;
  my $outfile = Bio::SeqIO->new('-file' => ">$outfilename",
         '-format' => $outformat) or die "could not open seq file $outfilename\n\n$usage";
  foreach ( $aln->each_seq) {
    my $seq = $_;
    my $string = $seq->seq;
    #$string =~ s/-//g;
    $seq->seq($string);
    $outfile->write_seq($seq);
  }
}

sub WritePhylipExt {
  my $aln = shift;
  my @seqs = $aln->each_seq;
  print OUT scalar(@seqs), " ", length($seqs[0]->seq), "\n";
  my @name_lengths;
  foreach (@seqs) { push(@name_lengths, length($_->display_id)); }
  my $name_size = max(@name_lengths) + 1;
  if ( $name_size < 10 ) { $name_size = 10; }
  foreach (@seqs) {
    printf OUT "%-*s",  $name_size, $_->display_id;
    print OUT $_->seq, "\n";
  }
}


sub WriteMega {
  my $aln = shift;
  my $outfilename = shift;
  my @seqs = $aln->each_seq;
  print OUT "#Mega\n!Title $outfilename;\n\n";
  my @name_lengths;
  foreach (@seqs) { push(@name_lengths, length($_->display_id)); }
  my $name_size = max(@name_lengths) + 1;
  if ( $name_size < 10 ) { $name_size = 10; }
  foreach (@seqs) {
    printf OUT "#%-*s",  $name_size, $_->display_id;
    print OUT $_->seq, "\n";
  }
}

