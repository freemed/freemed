<?php
	// $Id$
        // $Extensions by RPL 06/29/2005 $
	// $Based on work by rufustfirefly:  progress_notes.emr.module.php v 1.49$
	
LoadObjectDependency('_FreeMED.EMRModule');

class EncounterNotes extends EMRModule {

	var $MODULE_NAME = "Encounter Notes";
	var $MODULE_AUTHOR = "RPL RPL121@verizon.net -- adapted from jeff b Progress Notes";
	var $MODULE_VERSION = "0.3.1";
	var $MODULE_DESCRIPTION = "
		FreeMED Encounter Notes allow physicians and
		providers to track patient activity through
		SOAP style notes and structured history
		and physical.
	";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';
	var $record_name   = "Encounter Notes";
	var $table_name    = "enotes";
	var $patient_field = "pnotespat";
	var $widget_hash   = "##pnotesdt## ##pnotesdescrip##";
	var $print_template = 'encounter_notes';

	function EncounterNotes () {
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
	                'pnotesgeneral' => SQL__TEXT,
			'pnotesbmi' => SQL__INT_UNSIGNED(0),
			'iso' => SQL__VARCHAR(15),
			'locked' => SQL__INT_UNSIGNED(0),
			'id' => SQL__SERIAL,
			'pnotescc' => SQL__TEXT,
			'pnoteshpi' => SQL__TEXT,
			'pnotesroscons' => SQL__TEXT,
			'pnotesroseyes' => SQL__TEXT,
			'pnotesrosent' => SQL__TEXT,
			'pnotesroscv' => SQL__TEXT,
			'pnotesrosresp' => SQL__TEXT,
			'pnotesrosgi' => SQL__TEXT,
			'pnotesrosgu' => SQL__TEXT,
			'pnotesrosms' => SQL__TEXT,
                        'pnotesrosskinbreast' => SQL__TEXT,
			'pnotesrosneuro' => SQL__TEXT,
		        'pnotesrospsych' => SQL__TEXT,
			'pnotesrosendo' => SQL__TEXT,
			'pnotesroshemelymph' => SQL__TEXT,
			'pnotesrosallergyimmune' => SQL__TEXT,
			'pnotesph' => SQL__TEXT,
			'pnotesfh' => SQL__TEXT,
			'pnotessh' => SQL__TEXT,
		        'pnotespeeyes' => SQL__TEXT,
			'pnotespeent' => SQL__TEXT,
			'pnotespeneck' => SQL__TEXT,
			'pnotesperesp' => SQL__TEXT,
			'pnotespecv' => SQL__TEXT,
			'pnotespechestbreast' => SQL__TEXT,
			'pnotespegiabd' => SQL__TEXT,
			'pnotespegu' => SQL__TEXT,
			'pnotespelymph' => SQL__TEXT,
			'pnotespems' => SQL__TEXT,
			'pnotespeskin' => SQL__TEXT,
			'pnotespeneuro' => SQL__TEXT,
			'pnotespepsych' => SQL__TEXT,
			'pnoteshandp' => SQL__TEXT
						 
		);
		
		// Define variables for EMR summary
		$this->summary_vars = array (
			__("Date")        =>	"my_date",
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
	} // end constructor EncounterNotes

	function add () { $this->form(); }
	function mod () { $this->form(); }

