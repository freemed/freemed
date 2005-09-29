#!/usr/bin/perl -I/usr/share/freemed/lib/perl
# $Id$
# $Author$

use XML::RAX;

$VERSION = "0.1.1";
$appversion = `cat lib/freemed.php | grep \\'VERSION\\' | awk -F\\" '{ print \$2; }'`;

# Get parameters
$locale = shift || 'template';
$locale_name = shift || '';

sub Trim { my $s = shift; $s =~ s/^\s+//; $s =~ s/\s+$//; return $s }

sub Current_Date {
	my ($mon, $day, $year);
	(undef, undef, $mon, $day, $year, undef, undef, undef) = gmtime();
	$mon++;		# months returned as 0..11
	$year += 2000;	# years returned as # years since 1900
	$day = "0".$day if (length($day) eq 1);
	$mon = "0".$mon if (length($mon) eq 1);
	return $year . "-" . $mon . "-" . $day;
} # end sub Current_Date

sub File_Exists {
	my $file = shift;
	return (-f $relative_path.'locale/'.$locale.'/'.$file.'.xml');
} # end sub File_Exists

sub Generate_GettextXML {
	my ($component, $version, $_phrases) = @_; @phrases = @$_phrases;

	my $output = "<?xml version=\"1.0\"?>\n".
		"<gettextXML lang=\"".$locale."\">\n\n".
		"\t<information>\n".
		"\t\t<Application>FreeMED</Application>\n".
		"\t\t<ApplicationVersion>".Trim($appversion)."</ApplicationVersion>\n".
		"\t\t<Component>".HtmlEntities($component)."</Component>\n".
		"\t\t<ComponentVersion>".HtmlEntities($version)."</ComponentVersion>\n".
		"\t\t<Locale>".Trim(HtmlEntities($locale))."</Locale>\n".
		"\t\t<LocaleName>".Trim(HtmlEntities($locale_name))."</LocaleName>\n".
		"\t\t<RevisionDate>".Trim(Current_Date())."</RevisionDate>\n".
		"\t\t<RevisionCount>1</RevisionCount>\n".
		"\t\t<Generator>GettextXML</Generator>\n".
		"\t\t<ContentTransferEncoding>8bit</ContentTransferEncoding>\n".
		"\t\t<Translator></Translator>\n".
		"\t</information>\n".
		"\n";

	foreach $phrase (@phrases) {
		$output .= "\t<translation>\n";
		$output .= "\t\t<original>".Trim(HtmlEntities(StripSlashes($phrase)))."</original>\n";
		$output .= "\t\t<translated></translated>\n";
		$output .= "\t</translation>\n\n";
	}

	$output .= "</gettextXML>\n\n";

	return $output;
} # end sub Generate_GettextXML

sub Generate_GettextXML_Merged {
	my ($component, $version, $_mphrases) = @_; @mphrases = @$_mphrases;

	my %meta = Translation_Meta_Information($component);
	
	my %processed = %{&Merge_From_Translation($component, \@mphrases)};
	print "Generate_GettextXML_Merged: ".keys(%processed)." translations imported\n";

	my $revision;
	if ($meta{'RevisionDate'} eq Current_Date()) {
		# If more than one in a day, increment the revision counter
		$revision = $meta{'RevisionCount'} + 1;
	} else {
		# Reset revision
		$revision = 1;
	}

	my $output = "<?xml version=\"1.0\"?>\n".
		"<gettextXML lang=\"".$locale."\">\n\n".
		"\t<information>\n".
		"\t\t<Application>FreeMED</Application>\n".
		"\t\t<ApplicationVersion>".Trim($appversion)."</ApplicationVersion>\n".
		"\t\t<Component>".Trim(HtmlEntities($component))."</Component>\n".
		"\t\t<ComponentVersion>".Trim(HtmlEntities($version))."</ComponentVersion>\n".
		"\t\t<Locale>".Trim(HtmlEntities($locale))."</Locale>\n".
		"\t\t<LocaleName>".Trim(HtmlEntities($locale_name))."</LocaleName>\n".
		"\t\t<RevisionDate>".Trim(Current_Date())."</RevisionDate>\n".
		"\t\t<RevisionCount>".Trim($revision)."</RevisionCount>\n".
		"\t\t<Generator>".Trim($meta{'Generator'})."</Generator>\n".
		"\t\t<ContentTransferEncoding>8bit</ContentTransferEncoding>\n".
		"\t\t<Translator>".Trim($meta{'Translator'})."</Translator>\n".
		"\t</information>\n".
		"\n";

	foreach $phrase (@mphrases) {
		$output .= "\t<translation>\n";
		$output .= "\t\t<original>".Trim(HtmlEntities(StripSlashes($phrase)))."</original>\n";
		$output .= "\t\t<translated>".Trim(HtmlEntities($processed{$phrase}))."</translated>\n";
		$output .= "\t</translation>\n\n";
	}

	$output .= "</gettextXML>\n\n";

	return $output;
} # end sub Generate_GettextXML_Merged

sub Get_Modules {
	my @modules = glob("modules/*.module.php");
	$relative_path = "";
	if ($#modules le 1) {
		@modules = glob("../modules/*.module.php");
		$relative_path = "../";
	}
	return wantarray ? @modules : \@modules;
} # end sub Get_Modules

sub Get_Module_Name {
	my $module = shift;
	my $name;

	#print "\nmodule = $module\n";
	open(MODULE, $module) or die ("GetModuleName : error opening $module");

	while (<MODULE>) {
		chop;
		if (/^class\ ([^\s]+)\ /) {
			$name = $1;
			#print "name = $name\n";
		}
	}

	close(MODULE);

	# Have to translate to lowercase
	$name =~ tr/A-Z/a-z/;

	return $name;
} # end sub Get_Module_Name

sub Get_Module_Title {
	my $module = shift;
	my $title;

	open(MODULE, $module) or
		die ("GetModuleVersion : error opening $module");

	while (<MODULE>) {
		chop;
		if (/var\ \$MODULE_NAME = \"(.+?[^\"\)])\"/) {
			$title = $1;
			#print "title = $title\n";
		}
	}

	close(MODULE);

	return $title;
} # end sub Get_Module_Title

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
} # end sub Get_Module_Version

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
} # end sub Get_Page_Name

