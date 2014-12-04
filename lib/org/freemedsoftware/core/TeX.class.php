<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

// Class: org.freemedsoftware.core.TeX
//
//	LaTeX rendering class.
//
class TeX {

	protected $_buffer; // Internal buffer for creating TeX file
	protected $smarty;
	public $options;

	public function __construct ( $options = NULL ) {
		global $this_user;
		if (!is_object($this_user)) { $this_user = CreateObject('org.freemedsoftware.core.User'); }

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
	public function PrintTeX ( $copies = 1, $rendered = false ) {
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

	// Method: RenderFromTemplate
	//
	//	Renders a TeX document with substitutions from a TeX
	//	template in the data/tex/ directory.
	//
	// Parameters:
	//
	//	$template - Name of the template to be used. For
	//	data/tex/rx.tex this would be 'rx';
	//
	//	$rec - Associative array of primary record data.
	//
	// Returns:
	//
	//	TeX document (not rendered into a result format).
	//
	public function RenderFromTemplate ( $template, $rec ) {
		$basedir = dirname(__FILE__)."/../../../..";
		if (!file_exists("$basedir/data/tex/${template}.tex")) {
			print "$basedir/data/tex/$template.tex<br/>\n";
			die("Could not load $template TeX template.");
		}

		// Initialize Smarty engine, with caching
		if (!is_object($this->smarty)) {
			$this->smarty = CreateObject( 'net.php.smarty.Smarty' );
			$this->smarty->setTemplateDir("$basedir/data/tex/");
			$this->smarty->setCompileDir("$basedir/data/cache/smarty/templates_c/");
			$this->smarty->setCacheDir("$basedir/data/cache/smarty/cache/");
			$this->smarty->left_delimiter = '{[';
			$this->smarty->right_delimiter = ']}';
		}

		// Load rec data
		$this->smarty->assign( 'rec', $rec );
		if (is_array($rec)) {
			foreach ($rec AS $k => $v) {
				$this->smarty->assign( $k, $v );
			}
		}

		// Get the important part into the buffer
		$buffer = $this->smarty->fetch( "${template}.tex" );

		// Return processed string
		return $buffer;
	} // end method RenderFromTemplate

	// Method: RenderToPDF
	//
	//	Render to PDF and get file name of temporary file
	//
	// Returns:
	//
	//	Name of temporary file.
	//
	function RenderToPDF ( ) {
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

	// Method: SetBuffer
	//
	// Parameters:
	//
	//	$buffer - Rendered TeX buffer.
	//
	public function SetBuffer ( $buffer ) {
		$this->_buffer = $buffer;
	} // end method SetBuffer

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
