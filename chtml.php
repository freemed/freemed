<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
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

include_once ( 'lib/freemed.php' );

error_reporting ( );
set_error_handler("chtml_standard_error_handler");

unset ( $parts ); unset ( $file );
$parts = explode ( '/', $_SERVER['PATH_INFO'] );
$file = isset ( $parts[1] ) ? $parts[1] : '';

// The page token is a simple name - the Dojo UI asks for "dojo_<locale>" (see
// ui/dojo/view/org.freemedsoftware.ui.chtmlbrowser.tpl) - so a separator, a
// dot-segment, a percent-escape, a NUL or a backslash never belongs in it.
// Reject those here rather than handing them to the filesystem: the forced
// ".chtml" suffix and the file_exists() check below are confinement, not
// validation. See tests/security/evidence/path-sink-audit.md (Task 1.3) and
// tests/security/repro-chtml-path.sh, the regression gate for this row.
if ( ! preg_match ( '/^[A-Za-z0-9._-]+$/', $file ) ) {
	Header ( 'HTTP/1.1 400 Bad Request' );
	print 'Invalid CHTML page name.';
	exit;
}

$path = str_replace ( $parts[0].'/'.$parts[1], '', $_SERVER['PATH_INFO'] );

if ( !file_exists( dirname(__FILE__)."/doc/${file}.chtml" ) ) {
	print "Help index ${file} not present.";
	exit;
}

unset ( $chtml );
$chtml = CreateObject ( "org.freemedsoftware.core.CHTMLReader", dirname(__FILE__)."/doc/${file}.chtml" );

// Strip leading slash if it exists ...
if ( substr( $path, 0, 1 ) == '/' ) {
	$path = substr ( $path, - (strlen($path)-1) );
}

print $chtml->GetResource ( $path );

//----------------- Functions ----------------------------------------------

function chtml_standard_error_handler ($no, $str, $file, $line, $context = '') {
	switch ($no) {
		case E_USER_ERROR:
		die('
			<div style="border: 1px solid #000000; background-color: #ffff00; color: #000000; font-family: sans-serif; padding: 1em; font-size: 8pt;">'.$str.'</div>
		');
		break;
	}
}

?>