sub HtmlEntities {
	my $string = shift;
	$string =~ s/\&/\&amp;/ge;
	$string =~ s/\</\&lt;/ge;
	$string =~ s/\>/\&gt;/ge;
	return $string
} # end sub HtmlEntities

sub Merge_From_Translation {
	my ($component, $_phrases) = @_; my @phrases = @$_phrases;
	my $phrase;

	# Get previous translations
	my %translations = %{&Read_From_Translation($component)};
	#print "Merge_From_Translations: ".keys(%translations)." translations found\n";
	my %new = ( );

	# Loop through phrases
	foreach $phrase (@phrases) {
		if (defined $translations{$phrase}) {
			# Merge from old
			$new->{$phrase} = $translations{$phrase};
		} else {
			# None, add null
			$new->{$phrase} = '';
		}
	}

	# Return new array
	return $new;
	#return wantarray ? %new : \%new;
} # end sub Merge_From_Translation

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

	return wantarray ? @phrases : \@phrases;
} # end sub Parse_File

sub Read_From_Translation {
	my $component = shift;

	my %tphrases;

	my $R = new XML::RAX();
	$R->openfile($relative_path.'locale/'.$locale.'/'.$component.'.xml');
	$R->setRecord('translation');

	my $rec = $R->readRecord();
	while ($rec) {
		my $original = $rec->getField('original');
		my $translated = $rec->getField('translated');
		
		$tphrases->{$original} = $translated;
		#print "\t".$original." = ".$translated."\n";

		# Read next record
		$rec = $R->readRecord();
	}

	#print "Read_From_Translation: found ".keys(%tphrases)." phrases in old file\n";
	
	return $tphrases;
} # end sub Read_From_Translation

sub Remove_API_Duplicates {
	my ($_array, $_API) = @_;
	my @array = @$_array;
	my @API = @$_API;

	my @results = ( );

	foreach $value (@array) {
		my $found = 0;
		foreach $API_value (@API) {
			#print "value = $value, API_value = $API_value\n";
			if ($value eq $API_value) { $found = 1; }
		}
		#print "found in API: ".$value."\n" if ($found);
		push (@results, $value) if (!$found);
	}
	return wantarray ? @results : \@results;
} # end sub Remove_API_Duplicates

sub Remove_Duplicates {
	my ($_array) = @_; @array = @$_array;
	my %seen;
	return grep ( !$seen{$_}++, @array );
} # end sub Remove_Duplicates

sub StripSlashes {
	my $string = shift;
	$string =~ s/\\//g;
	return $string;
} # end sub StripSlashes

sub Translation_Meta_Information {
	my $file = shift;

	my $R = new XML::RAX();
	$R->openfile($relative_path.'locale/'.$locale.'/'.$file.'.xml');

	$R->setRecord('information');
	my $rec = $R->readRecord();

	my %hash = (
		'Generator' => $rec->getField('Generator'),
		'LocaleName' => $rec->getField('LocaleName'),
		'RevisionDate' => $rec->getField('RevisionDate'),
		'RevisionCount' => $rec->getField('RevisionCount'),
		'Translator' => $rec->getField('Translator')
	);

	return %hash;
} # end sub Translation_Meta_Information

