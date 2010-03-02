<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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

$gwtphpmap = array (
	array (
		  'className' => 'org.freemedsoftware.gwt.client.Api.Scheduler'
		, 'mappedBy' => 'org.freemedsoftware.api.Scheduler'
		, 'methods' => array (

			// Method: GetDailyAppointments
			//
			// Parameters:
			//
			//	$date - (optional) Date to get appointments for. Defaults to current date.
			//
			//	$provider - (optional) Provider number
			//
			// Returns:
			//
			//	Hash of daily appointments
			//	* scheduler_id
			//	* patient
			//	* patient_id
			//	* provider
			//	* provider_id
			//	* note
			//	* hour
			//	* minute
			//	* appointment_time
			//	* duration
			//	* status
			//	* status_color
			//
			  array (
				  'name' => 'GetDailyAppointments'
				, 'mappedName' => 'GetDailyAppointments'
				, 'returnType' => '[java.util.HashMap'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: GetDailyAppointmentsRange
			//
			// Parameters:
			//
			//	$datefrom - Starting date.
			//
			//	$dateto - Ending date.
			//
			//	$provider - (optional) Provider number
			//
			// Returns:
			//
			//	Hash of daily appointments
			//	* scheduler_id
			//	* patient
			//	* patient_id
			//	* provider
			//	* provider_id
			//	* note
			//	* hour
			//	* minute
			//	* appointment_time
			//	* duration
			//	* status
			//	* status_color
			//	* resource_type ( pat, temp )
			//
			, array (
				  'name' => 'GetDailyAppointmentRange'
				, 'mappedName' => 'GetDailyAppointmentRange'
				, 'returnType' => '[java.util.HashMap'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: GetDailyAppointmentScheduler
			//
			//	Return daily appointment schedule for the specified date, with
			//	blank areas and block booking.
			//
			// Parameters:
			//
			//	$dt - Date
			//
			//	$provider - (optional) Provider number
			//
			// Returns:
			//
			//	Array of hashes.
			//	* scheduler_id
			//	* patient
			//	* patient_id
			//	* provider
			//	* provider_id
			//	* note
			//	* hour
			//	* minute
			//	* appointment_time
			//	* duration
			//	* status
			//	* status_color
			//	* resource_type ( pat, temp, block )
			//
			, array (
				  'name' => 'GetDailyAppointmentScheduler'
				, 'mappedName' => 'GetDailyAppointmentScheduler'
				, 'returnType' => '[java.util.HashMap'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: CopyAppointment
			//
			//	Copy the given appointment to a specified date
			//
			// Parameters:
			//
			//	$id - id for the specified appointment
			//
			//	$date - SQL date format (YYYY-MM-DD) specifying the
			//	date to copy the appointment
			//
			// Returns:
			//
			//	Boolean, whether successful
			//
			// See Also:
			//	<CopyGroupAppointment>
			//
			, array (
				  'name' => 'CopyAppointment'
				, 'mappedName' => 'CopyAppointment'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

			// Method: CopyGroupAppointment
			//
			// Parameters:
			//
			//	$group_id - id for the group appointments
			//
			//	$date - Target date
			//
			// Return:
			//
			//	Boolean, whether successful
			//
			// See Also:
			//	<CopyAppointment>
			//
			, array (
				  'name' => 'CopyGroupAppointment'
				, 'mappedName' => 'CopyGroupAppointment'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

			// Method: FindDateAppointments
			//
			//	Look up list of appointments for specified day and provider.
			//
			// Parameters:
			//
			//	$date - Date in YYYY-MM-DD
			//
			//	$provider - (optional) id for the provider in question. If
			//	this is omitted, all providers will be queried.
			//
			// Returns:
			//
			//	Array of associative arrays containing appointment
			//	information
			//
			, array (
				  'name' => 'FindDateAppointments'
				, 'mappedName' => 'FindDateAppointments'
				, 'returnType' => '[java.util.HashMap'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: FindGroupAppointments
			//
			//	Given a group id, return the appointments in that group
			//
			// Parameters:
			//
			//	$group_id - id for the group that is being searched for
			//
			// Returns:
			//
			//	Array of associative arrays containing appointment 
			//	information.
			//
			, array (
				  'name' => 'FindGroupAppointments'
				, 'mappedName' => 'FindGroupAppointments'
				, 'returnType' => '[java.util.HashMap'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: GetAppointment
			//
			//	Retrieves an appointment record from its id
			//
			// Parameters:
			//
			//	$id - id for the specified appointment
			//
			// Returns:
			//
			//	Associative array containing appointment information
			//
			, array (
				  'name' => 'GetAppointment'
				, 'mappedName' => 'GetAppointment'
				, 'returnType' => 'java.util.HashMap'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: MoveAppointment
			//
			//	Given an appointment id and data, modify an appointment
			//	record.
			//
			// Parameters:
			//
			//	$original - Original appointment id
			//
			//	$data - Associative array of data to be changed in the
			//	appointment record. See <calendar_field_mapping> for a
			//	list of acceptable keys.
			//
			// Returns:
			//
			//	Boolean, whether successful.
			//
			, array (
				  'name' => 'MoveAppointment'
				, 'mappedName' => 'MoveAppointment'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.util.HashMap' )
				)
				, 'throws' => array ( )
			)

			// Method: MoveGroupAppointment
			//
			//	Given a group id (for a group of appointments), modify a
			//	group of appointment records with the given data. This
			//	follows the same basic format as <MoveAppointment>
			//
			// Parameters:
			//
			//	$group_id - id for the appointment group
			//
			//	$data - Associative array of data to be changed in the
			//	appointment record. See <calendar_field_mapping> for a
			//	list of acceptable keys.
			//
			// Returns:
			//
			//	Boolean, whether successful.
			//
			, array (
				  'name' => 'MoveGroupAppointment'
				, 'mappedName' => 'MoveGroupAppointment'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.util.HashMap' )
				)
				, 'throws' => array ( )
			)

			// Method: next_available
			//
			//	Get next available slot with appropriate parameters.
			//
			// Parameters:
			//
			//	$_criteria - Hash containing one or more of the following:
			//	* after    - After a particular hour
			//	* date     - Date to start the search from
			//	* days     - Number of days to search (defaults to 4)
			//	* duration - In minutes (defaults to 5)
			//	* forceday - Force day to be day of week (1..7 ~ Mon..Sun)
			//	* location - Room location
			//	* provider - With a particular provider
			//	* single   - Provide single answer
			//	* weekday  - Force weekday (boolean) 
			//
			// Returns:
			//
			//	array ( of array ( date, hour, minute ) )
			//	false if nothing is open
			//
			, array (
				  'name' => 'NextAvailable'
				, 'mappedName' => 'next_available'
				, 'returnType' => '[[java.lang.String'
				, 'params' => array (
					array ( 'type' => 'java.util.HashMap' )
				)
				, 'throws' => array ( )
			)

			// Method: SetAppointment 
			//
			//	Create an appointment record with the specified data
			//
			// Parameters:
			//
			//	$data - Associative array of values to be used when
			//	setting the appointment. Uses <calendar_field_mapping>
			//	to determine values from keys.
			//
			// Returns:
			//
			//	id of created appointment
			//
			, array (
				  'name' => 'SetAppointment'
				, 'mappedName' => 'SetAppointment'
				, 'returnType' => 'java.lang.Integer'
				, 'params' => array (
					  array ( 'type' => 'java.util.HashMap' )
				)
				, 'throws' => array ( )
			)

			// Method: SetGroupAppointment
			//
			// Parameters:
			//
			//	$patients - Array of patient identifiers. The first of this
			//	array will be the appointment used to generate the group id.
			//
			//	$data - Associative array of data used to populate the
			//	appointment data. Same syntax as <SetAppointment>.
			//
			// Returns:
			//
			//	Group key id for new group created.
			//
			, array (
				  'name' => 'SetGroupAppointment'
				, 'mappedName' => 'SetGroupAppointment'
				, 'returnType' => 'java.lang.Integer'
				, 'params' => array (
					  array ( 'type' => '[java.lang.Integer' )
					, array ( 'type' => 'java.util.HashMap' )
				)
				, 'throws' => array ( )
			)

			// Method: set_recurring_appointment
			//
			//	Given an appointment (by its id) and a set of dates,
			//	replicate the appointment exactly on given dates. All
			//	of the appointments can later be accessed through the
			//	use of the calrecurid field. This allows for recurring
			//	appointments to be modified and deleted. A natural
			//	language description of the appointment is placed in
			//	recurnote.
			//
			// Parameters:
			//
			//	$appointment - id of the appointment in question
			//
			//	$ts - Array of timestamps containing the dates for the
			//	appointment to repeat
			//
			//	$desc - Description of the recurrance
			//
			, array (
				  'name' => 'SetRecurringAppointment'
				, 'mappedName' => 'set_recurring_appointment'
				, 'returnType' => TypeSignatures::$VOID
				, 'params' => array ( 
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => '[java.lang.Integer' )
					, array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

			// Method: ImportDate
			//
			//	Import date to internal FreeMED format.
			//
			// Parameters:
			//
			//	$input - Input date.
			//
			// Returns:
			//
			//	YYYY-MM-DD formatted date
			//
			, array (
				  'name' => 'ImportDate'
				, 'mappedName' => 'ImportDate'
				, 'returnType' => 'java.lang.String'
				, 'params' => array (
					array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

		)
	)
);

?>
