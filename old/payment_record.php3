<?php
 # file: payment_record.php3
 # note: payment record functions
 # code: jeff b (jeff@univrel.pr.uconn.edu)
 # lic : GPL

 $page_name   = "payment_record.php3";
 $record_name = "Payment Record";
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
   // determine closest date if none is provided
   if (empty($payrecdt))
     $payrecdt = $cur_date; // by default, the date is now...

   // actual display
   freemed_display_box_top ("$Add $record_name", $_ref, $page_name);

   // if a patient is provided...
   if ($patient>0) {
     $payrecsource = 1; // set to patient payment
     echo "
      <CENTER>
       <$STDFONT_B>Patient : <$STDFONT_E>
       <A HREF=\"manage.php3?$_auth&id=$patient\"
        ><$STDFONT_B>".$this_patient->fullName(true)."<$STDFONT_E></A>
      </CENTER>
     ";  
   }

   // set proper selected tag for payment source
   $_prs_0 = $_prs_1 = $_prs_2 = "";
   switch ($payrecsource) {
     case "1": $_prs_1 = "SELECTED"; break;
     case "2": $_prs_2 = "SELECTED"; break;
     case "0": $_prs_0 = "SELECTED"; break;
   } // end switch payrecsource 

   echo "
     <P>
     <FORM ACTION=\"$page_name\" METHOD=POST>
      <INPUT TYPE=HIDDEN NAME=\"action\"  VALUE=\"addform2\">
      <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">

     <TABLE WIDTH=100% BORDER=0 CELLSPACING=2 CELLPADDING=2
      VALIGN=MIDDLE ALIGN=CENTER>

     <TR><TD COLSPAN=2>
     <$HEADERFONT_B>Step One: Describe the Payment<$HEADERFONT_E>
     </TD></TR>

     <TR>
      <TD ALIGN=RIGHT><$STDFONT_B>Payment Source : <$STDFONT_E></TD>
      <TD><SELECT NAME=\"payrecsource\">
       <OPTION VALUE=\"0\" $_prs_0>Insurance Payment 
       <OPTION VALUE=\"1\" $_prs_1>Patient Payment
       <OPTION VALUE=\"2\" $_prs_2>Worker's Comp
      </SELECT></TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT><$STDFONT_B>Payment Type : <$STDFONT_E></TD>
      <TD><SELECT NAME=\"payrectype\">
       <OPTION VALUE=\"0\"         >cash
       <OPTION VALUE=\"1\" SELECTED>check
       <OPTION VALUE=\"2\"         >money order
       <OPTION VALUE=\"3\"         >credit card
       <OPTION VALUE=\"4\"         >traveller's check
       <OPTION VALUE=\"5\"         >EFT
      </SELECT></TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT><$STDFONT_B>Date Recieved : <$STDFONT_E></TD>
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
   freemed_display_box_top ("$Add $record_name", $_ref, $page_name);
   echo "
    <TABLE WIDTH=100% CELLSPACING=2 CELLPADDING=2 BORDER=0
     VALIGN=MIDDLE ALIGN=CENTER>

    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
    <INPUT TYPE=HIDDEN NAME=\"payrecsource\" VALUE=\"$payrecsource\">
    <INPUT TYPE=HIDDEN NAME=\"payrectype\"   VALUE=\"$payrectype\">
    <INPUT TYPE=HIDDEN NAME=\"payrecamt\"    VALUE=\"$payrecamt\">
    <INPUT TYPE=HIDDEN NAME=\"payrecdt_y\"   VALUE=\"$payrecdt_y\">
    <INPUT TYPE=HIDDEN NAME=\"payrecdt_m\"   VALUE=\"$payrecdt_m\">
    <INPUT TYPE=HIDDEN NAME=\"payrecdt_d\"   VALUE=\"$payrecdt_d\">
    <INPUT TYPE=HIDDEN NAME=\"patient\"      VALUE=\"$patient\">

    <TR><TD COLSPAN=2>
    <$HEADERFONT_B>Step Two: Specify the Payer<$HEADERFONT_E>
    </TD></TR>

   ";

   switch ($payrecsource) {
     case "1":
      if ($patient>0) {
       echo "
         <TR>
         <TD ALIGN=RIGHT><$STDFONT_B>Patient : <$STDFONT_E></TD>
         <TD><I>".$this_patient->fullName()."</I>
          <INPUT TYPE=HIDDEN NAME=\"payreclink\" VALUE=\"$patient\">
         </TD></TR>
       ";
      } else {
       echo "<TR><TD COLSPAN=2><CENTER>NOT IMPLEMENTED YET!</CENTER></TD></TR>";
      }
      break;
     case "0": default:
      echo "
       <TR>
       <TD ALIGN=RIGHT><$STDFONT_B>Insurance Company<$STDFONT_E></TD>
       </TD><SELECT NAME=\"payreclink\">
      ";
      $q = fdb_query ("SELECT * FROM $database.insco ORDER BY insconame");
      while ($r = fdb_fetch_array ($q)) {
        $r_id = $r["id"];
        $r_name = $r["insconame"];
        if ($debug==1) $r_debug = "[$r_id]";
        echo "
          <OPTION VALUE=\"$r_id\">$r_name $r_debug
        ";
      } // end while 
      echo "  
       </SELECT></TD>
       </TR>
      ";
      break;
   } // payment source switch end

   echo "
    <TR><TD COLSPAN=2>
    <$HEADERFONT_B>Step Three: Payment Information<$HEADERFONT_E>
    </TD></TR>
   ";

   switch ($payrectype) {
    case "1": // check
     echo "
      <TR>
      <TD ALIGN=RIGHT><$STDFONT_B>Check Number : <$STDFONT_E></TD>
      <TD><INPUT TYPE=TEXT NAME=\"payrecnum\" SIZE=20
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
      <TD><INPUT TYPE=TEXT NAME=\"payrecnum_1\" SIZE=17
       MAXLENGTH=16></TD>
      </TR>

      <TR>
      <TD ALIGN=RIGHT><$STDFONT_B>Expiration Date : <$STDFONT_E></TD>
      <TD>";
     fm_number_select ("payrecnum_e1", 1, 12, 1, true);
     echo "\n <B>/</B>&nbsp; \n";
     fm_number_select ("payrecnum_e2", (date("Y")-2), (date("Y")+10), 1);
     echo "</TD></TR>\n";
     break;
    case "4": // traveller's check
     echo "
      <TR>
      <TD ALIGN=RIGHT><$STDFONT_B>Cheque Number : <$STDFONT_E></TD>
      <TD><INPUT TYPE=TEXT NAME=\"payrecnum\" SIZE=21
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
   echo "<$STDFONT_B>$Adding ... <$STDFONT_E>\n";
   $query = "INSERT INTO $database.$db_name VALUES (
     '$cur_date',
     '',
     '".addslashes($patient).      "',
     '".addslashes(fm_date_assemble("payrecdt"))."', 
     '".addslashes($payrecsource). "',
     '".addslashes($payreclink).   "',
     '".addslashes($payrectype).   "',
     '".addslashes($payrecnum).    "',
     '".addslashes($payrecamt).    "',
     '".addslashes($payrecdescrip)."',
     NULL )";
   if ($debug) echo "<BR>(query = \"$query\")<BR>\n";
   $result = fdb_query($query);
   if ($result) { echo "$Done."; }
    else        { echo "$ERROR"; }
   echo "
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

   $query = "SELECT * FROM $database.payrec
             WHERE payrecpatient='$patient'
             ORDER BY payrecdt DESC";
   $result = fdb_query ($query);
   if (($result<1) or (fdb_num_rows($result)<1)) {
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
     <TD><B>Amount</B></TD>
     <TD><B>Action</B></TD>
    </TR>
   ";

   $_alternate = freemed_bar_alternate_color ();

   $total_payments = 0.00; // initially no money

   while ($r = fdb_fetch_array ($result)) {
     $payrecdate      = fm_date_print ($r["payrecdt"]);
     $payrecdescrip   = fm_prep ($r["payrecdescrip"]);
     $payrecamt       = fm_prep ($r["payrecamt"]);
     $total_payments += $payrecamt;
     $id              = $r["id"];
     $_alternate      = freemed_bar_alternate_color ($_alternate);
     echo "
      <TR BGCOLOR=$_alternate>
       <TD>$payrecdate</TD>
       <TD><I>$payrecdescrip</I></TD>
       <TD ALIGN=RIGHT><TT><B>".bcadd ($payrecamt, 0, 2)."</B></TT></TD> 
       <TD>
     ";
     if ($this_user->getLevel() > $delete_level)
      echo "
       <A HREF=\"$page_name?$_auth&id=$id&patient=$patient\"
       ><$STDFONT_B>$lang_DEL<$STDFONT_E></A>
      "; 
     echo "\n   &nbsp;</TD></TR>";
   } // wend?

   // display the total payments
   $_alternate = freemed_bar_alternate_color ($_alternate);
   echo "
    <TR BGCOLOR=$_alternate>
     <TD><B><$STDFONT_B SIZE=-1>TOTAL<$STDFONT_E></B></TD>
     <TD>&nbsp;</TD>
     <TD ALIGN=RIGHT><TT>".bcadd($total_payments,0,2)."</TT></TD>
     <TD>&nbsp;</TD>
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
