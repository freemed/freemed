#!/usr/bin/perl -I/usr/share/freemed/lib/perl
#	$Id$
#	$Author$
#
#	Composite information over stock PDF forms
#
#	Parameters:
#
#		data - XML data file
#

# Add proper libraries for XML-RPC access and configuration data
use Config::IniFiles;
use PDF::API2;
use XML::Simple;
use Data::Dumper;

# Get configuration file for paths
my $config = new Config::IniFiles ( -file => '/usr/share/freemed/data/config/xmlrpc.ini' );

# Get interval parameter
my $data = shift || die "ERROR: data XML file must be given\n";

# Create heirarchical XML object
my $xs = new XML::Simple (
	NormalizeSpace => 1,
	ForceArray => [ 'page', 'element' ]
);
my $xml = $xs->XMLin($data);

# Create new pdf to work with
my $pdf = PDF::API2->new;
delete $pdf->{forcecompress};

# Loop through pages
my $pagecount = 0;
foreach my $page (@{$xml->{page}}) {
	$pagecount ++;
	process_page($pdf, $xml, $page, $pagecount);
}

my $output = $pdf->stringify;
$pdf->end;
print $output;

#----- Functions ------------------------------------------------------------------------

sub process_page {
	my $pdf = shift;
	my $xml = shift;
	my $pxml = shift;
	my $count = shift;

	# Load template
	my $original = PDF::API2->open('/usr/share/freemed/data/form/pdf/'.$xml->{information}->{pdf});
	$pdf->importpage( $original, $pxml->{oid}, $count);

	$page_height = get_pdf_height('/usr/share/freemed/data/form/pdf/'.$xml->{information}->{pdf});

	# Create text page object
	my $page = $pdf->openpage($count);
	my $txt = $page->text;
	my $gfx = $page->gfx;
	$gfx->strokecolor('#000000');
	my $corefont = $pdf->corefont('Courier', 1);
	$txt->font($corefont, 12);

	# Loop through elements
	foreach my $e (@{$pxml->{element}}) {
		# Set positioning for element
		if ($e->{type} eq 'data') {
			if (!$e->{ysize}) { $e->{ysize} = 12; }
			$txt->translate($e->{xpos}, $page_height - ($e->{ypos} + $e->{ysize}));
			#print "moveto (".$e->{xpos}.", ".($page_height - ($e->{ypos} + $e->{ysize})).")\n";
			$txt->text($e->{data});
			#print "print ( ".$e->{data}." )\n";
		} elsif ($e->{type} eq 'outline') {
			$gfx->move($e->{xpos}, $page_height - ($e->{ypos}));
			$gfx->line($e->{xpos}, $page_height - ($e->{ypos} + $e->{ysize}));
			$gfx->line($e->{xpos} + $e->{xsize}, $page_height - ($e->{ypos} + $e->{ysize}));
			$gfx->line($e->{xpos} + $e->{xsize}, $page_height - ($e->{ypos}));
			$gfx->line($e->{xpos}, $page_height - ($e->{ypos}));
			$gfx->stroke;
			$gfx->endpath();
		}
	}

	# End page and attach
	$txt->compress;
} # end sub process_page

sub get_pdf_height {
	my $pdf_file = shift;

	chomp ( my $raw = `pdfinfo "${pdf_file}" | grep "Page size"` );
	$raw =~ s/  / /g; $raw =~ s/  / /g;
	my @parts = split / /, $raw;
	return $parts[5];
} # end sub get_pdf_height

