package HTML::Entities::Latin2;

use 5.006;
use strict;
use warnings;
use vars qw(*encode_entities);
require Exporter;
our @ISA = qw(Exporter);
our @EXPORT_OK = qw(encode encode_entities);
our $VERSION = '0.04';

my %ascii_entities = (
	'"'		=> ['&#34;', '&#x0022;', '&quot;', "\x{0022}", 'QUOTATION MARK'],
	'&'		=> ['&#38;', '&#x0026;', '&amp;', "\x{0026}", 'AMPERSAND'],
	'\''	=> ['&#39;', '&#x0027;', '&apos;', "\x{0027}", 'APOSTROPHE'],
	'<'		=> ['&#60;', '&#x003C;', '&lt;', "\x{003C}", 'LESS-THAN SIGN'],
	'>'		=> ['&#62;', '&#x003E;', '&gt;', "\x{003E}", 'GREATER-THAN SIGN'],
);

my @char_map = (
	# chr(160) to chr(255)
	['&#160;', '&#x00A0;', '&nbsp;', "\x{00A0}", 'NO-BREAK SPACE'],
	['&#260;', '&#x0104;', '&Aogon;', "\x{0104}", 'LATIN CAPITAL LETTER A WITH OGONEK'],
	['&#728;', '&#x02D8;', '&breve;', "\x{02D8}", 'BREVE'],
	['&#321;', '&#x0141;', '&Lstrok;', "\x{0141}", 'LATIN CAPITAL LETTER L WITH STROKE'],
	['&#164;', '&#x00A4;', '&curren;', "\x{00A4}", 'CURRENCY SIGN'],
	['&#317;', '&#x013D;', '&Lcaron;', "\x{013D}", 'LATIN CAPITAL LETTER L WITH CARON'],
	['&#346;', '&#x015A;', '&Sacute;', "\x{015A}", 'LATIN CAPITAL LETTER S WITH ACUTE'],
	['&#167;', '&#x00A7;', '&sect;', "\x{00A7}", 'SECTION SIGN'],
	['&#168;', '&#x00A8;', '&uml;', "\x{00A8}", 'DIAERESIS'],
	['&#352;', '&#x0160;', '&Scaron;', "\x{0160}", 'LATIN CAPITAL LETTER S WITH CARON'],
	['&#350;', '&#x015E;', '&Scedil;', "\x{015E}", 'LATIN CAPITAL LETTER S WITH CEDILLA'],
	['&#356;', '&#x0164;', '&Tcaron;', "\x{0164}", 'LATIN CAPITAL LETTER T WITH CARON'],
	['&#377;', '&#x0179;', '&Zacute;', "\x{0179}", 'LATIN CAPITAL LETTER Z WITH ACUTE'],
	['&#150;', '&#x00AD;', '&shy;', "\x{00AD}", 'SOFT HYPHEN'],
	['&#381;', '&#x017D;', '&Zcaron;', "\x{017D}", 'LATIN CAPITAL LETTER Z WITH CARON'],
	['&#379;', '&#x017B;', '&Zdot;', "\x{017B}", 'LATIN CAPITAL LETTER Z WITH DOT ABOVE'],
	['&#730;', '&#x00B0;', '&deg;', "\x{00B0}", 'DEGREE SIGN'],
	['&#261;', '&#x0105;', '&aogon;', "\x{0105}", 'LATIN SMALL LETTER A WITH OGONEK'],
	['&#731;', '&#x02DB;', '&ogon;', "\x{02DB}", 'OGONEK'],
	['&#322;', '&#x0142;', '&lstrok;', "\x{0142}", 'LATIN SMALL LETTER L WITH STROKE'],
	['&#714;', '&#x00B4;', '&acute;', "\x{00B4}", 'ACUTE ACCENT'],
	['&#318;', '&#x013E;', '&lcaron;', "\x{013E}", 'LATIN SMALL LETTER L WITH CARON'],
	['&#347;', '&#x015B;', '&sacute;', "\x{015B}", 'LATIN SMALL LETTER S WITH ACUTE'],
	['&#711;', '&#x02C7;', '&caron', "\x{02C7}", 'CARON'],
	['&#184;', '&#x00B8;', '&cedil;', "\x{00B8}", 'CEDILLA'],
	['&#353;', '&#x0161;', '&scaron;', "\x{0161}", 'LATIN SMALL LETTER S WITH CARON'],
	['&#351;', '&#x015F;', '&scedil;', "\x{015F}", 'LATIN SMALL LETTER S WITH CEDILLA'],
	['&#357;', '&#x0165;', '&tcaron;', "\x{0165}", 'LATIN SMALL LETTER T WITH CARON'],
	['&#378;', '&#x017A;', '&zacute;', "\x{017A}", 'LATIN SMALL LETTER Z WITH ACUTE'],
	['&#733;', '&#x02DD;', '&dblac;', "\x{02DD}", 'DOUBLE ACUTE ACCENT'],
	['&#382;', '&#x017E;', '&zcaron;', "\x{017E}", 'LATIN SMALL LETTER Z WITH CARON'],
	['&#380;', '&#x017C;', '&zdot;', "\x{017C}", 'LATIN SMALL LETTER Z WITH DOT ABOVE'],
	['&#340;', '&#x0154;', '&Racute;', "\x{0154}", 'LATIN CAPITAL LETTER R WITH ACUTE'],
	['&#193;', '&#x00C1;', '&Aacute;', "\x{00C1}", 'LATIN CAPITAL LETTER A WITH ACUTE'],
	['&#194;', '&#x00C2;', '&Acirc;', "\x{00C2}", 'LATIN CAPITAL LETTER A WITH CIRCUMFLEX'],
	['&#258;', '&#x0102;', '&Abreve;', "\x{0102}", 'LATIN CAPITAL LETTER A WITH BREVE'],
	['&#196;', '&#x00C4;', '&Auml;', "\x{00C4}", 'LATIN CAPITAL LETTER A WITH UMLAUT'],
	['&#313;', '&#x0139;', '&Lacute;', "\x{0139}", 'LATIN CAPITAL LETTER L WITH ACUTE'],
	['&#262;', '&#x0106;', '&Cacute;', "\x{0106}", 'LATIN CAPITAL LETTER C WITH ACUTE'],
	['&#199;', '&#x00C7;', '&Ccedil;', "\x{00C7}", 'LATIN CAPITAL LETTER C WITH CEDILLA'],
	['&#268;', '&#x010C;', '&Ccaron;', "\x{010C}", 'LATIN CAPITAL LETTER C WITH CARON'],
	['&#201;', '&#x00C9;', '&Eacute;', "\x{00C9}", 'LATIN CAPITAL LETTER E WITH ACUTE'],
	['&#280;', '&#x0118;', '&Eogon;', "\x{0118}", 'LATIN CAPITAL LETTER E WITH OGONEK'],
	['&#203;', '&#x00CB;', '&Euml;', "\x{00CB}", 'LATIN CAPITAL LETTER E WITH UMLAUT'],
	['&#282;', '&#x011A;', '&Ecaron;', "\x{011A}", 'LATIN CAPITAL LETTER E WITH CARON'],
	['&#205;', '&#x00CD;', '&Iacute;', "\x{00CD}", 'LATIN CAPITAL LETTER I WITH ACUTE'],
	['&#206;', '&#x00CE;', '&Icirc;', "\x{00CE}", 'LATIN CAPITAL LETTER I WITH CIRCUMFLEX'],
	['&#270;', '&#x010E;', '&Dcaron;', "\x{010E}", 'LATIN CAPITAL LETTER D WITH CARON'],
	['&#272;', '&#x0110;', '&Dstrok;', "\x{0110}", 'LATIN CAPITAL LETTER D WITH STROKE'],
	['&#323;', '&#x0143;', '&Nacute;', "\x{0143}", 'LATIN CAPITAL LETTER N WITH ACUTE'],
	['&#327;', '&#x0147;', '&Ncaron;', "\x{0147}", 'LATIN CAPITAL LETTER N WITH CARON'],
	['&#211;', '&#x00D3;', '&Oacute;', "\x{00D3}", 'LATIN CAPITAL LETTER O WITH ACUTE'],
	['&#212;', '&#x00D4;', '&Ocirc;', "\x{00D4}", 'LATIN CAPITAL LETTER O WITH CIRCUMFLEX'],
	['&#336;', '&#x0151;', '&Odblac;', "\x{0151}", 'LATIN CAPITAL LETTER O WITH DOUBLE ACUTE'],
	['&#214;', '&#x00D6;', '&Ouml;', "\x{00D6}", 'LATIN CAPITAL LETTER O WITH UMLAUT'],
	['&#215;', '&#x00D7;', '&times;', "\x{00D7}", 'MULTIPLICATION SIGN'],
	['&#344;', '&#x0158;', '&Rcaron;', "\x{0158}", 'LATIN CAPITAL LETTER R WITH CARON'],
	['&#366;', '&#x016E;', '&Uring;', "\x{016E}", 'LATIN CAPITAL LETTER U WITH RING ABOVE'],
	['&#218;', '&#x00DA;', '&Uacute;', "\x{00DA}", 'LATIN CAPITAL LETTER U WITH ACUTE'],
	['&#368;', '&#x0170;', '&Udblac;', "\x{0170}", 'LATIN CAPITAL LETTER U WITH DOUBLE ACUTE'],
	['&#220;', '&#x00DC;', '&Uuml;', "\x{00DC}", 'LATIN CAPITAL LETTER U WITH UMLAUT'],
	['&#221;', '&#x00DD;', '&Yacute;', "\x{00DD}", 'LATIN CAPITAL LETTER Y WITH ACUTE'],
	['&#354;', '&#x0162;', '&Tcedil;', "\x{0162}", 'LATIN CAPITAL LETTER T WITH CEDILLA'],
	['&#223;', '&#x00DF;', '&szlig;', "\x{00DF}", 'LATIN SMALL LETTER SHARP S'],
	['&#341;', '&#x0155;', '&racute;', "\x{0155}", 'LATIN SMALL LETTER R WITH ACUTE'],
	['&#225;', '&#x00E1;', '&aacute;', "\x{00E1}", 'LATIN SMALL LETTER A WITH ACUTE'],
	['&#226;', '&#x00E2;', '&acirc;', "\x{00E2}", 'LATIN SMALL LETTER A WITH CIRCUMFLEX'],
	['&#259;', '&#x0103;', '&abreve;', "\x{0103}", 'LATIN SMALL LETTER A WITH BREVE'],
	['&#228;', '&#x00E4;', '&auml;', "\x{00E4}", 'LATIN SMALL LETTER A WITH UMLAUT'],
	['&#314;', '&#x013A;', '&lacute;', "\x{013A}", 'LATIN SMALL LETTER L WITH ACUTE'],
	['&#263;', '&#x0107;', '&cacute;', "\x{0107}", 'LATIN SMALL LETTER C WITH ACUTE'],
	['&#231;', '&#x00E7;', '&ccedil;', "\x{00E7}", 'LATIN SMALL LETTER C WITH CEDILLA'],
	['&#269;', '&#x010D;', '&ccaron;', "\x{010D}", 'LATIN SMALL LETTER C WITH CARON'],
	['&#233;', '&#x00E9;', '&eacute;', "\x{00E9}", 'LATIN SMALL LETTER E WITH ACUTE'],
	['&#281;', '&#x0119;', '&eogon;', "\x{0119}", 'LATIN SMALL LETTER E WITH OGONEK'],
	['&#235;', '&#x00EB;', '&euml;', "\x{00EB}", 'LATIN SMALL LETTER E WITH UMLAUT'],
	['&#283;', '&#x011B;', '&ecaron;', "\x{011B}", 'LATIN SMALL LETTER E WITH CARON'],
	['&#237;', '&#x00ED;', '&iacute;', "\x{00ED}", 'LATIN SMALL LETTER I WITH ACUTE'],
	['&#238;', '&#x00EE;', '&icirc;', "\x{00EE}", 'LATIN SMALL LETTER I WITH CIRCUMFLEX'],
	['&#271;', '&#x010F;', '&dcaron;', "\x{010F}", 'LATIN SMALL LETTER D WITH CARON'],
	['&#273;', '&#x0111;', '&dstrok;', "\x{0111}", 'LATIN SMALL LETTER D WITH STROKE'],
	['&#324;', '&#x0144;', '&nacute;', "\x{0144}", 'LATIN SMALL LETTER N WITH ACUTE'],
	['&#328;', '&#x0148;', '&ncaron;', "\x{0148}", 'LATIN SMALL LETTER N WITH CARON'],
	['&#243;', '&#x00F3;', '&oacute;', "\x{00F3}", 'LATIN SMALL LETTER O WITH ACUTE'],
	['&#244;', '&#x00F4;', '&ocirc;', "\x{00F4}", 'LATIN SMALL LETTER O WITH CIRCUMFLEX'],
	['&#337;', '&#x0151;', '&odblac;', "\x{0151}", 'LATIN SMALL LETTER O WITH DOUBLE ACUTE'],
	['&#246;', '&#x00F6;', '&ouml;', "\x{00F6}", 'LATIN SMALL LETTER O WITH UMLAUT'],
	['&#247;', '&#x00F7;', '&divide;', "\x{00F7}", 'DIVISION SIGN'],
	['&#345;', '&#x0159;', '&rcaron;', "\x{0159}", 'LATIN SMALL LETTER R WITH CARON'],
	['&#367;', '&#x016F;', '&uring;', "\x{016F}", 'LATIN SMALL LETTER U WITH RING ABOVE'],
	['&#250;', '&#x00FA;', '&uacute;', "\x{00FA}", 'LATIN SMALL LETTER U WITH ACUTE'],
	['&#369;', '&#x0171;', '&udblac;', "\x{0171}", 'LATIN SMALL LETTER U WITH DOUBLE ACUTE'],
	['&#252;', '&#x00FC;', '&uuml;', "\x{00FC}", 'LATIN SMALL LETTER U WITH UMLAUT'],
	['&#253;', '&#x00FD;', '&yacute;', "\x{00FD}", 'LATIN SMALL LETTER Y WITH ACUTE'],
	['&#355;', '&#x0163;', '&tcedil;', "\x{0163}", 'LATIN SMALL LETTER T WITH CEDILLA'],
	['&#183;', '&#x02D9;', '&dot;', "\x{02D9}", 'DOT ABOVE'],
);

