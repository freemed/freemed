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

	// Variable: $this->stock_macros
	//
	//	Associative array defining fixed macros for TeX templates
	//	which are set by default. The key specifies the name of
	//	the macro and the value sets the destination.
	//
	var $stock_macros;

	function TeX ( $options = NULL ) {
		$this->stock_macros = array (
			'PACKAGENAME' => PACKAGENAME,
			'VERSION' => DISPLAY_VERSION,
			'INSTALLATION' => INSTALLATION
		);

		// Pass options to internal array
		if (is_array($options)) {
			$this->options = $options;
		}
	} // end constructor

	// Method: AddLongItem
	//
	//	Add a large item to a FreeMED report document
	//
	// Parameters:
	//
	//	$title - The text title of the specified item
	//
	//	$item - HTML marked up "rich text" to be displayed
	//
	// See Also:
	//	<AddLongItems>
	//
	function AddLongItem ( $title, $item ) {
		$CRLF = "\n"; 
		// Handle no item title
		if (empty($title)) {
			$this->_buffer .= '\\section*{}'.$CRLF.
				$this->_HTMLToRichText($item).$CRLF.
				$CRLF;
		} else {
			$this->_buffer .= '\\section*{\\headingbox{'.
				$this->_SanitizeText($title).'}}'.$CRLF.
				$this->_HTMLToRichText($item).$CRLF.
				$CRLF;
		}
	} // end method AddLongItem

	// Method: AddLongItems
	//
	//	Adds multiple "long items" to a FreeMED report
	//	document
	//
	// Parameters:
	//
	//	$items - Associative array where keys represent the
	//	titles of the items and values represent their texts
	//
	// See Also:
	//	<AddLongItem>
	//
	function AddLongItems ( $items ) {
		if (!is_array($items)) return false;
		foreach ($items AS $title => $item) {
			$this->AddLongItem ( $title, $item );
		}
	} // end method AddLongItems

	// Method: AddShortItems
	//
	//	Adds multiple "short items" to a FreeMED report
	//	document
	//
	// Parameters:
	//
	//	$items - Associative array where keys represent the
	//	titles of the items and values represent their texts
	//
	function AddShortItems ( $items, $_options = NULL ) {
		$CRLF = "\n"; 
		$this->_buffer .= '\\begin{description}'.$CRLF;
		foreach ($items AS $k => $v) {
			$this->_buffer .= '  \\item[\\headingbox{'.
				$this->_SanitizeText($k).'}] '.
				$this->_SanitizeText($v).$CRLF;
		}
		$this->_buffer .= '\\end{description}'.$CRLF;
		$this->_buffer .= $CRLF;
	} // end method AddShortItems

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
		if (!$rendered) { $buffer .= $this->_CreateTeXHeader(); }
		$buffer .= $this->_buffer;
		if (!$rendered) { $buffer .= $this->_CreateTeXFooter(); }
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
	//	$macros - Associative array of substitutions, in the
	//	form of the key being the macro name, and the value
	//	being the value to be substituted for it.
	//
	// Returns:
	//
	//	TeX document (not rendered into a result format).
	//
	function RenderFromTemplate ( $template, $macros ) {
		if (!file_exists('lib/tex/'.$template.'.tex')) {
			die("Could not load $template TeX template.");
		}
		
		// Initial load of file
		$fp = fopen('lib/tex/'.$template.'.tex', 'r');
		if (!$fp) {
			die("Could not open $template TeX template.");
		}
		$buffer = '';
		while (!feof($fp)) { $buffer .= fgets($fp, 4096); }
		fclose ($fp);

		// Form superset
		$_macros = array_merge($this->stock_macros, $macros);

		// Substitutions
		foreach ($_macros AS $k => $v) {
			if (!empty($k) and !empty($v)) {
				$buffer = str_replace('{{'.$k.'}}', $v, $buffer);
			} elseif (!empty($k)) {
				// Broken behavior? Remove missing macros
				$buffer = str_replace('{{'.$k.'}}', '', $buffer);
			}
		}

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
		if (!$rendered) { $buffer .= $this->_CreateTeXHeader(); }
		$buffer .= $this->_buffer;
		if (!$rendered) { $buffer .= $this->_CreateTeXFooter(); }
		
		$tmp = tempnam('/tmp', 'fmtex');

		// Send data to $tmp.ltx
		$fp = fopen ($tmp.'.ltx', 'w');
		fwrite ($fp, $buffer);
		fclose ($fp);

		// Execute pdflatex rendering
		// (twice for appropriate page numbering)
		`( cd /tmp; pdflatex $tmp.ltx $tmp.pdf )`;
		`( cd /tmp; pdflatex $tmp.ltx $tmp.pdf )`;

		// Remove intermediary step file(s)
		unlink($tmp);
		//unlink($tmp.'.ltx');
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

	function _CreateTeXHeader ( ) {
		$CRLF = "\n";
		return '%%'.$CRLF.
			'%% Output generated by LaTeX Renderer'.$CRLF.
			'%% '.PACKAGENAME.' v'.VERSION.$CRLF.
			'%%'.$CRLF.
			'\\documentclass[10pt,letterpaper]{article}'.$CRLF.
			$CRLF.
			'\\newcommand{\\headingbox}[1]{\\fbox{\\sc #1}}'.$CRLF.
			$CRLF.
			'\\usepackage{courier} % For tt support'.$CRLF.
			'\\usepackage{lastpage}'.$CRLF.
			'\\usepackage{supertabular}'.$CRLF.
			'\\usepackage{fancyhdr}'.$CRLF.
			'\\usepackage[left=0.5in,right=0.5in,top=0.5in,bottom=1.2in,nohead,nofoot]{geometry}'.$CRLF.
			'\\usepackage{relsize}'.$CRLF.
			$CRLF.
			'% Define header and footer'.$CRLF.
			$CRLF.
			'\\lhead{'.$CRLF.
			' \\framebox[\\textwidth]{'.$CRLF.
			'  \\relsize{1}'.$CRLF.
			'  \\begin{tabular*}{\\textwidth}[t]{l@{\\extracolsep{\\fill}}r}'.$CRLF.
			'   \\textbf{'.$this->_SanitizeText(INSTALLATION).'} & '.$CRLF.
			'     '.$this->_HTMLToRichText($this->options['heading']).' \\\\ '.$CRLF.
			'   '.$this->_HTMLToRichText($this->options['title']).' & '.$CRLF.
			'     '.sprintf(__("Page %s of %s"), '\\thepage\\', '\\pageref{LastPage}').' \\\\'.$CRLF.
			'   '.$this->_SanitizeText($this->options['physician']).' & \\today \\\\'.$CRLF.
			'  \\end{tabular*}'.$CRLF.
			' }'.$CRLF.
			'}'.$CRLF.
			$CRLF.
			'\\cfoot{\\textsl{'.PACKAGENAME.' v'.VERSION.'}}'.$CRLF.
			$CRLF.
			'\\renewcommand{\\headrulewidth}{0pt}'.$CRLF.
			'\\renewcommand{\\footrulewidth}{0.5pt}'.$CRLF.
			'\\setlength{\\topskip}{7ex}'.$CRLF.
			'\\setlength{\\headheight}{9ex}'.$CRLF.
			'\\setlength{\\footskip}{3ex}'.$CRLF.
			$CRLF.
			'\\begin{document}'.$CRLF.
			'\\pagestyle{fancy}'.$CRLF.
			$CRLF;
	} // end method _CreateTeXHeader

	function _CreateTeXFooter ( ) {
		$CRLF = "\n";
		return '\\end{document}'.$CRLF.
			$CRLF;
	} // end method _CreateTeXFooter

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
	function _HTMLToRichText ( $orig ) {
		// Sanitize all but HTML markers
		$text = $this->_SanitizeText($orig, true);

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
		$text = preg_replace("#<BR\s/></SPAN>#i", "</span><br />", $text);
		$text = preg_replace("#<BR\s/></SPAN>#i", "</span><br />", $text);
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

		// Handle embedded CRs... for now we treat them as line
		// breaks
		$text = str_replace("\n", "\\  \\\\\n", $text);

		// Do something about <br /> and <br> tags (<br /> are used
		// by HTMLarea JS). For now, we treat them as though they
		// were paragraph breaks. What is the proper way to handle
		// these?
		$text = preg_replace("#\s*<BR\s/><BR\s/>#i", "\n\n\\bigskip\n\n", $text);
		$text = preg_replace("#\s*<BR\s/>#i", " \n\n", $text);
		$text = preg_replace("#\s*<BR>#i", " \n\n", $text);
	
		// Remove all tags we can't understand
		$text = preg_replace("#<[^>]*?>#i", '', $text);

		// Sanitize out quotes
		$string = str_replace('"', '\'\'', $string);
	
		return $text;
	} // end method _HTMLToRichText

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

		// Sanitize {, }
		$string = str_replace('{', '\{', $string);
		$string = str_replace('}', '\}', $string);

		// Get rid of #, _, %
		$string = str_replace('#', '\#', $string);
		$string = str_replace('_', '\_', $string);
		$string = str_replace('%', '\%', $string);

		// Deal with amphersands, and &quot; &amp; stuff
		$string = str_replace('&quot;', '\'\'', $string);
		$string = str_replace('&amp;', '&', $string);
		$string = str_replace('&', '\&', $string);

		// HTML/SGML specific texts
		if (!$skip_html) {
			// This one isn't *really* right, since there are
			// technically opening and closing quotes, but it
			// will do for now.
			$string = str_replace('"', '\'\'', $string);

			$string = str_replace('<', '\<', $string);
			$string = str_replace('>', '\>', $string);
		}

		// Return processed string
		return $string;
	} // end method _SanitizeText

} // end class TeX

?>
