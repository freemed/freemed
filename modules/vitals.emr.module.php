<?php
	// $Id$
	// note: vital signs module for patient management

LoadObjectDependency('_FreeMED.EMRModule');

class Vitals extends EMRModule {

	var $MODULE_NAME = "Vitals";
	var $MODULE_AUTHOR = "wade waden1@earthlink.net";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "
		FreeMED Vitals allows physicians and
		providers to evaluate new Patients.
		
	";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name   = "Vitals";
	var $table_name    = "vitals";
	var $patient_field = "vitalspat";
	var $date_field    = "vitalsdt";

	function Vitals () {
		// Table description
		$this->table_definition = array (
			"vitalsdt" => SQL__DATE,
			"vitalsdtadd" => SQL__DATE,
			"vitalsdtmod" => SQL__DATE,
			"vitalspat" => SQL__INT_UNSIGNED(0),
			"vitalsdescrip" => SQL__VARCHAR(100),
			"vitalsdoc" => SQL__INT_UNSIGNED(0),
			"vitalseoc" => SQL__INT_UNSIGNED(0),
			"vitals_wt" => SQL__REAL,
			"vitals_ht" => SQL__REAL,
			"vitals_sbp" => SQL__INT_UNSIGNED(0),
			"vitals_dbp" => SQL__INT_UNSIGNED(0),
			"vitals_hr" => SQL__INT_UNSIGNED(0),
			"vitals_rr" => SQL__INT_UNSIGNED(0),
			"vitals_temp" => SQL__REAL,
			"vitals_BMI" => SQL__REAL,
			"iso" => SQL__VARCHAR(15),
			"locked" => SQL__INT_UNSIGNED(0),
			"id" => SQL__SERIAL
		);
	
		// Define variables for EMR summary
		$this->summary_vars = array (
			__("Date")        =>	"my_date",
		//	__("Description") =>	"vitalsdescrip"
			__("BP")          =>	"bp",
			__("P")           =>	"vitals_hr",
			__("Resp")        =>	"vitals_rr",
			__("Temp")        =>	"vitals_temp",
			__("H")        	=>	"vitals_ht",
			__("W")        	=>	"vitals_wt"
		);
		$this->summary_options |= SUMMARY_DELETE | SUMMARY_VIEW | SUMMARY_LOCK;
		$this->summary_query = array (
			"DATE_FORMAT(vitalsdt, '%m/%d/%Y') AS my_date",
			"CONCAT(vitals_sbp, '/', vitals_dbp) AS bp"
		);
		$this->variables = array (
			"vitalspat"      => $_REQUEST['patient'],
			"vitalseoc",
			"vitalsdoc",
			"vitalsdt"       => fm_date_assemble("vitalsdt"),
			"vitalsdescrip",
			"vitalsdtadd"    => date("Y-m-d"),
			"vitalsdtmod"    => date("Y-m-d"),
			"vitals_wt",
			"vitals_ht",
			"vitals_sbp",
			"vitals_dbp",
			"vitals_hr",
			"vitals_rr",
			"vitals_temp",
			"vitals_BMI",
			"locked",
			"iso"
		);

		// Set associations
		$this->_SetAssociation('EpisodeOfCare');
		$this->_SetMetaInformation('EpisodeOfCareVar', 'vitalseoc');

		// Call parent constructor
		$this->EMRModule();
	} // end constructor vitals

	function form_table ( ) {
		$return = array (
			__("Provider") =>
			module_function('providermodule', 'widget', array ('vitalsdoc' )),
			//__("Descrip") =>
			//html_form::text_widget("vitalsdescrip", 25, 100),

			__("Date") => fm_date_entry("vitalsdt"),

			__("<u>W</u>eight (pounds)") =>
			html_form::text_widget('vitals_wt', array('id'=>'vitals_wt','length'=>7)),
			__("<u>H</u>eight (inches)") =>
			html_form::text_widget('vitals_ht', array('id'=>'vitals_ht','length'=>7)),
			__("<u>S</u>ystolic BP") =>
			html_form::text_widget('vitals_sbp', /*'VIRTUAL',*/ 5, 100),
			__("<u>D</u>iastolic BP") =>
			html_form::text_widget('vitals_dbp', /*'VIRTUAL',*/ 5, 100),
			__("<u>H</u>eart Rate") =>
			html_form::text_widget('vitals_hr', /*'VIRTUAL',*/ 5, 100),
			__("<u>R</u>espiratory Rate") =>
			html_form::text_widget('vitals_rr', /*'VIRTUAL',*/ 5, 100),
			__("<u>T</u>emperature (F)") =>
			html_form::text_widget('vitals_temp', /*'VIRTUAL',*/ 5, 100),
		 	__("<u>B</u>MI") =>
			html_form::text_widget('vitals_BMI', array('id'=>'vitals_BMI','length'=>7)).
			"<input type=\"button\" onclick=\"document.getElementById('vitals_BMI').value = ( document.getElementById('vitals_wt').value / ( document.getElementById('vitals_ht').value * document.getElementById('vitals_ht').value ) ) * 703; return true;\" value=\"".__("Calculate")."\" />",
		);
		if(check_module("EpisodeOfCare")) {
			// Actual piece
			$return[__("Related Episode(s)")] =
				module_function('EpisodeOfCare','widget',array('vitalseoc', $patient));
		}
		return $return;
	} // end method form_table

