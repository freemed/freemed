<?php
	// $Id$
	// $Author$

// Class: FreeMED.TeX
//
//	LaTeX rendering class.
//
class TeX {

	var $_buffer; // Internal buffer for creating TeX file
	var $options;

	function TeX ( $options = NULL ) {
		global $this_user;
		if (!is_object($this_user)) { $this_user = CreateObject('_FreeMED.User'); }

		// Pass options to internal array
		if (is_array($options)) {
			$this->options = $options;
		}
	} // end constructor

	// Method: PrintTeX
	//
	//	Prints a certain number of copies of the current TeX
	//	document.
	//
	// Parameters:
	//
	//	$copies - (optional) Number of copies of the current TeX
	//	document to print. Defaults to 1.
	//
	//	$rendered - (optional) Passed to <RenderToPDF>.
	//
	function PrintTeX ( $copies = 1, $rendered = false ) {
		$file = $this->RenderToPDF($rendered);

		// Render to postscript, since CUPS doesn't like PDF
		`( cd /tmp; pdf2ps $file $file.ps )`;

		// Check for a wrapper
		if (!is_object($this->wrapper)) { 
			syslog(LOG_INFO,"FreeMED.TeX|Failed to open wrapper class in PrintTex()");
			return false; 
		}

		// Loop for copies
		for ($i=1;$i<=$copies;$i++) {
			syslog(LOG_INFO,"FreeMED.TeX|Printing file $file.ps");
			$this->wrapper->driver->PrintFile($this->printer, $file.'.ps');
		}

		// Remove temporary files
		unlink($file);
		unlink($file.'.ps');

		// This is correct, so we return true
		return true;
	} // end method Print

	function RenderDebug ( $rendered = false ) {
		$buffer .= $this->_buffer;
		print "<pre>\n";
		print $buffer;
		print "</pre>\n";
	} // end method RenderDebug

	// Method: RenderFromTemplate
	//
	//	Renders a TeX document with substitutions from a TeX
	//	template in the lib/tex/ directory.
	//
	// Parameters:
	//
	//	$template - Name of the template to be used. For
	//	lib/tex/rx.tex this would be 'rx';
	//
	//	$rec - Associative array of primary record data.
	//
	// Returns:
	//
	//	TeX document (not rendered into a result format).
	//
	function RenderFromTemplate ( $template, $rec ) {
		if (!file_exists('lib/tex/'.$template.'.tex')) {
			die("Could not load $template TeX template.");
		}

		// Set iffi status to false
		$this->iffi = false;
		
		// Initial load of file
		$fp = fopen('lib/tex/'.$template.'.tex', 'r');
		if (!$fp) {
			die("Could not open $template TeX template.");
		}
		$buffer = '';
		while (!feof($fp)) { $buffer .= fgets($fp, 4096); }
		fclose ($fp);

		// Second-generation macros
		// 	**macro**
		if (!(strpos($buffer, '**') === false)) {
			$chunks = explode('**', $buffer);
			$buffer = ''; // overwrite what we had
			foreach ($chunks AS $k => $v) {
				if (!($k & 1)) {
					// Non macro, copy verbosely
					if (!$this->iffi) { $buffer .= $v; }
				} else {
					// Call out to buffer function
					$tmp = $this->_ParseTag($rec, $v);
					if (!$this->iffi) { $buffer .= $tmp; }
				}
			} // end foreach
		} // end checking if there are macros

		// Return processed string
		return $buffer;
	} // end method RenderFromTemplate

