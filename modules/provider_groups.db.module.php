<?php
  // $Id$
  // note: provider groups, used for booking? and user levels
  // code: jeff b (jeff@ourexchange.net) -- template
  //       adam b (gdrago23@yahoo.com) -- redesign and update
  // lic : GPL

LoadObjectDependency('_FreeMED.MaintenanceModule');

class ProviderGroupsMaintenance extends MaintenanceModule {
	var $MODULE_NAME    = "Provider Groups Maintenance";
	var $MODULE_AUTHOR  = "Adam (gdrago23@yahoo.com)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name     = "phygroup";
	var $record_name    = "Provider Group";
	var $order_field    = "phygroupname";

	var $variables = array (
		"phygroupname",
		"phygroupfac",
		"phygroupdtadd",
		"phygroupdtmod",
		"phygroupidmap",
		"phygroupdocs",
		"phygroupspe1"
	);

	function ProviderGroupsMaintenance () {
		global $phygroupdtmod;
		$phygroupdtmod = date("Y-m-d");

		// Table definition
		$this->table_definition = array (
			'phygroupname' => SQL__VARCHAR(100),
			'phygroupfac' => SQL__INT_UNSIGNED(0),
			'phygroupdtadd' => SQL__DATE,
			'phygroupdtmod' => SQL__DATE,
			'phygroupidmap' => SQL__TEXT,
			'phygroupdocs' => SQL__TEXT,
			'phygroupspe1' => SQL__INT_UNSIGNED(0),
			'id' => SQL__SERIAL
		);

		// Run constructor
		$this->MaintenanceModule();
	} // end constructor ProviderGroupsMaintenance

	function view () {  
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist(
			$sql->query(
				"SELECT phygroupname,phygroupfac,id ".
				"FROM ".$this->table_name." ".
				freemed::itemlist_conditions()." ".
				"ORDER BY phygroupname"
			),
			$this->page_name,
			array (
				__("Physician Group Name") => "phygroupname",
				__("Default Facility")     => "phygroupfac"
			),
			array ("",""),
			array (
				""         => "",
				"facility" => "psrname"
			)
		); // display main itemlist
	} // end function ProviderGroupsMaintenance->view()

	function form () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		if ($_REQUEST['__submit'] == __("Cancel")) {
			global $refresh;
			$refresh = $this->page_name.'?module='.get_class($this);
			return false;
		}

		// too much data for this now
		//$this->view();

		switch($action) { // inner action switch
			case "modform":
				if (strlen($id)<1) {
					$action="addform";
					break;
				}
				foreach ($this->variables AS $k => $v) { global ${$v}; }
				$r = freemed::get_link_rec($id, $this->table_name);
				extract ($r);
				$phygroupidmap  = fm_split_into_array($phygroupidmap);
				//$phygroupdocs = fm_split_into_array($phygroupdocs);
				break;
			case "addform": // addform *is* the default
			default:
				// nothing right here...
				break;
		} // inner action switch

		// set date of addition if not set 
		if (!isset($phygroupdtadd)) $phygroupdtadd = $cur_date;

		$display_buffer .= "
		<table CELLSPACING=\"0\" CELLPADDING=\"0\" BORDER=\"0\" WIDTH=\"100%\">
		<tr><td ALIGN=\"CENTER\">
		<form ACTION=\"$this->page_name\" METHOD=\"POST\">
		<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"".
		  (($action=="modform") ? "mod" : "add")."\"> 
		<input TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"".prepare($id)."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"return\" VALUE=\"".prepare($_REQUEST['return'])."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"phygroupdtadd\" VALUE=\"".prepare($phygroupdtadd)."\"/>";
 
		$display_buffer .= html_form::form_table( array (

			__("Physician Group Name") => 
				html_form::text_widget('phygroupname', 20, 100),
		
			__("Default Facility") => freemed_display_selectbox(

					$sql->query("SELECT psrname,psrnote,id FROM facility ORDER BY psrname,psrnote"),
					"#psrname# [#psrnote#]",
       					"phygroupfac"),

			__("Specialty 1") => freemed_display_selectbox (
					$sql->query("SELECT * FROM specialties ORDER BY specname,specdesc"),
       					"#specname#, #specdesc#",
					 "phygroupspe1"),

			__("Physicians") => freemed::multiple_choice(
					"SELECT phylname,phyfname,id FROM physician WHERE phylname != '' ORDER BY phylname",
					"##phylname##, ##phyfname## ##phymname##",
					"phygroupdocs",
					$phygroupdocs,
					false)
		) );

		// handle groupidmap (just like phyidmap)
		$insmap_buf = ""; // cache the output, as above
		$i_res = $sql->query("SELECT * FROM inscogroup");
		while ($i_r = $sql->fetch_array ($i_res)) {
			$i_id = $i_r ["id"];
			$insmap_buf .= "
			<tr CLASS=\"".freemed_alternate()."\">
			 <td>".prepare($i_r["inscogroup"])."</td>
			 <td>
			  <input TYPE=\"TEXT\" NAME=\"phygroupidmap$brackets\"
			   SIZE=\"15\" MAXLENGTH=\"30\" ".
			   "VALUE=\"".$phygroupidmap[$i_id]."\"/>
			 </td>
			</tr>
			";
		} // end looping for service types

		if (!empty($insmap_buf)) $display_buffer .= "
		<p/>
		<div ALIGN=\"CENTER\">
		<table BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"2\" 
		CLASS=\"reverse\" ALIGN=\"CENTER\"> <!-- black border --><tr><td>

		<!-- hide record zero, since it isn't used... -->
		<input TYPE=\"HIDDEN\" NAME=\"phygroupidmap$brackets\" VALUE=\"0\"/>

		<table BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"3\"
		 VALIGN=\"MIDDLE\" ALIGN=\"CENTER\">
		<tr CLASS=\"cell_hilite\">
		<td><b>".__("Insurance Group")."</b></td>
		<td><b>".__("ID Number")."</b></td>
		</tr>
		$insmap_buf
		</table>
		</td></tr></table></div>
		";
		// end groupidmap

		$display_buffer .= "<p/>
		<tr><td ALIGN=\"CENTER\">
		<input CLASS=\"button\" name=\"__submit\" TYPE=\"SUBMIT\" VALUE=\"".
		(($action=="modform") ? __("Modify") : __("Add"))."\"/>
		<input CLASS=\"button\" name=\"__submit\" TYPE=\"SUBMIT\" VALUE=\"".__("Cancel")."\"/>
		<input CLASS=\"button\" TYPE=\"RESET\" VALUE=\"".__("Remove Changes")."\"/>
		</form>
		</td></tr>
		</table>
		";
	} // end function ProviderGroupsMaintenance->form()

} // end class ProviderGroupsMaintenance

register_module ("ProviderGroupsMaintenance");

?>
