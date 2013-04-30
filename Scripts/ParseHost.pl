#!/usr/bin/perl -w
use warnings;
use strict;
while (<>){
  chomp;
  if ( $_ =~ /gi\|\d+\|\w+\|(\w+)\.\d+\|[^']*'(\w+\s+[\w.() ]+)cyanobiont'/ ) { print "$1\t$2\n"; }
  elsif ( $_ =~ /^>/ ) { print STDERR "$_: could not parse host info\n"; }
}