#!/usr/bin/perl
#
# dia2sql.pl - version 1.2 - 2001/03/20
# Entity-relationship with UML diagrams from Dia
#
# Copyright (c) 2001 by Alexander Troppmann
# http://www.cocktaildreams.de - talex@karyopse.de
#
# This program releases under the GNU Public License;
# You can redistribute it or modify it under the terms of GPL.
#
# Featurelist:
# - Creates a table for each UML class
# - All attributes of a Dia UML class will be converted to SQL statements:
#   "Name" = SQL column name,
#   "Type" = SQL datatype and also additional attributes,
#   "Visibility" = if set to "protected" the attribute will be a primary key
#   "Value" = if set a default value will be defined for this column
# - DROP TABLE statement can be created
# - For each column starting with "FK_" a foreign key constraint will be
#   created (PostgreSQL only). Do not use "REFERENCES" attributes in the UML
#   diagram!
# - Support for indexed columns (PostgreSQL only)
#
# Required stuff:
# - First get the expat library (> v1.95.0) as RPM package or from
#   http://www.jclark.com/xml/expat.html which is a XML1.0 parser written in C.
# - Second install XML::Parser from CPAN, just type in at your shell prompt:
#
#      root@localhost:~ > perl -MCPAN -e 'install XML::Parser'
#
# That's all! :-)

use XML::Parser;

$VERSION = 'dia2sql.pl v1.2';


###########################################################
## CONFIG - MAKE ANY CHANGES HERE

$DEBUG = 0;					# 1 if you want to get debug messages to STDERR
$CREATECOMMENTS = 0;		# 1 if you want to get comments like mysqldump does

$MYSQL = 1;					# choose either MySQL or PostgreSQL support
$POSTGRESQL = 0;			# by setting 0 and 1 values here

$CREATEDROPTABLES = 0;		# 1 if you want to have "DROP TABLE" statements

if($MYSQL) {
	$DROPTABLE = "DROP TABLE IF EXISTS";	# DROP TABLE statement to be used
} elsif($POSTGRESQL) {
	$DROPTABLE = "DROP TABLE";
	$CREATEFKSFROMDBNAMING = 1;				# creates REFERENCE for FK_ columns
} else {
	die "ERROR: You didn't choose a database!\n";
}


###########################################################
## VARIABLES

$table = 0;				# 1 if inside table
$tablename = 0;			# 1 if tablename tag detected
$column = 0;			# 1 if inside column
$columnname = 0;		# 1 if columnname detected
$columntype = 0;		# 1 if datatype for column detected
$columnvi = 0;			# 1 if "visibility" for primary key definitions
$columnvalue = 0;		# 1 if value attribute detected

undef($myTablename);			# current table
undef(%myTableContents);		# hash with columns for a certain table
undef(%myKeyMarker);			# marker for primary keys
undef(%myIndexMarker);			# marker for indexed column
undef(%myPrimaryKeys);			# hash with primary keys for a certain table
undef(%myIndexColumns);			# columns for index
undef($myColumnvalue);			# default value for datatype
undef($myTableUsesForeignKeys);	# is my current table using foreign keys?
undef($myTableUsesIndizes);		# is my current table using indexed columns?
undef(@myTablesIncludingFKs);	# build these tables later
undef(@myTables);				# build tables first (cause they're referenced)


###########################################################
## INIT

## get infile (and outfile) from command line
#
if(@ARGV < 1) {

	die <<"_USAGE_END_"
Usage: dia2sql.pl file.dia [file.sql]

$VERSION - (c) 2001 by Alexander Troppmann

Converts xml data input from Dia to sql statements. If file.sql is not
specified the sql statements will be printed to STDOUT.

Edit dia2sql.pl and change the configuration at top of the Perl script.
Make sure you have defined the right database (MySQL or PostgreSQL) for
SQL output.

_USAGE_END_

} else {

	$infile = shift;		# input file, Dia XML formatted
	$outfile = shift;		# output file, filled with SQL statements
	
	if($outfile) {
		open(STDOUT, ">$outfile") or die "$outfile: $!n";
	}
	
}

if($MYSQL) {
	if($DEBUG) { print STDERR "Creating SQL output for MySQL\n"; }
} elsif($POSTGRESQL) {
	if($DEBUG) { print STDERR "Creating SQL output for PostgreSQL\n"; }
}


