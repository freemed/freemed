<?php
 # file: containers.inc
 # desc: class containers for objects used by freemed ... to avoid any
 #       problems with repetitive database access
 # code: jeff b (jeff@univrel.pr.uconn.edu)
 # lic : GPL, v2

// check to see if global vars is loaded...
if (empty($packagename))
  DIE("global.var.inc has not been loaded before containers.inc -- fatal");

// class InsuranceCompany

class InsuranceCompany {
  var $local_record;                // stores basic record
  var $id;                          // record ID for insurance company
  var $insconame;                   // name of company
  var $modifiers;                   // modifiers array

  function InsuranceCompany ($insco = 0) {
    global $database;

    if ($insco==0) return false;    // error checking
    $this->local_record = freemed_get_link_rec ($insco, "insco");
    $this->insconame    = $this->local_record["insconame"];
    $this->modifiers    = fm_split_into_array (
                          $this->local_record["inscomod"]);
  } // end constructor InsuranceCompany

} // end class InsuranceCompany

// class Patient

class Patient {
  var $local_record;                // stores basic patient record
  var $ptlname, $ptfname, $ptmname; // name variables
  var $ptdob;                       // date of birth
  var $ptdep;                       // patient dependency
  var $ptsex;                       // gender
  var $ptmarital;                   // marital status
  var $ptreldep;                    // relation to guarantor
  var $id;                          // ID number
  var $is_callin;                   // flag for call ins
  var $insco;                       // array of insurance companies
  var $ptid;			    // local practice ID (chart num)

  function Patient ($patient_number = 0, $is_callin = false) { // constructor
    if ($patient_number<1) return false;   // if the patient number is an error
    if (!$is_callin) {
     $this->local_record = freemed_get_link_rec ($patient_number, "patient");
     // pull records
     $this->ptlname      = $this->local_record["ptlname"  ];
     $this->ptfname      = $this->local_record["ptfname"  ];
     $this->ptmname      = $this->local_record["ptmname"  ];
     $this->ptdob        = $this->local_record["ptdob"    ];
     $this->ptdep        = $this->local_record["ptdep"    ];
     $this->ptsex        = $this->local_record["ptsex"    ];
     $this->ptmarital    = $this->local_record["ptmarital"];
     $this->ptreldep     = $this->local_record["ptreldep" ];
     $this->ptid         = $this->local_record["ptid"     ];
     $this->id           = $this->local_record["id"       ];
     if ($this->local_record["ptins1"]>0)
       $this->insco[0] =
         new InsuranceCompany ($this->local_record["ptins1"]);
     if ($this->local_record["ptins2"]>0)
       $this->insco[1] =
         new InsuranceCompany ($this->local_record["ptins2"]);
     if ($this->local_record["ptins3"]>0)
       $this->insco[2] =
         new InsuranceCompany ($this->local_record["ptins3"]); 
     $this->is_callin    = false;
    } else {
     $this->local_record = freemed_get_link_rec ($patient_number, "callin");
     // pull records (limited for callins)
     $this->ptlname      = $this->local_record["cilname"];
     $this->ptfname      = $this->local_record["cifname"];
     $this->ptmname      = $this->local_record["cimname"];
     $this->ptdob        = $this->local_record["cidob"  ];
     $this->id           = $this->local_record["id"     ];
     $this->is_callin    = true;
    } // end if/else for is_callin
  } // end constructor Patient

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

  function insuranceSelection ($no_parameters = "") {
    $returned_string = "";
    for ($i=0;$i<=2;$i++) {
     $current = $this->insco[$i];
     if (is_object ($current)) {
      $returned_string .= "
        <OPTION VALUE=\"".$current->id."\">".
        $current->insconame;
     } // end if object case
    } // end for loop
    return $returned_string;
  } // end function insuranceSelection

  function isDependent ($no_parameters = "") {
    if ($is_callin) return false;  // if they are a callin, no information
    return ($this->ptdep != 0);
  } // end function Patient->isDependent

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
    $this->local_record = freemed_get_link_rec ($physician, "physician");
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
    return ( ($this_id == 0) ? "" :
              $this->phyidmap[$this_id] );
  } // end function Physician->getMapId

  function practiceName () {
    return $this->local_record["phypracname"];
  } // end function Physician->practiceName

} // end class Physician

// class User

class User {
  var $local_record;                 // local record
  var $cookie;                       // actual cookie
  var $user_number;                  // user number (id)
  var $user_level;                   // user level (0..9)
  var $user_name;                    // name of the user
  var $user_descrip;                 // user description
  var $user_phy;                     // number of physician 
  var $perms_fac, $perms_phy, $perms_phygrp;

  function User ($user_cookie = "") {
    global $LoginCookie;                   // global Login Cookie
    if ($user_cookie == "")  $user_cookie = $LoginCookie;
    $this->cookie       = $user_cookie;    // store cookie
    $cookie_data        = explode (":", $this->cookie);
    $this->user_number  = $cookie_data[0];
    $this->local_record = freemed_get_link_rec ($this->user_number, "user");
    $this->user_name    = $this->local_record["username"   ];
    $this->user_descrip = $this->local_record["userdescrip"];
    $this->user_level   = $this->local_record["userlevel"  ];
    $this->user_phy     = $this->local_record["userrealphy"];
    $this->perms_fac    = $this->local_record["userfac"    ]; 
    $this->perms_phy    = $this->local_record["userphy"    ];
    $this->perms_phygrp = $this->local_record["userphygrp" ];

    // special root stuff
    if ($this->user_number == 1) $this->user_level = 9;
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

} // end class User

?>
