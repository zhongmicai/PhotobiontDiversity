#!/usr/bin/perl -w
use warnings;
use strict;
use autodie qw(open close);

my %lichen_colors = ('Collema', '#ED99AA',
            'Degelia', '#D5A971',
            'Leptogium', '#9DBB6A',
            'Lobaria', '#4BC49E',
            'Massalongia', '#45C0D4',
            'Nephroma', '#A7ACEC',
            'Pannaria', '#E298D6',
            'Parmeliella', '#890939',
            'Peltigera', '#6A3B00',
            'Protopannaria', '#285000',
            'Pseudocyphellaria', '#005C26',
            'Stereocaulon', '#-3381505', #lavender
            'Sticta', '#28399F',
            'Cladonia', '#-16777088', #midnight
            'Cladina', '#-16777088', #midnight
            'Cladia', '#-16776961', #blueberry
            'Pilophorus', '#-10040065', #sky
            'Varicellaria', '#-16711936', #spring
            'Lepraria', '#-65536', #maraschino
            'Anzina', '#-16744448', #clover
            'Diploschistes', '#-32768', #tangarine
            );

my %family = ('Collema', 'Collemataceae',
            'Degelia', 'Pannariaceae',
            'Fuscopannaria', 'Pannariaceae',
            'Leptogium', 'Collemataceae',
            'Lobaria', 'Lobariaceae',
            'Massalongia', 'unknown',
            'Nephroma', 'Nephromataceae',
            'Pannaria', 'Pannariaceae',
            'Parmeliella', 'Pannariaceae',
            'Peltigera', 'Peltigeraceae',
            'Protopannaria', 'Pannariaceae',
            'Pseudocyphellaria', 'Lobariaceae',
            'Stereocaulon', 'Stereocaulaceae',
            'Sticta', 'Lobariaceae',
            'Acanthothecis', 'Graphidaceae',
            'Acarospora', 'Acarosporaceae',
            'Acrocordia', 'Dothideomycetes incertae sedis',
            'Amandinea', 'Caliciaceae',
            'Anaptychia', 'Physciaceae',
            'Anthracothecium', 'Pyrenulaceae',
            'Arthonia', 'Arthoniaceae',
            'Aspicilia', 'Megasporaceae',
            'Astrothelium', 'Trypetheliaceae',
            'Austrolecia', 'Catillariaceae',
            'Boreoplaca', 'Ramalinaceae',
            'Buellia', 'Caliciaceae',
            'Caloplaca', 'Teloschistaceae',
            'Candelaria', 'Candelariaceae',
            'Carbonea', 'Lecanoraceae',
            'Cetraria', 'Parmeliaceae',
            'Chaenotheca', 'Coniocybaceae',
            'Coenogonium', 'Coenogoniaceae',
            'Cryptothelium', 'Trypetheliaceae',
            'Cryptothecia', 'Arthoniaceae',
            'Cystocoleus', 'Dothideomycetes incertae sedis',
            'Dendrographa', 'Roccellaceae',
            'Dichosporidium', 'Roccellaceae',
            'Dimelaena', 'Caliciaceae',
            'Dimerella', 'Coenogoniaceae',
            'Diorygma', 'Graphidaceae',
            'Diploschistes', 'Graphidaceae',
            'Evernia', 'Parmeliaceae',
            'Everniastrum', 'Parmeliaceae',
            'Flavocetraria', 'Parmeliaceae',
            'Flavoparmelia', 'Parmeliaceae',
            'Fulgensia', 'Teloschistaceae',
            'Glyphis', 'Graphidaceae',
            'Graphis', 'Graphidaceae',
            'Gyalecta', 'Gyalectaceae',
            'Hemithecium', 'Graphidaceae',
            'Herpothallon', 'Arthoniales incertae sedis',
            'Huea', 'Teloschistaceae',
            'Hypogymnia', 'Parmeliaceae',
            'Imshaugia', 'Parmeliaceae',
            'Lasallia', 'Umbilicariaceae',
            'Laurera', 'Trypetheliaceae',
            'Lecanora', 'Lecanoraceae',
            'Lecidea', 'Lecideaceae',
            'Lecidella', 'Lecanoraceae',
            'Leiorreuma', 'Graphidaceae',
            'Lepraria', 'Stereocaulaceae',
            'Letharia', 'Parmeliaceae',
            'Lotrema', 'Graphidaceae',
            'Rimularia', 'Agyriaceae',
            'Melanelia', 'Parmeliaceae',
            'Mycoporum', 'Arthopyreniaceae',
            'Myriotrema', 'Graphidaceae',
            'Opegrapha', 'Opegraphaceae',
            'Parmelia', 'Parmeliaceae',
            'Parmelina', 'Parmeliaceae',
            'Parmotrema', 'Parmeliaceae',
            'Phaeographis', 'Graphidaceae',
            'Phaeophyscia', 'Physciaceae',
            'Physcia', 'Physciaceae',
            'Physconia', 'Physciaceae',
            'Pleurosticta', 'Parmeliaceae',
            'Polysporina', 'Acarosporaceae',
            'Porina', 'Porinaceae',
            'Protoparmeliopsis','',
            'Pseudevernia', 'Parmeliaceae',
            'Punctelia', 'Parmeliaceae',
            'Ramalina', 'Ramalinaceae',
            'Pyrenula', 'Pyrenulaceae',
            'Racodium', 'Dothideomycetes incertae sedis',
            'Rhizoplaca', 'Lecanoraceae',
            'Rimularia', 'Agyriaceae',
            'Rinodina', 'Physciaceae',
            'Rinodinella', 'Physciaceae',
            'Roccella', 'Roccellaceae',
            'Sarcographa', 'Graphidaceae',
            'Sarcogyne', 'Acarosporaceae',
            'Schaereria', 'Agyriaceae',
            'Seirophora', 'Teloschistaceae',
            'Strigula', 'Strigulaceae',
            'Teloschistes', 'Teloschistaceae',
            'Tephromela', 'Tephromelataceae',
            'Thalloloma', 'Graphidaceae',
            'Thamnolia', 'Icmadophilaceae',
            'Thelotrema', 'Graphidaceae',
            'Toninia', 'Ramalinaceae',
            'Trypethelium', 'Trypetheliaceae',
            'Tuckermannopsis', 'Parmeliaceae',
            'Umbilicaria', 'Umbilicariaceae',
            'Usnea', 'Parmeliaceae',
            'Xanthomendoza', 'Teloschistaceae',
            'Xanthoparmelia', 'Parmeliaceae',
            'Xanthoria', 'Teloschistaceae',
            'Anthoceros', 'Plant',
            'Blasia', 'Plant',
            'Cylindrospermum', 'Free-living',
            'Encephalartos', 'Plant',
            'Geosiphon', 'Other',
            'Macrozamia', 'Plant',
            'Nostoc', 'Free-living',
            'Gunnera', 'Plant',
            'Cycas', 'Plant'
            );
            
