<?php
 // file: payment_record.php3
 // note: ledger/patient payment record functions
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

 $page_name   = "payment_record.php3";
 $record_name = "Patient Ledger Record";
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

 switch ($action) {

  case "addform":
   freemed_display_box_top ("$Add $record_name");
   echo "
     <P>
     <CENTER>
      <$STDFONT_B>$Patient : <$STDFONT_E>
      <A HREF=\"manage.php3?$_auth&id=$patient\"
      ><$STDFONT_B>".$this_patient->fullName(true)."<$STDFONT_E></A>
     </CENTER>
     <P>

     <TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 VALIGN=MIDDLE
      ALIGN=CENTER>

     <TR>
      <TD COLSPAN=2 ALIGN=CENTER>
       <$HEADERFONT_B>Step One: Select the Item &amp;
         Type/Category<$HEADERFONT_E>
      </TD>
     </TR>

     <FORM ACTION=\"$page_name\" METHOD=POST>
     <INPUT TYPE=HIDDEN NAME=\"_auth\"   VALUE=\"$_auth\">
     <INPUT TYPE=HIDDEN NAME=\"action\"  VALUE=\"addform1\">
     <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">

     <TR>
      <TD ALIGN=RIGHT>
       <$STDFONT_B>Procedure : <$STDFONT_E>
      </TD><TD ALIGN=LEFT>
       <SELECT NAME=\"payrecproc\">
        <OPTION VALUE=\"0\">$NONE_SELECTED
  ";
  // grab all procedures for patient (with non-zero balance)
  $procs = fdb_query ("SELECT * FROM procrec
                       WHERE ((procpatient='$patient')
                       AND (procbalcurrent>0))");
  if (($procs==0) or (fdb_num_rows ($procs)>0)) { // if there are results...
   while ($p_r = fdb_fetch_array ($procs)) {
     if (($procedure>0) and ($procedure==$p_r["id"]))
       { $this_selected = "SELECTED"; } else { $this_selected = ""; }
     echo "\n      <OPTION VALUE=\"".$p_r["id"].
                   "\" $this_selected>".$p_r["procdt"]." - ".$p_r["procdesc"].
              "(".freemed_get_link_field($p_r["proccpt"], "cpt", "cptnameint").
              ")"; 
   } // end while there are results
  } // end if there are results
  echo "
       </SELECT>
      </TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       <INPUT TYPE=RADIO NAME=\"payreccat\" VALUE=\"0\" CHECKED>
      </TD><TD ALIGN=LEFT>
        <$STDFONT_B>Payment<$STDFONT_E>
      </TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       <INPUT TYPE=RADIO NAME=\"payreccat\" VALUE=\"1\">
      </TD><TD ALIGN=LEFT>
        <$STDFONT_B>Adjustment<$STDFONT_E>
      </TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       <INPUT TYPE=RADIO NAME=\"payreccat\" VALUE=\"2\">
      </TD><TD ALIGN=LEFT>
        <$STDFONT_B>Refund<$STDFONT_E>
      </TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       <INPUT TYPE=RADIO NAME=\"payreccat\" VALUE=\"3\">
      </TD><TD ALIGN=LEFT>
        <$STDFONT_B>Denial<$STDFONT_E>
      </TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       <INPUT TYPE=RADIO NAME=\"payreccat\" VALUE=\"4\">
      </TD><TD ALIGN=LEFT>
       <$STDFONT_B>Rebill<$STDFONT_E>
      </TD>
     </TR>

     <TR>
      <TD COLSPAN=2 ALIGN=CENTER>
       <CENTER>
        <INPUT TYPE=SUBMIT VALUE=\"Continue\">
       </CENTER>
      </TD>
     </TR>

     </FORM>

     </TABLE>
   ";
   freemed_display_box_bottom ();
   break;

  case "addform1":
   // determine closest date if none is provided
   if (empty($payrecdt) and empty($payrecdt_y))
     $payrecdt = $cur_date; // by default, the date is now...

   // actual display
   freemed_display_box_top ("$Add $record_name", $_ref, $page_name);

   // if a patient is provided...
   if ($patient>0) {
     if (empty($payrecsource)) $payrecsource = 1; // set to patient payment
     echo "
      <CENTER>
       <$STDFONT_B>Patient : <$STDFONT_E>
       <A HREF=\"manage.php3?$_auth&id=$patient\"
        ><$STDFONT_B>".$this_patient->fullName(true)."<$STDFONT_E></A>
      </CENTER>
     ";  
   }

   // addform1 action, top of form/table
   echo "
     <P>
     <FORM ACTION=\"$page_name\" METHOD=POST>
      <INPUT TYPE=HIDDEN NAME=\"action\"     VALUE=\"addform2\">
      <INPUT TYPE=HIDDEN NAME=\"patient\"    VALUE=\"$patient\">
      <INPUT TYPE=HIDDEN NAME=\"payreccat\"  VALUE=\"$payreccat\">
      <INPUT TYPE=HIDDEN NAME=\"payrecproc\" VALUE=\"$payrecproc\">

     <TABLE WIDTH=100% BORDER=0 CELLSPACING=2 CELLPADDING=2
      VALIGN=MIDDLE ALIGN=CENTER>

   ";

   switch ($payreccat) {
    case PAYMENT: // payment (0)
    echo "
     <TR><TD COLSPAN=2>
     <$HEADERFONT_B>Step Two: Describe the Payment<$HEADERFONT_E>
     </TD></TR>

     <TR>
      <TD ALIGN=RIGHT><$STDFONT_B>Payment Source : <$STDFONT_E></TD>
      <TD><SELECT NAME=\"payrecsource\">
       <OPTION VALUE=\"0\" ".
        ( ($payrecsource==0) ? "SELECTED" : "" ).">Insurance Payment 
       <OPTION VALUE=\"1\" ".
        ( ($payrecsource==1) ? "SELECTED" : "" ).">Patient Payment
       <OPTION VALUE=\"2\" ".
        ( ($payrecsource==2) ? "SELECTED" : "" ).">Worker's Comp
      </SELECT></TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT><$STDFONT_B>Payment Type : <$STDFONT_E></TD>
      <TD><SELECT NAME=\"payrectype\">
       <OPTION VALUE=\"0\" ".
        ( ($payrectype==0) ? "SELECTED" : "" ).">cash
       <OPTION VALUE=\"1\" ".
        ( ($payrectype==1) ? "SELECTED" : "" ).">check
       <OPTION VALUE=\"2\" ".
        ( ($payrectype==2 or empty($payrectype))
            ? "SELECTED" : "" ).">money order
       <OPTION VALUE=\"3\" ".
        ( ($payrectype==3) ? "SELECTED" : "" ).">credit card
       <OPTION VALUE=\"4\" ".
        ( ($payrectype==4) ? "SELECTED" : "" ).">traveller's check
       <OPTION VALUE=\"5\" ".
        ( ($payrectype==5) ? "SELECTED" : "" ).">EFT
      </SELECT></TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT><$STDFONT_B>Date Received : <$STDFONT_E></TD>
      <TD>";
    fm_date_entry ("payrecdt");
    echo "
      </TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT><$STDFONT_B>Payment Amount : <$STDFONT_E></TD>
      <TD><INPUT TYPE=TEXT NAME=\"payrecamt\" SIZE=10 MAXLENGTH=15
       VALUE=\"$payrecamt\"></TD>
     </TR>
   ";
   break; // end of payment

   case ADJUSTMENT: // adjustment (1)
   echo "
     <TR>
      <TD ALIGN=CENTER COLSPAN=2>
       <$HEADERFONT_B>Step Two: Describe the Adjustment<$HEADERFONT_E> 
      </TD>
     </TR>

     <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Insurance Company : <$STDFONT_E></TD>
     <TD ALIGN=LEFT>
   ".
   freemed_display_insco ($payreclink)
   ."
     </TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       <$STDFONT_B>Date Received : <$STDFONT_E>
      </TD><TD ALIGN=LEFT>
    ";
    fm_date_entry ("payrecdt");
    echo "
      </TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       <$STDFONT_B>Adjustment Amount : <$STDFONT_E>
      </TD><TD ALIGN=LEFT>
       <INPUT TYPE=TEXT NAME=\"payrecamt\" SIZE=10 MAXLENGTH=9>
      </TD>
     </TR>
   ";
   break; // end of adjustment

   case REFUND: // refund (2)
   echo "
     <TR>
      <TD COLSPAN=2 ALIGN=CENTER>
       <$HEADERFONT_B>Step Two: Describe the Refund<$HEADERFONT_E>
      </TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       <$STDFONT_B>Date of Refund : <$STDFONT_E>
      </TD><TD ALIGN=LEFT>
   ";
   fm_date_entry ("payrecdt");
   echo "
      </TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       <$STDFONT_B>Refund Amount : <$STDFONT_E>
      </TD><TD ALIGN=LEFT>
       <INPUT TYPE=TEXT NAME=\"payrecamt\" SIZE=10
        MAXLENGTH=9 VALUE=\"$payrecamt\">
      </TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       <$STDFONT_B>Destination : <$STDFONT_E>
      </TD><TD ALIGN=LEFT>
       <SELECT NAME=\"payreclink\">
        <OPTION VALUE=\"0\" ".
         ( ($payreclink==0) ? "SELECTED" : "" ).">Apply to credit
        <OPTION VALUE=\"1\" ".
         ( ($payreclink==1) ? "SELECTED" : "" ).">Refund to patient
       </SELECT>
      </TD>
     </TR>
   ";
   break; // end of refund

   case DENIAL: // denial (3)
   echo "
     <TR>
      <TD COLSPAN=2 ALIGN=CENTER>
       <$HEADERFONT_B>Step Two: Describe the Denial<$HEADERFONT_E>
      </TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       <$STDFONT_B>Date of Denial : <$STDFONT_E>
      </TD><TD ALIGN=LEFT>
   ";
   fm_date_entry ("payrecdt");
   echo "
      </TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       <$STDFONT_B>Adjust to Zero? : <$STDFONT_E>
      </TD><TD ALIGN=LEFT>
       <SELECT NAME=\"payreclink\">
        <OPTION VALUE=\"0\" ".
           ( ($payreclink==0) ? "SELECTED" : "" ).">no
        <OPTION VALUE=\"1\" ".
           ( ($payreclink==1) ? "SELECTED" : "" ).">yes
       </SELECT>
      </TD>
     </TR>
   ";
   break; // end of denial

   case REBILL: // rebills (4)
   echo "
     <TR>
      <TD COLSPAN=2 ALIGN=CENTER>
       <$HEADERFONT_B>Step Two: Describe the Rebill<$HEADERFONT_E>
      </TD>
     </TR>
   ";
   break; // end of rebills

   default: // we shouldn't be here
     echo "D'OH!";
   break;
  } // end switch payreccat

  // addform1 action, end of table
  echo "
     </TABLE>

     <CENTER>
     <INPUT TYPE=SUBMIT VALUE=\" Continue \">
     <INPUT TYPE=RESET  VALUE=\" Clear \">
     </FORM>
     </CENTER>
     <P>
   ";
   freemed_display_box_bottom ();
   break;

  case "addform2":
   if (empty($payrecamt)) {
     freemed_display_box_top ("$Add $record_name");
     echo "
      <P>
      You must enter an amount!
      <P>
      <CENTER>
      <FORM ACTION=\"$page_name\" METHOD=POST>
       <INPUT TYPE=HIDDEN NAME=\"_auth\"        VALUE=\"$_auth\">
       <INPUT TYPE=HIDDEN NAME=\"action\"       VALUE=\"addform1\">
       <INPUT TYPE=HIDDEN NAME=\"patient\"      VALUE=\"$patient\">
       <INPUT TYPE=HIDDEN NAME=\"payrecproc\"   VALUE=\"$payrecproc\">
       <INPUT TYPE=HIDDEN NAME=\"payreccat\"    VALUE=\"$payreccat\">
       <INPUT TYPE=HIDDEN NAME=\"payrecsource\" VALUE=\"$payrecsource\">
       <INPUT TYPE=HIDDEN NAME=\"payrectype\"   VALUE=\"$payrectype\">
       <INPUT TYPE=HIDDEN NAME=\"payrecdt_y\"   VALUE=\"$payrecdt_y\">
       <INPUT TYPE=HIDDEN NAME=\"payrecdt_m\"   VALUE=\"$payrecdt_m\">
       <INPUT TYPE=HIDDEN NAME=\"payrecdt_d\"   VALUE=\"$payrecdt_d\">
       <INPUT TYPE=SUBMIT VALUE=\"  Try Again  \">
      </FORM>
      </CENTER>
     ";
     freemed_display_box_bottom ();
     freemed_display_html_bottom ();
     freemed_close_db ();
     DIE("");
   } // end checking for empty payrecamt
   freemed_display_box_top ("$Add $record_name", $_ref, $page_name);
   echo "
    <P>
    <CENTER>
     <$STDFONT_B>$Patient : <$STDFONT_E>
     <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>".$this_patient->fullName(true)."<$STDFONT_E></A>
    </CENTER>
    <P>
   
    <TABLE WIDTH=100% CELLSPACING=2 CELLPADDING=2 BORDER=0
     VALIGN=MIDDLE ALIGN=CENTER>

    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
    <INPUT TYPE=HIDDEN NAME=\"payrecproc\"   VALUE=\"$payrecproc\">
    <INPUT TYPE=HIDDEN NAME=\"payreccat\"    VALUE=\"$payreccat\">
    <INPUT TYPE=HIDDEN NAME=\"payrecsource\" VALUE=\"$payrecsource\">
    <INPUT TYPE=HIDDEN NAME=\"payrectype\"   VALUE=\"$payrectype\">
    <INPUT TYPE=HIDDEN NAME=\"payrecamt\"    VALUE=\"$payrecamt\">
    <INPUT TYPE=HIDDEN NAME=\"payrecdt_y\"   VALUE=\"$payrecdt_y\">
    <INPUT TYPE=HIDDEN NAME=\"payrecdt_m\"   VALUE=\"$payrecdt_m\">
    <INPUT TYPE=HIDDEN NAME=\"payrecdt_d\"   VALUE=\"$payrecdt_d\">
    <INPUT TYPE=HIDDEN NAME=\"patient\"      VALUE=\"$patient\">

   ";

   switch ($payreccat) {
    case "0": // payment (addform2)
    echo "
     <TR><TD COLSPAN=2>
     <$HEADERFONT_B>Step Three: Specify the Payer<$HEADERFONT_E>
     </TD></TR>
    ";
    switch ($payrecsource) {
      case "1":
       if ($patient>0) {
        echo "
          <TR>
          <TD ALIGN=RIGHT><$STDFONT_B>Patient : <$STDFONT_E></TD>
          <TD ALIGN=LEFT><I>".$this_patient->fullName()."</I>
           <INPUT TYPE=HIDDEN NAME=\"payreclink\" VALUE=\"$patient\">
          </TD></TR>
        ";
       } else {
        echo "
          <TR><TD COLSPAN=2><CENTER>NOT IMPLEMENTED YET!</CENTER>
          </TD></TR>
        ";
       }
       break;
      case "0": default:
       echo "
        <TR>
        <TD ALIGN=RIGHT><$STDFONT_B>Insurance Company : <$STDFONT_E></TD>
        <TD ALIGN=LEFT><SELECT NAME=\"payreclink\">
       ".$this_patient->insuranceSelection()."
        </SELECT></TD>
        </TR>
       ";
       break;
    } // payment source switch end
 
    echo "
     <TR><TD COLSPAN=2>
     <$HEADERFONT_B>Step Four: Payment Information<$HEADERFONT_E>
     </TD></TR>
    ";

    switch ($payrectype) {
     case "1": // check
      echo "
       <TR>
       <TD ALIGN=RIGHT><$STDFONT_B>Check Number : <$STDFONT_E></TD>
       <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"payrecnum\" SIZE=20
        VALUE=\"$payrecnum\"></TD>
       </TR>
      "; 
      break;
     case "2": // money order
      echo "<B>NOT IMPLEMENTED YET!</B><BR>\n";
      break;
     case "3": // credit card
      echo "
       <TR>
       <TD ALIGN=RIGHT><$STDFONT_B>Credit Card Number : <$STDFONT_E></TD>
       <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"payrecnum_1\" SIZE=17
        MAXLENGTH=16></TD>
       </TR>
 
       <TR>
       <TD ALIGN=RIGHT><$STDFONT_B>Expiration Date : <$STDFONT_E></TD>
       <TD ALIGN=LEFT>";
      fm_number_select ("payrecnum_e1", 1, 12, 1, true);
      echo "\n <B>/</B>&nbsp; \n";
      fm_number_select ("payrecnum_e2", (date("Y")-2), (date("Y")+10), 1);
      echo "</TD></TR>\n";
      break;
     case "4": // traveller's check
      echo "
       <TR>
       <TD ALIGN=RIGHT><$STDFONT_B>Cheque Number : <$STDFONT_E></TD>
       <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"payrecnum\" SIZE=21
        MAXLENGTH=20></TD>
       </TR>
      ";
      break; 
     case "5": // EFT
      echo "<B>NOT IMPLEMENTED YET!</B><BR>\n";
      break;
     case "0": default: // if nothing... (or cash)
      break;
    } // end of type switch

    echo "
     <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Description : <$STDFONT_E></TD>
     <TD><INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30
       VALUE=\"$payrecdescrip\"></TD>
     </TR>
    ";
    break; // end payment (addform2)

    case ADJUSTMENT: // adjustment (addform2) 1
    echo "
     <TR><TD COLSPAN=2>
     <$HEADERFONT_B>Step Three: Adjustment Information<$HEADERFONT_E>
     </TD></TR>
     <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Description : <$STDFONT_E></TD>
     <TD><INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30
       VALUE=\"$payrecdescrip\"></TD>
     </TR>
    ";
     break; // end adjustment (addform2)

    case REFUND: // refund (addform2) 2
    echo "
     <TR><TD COLSPAN=2>
     <$HEADERFONT_B>Step Three:: Refund Information<$HEADERFONT_E>
     </TD></TR>
     <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Description : <$STDFONT_E></TD>
     <TD><INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30
       VALUE=\"$payrecdescrip\"></TD>
     </TR>
    ";
     break; // end refund (addform2)

    case DENIAL: // denial (addform2) 3
    echo "
     <TR><TD COLSPAN=2>
     <$HEADERFONT_B>Step Three: Denial Information<$HEADERFONT_E>
     </TD></TR>
     <INPUT TYPE=HIDDEN NAME=\"payreclink\" 
      VALUE=\"".fm_prep($payreclink)."\">
     <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Description : <$STDFONT_E></TD>
     <TD><INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30
       VALUE=\"$payrecdescrip\"></TD>
     </TR>
    ";
     break; // end denial (addform2)

    case REBILL: // rebill (addform2) 4
    echo "
     <TR><TD COLSPAN=2>
     <$HEADERFONT_B>Step Three: Rebill Information<$HEADERFONT_E>
     </TD></TR>
     <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Description : <$STDFONT_E></TD>
     <TD><INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30
       VALUE=\"$payrecdescrip\"></TD>
     </TR>
    ";
     break; // end rebill (addform2)
  } // end switch for category (addform2)

  echo " 
    </TABLE>
    <CENTER>
     <INPUT TYPE=SUBMIT VALUE=\" $Add \">
     <INPUT TYPE=RESET  VALUE=\" $Clear \">
    </CENTER>
    </FORM>
    <P>
   ";
   freemed_display_box_bottom ();
   break;

  case "add": // actual add is done here
   freemed_display_box_top ("$Adding $record_name");

   switch ($payreccat) { // begin category case (add)
     case PAYMENT: // payment category (add) 0
     // first clean payrecnum vars
     $payrecnum    = eregi_replace (":", "", $payrecnum   );
     $payrecnum_1  = eregi_replace (":", "", $payrecnum_1 );
     $payrecnum_e1 = eregi_replace (":", "", $payrecnum_e1);
     $payrecnum_e2 = eregi_replace (":", "", $payrecnum_e2);
 
     // then decide what to do with them
     switch ($payrectype) {
      case "0": // cash
       break;
      case "1": // check
       $payrecnum = chop($payrecnum);
       break;
      case "2": // money order
       echo "<B>NOT IMPLEMENTED YET!!!</B><BR>\n";
       break;
      case "3": // credit card
       $payrecnum = chop($payrecnum_1). ":".
                    chop($payrecnum_e1).":".
                    chop($payrecnum_e2);
       break;
      case "4": // traveller's cheque
       $payrecnum = chop($payrecnum);
       break;
      case "5": // EFT
       break;
      default: // if somebody messed up...
       echo "$ERROR!!! payrectype not present<BR>\n";
       $payrecnum = ""; // kill!!!
       DIE("");
       break;
     } // end switch payrectype
     break; // end payment category (add)

     case ADJUSTMENT: // adjustment category (add) 1
      break; // end adjustment category (add)

     case REFUND: // refund category (add) 2
      break; // end refund category (add)

     case DENIAL: // denial category (add) 3
      $amount_left = freemed_get_link_field ($payrecproc, "procrec",
                                             "procbalcurrent");
      $payrecamt   = -(abs($amount_left));
      break; // end denial category (add)

     case REBILL: // rebill category (add) 4
      break; // end rebill category (add)
   } // end category switch (add)

   echo "<$STDFONT_B>$Adding ... <$STDFONT_E>\n";
   $query = "INSERT INTO $db_name VALUES (
     '$cur_date',
     '',
     '".addslashes($patient).      "',
     '".addslashes(fm_date_assemble("payrecdt"))."', 
     '".addslashes($payreccat).    "',
     '".addslashes($payrecproc).   "',
     '".addslashes($payrecsource). "',
     '".addslashes($payreclink).   "',
     '".addslashes($payrectype).   "',
     '".addslashes($payrecnum).    "',
     '".addslashes($payrecamt).    "',
     '".addslashes($payrecdescrip)."',
     'unlocked',
     NULL )";
   if ($debug) echo "<BR>(query = \"$query\")<BR>\n";
   $result = fdb_query($query);
   if ($result) { echo "$Done."; }
    else        { echo "$ERROR"; }
   echo "  <BR><$STDFONT_B>Modifying procedural charges... <$STDFONT_E>\n";
   switch ($payreccat) {
     case ADJUSTMENT: // adjustment category (add) 1
      $query = "UPDATE procrec SET
                procbalcurrent = procbalcurrent - $payrecamt
                WHERE id='$payrecproc'";
      break; // end adjustment category (add)

     case REFUND: // refund category (add) 2
      $query = "UPDATE procrec SET
                procamtpaid    = procamtpaid    + $payrecamt
                WHERE id='$payrecproc'";
      break; // end refund category (add)

     case DENIAL: // denial category (add) 3
      if ($payreclink==1) {
        $query = "UPDATE procrec SET
                  procbalcurrent = '0'
                  WHERE id='$payrecproc'";
      } else { // if no adjust
        $query = "";
      } // end checking for adjust to zero
      break; // end denial category (add)

     case REBILL: // rebill category (add) 4
      $query = "";
      break; // end rebill category (add)

     case PAYMENT: // payment category (add) 0
     default:  // default is payment
      $query = "UPDATE procrec SET
                procbalcurrent = procbalcurrent - $payrecamt,
                procamtpaid    = procamtpaid    + $payrecamt
                WHERE id='$payrecproc'";
      break;
   } // end category switch (add)
   if ($debug) echo "<BR>(query = \"$query\")<BR>\n";
   if (!empty($query)) {
     $result = fdb_query($query);
     if ($result) { echo "$Done."; }
      else        { echo "$ERROR"; }
   } else { // if there is no query, let the user know we did nothing
     echo "unnecessary";
   } // end checking for null query
   echo "
    <P>
    <CENTER>
     <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A> <B>|</B>
     <A HREF=\"payment_record.php3?$_auth&patient=$patient\"
     ><$STDFONT_B>View Patient Ledger<$STDFONT_E></A>
    </CENTER>
    <P>
   ";
   freemed_display_box_bottom ();
   break;

  case "del":
   if ($this_user->getLevel() < $delete_level)
    die ("$page_name :: You don't have permission to do this");
   freemed_display_box_top ("$Deleting $record_name");
   echo "
    <P>
    <$STDFONT_B>$Deleting ... <$STDFONT_E>
   ";
   $query = "DELETE FROM $db_name WHERE id='$id'";
   $result = fdb_query ($query);
   if ($result) { echo "$Done."; }
    else        { echo "$ERROR"; }
   echo "
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth&patient=$patient\"
     ><$STDFONT_B>Return to $record_name Menu<$STDFONT_E></A> <B>|</B>
     <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
    </CENTER>
    <P>
   ";
   freemed_display_box_bottom ();
   break;

  default:
   // default action
   freemed_display_box_top ("$record_name");

   echo "
    <P>
    <CENTER>
     <$STDFONT_B><B>$Patient</B><$STDFONT_E>:
     <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>".$this_patient->fullName(true)."<$STDFONT_E></A>
    </CENTER>
    <P>
   ";

   $pay_query  = "SELECT * FROM payrec
                  WHERE payrecpatient='$patient'
                  ORDER BY payrecdt";
   $pay_result = fdb_query ($pay_query);
   
   if (($pay_result<1) or (fdb_num_rows($pay_result)<1)) {
     echo "
      <CENTER>
       <P>
       <B><$STDFONT_B>
        There are no records for this patient.
       </B><$STDFONT_E>
       <P>
       <A HREF=\"$page_name?$_auth&action=addform&patient=$patient\"
        ><$STDFONT_B>$Add $record_name<$STDFONT_E></A> <B>|</B>
       <A HREF=\"manage.php3?$_auth&id=$patient\"
        ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
       <P>
      </CENTER>
     ";
     freemed_display_box_bottom ();
     freemed_close_db ();
     freemed_display_html_bottom ();
     DIE(""); // kill!!
   } // end/if there are no results          

   // if there is something, show it...
   echo "
    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
    <TR>
     <TD><B>Date</B></TD>
     <TD><B>Description</B></TD>
     <TD><B>Type</B></TD>
     <TD><B>Charges</B></TD>
     <TD><B>Payments</B></TD>
     <TD><B>Action</B></TD>
    </TR>
   ";

   $_alternate = freemed_bar_alternate_color ();

   $total_payments = 0.00; // initially no payments
   $total_charges  = 0.00; // initially no charges

   while ($r = fdb_fetch_array ($chg_result)) {
     $procdate        = fm_date_print ($r["procdt"]);
     $proccomment     = fm_prep ($r["proccomment"]);
     $procbalorig     = $r["procbalorig"];
     $id              = $r["id"];
     $_alternate      = freemed_bar_alternate_color ($_alternate);
     $total_charges  += $procbalorig;     
     echo "
      <TR BGCOLOR=$_alternate>
       <TD>$procdate</TD>
       <TD><I>$proccomment</I></TD>
       <TD>charge</TD>
       <TD ALIGN=RIGHT>
        <FONT COLOR=#ff0000>
         <TT><B>".bcadd($procbalorig, 0, 2)."</B></TT>
        </FONT>
       </TD> 
       <TD ALIGN=RIGHT>
        <FONT COLOR=$paycolor>
         <TT>&nbsp;</TT>
        </FONT>
       </TD>
       <TD>
     ";
     if ($this_user->getLevel() > $database_level)
      echo "
       <A HREF=\"procedure.php3?$_auth&id=$id&patient=$patient&action=view\"
       ><$STDFONT_B>VIEW<$STDFONT_E></A>
      "; 
     echo "\n   &nbsp;</TD></TR>";
   } // wend?

   while ($r = fdb_fetch_array ($pay_result)) {
     $payrecdate      = fm_date_print ($r["payrecdt"]);
     $payrecdescrip   = fm_prep ($r["payrecdescrip"]);
     $payrecamt       = fm_prep ($r["payrecamt"]);
     switch ($r["payreccat"]) { // category switch
      case REFUND: // refunds 2
      case PROCEDURE: // charges 5
       $pay_color       = "#000000";
       $payment         = "&nbsp;";
       $charge          = bcadd($payrecamt, 0, 2);
       $total_charges  += $payrecamt; break;
      case REBILL: // rebills 4
       $payment         = "&nbsp;";
       $charge          = "&nbsp;";
       break;
      case DENIAL: // denials 3
       $pay_color       = "#000000";
       $charge          = bcadd($payrecamt, 0, 2);
       $payment         = "&nbsp;";
       $total_charges  += $charge;
       break;
      case TRANSFER: // transfer 6
      case WITHHOLD: // withhold 7
      case DEDUCTABLE: // deductable 8
       $pay_color       = "#000000";
       $charge          = bcadd(-$payrecamt, 0, 2);
       $payment         = "&nbsp;";
       $total_charges  += $charge;
       break;
      case ADJUSTMENT: // adjustments 1
      case PAYMENT: default: // default is payments 0
       $pay_color       = "#ff0000";
       $payment         = bcadd($payrecamt, 0, 2);
       $charge          = "&nbsp;";
       $total_payments += $payrecamt; break;
     } // end of category switch (for totals)
     switch ($r["payreccat"]) {
      case ADJUSTMENT: // adjustments 1
       $this_type = "adjust";
       break;
      case REFUND: // refunds 2
       $this_type = "refund";
       break;
      case DENIAL: // denial 3
       $this_type = "denial";
       break;
      case REBILL: // rebill 4
       $this_type = "rebill";
       break;
      case PROCEDURE: // charge 5
       $this_type = "charge";
       break;
      case TRANSFER: // transfer 6
       $this_type = "transfer";
       break;
      case WITHHOLD: // withhold 7
       $this_type = "withhold";
       break;
      case DEDUCTABLE: // deductable 8
       $this_type = "deductable";
       break;
      case PAYMENT: // payment 0
      default:  // default is payment
       $this_type = "payment";
       break;
     } // end of categry switch (name)
     $id              = $r["id"];
     $_alternate      = freemed_bar_alternate_color ($_alternate);
     if (empty($payrecdescrip)) $payrecdescrip="NO DESCRIPTION";
     echo "
      <TR BGCOLOR=$_alternate>
       <TD>$payrecdate</TD>
       <TD><I>$payrecdescrip</I></TD>
       <TD>$this_type</TD>
       <TD ALIGN=RIGHT>
        <FONT COLOR=#ff0000\">
         <TT><B>".$charge."</B></TT>
        </FONT>
       </TD> 
       <TD ALIGN=RIGHT>
        <FONT COLOR=#000000>
         <TT><B>".$payment."</B></TT>
        </FONT>
       </TD>
       <TD>
     ";

     if (($this_user->getLevel() > $delete_level) and
         ($r[payreclock] != "locked"))
      echo "
       <A HREF=\"$page_name?$_auth&id=$id&patient=$patient&action=del\"
       ><$STDFONT_B>$lang_DEL<$STDFONT_E></A>
      "; 

     echo "\n   &nbsp;</TD></TR>";
   } // wend?

   // calculate patient ledger total
   $patient_total = $total_payments - $total_charges;
   $patient_total = bcadd ($patient_total, 0, 2);
   if ($patient_total<0) {
     $pat_total = "<FONT COLOR=#000000>".
      bcadd (-$patient_total, 0, 2)."</FONT>";
   } else {
     $pat_total = "-<FONT COLOR=#ff0000>".
      bcadd (-$patient_total, 0, 2)."</FONT>";
   } // end of creating total string/color

   // display the total payments
   $_alternate = freemed_bar_alternate_color ($_alternate);
   echo "
    <TR BGCOLOR=$_alternate>
     <TD><B><$STDFONT_B SIZE=-1>TOTAL<$STDFONT_E></B></TD>
     <TD>&nbsp;</TD>
     <TD>&nbsp;</TD>
     <TD ALIGN=RIGHT>
      <FONT COLOR=#ff0000><TT>".bcadd($total_charges,0,2)."</TT></FONT>
     </TD>
     <TD ALIGN=RIGHT>
      <TT>".bcadd($total_payments,0,2)."</TT>
     </TD>
     <TD ALIGN=RIGHT>
      <B><TT>$pat_total</TT></B>
    </TD>
    </TR>
   ";

   echo "\n  </TABLE>\n"; // end the table

   echo "
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth&action=addform&patient=$patient\"
     ><$STDFONT_B>$Add $record_name<$STDFONT_E></A> <B>|</B>
     <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
    </CENTER>
    <P>
   "; 

   freemed_display_box_bottom ();
   break;

 } // master action switch

 freemed_close_db ();
 freemed_display_html_bottom ();
?>
