<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

class IIGAlert {

	public function __constructor() { }

	public function sendAlertsForAppointments() {
		$FacilityTimezone='America/Los_Angeles';
		date_default_timezone_set($FacilityTimezone);
		$SqlStatementConnection=mysql_connect("$host:$port",$login,$pass);

		$EventDate=date('Y-m-d');
		$NextDaySameTime = mktime (date('h')+24,0,0,date('m'),date('d'),date('Y'));
		$EventDate = date('Y-m-d',$NextDaySameTime);

		$SqlStatement = "select pc_eid, pc_eventDate,pc_starttime,pc_endtime,pd.phone_home,pd.lname,u.lname as uname 
	               from openemr_postcalendar_events ope join patient_data pd on ope.pc_pid=pd.pid join users u on u.id=ope.pc_aid 
	               where pc_eventDate='$EventDate' and phone_home <>''";
	
		$ExtraData = "<Input><VOICE><CheckResponse Response='1' Action='PLAYAUDIO' ActionData='http://www.site.com/AppointmentConfirmed.wav' /></VOICE>
			   <VOICE><CheckResponse Response='2' Action='PLAYAUDIO' ActionData='http://www.site.com/AppointmentCanceled.wav' /></VOICE>
			   <VOICE><CheckResponse Response='3' Action='TRANSFERCALL' ActionData='9998887777' /></VOICE>
	                   <ALL><SendResponse URL='http://www.site.com/default/ReceiveHBIIGAlertResponse.php' /></ALL></Input>";
	 
		$RecordSet=mysql_query($SqlStatement);
		while($row=mysql_fetch_array($RecordSet)) {
			$phone=$row["phone_home"];
			$ScheduleTime="$row['pc_eventDate']"." "."$row['pc_starttime']";
			$GMTScheduleTime=gmdate ("M d Y H:i",$ScheduleTime);
	
			$XmlQuestion="<Input><VOICE><Message>
				      <MessagePart><Audio>http://www.site.com/GoodMorning.wav</Audio><TTS>Good morning</TTS></MessagePart>
			              <MessagePart><Audio></Audio><TTS>".$row['uname']."</TTS></MessagePart>
	        		      <MessagePart><Audio>http://www.site.com/WeAreCalling.wav</Audio><TTS>We are calling from the clinic. You have an appointment with the clinic at</TTS></MessagePart>
			              <MessagePart><Audio></Audio><TTS>".$row['pc_starttime']." tomorrow"."</TTS></MessagePart>
	        		      <MessagePart><Audio>http://www.site.com/PleaseInputYourResponse.wav</Audio><TTS>Please press 1 to confirm, 2 to cancel and 3 to reschedule</TTS></MessagePart>
			              </Message></VOICE></Input>";
	
			$SoapData=array(
				  'ClientID' => 'yourhblogic'
				, 'Password' => 'yourhbpassword'
				, 'AlertType' => 'GENERIC'
				, 'Channel' => 'VOICE'
				, 'Destination' => $phone
				, 'Subject' => ''
				, 'Question' => ''
				, 'AnswerType' => 'DTMF1'
				, 'AnswerChoices' => '1,2,3'
				, 'XmlQuestion' => $XmlQuestion
				, 'ExtraData' => $ExtraData
				, 'StartTime' => $GMTScheduleTime
				, 'EndTime' => $EndTime
			);
			$SoapResponse=$SoapClient->PutIIGQuestion($SoapData); 
			if($ResponseArray['ReturnValue']=='SUCCESS') {
				echo "success";
				$SqlStatement="update openemr_postcalendar_events set pc_hb_confirm=concat(pc_hb_confirm,".$ResponseArray['ReturnMessage'].") where pc_eid=".$row['pc_eid'];
				mysql_query($SqlStatement);
			} else {
				echo "fail";
				$SqlStatement="update openemr_postcalendar_events set pc_hb_confirm='Failed' where pc_eid=".$row['pc_eid'];
				mysql_query($SqlStatement);
			}
		} // end loop
	} // end method

	protected function getSoapClient() {
		$options=array('features'=>SOAP_WAIT_ONE_WAY_CALLS);
		$sc=new SoapClient("https://www.hummingbytes.com/SecureWebservices/IIGQuestionAndAnswer.asmx?WSDL", $Options);
		return $sc;
	} // end method getSoapClient

	protected function parseResponse($soapResponse) {
		$XMLElement=new SimpleXMLElement($soapResponse); 
		$ResponseArray = array();
		foreach ($XMLElement->children() as $child) {
			$ResponseArray[$child->getName()] = sprintf("%s", $child);
		}
		return $ResponseArray;
	} // end method parseResponse

} // end class IIGAlert

?>