## init xml parser and parse input file
#
$parser = new XML::Parser(Style => 'Stream');
$parser->parsefile($infile);


## create sql output
#
&createSql();


## cleanup and exit
#
if($outfile) {
	close(STDOUT);
}

exit;


###########################################################
## SUB ROUTINES

## called if parser enters new tag
#
$prefix = '';				# patch for Dia v0.83 by Georges Khaznadar

sub StartTag {
	
	my $p = shift;			# parser context
	$ctag = shift;			# name of this tag element
	%attr = %_;				# hash with attributes
	
	if($ctag eq 'dia:diagram') {
	    # then all other tags will be prefixed with dia:
		# (patch for Dia v0.83 by Georges Khaznadar)
	    $prefix = 'dia:';
	}
	
	if($ctag eq 'object' and $attr{'type'} eq 'UML - Class') {
		$table = 1;
		$myTableUsesForeignKeys = 0;
		$myTableUsesIndizes = 0;
	} elsif($ctag eq 'composite' and $attr{'type'} eq 'umlattribute' and $table) {
		$column = 1;
	} elsif($table and $ctag eq $prefix.'attribute' and $attr{'name'} eq 'name' and !$column) {
		$tablename = 1;
	} elsif($column and $ctag eq $prefix.'attribute' and $attr{'name'} eq 'name' and !$tablename) {
		$columnname = 1;
	} elsif($column and $ctag eq $prefix.'attribute' and $attr{'name'} eq 'value' and $column) {
		$columnvalue = 1;
	} elsif($column and $ctag eq $prefix.'attribute' and $attr{'name'} eq 'type') {
		$columntype = 1;
	} elsif($column and $ctag eq $prefix.'attribute' and $attr{'name'} eq 'visibility') {
		$columnvi = 1;
	} elsif($columnvi and $ctag eq $prefix.'enum') {
		if($attr{'val'} == 2) { $myKeyMarker = 1; }		# primary key found
		else { $myKeyMarker = 0; }
		if($attr{'val'} == 1) { $myIndexMarker = 1; }		# index column found
		else { $myIndexMarker = 0; }
	}
	
}

## called if parser leaves a tag
#
sub EndTag {
	
	my $p = shift;
	$ctag = shift;
	
	if($ctag eq $prefix.'object' and $table) {
		
		$table = 0;
		$myTableUsesIndizes = 0;
		
		if($myTableUsesForeignKeys) {
			push @myTablesIncludingFKs, $myTablename;
			$myTableUsesForeignKeys = 0;
			if($DEBUG) { print STDERR "Table '$myTablename' has foreign keys\n"; }
		} else {
			push @myTables, $myTablename;
			if($DEBUG) { print STDERR "Table '$myTablename' (may be referenced by other tables)\n"; }
		}
		
	} elsif($ctag eq $prefix.'composite' and $column) {
		
		$column = 0;
		my $sql = "$myColumnname $myColumntype";
		
		if($myKeyMarker) {
			push @{$myPrimaryKeys{$myTablename}}, "$myColumnname";
			if($MYSQL) {
				$sql .= " NOT NULL";
			}
		}
		
		if($myIndexMarker and $POSTGRESQL) {
			push @{$myIndexColumns{$myTablename}}, "$myColumnname";
			$myTableUsesIndizes = 1;
			if($DEBUG) { print STDERR "Indexed column found!\n"; }
		}
		
		if($myColumnvalue) {
			$sql .= " DEFAULT '$myColumnvalue'";
		}
		
		if($CREATEFKSFROMDBNAMING) {
			if($myColumnname =~ /^FK_/) {
				$myColumnname =~ /([A-Za-z0-9]+)_([A-Za-z0-9]+)$/;
				my ($table, $column) = ($1, $2);
				$sql .= " REFERENCES $table";
				$myTableUsesForeignKeys = 1;
				if($DEBUG) { print STDERR "Foreign key found!\n"; }
			}
		}
		
		undef($myColumnvalue);
		
		push @{$myTableContents{$myTablename}}, $sql;
		if($DEBUG) { print STDERR "Added new column data \"$sql\"\n"; }
		
	} elsif($ctag eq $prefix.'attribute' and $tablename) {
		$tablename = 0;
	} elsif($ctag eq $prefix.'attribute' and $column and $columnname) {
		$columnname = 0;
	} elsif($ctag eq $prefix.'attribute' and $column and $columnvalue) {
		$columnvalue = 0;
	} elsif($ctag eq $prefix.'attribute' and $column and $columntype) {
		$columntype = 0;
	} elsif($ctag eq $prefix.'enum' and $column and $columnvi) {
		$columnvi = 0;
	}
	
}


