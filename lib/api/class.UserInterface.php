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

class UserInterface {

	protected $user;

	public function __construct ( ) {
		$this->user = CreateObject('org.freemedsoftware.core.User');
	}

	// Method: GetCurrentUsername
	//
	//	Determine the username for the current user.
	//
	// Returns:
	//
	//	String.
	//
	public function GetCurrentUsername ( ) {
		return $this->user->getDescription();
	} // end method GetCurrentUsername

	// Method: GetEMRConfiguration
	public function GetEMRConfiguration ( ) {
		return $this->user->manage_config;
	} // end method GetEMRConfiguration

	public function GetNewMessages ( ) {
		return $this->user->newMessages();
	} // end method GetNewMessages

} // end class UserInterface

?>