sub Write_to_File {
	my ($filename, $output) = @_;

	open (OUTPUT, ">$filename") || die("Cannot open $filename for output\n");
	print OUTPUT $output;
	close (OUTPUT);
} # end sub Write_to_File

print "GettextXML Catalog Builder v$VERSION\n";
print "(c) 2003 by the FreeMED Software Foundation\n\n";

my @modules = Get_Modules();

# Create template path
system ("mkdir -p ".$relative_path."locale/".$locale."/");

print "Processing API ... \n";

my @API_files = glob($relative_path."lib/*.php");
@API_strings = ( );
foreach $API_file (@API_files) {
	print "\t($API_file)\n";
	my @strings = Parse_File($API_file);
	push (@API_strings, @strings);
}
@API_strings = Remove_Duplicates(\@API_strings);
@API_strings = sort @API_strings;
if (File_Exists('freemed')) {
	my $output = Generate_GettextXML_Merged(
		'freemed',
		$version,
		\@API_strings
	);
	Write_to_File($relative_path."locale/".$locale."/freemed.xml", $output);
	print "\t\t[ merged old translations ]\n";
} else {
	my $output = Generate_GettextXML(
		'freemed',
		$version,
		\@API_strings
	);
	Write_to_File($relative_path."locale/".$locale."/freemed.xml", $output);
}

print "Processing modules ... \n";

if ($#modules ge 1) {
	foreach $module (@modules) {
		my @strings = Parse_File($module);
		my $module_name = Get_Module_Name($module);
		print "\t($module)\n";
		my $module_version = Get_Module_Version($module);
		my $module_title = Get_Module_Title($module);
		if ($module_title) {
			push @strings, $module_title;
		}
		@strings = Remove_Duplicates(\@strings);
		@strings = Remove_API_Duplicates(\@strings, \@API_strings);
		@strings = sort @strings;
		if (File_Exists($module_name)) {
			my $output = Generate_GettextXML_Merged(
				$module_name,
				$module_version,
				\@strings
			);
			if (length($module_name) ge 1) {
				Write_to_File($relative_path."locale/".
					$locale."/".$module_name.".xml",
					$output);
			}
			print "\t\t[ merged old translations ]\n";
		} else {
			my $output = Generate_GettextXML($module_name, $module_version, \@strings);
			if (length($module_name) ge 1) {
				Write_to_File($relative_path."locale/".
					$locale."/".$module_name.".xml",
					$output);
			}
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
	@strings = Remove_API_Duplicates(\@strings, \@API_strings);
	@strings = sort @strings;
	if (($#strings ge 1) and (length($page_name) ge 1)) {
		if (File_Exists($page_name)) {
			Write_to_File($relative_path."locale/".
				$locale."/".$page_name.".xml",
				Generate_GettextXML_Merged(
					$page_name,
					$appversion,
					\@strings
				)
			);
			print "\t\t[ merged old translations ]\n";
		} else {
			Write_to_File($relative_path."locale/".$locale."/".
				$page_name.".xml",
				Generate_GettextXML(
					$page_name,
					$appversion,
					\@strings
				)
			);
		}
	}
}

opendir(DH, $relative_path."lib/template/") or
	die("Could not open template directory");

while ($template = readdir(DH)) {
	if ((-d $relative_path."lib/template/".$template) and
			($template ne ".") and
			($template ne "..") and
			($template ne "CVS")) {
		print "Processing template '$template' ... \n";

		my @template_files = glob($relative_path.
			"lib/template/".$template."/*.php");
		my @template_strings = ( );
		foreach $template_file (@template_files) {
			print "\t($template_file)\n";
			my @strings = Parse_File($template_file);
			push (@template_strings, @strings);
		}
		@template_strings = Remove_Duplicates(\@template_strings);
		@template_strings = Remove_API_Duplicates(
				\@template_strings,
				\@API_strings);
		@template_strings = sort @template_strings;
		if (File_Exists('template_'.$template)) {
			my $output = Generate_GettextXML_Merged(
				"template_".$template,
				$version,
				\@template_strings
			);
			Write_to_File(
				$relative_path."locale/".$locale.
				"/template_".$template.".xml",
				$output
			);
			print "\t\t[ merged old translations ]\n";
		} else {
			my $output = Generate_GettextXML(
				"template_".$template,
				$version,
				\@template_strings
			);
			Write_to_File(
				$relative_path."locale/".$locale.
				"/template_".$template.".xml",
				$output
			);
		}
	} # end checking for a proper template
} # end directory looping

print "\n".
	"-----\n\n".
	"Template language files should be located in ".$relative_path.
	"locale/template/.\n\n".
	"There is a possibility that some strings will not have been\n".
	"extracted properly, so please be sure to check the catalogs.\n";

1;