## called for text between any tags
#
sub Text {
	
	$text = $_;
	
	if($text =~ /^\s+$/) { return; }		# skip whitespaces
	
	if($ctag eq $prefix.'string' and $tablename) {
		$text =~ s/(^#|#$)//g;				# remove hash characters
		$myTablename = $text;
		if($DEBUG) { print STDERR "\nTable: $myTablename\n"; }
	} elsif($ctag eq $prefix.'string' and $columnname) {
		$text =~ s/(^#|#$)//g;              # remove hash characters
		$myColumnname = $text;
		if($DEBUG) { print STDERR "Columnname: $myColumnname\n"; }
	} elsif($ctag eq $prefix.'string' and $columnvalue) {
		$text =~ s/(^#|#$)//g;
		$text =~ s/('|")/\\$1/sg;
		$myColumnvalue = $text;
		if($DEBUG) { print STDERR "Columnvalue: $myColumnvalue\n"; }
	} elsif($ctag eq $prefix.'string' and $columntype) {
		$text =~ s/(^#|#$)//g;              # remove hash characters
		$myColumntype = $text;
		if($DEBUG) { print STDERR "Columntype: $myColumntype\n"; }
	}
	
}


## create sql output
#
sub createSql {
	
	my($columns,$keys,$sql,$date);
	
	if($DEBUG) { print STDERR "\nWriting SQL statements...\n"; }
	
	if($CREATECOMMENTS) {
		$date = `date`; chop($date);
		print "# Created by $VERSION (".$date.")\n\n";
	}
	
	if($DEBUG) { print STDERR "\nFirst build tables referenced by other tables...\n"; }
	foreach(@myTables) {
		&buildTable($_);
	}
	
	if($DEBUG) { print STDERR "\nBuild tables including foreign keys...\n"; }
	foreach(@myTablesIncludingFKs) {
		&buildTable($_);	
	}
	
	if($DEBUG) { print STDERR "Done!\n\n"; }
	
}


## build sql table
#
sub buildTable($) {
	
	my $tablename = shift;
	undef($sql);
	
	if($DEBUG) { print STDERR "Working on '$tablename':\n"; }
	
	if($CREATECOMMENTS) {
		print "#\n# Table structure for table '$tablename'\n#\n\n";
	}
	
	if($CREATEDROPTABLES) {
		if($DEBUG) { print STDERR "-> Creating DROP TABLE statement\n"; }
		print "$DROPTABLE $tablename;\n";
	}
	
	if($DEBUG) { print STDERR "-> Collect table columns\n"; }
	my $columns = join(",\n\t",@{$myTableContents{$tablename}});
	my $keys = "PRIMARY KEY(".join(",",@{$myPrimaryKeys{$tablename}}).")";
	
	if($DEBUG) { print STDERR "-> Create CREATE TABLE statement\n"; }
	my $sql = "CREATE TABLE $tablename (\n\t".$columns;
	
	if(@{$myPrimaryKeys{$tablename}}) {
		if($DEBUG) { print STDERR "-> Adding PRIMARY KEY definitions\n"; }
		$sql .= ",\n\t".$keys."\n);\n\n";
	} else {
		$sql .= "\n);\n\n";
	}
	
	print $sql;
	undef($sql);
	
	if(@{$myIndexColumns{$tablename}}) {			# add indexed columns
		foreach $column (@{$myIndexColumns{$tablename}}) {
			if($DEBUG) { print STDERR "-> Adding INDEX for $tablename ($column)\n"; }
			$sql .= "CREATE INDEX ".$column."_idx ON $tablename ($column);\n";
		}
	}
	
	print "$sql\n";
	
}
