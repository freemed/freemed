<?php
 // $Id$
 // note: progress notes module for patient management
 // lic : GPL, v2

if (!defined("__PROGRESS_NOTES_MODULE_PHP__")) {

define (__PROGRESS_NOTES_MODULE_PHP__, true);

include ("lib/freemed.php");
include ("lib/API.php");
include ("lib/module_emr.php");

class progressNotes extends freemedEMRModule {

	var $MODULE_NAME = "Progress Notes";
	var $MODULE_VERSION = "0.1";

	var $record_name = "Progress Notes";
	var $table_name  = "pnotes";

	function progressNotes () {
		// call parent constructor
		$this->freemedEMRModule();
	} // end constructor progressNotes

	function add () { $this->form(); }
	function mod () { $this->form(); }

	function form () {
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		if (!$been_here) {
      switch ($action) { // internal switch
        case "addform":
         if ($this->this_user->isPhysician()) // check if we are a physician
 	  $pnotesdoc = $this->this_user->getPhysician(); // if so, set as default
         $pnotesdt     = $cur_date;
         break; // end addform
        case "modform":
         if (($id<1) OR (strlen($id)<1)) {
           //freemed_display_box_top (_($this->record_name)." :: "._("ERROR"));
           echo "
             <$HEADERFONT_B>"._("You must select a patient.")."<$HEADERFONT_E>
           ";
           //freemed_display_box_bottom ();
           DIE("");
         }
         $r = freemed_get_link_rec ($id, "pnotes");
  	 extract ($r);
         break; // end modform
      } // end internal switch
      $been_here = 1;
     } // end checking if been here

     //freemed_display_box_top (( (($action=="addform") or ($action=="add")) ?
     //  _("Add") : _("Modify") )." "._($this->record_name));

     $book = new notebook (array ("been_here", "id",
       "module", "patient", "action"),
       NOTEBOOK_COMMON_BAR | NOTEBOOK_STRETCH, 4);
     
     $book->add_page (
       _("Basic Information"),
       array ("pnoteseoc", date_vars("pnotesdt")),
       form_table (
        array (
	 _("Provider") =>
	   freemed_display_selectbox (
            fdb_query ("SELECT * FROM physician ORDER BY phylname,phyfname"),
	    "#phylname#, #phyfname#",
	    "pnotesdoc"
	   ),
	   
         _("Description") =>
	  "<INPUT TYPE=TEXT NAME=\"pnotesdescrip\" SIZE=25 MAXLENGTH=100
	    VALUE=\"".prepare($pnotesdescrip)."\">\n",
	   
         _("Related Episode(s)") =>
           freemed_multiple_choice ("SELECT id,eocdescrip,eocstartdate,".
                                  "eocdtlastsimilar FROM eoc WHERE ".
                                  "eocpatient='$patient'",
                                  "eocdescrip:eocstartdate:eocdtlastsimilar",
                                  "pnoteseoc",
                                  $pnoteseoc,
                                  false),
				  
         _("Date") => fm_date_entry("pnotesdt") 
	 )
        )
      ); 

     $book->add_page (
       _("<U>S</U>ubjective"),
       array ("pnotes_S"),
       form_table (
        array (
          _("<U>S</U>ubjective") =>
          "<TEXTAREA NAME=\"pnotes_S\" ROWS=8 COLS=45
         WRAP=VIRTUAL>".prepare($pnotes_S)."</TEXTAREA>"
        )
       )
     );

     $book->add_page (
       _("<U>O</U>bjective"),
       array ("pnotes_O"),
       form_table (
        array (
          _("<U>O</U>bjective") =>
          "<TEXTAREA NAME=\"pnotes_O\" ROWS=8 COLS=45
         WRAP=VIRTUAL>".prepare($pnotes_O)."</TEXTAREA>"
        )
       )
     );

     $book->add_page (
       _("<U>A</U>ssessment"),
       array ("pnotes_A"),
       form_table (
        array (
          _("<U>A</U>ssessment") =>
          "<TEXTAREA NAME=\"pnotes_A\" ROWS=8 COLS=45
         WRAP=VIRTUAL>".prepare($pnotes_A)."</TEXTAREA>"
        )
       )
     );

     $book->add_page (
       _("<U>P</U>lan"),
       array ("pnotes_P"),
       form_table (
        array (
          _("<U>P</U>lan") =>
          "<TEXTAREA NAME=\"pnotes_P\" ROWS=8 COLS=45
         WRAP=VIRTUAL>".prepare($pnotes_P)."</TEXTAREA>"
        )
       )
     );

     $book->add_page (
       _("<U>I</U>nterval"),
       array ("pnotes_I"),
       form_table (
        array (
          _("<U>I</U>nterval") =>
          "<TEXTAREA NAME=\"pnotes_I\" ROWS=8 COLS=45
         WRAP=VIRTUAL>".prepare($pnotes_I)."</TEXTAREA>"
        )
       )
     );

     $book->add_page (
       _("<U>E</U>ducation"),
       array ("pnotes_E"),
       form_table (
        array (
          _("<U>E</U>ducation") =>
          "<TEXTAREA NAME=\"pnotes_E\" ROWS=8 COLS=45
         WRAP=VIRTUAL>".prepare($pnotes_E)."</TEXTAREA>"
        )
       )
     );

     $book->add_page (
       _("P<U>R</U>escription"),
       array ("pnotes_R"),
       form_table (
        array (
          _("P<U>R</U>escription") =>
          "<TEXTAREA NAME=\"pnotes_R\" ROWS=8 COLS=45
         WRAP=VIRTUAL>".prepare($pnotes_R)."</TEXTAREA>"
        )
       )
     );

     if (!$book->is_done()) {
      echo $book->display();

      echo "
        <CENTER>
         <A HREF=\"$this->page_name?module=$module&$_auth&patient=$patient\"
          ><$STDFONT_B>"._("Abandon ".( ($action=="addform") ?
 	   "Addition" : "Modification" ))."<$STDFONT_E></A>
        </CENTER>
      ";
     } else {
       switch ($action) {
        case "addform": case "add":
         echo "
           <CENTER><$STDFONT_B><B>"._("Adding")." ... </B>
         ";
           // preparation of values
         $pnotesdtadd = $cur_date;
         $pnotesdtmod = $cur_date;

           // actual addition
         $query = "INSERT INTO ".$this->table_name." VALUES (
           '".fm_date_assemble("pnotesdt")."',
           '$pnotesdtadd',
           '$pnotesdtmod',
           '".addslashes($patient)."',
	   '".addslashes($pnotesdescrip)."',
	   '".addslashes($pnotesdoc)."',
           '".addslashes(sql_squash($pnoteseoc))."',
           '".addslashes($pnotes_S)."',
           '".addslashes($pnotes_O)."',
           '".addslashes($pnotes_A)."',
           '".addslashes($pnotes_P)."',
           '".addslashes($pnotes_I)."',
           '".addslashes($pnotes_E)."',
           '".addslashes($pnotes_R)."',
           '$__ISO_SET__',
           NULL ) "; // actual add query
         break;

	case "modform": case "mod":
         echo "
           <CENTER><$STDFONT_B><B>"._("Modifying")." ... </B>
         ";
         $query = "UPDATE ".$this->table_name." SET
          pnotespat      = '".addslashes($patient)."',
          pnoteseoc      = '".addslashes(sql_squash($pnoteseoc))."',
          pnotesdt       = '".addslashes(fm_date_assemble("pnotesdt"))."',
          pnotesdtmod    = '".addslashes($cur_date)."',
          pnotes_S       = '".addslashes($pnotes_S)."',
          pnotes_O       = '".addslashes($pnotes_O)."',
          pnotes_A       = '".addslashes($pnotes_A)."',
          pnotes_P       = '".addslashes($pnotes_P)."',
          pnotes_I       = '".addslashes($pnotes_I)."',
          pnotes_E       = '".addslashes($pnotes_E)."',
          pnotes_R       = '".addslashes($pnotes_R)."',
          iso            = '$__ISO_SET__'
          WHERE id='$id'";
	 break;
       } // end inner switch
       // now actually send the query
       $result = fdb_query ($query);
       if ($debug) echo "(query = '$query') ";
       if ($result)
         echo " <B> "._("done").". </B><$STDFONT_E>\n";
       else
         echo " <B> <FONT COLOR=#ff0000>"._("ERROR")."</FONT> </B><$STDFONT_E>\n";
       echo "
        </CENTER>
        <BR><BR>
         <CENTER><A HREF=\"manage.php?$_auth&id=$patient\"
          ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A>
         <B>|</B>
         <A HREF=\"$this->page_name?$_auth&module=$module&patient=$patient\"
          ><$STDFONT_B>"._($this->record_name)."<$STDFONT_E></A>
	  ";
       if ($action=="mod" OR $action=="modform")
         echo "
	 <B>|</B>
	 <A HREF=\"$this->page_name?$_auth&module=$module&patient=$patient&action=view&id=$id\"
	  ><$STDFONT_B>"._("View $this->record_name")."<$STDFONT_E></A>
	 ";
       echo "
         </CENTER>
         <BR>
         ";
     } // end if is done
     //freemed_display_box_bottom ();


     //freemed_display_box_bottom ();
	} // end of function progressNotes->form()

	function display () {
     if (($id<1) OR (strlen($id)<1)) {
       //freemed_display_box_top (_($this->record_name)." :: "._("ERROR"));
       echo "
         <$HEADERFONT_B>"._("Specify Notes to Display")."<$HEADERFONT_E>
         <P>
         <CENTER><A HREF=\"$this->page_name?$_auth&module=$module&patient=$patient\"
          ><$STDFONT_B>"._("back")."<$STDFONT_E></A> |
          <A HREF=\"manage.php?$_auth&id=$patient\"
          ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A>
         </CENTER>
       ";
       //freemed_display_box_bottom ();
       freemed_display_html_bottom ();
       DIE("");
     }
      // if it is legit, grab the data
     $r = freemed_get_link_rec ($id, "pnotes");
     if (is_array($r)) extract ($r);
     $pnotesdt_formatted = substr ($pnotesdt, 0, 4). "-".
                           substr ($pnotesdt, 5, 2). "-".
                           substr ($pnotesdt, 8, 2);
     $pnotespat = $r ["pnotespat"];
     $pnoteseoc = sql_expand ($r["pnoteseoc"]);

     $this->this_patient = new Patient ($pnotespat);

     //freemed_display_box_top (_($this->record_name));
     if (freemed_get_userlevel($LoginCookie)>$database_level)
       $__MODIFY__ = " |
         <A HREF=\"$this->page_name?$_auth&module=$module&patient=$patient&id=$id&action=modform\"
          ><$STDFONT_B>"._("Modify")."<$STDFONT_E></A>
       "; // add this if they have modify privledges
     echo "
       <P>
       <CENTER><A HREF=\"$this->page_name?$_auth&module=$module&patient=$pnotespat\"
        ><$STDFONT_B>"._($this->record_name)."<$STDFONT_E></A> |
        <A HREF=\"manage.php?$_auth&id=$pnotespat\"
        ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A> $__MODIFY__
       </CENTER>
       <P>

       <CENTER>
        <$STDFONT_B>
        <B>Relevant Date : </B>
         $pnotesdt_formatted
        <$STDFONT_E>
       </CENTER>
       <P>
     ";
     if (count($pnoteseoc)>0 and is_array($pnoteseoc)) {
      echo "
       <CENTER>
        <$STDFONT_B><B>"._("Related Episode(s)")."</B><$STDFONT_E>
        <BR>
      ";
      for ($i=0;$i<count($pnoteseoc);$i++) {
        if ($pnoteseoc[$i] != -1) {
          $e_r     = freemed_get_link_rec ($pnoteseoc[$i]+0, "eoc"); 
          $e_id    = $e_r["id"];
          $e_desc  = $e_r["eocdescrip"];
          $e_first = $e_r["eocstartdate"];
          $e_last  = $e_r["eocdtlastsimilar"];
          echo "
           <A HREF=\"episode_of_care.php3?$_auth&patient=$patient&".
  	   "action=manage&id=$e_id\"
           ><$STDFONT_B>$e_desc / $e_first to $e_last<$STDFONT_E></A><BR>
          ";
	} else {
	  $episodes = fdb_query (
	    "SELECT * FROM eoc WHERE eocpatient='$patient'" );
	  while ($epi = fdb_fetch_array ($episodes)) {
            $e_id    = $epi["id"];
            $e_desc  = $epi["eocdescrip"];
            $e_first = $epi["eocstartdate"];
            $e_last  = $epi["eocdtlastsimilar"];
            echo "
             <A HREF=\"episode_of_care.php3?$_auth&patient=$patient&".
  	     "action=manage&id=$e_id\"
             ><$STDFONT_B>$e_desc / $e_first to $e_last<$STDFONT_E></A><BR>
            ";
	  } // end fetching
	} // check if not "ALL"
      } // end looping for all EOCs
      echo "
       </CENTER>
      ";
     } // end checking for EOC stuff
     echo "<CENTER>\n";
     if (!empty($pnotes_S)) echo "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=400><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><CENTER><$STDFONT_B COLOR=#ffffff>
        <B>"._("<U>S</U>ubjective")."</B><$STDFONT_E></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
         <$STDFONT_B COLOR=#555555>
           ".prepare($pnotes_S)."
         <$STDFONT_E>
       </TD></TR></TABLE>
       ";
      if (!empty($pnotes_O)) echo "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=400><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><CENTER><$STDFONT_B COLOR=#ffffff>
        <B>"._("<U>O</U>bjective")."</B><$STDFONT_E></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
         <$STDFONT_B COLOR=#555555>
           ".prepare($pnotes_O)."
         <$STDFONT_E>
       </TD></TR></TABLE>
       ";
      if (!empty($pnotes_A)) echo "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=400><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><CENTER><$STDFONT_B COLOR=#ffffff>
        <B>"._("<U>A</U>ssessment")."</B><$STDFONT_E></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
         <$STDFONT_B COLOR=#555555>
           ".prepare($pnotes_A)."
         <$STDFONT_E>
       </TD></TR></TABLE>
       ";
      if (!empty($pnotes_P)) echo "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=400><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><CENTER><$STDFONT_B COLOR=#ffffff>
        <B>"._("<U>P</U>lan")."</B><$STDFONT_E></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
         <$STDFONT_B COLOR=#555555>
           ".prepare($pnotes_P)."
         <$STDFONT_E>
       </TD></TR></TABLE>
       ";
      if (!empty($pnotes_I)) echo "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=400><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><CENTER><$STDFONT_B COLOR=#ffffff>
        <B>"._("<U>I</U>nterval")."</B><$STDFONT_E></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
         <$STDFONT_B COLOR=#555555>
           ".prepare($pnotes_I)."
         <$STDFONT_E>
       </TD></TR></TABLE>
       ";
      if (!empty($pnotes_E)) echo "
       <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=400><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><CENTER><$STDFONT_B COLOR=#ffffff>
        <B>"._("<U>E</U>ducation")."</B><$STDFONT_E></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
         <$STDFONT_B COLOR=#555555>
           ".prepare($pnotes_E)."
         <$STDFONT_E>
       </TD></TR></TABLE> 
       ";
      if (!empty($pnotes_R)) echo "
      <TABLE BGCOLOR=#ffffff BORDER=1 WIDTH=400><TR BGCOLOR=$darker_bgcolor>
       <TD ALIGN=CENTER><CENTER><$STDFONT_B COLOR=#ffffff>
        <B>"._("P<U>R</U>escription")."</B><$STDFONT_E></CENTER></TD></TR>
       <TR BGCOLOR=#ffffff><TD>
         <$STDFONT_B COLOR=#555555>
           ".prepare($pnotes_R)."
         <$STDFONT_E>
       </TD></TR></TABLE>
      ";
        // back to your regularly sceduled program...
      echo "
       <P>
       <CENTER><A HREF=\"$this->page_name?$_auth&module=$module&patient=$pnotespat\"
        ><$STDFONT_B>"._($this->record_name)."<$STDFONT_E></A> |
        <A HREF=\"manage.php?$_auth&id=$pnotespat\"
        ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A> $__MODIFY__
       </CENTER>
       <P>
     ";

     //freemed_display_box_bottom ();
	} // end of case display

	function delete () {
     echo "DELETE STUB!<BR>\n";
    } // end function progressNotes->delete()
     
	function view () {
		global $patient;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE (pnotespat='".addslashes($patient)."') ".
			"ORDER BY pnotesdt";
		$result = fdb_query ($query);

		echo freemed_display_itemlist(
			$result,
			$this->page_name,
			array (
				"Date"        => "pnotesdt",
				"Description" => "pnotesdescrip"
			), // array
			array (
				"",
				_("NO DESCRIPTION")
			)
		);
		echo "\n<P>\n";
	} // end function progressNotes->view()

} // end of class progressNotes

register_module ("progressNotes");

} // end if defined

?>
