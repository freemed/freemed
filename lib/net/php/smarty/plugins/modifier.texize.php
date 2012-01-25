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

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty texize modifier plugin
 *
 * Type:     modifier<br>
 * Name:     texize<br>
 * Purpose:  Escape the string according to tex
 * @author   Jeff Buchbinder <jeff at freemedsoftware dot org>
 * @param string
 * @return string
 */
function smarty_modifier_texize( $string )
{
        // Sanitize all but HTML markers
        $text = str_replace('\\', '\\\\', $string);

        // Get rid of \r character
        $text = str_replace("\r", "", $text);

        // Destrip \'
        $text = str_replace("\\'", "'", $text);

        // Sanitize {, } (do NOT escape [ and ])
        $text = str_replace('{', '\lbrace\ ', $text);
        $text = str_replace('}', '\lbrace\ ', $text);
        $text = str_replace('[', '\lbrack\ ', $text);
        $text = str_replace(']', '\rbrack\ ', $text);
        
        // Make sure dollar sign escaping used before $+$ escaped
        $text = str_replace('$', '\$', $text);
        
        // Get rid of #, _, %, +
        $text = str_replace('#', '\#', $text);
        $text = str_replace('_', '\_', $text);
        $text = str_replace('%', '\%', $text);
        $text = str_replace('+', '$+$', $text);
        
        // Deal with amphersands, and &quot; &amp; stuff
        $text = str_replace('&quot;', '"', $text);
        $text = str_replace('&amp;', '&', $text);
        $text = str_replace('&lt;', '$<$', $text);
        $text = str_replace('&gt;', '$>$', $text);
        $text = str_replace('&', '\&', $text);
        $text = str_replace('&nbsp;', '\ ', $text);
        
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

        // Skip if there are no quotes
        if (strpos($text, '"') === false) {
                
        } else {
		$quotetype = 0;
		for ($i = 0; $i < strlen( $text ); $i++) {
			if (substr($text, $i, 1) == '"') {
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
				$output .= substr($text, $i, 1);
			}
		}
                // Reassign output string
                $text = $output;
        } // end if quotes
	
        // Handle embedded CRs... for now we treat them as line
        // breaks
        $text = str_replace("\r\n", "\n", $text);
        $text = str_replace("\r", "\n", $text);
        $text = str_replace("\n", "\\  \\\\\n", $text);

	$text = str_replace('<', '\<', $text);
	$text = str_replace('>', '\>', $text);
        return $text;
}

/* vim: set expandtab: */

?>
