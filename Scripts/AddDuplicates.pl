use warnings;
use strict;

my $metadatafile = shift;
my %dups;
while (<>){
  chomp;
  my @fields = split(/ *\t */, $_);
  my @species_list;
  if ($dups{$fields[0]}) {
    @species_list = @{$dups{$fields[0]}};
  }
  for ( my $x = $fields[2]; $x > 0; $x -- ) {
    push(@species_list, $fields[1]);
  }
  $dups{$fields[0]} = \@species_list;
}

open(my $metadata, "<", $metadatafile);
while (<$metadata>){
  chomp;
  my @fields = split(/ *\t */, $_);
  if ( $dups{$fields[0]} and scalar(@{$dups{$fields[0]}}) > 1 ) {
    my $counter = 0;
    my $base = $fields[0];
    foreach (@{$dups{$fields[0]}}) {
      $fields[0] = join(".", ($base, sprintf("%03d", $counter)));
      $fields[1] = $_;
      $counter ++;
      print join("\t", @fields), "\n";
    }
  }
  else {
    print join("\t", @fields), "\n";
  }
}