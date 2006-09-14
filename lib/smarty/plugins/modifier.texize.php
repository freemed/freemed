<?php
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
 * @author   Jeff Buchbinder <jeff at freemedsoftware dot com>
 * @param string
 * @return string
 */
function smarty_modifier_texize( $string )
{
        // Sanitize all but HTML markers
        $text = $this->_SanitizeText($string, true);
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
        $text = str_replace("\n", "\\  \\\\\n", $text);

        return $text;
}

/* vim: set expandtab: */

?>