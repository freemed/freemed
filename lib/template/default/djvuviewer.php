<?php
 // $Id$
 //
 // Authors:
 //     Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
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

// Function: djvu_widget
//
// Parameters:
//
//	$filename - Id or filename of document in question
//
//	$type - Type of document or patient ID.
//
// Returns:
//
//	DHTML code for Djvu viewer widget.
//
function djvu_widget ( $filename, $type ) {
	switch ($type) {
		case 'unfiled': case 'unread':
		$mytype = $type;
		$imagefilename = 'data/fax/' . $type . '/' . $filename;
		break;

		default:
		$id = $filename;
		$patient = $type;
		$mytype = 'djvu';
		$imagefilename = freemed::image_filename( $patient, $id, 'djvu' );
		break;
	}

	$djvu = CreateObject( "_FreeMED.Djvu", $imagefilename );
	$pages = $djvu->NumberOfPages();
	$buffer .= "
		<!-- Djvu Viewer, DHTML Style -->

		<div align=\"center\">
		<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" style=\"border: 1px solid #000000;\">
		<tr><td align=\"center\">
		<input type=\"button\" id=\"djvuPagePrevTop\" value=\" &lt; \" onClick=\"djvuChangePage(currentDjvuPage - 1);\" disabled=\"disabled\" />
		<span id=\"djvuCurrentPageTop\">1</span> of ".($pages+0)."
		<input type=\"button\" id=\"djvuPageNextTop\" value=\" &gt; \" onClick=\"djvuChangePage(currentDjvuPage + 1);\" ".( $pages == 1 ? "disabled=\"disabled\"" : ""  )." />
		</td>
		</tr>
		<tr>
		<td><img src=\"djvu_service.php?type=".urlencode($mytype)."&name=".urlencode($filename)."&id=".urlencode($id)."&patient=".urlencode($patient)."&page=1\" height=\"800\" width=\"600\" border=\"0\" id=\"djvuViewer\" /></td>
		</tr>
		<tr><td align=\"center\">
		<input type=\"button\" id=\"djvuPagePrevBottom\" value=\" &lt; \" onClick=\"djvuChangePage(currentDjvuPage - 1);\" disabled=\"disabled\" />
		<span id=\"djvuCurrentPageBottom\">1</span> of ".($pages+0)."
		<input type=\"button\" id=\"djvuPageNextBottom\" value=\" &gt; \" onClick=\"djvuChangePage(currentDjvuPage + 1);\" ".( $pages == 1 ? "disabled=\"disabled\"" : ""  )." />
		</td>
		</tr>
		</table>
		<script language=\"javascript\">
		var currentDjvuPage = 1;
		var totalDjvuPages = ".($pages+0).";
		function djvuChangePage( page ) {
			currentDjvuPage = page;
			document.getElementById('djvuViewer').src = 'djvu_service.php?type=".urlencode($mytype)."&name=".urlencode($filename)."&id=".urlencode($id)."&patient=".urlencode($patient)."&page=' + currentDjvuPage;

			// Handle enable/disabled
			document.getElementById('djvuCurrentPageTop').innerHTML = currentDjvuPage;
			document.getElementById('djvuCurrentPageBottom').innerHTML = currentDjvuPage;
			if (currentDjvuPage > 1) {
				document.getElementById('djvuPagePrevTop').disabled = false;
				document.getElementById('djvuPagePrevBottom').disabled = false;
			} else {
				document.getElementById('djvuPagePrevTop').disabled = true;
				document.getElementById('djvuPagePrevBottom').disabled = true;
			}
			if (currentDjvuPage < totalDjvuPages) {
				document.getElementById('djvuPageNextTop').disabled = false;
				document.getElementById('djvuPageNextBottom').disabled = false;
			} else {
				document.getElementById('djvuPageNextTop').disabled = true;
				document.getElementById('djvuPageNextBottom').disabled = true;
			}

			// Return true
			return true;
		}
		</script>
		</div>

		</form>
	";
	return $buffer;
} // end method djvu_widget

?>
