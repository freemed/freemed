<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
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

include_once ( 'lib/freemed.php' );

unset ( $parts ); unset ( $file );
$parts = explode ( '/', $_SERVER['PATH_INFO'] );
$file = $parts[1];
$path = str_replace ( $parts[0].'/'.$parts[1], '', $_SERVER['PATH_INFO'] );

if ( !file_exists( dirname(__FILE__)."/doc/${file}.chtml" ) ) {
	print "Help index ${file} not present.";
	exit;
}

unset ( $chtml );
$chtml = CreateObject ( "org.freemedsoftware.core.CHTMLReader", dirname(__FILE__)."/doc/${file}.chtml" );
print $chtml->GetResource ( $path );

?>