	// Method: RenderToPDF
	//
	//	Render to PDF and get file name of temporary file
	//
	// Parameters:
	//
	//	$rendered - (optional) Boolean. Set to true if header
	//	and footer are to be supressed.
	//
	// Returns:
	//
	//	Name of temporary file.
	//
	function RenderToPDF ( $rendered = false ) {
		$buffer .= $this->_buffer;
		
		$tmp = tempnam('/tmp', 'fmtex');

		// Send data to $tmp.ltx
		$fp = fopen ($tmp.'.ltx', 'w');
		fwrite ($fp, $buffer, strlen($buffer));
		fclose ($fp);

		// Execute pdflatex rendering
		// (twice for appropriate page numbering)
		`( cd /tmp; pdflatex --interaction nonstopmode "$tmp.ltx" "$tmp.pdf" )`;
		`( cd /tmp; pdflatex --interaction nonstopmode "$tmp.ltx" "$tmp.pdf" )`;

		// Remove intermediary step file(s)
		unlink($tmp);
		unlink($tmp.'.ltx');
		unlink($tmp.'.log');
		unlink($tmp.'.aux');

		return ($tmp.'.pdf');
	} // end method RenderToPDF

	// Method: SetPrinter
	//
	//	Sets the printer and printer wrapper for this TeX document
	//	to use when printing.
	//
	// Parameters:
	//
	//	$wrapper - PHP.PrinterWrapper object
	//
	//	$printer - Text name of the printer to use
	//
	function SetPrinter ( $wrapper, $printer ) {
		$this->wrapper = $wrapper;
		$this->printer = $printer;
	} // end method SetPrinter

	//----------- Internal Methods -----------------------------------

	// Method: _HTMLToRichText
	//
	//	Convert SGML/HTML formatted "rich text" to LaTeX-formatted
	//	rich text, while sanitizing.
	//
	// Parameters:
	//
	//	$orig - Original marked up string
	//
	// Returns:
	//
	//	LaTeX-style rich text
	//
	function _HTMLToRichText ( $orig ) {
		// Sanitize all but HTML markers
		$text = $this->_SanitizeText($orig, true);
		$text = str_replace('\\\\', '\\', $text); // kill double slashes

		// Remove leading CRs if present (mucks with the formatting)
		if (substr($text, 0, 1) == "\n") {
			while (substr($text, 0, 1) == "\n") {
				$text = substr($text, -(strlen($text) - 1));
			}
		}

		// Remove ending <br /><br /> from Gecko (thanks to Volker)
		if (substr($text, -13) == "<br /><br />\n") {
			$text = substr($text, 0, strlen($text)-13);
		}

		// Thanks to Adam for the Perl regular expressions to
		// do all of this work ....

		// Deal with whitespace
		$text = preg_replace("#<\s*#i", '<', $text);
		$text = preg_replace("#\s*>#i", '>', $text);
		$text = preg_replace("#<[^>\S]*/\s*#i", '</', $text);

		// Format paragraph breaks properly
		$text = preg_replace("#\s*<P>#i", "\n\n", $text);

		// Format tags, one by one
		$text = preg_replace("#<B>(.*?)</B>#i", '\textbf{$1}', $text);
		$text = preg_replace("#<I>(.*?)</I>#i", '\textit{$1}', $text);
		$text = preg_replace("#<U>(.*?)</U>#i", '\underline{$1}', $text);

		// Switch BR and SPAN tags (fix for HTMLArea JS)
		while (preg_match("#<BR\s/></SPAN>#i", $text)) {
			$text = preg_replace("#<BR\s/></SPAN>#i", "</span><br />", $text);
		}
		$text = preg_replace("#<SPAN\sSTYLE=\"FONT\-WEIGHT:\sBOLD\;\"><BR\s/>#i", "<br /><span style=\"font-weight: bold;\">", $text);

		// Remove empty tags from HTMLarea malformatting
		$text = str_replace('<span style="font-weight: bold;"></span>', '', $text);
		$text = str_replace('<b></b>', '', $text);
		$text = str_replace('<strong></strong>', '', $text);
		$text = str_replace('<span style="font-style: italic;"></span>', '', $text);
		$text = str_replace('<i></i>', '', $text);
		$text = str_replace('<em></em>', '', $text);
		$text = str_replace('<span style="font-decoration: underline;"></span>', '', $text);
		$text = str_replace('<u></u>', '', $text);
		$text = preg_replace("#<U>\s<BR\s/>\s</U>#i", "", $text);

		// Also do "SPAN" tags, which are put out by HTMLarea JS,
		// and STRONG/EM tags which IE puts out
		$text = preg_replace("#<SPAN\sSTYLE=\"FONT\-WEIGHT:\sBOLD\;\">(.*?)</SPAN>#i", '\textbf{$1}', $text);
		$text = preg_replace("#<STRONG>(.*?)</STRONG>#i", '\textbf{$1}', $text);
		$text = preg_replace("#<SPAN\sSTYLE=\"FONT\-STYLE:\sITALIC\;\">(.*?)</SPAN>#i", '\textit{$1}', $text);
		$text = preg_replace("#<EM>(.*?)</EM>#i", '\textit{$1}', $text);
		$text = preg_replace("#<SPAN\sSTYLE=\"TEXT\-DECORATION:\sUNDERLINE\;\">(.*?)</SPAN>#i", '\underline{$1}', $text);

		// And combination <B><U> things sadly need to be handled...
		$text = preg_replace("#<SPAN\sSTYLE=\"FONT\-WEIGHT:\sBOLD\;\sTEXT\-DECORATION:\sUNDERLINE\;\">(.*?)</SPAN>#i", '\textbf{\underline{$1}}', $text);
		$text = preg_replace("#<SPAN\sSTYLE=\"TEXT\-DECORATION:\sUNDERLINE\;\sFONT\-WEIGHT:\sBOLD\;\">(.*?)</SPAN>#i", '\textbf{\underline{$1}}', $text);

		// Do something about <br /> and <br> tags (<br /> are used
		// by HTMLarea JS). For now, we treat them as though they
		// were paragraph breaks. What is the proper way to handle
		// these?
		$text = preg_replace("#\s*<BR\s/><BR\s/>#i", "\n\\medskip\n", $text);
		$text = preg_replace("#\s*<BR\s/>#i", " \n", $text);
		$text = preg_replace("#\s*<BR>#i", " \n", $text);
	
		// Remove all tags we can't understand
		$text = preg_replace("#<[^>]*?>#i", '', $text);

		// Sanitize out quotes
		$text = $this->_ReplaceQuotes($text);
	
		// Handle embedded CRs... for now we treat them as line
		// breaks
		$text = str_replace("\n", "\\  \\\\\n", $text);

		return $text;
	} // end method _HTMLToRichText

