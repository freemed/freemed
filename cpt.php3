<?php
 // file: cpt.php3
 // desc: CPT (procedural codes) database
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

 $page_name = "cpt.php3";
 $record_name = "CPT Codes";
 $db_name = "cpt";
 include ("global.var.inc");
 include ("freemed-functions.inc");

freemed_open_db ($LoginCookie);
$this_user = new User ($LoginCookie);

freemed_display_html_top ();
freemed_display_banner ();

switch ($action) { // begin master switch
 case "addform": case "add":
 case "modform": case "mod":
  if (!$been_here) {
   switch ($action) { // begin inner action switch
    case "addform":
     break; // end case addform
    case "modform":
     if ($id<1) DIE ("$page_name :: need to have id for modform");
     $this_record  = freemed_get_link_rec ($id, $db_name);
     extract ($this_record);
     $cptreqcpt    = fm_split_into_array ($cptreqcpt);
     $cptexccpt    = fm_split_into_array ($cptexccpt);
     $cptreqicd    = fm_split_into_array ($cptreqicd);
     $cptexcicd    = fm_split_into_array ($cptexcicd);
     $cptrelval    = bcadd($cptrelval, 0, 2);
     $cptstdfee    = fm_split_into_array ($cptstdfee);
     $cpttos       = fm_split_into_array ($cpttos);
     break; // end case modform
   } // end inner action switch
   $been_here = 1; // make sure been here is set now
  } // end checking if been here

  freemed_display_box_top (( ($action=="addform") ? _("Add") : _("Modify") ).
    " "._($record_name));

  $book = new notebook (
    array ("action", "_auth", "id", "been_here"),
    NOTEBOOK_COMMON_BAR | NOTEBOOK_STRETCH);
    
  $book->add_page (
    _("Primary Information"),
    array ("cptcode", "cptnameint", "cptnameext", "cptgender",
           "cpttaxed", "cpttype"),
    form_table (array (
      _("Procedural Code") =>
       "<INPUT TYPE=TEXT NAME=\"cptcode\" SIZE=8 MAXLENGTH=7
        VALUE=\"".prepare($cptcode)."\"> &nbsp;".
	$book->generate_refresh(),
      _("Internal Description") =>
      "<INPUT TYPE=TEXT NAME=\"cptnameint\" SIZE=20 MAXLENGTH=50
       VALUE=\"".prepare($cptnameint)."\">",
      _("External Description") =>
      "<INPUT TYPE=TEXT NAME=\"cptnameext\" SIZE=20 MAXLENGTH=50
       VALUE=\"".prepare($cptnameext)."\">",
      _("Gender Restriction") =>
       "<SELECT NAME=\"cptgender\">
       <OPTION VALUE=\"n\" ".
         ( ($cptgender=="n") ? "SELECTED" : "" ).">"._("no restriction")."
       <OPTION VALUE=\"f\" ".
         ( ($cptgender=="f") ? "SELECTED" : "" ).">"._("female only")."
       <OPTION VALUE=\"m\" ".
         ( ($cptgender=="m") ? "SELECTED" : "" ).">"._("male only")."
      </SELECT>",
      _("Taxed?") =>
      "<SELECT NAME=\"cpttaxed\">
       <OPTION VALUE=\"n\" ".
         ( ($cpttaxed=="n") ? "SELECTED" : "" ).">"._("no")."
       <OPTION VALUE=\"y\" ".
         ( ($cpttaxed=="y") ? "SELECTED" : "" ).">"._("yes")."
      </SELECT>",
      _("Internal Service Types") =>
     freemed_display_selectbox(
       fdb_query("SELECT * FROM intservtype"),
       "#intservtype#",
       "cpttype")
       ))
  );

  $book->add_page (
    _("Billing Information"),
    array ("cptrelval", "cptdeftos", "cptdefstdfee"),
    "<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Relative Value")." : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"cptrelval\" SIZE=10 MAXLENGTH=9
       VALUE=\"".prepare($cptrelval)."\">
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Default Type of Service")." : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
        ".freemed_display_selectbox (
          fdb_query ("SELECT tosname,tosdescrip,id FROM tos ORDER BY tosname"),
  	  "#tosname# #tosdescrip#",
	  "cpttos[$i]"
	  )."
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Default Standard Fee")." : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"cptdefstdfee\" SIZE=10 MAXLENGTH=8
       VALUE=\"".prepare($cptdefstdfee)."\">
     </TD>
    </TR>

    </TABLE>
  ");

  $book->add_page (
    _("Inclusion/Exclusion"),
    array ("cptreqicd", "cptexcicd", "cptreqcpt", "cptexccpt"),
    "<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Diagnosis Required")." : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
   ".freemed_multiple_choice ("SELECT * FROM icd9
                               ORDER BY icd9code,icd9descrip",
                              "icd9code:icd9descrip",
                              "cptreqicd",
                              $cptreqicd,
                              false)."
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Diagnosis Excluded")." : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
   ".freemed_multiple_choice ("SELECT * FROM icd9
                              ORDER BY icd9code,icd9descrip",
                             "icd9code:icd9descrip",
                             "cptexcicd",
                             $cptexcicd,
                             true)."
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Procedural Codes Required")." : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
   ".freemed_multiple_choice ("SELECT * FROM cpt
                               ORDER BY cptnameint,cptcode",
                              "cptcode:cptnameint",
                              "cptreqcpt",
                              $cptreqcpt,
                              false)."
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Procedural Codes Excluded")." : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
   ".freemed_multiple_choice ("SELECT * FROM cpt
                               ORDER BY cptcode,cptnameint",
                              "cptcode:cptnameint",
                              "cptexccpt",
                              $cptexccpt,
                              true)."
     </TD>
    </TR>

    </TABLE>
  ");

  if ( (!empty($cptcode)) and (!empty($cptnameint)) ) {
    $num_inscos = fdb_num_rows (fdb_query ("SELECT * FROM insco"));
    $serv_buffer = "
     <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>
      <TD><B>"._("Insurance Company")."</B>&nbsp;</TD>
      <TD><B>"._("Type of Service")."</B>&nbsp;</TD>
      <TD><B>"._("Standard Fee")."</B></TD>
     </TR>
    ";
    for ($i=1;$i<=$num_inscos;$i++) { // loop thru inscos
     if (empty($cptstdfee[$i])) $cptstdfee[$i] = "0.00";
     $this_insco = new InsuranceCompany ($i);
     $serv_buffer .= "
      <TR BGCOLOR=".($_alternate=freemed_bar_alternate_color($_alternate)).">
       <TD>".prepare($this_insco->insconame)."</TD>
       <TD>
        ".freemed_display_selectbox (
          fdb_query ("SELECT tosname,tosdescrip,id FROM tos ORDER BY tosname"),
  	  "#tosname# #tosdescrip#",
	  "cpttos[$i]"
	  )."
       </TD>
       <TD>
        <INPUT TYPE=TEXT NAME=\"cptstdfee$brackets\" SIZE=10
         MAXLENGTH=9 VALUE=\"".prepare($cptstdfee[$i])."\">
       </TD>
      </TR>
     ";
    } // end loop thru inscos
    $serv_buffer .= "
     </TABLE>
    ";
  $book->add_page (
    _("Fee Profiles"),
    array (""),
    "<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2
      ALIGN=CENTER>

      <!-- first values, to push offset to 1 from 0 -->
      <INPUT TYPE=HIDDEN NAME=\"cpttos$brackets\"    VALUE=\"\">
      <INPUT TYPE=HIDDEN NAME=\"cptstdfee$brackets\" VALUE=\"\">

     <TR>
      <TD ALIGN=RIGHT WIDTH=\"50%\">
       <$STDFONT_B><B>"._("Procedural Code")."</B> : <$STDFONT_E></TD>
      <TD ALIGN=LEFT><$STDFONT_B>".prepare($cptcode)."<$STDFONT_E>
       <$STDFONT_B><I>(".prepare($cptnameint).")</I><$STDFONT_E></TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       <$STDFONT_B>"._("Default Standard Fee")." : <$STDFONT_E></TD>
      <TD ALIGN=LEFT>
       <$STDFONT_B>".bcadd($this_code["cptdefstdfee"],0,2)."<$STDFONT_E>
      </TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       <$STDFONT_B>"._("Default Type of Service")." : <$STDFONT_E></TD>
      <TD ALIGN=LEFT>
       <$STDFONT_B>".freemed_get_link_field ($cptdeftos, "tos",
        "tosname")."<$STDFONT_E></TD>
     </TR>

     <TR>
      <TD COLSPAN=2><$STDFONT_B SIZE=-1><I>
       "._("Please note that selecting \"0\" or \"NONE SELECTED\" will cause the default values to be used.")."
      </I><$STDFONT_E>
     </TD></TR>
     
     </TABLE>

     <! -- fee profiles stuff here -->
     $serv_buffer

  ");
 } // end of fee profiles conditional

 if (!$book->is_done()) {
   echo $book->display();
 } else {
   switch ($action) {
     case "add": case "addform":
      $query = "INSERT INTO $db_name VALUES (
            '".addslashes($cptcode).                        "',
            '".addslashes($cptnameint).                     "',
            '".addslashes($cptnameext).                     "',
            '".addslashes($cptgender).                      "',
            '".addslashes($cpttaxed).                       "',
            '".addslashes($cpttype).                        "',
            '".addslashes(fm_join_from_array ($cptreqcpt)). "',
            '".addslashes(fm_join_from_array ($cptexccpt)). "',
            '".addslashes(fm_join_from_array ($cptreqicd)). "',
            '".addslashes(fm_join_from_array ($cptexcicd)). "',
            '".addslashes($cptrelval).                      "',
            '".addslashes($cptdeftos).                      "',
            '".addslashes($cptdefstdfee).                   "',
            '".addslashes(fm_join_from_array ($cptstdfee)). "',
            '".addslashes(fm_join_from_array ($cpttos)).    "',
            NULL )";
      echo "
       <P ALIGN=CENTER>
       <$STDFONT_B>"._("Adding")." ...
      ";
      if ($debug) echo " ( query = \"$query\" ) <BR>\n";
      break; // end action = add
      
     case "mod": case "modform": // modify action
      $query = "UPDATE $db_name SET
            cptcode      ='".addslashes($cptcode).                        "',
            cptnameint   ='".addslashes($cptnameint).                     "',
            cptnameext   ='".addslashes($cptnameext).                     "',
            cptgender    ='".addslashes($cptgender).                      "',
            cpttaxed     ='".addslashes($cpttaxed).                       "',
            cpttype      ='".addslashes($cpttype).                        "',
            cptreqcpt    ='".addslashes(fm_join_from_array ($cptreqcpt)). "',
            cptexccpt    ='".addslashes(fm_join_from_array ($cptexccpt)). "',
            cptreqicd    ='".addslashes(fm_join_from_array ($cptreqicd)). "',
            cptexcicd    ='".addslashes(fm_join_from_array ($cptexcicd)). "',
            cptrelval    ='".addslashes($cptrelval).                      "',
            cptdeftos    ='".addslashes($cptdeftos).                      "',
            cptdefstdfee ='".addslashes($cptdefstdfee).                   "',
            cptstdfee    ='".addslashes(fm_join_from_array ($cptstdfee)). "',
            cpttos       ='".addslashes(fm_join_from_array ($cpttos)).    "'
            WHERE id='$id'";
      echo "
       <P ALIGN=CENTER>
       <$STDFONT_B>"._("Modifying")." ...
      ";
      if ($debug) echo " ( query = \"$query\" ) <BR>\n";
      break; // end action mod/modform 
   } // end switch add/modform   
  $result = fdb_query ($query);
  if ($result) { echo _("done")."."; }
  else         { echo _("ERROR");    }
  echo "
   <$STDFONT_E>
   <P>
   <CENTER>
   <A HREF=\"$page_name?$_auth&action=addform\"
   ><$STDFONT_B>"._("Add Another")."<$STDFONT_E></A> <B>|</B>
   <A HREF=\"$page_name?$_auth\"
   ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
   </CENTER>
   <P>
  ";
  } // end if book done
  freemed_display_box_bottom ();
  break; // end add/mod action

 case "del": case "delete": // delete action
  freemed_display_box_top (_("Deleting")." "._($record_name));
  echo "<P ALIGN=CENTER><$STDFONT_B>"._("Deleting")." ... ";
  $query = "DELETE FROM $db_name WHERE id='".addslashes($id)."'";
  $result = fdb_query ($query);
  if ($result) { echo "<B>"._("done").".</B>"; }
   else        { echo "<B>"._("ERROR")."</B>"; }
  echo "
   <$STDFONT_E></P>
   <CENTER>
    <A HREF=\"$page_name?$_auth\"
    ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
   </CENTER>
  ";
  freemed_display_box_bottom ();
  break; // end delete action

