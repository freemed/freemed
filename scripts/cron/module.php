#!/usr/bin/env php
<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // Copyright (C) 1999-2006 FreeMED Software Foundation
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

ini_set('include_path', dirname(dirname(dirname(__FILE__))).':'.ini_get('include_path'));

include_once ( 'lib/freemed.php' );

// Force recaching of modules
print date('r')." : Loaded ".__FILE__."\n";
print date('r')." : Forcing recache of modules ... \n";
$modules = CreateObject( 'org.freemedsoftware.core.ModuleIndex', true );
print date('r')." :- org.freemedsoftware.core.ModuleIndex instantiated\n";

?>