	// Method: _ParseTag
	//
	//	Parse appropriate information from tag to convert into output
	//	data to be passed back to TeX.
	//
	// Parameters:
	//
	//	$rec - Associative array of data to be used for processing.
	//
	//	$tag - Text of the tag to be processed.
	//
	// Returns:
	//
	//	TeX formatted text.
	//
	function _ParseTag ( $rec, $tag ) {
		// Seperate the tag by : seperated parameters
		$params = explode ( ':', $tag );

		// Deal with this by the first parameter, explaining type
		switch ($params[0]) {
			case 'if':
				// Format:
				//	if:(conditiontype):(var):(test?)
				// Needs to be broken with a 'fi' tag
				switch ($params[1]) {
					case 'equals':
					$p = explode(',', $params[3]);
					foreach ($p AS $my_p) {
						if (!($rec[$params[2]] == $my_p)) { $this->iffi = true; }
					}
					break;

					case 'not':
					if ($rec[$params[2]]) { $this->iffi = true; }
					break;
					
					case 'linkexists':
					$p = explode(',', $params[2]);
					$linkrec = freemed::get_link_rec($rec[$p[0]], $p[1]);
					if (!$linkrec[$params[3]]) { $this->iffi = true; }
					break;

					default:
					if (!$rec[$params[2]]) { $this->iffi = true; }
					break;
				}
				return ''; // need to send blanks back
				break; // end if

			case 'fi':
				// Format:
				//	fi
				// Breaks an 'if' tag
				$this->iffi = false;
				return ''; // need to send blanks back
				break; // end if

			case 'method':
				// Format:
				//	method:(class):(method):(objparameter)
				$ids = explode(',', $rec[$params[3]]);
				foreach ($ids AS $v) {
					$obj = CreateObject('_FreeMED.'.$params[1], $v);
					$values[] = call_user_method($params[2], $obj);
					unset ($obj);
				}
				return $this->_SanitizeText(join(', ', $values));
				break; // end method

			case 'module':
				// Format:
				//	module:(module):(method):(fieldtopass)
				return $this->_SanitizeText(
					module_function (
						$params[1],
						$params[2],
						array (
							$rec[$params[3]]
						)
					)
				);
				break; // end module

			case 'field':
				// Format:
				//	field:(fieldname):(optionalformatting)
				switch ($params[2]) {
					case 'html':
					return '\dohtml'."\n".
						'<html>'."\n".
						$rec[$params[1]]."\n".
						'</html>'."\n";
					break;

					case 'date':
					return $this->_SanitizeText(fm_date_print($rec[$params[1]]));
					break;

					case 'ssn':
					return $this->_SanitizeText('('.substr($rec[$params[1]], 0, 3).'-'.substr($rec[$params[1]], 3, 2).'-'.substr($rec[$params[1]], 5, 4));
					break;

					case 'phone':
					return $this->_SanitizeText('('.substr($rec[$params[1]], 0, 3).') '.substr($rec[$params[1]], 3, 3).'-'.substr($rec[$params[1]], 6, 4));
					break;

					case 'fixcase':
					return $this->_SanitizeText($this->_FixCase($rec[$params[1]]));
					break;

					default:
					return $this->_HTMLToRichText($rec[$params[1]]);
					break;
				}
				break; // end field

			case 'link':
				// Format:
				//	link:(fieldname):(table):(targetfield):(optionalformatting)
				$linkrec = freemed::get_link_rec($rec[$params[1]], $params[2]);
				switch ($params[4]) {
					case 'date':
					return $this->_SanitizeText(fm_date_print($linkrec[$params[3]]));
					break;

					case 'phone':
					$ph = $linkrec[$params[3]];
					return $this->_SanitizeText('('.substr($ph, 0, 3).') '.substr($ph, 3, 3).'-'.substr($ph, 6, 4));
					break;

					case 'ssn':
					$ssn = $linkrec[$params[3]];
					return $this->_SanitizeText('('.substr($ssn, 0, 3).'-'.substr($ssn, 3, 2).'-'.substr($ssn, 5, 4));
					break;

					case 'multiple':
					$ids = explode(',', $rec[$params[1]]);
					foreach ($ids AS $v) {
						$linkrec = freemed::get_link_rec($v, $params[2]);
						
						$values[] = $this->_HTMLToRichText($linkrec[$params[3]]);
					}
					return join(', ', $values);
					break;

					case 'fixcase':
					return $this->_HTMLToRichText($this->_FixCase($linkrec[$params[3]]));
					break;

					default:
					return $this->_HTMLToRichText($linkrec[$params[3]]);
					break;
				} // end optional formatting switch
				break; // end field

			default: // unhandled
				return "(unhandled tag)";
				break;
		} // end case params
		die("Should never reach here");
	} // end method _ParseTag

