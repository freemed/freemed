<?php
 // $Id$
 // note: ledger/patient payment record functions
 // lic : GPL, v2

 $page_name   = "payment_record.php";
 $record_name = "Patient Ledger Record";
 $db_name     = "payrec";

 include ("lib/freemed.php");
 include ("lib/API.php");

 freemed_open_db ($LoginCookie);
 $this_user = new User ($LoginCookie);

 freemed_display_html_top ();
 freemed_display_banner ();

 // create patient object
 if ($patient>0) { $this_patient = new Patient ($patient); }
  else           { DIE("NO PATIENT PROVIDED!");            }

 switch ($action) {

  case "addform": case "add":
   // ************** PREP STUFF ****************

   // grab all procedures for patient (with non-zero balance)
   $procs = $sql->query ("SELECT * FROM procrec
                       WHERE ((procpatient='$patient')
                       AND (procbalcurrent>0))");
   if (($procs==0) or ($sql->num_rows ($procs)>0)) { // if there are results...
    while ($p_r = $sql->fetch_array ($procs)) {
      if (($procedure>0) and ($procedure==$p_r["id"]))
        { $this_selected = "SELECTED"; } else { $this_selected = ""; }
      $procedures_to_display .= "\n      <OPTION VALUE=\"".$p_r["id"].
                    "\" $this_selected>".$p_r["procdt"]." - ".$p_r["procdesc"].
               "(".freemed_get_link_field($p_r["proccpt"], "cpt", "cptnameint").
               ")"; 
    } // end while there are results
   } // end if there are results

   // *************** DISPLAY TOP **************
   //freemed_display_box_top (_("Add")." "._($record_name));
   //echo freemed_patient_box ($this_patient)."<P>\n";

 
   // **************** FORM THE WIZARD ***************
   $wizard = new wizard (array("action", "patient", "_auth"));

   $wizard->add_page (
     "Step One: Select the Item & Type/Category",
     array (),
     form_table ( array (
       _("Procedure") =>
       "<SELECT NAME=\"payrecproc\">
        <OPTION VALUE=\"0\">"._("NONE SELECTED")."\n".
        $procedures_to_display."  </SELECT>\n"
     ) ).
     "<TABLE ALIGN=CENTER BORDER=0 CELLSPACING=0 CELLPADDING=2>
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
    </TABLE></CENTER>"
   );

   // ************** SECOND STEP PREP ****************
   // determine closest date if none is provided
   if (empty($payrecdt) and empty($payrecdt_y))
     $payrecdt = $cur_date; // by default, the date is now...

   // if a patient is provided...
   if ($patient>0) {
     if (empty($payrecsource)) $payrecsource = 1; // set to patient payment
   }

   // ************* ADD PAGE FOR STEP TWO *************

   switch ($payreccat) {
    case PAYMENT: // payment (0)
    $wizard->add_page (
      "Step Two: Describe the Payment",
      array ("payrecsource", "payrectype", "payrecdt_m", "payrecdt_y",
        "payrecdt_d", "payrecamt"),
      form_table ( array (
        "Payment Source" =>
          "<SELECT NAME=\"payrecsource\">
         <OPTION VALUE=\"0\" ".
        ( ($payrecsource==0) ? "SELECTED" : "" ).">Insurance Payment 
       <OPTION VALUE=\"1\" ".
        ( ($payrecsource==1) ? "SELECTED" : "" ).">Patient Payment
       <OPTION VALUE=\"2\" ".
        ( ($payrecsource==2) ? "SELECTED" : "" ).">Worker's Comp
      </SELECT>",

      "Payment Type" =>
        "<SELECT NAME=\"payrectype\">
       <OPTION VALUE=\"0\" ".
        ( ($payrectype==0) ? "SELECTED" : "" ).">cash
       <OPTION VALUE=\"1\" ".
        ( ($payrectype==1) ? "SELECTED" : "" ).">check
       <OPTION VALUE=\"2\" ".
        ( ($payrectype==2) ? "SELECTED" : "" ).">money order
       <OPTION VALUE=\"3\" ".
        ( ($payrectype==3) ? "SELECTED" : "" ).">credit card
       <OPTION VALUE=\"4\" ".
        ( ($payrectype==4) ? "SELECTED" : "" ).">traveller's check
       <OPTION VALUE=\"5\" ".
        ( ($payrectype==5) ? "SELECTED" : "" ).">EFT
      </SELECT>",

      "Date Received" =>
        fm_date_entry ("payrecdt"),

      "Payment Amount" =>
        "<INPUT TYPE=TEXT NAME=\"payrecamt\" SIZE=10 MAXLENGTH=15 ".
        "VALUE=\"".prepare($payrecamt)."\">\n"
    ) )
   );

   // second page of payments
   $second_page_array = "";

    switch ($payrecsource) {
      case "1":
       if ($patient>0) {
        $second_page_array["Patient"] =
          "<TD ALIGN=LEFT><I>".$this_patient->fullName()."</I>
           <INPUT TYPE=HIDDEN NAME=\"payreclink\" ".
           "VALUE=\"".prepare($patient)."\">\n";
       } else {
        echo "
          <TR><TD COLSPAN=2><CENTER>NOT IMPLEMENTED YET!</CENTER>
          </TD></TR>
        ";
       }
       break;
      case "0": default:
        $second_page_array["Insurance Company"] =
        "<SELECT NAME=\"payreclink\">".
        $this_patient->insuranceSelection().
        "</SELECT>\n";
       break;
    } // payment source switch end
 
    switch ($payrectype) {
     case "1": // check
      $second_page_array["Check Number"] =
       "<INPUT TYPE=TEXT NAME=\"payrecnum\" SIZE=20 ".
       "VALUE=\"".prepare($payrecnum)."\">\n";
      break;
     case "2": // money order
      $second_page_array[] = "<B>NOT IMPLEMENTED YET!</B><BR>\n";
      break;
     case "3": // credit card
      $second_page_array["Credit Card Number"] =
       "<INPUT TYPE=TEXT NAME=\"payrecnum_1\" SIZE=17 ".
       "MAXLENGTH=16 VALUE=\"".prepare($payrecnum_1)."\">\n";
 
      $second_page_array["Expiration Date"] =
        number_select ("payrecnum_e1", 1, 12, 1, true).
        "\n <B>/</B>&nbsp; \n".
        number_select ("payrecnum_e2", (date("Y")-2), (date("Y")+10), 1);
      break;
     case "4": // traveller's check
      $second_page_array["Cheque Number"] =
        "<INPUT TYPE=TEXT NAME=\"payrecnum\" SIZE=21 ".
        "MAXLENGTH=20 VALUE=\"".prepare($payrecnum)."\">\n";
      break; 
     case "5": // EFT
      $second_page_array[] = "<B>NOT IMPLEMENTED YET!</B><BR>\n";
      break;
     case "0": default: // if nothing... (or cash)
      break;
    } // end of type switch

   $second_page_array[_("Description")] =
     "<INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30 ".
     "VALUE=\"".prepare($payrecdescrip)."\">\n";

   $wizard->add_page(
     "Step Three: Specify the Payer",
     array ("payreclink", "payrecdescrip", "payrecnum",
            "payrecnum_e1", "payrecnum_e2"),
     form_table ( $second_page_array )
   );

   break; // end of payment

   case ADJUSTMENT: // adjustment (1)
   $wizard->add_page (
     "Step Two: Describe the Adjustment",
     array ("payreclink", "payrecdt", "payrecamt"),
     form_table ( array (
       "Insurance Company" =>
         freemed_display_selectbox (
           $sql->query ("SELECT insconame,inscocity,inscostate,id FROM insco
           ORDER BY insconame,inscostate,inscocity"),
           "#insconame# (#inscocity#, #inscostate#)", "payreclink" 
         ),

       "Date Received" =>
         fm_date_entry ("payrecdt"),

       "Adjustment Amount" =>
       "<INPUT TYPE=TEXT NAME=\"payrecamt\" SIZE=10 MAXLENGTH=9 ".
       "VALUE=\"".prepare($payrecamt)."\">\n"
     ) )
   );

   $wizard->add_page(
     "Step Three: Adjustment Information",
     array ("payrecdescrip"),
     form_table ( array (
       _("Description") =>
         "<INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30 ".
         "VALUE=\"".prepare($payrecdescrip)."\">\n"
     ) )
   );
   break; // end of adjustment

   case REFUND: // refund (2)
   $wizard->add_page (
     "Step Two: Describe the Refund",
     array ("payrecdt_y", "payrecdt_m", "payrecdt_d",
            "payrecamt", "payreclink"),
     form_table ( array (
       "Date of Refund" =>
         fm_date_entry ("payrecdt"),

       "Refund Amount" =>
         "<INPUT TYPE=TEXT NAME=\"payrecamt\" SIZE=10 ".
         "MAXLENGTH=9 VALUE=\"".prepare($payrecamt)."\">\n",

       "Destination" =>
         "<SELECT NAME=\"payreclink\">
        <OPTION VALUE=\"0\" ".
         ( ($payreclink==0) ? "SELECTED" : "" ).">Apply to credit
        <OPTION VALUE=\"1\" ".
         ( ($payreclink==1) ? "SELECTED" : "" ).">Refund to patient
       </SELECT>\n"
     ) )
   );
   $wizard->add_page(
     "Step Three: Refund Information",
     array ("payrecdescrip"),
     form_table ( array (
       _("Description") =>
       "<INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30 ".
       "VALUE=\"".prepare($payrecdescrip)."\">\n"
     ) )
   );
   break; // end of refund

   case DENIAL: // denial (3)
   $wizard->add_page (
     "Step Two: Describe the Denial",
     array ("payrecdt_y", "payrecdt_m", "payrecdt_d", "payreclink"), 
     form_table ( array (
       "Date of Denial" =>
         fm_date_entry ("payrecdt"),

       "Adjust to Zero?" =>
         "<SELECT NAME=\"payreclink\">
        <OPTION VALUE=\"0\" ".
           ( ($payreclink==0) ? "SELECTED" : "" ).">no
        <OPTION VALUE=\"1\" ".
           ( ($payreclink==1) ? "SELECTED" : "" ).">yes
       </SELECT>\n"
     ) )
   );

   $wizard->add_page(
     "Step Three: Denial Information",
     array("payrecdescrip"),
     form_table ( array (
        _("Description") =>
          "<INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30 ".
          "VALUE=\"".prepare($payrecdescrip)."\">\n"
     ) )
   );
   break; // end of denial

   case REBILL: // rebills (4)
   // no page for this one
   //  "Step Two: Describe the Rebill",
    case REBILL: // rebill (addform2) 4
   $wizard->add_page(
     "Step Two: Rebill Information",
     array ("payrecdescrip"),
     form_table ( array (
       _("Description") =>
         "<INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30 ".
         "VALUE=\"".prepare($payrecdescrip)."\">\n"
     ) )
   );
   break; // end of rebills

   default: // we shouldn't be here
     // do nothing -- we haven't selected payments yet
   break;
  } // end switch payreccat

   // we should figure out a better way to do this...
   if (0 == 1) {
     freemed_display_box_top (_("Adding")." "._($record_name));
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

  // check for book display, etc
  if (!$wizard->is_done() and !$wizard->is_cancelled()) {
    // if not done or cancelled, display the wizard
    freemed_display_box_top (_("Add")." "._($record_name));
    if ($patient>0) echo freemed_patient_box ($this_patient);
    echo "<CENTER>".$wizard->display()."</CENTER>\n";
    freemed_display_box_bottom ();
  } elseif ($wizard->is_done()) {
    freemed_display_box_top (_("Adding")." "._($record_name));
    if ($patient>0) echo freemed_patient_box ($this_patient);
    echo "<CENTER>\n";
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

    echo "<$STDFONT_B>"._("Adding")." ... <$STDFONT_E>\n";
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
    $result = $sql->query($query);
    if ($result) { echo _("done")."."; }
     else        { echo _("ERROR");    }
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
                 WHERE id='".addslashes($payrecproc)."'";
       break;
    } // end category switch (add)
    if ($debug) echo "<BR>(query = \"$query\")<BR>\n";
    if (!empty($query)) {
     $result = $sql->query($query);
      if ($result) { echo _("done")."."; }
       else        { echo -("ERROR");    }
    } else { // if there is no query, let the user know we did nothing
      echo "unnecessary";
    } // end checking for null query
    echo "
     </CENTER>
     <P>
     <CENTER>
      <A HREF=\"manage.php?$_auth&id=$patient\"
      ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A> <B>|</B>
      <A HREF=\"payment_record.php?$_auth&patient=$patient\"
      ><$STDFONT_B>View Patient Ledger<$STDFONT_E></A>
     </CENTER>
     <P>
    ";
    freemed_display_box_bottom ();
  } else {
    // if the wizard was cancelled
    echo "CANCELLED STUB<BR>\n";
  } // end of seeing what to do with the wizard
  break; // end of adding

  case "del":
   if ($this_user->getLevel() < $delete_level)
    die ("$page_name :: You don't have permission to do this");
   freemed_display_box_top (_("Deleting")." "._($record_name));
   echo "
    <P><CENTER>
    <$STDFONT_B>"._("Deleting")." ... <$STDFONT_E>\n";
   $query = "DELETE FROM $db_name WHERE id='".addslashes($id)."'";
   $result = $sql->query ($query);
   if ($result) { echo _("done")."."; }
    else        { echo _("ERROR");    }
   echo "
    </CENTER>
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth&patient=$patient\"
     ><$STDFONT_B>"._("back")."<$STDFONT_E></A> <B>|</B>
     <A HREF=\"manage.php?$_auth&id=$patient\"
     ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A>
    </CENTER>
    <P>
   ";
   freemed_display_box_bottom ();
   break;

  default:
   // default action
   freemed_display_box_top (_($record_name));

   echo freemed_patient_box ($this_patient)."
    <P>
   ";
   if (isset($byproc))
   {
       $pay_query  = "SELECT * FROM payrec
                  WHERE payrecpatient='$patient' AND payrecproc='$byproc'
                  ORDER BY payrecdt";
   }
   else
   {
       $pay_query  = "SELECT * FROM payrec
                  WHERE payrecpatient='$patient'
                  ORDER BY payrecdt";
   }
   $pay_result = $sql->query ($pay_query);
   
   if (!$sql->results($pay_result)) {
     echo "
      <CENTER>
       <P>
       <B><$STDFONT_B>
        There are no records for this patient.
       </B><$STDFONT_E>
       <P>
       <A HREF=\"$page_name?$_auth&action=addform&patient=$patient\"
        ><$STDFONT_B>"._("Add")." "._($record_name)."<$STDFONT_E></A> <B>|</B>
       <A HREF=\"manage.php?$_auth&id=$patient\"
        ><$STDFONT_B>"._("Manage_Patient")."<$STDFONT_E></A>
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
     <TD><B>"._("Date")."</B></TD>
     <TD><B>"._("Description")."</B></TD>
     <TD><B>Type</B></TD>
     <TD ALIGN=RIGHT><B>Charges</B></TD>
     <TD ALIGN=RIGHT><B>Payments</B></TD>
     <TD ALIGN=RIGHT><B>"._("Action")."</B></TD>
    </TR>
   ";

   $_alternate = freemed_bar_alternate_color ();

   $total_payments = 0.00; // initially no payments
   $total_charges  = 0.00; // initially no charges

   while ($r = $sql->fetch_array ($chg_result)) {
     $procdate        = fm_date_print ($r["procdt"]);
     $proccomment     = prepare ($r["proccomment"]);
     $procbalorig     = $r["procbalorig"];
     $id              = $r["id"];
     $total_charges  += $procbalorig;     
     echo "
      <TR BGCOLOR=\"".($_alternate=freemed_bar_alternate_color($_alternate))."\">
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
       <A HREF=\"procedure.php?$_auth&id=$id&patient=$patient&action=view\"
       ><$STDFONT_B>"._("VIEW")."<$STDFONT_E></A>
      "; 
     echo "\n   &nbsp;</TD></TR>";
   } // wend?

   while ($r = $sql->fetch_array ($pay_result)) {
     $payrecdate      = fm_date_print ($r["payrecdt"]);
     $payrecdescrip   = prepare ($r["payrecdescrip"]);
     $payrecamt       = prepare ($r["payrecamt"]);
     $payrectype      = $r["payrectype"];
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
       if ($payrectype == "6")
           $this_type = "Fee Adjust";
       else
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
     if (empty($payrecdescrip)) $payrecdescrip="NO DESCRIPTION";
     echo "
      <TR BGCOLOR=\"".
       ($_alternate = freemed_bar_alternate_color ($_alternate)).
      "\">
       <TD>$payrecdate</TD>
       <TD><I>$payrecdescrip</I></TD>
       <TD>$this_type</TD>
       <TD ALIGN=RIGHT>
        <FONT COLOR=\"#ff0000\">
         <TT><B>".$charge."</B></TT>
        </FONT>
       </TD> 
       <TD ALIGN=RIGHT>
        <FONT COLOR=\"#000000\">
         <TT><B>".$payment."</B></TT>
        </FONT>
       </TD>
       <TD ALIGN=RIGHT>
     ";

     if (($this_user->getLevel() > $delete_level) and
         ($r[payreclock] != "locked"))
      echo "
       <A HREF=\"$page_name?$_auth&id=$id&patient=$patient&action=del\"
       ><$STDFONT_B>"._("DEL")."<$STDFONT_E></A>
      "; 

     echo "\n   &nbsp;</TD></TR>";
   } // wend?

   // calculate patient ledger total
   $patient_total = $total_payments - $total_charges;
   $patient_total = bcadd ($patient_total, 0, 2);
   if ($patient_total<0) {
     $pat_total = "<FONT COLOR=\"#000000\">".
      bcadd (-$patient_total, 0, 2)."</FONT>";
   } else {
     $pat_total = "<FONT COLOR=\"#ff0000\">".
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
      <FONT COLOR=\"#ff0000\"><TT>".bcadd($total_charges,0,2)."</TT></FONT>
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
     ><$STDFONT_B>"._("Add")." "._($record_name)."<$STDFONT_E></A> <B>|</B>
     <A HREF=\"manage.php?$_auth&id=$patient\"
     ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A>
    </CENTER>
    <P>
   "; 

   freemed_display_box_bottom ();
   break;

 } // master action switch

 freemed_close_db ();
 freemed_display_html_bottom ();
?>
