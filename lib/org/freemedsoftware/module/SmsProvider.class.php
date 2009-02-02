<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2009 FreeMED Software Foundation
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

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class SmsProvider extends SupportModule {

	var $MODULE_NAME = "SMS Provider";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "93523a88-cd11-47e1-9e68-ffc6c55d5a47";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "SMS Provider";
	var $table_name  = "smsprovider";
	var $order_field = "providername";

	var $widget_hash = "##providername##";

	var $variables = array (
		'providername',
		'numberlength',
		'mailgwaddr',
		'countrycode'
	);

	public function __construct ( ) {
		// __("SMS Provider")
	
		$this->list_view = array (
			__("Name") => "providername",
			__("Gateway") => "mailgwaddr"
		);

		// Run parent constructor
		parent::__construct();
	} // end constructor

	// Method: SendSMSToUser
	//
	//	Send an SMS message to a user.
	//
	// Parameters:
	//
	//	$userId - User ID
	//
	//	$message - Message to send
	//
	// Returns:
	//
	//	Boolean, based on success of PHP mail() call.	
	//
	public function SendSMSToUser ( $userId, $message ) {
		$u = CreateObject( 'org.freemedsoftware.core.User', $userId + 0 );
		$sms = $GLOBALS['sql']->get_link( 'smsprovider', $u->local_record['usersmsprovider'] );
		$mailAddress = $u->local_record['usersms'] . '@' . $sms['mailgwaddr'];
		return mail( $mailAddress, '', $message );
	} // end method SendSMS

} // end class SmsProvider

register_module ("SmsProvider");

?>
