<?php
 // $Id$
 // note: patient authorizations module
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 //       adam b (gdrago23@yahoo.com)
 // lic : GPL, v2

 $record_name = "Authorizations";
 $page_name   = "authorizations.php";
 $db_name     = "authorizations";
 include ("lib/freemed.php");
 include ("lib/API.php");

 freemed_open_db ($LoginCookie); // authenticate
 freemed_display_html_top ();
 freemed_display_banner ();

 if ($patient<1) {
   freemed_display_box_top (_($record_name)." :: "._("ERROR"));
   echo "
     <$HEADERFONT_B>"._("You must select a patient.")."<$HEADERFONT_E>
   ";
   freemed_display_box_bottom ();
   freemed_close_db ();
   freemed_display_html_bottom ();
   DIE(""); // go on to a better place
 }

 switch ($action) { // master action switch
   case "addform":
   case "modform":
     switch ($action) { // internal action switch
      case "addform":
       // do nothing
       break; // end internal addform
      case "modform":
       if (($patient<1) OR (empty($patient))) {
         freemed_display_box_top (_("$record_name")." :: "._("ERROR"),
	   $page_name, "$page_name?patient=$patient");
         echo "
           <$HEADERFONT_B>"._("You must select a patient.")."<$HEADERFONT_E>
         ";
         freemed_display_box_bottom ();
         DIE("");
       }
       $r = freemed_get_link_rec ($id, $db_name);
       extract ($r);
       break; // end internal modform
     } // end internal action switch
     freemed_display_box_top (($action=="addform" ? _("Add") : _("Modify")).
       " "._("$record_name"), $page_name,
       "manage.php?id=$patient");
     $pnotesdt     = $cur_date;

     $this_patient = new Patient ($patient);

     echo freemed_patient_box($this_patient)."
       <P>

       <FORM ACTION=\"$page_name\" METHOD=POST>
       <INPUT TYPE=HIDDEN NAME=\"action\"  VALUE=\"".
         ( ($action=="addform") ? "add" : "mod" )."\">
       <INPUT TYPE=HIDDEN NAME=\"id\"      VALUE=\"".prepare($id)."\">
       <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($patient)."\">

       <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3
        VALIGN=MIDDLE ALIGN=CENTER>
        
       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>"._("Starting Date")." : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
         ".date_entry("authdtbegin")."
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>"._("Ending Date")." : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
         ".date_entry("authdtend")."
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>"._("Authorization Number")." : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
         <INPUT TYPE=TEXT NAME=\"authnum\" SIZE=30
          MAXLENGTH=25 VALUE=\"".prepare($authnum)."\">
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>"._("Authorization Type")." : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
         <SELECT NAME=\"authtype\">
          <OPTION VALUE=\"0\" ".
          ( ($authtype <  1) ? "SELECTED" : "" ).">"._("NONE SELECTED")."
          <OPTION VALUE=\"1\" ".
          ( ($authtype == 1) ? "SELECTED" : "" ).">physician
          <OPTION VALUE=\"2\" ".
          ( ($authtype == 2) ? "SELECTED" : "" ).">insurance company
          <OPTION VALUE=\"3\" ".
          ( ($authtype == 3) ? "SELECTED" : "" )."
           >certificate of medical neccessity
          <OPTION VALUE=\"4\" ".
          ( ($authtype == 4) ? "SELECTED" : "" ).">surgical
          <OPTION VALUE=\"5\" ".
          ( ($authtype == 5) ? "SELECTED" : "" ).">worker's compensation
          <OPTION VALUE=\"6\" ".
          ( ($authtype == 6) ? "SELECTED" : "" ).">consulation
         </SELECT>
        </TD>
       </TR>
     ";

     $phys_q="SELECT * FROM physician ORDER BY phylname,phyfname";
     $phys_r=$sql->query($phys_q);
     $ins_q="SELECT * FROM insco ORDER BY insconame,inscostate,inscocity";
     $ins_r=$sql->query($ins_q);
     
     echo "
       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>"._("Authorizing Provider")." : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
     ".
     freemed_display_selectbox ($phys_r, "#phylname#, #phyfname#", "authprov")
     ."
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>"._("Provider Identifier")." : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
         <INPUT TYPE=TEXT NAME=\"authprovid\" SIZE=20 MAXLENGTH=15
          VALUE=\"".prepare($authprovid)."\">
         </SELECT>
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>"._("Authorizing Insurance Company")." : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
     ".
     freemed_display_selectbox ($ins_r, 
       "#insconame# (#inscocity#,#inscostate#)", "authinsco")
     ."
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>"._("Number of Visits")." : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
     ".fm_number_select ("authvisits", 0, 100)."
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>"._("Comment")." : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
         <INPUT TYPE=TEXT NAME=\"authcomment\" SIZE=30 MAXLENGTH=100
          VALUE=\"".prepare($authcomment)."\">
        </TD>
       </TR>
 
       </TABLE>

       <CENTER>
       <INPUT TYPE=SUBMIT VALUE=\"  ".
         ( ($action=="addform") ? _("Add") : _("Modify"))."  \">
       <INPUT TYPE=RESET  VALUE=\" "._("Clear")." \">
       </CENTER>
       </FORM>

       <CENTER>
        <A HREF=\"$page_name?$_auth&patient=$patient\"
         ><$STDFONT_B>". 
	  ( ($action=="addform") ? _("Abandon Addition") :
	    _("Abandon Modification") )."<$STDFONT_E></A>
       </CENTER>
     ";
     freemed_display_box_bottom ();
     break;

   case "add":
     freemed_display_box_top (_("Adding")." "._("$record_name"), $page_name, 
       "manage.php?id=$patient");
     echo "
       <CENTER><$STDFONT_B><B>".("Adding")." . . . </B>
     ";

       // actual addition
     $query = "INSERT INTO $db_name VALUES (
       '$cur_date',
       '0000-00-00',
       '".addslashes($patient)             ."',
       '".fm_date_assemble("authdtbegin")  ."',
       '".fm_date_assemble("authdtend")    ."',
       '".addslashes($authnum)             ."',
       '".addslashes($authtype)            ."',
       '".addslashes($authprov)            ."',
       '".addslashes($authprovid)          ."',
       '".addslashes($authinsco)           ."',
       '".addslashes($authvisits)          ."',
       '0',
       '0',
       '".addslashes($authcomment)         ."',
       NULL ) "; // actual add query
     $result = $sql->query ($query);
     if ($result)
       echo " <B> "._("done")." </B>\n";
     else
       echo " <B> <FONT COLOR=\"#ff0000\">"._("ERROR")."</FONT> </B>\n";
     echo "
       <$STDFONT_E></CENTER>
       <BR><BR>
       <CENTER><A HREF=\"manage.php?$_auth&id=$patient\"
        ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A>
       <B>|</B>
       <A HREF=\"$page_name?$_auth&patient=$patient\"
        ><$STDFONT_B>"._("$record_name")."<$STDFONT_E></A>
       </CENTER>
       <BR>
     ";
     freemed_display_box_bottom ();
     break;

   case "mod":
     freemed_display_box_top (_("Modifying")." "._("$record_name"));
     echo "<B><$STDFONT_B>"._("Modifying")." . . . <$STDFONT_E></B>\n";
     $query = "UPDATE $db_name SET
       authdtmod      = '$cur_date',
       authdtbegin    = '".fm_date_assemble("authdtbegin")."',
       authdtend      = '".fm_date_assemble("authdtend")  ."',
       authnum        = '".addslashes($authnum)           ."',
       authtype       = '".addslashes($authtype)          ."',
       authprov       = '".addslashes($authprov)          ."',
       authprovid     = '".addslashes($authprovid)        ."',
       authinsco      = '".addslashes($authinsco)         ."',
       authvisits     = '".addslashes($authvisits)        ."',
       authcomment    = '".addslashes($authcomment)       ."'
       WHERE id='$id'";
     $result = $sql->query ($query);
     if ($result) echo "<B><$STDFONT_B>"._("done")."<$STDFONT_E></B>\n";
      else echo "<B><$STDFONT_B>"._("ERROR")."<$STDFONT_E></B>\n";
     echo "
       <P>
       <CENTER>
        <A HREF=\"manage.php?$_auth&id=$patient\"
         ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A>
        <B>|</B>
        <A HREF=\"$page_name?$_auth&patient=$patient\"
         ><$STDFONT_B>"._("View/Modify")." "._("$record_name")."<$STDFONT_E></A>
        <BR>
        <A HREF=\"$page_name?$_auth&patient=$patient&action=addform\"
         ><$STDFONT_B>"._("Add")." "._("$record_name")."<$STDFONT_E></A>
       </CENTER>
     ";
     freemed_display_box_bottom ();
     break;

   default:
     // in case of emergency, break glass -- default shows all things from
     // specified patient...

     $query = "SELECT * FROM $db_name
        WHERE (authpatient='".addslashes($patient)."')
        ORDER BY authdtbegin,authdtend";
     $result = $sql->query ($query);
     $rows = ( ($result > 0) ? $sql->num_rows ($result) : 0 );

     $this_patient = new Patient ($patient);
     
     if ($rows < 1) {
       freemed_display_box_top (_($record_name));
       echo freemed_patient_box($this_patient)."
         <P>
         <CENTER>
         <$STDFONT_B>"._("This patient has no authorizations.")."<$STDFONT_E>
         </CENTER>
         <P>
         <CENTER>
         <A HREF=\"$page_name?$_auth&action=addform&patient=$patient\"
          ><$STDFONT_B>"._("Add")." "._("$record_name")."<$STDFONT_E></A>
         <B>|</B>
         <A HREF=\"manage.php?$_auth&id=$patient\"
          ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A>
         </CENTER>
         <P>
       ";
       freemed_display_box_bottom ();
       freemed_close_db ();
       freemed_display_html_bottom ();
       DIE("");
     } // if there are none...

       // or else, display them...
     freemed_display_box_top (_($record_name),
      "manage.php?id=$patient");
     $this_patient = new Patient ($patient);
     echo freemed_patient_box($this_patient)."
       <P>
     ".
     freemed_display_itemlist (
       $result,
       $page_name,
       array (
         "Dates" => "authdtbegin",
	 "<FONT COLOR=\"#000000\">_</FONT>" => 
	    "", // &nbsp; doesn't work, dunno why
	 "&nbsp;"  => "authdtend"
       ),
       array ("", "/", "")
     );
     freemed_display_box_bottom ();
     break;
 } // end master action switch

 freemed_close_db ();
 freemed_display_html_bottom ();

?>
