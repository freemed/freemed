<?php
  // $Id$
  // note: provider groups, used for booking? and user levels
  // code: jeff b (jeff@univrel.pr.uconn.edu) -- template
  //       adam b (gdrago23@yahoo.com) -- redesign and update
  // lic : GPL

if (!defined("__PROVIDER_GROUPS_MODULE_PHP__")) {

define(__PROVIDER_GROUPS_MODULE_PHP__, true);

class providerGroupsMaintenance extends freemedMaintenanceModule {
	var $MODULE_NAME    = "Provider Groups Maintenance";
	var $MODULE_VERSION = "0.1";

	var $table_name     = "phygroup";
	var $record_name    = "Provider Group";
	var $order_field    = "phygroupname";

	var $variables      = array (
		"phygroupname",
		"phygroupfac",
		"phygroupdtadd",
		"phygroupdtmod",
		"phygroupidmap",
		"phygroupdocs",
		"phygroupspe1"
	);

	function providerGroupsMaintenance () {
		global $phygroupdtmod, $cur_date;
		$this->freemedMaintenanceModule();
		$phygroupdtmod = $cur_date;
	} // end constructor providerGroupsMaintenance

	function view () {  
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist(
			$sql->query("SELECT phygroupname,phygroupfac,id FROM $this->table_name"),
			$this->page_name,
			array (
				_("Physician Group Name") => "phygroupname",
				_("Default Facility")     => "phygroupfac"
			),
			array ("",""),
			array (
				""         => "",
				"facility" => "psrname"
			)
		); // display main itemlist
	} // end function providerGroupsMaintenance->view()

	function form () {
		global $display_buffer;
		reset($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

		// too much data for this now
		//$this->view();

		switch($action) { // inner action switch
			case "modform":
				if (strlen($id)<1) {
					$action="addform";
					break;
				}
				while(list($k,$v)=each($this->variables))
            		global $$v;
				$r = freemed_get_link_rec($id,$this->table_name);
				extract ($r);
				$phygroupidmap  = fm_split_into_array($phygroupidmap);
				//$phygroupdocs 	= fm_split_into_array($phygroupdocs);
				break;
			case "addform": // addform *is* the default
			default:
				// nothing right here...
				break;
		} // inner action switch

		// set date of addition if not set 
		if (!isset($phygroupdtadd)) $phygroupdtadd = $cur_date;
 
		$fac_r = $sql->query("SELECT psrname,psrnote,id FROM facility ORDER BY psrname,psrnote");
		$spec_r = $sql->query("SELECT * FROM specialties ORDER BY specname,specdesc");
		$phy_q = "SELECT phylname,phyfname,id FROM physician ORDER BY phylname";


		$display_buffer .= "
			<TABLE CELLSPACING=0 CELLPADDING=0 BORDER=0 WIDTH=\"100%\">
   <TR><TD ALIGN=CENTER>
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".
      (($action=="modform") ? "mod" : "add")."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"     VALUE=\"".prepare($id)."\">
    <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
    <INPUT TYPE=HIDDEN NAME=\"phygroupdtadd\" VALUE=\"".prepare($phygroupdtadd)."\">";
 
	$display_buffer .= html_form::form_table( array (
	_("Physician Group Name") => 
	"<INPUT TYPE=TEXT NAME=phygroupname SIZE=20 MAXLENGTH=100 ".
     "VALUE=\"".prepare($phygroupname)."\">",
	_("Default Facility") => freemed_display_selectbox($fac_r, 
														"#psrname# [#psrnote#]",
       													"phygroupfac"),
	_("Specialty 1") => freemed_display_selectbox ($spec_r,
       												"#specname#, #specdesc#",
													 "phygroupspe1"),

	_("Physicians") => freemed_multiple_choice($phy_q,"phylname:phyfname","phygroupdocs",$phygroupdocs,false)
			)
		);
/*
	 
    "._("Physician Group Name")." :
    <INPUT TYPE=TEXT NAME=phygroupname SIZE=20 MAXLENGTH=100
     VALUE=\"".prepare($phygroupname)."\">
   </TD></TR>

   <TR><TD ALIGN=CENTER>
    "._("Default Facility")." :
    ".freemed_display_selectbox($fac_r, "#psrname# [#psrnote#]", 
       "phygroupfac")."
   </TD></TR>
	";
*/

	// handle groupidmap (just like phyidmap)

  $insmap_buf = ""; // cache the output, as above
  $i_res = $sql->query("SELECT * FROM inscogroup");
  while ($i_r = $sql->fetch_array ($i_res)) {
    $i_id = $i_r ["id"];
    $insmap_buf .= "
     <TR BGCOLOR=".($_alternate=freemed_bar_alternate_color($_alternate)).">
      <TD>".prepare($i_r["inscogroup"])."</TD>
      <TD>
       <INPUT TYPE=TEXT NAME=\"phygroupidmap$brackets\"
        SIZE=15 MAXLENGTH=30 VALUE=\"".$phygroupidmap[$i_id]."\">
      </TD>
     </TR>
    ";
  } // end looping for service types
	$display_buffer .= "<P>
  <CENTER><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2 
   BGCOLOR=\"#000000\"> <!-- black border --><TR><TD>

    <!-- hide record zero, since it isn't used... -->
    <INPUT TYPE=HIDDEN NAME=\"phygroupidmap$brackets\" VALUE=\"0\">

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR BGCOLOR=#aaaaaa>
     <TD><B>"._("Insurance Group")."</B></TD>
     <TD><B>"._("ID Number")."</B></TD>
    </TR>
    $insmap_buf
    </TABLE>
  </TD></TR></TABLE></CENTER>
	";
	// end groupidmap
	$display_buffer .= "<P>
   <TR><TD ALIGN=CENTER>
    <INPUT TYPE=SUBMIT VALUE=\"".
      (($action=="modform") ? _("Modify") : _("Add"))."\">
    <INPUT TYPE=RESET  VALUE=\""._("Remove Changes")."\">
    </FORM>
   </TD></TR>
   </TABLE>
  ";
		if ($action=="modform") $display_buffer .= "
			<CENTER>
			<A HREF=\"$this->page_name?module=$module&action=view\"
			 >"._("Abandon Modification")."</A>
			</CENTER>\n";
	} // end function providerGroupsMaintenance->form()

} // end class providerGroupsMaintenance

register_module ("providerGroupsMaintenance");

} // end if not defined

?>
