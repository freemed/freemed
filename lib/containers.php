<?php
 // $Id$
 // desc: class containers for objects used by freemed ... to avoid any
 //       problems with repetitive database access
 // lic : GPL, v2

if (!defined ("__CONTAINERS_PHP__")) {

define ('__CONTAINERS_PHP__', true);

// class Guarantor
class Guarantor {
  var $local_record;                // stores basic record
  var $id;                          // record ID for insurance company
  var $guarfname;
  var $guarlname;
  var $guarmname;
  var $guaraddr1;
  var $guaraddr2;
  var $guarcity;
  var $guarstate;
  var $guarzip;
  var $guarsex;
  var $guardob;
  var $guarsame;


  function Guarantor ($coverageid = "") {
    global $database;

    if ($coverageid=="") return false;    // error checking
    $this->local_record = freemed::get_link_rec ($coverageid, "coverage");
	$this->guarfname = $this->local_record["covfname"];
	$this->guarlname = $this->local_record["covlname"];
	$this->guarmname = $this->local_record["covmname"];
	$this->guaraddr1 = $this->local_record["covaddr1"];
	$this->guaraddr2 = $this->local_record["covaddr2"];
	$this->guarcity = $this->local_record["covcity"];
	$this->guarstate = $this->local_record["covstate"];
	$this->guarzip = $this->local_record["covzip"];
	$this->guardob = $this->local_record["covdob"];
	$this->guarsex = $this->local_record["covsex"];
	$this->id = $this->local_record["id"];
	if (empty($this->local_record[covaddr1]))
	{
		$this->guarsame = 1;
	}
	
  }
}

// class Coverage
class Coverage {
  var $local_record;                // stores basic record
  var $id;                          // record ID for insurance company
  var $covpatgrpno;                  // patients group no for this payer
  var $covpatinsno;                  // patients id number for this payer
  var $covstatus;
  var $covtype;                   // payertype 1 prim, 2 sec 3 tert 4 wc
  var $coveffdt;                // effective dates for coverage
  var $covinsco;						// pointer to corresponding insco.
  var $covreldep;                 // guar relation to insured 
  var $covdep;                 // help ease the conversion
  var $covpatient;             // the patient

  // insureds info only if rel is not "S"elf

  function Coverage ($coverageid = "") {
    global $database;

    if ($coverageid=="" OR $coverageid==0) return false;    // error checking
    $this->local_record = freemed::get_link_rec ($coverageid, "coverage");
	$this->covpatgrpno = $this->local_record[covpatgrpno];	
	$this->covpatinsno = $this->local_record[covpatinsno];	
	$this->covstatus = $this->local_record[covstatus];	
	$this->covtype = $this->local_record[covtype];	
	$this->coveffdt = $this->local_record[coveffdt];	
	$this->covinsco = new InsuranceCompany($this->local_record[covinsco]);	
	$this->covreldep = $this->local_record[covrel];	
	$this->id = $this->local_record[id];
    $this->covpatient = $this->local_record[covpatient];	
	if ($this->covreldep != "S")
	{
		$this->covdep = $this->id; // you pass this to the guarantor class
	}
	else
		$this->covdep = 0;


  } // end constructor Coverage

} // end class Payer
// class Payer
class Payer {
  var $local_record;                // stores basic record
  var $id;                          // record ID for insurance company
  var $patgroupno;                  // patients group no for this payer
  var $patinsidno;                  // patients id number for this payer
  var $payerstatus;
  var $payertype;                   // payertype 0 prim, 1 sec 2 tert 4 wc
  var $payerstartdt;                // effective dates for coverage
  var $payerenddt;
  var $inscoid;						// pointer to corresponding insco.

  function Payer ($payerid = "") {
    global $database;

    if ($payerid=="") return false;    // error checking
    $this->local_record = freemed::get_link_rec ($payerid, "payer");
	$this->patgroupno = $this->local_record[payerpatientgrp];	
	$this->patinsidno = $this->local_record[payerpatientinsno];	
	$this->payerstatus = $this->local_record[payerstatus];	
	$this->payertype = $this->local_record[payertype];	
	$this->payerstartdt = $this->local_record[payerstartdt];	
	$this->payerenddt = $this->local_record[payerenddt];	
	$this->inscoid = $this->local_record[payerinsco];	

  } // end constructor Payer

} // end class Payer

// class InsuranceCompany
class InsuranceCompany {
	var $local_record;                // stores basic record
	var $id;                          // record ID for insurance company
	var $insconame;                   // name of company
	var $inscoalias;                  // insurance company alias (for forms)
	var $modifiers;                   // modifiers array

	function InsuranceCompany ($insco = 0) {
		global $database;

		if ($insco==0) return false;    // error checking
		$this->local_record = freemed::get_link_rec ($insco, "insco");
		$this->id    		= $this->local_record["id" ];
		$this->insconame    = $this->local_record["insconame" ];
		$this->inscoalias   = $this->local_record["inscoalias"];
		$this->modifiers    = fm_split_into_array (
		$this->local_record["inscomod"]);
	} // end constructor InsuranceCompany
} // end class InsuranceCompany

// class Patient
class Patient {
	var $local_record;                // stores basic patient record
	var $ptlname, $ptfname, $ptmname; // name variables
	var $ptdob;                       // date of birth
	var $ptdep;                       // patient dependencies
	var $ptsex;                       // gender
	var $ptmarital;                   // marital status
	var $ptreldep;                    // relation to guarantor
	var $id;                          // ID number
	var $ptempl;
	var $is_callin;                   // flag for call ins
	//var $insco;                       // array of insurance companies
	var $ptid;			  // local practice ID (chart num)
	//var $payer;                      // array of the patients insurers
	var $coverage;

	function Patient ($patient_number, $is_callin = false) { // constructor
		// if the patient number is an error
		if ($patient_number<1) return false;

		// Check if this is supposed to be a call-in
		if (!$is_callin) {
			$this->local_record = freemed::get_link_rec (
				$patient_number, "patient"
			);

			// Check for null ID, then trigger error
			if (!isset($this->local_record[id]))
				trigger_error ("Patient container: ".
					"invalid patient ID specified!",
					E_USER_ERROR
				);

			
			// pull records
			$this->ptlname      = $this->local_record["ptlname"  ];
			$this->ptfname      = $this->local_record["ptfname"  ];
			$this->ptmname      = $this->local_record["ptmname"  ];
			$this->ptdob        = $this->local_record["ptdob"    ];
			$this->ptsex        = $this->local_record["ptsex"    ];
			$this->ptmarital    = $this->local_record["ptmarital"];
			$this->ptid         = $this->local_record["ptid"     ];
			$this->id           = $this->local_record["id"       ];
			$this->ptempl       = $this->local_record["ptempl"   ];
     
     // do dependency stuff
     //$this->ptreldep     = 0; 
     //$this->ptdep        = 0; 
     //$guarids = fm_get_active_guarids($patient_number);
     //if (is_array($guarids))
     //{
     //    $guarrec = freemed::get_link_rec($guarids[0],"guarantor");
     //    if (!$guarrec)
     //    {
     //        echo "Error getting link rec guarantor in patient class<BR>";
     //        DIE("Error in patient class guarantor");
     //    }
	 
	 // not sure about this but we asume only 1 guar is allowed
         // and take the first one
     //    $this->ptreldep     = $guarrec["guarrel"];
     //    $this->ptdep        = $guarrec["guarguar"];

     //}

     // do payors
     //$this->payer[0] = $this->payer[1] = $this->payer[2] = 0;
     //$this->insco[0] = $this->insco[1] = $this->insco[2] = 0;

		// get a list of the active insurance companies for this patient

     //$covids = fm_get_active_coverage($patient_number);
     //if (is_array($covids))
     //{
		// now for each active insurer build an array of insurers (payers)
		// in the order of the coverage type. prim 0, sec 1, ter 2 wc 3
		// since we allow (but warn) about having multiple inusrers of the same
		// type we allow the second primary to overlay the first. so if they have 3 primarys
		// the 3rd primary will be considered as THIS primary.
    //     $num = count($covids);
    //     //echo "got $num payors<BR>";
	// 	 for ($i=0;$i<$num;$i++)
    //     {
	//		 $covtype = freemed::get_link_field($covids[$i], "coverage", "covtype");
	//			 if ($covtype=="")
	//			 {
	//				 echo "Error getting link field covtype in patient class<BR>";
	//				 DIE("Error in patient class covtype");
	//			 }
	//		 	 //$this->payer[$payertype] = new Payer ($payerids[$i]);
	//			 //$this->insco[$covtype] = new InsuranceCompany ($this->coverage[$covtype]->insco);
	//			 $this->coverage[$covtype] = new Coverage ($covids[$i]);
	//			 //$insname = $this->insco[$payertype]->insconame;
	//			 //echo "insname $insname"<BR>;
      //   }
    // }


		// callin set as false
		$this->is_callin    = false;
	} else {
		$this->local_record = freemed::get_link_rec ($patient_number,
			"callin");
		// pull records (limited for callins)
		$this->ptlname      = $this->local_record["cilname"];
		$this->ptfname      = $this->local_record["cifname"];
		$this->ptmname      = $this->local_record["cimname"];
		$this->ptdob        = $this->local_record["cidob"  ];
		$this->id           = $this->local_record["id"     ];
		$this->is_callin    = true;
	} // end if/else for is_callin
} // end constructor Patient

	function age ($date_to="") {
		return date_diff_display ($this->local_record["ptdob"],
		( ($date_to=="") ? date("Y-m-d") : $date_to ),
		_("year(s)"), _("month(s)"), _("day(s)"), _("ago"), _("ahead"));
	} // end function Patient->age

	function fullName ($with_dob = false) {
		if (!$with_dob) {
			return $this->ptlname.", ".$this->ptfname." ".
			$this->ptmname;
		} else {
			return $this->ptlname.", ".$this->ptfname." ".
			$this->ptmname." [ ".$this->ptdob." ] ";
		} // end if for checking for date of birth
	} // end function Patient->fullName

	function dateOfBirth ($no_parameters = "") {
		return fm_date_print ($this->ptdob);
	} // end function Patient->dateOfBirth

	function idNumber ($no_parameters = "") {
		return ($this->ptid);
	} // end func idNumber

  //function insuranceSelection ($no_parameters = "") {
  //  $returned_string = "";
  //  for ($i=0;$i<=(count($this->coverage));$i++) {
  //   $current = $this->coverage[$i];
  //   if (is_object ($current)) {
  //    $returned_string .= "
  //      <OPTION VALUE=\"".$current->covinsco->id."\">".
  //      $current->covinsco->insconame;
  //   } // end if object case
  //  } // end for loop
  //  return $returned_string;
  //} // end function insuranceSelection

  //function insurersID ($offset) {
  //  $returned_string = "";
  //   $current = $this->coverage[$offset];
  //   if (is_object ($current)) {
  //    $returned_string = $current->covinsco->id;
  //   } // end if object case
  //  return $returned_string;
  //} // end function insurersID

 // function insuranceSelectionByType ($no_parameters = "") {
 //   $returned_string = "";
 //   for ($i=0;$i<=(count($this->coverage));$i++) {
 //    $current = $this->coverage[$i];
 //    if (is_object ($current)) {
 //     $returned_string .= "
 //       <OPTION VALUE=\"".$i."\">".
 //       $current->covinsco->insconame;
 //    } // end if object case
 //   } // end for loop
 //   return $returned_string;
 // } // end function insuranceSelectionByType

// this is no longer usefull since you no longer have to 
// be patient to be an insured (Guarantor)
//  function isDependent ($no_parameters = "") {
//    if ($is_callin) return false;  // if they are a callin, no information
//    return (!empty($this->ptdep));
//    //return ($this->ptdep != 0);
//  } // end function Patient->isDependent

	function isEmployed ($no_parameters = "") {
		return ($this->ptempl == "y");
	} // end function Patient->isEmployed

	function isFemale ($no_parameters = "") {
		return ($this->ptsex == "f");
	} // end function Patient->isFemale

	function isMale ($no_parameters = "") {
		return ($this->ptsex == "m");
	} // end function Patient->isMale

} // end class Patient

// class Physician
class Physician {
	var $local_record;                 // stores basic record
	var $id;                           // record ID for physician
	var $phylname,$phyfname,$phymname; // name of physician
	var $phyidmap;                     // id map

	function Physician ($physician = 0) {
		global $database;

		if ($physician==0) return false;    // error checking
		$this->local_record = freemed::get_link_rec ($physician,
			"physician");
		$this->phylname     = $this->local_record["phylname"];
		$this->phyfname     = $this->local_record["phyfname"];
		$this->phymname     = $this->local_record["phymname"];
		$this->phyidmap     = fm_split_into_array(
		$this->local_record["phyidmap"]);
	} // end constructor Physician

	function fullName () {
		return $this->phyfname . " " . $this->phymname .
		( (!empty($this->phymname)) ? " " : "" ) . $this->phylname;
	} // end function Physician->fullName

	function getMapId ($this_id = 0) {
		return ( ($this_id == 0) ? "" : $this->phyidmap[$this_id] );
	} // end function Physician->getMapId

	function practiceName () {
		return $this->local_record["phypracname"];
	} // end function Physician->practiceName

} // end class Physician

// class User
class User {
	var $local_record;                 // local record
	var $user_number;                  // user number (id)
	var $user_level;                   // user level (0..9)
	var $user_name;                    // name of the user
	var $user_descrip;                 // user description
	var $user_phy;                     // number of physician 
	var $manage_config; // configuration for patient management
	var $perms_fac, $perms_phy, $perms_phygrp;

	function User () {
		global $SESSION; // authorization data
		extract ($SESSION);

		$this->user_number  = $authdata["user"];
		$this->local_record = freemed::get_link_rec ($this->user_number,
			"user");
		$this->user_name    = $this->local_record["username"   ];
		$this->user_descrip = $this->local_record["userdescrip"];
		$this->user_level   = $this->local_record["userlevel"  ];
		$this->user_phy     = $this->local_record["userrealphy"];
		$this->perms_fac    = $this->local_record["userfac"    ]; 
		$this->perms_phy    = $this->local_record["userphy"    ];
		$this->perms_phygrp = $this->local_record["userphygrp" ];

		// special root stuff
		if ($this->user_number == 1) $this->user_level = 9;

		// Map configuration vars
		$this->mapConfiguration();
	} // end function User

	function getDescription ($no_parameters = "") {
		if (empty($this->user_descrip)) return "(no description)";
		return ($this->user_descrip);
	} // end function getDescription

	function getLevel ($no_parameters = "") {
		return ($this->user_level)+0;
	} // end function getLevel

	function getPhysician ($no_parameters = "") {
		return ($this->user_phy)+0;
	} // end function getPhysician

	function getName ($no_parameters = "") {
		return ($this->user_name);
	} // end function getName

	function isPhysician ($no_parameters = "") {
		return ($this->user_phy != 0);
	} // end function isPhysician

	function mapConfiguration () {
		// Start with usermanageopt
		$usermanageopt = $this->local_record["usermanageopt"];

		// Check if set...
		if (empty($usermanageopt)) return false;

		// Split out by "/"'s
		$usermanageopt_array = explode("/", $usermanageopt);

		// Pull pairs one by one
		foreach ($usermanageopt_array AS $garbage => $opt) {
			// Check if not empty..
			if (!empty($opt)) {
				// Explode pairs by "="
				list ($key, $val) = explode ("=", $opt);

				// Map to global manage_config map
				if ( !(strpos($val, ":") === false) ) {
					// Handle arrays
					$this->manage_config["$key"] =
						explode(":", $val);
				} else {
					// Handle scalar
					$this->manage_config["$key"] = $val;
				} // end mapping
			} // end checking for empties
		} // end looping through
	} // end function User->mapConfiguration

	function getManageConfig ($key) {
		return $this->manage_config["$key"];
	} // end function getManageConfig

	// Messages
	function newMessages () {
		global $sql;
		$result = $sql->query("SELECT * FROM messages WHERE ".
			"msgfor='".$this->user_phy."' AND ".
			"msgread='0'");
		if (!$sql->results($result)) return false;
		return $sql->num_rows($result);
	} // end function newMessages

} // end class User

} // end checking for __CONTAINERS_PHP__

?>
