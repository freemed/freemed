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

include_once "../lib.php";

if (!$login->Validate($_POST['username'], $_POST['password'])) {
	Header("Location: login.php", true, 307);
	die();
} else {
	Header("Location: menu.php", true, 307);
	die();
}

UiMobileLib::header("FreeMED");
UiMobileLib::pageHeader("FreeMED", "mainMenu");
UiMobileLib::displayList("Sites", array(
	  new UiMobileListItem( "link", "Logout", "logout.php" )
));
UiMobileLib::pageFooter();
UiMobileLib::footer();

?>