	// Method: _FixCase
	//
	//	Fix capitalization with what we think is "natural"
	//	capitalization.
	//
	// Parameters:
	//
	//	$string - Original string
	//
	// Returns:
	//
	//	String with appropriate capitalization.
	//
	function _FixCase ( $string ) {
		// Simple substitutions
		$subs = array (
			'Ii' => 'II', // The second
			'Iii' => 'III', // The third
			'Iv' => 'IV', // The fourth
			'Po' => 'PO', // PO Box 
			'St' => 'St.', // Street/Saint
			'Nh' => 'NH', // New Hampshire abbrev
			'Us' => 'US', // United States route abbrev
		);
		$a = explode(' ', $string);
		foreach ($a AS $k => $v) {
			$a[$k] = ucfirst(strtolower($v));

			// Handle obvious substitutions
			foreach ($subs AS $s_k => $s_v) {
				if ($a[$k] == $s_k) { $a[$k] = $s_v; }
			}

			// Handle McDonald and kin
			if ((substr($a[$k], 0, 2) == 'Mc') and (strlen($a[$k])>3)) { 
				$a[$k] = 'Mc' . ucfirst(strtolower(substr($a[$k], -(strlen($a[$k])-2) )));
			}

			// Handle rural routes
			if (substr($a[$k], 0, 2) == 'Rr') { 
				$a[$k] = strtoupper($a[$k]);
			}
			
			// Handle things like 212B Baker Street
			if ((substr($a[$k], 0, 1) + 0) > 0) {
				$a[$k] = strtoupper($a[$k]);
			}
		} // end foreach part of the string
		return join(' ', $a);
	} // end method _FixCase

