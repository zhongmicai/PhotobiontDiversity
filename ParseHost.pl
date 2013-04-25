#!/usr/bin/perl -w
use warnings;
use strict;
while (<>){
  chomp;
  if ( $_ =~ /gi\|\d+\|\w+\|(\w+)\.\d+\|[^']*'(\w+\s+[\w.]+)[()\d\s]*cyanobiont'/ ) { print "$1\t$2\n"; }
}