<?php
 # file: manage_payment_records.php3
 # note: ledger/patient payment record functions
 # code: jeff b (jeff@univrel.pr.uconn.edu)
 # lic : GPL, v2

 $page_name   = "manage_payment_records.php3";
 $record_name = "Manage Patient Ledger";
 $db_name     = "payrec";

 include ("global.var.inc");
 include ("freemed-functions.inc");

 freemed_open_db ($LoginCookie);
 $this_user = new User ($LoginCookie);

 freemed_display_html_top ();
 freemed_display_banner ();

 // create patient object
 if ($patient>0) { $this_patient = new Patient ($patient); }
  else           { DIE("NO PATIENT PROVIDED!");            }

 freemed_display_box_top ("$record_name");

 echo "
  <P>
  <CENTER>
   <$STDFONT_B><B>$Patient</B> : 
    <A HREF=\"manage.php3?$_auth&id=$patient\"
    >".$this_patient->fullName(true)."</A><$STDFONT_E>
  </CENTER>
  <P>

  <FORM ACTION=\"$page_name\" METHOD=POST>
   <INPUT TYPE=HIDDEN NAME=\"_auth\"   VALUE=\"$_auth\"  >
   <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">
 ";

 // initialize line item count
 $line_item_count = 0;

 $query = "SELECT * FROM $database.procrec
           WHERE ( (procpatient = '$patient') AND
                   (procbalcurrent > 0) )
           ORDER BY procdt";

 $result = fdb_query ($query);

 #if (!$result) die ("nothing for this patient");

 echo "
  <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3>
  <TR BGCOLOR=#cccccc>
   <TD>&nbsp;</TD>
   <TD><B>Date</B></TD>
   <TD><B>Proc Code</B></TD>
   <TD><B>Original Amt</B></TD>
   <TD><B>Amount Paid</B></TD>
   <TD><B>Current Amt</B></TD>
  </TR>
 ";

 $_alternate = freemed_bar_alternate_color ();

 // loop for all "line items"
 while ($r = fdb_fetch_array ($result)) {
   $line_item_count++;
   $_alternate = freemed_bar_alternate_color ($_alternate);
   $this_cpt = freemed_get_link_field ($r[proccpt], "cpt", "cptnameint"); 
   $this_cptmod = freemed_get_link_field ($r[proccptmod],
     "cptmod", "cptmod"); 
   echo "
    <TR BGCOLOR=".(
      ($item == $r[id]) ?
      "#aaaaaa" : $_alternate
    ).">
    <TD>
    <INPUT TYPE=RADIO NAME=\"item\" VALUE=\"".htmlentities($r[id])."\"
     ".( ($r[id] == $item) ?
         "CHECKED"        :
         ""                )."></TD>
    <TD>".fm_date_print ($r[procdt])."</TD>
    <TD ALIGN=LEFT>".htmlentities($this_cpt." (".$this_cptmod.")")."</TD>
    <TD ALIGN=RIGHT>".bcadd ($r[procbalorig], 0, 2)."</TD>
    <TD ALIGN=RIGHT>".bcadd ($r[procamtpaid], 0, 2)."</TD>
    <TD ALIGN=RIGHT>".bcadd ($r[procbalcurrent], 0, 2)."</TD>
    </TR>
   ";
 } // end looping for results

 echo "
  </TABLE>
  <P>
  <CENTER>
   <INPUT TYPE=SUBMIT VALUE=\"  Select Line Item  \">
  </CENTER>
  </FORM>
 ";

 if ($item > 0) {
  echo "
   <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"_auth\"   VALUE=\"$_auth\">
    <INPUT TYPE=HIDDEN NAME=\"item\"    VALUE=\"$item\">
    <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">
  ";
  switch ($action) {

   case "denial": // denial action
    $query = "INSERT INTO $database.payrec VALUES (
                '$cur_date',
                '0000-00-00',
                '$patient',
                '".fm_date_assemble("date_of_action")."',
                '3',
                '".addslashes($item)."',
                '0',
                '0',
                '0',
                '',
                '0',     
                #  (above is payrecamt)
                '".addslashes((
                   (empty($denial_reason)) ?
                   $denial_reason_text :
                   $denial_reason ))."',
                'unlocked',
                NULL 
              )";
    $result = fdb_query ($query);
    echo "
     <CENTER>
      Added denial.
     </CENTER>
    ";
    if ($denial_rebill == "yes") {
      $query = "UPDATE $database.procrec
                SET procbilled='0'
                WHERE id='".addslashes($item)."'";
      $result = fdb_query ($query);
      echo "
       <CENTER>
        Procedure set for rebill.
       </CENTER>
      ";
    } // end of denial rebill check
    break; // end of denial action

   case "transfer": // transfer action
    $query = "UPDATE $database.payrec
              SET payreclink='".addslashes($transfer_to)."'
              WHERE (
                id='".addslashes($item)."' AND
                payreccat='".PROCEDURE."'
              )";
    $result = fdb_query ($query);
    echo "
     <CENTER>
      Item transfered.
     </CENTER>
    ";
    $query = "INSERT INTO $database.payrec VALUES (
                '$cur_date',
                '0000-00-00',
                '$patient',
                '".fm_date_assemble("date_of_action")."',
                '".TRANSFER."',
                '".addslashes($item)."',
                '0',
                '0',
                '0',
                '',
                '0',     
                'TRANSFER',
                'unlocked',
                NULL 
              )";
    $result = fdb_query ($query);
    echo "
     <CENTER>
      Added transfer.
     </CENTER>
    ";
    break; // end of transfer action

   case "mistake": // mistake action
    $query = "DELETE FROM $database.procrec 
              WHERE id='".addslashes($item)."'";
    $result = fdb_query ($query);
    $query = "DELETE FROM $database.payrec 
              WHERE payrecproc='".addslashes($item)."'";
    $result = fdb_query ($query);
    echo "
     <CENTER>
      Item $item removed (with references).
     </CENTER>
    ";
    break; // end mistake action

   default: // default action (display forms)
    echo "
     <CENTER>
     <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3>

      <TR>
       <TD ALIGN=RIGHT>
        <$STDFONT_B>Date of Action : <$STDFONT_E>
       </TD><TD ALIGN=LEFT>
    ";
    fm_date_entry ("date_of_action");
    echo "
       </TD>
      </TR>

      <TR>
       <TD COLSPAN=2 ALIGN=LEFT BGCOLOR=#aaaaaa>
       <$STDFONT_B>
        <INPUT TYPE=RADIO NAME=\"action\" VALUE=\"denial\">
        <B>Denial</B>
       <$STDFONT_E>
      </TR>
      <TR>
       <TD ALIGN=RIGHT>
        <$STDFONT_B>Reason : <$STDFONT_E>
       </TD><TD ALIGN=LEFT>
        <SELECT NAME=\"denial_reason\">
         <OPTION VALUE=\"\">[Fill in]
     ";
     // generate list of past denial reasons to choose from
     $denial_query = "SELECT DISTINCT payrecdescrip FROM $database.payrec
                      WHERE payreccat='3'
                      ORDER BY payrecdescrip";
     $denial_result = fdb_query ($denial_query);
     if ($denial_result and (fdb_num_rows($denial_result)>0)) {
       while ($denial_r = fdb_fetch_array ($denial_result)) {
         echo "     <OPTION VALUE=\"".htmlentities($denial_r[payrecdesrip]).
              "\">".htmlentities($denial_r[payrecdescrip])."\n";
       } // end looping for all denial comments
     } // end checking for any results at all
     echo "
        </SELECT>
        <INPUT TYPE=TEXT NAME=\"denial_reason_text\" SIZE=25>
       </TD>
      </TR>
      <TR>
       <TD ALIGN=RIGHT>
        <$STDFONT_B>Resubmit? : <$STDFONT_E>
       </TD><TD ALIGN=LEFT>
        <INPUT TYPE=RADIO NAME=\"denial_resubmit\"
         VALUE=\"yes\" CHECKED>Yes &nbsp;&nbsp;
        <INPUT TYPE=RADIO NAME=\"denial_resubmit\"
         VALUE=\"no\"         >No
       </TD>
      </TR>

      <TR>
       <TD COLSPAN=2 ALIGN=LEFT BGCOLOR=#aaaaaa>
        <$STDFONT_B>
         <INPUT TYPE=RADIO NAME=\"action\" VALUE=\"transfer\">
         <B>Transfer</B> <I>(to another source of payment)</I>
        <$STDFONT_E>
       </TD>
      </TR>
      <TR>
       <TD ALIGN=RIGHT>
        <$STDFONT_B>To : <$STDFONT_E>
       </TD><TD ALIGN=LEFT>
        <SELECT NAME=\"transfer_to\">
         <OPTION VALUE=\"4\">Patient
        ".(
            ($this_patient->local_record[ptins1] != 0) ?
            "<OPTION VALUE=\"0\">1st Insurance\n" : ""
        ).(
            ($this_patient->local_record[ptins2] != 0) ?
            "<OPTION VALUE=\"1\">2nd Insurance\n" : ""
        ).(
            ($this_patient->local_record[ptins3] != 0) ?
            "<OPTION VALUE=\"2\">3rd Insurance\n" : ""
        )."
         <OPTION VALUE=\"3\">Worker's Comp
        </SELECT>
       </TD>
      </TR>

      <TR>
       <TD COLSPAN=2 ALIGN=LEFT BGCOLOR=#aaaaaa>
        <$STDFONT_B>
         <INPUT TYPE=RADIO NAME=\"action\" VALUE=\"mistake\">
         <B>Mistake</B> <I>(deletes record permanently)</I>
        <$STDFONT_E>
       </TD>
      </TR>

      <TR>
      </TR>

     </TABLE>
     </CENTER>
    ";
    break; // end of default action

  } // end master action switch
  echo "
    <P>

    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\"  Execute Action  \">
    </CENTER>

   </FORM>
  ";
 } // end checking for item

 freemed_display_box_bottom ();

 freemed_close_db ();
 freemed_display_html_bottom ();
?>