my %orders = ('Arthoniaceae', 'Arthoniales',
              'Arthopyreniaceae', 'Pleosporales',
              'Acarosporaceae', 'Acarosporales',
              'Agyriaceae', 'Agyriales', 
              'Arthoniales incertae sedis', 'Arthoniales',
              'Caliciaceae', 'Caliciales', 
              'Candelariaceae', 'Candelariales',
              'Catillariaceae', 'Lecanorales',
              'Coenogoniaceae', 'Ostropales',
              'Collemataceae', 'Peltigerales',
              'Coniocybaceae', 'Coniocybales',
              'Dothideomycetes incertae sedis', 'Dothideomycetes incertae sedis',
              'Graphidaceae', 'Ostropales',
              'Gyalectaceae', 'Ostropales',
              'Icmadophilaceae', 'Pertusariales',
              'Lecanoraceae', 'Lecanorales',
              'Lecideaceae', 'Lecanorales',
              'Lobariaceae', 'Peltigerales',
              'Massalongia', 'Peltigerales',
              'Megasporaceae', 'Pertusariales',
              'Nephromataceae', 'Peltigerales',
              'Opegraphaceae', 'Arthoniales',
              'Pannariaceae', 'Peltigerales',
              'Parmeliaceae', 'Lecanorales',
              'Peltigeraceae', 'Peltigerales',
              'Porinaceae', 'Ostropales',
              'Physciaceae', 'Caliciales',
              'Pyrenulaceae', 'Pyrenulales',
              'Ramalinaceae', 'Lecanorales',
              'Roccellaceae', 'Arthoniales',
              'Stereocaulaceae', 'Lecanorales',
              'Strigulaceae', 'Dothideomycetes incertae sedis',
              'Teloschistaceae', 'Teloschistales',
              'Tephromelataceae', 'Lecanorales',
              'Trypetheliaceae', 'Trypetheliales',
              'Umbilicariaceae', 'Umbilicariales'
         );