	function form () {
		global $display_buffer, $sql, $pnoteseoc;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		$book = CreateObject('PHP.notebook',
				array ("id", "module", "patient", "action", "return"),
				NOTEBOOK_COMMON_BAR | NOTEBOOK_STRETCH, 6);

		switch ($action) {
			case "add": case "addform":
			$book->set_submit_name(__("Add"));
			break;

			case "mod": case "modform":
			$book->set_submit_name(__("Modify"));
			break;
		}
     
		if (!$book->been_here()) {      switch ($action) { // internal switch
        case "addform":
 	// check if we are a physician
         if ($this->this_user->isPhysician()) {
		global $pnotesdoc;
 		$pnotesdoc = $this->this_user->getPhysician(); // if so, set as default
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

	// Check for encounter notes templates addon module
	if (check_module("EncounterNotesTemplates")
// RPL commented off 08/17/2005
//          and ($action=='addform')
	  ) {
		// Create picklist widget
		$pnt_array = array (
			__("Encounter Notes Template") =>
			module_function(
				'EncounterNotesTemplates', 
				'picklist', 
				array('pnt', $book->formname)
			)
		);

		// Check for used status
		module_function(
			'EncounterNotesTemplates',
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
       __("Basic Info"),
       array ("pnotesdoc", "pnotesdescrip", "pnoteseoc", date_vars("pnotesdt")),
       "<input TYPE=\"HIDDEN\" NAME=\"pnt_used\" VALUE=\"\"/>\n".
       html_form::form_table (
        array_merge (
	$pnt_array,
        array (
	 __("Provider") =>
	   freemed_display_selectbox (
            $sql->query ("SELECT * FROM physician ".
	    	"WHERE phyref != 'yes' AND phylname != '' ".
		"ORDER BY phylname,phyfname"),
	    "#phylname#, #phyfname#",
	    "pnotesdoc"
	   ),
	   
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

	    Include_once(freemed::template_file('ajax.php'));    
     $book->add_page (
       __("<u>S</u>OAP Note"),
       array ("pnotes_S", "pnotes_O", "pnotes_A", "pnotes_P"),
       html_form::form_table (
        array (
          __("Subjective") =>
	       html_form::text_area('pnotes_S', 'VIRTUAL', 10, 60),
	       " " => ajax_insert_module_text('pnotes_S', $_REQUEST['patient']),
	  __("Objective") =>

	    freemed_display_selectbox (
            $sql->query ("SELECT * FROM enotes ".
//	    	"WHERE ...  qualify by patient
		"ORDER BY pnotesdtadd"),
	    "#pnotesdtadd#, #pnotes_O#",
	    $res
	   ),

//	     pnotes_O = freemed::get_link_field($res, "enotes", "pnotes_S");
	       
//	       html_form::text_area('pnotes_O', 'VIRTUAL',10, 60),
	  __("Assessment") =>
	       html_form::text_area('pnotes_A', 'VIRTUAL', 10, 60),
	  __("Plan") =>
	       html_form::text_area('pnotes_P', 'VIRTUAL', 10, 60)
        )
       )
     );

     $book->add_page (
       __("<u>I</u>ER"),
       array ('pnotes_I', 'pnotes_E', 'pnotes_R'),
       html_form::form_table (
        array (
          __("Interval") =>
		html_form::text_area('pnotes_I', 'VIRTUAL', 10, 60),
          __("Education") =>
                html_form::text_area('pnotes_E', 'VIRTUAL', 10, 60),
	  __("Rx") =>
		html_form::text_area('pnotes_R', 'VIRTUAL', 10, 60),
         	" " => ajax_insert_module_text('pnotes_R', $_REQUEST['patient'])

	       )
       )
     );


 
	// Calculate BMI, if it exists
	if ($_REQUEST['pnotesheight'] > 0) {
		// English is ( W / H^2 ) * 703
		// Metric  is ( W / H^2 )
		$bmi = ($_REQUEST['pnotesweight'] / ( pow($_REQUEST['pnotesheight'],2) ) ) * 703;
		// And we'll round off to one decimal place
		$bmi = bcadd($bmi, 0, 1);
	}

	// Vital signs page
	$book->add_page(
		__("Vitals/General"),
		array(
			'pnotessbp',
		        'pnotesdbp',
			'pnotestemp',
			'pnotesheartrate',
			'pnotesresprate',
			'pnotesweight',
			'pnotesheight',
		        'pnotesgeneral'
		), html_form::form_table(array(
//			__("Blood Pressure") => html_form::number_pulldown('pnotessbp', 0, 250)."<b>/</b>".
//				html_form::number_pulldown('pnotesdbp', 0, 150),
			__("Blood Pressure") => html_form::text_widget('pnotessbp', 3)."<b> / </b>".
				html_form::text_widget('pnotesdbp', 3),					       
			__("Temperature") => html_form::number_pulldown('pnotestemp', 90, 108, .1, false),
//			__("Temperature" => html_form::text_widget('pnotestemp',4, 3),
//		        __("Heart Rate") => html_form::number_pulldown('pnotesheartrate', 0, 300),
		        __("Heart Rate") => html_form::text_widget('pnotesheartrate', 20),
//		        __("Respiratory Rate") => html_form::number_pulldown('pnotesresprate', 0, 50),
		        __("Respiratory Rate") => html_form::text_widget('pnotesresprate', 20),
//	         	__("Weight") => html_form::number_pulldown('pnotesweight', 0, 650),
			__("Weight") => html_form::text_widget('pnotesweight', 20),
//		        __("Height") => html_form::number_pulldown('pnotesheight', 1, 84),
			__("Height") => html_form::text_widget('pnotesheight', 20),
					       __("BMI") => prepare($bmi)." &nbsp; <input type=\"submit\" class=\"button\" value=\"".__("Calculate")."\" />",              
                        __("General (PE)") =>
		                     html_form::text_area('pnotesgeneral', 'VIRTUAL', 5, 60)

		       
					       
			  ))
	);

    $book->add_page (
       __("<U>C</U>C and HPI"),
       array ("pnotescc", "pnoteshpi"),
       html_form::form_table (
        array (
          __("CC") =>
		//freemed::rich_text_area('pnotescc', 10, 60, true),
		html_form::text_area('pnotescc', 'VIRTUAL', 3, 75),
	  __("HPI") =>
	       html_form::text_area('pnoteshpi', 'VIRTUAL', 30, 75)
        )
       )
     );

//     $book->add_page (
//       __("<U>H</U>PI"),
//       array ("pnoteshpi"),
//      html_form::form_table (
//        array (
//          __("HPI") =>
//		//freemed::rich_text_area('pnoteshpi', 30, 60, true),
//		html_form::text_area('pnoteshpi', 'VIRTUAL', 20, 75)
//       )
//       )
//     );

	         $book->add_page (
       __("<u>R</u>eview of Systems"),
       array ("pnotesroscons",
	      "pnotesroseyes",
	      "pnotesrosent",
	      "pnotesroscv",
	      "pnotesrosresp",
	      "pnotesrosgi",
	      "pnotesrosgu",
	      "pnotesrosms",
	      "pnotesrosskinbreast",
	      "pnotesrosneuro",
	      "pnotesrospsych",
	      "pnotesrosendo",
	      "pnotesroshemelymph",
	      "pnotesrosallergyimmune"),

	        html_form::form_table (
        array (
          __("Constitutional") =>
		html_form::text_area('pnotesroscons', 'VIRTUAL', 5, 60),
	  __("Eyes") =>
	        html_form::text_area('pnotesroseyes', 'VIRTUAL', 5, 60),
	  __("ENT") =>
	        html_form::text_area('pnotesrosent', 'VIRTUAL', 5 ,60),
	  __("CV") =>
	        html_form::text_area('pnotesroscv', 'VIRTUAL', 5, 60),
          __("Resp") =>
	        html_form::text_area('pnotesrosresp', 'VIRTUAL', 5,60),
	  __("GI") =>
	       html_form::text_area('pnotesrosgi', 'VIRTUAL', 5, 60),
	  __("GU") =>
	       html_form::text_area('pnotesrosgu', 'VIRTUAL', 5, 60),
	  __("MS") =>
	       html_form::text_area('pnotesrosms', 'VIRTUAL', 5, 60),
	  __("Skin/breast") =>
	       html_form::text_area('pnotesrosskinbreast', 'VIRTUAL', 5, 60),
	  __("Neuro") =>
	       html_form::text_area('pnotesrosneuro', 'VIRTUAL', 5, 60),
	  __("Psych") =>
	       html_form::text_area('pnotesrospsych', 'VIRTUAL', 5, 60),
	  __("Endo") =>
	       html_form::text_area('pnotesrosendo', 'VIRTUAL', 5, 60),
	  __("Heme/lymph") =>
	       html_form::text_area('pnotesroshemelymph', 'VIRTUAL', 5, 60),
	  __("Allergy/immune") =>
	       html_form::text_area('pnotesrosallergyimmune', 'VIRTUAL', 5,60)
	       
	       )
       )
     );

     $book->add_page (
       __("<u>P</u>ast History"),
       array ("pnotesph"),
       html_form::form_table (
        array (
          __("PH") =>
		//freemed::rich_text_area('pnotesph', 30, 60, true),
		html_form::text_area('pnotesph', 'VIRTUAL', 20, 75),
	  " " => ajax_insert_module_text('pnotesph', $_REQUEST['patient']),
        )
       )
     );

     $book->add_page (
       __("<U>F</U>amily History"),
       array ("pnotesfh"),
       html_form::form_table (
        array (
          __("FH") =>
		//freemed::rich_text_area('pnotesfh', 30, 60, true),
		html_form::text_area('pnotesfh', 'VIRTUAL', 20, 75),
       
        )
       )
     );

     $book->add_page (
       __("<U>S</U>ocial History"),
       array ("pnotessh"),
       html_form::form_table (
        array (
          __("SH") =>
		//freemed::rich_text_area('pnotessh', 30, 60, true),
		html_form::text_area('pnotessh', 'VIRTUAL', 20, 75),
        )
       )
     );
	
    $book->add_page (
       __("<U>E</U>xam"),
       array ("pnotespeeyes",
	      "pnotespeent",
	      "pnotespeneck",
	      "pnotesperesp",
	      "pnotespecv",
	      "pnotespechestbreast",
	      "pnotespegiabd",
	      "pnotespegu",
	      "pnotespelymph",
	      "pnotespems",
	      "pnotespeskin",
	      "pnotespeneuro",
	      "pnotespepsych"
	      ),
       html_form::form_table (
        array (
          __("Eyes") =>
		html_form::text_area('pnotespeeyes', 'VIRTUAL', 5, 75),
          __("ENT") =>
	       html_form::text_area('pnotespeent', 'VIRTUAL', 5, 75),
	  __("Neck") =>
	       html_form::text_area('pnotespeneck', 'VIRTUAL', 5, 75),
	  __("Resp") =>
	       html_form::text_area('pnotesperesp', 'VIRTUAL', 5, 75),
	  __("CV") =>
	       html_form::text_area('pnotespecv', 'VIRTUAL', 5, 75),
	  __("Chest/breast") =>
	       html_form::text_area('pnotespechestbreast', 'VIRTUAL', 5, 75),
	  __("GI/abdomen") =>
	       html_form::text_area('pnotespegiabd', 'VIRTUAL', 5, 75),
	  __("GU") =>
	       html_form::text_area('pnotespegu', 'VIRTUAL', 5, 75),
          __("Lymphatics") =>
	       html_form::text_area('pnotespelymph', 'VIRTUAL', 5, 75),
	  __("MS") =>
	       html_form::text_area('pnotespems', 'VIRTUAL', 5, 75),
	  __("Skin") =>
	       html_form::text_area('pnotespeskin', 'VIRTUAL', 5, 75),
	  __("Neuro") =>
	       html_form::text_area('pnotespeneuro', 'VIRTUAL', 5, 75),
	  __("Psych") =>
	       html_form::text_area('pnotespepsych', 'VIRTUAL', 5, 75),
	       )
       )
     );

     $book->add_page (
       __("<U>A</U>ssessment/Plan"),
       array ("pnotes_A", "pnotes_P"),
       html_form::form_table (
        array (
          __("Assessment") =>
		//freemed::rich_text_area('pnotes_A', 30, 60, true),
		html_form::text_area('pnotes_A', 'VIRTUAL', 10, 75),
	  __("Plan") =>
	       html_form::text_area('pnotes_P', 'VIRTUAL', 10, 75)
        )
       )
     );
	    
	
    $book->add_page (
       __("<U>F</U>ree Form Entry"),
       array ("pnoteshandp"),
       html_form::form_table (
        array (
          __("Free Form Entry") =>
		freemed::rich_text_area('pnoteshandp', 17, 112, true),
                //html_form::text_area('pnoteshandp', 'VIRTUAL', 40, 75),
	       )
       )
     );
	
	
	
	// Handle cancel action
	if ($book->is_cancelled()) {
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
		        'pnotesbmi'  => $bmi,
		        'pnotesgeneral',
		        'pnotescc',
		        'pnoteshpi',
		        'pnotesroscons',
		        'pnotesroseyes',
		        'pnotesrosent',
		        'pnotesroscv',
		        'pnotesrosresp',
		        'pnotesrosgi',
		        'pnotesrosgu',
		        'pnotesrosms',
		        'pnotesrosskinbreast',
		        'pnotesrosneuro',
		        'pnotesrospsych',
		        'pnotesrosendo',
		        'pnotesroshemelymph',
		        'pnotesrosallergyimmune',
		        'pnotesph',
		        'pnotesfh',
		        'pnotessh',
		        'pnotespeeyes',
		        'pnotespeent',
		        'pnotespeneck',
		        'pnotesperesp',
		        'pnotespecv',
		        'pnotespechestbreast',
                        'pnotespegiabd',
		        'pnotespegu',
		        'pnotespelymph',
		        'pnotespems',
		        'pnotespeskin',
		        'pnotespeneuro',
		        'pnotespepsych',
			'pnoteshandp',
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
		        'pnotesbmi' => $bmi,
		        'pnotesgeneral',
		        'pnotescc',
		        'pnoteshpi',
		        'pnotesroscons',
		        'pnotesroseyes',
		        'pnotesrosent',
		        'pnotesroscv',
		        'pnotesrosresp',
		        'pnotesrosgi',
		        'pnotesrosgu',
		        'pnotesrosms',
		        'pnotesrosskinbreast',
		        'pnotesrosneuro',
		        'pnotesrospsych',
		        'pnotesrosendo',
		        'pnotesroshemelymph',
		        'pnotesrosallergyimmune',
		        'pnotesph',
		        'pnotesfh',
		        'pnotessh',
		        'pnotespeeyes',
		        'pnotespeent',
		        'pnotespeneck',
		        'pnotesperesp',
		        'pnotespecv',
		        'pnotespechestbreast',
                        'pnotespegiabd',
		        'pnotespegu',
		        'pnotespelymph',
		        'pnotespems',
		        'pnotespeskin',
		        'pnotespeneuro',
		        'pnotespepsych',
			'pnoteshandp',
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


	} // end of function EncounterNotes->form()

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
     $r = freemed::get_link_rec ($id, "enotes");
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
        __("Encounter Notes") =>
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
     $pnotesgeneral = str_replace (' />', '/>', $pnotesgeneral);
     $pnotes_S = str_replace (' />', '/>', $pnotes_S);
     $pnotes_O = str_replace (' />', '/>', $pnotes_O);
     $pnotes_A = str_replace (' />', '/>', $pnotes_A);
     $pnotes_P = str_replace (' />', '/>', $pnotes_P);
     $pnotes_I = str_replace (' />', '/>', $pnotes_I);
     $pnotes_E = str_replace (' />', '/>', $pnotes_E);
     $pnotes_R = str_replace (' />', '/>', $pnotes_R);
     $pnotescc = str_replace (' />', '/>', $pnotescc);
     $pnoteshpi = str_replace (' />', '/>', $pnoteshpi);
     $pnotesroscons = str_replace (' />', '/>', $pnotesroscons);
     $pnotesroseyes = str_replace (' />', '/>', $pnotesroseyes);
     $pnotesrosent = str_replace (' />', '/>', $pnotesrosent);
     $pnotesroscv = str_replace (' />', '/>', $pnotesroscv);
     $pnotesrosresp = str_replace (' />', '/>', $pnotesrosresp);
     $pnotesrosgi = str_replace (' />', '/>', $pnotesrosgi);
     $pnotesrosgu = str_replace (' />', '/>', $pnotesrosgu);
     $pnotesrosms = str_replace (' />', '/>', $pnotesrosms);
     $pnotesrosskinbreast = str_replace (' />', '/>', $pnotesrosskinbreast);
     $pnotesrosneuro = str_replace (' />', '/>', $pnotesrosneuro);
     $pnotesrospsych = str_replace (' />', '/>', $pnotesrospsych);
     $pnotesrosendo = str_replace (' />', '/>', $pnotesrosendo);
     $pnotesroshemelymph = str_replace (' />', '/>', $pnotesroshemelymph);
     $pnotesrosallergyimmune = str_replace (' />', '/>', $pnotesrosallergyimmune);
     $pnotesph = str_replace (' />', '/>', $pnotesph);
     $pnotesfh = str_replace (' />', '/>', $pnotesfh);
     $pnotessh = str_replace (' />', '/>', $pnotessh);
     $pnotespeeyes = str_replace (' />', '/>', $pnotespeeyes);
     $pnotespeent = str_replace (' />', '/>', $pnotespeent);
     $pnotespeneck = str_replace (' />', '/>', $pnotespeneck);
     $pnotesperesp = str_replace (' />', '/>', $pnotesperesp);
     $pnotespecv = str_replace (' />', '/>', $pnotespecv);
     $pnotespechestbreast = str_replace (' />', '/>', $pnotespechestbreast);
     $pnotespegiabd = str_replace (' />', '/>', $pnotespegiabd);
     $pnotespegu = str_replace (' />', '/>', $pnotespegu);
     $pnotespelymph = str_replace (' />', '/>', $pnotespelymph);
     $pnotespems = str_replace (' />', '/>', $pnotespems);
     $pnotespeskin = str_replace (' />', '/>', $pnotespeskin);
     $pnotespeneuro = str_replace (' />', '/>', $pnotespeneuro);
     $pnotespepsych = str_replace (' />', '/>', $pnotespepsych);
     $pnoteshandp = str_replace (' />', '/>', $pnoteshandp);

      if (strlen($pnotesgeneral) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>G</u>eneral")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotesgeneral) ?
		prepare($pnotesgeneral) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotesgeneral))) )."
       </TD></TR></TABLE>
       ";
	    
      if (strlen($pnotes_S) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>S</u>ubjective")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotes_S) ?
		prepare($pnotes_S) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotes_S))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotes_O) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<U>O</U>bjective")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotes_O) ?
		prepare($pnotes_O) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotes_O))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotes_A) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<U>A</U>ssessment")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotes_A) ?
		prepare($pnotes_A) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotes_A))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotes_P) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>P</u>lan")."</B></FONT></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotes_P) ?
		prepare($pnotes_P) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotes_P))) )."
       </TD></TR></TABLE>
       ";
      if (!empty($pnotes_I)) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>I</u>nterval")."</B></TD></TR>
       <TR BGCOLOR=\"#ffffff\"><TD>
		".( eregi("<[A-Z/]*>", $pnotes_I) ?
		prepare($pnotes_I) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotes_I))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotes_E) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>E</u>ducation")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotes_E) ?
		prepare($pnotes_E) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotes_E))) )."
       </TD></TR></TABLE> 
       ";
      if (strlen($pnotes_R) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>R</u>x")."</B></TD></TR>
       <TR BGCOLOR=#FFFFFF><td>
		".( eregi("<[A-Z/]*>", $pnotes_R) ?
		prepare($pnotes_R) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotes_R))) )."
       </TD></TR></TABLE>
      ";
      if (strlen($pnotescc) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>C</u>hief Complaint")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotescc) ?
		prepare($pnotescc) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotescc))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnoteshpi) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<U>H</U>istory of Present Illness")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnoteshpi) ?
		prepare($pnoteshpi) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnoteshpi))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotesroscons) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<U>C</U>onstitutional")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotesroscons) ?
		prepare($pnotesroscons) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotesroscons))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotesroseyes) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>E</u>yes")."</B></FONT></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotesroseyes) ?
		prepare($pnotesroseyes) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotesroseyes))) )."
       </TD></TR></TABLE>
       ";
      if (!empty($pnotesrosent)) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>E</u>ars Nose and Throat")."</B></TD></TR>
       <TR BGCOLOR=\"#ffffff\"><TD>
		".( eregi("<[A-Z/]*>", $pnotesrosent) ?
		prepare($pnotesrosent) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotesrosent))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotesroscv) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>C</u>ardiovascular")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotesroscv) ?
		prepare($pnotesroscv) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotesroscv))) )."
       </TD></TR></TABLE> 
       ";
      if (strlen($pnotesrosresp) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>R</u>espiratory")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
               ".( eregi("<[A-Z/]*>", $pnotesrosresp) ?
		prepare($pnotesrosresp) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotesrosresp))) )."
       </TD></TR></TABLE>
      ";
      if (strlen($pnotesrosgi) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>G</u>astrointestinal")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotesrosgi) ?
		prepare($pnotesrosgi) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotesrosgi))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotesrosgu) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<U>G</U>enitourinary")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotesrosgu) ?
		prepare($pnotesrosgu) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotesrosgu))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotesrosms) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<U>M</U>usculoskeletal")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotesrosms) ?
		prepare($pnotesrosms) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotesrosms))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotesrosskinbreast) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>S</u>kin and Breast")."</B></FONT></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotesrosskinbreast) ?
		prepare($pnotesrosskinbreast) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotesrosskinbreast))) )."
       </TD></TR></TABLE>
       ";
      if (!empty($pnotesrosneuro)) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>N</u>eurological")."</B></TD></TR>
       <TR BGCOLOR=\"#ffffff\"><TD>
		".( eregi("<[A-Z/]*>", $pnotesrosneuro) ?
		prepare($pnotesrosneuro) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotesrosneuro))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotesrospsych) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>P</u>sychiatric")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotesrospsych) ?
		prepare($pnotesrospsych) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotesrospsych))) )."
       </TD></TR></TABLE> 
       ";
      if (strlen($pnotesrosendo) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>E</u>ndocrine")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
 	        ".( eregi("<[A-Z/]*>", $pnotesrosendo) ?
		prepare($pnotesrosendo) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotesrosendo))) )."
       </TD></TR></TABLE>
      ";
      if (strlen($pnotesroshemelymph) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<U>H</U>ematologic and Lymphatic")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotesroshemelymph) ?
		prepare($pnotesroshemelymph) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotesroshemelymph))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotesrosallergyimmune) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>A</u>llergy and Immune")."</B></FONT></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotesrosallergyimmune) ?
		prepare($pnotesrosallergyimmune) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotesrosallergyimmune))) )."
       </TD></TR></TABLE>
       ";
      if (!empty($pnotesph)) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>P</u>ast History")."</B></TD></TR>
       <TR BGCOLOR=\"#ffffff\"><TD>
		".( eregi("<[A-Z/]*>", $pnotesph) ?
		prepare($pnotesph) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotesph))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotesfh) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>F</u>amily History")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotesfh) ?
		prepare($pnotesfh) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotesfh))) )."
       </TD></TR></TABLE> 
       ";
      if (strlen($pnotessh) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>S</u>ocial History")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotessh) ?
		prepare($pnotessh) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotessh))) )."
       </TD></TR></TABLE>
      ";
      if (strlen($pnotespeeyes) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>E</u>yes")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotespeeyes) ?
		prepare($pnotespeeyes) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotespeeyes))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotespeent) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<U>E</U>ars Nose and Throat")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotespeent) ?
		prepare($pnotespeent) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotespeent))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotespeneck) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<U>N</U>eck")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotespeneck) ?
		prepare($pnotespeneck) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotespeneck))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotesperesp) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>R</u>espiratory")."</B></FONT></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotesperesp) ?
		prepare($pnotesperesp) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotesperesp))) )."
       </TD></TR></TABLE>
       ";
      if (!empty($pnotespecv)) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>C</u>ardiovascular")."</B></TD></TR>
       <TR BGCOLOR=\"#ffffff\"><TD>
		".( eregi("<[A-Z/]*>", $pnotespecv) ?
		prepare($pnotespecv) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotespecv))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotespechestbreast) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>C</u>hest and Breasts")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotespechestbreast) ?
		prepare($pnotespechestbreast) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotespechestbreast))) )."
       </TD></TR></TABLE> 
       ";
      if (strlen($pnotespegiabd) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>G</u>astrointestinal and Abdomen")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotespegiabd) ?
		prepare($pnotespegiabd) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotespegiabd))) )."
       </TD></TR></TABLE>
      ";
      if (strlen($pnotespegu) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>G</u>enitourinary")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotespegu) ?
		prepare($pnotespegu) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotespegu))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotespelymph) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<U>L</U>ymphatics")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotespelymph) ?
		prepare($pnotespelymph) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotespelymph))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotespems) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<U>M</U>usculoskeletal")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotespems) ?
		prepare($pnotespems) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotespems))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotespeskin) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>S</u>kin")."</B></FONT></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotespeskin) ?
		prepare($pnotespeskin) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotespeskin))) )."
       </TD></TR></TABLE>
       ";
      if (!empty($pnotespeneuro)) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>N</u>eurological")."</B></TD></TR>
       <TR BGCOLOR=\"#ffffff\"><TD>
		".( eregi("<[A-Z/]*>", $pnotespeneuro) ?
		prepare($pnotespeneuro) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotespeneuro))) )."
       </TD></TR></TABLE>
       ";
      if (strlen($pnotespepsych) > 0) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>P</u>sychiatric")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnotespepsych) ?
		prepare($pnotespepsych) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnotespepsych))) )."
       </TD></TR></TABLE> 
       ";
      if (strlen($pnoteshandp) > 7) $display_buffer .= "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=\"100%\"><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=LEFT><B>".__("<u>F</u>ree Form Entry")."</B></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
		".( eregi("<[A-Z/]*>", $pnoteshandp) ?
		prepare($pnoteshandp) :
		stripslashes(str_replace("\n", "<br/>", htmlentities($pnoteshandp))) )."
       </TD></TR></TABLE>
      ";
	    
      // back to your regularly sceduled program...
      $display_buffer .= "
       <p/>
       ".template::link_bar(array(
        __("Encounter Notes") =>
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
			ITEMLIST_VIEW | ITEMLIST_MOD | ITEMLIST_DEL | ITEMLIST_LOCK
		);
		$display_buffer .= "\n<p/>\n";
	} // end function EncounterNotes->view()

	// Method: noteForDate
	//
	//	Determines if an encounter note was entered for a particular
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

} // end of class EncounterNotes

register_module ("EncounterNotes");

?>

