<?php
 # file: cpt.php3
 # desc: CPT (procedural codes) database
 # code: jeff b (jeff@univrel.pr.uconn.edu)
 # lic : GPL, v2

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
 case "addform":
 case "modform":
  switch ($action) { // begin inner action switch
   case "addform":
    $next_action = "add";
    $this_action = "$Add";
    break; // end case addform
   case "modform":
    $next_action = "mod";
    $this_action = "$Modify";
    if ($id<1) DIE ("$page_name :: need to have id for modform");
    $this_record  = freemed_get_link_rec ($id, $db_name);
    $cptcode      = $this_record ["cptcode"];
    $cptnameint   = $this_record ["cptnameint"];
    $cptnameext   = $this_record ["cptnameext"];
    $cptgender    = $this_record ["cptgender"];
    $cpttaxed     = $this_record ["cpttaxed"];
    $cptreqcpt    = fm_split_into_array ($this_record ["cptreqcpt"]);
    $cptexccpt    = fm_split_into_array ($this_record ["cptexccpt"]);
    $cptreqicd    = fm_split_into_array ($this_record ["cptreqicd"]);
    $cptexcicd    = fm_split_into_array ($this_record ["cptexcicd"]);
    $cptrelval    = bcadd($this_record ["cptrelval"],0,2);
    $cptdeftos    = $this_record ["cptdeftos"];
    $cptdefstdfee = $this_record ["cptdefstdfee"];
    $cptstdfee    = fm_split_into_array ($this_record ["cptstdfee"]);
    $cpttos       = fm_split_into_array ($this_record ["cpttos"]);
    $cpttype      = $this_record ["cpttype"];
    break; // end case modform
  } // end inner action switch
  freemed_display_box_top ("$this_action $record_name");

  // gender switch
  $gender_n = $gender_f = $gender_m = "";
  switch ($cptgender) {
    case "f": $gender_f = "SELECTED"; break;
    case "m": $gender_m = "SELECTED"; break;
    case "n":
    default:  $gender_n = "SELECTED"; break;
  } // end of gender switch

  // taxed switch
  $taxed_n = $taxed_y = "";
  switch ($cpttaxed) {
    case "y": $taxed_y = "SELECTED"; break;
    case "n":
    default:  $taxed_n = "SELECTED"; break;
  } // end of taxed switch

  if ($action=="modform")
   echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&id=$id&action=profileform\"
    ><$STDFONT_B>Fee Profiles Modification<$STDFONT_E></A>
    </CENTER>
    <P>
   ";

  echo "
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"_auth\"  VALUE=\"$_auth\">
    <INPUT TYPE=HIDDEN NAME=\"id\"     VALUE=\"$id\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"$next_action\">

    <TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 VALIGN=MIDDLE
     ALIGN=CENTER>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Procedural Code : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
       <INPUT TYPE=TEXT NAME=\"cptcode\" SIZE=8 MAXLENGTH=7
        VALUE=\"$cptcode\">
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Internal Description : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"cptnameint\" SIZE=20 MAXLENGTH=50
       VALUE=\"$cptnameint\">
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>External Description : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"cptnameext\" SIZE=20 MAXLENGTH=50
       VALUE=\"$cptnameext\">
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Gender Restriction : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <SELECT NAME=\"cptgender\">
       <OPTION VALUE=\"n\" $gender_n>no restriction
       <OPTION VALUE=\"f\" $gender_f>female only
       <OPTION VALUE=\"m\" $gender_m>male only
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Taxed? : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <SELECT NAME=\"cpttaxed\">
       <OPTION VALUE=\"n\" $taxed_n>no
       <OPTION VALUE=\"y\" $taxed_y>yes
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Internal Procedure Type : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <SELECT NAME=\"cpttype\">
       <OPTION VALUE=\"0\">$NONE_SELECTED
  ";
  $i_res = fdb_query ("SELECT * FROM intservtype");
  while ($i_r = fdb_fetch_array ($i_res)) {
    if ($i_r["id"]==$cpttype) { $this_selected = "SELECTED"; }
     else                     { $this_selected = "";         }
    echo "    <OPTION VALUE=\"".$i_r["id"]."\" $this_selected>".
         fm_prep($i_r["intservtype"])."\n";
  } // end while for intservtype
  echo "
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Relative Value : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"cptrelval\" SIZE=10 MAXLENGTH=9
       VALUE=\"$cptrelval\">
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Default Type of Service : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <SELECT NAME=\"cptdeftos\">
  ";
  freemed_display_tos ($cptdeftos);
  echo "
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Default Standard Fee : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"cptdefstdfee\" SIZE=10 MAXLENGTH=8
       VALUE=\"$cptdefstdfee\">
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>Diagnosis Required : <$STDFONT_E>
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
      <$STDFONT_B>Diagnosis Excluded : <$STDFONT_E>
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
      <$STDFONT_B>Procedural Codes Required : <$STDFONT_E>
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
      <$STDFONT_B>Procedural Codes Excluded : <$STDFONT_E>
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

    <P>
    <CENTER>
     <INPUT TYPE=SUBMIT VALUE=\"$this_action\">
     <INPUT TYPE=RESET VALUE=\"$Clear\">
    </CENTER>
    <P>

    </FORM>
  ";
  freemed_display_box_bottom ();
  break; // end add/mod form

 case "add": // modify action
  freemed_display_box_top ("$Adding $record_name");
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
   <P>
   <$STDFONT_B>$Adding ...
  ";
  if ($debug) echo " ( query = \"$query\" ) <BR>\n";
  $result = fdb_query ($query);
  if ($result) { echo "$Done."; }
   else        { echo "$ERROR"; }
  echo "
   <$STDFONT_E>
   <P>
   <CENTER>
   <A HREF=\"$page_name?$_auth&action=addform\"
   ><$STDFONT_B>Add Another<$STDFONT_E></A> <B>|</B>
   <A HREF=\"$page_name?$_auth\"
   ><$STDFONT_B>Return to the CPT Codes Menu<$STDFONT_E>
   </CENTER>
   <P>
  ";
  freemed_display_box_bottom ();
  break; // end add action

 case "mod": // modify action
   freemed_display_box_top ("$Modifying $record_name");
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
   <P>
   <$STDFONT_B>$Modifying ...
  ";
  if ($debug) echo " ( query = \"$query\" ) <BR>\n";
  $result = fdb_query ($query);
  if ($result) { echo "$Done."; }
   else        { echo "$ERROR"; }
  echo "
   <$STDFONT_E>
   <P>
   <CENTER>
   <A HREF=\"$page_name?$_auth\"
   ><$STDFONT_B>Return to the CPT Codes Menu<$STDFONT_E>
   </CENTER>
   <P>
  ";
  freemed_display_box_bottom ();
  break; // end modify action

 case "del": // delete action
  echo "ACTION NOT IMPLEMENTED YET<BR>\n";
  break; // end delete action

 case "profileform": // insurance company profiles form
  $num_inscos = fdb_num_rows (fdb_query ("SELECT * FROM insco"));
  $this_code  = freemed_get_link_rec ($id, $db_name);
  $cpttos     = fm_split_into_array ($this_code["cpttos"]);
  $cptstdfee  = fm_split_into_array ($this_code["cptstdfee"]);
  freemed_display_box_top ("$record_name");
  echo "
   <P>
    <CENTER>
    <$STDFONT_B><B>Current Code</B> : <$STDFONT_E>
    <A HREF=\"$page_name?$_auth&id=$id&action=modform\"
    ><$STDFONT_B>".$this_code["cptcode"]."<$STDFONT_E></A>&nbsp;
    <$STDFONT_B><I>(".$this_code["cptnameint"].")</I><$STDFONT_E>
    <BR>
    <$STDFONT_B><U>Default Standard Fee</U> :
    ".bcadd($this_code["cptdefstdfee"],0,2)."<$STDFONT_E>
    <BR>
    <$STDFONT_B><U>Default Type of Service</U> :
    ".freemed_get_link_field ($this_code["cptdeftos"], "tos",
      "tosname")."<$STDFONT_E>
    </CENTER> 
   <P>
   <CENTER>
    <$STDFONT_B SIZE=-1><I>
     Please note that selecting \"0\" or \"NONE SELECTED\"<BR>
     will cause the default values to be used.
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
    <TD><B>Insurance Company</B>&nbsp;</TD>
    <TD><B>Type of Service</B>&nbsp;</TD>
    <TD><B>Standard Fee</B></TD>
   </TR>
  ";
  $_alternate = freemed_bar_alternate_color ();
  for ($i=1;$i<=$num_inscos;$i++) { // loop thru inscos
   if (empty($cptstdfee[$i])) $cptstdfee[$i] = "0.00";
   $this_insco = new InsuranceCompany ($i);
   $_alternate = freemed_bar_alternate_color ($_alternate);
   echo "
    <TR BGCOLOR=$_alternate>
     <TD>".fm_prep($this_insco->insconame)."</TD>
     <TD>
      <SELECT NAME=\"cpttos$brackets\">
   ";
   freemed_display_tos ($cpttos[$i]);
   echo "
      </SELECT>
     </TD>
     <TD>
      <INPUT TYPE=TEXT NAME=\"cptstdfee$brackets\" SIZE=10
       MAXLENGTH=9 VALUE=\"".fm_prep($cptstdfee[$i])."\">
     </TD>
    </TR>
   ";
  } // end loop thru inscos
  echo "
   </TABLE>
   <P>
    <CENTER>
     <INPUT TYPE=SUBMIT VALUE=\"$Modify\">
     <INPUT TYPE=RESET  VALUE=\"$Clear\">
    </CENTER>
   <P>
   </FORM>
  ";
  freemed_display_box_bottom ();
  break; // end insurance company profiles form 

 case "profile": // modification for the profile form
  freemed_display_box_top ("$Modifying $record_name");
  $query = "UPDATE $db_name SET
            cpttos='".fm_join_from_array($cpttos)."',
            cptstdfee='".fm_join_from_array($cptstdfee)."'
            WHERE id='$id'";
  echo "
   <P>
   <$STDFONT_B>$Modifying ... 
  ";
  $result = fdb_query ($query);
  if ($result) { echo "$Done."; }
   else        { echo "$ERROR"; }
  echo "
   <$STDFONT_E>
   <P>
   <CENTER>
    <A HREF=\"$page_name?$_auth\"
    ><$STDFONT_B>Return to $record_name Menu<$STDFONT_E></A>
   </CENTER>
   <P>
  ";
  freemed_display_box_bottom ();
  break; // end of mod for the profile form

 default: // default action begin
  freemed_display_box_top ("$record_name");
  $query = "SELECT * FROM $db_name
            ORDER BY cptcode";
  $result = fdb_query ($query);
  freemed_display_actionbar ();
  $_alternate = freemed_bar_alternate_color ();
  echo "
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 VALIGN=MIDDLE
    ALIGN=CENTER>
   <TR>
    <TD><B>CPT Code</B></TD>
    <TD><B>Internal Name</B></TD>
    <TD><B>Action</B></TD>
   </TR>
  ";
  while ($r = fdb_fetch_array ($result)) {
    $_alternate = freemed_bar_alternate_color ($_alternate);
    echo "
     <TR BGCOLOR=$_alternate>
      <TD><$STDFONT_B>".$r["cptcode"]."<$STDFONT_E></TD>
      <TD><$STDFONT_B><I>".$r["cptnameint"]."</I><$STDFONT_E></TD>
      <TD>
    ";
    if (($this_user->getLevel())>$database_level)
     echo "
      <A HREF=\"$page_name?$_auth&id=".$r["id"]."&action=modform\"
      ><$STDFONT_B SIZE=-2>$lang_MOD<$STDFONT_E></A> &nbsp;
     ";
    if (($this_user->getLevel())>$delete_level)
     echo "
      <A HREF=\"$page_name?$_auth&id=".$r["id"]."&action=del\"
      ><$STDFONT_B SIZE=-2>$lang_DEL<$STDFONT_E></A> &nbsp;
     ";
    echo "&nbsp;
      </TD>
     </TR>
    ";
  } // end while looping for fetch array
  echo "
   </TABLE>
  ";
  freemed_display_actionbar ();
  freemed_display_box_bottom ();
  break; // default action end
} // end master switch

freemed_close_db ();
freemed_display_html_bottom ();
?>
