<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class ProgressNotes extends EMRModule {

	var $MODULE_NAME = "Progress Notes";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.3.1";
	var $MODULE_DESCRIPTION = "
		FreeMED Progress Notes allow physicians and
		providers to track patient activity through
		SOAPIER style notes.
	";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name   = "Progress Notes";
	var $table_name    = "pnotes";
	var $patient_field = "pnotespat";
	var $widget_hash   = "##pnotesdt## ##pnotesdescrip##";

	var $print_template = 'progress_notes';

	function ProgressNotes () {
		// Table description
		$this->table_definition = array (
			'pnotesdt' => SQL__DATE,
			'pnotesdtadd' => SQL__DATE,
			'pnotesdtmod' => SQL__DATE,
			'pnotespat' => SQL__INT_UNSIGNED(0),
			'pnotesdescrip' => SQL__VARCHAR(100),
			'pnotesdoc' => SQL__INT_UNSIGNED(0),
			'pnoteseoc' => SQL__INT_UNSIGNED(0),
			'pnotes_S' => SQL__TEXT,
			'pnotes_O' => SQL__TEXT,
			'pnotes_A' => SQL__TEXT,
			'pnotes_P' => SQL__TEXT,
			'pnotes_I' => SQL__TEXT,
			'pnotes_E' => SQL__TEXT,
			'pnotes_R' => SQL__TEXT,
			'pnotessbp' => SQL__INT_UNSIGNED(0),
			'pnotesdbp' => SQL__INT_UNSIGNED(0),
			'pnotestemp' => SQL__REAL,
			'pnotesheartrate' => SQL__INT_UNSIGNED(0),
			'pnotesresprate' => SQL__INT_UNSIGNED(0),
			'pnotesweight' => SQL__INT_UNSIGNED(0),
			'pnotesheight' => SQL__INT_UNSIGNED(0),
			'pnotesbmi' => SQL__INT_UNSIGNED(0),
			'iso' => SQL__VARCHAR(15),
			'locked' => SQL__INT_UNSIGNED(0),
			'id' => SQL__SERIAL
		);
		
		// Define variables for EMR summary
		$this->summary_vars = array (
			__("Date")        =>	"my_date",
			__("Provider")    =>	"pnotesdoc:physician",
			__("Description") =>	"pnotesdescrip"
		);
		$this->summary_options |= SUMMARY_VIEW | SUMMARY_LOCK | SUMMARY_PRINT | SUMMARY_DELETE;
		$this->summary_query = array("DATE_FORMAT(pnotesdt, '%m/%d/%Y') AS my_date");
		$this->summary_order_by = 'pnotesdt DESC,id';

		// Set associations
		$this->_SetAssociation('EpisodeOfCare');
		$this->_SetMetaInformation('EpisodeOfCareVar', 'pnoteseoc');

		$this->acl = array ( 'emr' );

		// Call parent constructor
		$this->EMRModule();
	} // end constructor ProgressNotes

	function add () { $this->form(); }
	function mod () { $this->form(); }

	function form () {
		global $display_buffer, $sql, $pnoteseoc;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		$book = CreateObject('PHP.notebook',
				array ("id", "module", "patient", "action", "return"),
				NOTEBOOK_COMMON_BAR | NOTEBOOK_STRETCH, 5);

		switch ($action) {
			case "add": case "addform":
			$book->set_submit_name(__("Add"));
			break;

			case "mod": case "modform":
			$book->set_submit_name(__("Modify"));
			break;
		}
     
		if (!$book->been_here()) {
      switch ($action) { // internal switch
        case "addform":
 	// check if we are a physician
         if ($GLOBALS['this_user']->isPhysician()) {
		global $pnotesdoc;
 		$pnotesdoc = $GLOBALS['this_user']->getPhysician(); // if so, set as default
	}
	 global $pnotesdt;
         $pnotesdt     = date("Y-m-d");
         break; // end addform
        case "modform":
         //while(list($k,$v)=each($this->variables)) { global ${$v}; }

         if (($id<1) OR (strlen($id)<1)) {
           $page_title = _($this->record_name)." :: ".__("ERROR");
           $display_buffer .= "
             ".__("You must select a patient.")."
           ";
           template_display();
         }

         $r = freemed::get_link_rec ($id, $this->table_name);

	 if ($r['locked'] > 0) {
		$display_buffer .= "
		<div ALIGN=\"CENTER\">
		".__("This record is locked, and cannot be modified.")."
		</div>

		<p/>
		
		<div ALIGN=\"CENTER\">
		".
		(($return == "manage") ?
		"<a href=\"manage.php?id=$patient\">".__("Manage Patient")."</a>" :
		"<a href=\"module_loader.php?module=".get_class($this)."\">".
			__("back")."</a>" )
		."\n</div>\n";
		return false;
	 }
	 
         foreach ($r AS $k => $v) {
           global ${$k}; ${$k} = stripslashes($v);
         }
  	 	 extract ($r);
         break; // end modform
      } // end internal switch
     } // end checking if been here

	// Check for progress notes templates addon module
	if (check_module("ProgressNotesTemplates") and ($action=='addform')) {
		// Create picklist widget
		$pnt_array = array (
			__("Progress Notes Template") =>
			module_function(
				'ProgressNotesTemplates', 
				'picklist', 
				array('pnt', $book->formname)
			)
		);

		// Check for used status
		module_function(
			'ProgressNotesTemplates',
			'retrieve',
			array('pnt')
		);
	} else {
		$pnt_array = array ("" => "");
	}

     // Check episode of care dependency
     if(check_module("EpisodeOfCare")) {
       // Actual piece
       global $pnoteseoc;
	$pnoteseoc = sql_squash($pnoteseoc); // for multiple choice (HACK)
       $related_episode_array = array (
         __("Related Episode(s)") =>
	 module_function('EpisodeOfCare','widget',array('pnoteseoc', $patient))
        );
     } else {
        // Put in blank array instead
	$related_episode_array = array ("" => "");
     }
     $book->add_page (
       __("Basic Information"),
       array ("pnotesdoc", "pnotesdescrip", "pnoteseoc", date_vars("pnotesdt")),
       "<input TYPE=\"HIDDEN\" NAME=\"pnt_used\" VALUE=\"\"/>\n".
       html_form::form_table (
        array_merge (
	$pnt_array,
        array (
	 __("Provider") =>
	module_function('providermodule', 'widget', 'pnotesdoc'),
	   
         __("Description") =>
	html_form::text_widget("pnotesdescrip", 25, 100)
	),
	$related_episode_array,
	array (
         __("Date") => fm_date_entry("pnotesdt") 
	 )
        ) // end array_merge	
        )
      );

	if (substr($_REQUEST['action'], 0, 3) == 'add') {
	// form ...
	function __form_procs ( ) {
		$procs = $_REQUEST['procs'];
		if (!is_array($procs)) { return ''; }
		foreach ( $procs AS $p ) {
			$r .= '<div id="procs_div_'.$p.'">'.
			'<input type="hidden" name="procs[]" value="'.$p.'" /> '.
			module_function('cptmaintenance', 'to_text', array($p)).
			'[<a onClick="removePx('.$p.')">X</a>]</div>';

		}
		return $r;
	}
	function __form_dx ( ) {
		$dx = $_REQUEST['dx'];
		if (!is_array($dx)) { return ''; }
		foreach ( $dx AS $d ) {
			$r .= '<div id="dx_div_'.$d.'">'.
			'<input type="hidden" name="dx[]" value="'.$d.'" /> '.
			module_function('icdmaintenance', 'to_text', array($d)).
			'[<a onClick="removeDx('.$d.')">X</a>]</div>';

		}
		return $r;
	}

	$book->add_page(
		__("Superbill"),
		array ( 'procs', 'dx' ),
		html_form::form_table( array (
			__("Procedures") => "
			<script language=\"javascript\">
			function addPx ( ) {
				container = document.getElementById('px_container');
				id = document.getElementById('p').value;
				text = document.getElementById('p_text').value;
				if ( id != '' && id > 0 ) {
					var tempElement;
					try {
						tempElement = document.getElementById('procs_div_'+id);
						if (!tempElement.innerHTML) {

							tempElement = document.createElement('div');
							tempElement.id = 'procs_div_'+id;
						}
					} catch (err) {
						tempElement = document.createElement('div');
						tempElement.id = 'procs_div_'+id;
					}
					tempElement.innerHTML = '<input type=\"hidden\" name=\"procs[]\" value=\"'+id+'\" /> '+
						text +
						'[<a onClick=\"removePx('+id+')\">X</a>]';
					container.appendChild( tempElement );
				}
			}
			function removePx ( id ) {
				try {
					document.getElementById('procs_div_'+id).innerHTML = '';
				} catch (err) { }
			}
			</script>
			".module_function('cptmaintenance', 'widget', array ('p'))."
			<input type=\"button\" class=\"button\" value=\"Add Procedure\" onClick=\"addPx(); return true;\" />
			<br/>
			<div id=\"px_container\">
			".__form_procs()."
			</div>
			",
			__("Diagnoses") => "
			<script language=\"javascript\">
			function addDx ( ) {
				container = document.getElementById('dx_container');
				id = document.getElementById('d').value;
				text = document.getElementById('d_text').value;
				if ( id != '' && id > 0 ) {
					var tempElement;
					try {
						tempElement = document.getElementById('dx_div_'+id);
						if (!tempElement.innerHTML) {

							tempElement = document.createElement('div');
							tempElement.id = 'dx_div_'+id;
						}
					} catch (err) {
						tempElement = document.createElement('div');
						tempElement.id = 'dx_div_'+id;
					}
					tempElement.innerHTML = '<input type=\"hidden\" name=\"dx[]\" value=\"'+id+'\" /> '+
						text +
						'[<a onClick=\"removeDx('+id+')\">X</a>]';
					container.appendChild( tempElement );
				}
			}
			function removeDx ( id ) {
				try {
					document.getElementById('dx_div_'+id).innerHTML = '';
				} catch (err) { }
			}
			</script>
			".module_function('icdmaintenance', 'widget', array ('d'))."
			<input type=\"button\" class=\"button\" value=\"Add Dx\" onClick=\"addDx(); return true;\" />
			<br/>
			<div id=\"dx_container\">
			".__form_dx()."
			</div>
			"
		) )
	);
	} // end superbill page

     $book->add_page (
       __("Subjective"),
       array ("pnotes_S"),
       html_form::form_table (
        array (
          __("Subjective") =>
		freemed::rich_text_area('pnotes_S', 30, 60, true),
	  " " => "<input type=\"submit\" class=\"button\" value=\"".__("Save")."\" />".
	  	"<input type=\"reset\" class=\"button\" value=\"".__("Revert to Saved")."\" />"
        )
       )
     );

     $book->add_page (
       __("Objective"),
       array ("pnotes_O"),
       html_form::form_table (
        array (
          __("Objective") =>
		freemed::rich_text_area('pnotes_O', 30, 60, true),
		//html_form::text_area('pnotes_O', 'VIRTUAL', 20, 75),
	  " " => "<input type=\"submit\" class=\"button\" value=\"".__("Save")."\" />".
	  	"<input type=\"reset\" class=\"button\" value=\"".__("Revert to Saved")."\" />"
        )
       )
     );

     $book->add_page (
       __("Assessment"),
       array ("pnotes_A"),
       html_form::form_table (
        array (
          __("Assessment") =>
		freemed::rich_text_area('pnotes_A', 30, 60, true),
		//html_form::text_area('pnotes_A', 'VIRTUAL', 20, 75),
	  " " => "<input type=\"submit\" class=\"button\" value=\"".__("Save")."\" />".
	  	"<input type=\"reset\" class=\"button\" value=\"".__("Revert to Saved")."\" />"
        )
       )
     );

     $book->add_page (
       __("Plan"),
       array ("pnotes_P"),
       html_form::form_table (
        array (
          __("Plan") =>
		freemed::rich_text_area('pnotes_P', 30, 60, true),
		//html_form::text_area('pnotes_P', 'VIRTUAL', 20, 75),
	  " " => "<input type=\"submit\" class=\"button\" value=\"".__("Save")."\" />".
	  	"<input type=\"reset\" class=\"button\" value=\"".__("Revert to Saved")."\" />"
        )
       )
     );

     $book->add_page (
       __("Interval"),
       array ("pnotes_I"),
       html_form::form_table (
        array (
          __("Interval") =>
		//freemed::rich_text_area('pnotes_I', 30, 60, true),
		html_form::text_area('pnotes_I', 'VIRTUAL', 20, 75),
	  " " => "<input type=\"submit\" class=\"button\" value=\"".__("Save")."\" />".
	  	"<input type=\"reset\" class=\"button\" value=\"".__("Revert to Saved")."\" />"
        )
       )
     );

     $book->add_page (
       __("Education"),
       array ("pnotes_E"),
       html_form::form_table (
        array (
          __("Education") =>
		freemed::rich_text_area('pnotes_E', 30, 60, true),
		//html_form::text_area('pnotes_E', 'VIRTUAL', 20, 75),
	  " " => "<input type=\"submit\" class=\"button\" value=\"".__("Save")."\" />".
	  	"<input type=\"reset\" class=\"button\" value=\"".__("Revert to Saved")."\" />"
        )
       )
     );

     $book->add_page (
       __("Rx"),
       array ("pnotes_R"),
       html_form::form_table (
        array (
          __("Prescription") =>
		freemed::rich_text_area('pnotes_R', 30, 60, true),
		//html_form::text_area('pnotes_R', 'VIRTUAL', 20, 75),
	  " " => "<input type=\"submit\" class=\"button\" value=\"".__("Save")."\" />".
	  	"<input type=\"reset\" class=\"button\" value=\"".__("Revert to Saved")."\" />"
        )
       )
     );

	// Calculate BMI, if it exists
	if ($_REQUEST['pnotesheight'] > 0) {
		// English is ( W / H^2 ) * 703
		// Metric  is ( W / H^2 )
		$bmi = ($_REQUEST['pnotesweight'] / ( pow($_REQUEST['pnotesheight'],2) ) ) * 703;
		// And we'll round off to two decimal places
		$bmi = bcadd($bmi, 0, 2);
	}

	// Vital signs page
	$book->add_page(
		__("Vitals"),
		array(
			'pnotessbp',
			'pnotesdbp',
			'pnotestemp',
			'pnotesheartrate',
			'pnotesresprate',
			'pnotesweight',
			'pnotesheight'
		), html_form::form_table(array(
			__("Blood Pressure") => html_form::number_pulldown('pnotessbp', 0, 250)."<b>/</b>".
				html_form::number_pulldown('pnotesdbp', 0, 150),
			__("Temperature") => html_form::number_pulldown('pnotestemp', 90, 108, .1, false),
			__("Heart Rate") => html_form::number_pulldown('pnotesheartrate', 0, 300),
			__("Respiratory Rate") => html_form::number_pulldown('pnotesresprate', 0, 50),
			__("Weight") => html_form::number_pulldown('pnotesweight', 0, 650),
			__("Height") => html_form::number_pulldown('pnotesheight', 0, 84),
			__("BMI") => prepare($bmi)." &nbsp; <input type=\"submit\" class=\"button\" value=\"".__("Calculate")."\" />"
		))
	);

	// Handle cancel action
	if ($book->is_cancelled()) {
		// Unlock record, if it is locked
		$__lock = CreateObject('_FreeMED.RecordLock', $this->table_name);
		$__lock->UnlockRow ( $_REQUEST['id'] );

		if ($return=='manage') {
			Header("Location: manage.php?id=".urlencode($patient));
		} else {
			Header("Location: ".$this->page_name."?".
				"module=".$this->MODULE_CLASS."&".
				"patient=".$patient);
		}
		die("");
	}

     if (!$book->is_done()) {
      $display_buffer .= $book->display();
     } else {
       switch ($action) {
        case "addform": case "add":
         $display_buffer .= "
           <div ALIGN=\"CENTER\"><b>".__("Adding")." ... </b>
         ";
           // preparation of values
         $pnotesdtadd = $cur_date;
         $pnotesdtmod = $cur_date;

           // actual addition
	global $patient, $locked, $__ISO_SET__, $id;
	$query = $sql->insert_query (
		$this->table_name,
		array (
			"pnotespat"      => $patient,
			"pnoteseoc",
			"pnotesdoc",
			"pnotesdt"       => fm_date_assemble("pnotesdt"),
			"pnotesdescrip",
			"pnotesdtadd"    => date("Y-m-d"),
			"pnotesdtmod"    => date("Y-m-d"),
			"pnotes_S",
			"pnotes_O",
			"pnotes_A",
			"pnotes_P",
			"pnotes_I",
			"pnotes_E",
			"pnotes_R",
			'pnotessbp',
			'pnotesdbp',
			'pnotestemp',
			'pnotesheartrate',
			'pnotesresprate',
			'pnotesweight',
			'pnotesheight',
			'pnotesbmi'	 => $bmi,
			"locked"         => $locked,
			"iso"            => $__ISO_SET__
		)
	);
         break;

	case "modform": case "mod":
         $display_buffer .= "
           <div ALIGN=\"CENTER\"><b>".__("Modifying")." ... </b>
         ";
	global $patient, $__ISO_SET__, $locked, $id;
	$query = $sql->update_query (
		$this->table_name,
		array (
			"pnotespat"      => $patient,
			"pnoteseoc",
			"pnotesdoc",
			"pnotesdt"       => fm_date_assemble("pnotesdt"),
			"pnotesdescrip",
			"pnotesdtmod"    => date("Y-m-d"),
			"pnotes_S",
			"pnotes_O",
			"pnotes_A",
			"pnotes_P",
			"pnotes_I",
			"pnotes_E",
			"pnotes_R",
			"locked"         => $locked,
			"iso"            => $__ISO_SET__
		),
		array ( "id" => $id )
	);
	 break;
       } // end inner switch
       // now actually send the query
       $result = $sql->query ($query);
       if ($debug) $display_buffer .= "(query = '$query') ";
       if ($result)
         $display_buffer .= " <b> ".__("done").". </b>\n";
       else
         $display_buffer .= " <b> <font COLOR=\"#ff0000\">".__("ERROR")."</font> </b>\n";

	// handle superbills if they exist
	if ( is_array($_REQUEST['dx']) or is_array($_REQUEST['procs']) ) {
		$GLOBALS['sql']->query($GLOBALS['sql']->insert_query(
			'superbill',
			array (
				'dateofservice' => fm_date_assemble('pnotesdt'),
				'enteredby' => $this_user->user_number,
				'patient',
				'note' => $_REQUEST['pnotesdescrip'],
				'procs' => join ( ',', $_REQUEST['procs'] ),
				'dx' => join ( ',', $_REQUEST['dx'] ),
				'reviewed' => 0
			)
		));
	}

       $display_buffer .= "
        </div>
        <p/>
         <div ALIGN=\"CENTER\"><a HREF=\"manage.php?id=$patient\"
          >".__("Manage Patient")."</a>
         <b>|</b>
         <a HREF=\"$this->page_name?module=$module&patient=$patient\"
          >".__($this->record_name)."</a>
	  ";
       if ($action=="mod" OR $action=="modform")
         $display_buffer .= "
	 <b>|</b>
	 <a HREF=\"$this->page_name?module=$module&patient=$patient&action=view&id=$id\"
	  >".__("View $this->record_name")."</a>
	 ";
       $display_buffer .= "
         </div>
         <p/>
         ";

	 // Handle returning to patient management screen after add
	 global $refresh;
	 if ($_REQUEST['return'] == 'manage') {
		$refresh = 'manage.php?id='.urlencode($patient).'&ts='.urlencode(mktime());
	 }
     } // end if is done


	} // end of function ProgressNotes->form()

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
     $r = freemed::get_link_rec ($id, "pnotes");
     if (is_array($r)) extract ($r);
     $pnotesdt_formatted = substr ($pnotesdt, 0, 4). "-".
                           substr ($pnotesdt, 5, 2). "-".
                           substr ($pnotesdt, 8, 2);
     $pnotespat = $r ["pnotespat"];
     $pnoteseoc = sql_expand ($r["pnoteseoc"]);

     $this->this_patient = CreateObject('FreeMED.Patient', $pnotespat);

     $display_buffer .= "
       <p/>
       ".template::link_bar(array(
        __("Progress Notes") =>
       $this->page_name."?module=$module&patient=$pnotespat",
        __("Manage Patient") =>
       "manage.php?id=$pnotespat",
	__("Select Patient") =>
        "patient.php",
	( freemed::acl_patient('emr', 'modify', $patient) ? __("Modify") : "" ) =>
        $this->page_name."?module=$module&patient=$patient&id=$id&action=modform",
	__("Print") =>
        "module_loader.php?module=".get_class($this)."&patient=$patient&".
        "action=print&id=".$r['id']
       ))."
       <p/>

       <CENTER>
        <B>Relevant Date : </B>
         $pnotesdt_formatted
       </CENTER>
       <P>
     ";
     // Check for EOC stuff
     if (count($pnoteseoc)>0 and is_array($pnoteseoc) and check_module("episodeOfCare")) {
      $display_buffer .= "
       <CENTER>
        <B>".__("Related Episode(s)")."</B>
        <BR>
      ";
      for ($i=0;$i<count($pnoteseoc);$i++) {
        if ($pnoteseoc[$i] != -1) {
          $e_r     = freemed::get_link_rec ($pnoteseoc[$i]+0, "eoc"); 
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
     $display_buffer .= "<CENTER>\n";

     // Crappy hack to get around not detecting <br />'s
     $pnotes_S = str_replace (' />', '/>', $pnotes_S);
     $pnotes_O = str_replace (' />', '/>', $pnotes_O);
     $pnotes_A = str_replace (' />', '/>', $pnotes_A);
     $pnotes_P = str_replace (' />', '/>', $pnotes_P);
     $pnotes_I = str_replace (' />', '/>', $pnotes_I);
     $pnotes_E = str_replace (' />', '/>', $pnotes_E);
     $pnotes_R = str_replace (' />', '/>', $pnotes_R);

      if (strlen($pnotes_S) > 7) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=\"CENTER\"><B>".__("Subjective")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotes_S) ?
		prepare($pnotes_S) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotes_S))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotes_O) > 7) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><B>".__("Objective")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotes_O) ?
		prepare($pnotes_O) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotes_O))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotes_A) > 7) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><B>".__("Assessment")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotes_A) ?
		prepare($pnotes_A) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotes_A))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotes_P) > 7) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><B>".__("Plan")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotes_P) ?
		prepare($pnotes_P) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotes_P))) )."
       </TD></TR></TABLE>
       ";
      if (!empty($pnotes_I)) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><B>".__("Interval")."</B></TD></TR>
       <TR BGCOLOR=\"#ffffff\"><TD>
		".( eregi("<[A-Z/]*>", $pnotes_I) ?
		prepare($pnotes_I) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotes_I))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotes_E) > 7) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><B>".__("Education")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotes_E) ?
		prepare($pnotes_E) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotes_E))) )."
       </TD></TR></TABLE> 
       ";
      if (strlen($pnotes_R) > 7) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><B>".__("Rx")."</B></TD></TR>
		".( eregi("<[A-Z/]*>", $pnotes_R) ?
		prepare($pnotes_R) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotes_R))) )."
       </TD></TR></TABLE>
      ";
        // back to your regularly sceduled program...
      $display_buffer .= "
       <p/>
       ".template::link_bar(array(
        __("Progress Notes") =>
       $this->page_name."?module=$module&patient=$pnotespat",
        __("Manage Patient") =>
       "manage.php?id=$pnotespat",
	__("Select Patient") =>
        "patient.php",
	( freemed::acl_patient('emr', 'modify', $patient) ? __("Modify") : "" ) =>
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
			"WHERE (pnotespat='".addslashes($patient)."') ".
			freemed::itemlist_conditions(false)." ".
			( $condition ? 'AND '.$condition : '' )." ".
			"ORDER BY pnotesdt";
		$result = $sql->query ($query);

		$display_buffer .= freemed_display_itemlist(
			$result,
			$this->page_name,
			array (
				__("Date")        => "pnotesdt",
				__("Description") => "pnotesdescrip"
			), // array
			array (
				"",
				__("NO DESCRIPTION")
			),
			NULL, NULL, NULL,
			ITEMLIST_MOD | ITEMLIST_VIEW | ITEMLIST_DEL | ITEMLIST_LOCK
		);
		$display_buffer .= "\n<p/>\n";
	} // end function ProgressNotes->view()

	// Method: noteForDate
	//
	//	Determines if a progress note was entered for a particular
	//	appointment.
	//
	// Parameters:
	//
	//	$patient - ID for patient record
	//
	//	$date - Date to be queried
	//
	// Returns:
	//
	//	Boolean, whether or not a note exists.
	//
	function noteForDate ( $patient, $date ) {
		$q = "SELECT COUNT(id) AS my_count ".
			"FROM ".$this->table_name." WHERE ".
			"pnotespat = '".addslashes($patient)."' AND ".
			"pnotesdt = '".addslashes($date)."'";
		$res = $GLOBALS['sql']->query($q);
		$r = $GLOBALS['sql']->fetch_array($res);
		if ($r['my_count'] > 0) {
			return true;
		} else {
			return false;
		}
	} // end method noteForDate

	function _update() {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);
		// Version 0.3
		//
		//	Vitals information added
		//
		if (!version_check($version, '0.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN pnotessbp INT UNSIGNED AFTER pnotes_R');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN pnotesdbp INT UNSIGNED AFTER pnotessbp');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN pnotestemp INT UNSIGNED AFTER pnotesdbp');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN pnotesheartrate INT UNSIGNED AFTER pnotestemp');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN pnotesresprate INT UNSIGNED AFTER pnotesheartrate');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN pnotesweight INT UNSIGNED AFTER pnotesresprate');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN pnotesheight INT UNSIGNED AFTER pnotesweight');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN pnotesbmi INT UNSIGNED AFTER pnotesheight');
		} // end version 0.3 updates
		// Version 0.3.1
		//
		//	Temperature should be a REAL
		//
		if (!version_check($version, '0.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN pnotestemp pnotestemp REAL');
		} // end version 0.3.1 update
	} // end _update
} // end of class ProgressNotes

register_module ("ProgressNotes");

?>
