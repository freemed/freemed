<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

if (!defined("__REBILL_BYCOV_MODULE_PHP__")) {

define (__REBILL_BYCOV_MODULE_PHP__, true);

// class RebillByCovModule extends freemedModule
class RebillByCovModule extends freemedBillingModule {

	// override variables
	var $MODULE_NAME = "Rebill By Coverage";
	var $MODULE_VERSION = "0.1";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";

	var $PACKAGE_MINIMUM_VERSION = "0.2.1";

	var $CATEGORY_NAME = "Billing";
	var $CATEGORY_VERSION = "0";


	// contructor method
	function RebillByCovModule ($nullvar = "") {
		// call parent constructor
		$this->freemedBillingModule($nullvar);
	} // end function RebillByCovModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module)) return false;
		return true;
	} // end function check_vars

	// override main function

	function addform()
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		if (!$been_here)
		{
			$this->view();
			return;
		}

		if ($viewaction=="rebill")
		{
			//echo "insco $whichinsco<BR>";
			$insco=0;
			$insco = new InsuranceCompany($whichinsco);
			if ($insco==0)
			{
				echo "Error getting insco name<BR>";
				return;
			}
			echo "Rebilling ".$insco->local_record[insconame]." ID $whichinsco<BR>";
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
				//echo "Updateing Proc $procid<BR>";
				$updquery = "UPDATE procrec SET procbilled='0' WHERE id='$procid'";
				$updres = $sql->query($updquery);
				if (!$updres)
					echo "Update failed for Procedure $procid<BR>";
			}
			echo "$numrows Procedures Rebilled<BR>";

			echo "
			<P>
			<CENTER>
			<$STDFONT_B><B>"._("Rebill for ").$insco->local_record[insconame]." "._("Done")."</B><$STDFONT_E>
			</CENTER>
			<P>
			<CENTER>
			<A HREF=\"$this->page_name?$_auth&module=$module\"
			><$STDFONT_B>"._("Return to Rebill Menu")."<$STDFONT_E></A>
			</CENTER>
			<P>
			";


			return;

		} // end geninsform

		trigger_error("Bad action passed in generate statements module", E_USER_ERROR);

	}
	


	function view()
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
	
       $query = "SELECT DISTINCT c.id,c.insconame FROM procrec AS a,coverage AS b,insco AS c ".
			    "WHERE a.procbalcurrent>'0' AND a.proccurcovid>'0' AND a.procbilled='1' ".
				"AND a.proccurcovid=b.id AND b.covinsco=c.id ORDER BY c.insconame";
		//echo "$query<BR>";

	    echo "
		<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3
		 VALIGN=MIDDLE ALIGN=CENTER>
		<TR>
		 <TD COLSPAN=2>
		  <CENTER>
		   <$STDFONT_B><B>"._("Rebill By Coverage")."</B><$STDFONT_E>
		  </CENTER>
		 </TD>
    	</TR>

		<FORM ACTION=\"$this->page_name\" METHOD=POST>
		<INPUT TYPE=HIDDEN NAME=\"_auth\"  VALUE=\"".prepare($_auth)."\">
		<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"addform\">
		<INPUT TYPE=HIDDEN NAME=\"viewaction\" VALUE=\"rebill\">
		<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"$module\">

		<TR>
		 <TD ALIGN=RIGHT>
		  <CENTER>
		   <$STDFONT_B>Coverage : <$STDFONT_E>
		  </CENTER>
		 </TD>
     	<TD ALIGN=LEFT>
      	<SELECT NAME=\"whichinsco\">
   		";
		
	   $result = $sql->query ($query);
       if ($sql->num_rows($result) <= 0)
       {
          echo "Nothing to Bill<BR>";
		  return;
       }

	   while ($r = $sql->fetch_array ($result)) {
		echo "
		 <OPTION VALUE=\"$r[id]\">".prepare($r[insconame])."
		";
	   } // end looping through results                         
	   echo "
		  </SELECT>
		 </TD>
    	</TR>
		";

		echo "
		<TR>
		 <TD COLSPAN=2>
		  <CENTER>
		   <INPUT TYPE=SUBMIT VALUE=\""._("Go")."\">
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

} // end if not defined

?>