sub encode {
	my($source_str, $scheme_name, $unsafe) = @_;
   
	my $scheme = {
		decimal=>0, number=>0, numeric=>0, 'hex'=>1, name=>2, named=>2, utf8=>3, description=>4
	}->{lc($scheme_name)};
	
	$scheme = 0 unless defined $scheme; # defaults to decimal/numeric entities
	
	my %unsafe = ();
	if ($unsafe) {
		foreach (split //, $unsafe) {
			if (defined $ascii_entities{$_}) { $unsafe{ord $_} = $ascii_entities{$_}; } 
		}
	}

	my $encoded = '';
	foreach my $char_val (unpack('C*', $source_str)) {
		if ($char_val < 127) { # ASCII character
			if (defined $unsafe{$char_val}) {
				$encoded .= $unsafe{$char_val}->[$scheme];
			}
			else { $encoded .= chr $char_val; }
		}
		elsif ($char_val >= 160) {
			$encoded .= $char_map[$char_val - 160]->[$scheme];
		}
		else {
			warn 'character not in Latin-2 map, character code: '.$char_val;
		}
	}
	return $encoded;
}

*encode_entities = \&encode;

1;
__END__

=head1 NAME

HTML::Entities::Latin2 - Encode ISO-8859-2 characters into HTML entities.

