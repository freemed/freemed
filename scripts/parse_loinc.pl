#!/usr/bin/perl
# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2008 FreeMED Software Foundation
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

#	LOINC_NUM (field #1), COMPONENT (field #2), PROPERTY (field #3),
#	TIME_ASPCT (field #4), SYSTEM (field #5), SCALE_TYP (field #6),
#	METHOD_TYP (field #7), ANSWERLIST (field #18), STATUS (field #19), and
#	SHORTNAME (field #59), EXTERNAL_COPYRIGHT_NOTICE (field #63)

my $cut = 0;
while (my $line = <>) {
	# Remove enclosing quotes
	$line =~ s/\"//g;

	# Escape commas
	$line =~ s/,/\\,/g;

	my (@fields) = split /\t/, $line;
	#print "LOINC_NUM = $fields[0]\n";
	#print "COMPONENT = $fields[1]\n";
	#print "PROPERTY = $fields[2]\n";
	#print "TYPE_ASPCT = $fields[3]\n";
	#print "SYSTEM = $fields[4]\n";
	#print "SCALE_TYP = $fields[5]\n";
	#print "METHOD_TYP = $fields[6]\n";
	#print "ANSWERLIST = $fields[17]\n";
	#print "STATUS = $fields[18]\n";
	#print "SHORTNAME = $fields[58]\n";
	#print "EXTERNAL_COPYRIGHT_NOTICE = $fields[62]\n";
	#print "-----------------------------\n";
	if ($cut == 1) {
		print "$fields[0],$fields[1],$fields[2],$fields[3],$fields[4],$fields[5],$fields[6],$fields[17],$fields[18],$fields[58],$fields[62]";
	}
	if ($line =~ /Clip Here for Data/) {
		$cut = 1;
	}
}