my %subclass = ('Acarosporales', 'Acarosporomycetidae',
              'Agyriales', 'Ostropomycetidae',
              'Arthoniales', 'Arthoniomycetes',
              'Caliciales', 'Lecanoromycetidae',
              'Candelariales', 'Lecanoromycetes incertae sedis',
              'Dothideomycetes incertae sedis', 'Dothideomycetes incertae sedis',
              'Lecanorales', 'Lecanoromycetidae',
              'Peltigerales', 'Lecanoromycetidae',
              'Coniocybales', 'Coniocybomycetes',
              'Ostropales', 'Ostropomycetidae',
              'Pertusariales', 'Ostropomycetidae',
              'Pleosporales', 'Pleosporomycetidae',
              'Pyrenulales', 'Chaetothyriomycetidae',
              'Teloschistales', 'Lecanoromycetidae',
              'Trypetheliales', 'Dothideomycetes incertae sedis',
              'Lecanorales', 'Lecanoromycetidae',
              'Umbilicariales', 'Lecanoromycetes incertae sedis'
         );

my %class = ( 'Acarosporomycetidae', 'Lecanoromycetes',
              'Arthoniomycetes', 'Arthoniomycetes',
              'Ostropomycetidae', 'Lecanoromycetes',
              'Lecanoromycetidae', 'Lecanoromycetes',
              'Coniocybomycetes', 'Coniocybomycetes',
              'Dothideomycetes incertae sedis', 'Dothideomycetes',
              'Pleosporomycetidae', 'Dothideomycetes',
              'Chaetothyriomycetidae', 'Eurotiomycetes',
         );
              
my %host = ('Collema', 'Lichen',
            'Degelia', 'Lichen',
            'Leptogium', 'Lichen',
            'Lobaria', 'Lichen',
            'Massalongia', 'Lichen',
            'Nephroma', 'Lichen',
            'Pannaria', 'Lichen',
            'Parmeliella', 'Lichen',
            'Peltigera', 'Lichen',
            'Protopannaria', 'Lichen',
            'Pseudocyphellaria', 'Lichen',
            'Stereocaulon', 'Lichen',
            'Sticta', 'Lichen',
            'Anthoceros', 'Plant',
            'Blasia', 'Plant',
            'Cylindrospermum', 'Free-living',
            'Encephalartos', 'Plant',
            'Geosiphon', 'Other',
            'Macrozamia', 'Plant',
            'Nostoc', 'Free-living',
            'Gunnera', 'Plant',
            'Cycas', 'Plant'
         );

my %host_colors = ('Plant', '#7CDC00',
                'Free-living', '#00AAB7',
                'Lichen', '#C86DD7',
                'Other', '#E14A46'
                );
                

my %family_colors = ('Collemataceae', '#-16777088', #midnight
                  'Pannariaceae', '#-39322', #salmon
                  'Lobariaceae', '#-8372224', #mocha
                  'unknown', '#000000', #black
                  'Nephromataceae', '#-32768', #tangarine
                  'Peltigeraceae', '#-65536', #maraschino
                  'Stereocaulaceae', '#-3381505', #lavender
                  'Graphidaceae', '#005C26',  #Dark Green
                  'Coenogoniaceae', '#-16776961', #blueberry
                  'Arthoniaceae', '#-65536', #maraschino
                  'Free-living', '#-16744320', #teal 
                  'Plant', '#-16744448', #clover
            );

my %class_colors = ( 'Arthoniomycetes', '#FFA500', #orange 
                     'Lecanoromycetes',  '#005C26',  #Dark Green
                     'Coniocybomycetes', '#-3381505', #lavender
                     'Dothideomycetes',  '#28399F', #Dark blue
                     'Eurotiomycetes', '#-65536', #maraschino
                    );

my %specialists = ( 'Leptogium saturninum', '#C87A8A',
                    'Collema flaccidum', '#AE8B50',
                    'Leptogium lichenoides', '#729C55',
                    'Leptogium furfuraceum', '#FFD000',
                    'Leptogium magnussonii', '#4C99BE',
                    'Peltigera malacea', '#A782C3',
                    'Sticta hypochra', '#-13210', #cantaloupe
            );

my %species_colors = ( 'other', '#FF2020', #Dark Red
                'arboricola', '#C87A8A', #Light Red
                'asymmetrica', '#8A4117', #Brown
                'corticola', '#FFD000', #Yellow
                'decolorans', '#4C99BE', #Light blue
                'gelatinosa', '#A782C3', #purple
                'gigantea', '#FFA500', #ornage
                'impressa', '#28399F', #Dark blue
                'incrustata', '#66CC66', #Light green
                'jamesii', '#005C26',  #Dark Green
                'simplex', '#FF2020', #Dark Red
                'erici', '#-16711936', #spring
                'excentrica', '#-32768', #tangarine
                'glomerata', '#-65536', #maraschino
                'irregularis', '#-10040065', #sky
                'italiana', '#-3381505', #lavender
                'magna', '#-16744448', #clover
                'phycobiontica', '#-16777088', #midnight
                'pyriformis', '#66CC66', #Light green
              );