=head1 SYNOPSIS

	use HTML::Entities::Latin2;

	$lat2_string = "\"k\xF6zponti\" <b>sz\xE1m\xEDt\xF3g\xE9p</b>";
	
	print HTML::Entities::Latin2::encode($lat2_string);
	# "k&#246;zponti" <b>sz&#225;m&#237;t&#243;g&#233;p</b>
	
	print HTML::Entities::Latin2::encode($lat2_string, 'name', '<"');
	# &quot;k&ouml;zponti&quot; &lt;b>sz&aacute;m&iacute;t&oacute;g&eacute;p&lt;/b>
	
	print HTML::Entities::Latin2::encode($lat2_string, 'hex');
	# "k&#x00F6;zponti" <b>sz&#x00E1;m&#x00ED;t&#x00F3;g&#x00E9;p</b>


=head1 DESCRIPTION

Translate high-bit Latin2 characters into HTML entities based on the ISO-8859-2
character map, with option of using named, decimal or hex entities. Using
this process will allow Eastern European encoded text to be used in ASCII
HTML pages.

=head2 FUNCTIONS

encode($latin2_string, $encoding_scheme, $unsafe_chars);

=head1 SEE ALSO

HTML::Entities
http://czyborra.com/charsets/iso8859.html#ISO-8859-2
http://www.w3schools.com/html/html_entitiesref.asp
http://www.microsoft.com/globaldev/reference/iso/28592.htm

=head1 AUTHOR

Michael J. Mathews, michael@perlinpractice.com

=head1 COPYRIGHT AND LICENSE

Copyright 2005 Michael J. Mathews. All rights reserved.

This library is free software; you can redistribute it and/or
modify it under the same terms as Perl itself.

=head1 CAVEATS

This module has only been tested on Unix, Perl 5.6.1, and 5.8.1.

=cut