/*
 case "profileform": // insurance company profiles form
  $num_inscos = fdb_num_rows (fdb_query ("SELECT * FROM insco"));
  $this_code  = freemed_get_link_rec ($id, $db_name);
  $cpttos     = fm_split_into_array ($this_code["cpttos"]);
  $cptstdfee  = fm_split_into_array ($this_code["cptstdfee"]);
  freemed_display_box_top (_($record_name));
  echo "
   <P>
    <CENTER>
    <$STDFONT_B><B>"._("Current Code")."</B> : <$STDFONT_E>
    <A HREF=\"$page_name?$_auth&id=$id&action=modform\"
    ><$STDFONT_B>".$this_code["cptcode"]."<$STDFONT_E></A>&nbsp;
    <$STDFONT_B><I>(".$this_code["cptnameint"].")</I><$STDFONT_E>
    <BR>
    <$STDFONT_B><U>"._("Default Standard Fee")."</U> :
    ".bcadd($this_code["cptdefstdfee"],0,2)."<$STDFONT_E>
    <BR>
    <$STDFONT_B><U>"._("Default Type of Service")."</U> :
    ".freemed_get_link_field ($this_code["cptdeftos"], "tos",
      "tosname")."<$STDFONT_E>
    </CENTER> 
   <P>
   <CENTER>
    <$STDFONT_B SIZE=-1><I>
     "._("Please note that selecting \"0\" or \"NONE SELECTED\" will cause the default values to be used.")."
    </I><$STDFONT_E> 
   </CENTER>
   <P>
   <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"profile\">

    <!-- first values, to push offset to 1 from 0 -->
    <INPUT TYPE=HIDDEN NAME=\"cpttos$brackets\"    VALUE=\"\">
    <INPUT TYPE=HIDDEN NAME=\"cptstdfee$brackets\" VALUE=\"\">

   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2 VALIGN=MIDDLE
    ALIGN=CENTER>
   <TR>
    <TD><B>"._("Insurance Company")."</B>&nbsp;</TD>
    <TD><B>"._("Type of Service")."</B>&nbsp;</TD>
    <TD><B>"._("Standard Fee")."</B></TD>
   </TR>
  ";
  for ($i=1;$i<=$num_inscos;$i++) { // loop thru inscos
   if (empty($cptstdfee[$i])) $cptstdfee[$i] = "0.00";
   $this_insco = new InsuranceCompany ($i);
   echo "
    <TR BGCOLOR=".($_alternate=freemed_bar_alternate_color($_alternate)).">
     <TD>".prepare($this_insco->insconame)."</TD>
     <TD>
      ".freemed_display_selectbox (
        fdb_query ("SELECT tosname,tosdescrip,id FROM tos ORDER BY tosname"),
	"#tosname# #tosdescrip#",
	"cpttos[$i]"
	)."
     </TD>
     <TD>
      <INPUT TYPE=TEXT NAME=\"cptstdfee$brackets\" SIZE=10
       MAXLENGTH=9 VALUE=\"".prepare($cptstdfee[$i])."\">
     </TD>
    </TR>
   ";
  } // end loop thru inscos
  echo "
   </TABLE>
   <P>
    <CENTER>
     <INPUT TYPE=SUBMIT VALUE=\""._("Modify")."\">
     <INPUT TYPE=RESET  VALUE=\""._("Clear")."\">
    </CENTER>
   <P>
   </FORM>
  ";
  freemed_display_box_bottom ();
  break; // end insurance company profiles form 

 case "profile": // modification for the profile form
  freemed_display_box_top (_("Modifying")." "._($record_name));
  $query = "UPDATE $db_name SET
            cpttos='".fm_join_from_array($cpttos)."',
            cptstdfee='".fm_join_from_array($cptstdfee)."'
            WHERE id='$id'";
  echo "
   <P>
   <$STDFONT_B>"._("Modifying")." ... 
  ";
  $result = fdb_query ($query);
  if ($result) { echo _("done")."."; }
   else        { echo _("ERROR");    }
  echo "
   <$STDFONT_E>
   <P>
   <CENTER>
    <A HREF=\"$page_name?$_auth\"
    ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
   </CENTER>
   <P>
  ";
  freemed_display_box_bottom ();
  break; // end of mod for the profile form
*/

 default: // default action begin
  freemed_display_box_top (_($record_name));
  $query = "SELECT cptcode,cptnameint,id FROM $db_name ORDER BY cptcode";
  $result = fdb_query ($query);
  echo freemed_display_itemlist (
    $result,
    $page_name,
    array (
      _("Procedural Code")	=>	"cptcode",
      _("Internal Description")	=>	"cptnameint"
    ),
    array ("", "")
  );
  freemed_display_box_bottom ();
  break; // default action end
} // end master switch

freemed_close_db ();
freemed_display_html_bottom ();
?>
