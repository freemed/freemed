#!/usr/bin/perl
# $Id$
# $Author$

$VERSION = "0.1";
$appversion = "0.6.0";

sub Generate_GettextXML {
	my ($component, $version, $_phrases) = @_; @phrases = @$_phrases;

	my $output = "<?xml version=\"1.0\">\n".
		"<GettextXML lang=\"en\">\n\n".
		"\t<information>\n".
		"\t\t<Application>FreeMED</Application>\n".
		"\t\t<ApplicationVersion>$appversion</ApplicationVersion>\n".
		"\t\t<Component>".HtmlEntities($component)."</Component>\n".
		"\t\t<ComponentVersion>".HtmlEntities($version)."</ComponentVersion>\n".
		"\t\t<Locale></Locale>\n".
		"\t\t<RevisionDate>".$cur_date."</RevisionDate>\n".
		"\t\t<RevisionCount>1</RevisionCount>\n".
		"\t\t<Generator>Perl5</Generator>\n".
		"\t\t<ContentTransferEncoding>8bit</ContentTransferEncoding>\n".
		"\t\t<Translator>\n".
		"\t\t\t<Name></Name>\n".
		"\t\t\t<Address></Address>\n".
		"\t\t</Translator>\n".
		"\t</information>\n".
		"\n";

	foreach $phrase (@phrases) {
		$output .= "\t<translation>\n";
		$output .= "\t\t<original>".HtmlEntities(StripSlashes($phrase))."</original>\n";
		$output .= "\t\t<translated></translated>\n";
		$output .= "\t</translation>\n\n";
	}

	$output .= "</GettextXML>\n\n";

	return $output;
} # end sub Generate_GettextXML

sub Get_Modules {
	my @modules = glob("modules/*.module.php");
	$relative_path = "";
	if ($#modules le 1) {
		@modules = glob("../modules/*.module.php");
		$relative_path = "../";
	}
	return @modules;
} # end Get_Modules

sub Get_Module_Name {
	my $module = shift;
	my $name;

	#print "\nmodule = $module\n";
	open(MODULE, $module) or die ("GetModuleName : error opening $module");

	while (<MODULE>) {
		chop;
		if (/register_module+[\ ]\(\"(.+?[^\"\)])\"\)/) {
			$name = $1;
			#print "name = $name\n";
		}
	}

	close(MODULE);

	# Have to translate to lowercase
	$name =~ tr/A-Z/a-z/;

	return $name;
} # end Get_Module_Name

sub Get_Module_Version {
	my $module = shift;
	my $version;

	open(MODULE, $module) or
		die ("GetModuleVersion : error opening $module");

	while (<MODULE>) {
		chop;
		if (/var\ \$MODULE_VERSION = \"(.+?[^\"\)])\"/) {
			$version = $1;
			#print "version = $version\n";
		}
	}

	close(MODULE);

	return $version;
} # end Get_Module_Version

sub Get_Page_Name {
	my $file = shift;

	# Get last component of filename, if it's in a path
	if ($file =~ /\//) {
		my @components = explode(/\//, $file);
		$file = pop(@components);
	}

	# Remove .php
	$file =~ s/\.php$//;

	return $file;
} # end Get_Page_Name

sub HtmlEntities {
	my $string = shift;
	$string =~ s/\&/\&amp;/ge;
	$string =~ s/\</\&lt;/ge;
	$string =~ s/\>/\&gt;/ge;
	return $string
} # end sub HtmlEntities

sub Parse_File {
	my $filename = shift;
	open (HANDLE, $filename) || die "Failed to open $filename\n";

	my @phrases = ( );
	my $line;

	while ($line=<HANDLE>) {
		chop $line;
		if ($line =~ /__\(\"/) {
			$_ = $line;
			if (/__\(\"(.+?[^\"\)])\"\)/) {
				#print $1 . "\n";	
				push @phrases, $1;
			}
		}
	}

	close(HANDLE);

	return @phrases;

} # end Parse_File

sub Remove_Duplicates {
	my ($_array) = @_; @array = @$_array;
	my %seen;
	return grep ( !$seen{$_}++, @array );
} # end Remove_Duplicates

sub StripSlashes {
	my $string = shift;
	$string =~ s/\\//g;
	return $string;
} # end StripSlashes

sub Write_to_File {
	my ($filename, $output) = @_;

	open (OUTPUT, ">$filename") || die("Cannot open $filename for output\n");
	print OUTPUT $output;
	close (OUTPUT);
} # end sub Write_to_File


print "GettextXML Catalog Builder v$VERSION\n";
print "(c) 2003 by the FreeMED Software Foundation\n\n";

print "Processing modules ... \n";
my @modules = Get_Modules();

# Create template path
`mkdir -p $relative_path/locale/template/`;

if ($#modules ge 1) {
	foreach $module (@modules) {
		my @strings = Parse_File($module);
		my $module_name = Get_Module_Name($module);
		print "\t($module)\n";
		my $module_version = Get_Module_Version($module);
		my $output = Generate_GettextXML($module_name, $module_version, \@strings);
		if (length($module_name) ge 1) {
			Write_to_File($relative_path."locale/template/".
				$module_name.".xml", $output);
		}
	}
}

print "Processing files ... \n";
my @files = glob($relative_path."*.php");
foreach $file (@files) {
	print "\t($file)\n";
	my @strings = Parse_File($file);
	my $page_name = Get_Page_Name($file);
	@strings = Remove_Duplicates(\@strings);
	if (($#strings ge 1) and (length($page_name) ge 1)) {
		Write_to_File($relative_path."locale/template/".
			$page_name.".xml",
			Generate_GettextXML(
				$page_name,
				$appversion,
				\@strings
			)
		);
	}
}

print "Processing API ... \n";

my @API_files = glob($relative_path."lib/*.php");
my @API_strings;
foreach $API_file (@API_files) {
	print "\t($API_file)\n";
	my @strings = Parse_File($API_file);
	push (@API_strings, @strings);
}
@API_strings = Remove_Duplicates(\@API_strings);
my $output = Generate_GettextXML("API", $version, \@API_strings);
Write_to_File($relative_path."locale/template/freemed.xml", $output);

print "Processing default template ... \n";

my @template_files = glob($relative_path."lib/template/default/*.php");
my @template_strings;
foreach $template_file (@template_files) {
	print "\t($template_file)\n";
	my @strings = Parse_File($template_file);
	push (@template_strings, @strings);
}
@template_strings = Remove_Duplicates(\@template_strings);
Write_to_File(
	$relative_path."locale/template/template_default.xml",
	Generate_GettextXML(
		"Default Template",
		$version,
		\@template_strings
	)
);

print "\n".
	"-----\n".
	"Template language files should be located in ".$relative_path.
	"locale/template/.\n\n".
	"There is a possibility that some strings will not have been\n".
	"extracted properly, so please be sure to check the catalogs.\n";

1;
