#!/usr/bin/perl -w
use warnings;
use strict;

while(<>){
  while ( $_ =~ s/gi\|(\d+)\|// ) { print "$1\n"; }
}
  