	// Method: _ReplaceQuotes
	//
	//	Replace quotes with proper beginning and ending quotation
	//	marks, TeX-style.
	//
	// Parameters:
	//
	//	$string - String to be mucked with.
	//
	// Returns:
	//
	//	TeX-quoted string
	//
	function _ReplaceQuotes ( $string ) {
		// Skip if there are no quotes
		if (strpos($string, '"') === false) { return $string; }

		$quotetype = 0;
		for ($i=0; $i<strlen($string); $i++) {
			if (substr($string, $i, 1) == '"') {
				switch ($quotetype) {
					case 0:
					$output .= '``';
					$quotetype = 1;
					break;

					case 1:
					$output .= '\'\'';
					$quotetype = 0;
					break;
				}
			} else {
				$output .= substr($string, $i, 1);
			}
		}
		return $output;
	} // end method _ReplaceQuotes

	// Method: _SanitizeText
	//
	//	Escapes offending TeX control sequences.
	//
	// Parameters:
	//
	//	$text - Text to be sanitized
	//
	//	$skip_html - (optional) Whether or not to skip HTML
	//	specific escape sequences. This is useful if presenting
	//	rich text markup (which uses SGML/HTML tags) to the
	//	renderer. Defaults to false.
	//
	// Returns:
	//
	//	Text that can be cleanly inserted into TeX code.
	//
	function _SanitizeText ( $text, $skip_html=false ) {
		$string = stripslashes($text);

		// First, sanitize escape character
		$string = str_replace('\\', '\\\\', $string);

		// Get rid of \r character
		$string = str_replace("\r", "", $string);

		// Sanitize {, } (do NOT escape [ and ])
		$string = str_replace('{', '\lbrace\ ', $string);
		$string = str_replace('}', '\lbrace\ ', $string);
		$string = str_replace('[', '\lbrack\ ', $string);
		$string = str_replace(']', '\rbrack\ ', $string);

		// Make sure dollar sign escaping used before $+$ escaped
		$string = str_replace('$', '\$', $string);

		// Get rid of #, _, %, +
		$string = str_replace('#', '\#', $string);
		$string = str_replace('_', '\_', $string);
		$string = str_replace('%', '\%', $string);
		$string = str_replace('+', '$+$', $string);

		// Deal with amphersands, and &quot; &amp; stuff
		$string = str_replace('&quot;', '"', $string);
		$string = str_replace('&amp;', '&', $string);
		$string = str_replace('&lt;', '$<$', $string);
		$string = str_replace('&gt;', '$>$', $string);
		$string = str_replace('&', '\&', $string);
		$string = str_replace('&nbsp;', '\ ', $string);

		// HTML/SGML specific texts
		if (!$skip_html) {
			// This one isn't *really* right, since there are
			// technically opening and closing quotes, but it
			// will do for now.
			$string = $this->_ReplaceQuotes($string);

			$string = str_replace('<', '\<', $string);
			$string = str_replace('>', '\>', $string);
		}

		// Return processed string
		return $string;
	} // end method _SanitizeText

} // end class TeX

?>
