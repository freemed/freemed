<?php
  # file: global.var.inc
  # note: global variables for entire freemed code base
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL, v2
  # mod : 2000-01-18

  // This is the global.var.php3 file, which keeps all
  // variables that the program needs to know, to
  // eliminate needless typing...

    // these variables you should not touch
  $packagename="freemed";		// package name
  $coded_by="jeff b";			// coded by tag
  $version="0.1.1/pre-20000118";	// current version number
  $bugs_email="code_bugs@test.pr.uconn.edu";	// coder email...
  $bugs_address="http://www.freemed.org/keystone/visitor.php3";
  $cur_date=date("Y-m-d");		// SQL date format (don't touch!)
  $cur_date_hash=date("Ymd");		// YYYYMMDD date hash
  $_auth="default_value=yes";		// authentication (KFM fix)

   // CHANGE THIS FOR YOUR LOCAL DATE DISPLAY
  $local_date_display="%Y-%m-%d";       // United States
  // $local_date_display="%d.%m.%Y";    // European countries

    // **********************************
    // ***** customizable variables *****
    // **********************************
  $installation="Stock Freemed Install"; // installation name
  $show_top_links=true;                 // do we show links on the top?
  $host=chop(`cat /etc/HOSTNAME`); 	// webserver location
  $db_host="localhost";			// database (SQL) host location
  //$physical_loc="/var/freemed";       // physical directory location
  $physical_loc=chop(`pwd`);            // get physical dir from "pwd"
  $database="freemed";			// SQL db name (for places
					// with multiple iterations...)
  $base_url="/freemed";			// offset (i.e. http://here/package)
  $http="http";                         // http for normal, https for SSL
    // these are for the SQL server, since it needs at least one real
    // account to function. please change at will
  $db_user="root";			// SQL server username
  $db_password="password";		// SQL server password
  $default_language="EN";               // default language

    // *************************************
    // ** fax subsystem  --please        ***
    // ** read incoming_fax_scripts.mk   ***
    // *************************************

  $gifhome="$physical_loc/data/fax/incoming";

    // *************************************
    // ***** language setting routines *****
    // *************************************

  if (strlen($u_lang)==2) $language=$u_lang;
  else $language=$default_language;

    // don't touch these variables either...
  $complete_url="$http://$host$base_url"; 
  $_cookie_expire="36000";     // cookies expire in 1 hour

    // *** database engine ***
    //   mysql    - MySQL backend
    //   odbc     - ODBC compliant (i.e. M$ SQL Server)
    //   postgres - PostgreSQL backend
    //   msql     - mSQL backend (new, as of 19990722, still broken)
  $db_engine="mysql";

  $debug=false;  // true=debug info on, false=debug info off
  $_hf=true;     // true=help files on, false=help files off
  $_mail_handler="mailto:";  // where the mail goes...
    // the _mail_handler variable is so that we can farm
    // this mail to some mail hook in a program.

    // user level qualifiers
  $admin_level   =8; // administrator user level
  $delete_level  =5; // user level above which someone can delete
  $export_level  =3; // user level to export databases
  $database_level=2; // level above which you can add db recs...

    // colors
  $bar_start_color="#dddddd";
  $bar_alt_color  ="#ffffff";
  $topbar_color   ="#0022cf";
  $darker_bgcolor ="#777777";
  $menubar_color  ="#bbbbbb";

  // related to the calendar --
  //   times are given in 24 hour format, then reformatted for
  //   am and pm by the program
  // these are settable as calshr/calehr in the config file,
  // but these are there by default
  $cal_starting_hour = "8";  // start at 8 o'clock
  $cal_ending_hour   = "18"; // end at 6 o'clock pm

  // maximum number of returned results in multipage result queries
  $max_num_res = 20;

  // now, some all-purpose time savers
  // don't touch unless you -KNOW- what you are doing.
    // header fonts, begin and end
  $HEADERFONT_B = "CENTER><FONT FACE=\"Arial, Helvetica, Verdana\"><B";
  $HEADERFONT_E = "/B></FONT></CENTER";
    // standard fonts, begin and end
  $STDFONT_B    = "FONT FACE=\"Arial, Helvetica, Verdana\"";
  $STDFONT_E    = "/FONT";

  $ADD          = "<$STDFONT_B SIZE=-1>ADD<$STDFONT_E>";
  $MOD          = "<$STDFONT_B SIZE=-1>MOD<$STDFONT_E>";
  $DEL          = "<$STDFONT_B SIZE=-1>DEL<$STDFONT_E>";
  $brackets     = "[]";

  $__global_var__ = true;

  if ($default_facility>0)
    SetCookie ("default_facility", $default_facility, time()+$_cookie_expire); 

  // set the maximum timeout...
  set_time_limit (0);

  // ************ HANDLERS AND OTHER MODULE LOADERS ****************

  include ("iso-set.inc");             // ISO set handler
  include ("containers.inc");          // class containers
  include ("language-loader.inc");     // external language loader

  // ***************************************************************
?>