	function display () {
		global $display_buffer;

		// Tell FreeMED not to display a template
		$GLOBALS['__freemed']['no_template_display'] = true;
		
		foreach ($GLOBALS AS $k => $v) global $$k;
     if (($id<1) OR (strlen($id)<1)) {
       $display_buffer .= "
         ".__("Specify Notes to Display")."
         <p/>
         <div ALIGN=\"CENTER\">
	 <a HREF=\"$this->page_name?module=$module&patient=$patient\"
          >".__("back")."</a> |
          <a HREF=\"manage.php?id=$patient\"
          >".__("Manage Patient")."</a>
         </div>
       ";
       template_display();
     }
      // if it is legit, grab the data
     $r = freemed::get_link_rec ($id, "vitals");
     if (is_array($r)) extract ($r);
     $vitalsdt_formatted = substr ($vitalsdt, 0, 4). "-".
                           substr ($vitalsdt, 5, 2). "-".
                           substr ($vitalsdt, 8, 2);
     $vitalspat = $r ["vitalspat"];
     $vitalseoc = sql_expand ($r["vitalseoc"]);

     $this->this_patient = CreateObject('FreeMED.Patient', $vitalspat);

     $display_buffer .= "
       <p/>
       ".template::link_bar(array(
        __("Vitals") =>
       $this->page_name."?module=$module&patient=$vitalspat",
        __("Manage Patient") =>
       "manage.php?id=$vitalspat",
	__("Select Patient") =>
        "patient.php",
	( freemed::user_flag(USER_DATABASE) ? __("Modify") : "" ) =>
        $this->page_name."?module=$module&patient=$patient&id=$id&action=modform"
       ))."
       <p/>

       <CENTER>
        <B>Relevant Date : </B>
         $vitalsdt_formatted
       </CENTER>
       <P>
     ";
     // Check for EOC stuff
     if (count($vitalseoc)>0 and is_array($vitalseoc) and check_module("episodeOfCare")) {
      $display_buffer .= "
       <CENTER>
        <B>".__("Related Episode(s)")."</B>
        <BR>
      ";
      for ($i=0;$i<count($vitalseoc);$i++) {
        if ($vitalseoc[$i] != -1) {
          $e_r     = freemed::get_link_rec ($vitalseoc[$i]+0, "eoc"); 
          $e_id    = $e_r["id"];
          $e_desc  = $e_r["eocdescrip"];
          $e_first = $e_r["eocstartdate"];
          $e_last  = $e_r["eocdtlastsimilar"];
          $display_buffer .= "
           <A HREF=\"module_loader.php?module=episodeOfCare&patient=$patient&".
  	   "action=manage&id=$e_id\"
           >$e_desc / $e_first to $e_last</A><BR>
          ";
	} else {
	  $episodes = $sql->query (
	    "SELECT * FROM eoc WHERE eocpatient='".addslashes($patient)."'" );
	  while ($epi = $sql->fetch_array ($episodes)) {
            $e_id    = $epi["id"];
            $e_desc  = $epi["eocdescrip"];
            $e_first = $epi["eocstartdate"];
            $e_last  = $epi["eocdtlastsimilar"];
            $display_buffer .= "
           <A HREF=\"module_loader.php?module=episodeOfCare&patient=$patient&".
  	     "action=manage&id=$e_id\"
             >$e_desc / $e_first to $e_last</A><BR>
            ";
	  } // end fetching
	} // check if not "ALL"
      } // end looping for all EOCs
      $display_buffer .= "
       </CENTER>
      ";
     } // end checking for EOC stuff
     $display_buffer .= "<LEFT>\n";
     if (!empty($vitals_wt)) $display_buffer .= "
       <TABLE BGCOLOR=\"#ffffff\" BORDER=0><TR BGCOLOR=\"$darker_bgcolor\">
       <TD ALIGN=CENTER><B>".__("Weight")."</B>
	   ".stripslashes(str_replace("\n", "<BR>", htmlentities($vitals_wt)))."
	   </TD>
       ";
      
	  if (!empty($vitals_ht)) $display_buffer .= "
       <TD ALIGN=CENTER><B>".__("Height")."</B>
       ".stripslashes(str_replace("\n", "<BR>", htmlentities($vitals_ht)))."
       </TD>
       ";
      
	  if (!empty($vitals_sbp)) $display_buffer .= "
       <TD ALIGN=CENTER><B>".__("Blood Pressure")."</B>
       ".stripslashes(str_replace("\n", "<BR>", htmlentities($vitals_sbp)))."
	   </TD>
	  ";
	    
      if (!empty($vitals_dbp)) $display_buffer .= "
       <TD ALIGN=CENTER><CENTER><B>".__("/")."</B>
       ".stripslashes(str_replace("\n", "<BR>", htmlentities($vitals_dbp)))."
       </TD>
       ";
	   
      if (!empty($vitals_hr)) $display_buffer .= "
      <TD ALIGN=CENTER><B>".__("Pulse")."</B>
      ".stripslashes(str_replace("\n", "<BR>", htmlentities($vitals_hr)))."
       </TD>
       ";
	   
      if (!empty($vitals_rr)) $display_buffer .= "
      <TD ALIGN=CENTER><B>".__("Resp Rate")."</B>
      ".stripslashes(str_replace("\n", "<BR>", htmlentities($vitals_rr)))."
       </TD>
       ";
	   
      if (!empty($vitals_temp)) $display_buffer .= "
      <TD ALIGN=CENTER><B>".__("Temp")."</B>
      ".stripslashes(str_replace("\n", "<BR>", htmlentities($vitals_temp)))."
      </TD>
      ";
	  if (!empty($vitals_BMI)) $display_buffer .= "
      <TD ALIGN=CENTER><B>".__("BMI")."</B>
      ".stripslashes(str_replace("\n", "<BR>", htmlentities($vitals_BMI)))."
      </TD></TR></TABLE>
      ";
	  
	  
	  
	  
        // back to your regularly sceduled program...
      $display_buffer .= "
       <p/>
       ".template::link_bar(array(
        __("Vitals") =>
       $this->page_name."?module=$module&patient=$vitalspat",
        __("Manage Patient") =>
       "manage.php?id=$vitalspat",
	__("Select Patient") =>
        "patient.php",
	( freemed::user_flag(USER_DATABASE) ? __("Modify") : "" ) =>
        $this->page_name."?module=$module&patient=$patient&id=$id&action=modform"
       ))."
       <p/>
     ";
	} // end of case display

	function view ($condition = false) {
		global $display_buffer;
		global $patient, $action;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		// Check for "view" action (actually display)
		if ($action=="view") {
			$this->display();
			return NULL;
		}

		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE (vitalspat='".addslashes($patient)."') ".
			freemed::itemlist_conditions(false)." ".
			( $condition ? 'AND '.$condition : '' )." ".
			"ORDER BY vitalsdt";
		$result = $sql->query ($query);

		$display_buffer .= freemed_display_itemlist(
			$result,
			$this->page_name,
			array (
				__("Date")        => "vitalsdt",
				__("Description") => "vitalsdescrip"
			), // array
			array (
				"",
				__("NO DESCRIPTION")
			),
			NULL, NULL, NULL,
			ITEMLIST_MOD | ITEMLIST_VIEW | ITEMLIST_DEL | ITEMLIST_LOCK
		);
		$display_buffer .= "\n<p/>\n";
	} // end method view

	function recent_text ( $patient, $recent_date = NULL ) {
		$r = $this->_recent_record ( $patient, $recent_date );
		$return .= 
			"BP: ".$r['vitals_sbp']."/".$r['vitals_dbp'].", ".
			"P: ".$r['vitals_hr'].", ".
			"R: ".$r['vitals_rr'].", ".
			"T: ".$r['vitals_temp'].", ".
			"H: ".$r['vitals_ht'].", ".
			"W: ".$r['vitals_wt'];
		return $return;
	} // end method recent_text

} // end of Vitals

register_module ("Vitals");

?>
