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

 // pull the current physician (main physician)
 $physician = new Physician ($this_patient->local_record[ptdoc]);

 echo "
  <P>
  <CENTER>
   <$STDFONT_B><B>$Patient</B> : 
    <A HREF=\"manage.php3?$_auth&id=$patient\"
    >".htmlentities($this_patient->fullName(true))."</A><BR>
    <I>(".htmlentities($physician->fullName()).")</I><$STDFONT_E>
  </CENTER>
  <P>

  <FORM ACTION=\"$page_name\" METHOD=POST>
   <INPUT TYPE=HIDDEN NAME=\"_auth\"   VALUE=\"$_auth\"  >
   <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">
 ";

 // initialize line item count
 $line_item_count = 0;

 $query = "SELECT * FROM procrec
           WHERE ( (procpatient = '$patient') AND
                   (procbalcurrent > 0) )
           ORDER BY procdt";

 $result = fdb_query ($query);

 echo "
  <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
  <TR BGCOLOR=#cccccc>
   <TD>&nbsp;</TD>
   <TD><B>Date</B></TD>
   <TD><B>Proc Code</B></TD>
   <TD><B>Provider</B></TD>
   <TD ALIGN=RIGHT><B>Charged</B></TD>
   <TD ALIGN=RIGHT><B>Allowed</B></TD>
   <TD ALIGN=RIGHT><B>Paid</B></TD>
   <TD ALIGN=RIGHT><B>Balance</B></TD>
   <TD ALIGN=RIGHT><B>Billed</B></TD>
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
   $this_physician = new Physician ($r[procphysician]);
   echo "
    <TR BGCOLOR=".(
      ($item == $r[id]) ?
      "#00ffff" : $_alternate
    ).">
    <TD>
    <INPUT TYPE=RADIO NAME=\"item\" VALUE=\"".htmlentities($r[id])."\"
     ".( ($r[id] == $item) ?
         "CHECKED"        :
         ""                )."></TD>
    <TD>".fm_date_print ($r[procdt])."</TD>
    <TD ALIGN=LEFT>".htmlentities($this_cpt." (".$this_cptmod.")")."</TD>
    <TD ALIGN=LEFT>".htmlentities($this_physician->fullName())."</TD>
    <TD ALIGN=RIGHT>".bcadd ($r[procbalorig], 0, 2)."</TD>
    <TD ALIGN=RIGHT>".bcadd ($r[procamtallowed], 0, 2)."</TD>
    <TD ALIGN=RIGHT>".bcadd ($r[procamtpaid], 0, 2)."</TD>
    <TD ALIGN=RIGHT>".bcadd ($r[procbalcurrent], 0, 2)."</TD>
    <TD ALIGN=RIGHT>".(($r[procbilled]) ? "Yes" : "No")."</TD>

    </TR>
   ";
 } // end looping for results

 echo "
  </TABLE>
  <P>
  <CENTER>
   <SELECT NAME=\"action\">
    <OPTION VALUE=\"refresh\"  >Refresh
    <OPTION VALUE=\"denialform\"  >Denial
    <OPTION VALUE=\"mistakeform\" >Mistake
    <OPTION VALUE=\"paymentform\" >Payment
    <OPTION VALUE=\"transferform\">Transfer
   </SELECT>
   <INPUT TYPE=SUBMIT VALUE=\"  Select Line Item  \">
  </CENTER>
  </FORM>
 ";
 if ($action != "refresh") {
 if ($item > 0) {
  echo "
   <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"_auth\"   VALUE=\"$_auth\">
    <INPUT TYPE=HIDDEN NAME=\"item\"    VALUE=\"$item\">
    <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">
  ";

  // decide whether to show submit button
  $show_submit = true;

  switch ($action) {

   case "denial": // denial action
    $query = "INSERT INTO payrec VALUES (
                '".addslashes($cur_date)."',
                '0000-00-00',
                '".addslashes($patient)."',
                '".fm_date_assemble("date_of_action")."',
                '3',
                '".addslashes($item)."',
                '0',
                '0',
                '0',
                '',
                '0',     
                ". // (above is payrecamt)
                "'".addslashes((
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
    if ($denial_resubmit == "yes") {
      $query = "UPDATE procrec
                SET procbilled='0'
                WHERE id='".addslashes($item)."'";
      $result = fdb_query ($query);
      echo "
       <CENTER>
        Procedure set for rebill.
       </CENTER>
      ";
    } else {
      // otherwise adjust the amount to 0
      $query = "UPDATE procrec
                SET procbalcurrent='0'
                WHERE id='".addslashes($item)."'";
      $result = fdb_query ($query);
      echo "
       <CENTER>
        Procedure adjusted to zero.
       </CENTER>
      ";
    } // end of denial rebill check
    $show_submit = false;
    break; // end of denial action

   case "payment": // payment action
    echo "<CENTER>\n";
    if ($withhold > 0) {
      $query = "INSERT INTO $db_name VALUES (
                '".addslashes($cur_date)."',
                '',
                '".addslashes($patient)."',
                '".fm_date_assemble("date_of_action")."',
                '".WITHHOLD."',
                '".addslashes($item)."',
                '0',
                '',
                '',
                '".addslashes($voucher)."',
                '".addslashes($withhold)."',
                '',
                'unlocked',
                NULL
                )";
      $result = fdb_query ($query); 
      if ($result) echo "<$STDFONT_B>$Adding withhold <$STDFONT_E><BR> \n";
    } // end of withhold check
    if ($deductable > 0) {
      $query = "INSERT INTO $db_name VALUES (
                '".addslashes($cur_date)."',
                '',
                '".addslashes($patient)."',
                '".fm_date_assemble("date_of_action")."',
                '".DEDUCTABLE."',
                '".addslashes($item)."',
                '0',
                '',
                '',
                '".addslashes($voucher)."',
                '".addslashes($deductable)."',
                '',
                'unlocked',
                NULL
                )";
      $result = fdb_query ($query); 
      if ($result) echo "<$STDFONT_B>$Adding deductable.<$STDFONT_E><BR> \n";
    } // end of deductable check
    if ($adjustment > 0) {
      $query = "INSERT INTO $db_name VALUES (
                '".addslashes($cur_date)."',
                '',
                '".addslashes($patient)."',
                '".fm_date_assemble("date_of_action")."',
                '".ADJUSTMENT."',
                '".addslashes($item)."',
                '0',
                '',
                '',
                '".addslashes($voucher)."',
                '".addslashes($adjustment)."',
                '',
                'unlocked',
                NULL
                )";
      $result = fdb_query ($query); 
      if ($result) echo "<$STDFONT_B>$Adding adjustment.<$STDFONT_E><BR> \n";
    } // end of adjustment check

    if ($allowed_amount > 0)
    {
		// recalc the procedure balance

                $query = "UPDATE procrec SET procbalcurrent = '".addslashes($allowed_amount)."' - procamtpaid,
                              procamtallowed = '".addslashes($allowed_amount)."'
                     WHERE id='".addslashes($item)."'";
                $result = fdb_query ($query);
                if ($result)
                {
                        echo "<$STDFONT_B>Updated procedure Allowed Amount<$STDFONT_E><BR>\n";
                }
                else
                {
                        DIE ("$page_name :: DB error updating procedure");
                }

		// recalc the procedure's charge and change the type to 6 which means a PROCEDURE charge with Fee Adjustment.
                $query = "UPDATE payrec SET payrecamt = '".addslashes($allowed_amount)."', payrectype = '6'
                     WHERE payrecproc ='".addslashes($item)."' AND payrecpatient = '".addslashes($patient)."' AND payreccat = '".PROCEDURE."'";

                $result = fdb_query ($query);
                if ($result)
                {
                        echo "<$STDFONT_B>Updated Procedure Payrec<$STDFONT_E><BR>\n";
                }
                else
                {
                        DIE ("$page_name :: DB error updateing procedure payrec");
                }


    } // end allowed amount

    if ($payment_amount > 0) {
      $query = "INSERT INTO $db_name VALUES (
                '".addslashes($cur_date)."',
                '',
                '".addslashes($patient)."',
                '".fm_date_assemble("date_of_action")."',
                '".PAYMENT."',
                '".addslashes($item)."',
                '0',
                '',
                '',
                '".addslashes($voucher)."',
                '".addslashes($payment_amount)."',
                '',
                'unlocked',
                NULL
                )";
      $result = fdb_query ($query); 
      if ($result) echo "<$STDFONT_B>$Adding payment.<$STDFONT_E><BR> \n";
    } // end of payment amount check
    // calculate the amounts
    $total_deducts  = $total_payments = 0;
    $total_deducts  = (abs($withhold) + abs($deductable) + abs($adjustment));
    $total_payments = $payment_amount;
    if (($total_charges != 0) or ($total_payments != 0)) {
      $query = "UPDATE procrec
                SET procbalcurrent = 
                      procbalcurrent - '".addslashes(
                        ($total_deducts + $total_payments))."',
                    procamtpaid    =
                      procamtpaid + '".addslashes($total_payments)."'
                WHERE id='".addslashes($item)."'";
      $result = fdb_query ($query);
      if ($result) 
        echo "<$STDFONT_B>Updated procedure record.<$STDFONT_E><BR>\n";
    } // end of checking for any changes
    echo "</CENTER>\n";
    $show_submit = false;
    break; // end payment action

   case "transfer": // transfer action
    $query = "UPDATE payrec
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
    $query = "INSERT INTO payrec VALUES (
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
    $show_submit = false;
    break; // end of transfer action

   case "mistake": // mistake action
    $query = "DELETE FROM procrec 
              WHERE id='".addslashes($item)."'";
    $result = fdb_query ($query);
    $query = "DELETE FROM payrec 
              WHERE payrecproc='".addslashes($item)."'";
    $result = fdb_query ($query);
    echo "
     <CENTER>
      Item $item removed (with references).
     </CENTER>
    ";
    $show_submit = false;
    break; // end mistake action

   case "denialform": // denial form action
    echo "
     <CENTER>
     <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3>

      <TR>
       <TD COLSPAN=2 ALIGN=LEFT BGCOLOR=#aaaaaa>
       <$HEADERFONT_B>
        <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"denial\">
        Denial
       <$HEADERFONT_E>
      </TR>
      <TR>
       <TD ALIGN=RIGHT>
        <$STDFONT_B>Date of Action : <$STDFONT_E>
       </TD><TD ALIGN=LEFT>
    ".fm_date_entry ("date_of_action")."
       </TD>
      </TR>

      <TR>
       <TD ALIGN=RIGHT>
        <$STDFONT_B>Reason : <$STDFONT_E>
       </TD><TD ALIGN=LEFT>
        <SELECT NAME=\"denial_reason\">
         <OPTION VALUE=\"\">[Fill in]
     ";
     // generate list of past denial reasons to choose from
     $denial_query = "SELECT DISTINCT payrecdescrip FROM payrec
                      WHERE payreccat='3'
                      ORDER BY payrecdescrip";
     $denial_result = fdb_query ($denial_query);
     if ($denial_result and (fdb_num_rows($denial_result)>0)) {
       while ($denial_r = fdb_fetch_array ($denial_result)) {
        if (!empty ($denial_r[payrecdescrip]))
         echo "     <OPTION VALUE=\"".htmlentities($denial_r[payrecdescrip]).
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

      </TABLE>
      </CENTER>
     ";
    break;

   case "paymentform": // payments form
    echo "
     <CENTER>
     <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3>
      <TR>
       <TD COLSPAN=6 ALIGN=LEFT BGCOLOR=#aaaaaa>
        <$HEADERFONT_B>
         <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"payment\">
         Payment
        <$HEADERFONT_E>
       </TD>
      </TR>

      <TR>
       <TD COLSPAN=2 ALIGN=RIGHT>
        <$STDFONT_B>Date of Action : <$STDFONT_E>
       </TD><TD COLSPAN=4 ALIGN=LEFT>
    ".fm_date_entry ("date_of_action")."
       </TD>
      </TR>

      <TR>
       <TD ALIGN=RIGHT>
        <$STDFONT_B>Voucher<$STDFONT_E>
       </TD>
	<TD ALIGN=RIGHT>
        <$STDFONT_B>Withhold<$STDFONT_E>
       </TD>
	<TD ALIGN=RIGHT>&nbsp;
        <$STDFONT_B>Deductable<$STDFONT_E>
       </TD>
	<TD ALIGN=RIGHT>&nbsp;
        <$STDFONT_B>Adjustment<$STDFONT_E>
       </TD>
	<TD ALIGN=RIGHT>&nbsp;
        <$STDFONT_B>Allowed<$STDFONT_E>
       </TD>
	<TD ALIGN=RIGHT>&nbsp;
        <$STDFONT_B>Payment Amt<$STDFONT_E>
       </TD>
      </TR>

      <TR>
       <TD ALIGN=RIGHT>
        <INPUT TYPE=TEXT NAME=\"voucher\"
         SIZE=8 MAXLENGTH=8
         VALUE=\"\">
       </TD>
	<TD ALIGN=RIGHT>
        <INPUT TYPE=TEXT NAME=\"withhold\"
         SIZE=8 MAXLENGTH=8
         VALUE=\"0.00\">
       </TD>
	<TD ALIGN=RIGHT>&nbsp;
        <INPUT TYPE=TEXT NAME=\"deductable\"
         SIZE=8 MAXLENGTH=8
         VALUE=\"0.00\">
       </TD>
	<TD ALIGN=RIGHT>&nbsp;
        <INPUT TYPE=TEXT NAME=\"adjustment\"
         SIZE=8 MAXLENGTH=8
         VALUE=\"0.00\">
       </TD>
	<TD ALIGN=RIGHT>&nbsp;
        <INPUT TYPE=TEXT NAME=\"allowed_amount\"
         SIZE=8 MAXLENGTH=8
         VALUE=\"0.00\">
       </TD>
	<TD ALIGN=RIGHT>&nbsp;
        <INPUT TYPE=TEXT NAME=\"payment_amount\"
         SIZE=8 MAXLENGTH=8
         VALUE=\"0.00\">
       </TD>
      </TR>
      </TABLE>
      </CENTER>
    ";
    break; // end of payments form

   case "transferform":
    echo "
     <CENTER>
     <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3>
      <TR>
       <TD COLSPAN=2 ALIGN=LEFT BGCOLOR=#aaaaaa>
        <$HEADERFONT_B>
         <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"transfer\">
         Transfer <I>(to another source of payment)</I>
        <$HEADERFONT_E>
       </TD>
      </TR>
      <TR>
       <TD ALIGN=RIGHT>
        <$STDFONT_B>Date of Action : <$STDFONT_E>
       </TD><TD ALIGN=LEFT>
    ".fm_date_entry ("date_of_action")."
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

      </TABLE>
      </CENTER>
     ";
     break;

    case "mistakeform":
     echo "
     <CENTER>
     <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3>
      <TR>
       <TD COLSPAN=2 ALIGN=LEFT BGCOLOR=#aaaaaa>
        <$HEADERFONT_B>
         <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mistake\">
         Mistake <I>(deletes record permanently)</I>
        <$HEADERFONT_E>
       </TD>
      </TR>
      <TR>
       <TD><CENTER>Confirm deletion of record.</CENTER></TD>
      </TR>
      </TABLE>
      </CENTER>
     ";
     break; // end of mistakeform action

  } // end master action switch
  echo "
    <P>

    ".( ($show_submit) ?
    "<CENTER>
    <INPUT TYPE=SUBMIT VALUE=\"  Execute Action  \">
    </CENTER>" : "" )."

   </FORM>
  ";
 } // end checking for item
 } // end checking for action not refresh

 freemed_display_box_bottom ();

 freemed_close_db ();
 freemed_display_html_bottom ();
?>
