<?php
 // $Id$
 // desc: class containers for objects used by freemed ... to avoid any
 //       problems with repetitive database access
 // lic : GPL, v2

if (!defined ("__CONTAINERS_PHP__")) {

define (__CONTAINERS_PHP__, true);

// class Payer

class Payer {
  var $local_record;                // stores basic record
  var $id;                          // record ID for insurance company
  var $insconame;                   // name of company
  var $inscoalias;                  // insurance company alias (for forms)
  var $modifiers;                   // modifiers array

  function Payer ($payerid = "") {
    global $database;

    if ($payerid=="") return false;    // error checking
    $this->local_record = freemed_get_link_rec ($payerid, "payer");

  } // end constructor InsuranceCompany

} // end class InsuranceCompany
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
    $this->local_record = freemed_get_link_rec ($insco, "insco");
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
  var $is_callin;                   // flag for call ins
  var $insco;                       // array of insurance companies
  var $ptid;			    // local practice ID (chart num)
  var $payer;                      // array of the patients insurers

  function Patient ($patient_number = 0, $is_callin = false) { // constructor
    if ($patient_number<1) return false;   // if the patient number is an error
    if (!$is_callin) {
     $this->local_record = freemed_get_link_rec ($patient_number, "patient");
     // pull records
     $this->ptlname      = $this->local_record["ptlname"  ];
     $this->ptfname      = $this->local_record["ptfname"  ];
     $this->ptmname      = $this->local_record["ptmname"  ];
     $this->ptdob        = $this->local_record["ptdob"    ];
     $this->ptsex        = $this->local_record["ptsex"    ];
     $this->ptmarital    = $this->local_record["ptmarital"];
     $this->ptid         = $this->local_record["ptid"     ];
     $this->id           = $this->local_record["id"       ];
     
     // do dependency stuff
     $this->ptreldep     = 0; 
     $this->ptdep        = 0; 
     $guarids = fm_get_active_guarids($patient_number);
     if (is_array($guarids))
     {
         $guarrec = freemed_get_link_rec($guarids[0],"guarantor");
         if (!$guarrec)
         {
             echo "Error getting link rec guarantor in patient class<BR>";
             DIE("Error in patient class guarantor");
         }

	 
	 // not sure about this but we asume only 1 guar is allowed
         // and take the first one
         $this->ptreldep     = $guarrec["guarrel"];
         $this->ptdep        = $guarrec["guarguar"];

     }

     // do payors
     $this->payer[0] = $this->payer[1] = $this->payer[2] = 0;
     $this->insco[0] = $this->insco[1] = $this->insco[2] = 0;
     $payerids = fm_get_active_payerids($patient_number);
     if (is_array($payerids))
     {
         $num = count($payerids);
         //echo "got $num payors<BR>";
	 for ($i=0;$i<$num;$i++)
         {
	     $payertype = freemed_get_link_field($payerids[$i], "payer", "payertype");
             if ($payertype=="")
             {
                 echo "Error getting link field payertype in patient class<BR>";
                 DIE("Error in patient class payertype");
             }
	     $this->payer[$payertype] = new Payer ($payerids[$i]);
             $this->insco[$payertype] = new InsuranceCompany ($this->payer[$payertype]->local_record["payerinsco"]);
             $insname = $this->insco[$payertype]->insconame;
             //echo "insname $insname";
         }
     }

     // pull insurance companies
     //$ins      = sql_expand ($this->local_record["ptins"]     );
     //$insno    = sql_expand ($this->local_record["ptinsno"]   );
     //$insgrp   = sql_expand ($this->local_record["ptinsgrp"]  );
     //$insstart = sql_expand ($this->local_record["ptinsstart"]);
     //$insend   = sql_expand ($this->local_record["ptinsend"]  );

     // make sure arrays are passed
     //if (!is_array ($ins)     ) $ins[0]      = $ins;
     //if (!is_array ($insno)   ) $insno[0]    = $insno;
     //if (!is_array ($insgrp)  ) $insgrp[0]   = $insgrp;
     //if (!is_array ($insstart)) $insstart[0] = $insstart;
     //if (!is_array ($insend)  ) $insend[0]   = $insend;
     
     // reset all pulled arrays
     //if (is_array($ins)) {
     //  reset ($ins);
     //  if (is_array($insno))    reset ($insno);
     //  if (is_array($insgrp))   reset ($insgrp);
     //  if (is_array($insstart)) reset ($insstart);
     //  if (is_array($insend))   reset ($insend);
     //  $count = 0;
     //  while (list ($k, $v) = each ($ins)) {
     //    if ($v>0) $this->insco[$count] = new InsuranceCompany ($v);
     //  } // end looping for inscos
     //} // end checking for insurance companies at all  

     // callin set as false
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

  function insuranceSelection ($no_parameters = "") {
    $returned_string = "";
    for ($i=0;$i<=(count($this->insco));$i++) {
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
    return (!empty($this->ptdep));
    //return ($this->ptdep != 0);
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

} // end checking for __CONTAINERS_PHP__

?>
