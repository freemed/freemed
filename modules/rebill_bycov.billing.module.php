<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.BillingModule');

class RebillByCovModule extends BillingModule {

	// override variables
	var $MODULE_NAME = "Rebill By Coverage";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE = __FILE__;

	//var $ICON = "img/rebill.gif";

	var $PACKAGE_MINIMUM_VERSION = '0.6.1';

	var $CATEGORY_NAME = "Billing";
	var $CATEGORY_VERSION = "0.1";

	// contructor method
	function RebillByCovModule ($nullvar = "") {
		// Handler for the billing menu
		$this->_SetHandler('BillingFunctions', 'view');
		$this->_SetMetaInformation('BillingFunctionName', __("Rebill by Coverage"));
		$this->_SetMetaInformation('BillingFunctionDescription', __("Mark claims to be rebilled by coverage. This option is depreciated."));
		
		// call parent constructor
		$this->BillingModule($nullvar);
	} // end function RebillByCovModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module)) return false;
		return true;
	} // end function check_vars

	// override main function

	function addform() {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		if (!$been_here) {
			$this->view();
			return;
		}

		if ($viewaction=="rebill") {
			//$display_buffer .= "insco $whichinsco<BR>";
			$insco=0;
			$insco = CreateObject('FreeMED.InsuranceCompany',
				$whichinsco
			);
			if ($insco==0)
			{
				$display_buffer .= "Error getting insco name<BR>";
				return;
			}
			$display_buffer .= "Rebilling ".$insco->local_record[insconame]." ID $whichinsco<BR>";
			$query = "SELECT a.id FROM procrec as a, coverage as b ".
					 "WHERE a.procbalcurrent>'0' AND a.procbilled='1' AND a.proccurcovid=b.id ".
					 "AND b.covinsco='$whichinsco'";

			$result = $sql->query($query);

			$numrows=$sql->num_rows($result);
			if ($numrows <= 0)
			{
				trigger_error("Failed getting Procedures to ReBill", E_USER_ERROR);
				return;
			}

			while($row = $sql->fetch_array($result))
			{
				$procid = $row[id];
				//$display_buffer .= "Updateing Proc $procid<BR>";
				$updquery = "UPDATE procrec SET procbilled='0' WHERE id='$procid'";
				$updres = $sql->query($updquery);
				if (!$updres)
					$display_buffer .= "Update failed for Procedure $procid<BR>";
			}
			$display_buffer .= "$numrows Procedures Rebilled<BR>";

			$display_buffer .= "
			<P>
			<CENTER>
			<B>".__("Rebill for ").$insco->local_record[insconame]." ".__("Done")."</B>
			</CENTER>
			<P>
			<CENTER>
			<A HREF=\"$this->page_name?module=$module\"
			>".__("Return to Rebill Menu")."</A>
			</CENTER>
			<P>
			";


			return;

		} // end geninsform

		trigger_error("Bad action passed in generate statements module", E_USER_ERROR);

	}
	


	function view() {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
	
       $query = "SELECT DISTINCT c.id,c.insconame FROM procrec AS a,coverage AS b,insco AS c ".
			    "WHERE a.procbalcurrent>'0' AND a.proccurcovid>'0' AND a.procbilled='1' ".
				"AND a.proccurcovid=b.id AND b.covinsco=c.id ORDER BY c.insconame";
		//$display_buffer .= "$query<BR>";

	$result = $sql->query ($query);
	if (!$sql->results($result)) {
        	$display_buffer .= __("There is nothing to rebill at this time.")."<br/>\n";
		return;
	}

	    $display_buffer .= "
		<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3
		 VALIGN=MIDDLE ALIGN=CENTER>
		<TR>
		 <TD COLSPAN=2>
		  <CENTER>
		   <B>".__("Rebill By Coverage")."</B>
		  </CENTER>
		 </TD>
    	</TR>

		<FORM ACTION=\"$this->page_name\" METHOD=POST>
		<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"addform\">
		<INPUT TYPE=HIDDEN NAME=\"viewaction\" VALUE=\"rebill\">
		<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($_REQUEST['module'])."\">
		<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($_REQUEST['type'])."\">

		<TR>
		 <TD ALIGN=RIGHT>
		  <CENTER>
		   Coverage :
		  </CENTER>
		 </TD>
     	<TD ALIGN=LEFT>
      	<SELECT NAME=\"whichinsco\">
   		";
		
	   while ($r = $sql->fetch_array ($result)) {
		$display_buffer .= "
		 <OPTION VALUE=\"$r[id]\">".prepare($r[insconame])."
		";
	   } // end looping through results                         
	   $display_buffer .= "
		  </SELECT>
		 </TD>
    	</TR>
		";

		$display_buffer .= "
		<TR>
		 <TD COLSPAN=2>
		  <CENTER>
		   <INPUT TYPE=SUBMIT VALUE=\"".__("Go")."\">
		  </CENTER>
		 </TD>
		  <TD><INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"1\"></TD>
		</TR>

		</FORM>

		</TABLE>
	   ";
	} // end view functions


} // end class RebillByCovModule

register_module("RebillByCovModule");

?>