my @colors = ('#C87A8A', '#AE8B50', '#729C55', '#00A38F', '#4C99BE', '#A782C3');

my $treefilename = shift;
my $comparison = shift;
my %custom;
if ( $comparison =~ /custom/i ) {
  foreach ( @ARGV ) { $custom{$_} = shift(@colors); }
}



open(my $treefile, "<", $treefilename );
my $tax_labels = 0;
my $trees = 0;
while (my $line = <$treefile>){
  chomp($line);
  if ( $line =~ /^\s*;\s*/ ) { print "$line\n"; next; }
  if ( $line =~ /^\s*taxlabels\s*$/ ) { $tax_labels = 1; }
  if ( $line =~ /^begin trees;$/ ) { $trees = 1; }
  if ( $line =~ /^\s*end;\s*/ ) { $tax_labels = 0; $trees = 0;}
  if ( $tax_labels and $comparison !~ /none/i) { 
    if ( $comparison =~ /species/i ) { $line = ColorBySpecies($line); }
    else {$line = ColorByGenus($line, $comparison); }
  }
  print "$line\n";
}
 
 sub ColorBySpecies {
  my $line = shift;
  my $multiple = 0;
  my $group;
  while ( $line =~ /[A-Z][a-z.]+ ([a-z]+)/g ) {
    my $species = $1;
    if ( $species eq 'sp' ) { next; }
    if ( $group ) { $multiple = 1; }
    elsif ( $species_colors{$species} ) { $group = $species_colors{$species}; }
    else { $group = $species_colors{'other'}; }
  }
  if ( $multiple ) { $line .=  "[&!color=#000000]"; }
  elsif ( $group ){ $line .=  "[&!color=$group]"; }
  else { $line .=  "[&!color=#C0C0C0]"; }
  return $line;
}

 
sub ColorByGenus {
  my $line = shift;
  my $comparison = shift;
  my $multiple = 0;
  my $group;
  if ( $comparison =~ /specialists/i ) {
    foreach ( keys %specialists ) {
      if ( $line =~ /$_/ ) {
        if ( $group and $group ne $specialists{$_} ) { $multiple = 1; }
        $group = $specialists{$_};
      }
    }
  }     
  while ( $line =~ /([A-Z][a-z]+)/g ) {
    my $genus = $1;
    if ( $1 eq 'Group' or $comparison =~ /specialists/i) {next; }
    #print STDERR "??\n";
    if ( $comparison =~ /host/i ) { 
      if ( $host{$genus} and $host_colors{$host{$genus}} ) {
        if ( $group and $group ne $host_colors{$host{$genus}} ) { $multiple = 1; }
        $group = $host_colors{$host{$genus}}; 
      }
    }
    elsif ( $comparison =~ /family/i ) { 
      if ( $family{$genus} and $family_colors{$family{$genus}} ) {
        if ( $group and $group ne $family_colors{$family{$genus}} ) { $multiple = 1; }
        $group = $family_colors{$family{$genus}}; 
      }
    }
    elsif ( $comparison =~ /class/i and $genus !~ /free[- ]?living/i ) {
      print STDERR join(", ", ($genus, $family{$genus}, $orders{$family{$genus}}, $subclass{$orders{$family{$genus}}}, $class{$subclass{$orders{$family{$genus}}}}, $class_colors{$class{$subclass{$orders{$family{$genus}}}}})), "\n";
      if ( $class{$subclass{$orders{$family{$genus}}}} and $class_colors{$class{$subclass{$orders{$family{$genus}}}}} ) {
        if ( $group and $group ne $class_colors{$class{$subclass{$orders{$family{$genus}}}}} ) { $multiple = 1; }
        $group = $class_colors{$class{$subclass{$orders{$family{$genus}}}}}; 
      }
    }
    elsif ( $comparison =~ /genus/i ) { 
      if ( $lichen_colors{$genus} ) {
        if ( $group and $group ne $lichen_colors{$genus} ) { $multiple = 1; }
        $group = $lichen_colors{$genus}; 
      }
    }
    elsif ( $comparison =~ /custom/i ) {
      if ( $custom{$genus} ) {
        if ( $group and $group ne $custom{$genus} ) { $multiple = 1; }
        $group = $custom{$genus}; 
      }
    }  
    elsif ( $comparison =~ /none/i ) { $group = '#000000'; }
    else { die "Colouring scheme $comparison not recognized. Use 'host', 'family', 'genus', 'specialists' or 'custom'\n"; }
  }
  if ( $multiple ) { $line .=  "[&!color=#000000]"; }
  elsif ( $group ){ $line .=  "[&!color=$group]"; }
  else { $line .=  "[&!color=#A0A0A0]"; }
  return $line;
}

