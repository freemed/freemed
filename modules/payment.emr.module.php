<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //	Fred Forester <fforest@netcarrier.com>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2006 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

LoadObjectDependency('org.freemedsoftware.core.EMRModule');

class PaymentModule extends EMRModule {

	var $MODULE_NAME    = "Transactions";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID	    = "715acf0f-9fd8-40cd-932c-1e864099d0e3";

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name     = "payrec";
	var $record_name    = "Payments";
	var $patient_field  = "payrecpatient";

	var $item;

	var $variables = array(
		"payrecdtadd",
		"payrecdtmod",
		"payrecpatient",
		"payrecdt",
		"payreccat",
		"payrecproc",
		"payrecsource",
		"payreclink",
		"payrectype",
		"payrecnum",
		"payrecamt",
		"payrecdescrip",
		"payreclock"
	);

	public function __construct ( ) {
		// __("Transactions")

		// Summary box information
		$this->summary_vars = array (
			"Date" => "payrecdt",
			"Proc Date" => "procdt",
			"Type" => "_type",
			"Form" => "_form",
			"Amount" => "_amount",
			"Balance" => "_balance"
		);
		$this->summary_query = array (
			"CASE payreccat ".
				"WHEN 0 THEN '".addslashes(__("Payment"))."' ".
				"WHEN 1 THEN '".addslashes(__("Adjustment"))."' ".
				"WHEN 2 THEN '".addslashes(__("Refund"))."' ".
				"WHEN 3 THEN '".addslashes(__("Denial"))."' ".
				"WHEN 4 THEN '".addslashes(__("Rebill"))."' ".
				"WHEN 5 THEN '".addslashes(__("Charge"))."' ".
				"WHEN 6 THEN '".addslashes(__("Transfer"))."' ".
				"WHEN 7 THEN '".addslashes(__("Withholding"))."' ".
				"WHEN 8 THEN '".addslashes(__("Deductable"))."' ".
				"WHEN 9 THEN '".addslashes(__("Fee Adjustment"))."' ".
				"WHEN 10 THEN '".addslashes(__("Billed"))."' ".
				"WHEN 11 THEN '".addslashes(__("Copayment"))."' ".
				"WHEN 12 THEN '".addslashes(__("Writeoff"))."' ".
				"ELSE '".addslashes(__("Unknown"))."' END AS _type",
			"CASE payreccat ".
				"WHEN 0 THEN ".
				"CASE payrectype ".
					"WHEN 0 THEN '".addslashes(__("Cash"))."' ".
					"WHEN 1 THEN '".addslashes(__("Check"))."' ".
					"WHEN 2 THEN '".addslashes(__("Credit Card"))."' ".
					"WHEN 3 THEN '".addslashes(__("Money Order"))."' ".
					"WHEN 4 THEN '".addslashes(__("Traveller's Check"))."' ".
					"ELSE '-' ".
				"END ".
				"ELSE '-' END AS _form",
			"CASE (CAST(procbalcurrent AS CHAR) LIKE '%.%') WHEN 1 THEN CONCAT('\$', CAST(procbalcurrent AS CHAR)) ELSE CONCAT('\$', CAST(procbalcurrent AS CHAR), '.00') END AS _balance",
			"CASE (CAST(payrecamt AS CHAR) LIKE '%.%') WHEN 1 THEN CONCAT('\$', CAST(payrecamt AS CHAR)) ELSE CONCAT('\$', CAST(payrecamt AS CHAR), '.00') END AS _amount"
		);
		$this->summary_query_link = array (
			'payrecproc' => 'procrec'
		);
		$this->summary_order_by = "payrecproc DESC, __actual_id";

		$this->acl = array ( 'bill', 'emr' );

		// Call parent constructor
		parent::__construct ( );
	} // end function PaymentModule

/*		// FIXME: migrate this code to functions :
	function transaction_wizard($procid, $paycat) {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
            global $payrecproc, $payreccat;

            $payreccat = $paycat;
            $payrecproc = $procid;

            if ($patient>0) {
                $this_patient = CreateObject('org.freemedsoftware.core.Patient', $patient);
            } else {
		$display_buffer .= __("No patient");
                template_display();
	    }

			$proc_rec = $GLOBALS['sql']->get_link('procrec', $procid);
			if (!$proc_rec) {
				$display_buffer .= __("Error getting procedure");
				template_display();
			}
			$proccovmap = "0:".$proc_rec[proccov1].":".$proc_rec[proccov2].":".
							$proc_rec[proccov3].":".$proc_rec[proccov4];

			global $visitsused,$visitsremain;
			$this->IsAuthorized($proc_rec,$visitsremain,$visitsused);

            // **************** FORM THE WIZARD ***************
            $wizard = CreateObject('PHP.wizard', array("item", "been_here", "module", 
									     "viewaction", "action", "patient"));


            // ************** SECOND STEP PREP ****************
            // determine closest date if none is provided
            //if (empty($payrecdt) and empty($payrecdt_y))
            //	$display_buffer .= "date $payrecdt $payrecdt_y $cur_date";
	    global $payrecdt_y, $payrecdt;
            if (empty($payrecdt_y) and empty($payrecdt)) {
                $payrecdt = $cur_date; // by default, the date is now...
		}
            // ************* ADD PAGE FOR STEP TWO *************

            switch ($payreccat)
            {
            case PAYMENT: // payment (0)
                    $wizard->add_page (
                        __("Describe the Payment"),
                        array_merge(array ("payrecsource", "payrectype", "payrecamt"), date_vars("payrecdt")),
                        html_form::form_table ( array (
                                                    __("Payment Source") =>
                                                                "<SELECT NAME=\"payrecsource\">"
                        											.$this->insuranceSelectionByType($proccovmap)."
                                                                 </SELECT>",

                                                    __("Payment Type") =>
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

                                                    __("Date Received") =>
                                                                     fm_date_entry ("payrecdt"),

                                                    __("Payment Amount") =>
                                                                      "<INPUT TYPE=TEXT NAME=\"payrecamt\" SIZE=10 MAXLENGTH=15 ".
                                                                      "VALUE=\"".prepare($payrecamt)."\">\n"
                                                )
                                              )
                    );

                // second page of payments whois making the payment
                $second_page_array = "";

                switch ($payrecsource)
                {
                case "0":
                    if ($patient>0)
                    {
                        $second_page_array["Patient"] =
                            $this_patient->fullName()."
                            <INPUT TYPE=HIDDEN NAME=\"payreclink\" ".
                            "VALUE=\"".prepare($patient)."\">\n";
                    }
                    else
                    {
                        $display_buffer .= "
                        <TR><TD COLSPAN=2><CENTER>NOT IMPLEMENTED YET!</CENTER>
                        </TD></TR>
                        ";
                    }
                    break;
				default:
					$payreclink = $this->coverageIDFromType($proccovmap,$payrecsource);
					// we can make this hidden now also since we know the link amount
					// fixme when you get a chance.
                    $second_page_array[__("Insurance Company")] =
                        $this->insuranceName($payreclink)."
                        <INPUT TYPE=HIDDEN NAME=\"payreclink\" ".
                        "VALUE=\"".prepare($payreclink)."\">\n";
                    break;
                } // payment source switch end
                // how is the payment being made
                switch ($payrectype)
                {
                case "1": // check
                    $second_page_array[__("Check Number")] =
                        "<INPUT TYPE=TEXT NAME=\"payrecnum\" SIZE=20 ".
                        "VALUE=\"".prepare($payrecnum)."\">\n";
                    break;
                case "2": // money order
                    $second_page_array[] = "<B>NOT IMPLEMENTED YET!</B><BR>\n";
                    break;
                case "3": // credit card
                    $second_page_array[__("Credit Card Number")] =
                        "<INPUT TYPE=TEXT NAME=\"payrecnum_1\" SIZE=17 ".
                        "MAXLENGTH=16 VALUE=\"".prepare($payrecnum_1)."\">\n";

                    $second_page_array[__("Expiration Date")] =
                        fm_number_select ("payrecnum_e1", 1, 12, 1, true).
                        "\n <B>/</B>&nbsp; \n".
                        fm_number_select ("payrecnum_e2", (date("Y")-2), (date("Y")+10), 1);
                    break;
                case "4": // traveller's check
                    $second_page_array[__("Cheque Number")] =
                        "<INPUT TYPE=TEXT NAME=\"payrecnum\" SIZE=21 ".
                        "MAXLENGTH=20 VALUE=\"".prepare($payrecnum)."\">\n";
                    break;
                case "5": // EFT
                    $second_page_array[] = "<B>NOT IMPLEMENTED YET!</B><BR>\n";
                    break;
                case "0":
                default: // if nothing... (or cash)
                    break;
                } // end of type switch

                $second_page_array[__("Description")] =
                    "<INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30 ".
                    "VALUE=\"".prepare($payrecdescrip)."\">\n";

                $wizard->add_page(
                    __("Step Three").": ".__("Specify the Payer"),
                    array ("payreclink", "payrecdescrip", "payrecnum",
                           "payrecnum_e1", "payrecnum_e2"),
                    html_form::form_table ( $second_page_array )
                );

				if ($visitsremain)
				{
					$wizard->add_page(__("Handle Authorization"),
						array("updatevists","visitsremain","visitsused"),
						html_form::form_table( array(
								__("Update Visits") => "<SELECT NAME=\"updatevisits\">".
													  "<OPTION VALUE=\"0\">".__("No").
													  "<OPTION VALUE=\"1\">".__("Yes").
													  "</SELECT>",
								__("Remaining Visits") => prepare($visitsremain),
								__("Used Visits") => prepare($visitsused)
											))
								);
				}

                break; // end of payment

            case WITHHOLD: // adjustment (7)
            case DEDUCTABLE: // adjustment (8)
            case ADJUSTMENT: // adjustment (1)
				$amount_heading[WITHHOLD] = __("Withhold Amount");
				$amount_heading[ADJUSTMENT] = __("Adjustment Amount");
				$amount_heading[DEDUCTABLE] = __("Deductable Amount");
				$title[WITHHOLD] = __("Describe the Withholding");
				$title[ADJUSTMENT] = __("Describe the Adjustment");
				$title[DEDUCTABLE] = __("Describe the Deductable");
                $wizard->add_page (
                    __("Step Two").": ".$title[$payreccat],
                    array_merge(array ("payrecamt", "payrecdescrip"),date_vars("payrecdt")),
                    html_form::form_table ( array (
                                                __("Date Received") =>
                                                                 fm_date_entry ("payrecdt"),
                                                __("Description") =>
                                                                  "<INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30 ".
                                                                  "VALUE=\"".prepare($payrecdescrip)."\">\n",
                                                "$amount_heading[$payreccat]" =>
                                                                     "<INPUT TYPE=TEXT NAME=\"payrecamt\" SIZE=10 MAXLENGTH=9 ".
                                                                     "VALUE=\"".prepare($payrecamt)."\">\n"
                                            ) )
                );

                break; // end of adjustment

            case FEEADJUST: // adjustment (1)
                $wizard->add_page (
                    __("Step Two").": ".__("Describe the Adjustment"),
                    array_merge(array ("payrecsource", "payrecamt", "payrecdescrip"),date_vars("payrecdt")),
                    html_form::form_table ( array (
                                                __("Insurance Company") =>
                        										"<SELECT NAME=\"payrecsource\">".
                        										$this->insuranceSelection($proccovmap).
                        										"</SELECT>\n",
                                                __("Date Received") =>
                                                                 fm_date_entry ("payrecdt"),
                                                __("Description") =>
                                                                  "<INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30 ".
                                                                  "VALUE=\"".prepare($payrecdescrip)."\">\n",
                                                "Allowed Amount" =>
                                                                     "<INPUT TYPE=TEXT NAME=\"payrecamt\" SIZE=10 MAXLENGTH=9 ".
                                                                     "VALUE=\"".prepare($payrecamt)."\">\n"
                                            ) )
                );

                break; // end of adjustment
            case REFUND: // refund (2)
                $wizard->add_page (
                    __("Step Two").": ".__("Describe the Refund"),
                    array_merge(array ("payrecamt", "payrecdescrip", "payreclink"),date_vars("payrecdt")),
                    html_form::form_table ( array (
                                                __("Date of Refund") =>
                                                                  fm_date_entry ("payrecdt"),

                                                __("Destination") =>
                                                               "<SELECT NAME=\"payreclink\">
                                                               <OPTION VALUE=\"0\" ".
                                                               ( ($payreclink==0) ? "SELECTED" : "" ).">".__("Apply to Credit")."
                                                               <OPTION VALUE=\"1\" ".
                                                               ( ($payreclink==1) ? "SELECTED" : "" ).">".__("Refund to Patient")."
                                                               </SELECT>\n",

                                                __("Description") =>
                                                                  "<INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30 ".
                                                                  "VALUE=\"".prepare($payrecdescrip)."\">\n",
                                                __("Refund Amount") =>
                                                                 "<INPUT TYPE=TEXT NAME=\"payrecamt\" SIZE=10 ".
                                                                 "MAXLENGTH=9 VALUE=\"".prepare($payrecamt)."\">\n",

                                            ) )
                );

                break; // end of refund

            case COPAY: // copay (11)
                $wizard->add_page (
                    __("Step Two").": ".__("Describe the Copayment"),
                    array_merge(array ("payrecamt", "payrecdescrip"),date_vars("payrecdt")),
                    html_form::form_table ( array (
                                                __("Date of Copay") =>
                                                                  fm_date_entry ("payrecdt"),

                                                __("Description") =>
                                                                  "<INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30 ".
                                                                  "VALUE=\"".prepare($payrecdescrip)."\">\n",
                                                __("Copay Amount") =>
                                                                 "<INPUT TYPE=TEXT NAME=\"payrecamt\" SIZE=10 ".
                                                                 "MAXLENGTH=9 VALUE=\"".prepare($payrecamt)."\">\n",

                                            ) )
                );

                break; // end of copay


            case DENIAL: // denial (3)
                $wizard->add_page (
                    __("Step Two").": ".__("Describe the Denial"),
                    array_merge(array ("payreclink", "payrecdescrip"), date_vars("payrecdt")),
                    html_form::form_table ( array (
                                                __("Date of Denial") =>
                                                                  fm_date_entry ("payrecdt"),

                                                __("Description") =>
                                                                  "<INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30 ".
                                                                  "VALUE=\"".prepare($payrecdescrip)."\">\n",

                                                __("Adjust to Zero?") =>
                                                                   "<SELECT NAME=\"payreclink\">
                                                                   <OPTION VALUE=\"0\" ".
                                                                   ( ($payreclink==0) ? "SELECTED" : "" ).">".__("no")."
                                                                   <OPTION VALUE=\"1\" ".
                                                                   ( ($payreclink==1) ? "SELECTED" : "" ).">".__("yes")."
                                                                   </SELECT>\n"
                                            ) )
                );

                break; // end of denial

            case WRITEOFF: // writeoff (12)
                $wizard->add_page (
                    __("Step Two").": ".__("Describe the Writeoff"),
                    array_merge(array ("payreclink", "payrecdescrip"), date_vars("payrecdt")),
                    html_form::form_table ( array (
                                                __("Date of Writeoff") =>
                                                                  fm_date_entry ("payrecdt"),

                                                __("Description") =>
                                                                  "<INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30 ".
                                                                  "VALUE=\"".prepare($payrecdescrip)."\">\n"
                                            ) )
                );

                break; // end of writeoff
            case TRANSFER: // transfer (6)
                $wizard->add_page (
                    __("Step Two").": ".__("Describe the Transfer"),
                    array_merge(array ("payrecsource", "payrecdescrip"), date_vars("payrecdt")),
                    html_form::form_table ( array (
                                                __("Date of Transfer") =>
                                                                  fm_date_entry ("payrecdt"),
                                                __("Description") =>
                                                                  "<INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30 ",
                                                __("Transfer to") =>
                                                                "<SELECT NAME=\"payrecsource\">"
                        											.$this->insuranceSelectionByType($proccovmap)."
                                                                 </SELECT>"
                                            ) )
                );

                break; // end of denial
            case REBILL: // rebill 4
                $wizard->add_page(
                    __("Step Two").": ".__("Rebill Information"),
                    array_merge(array ("payrecdescrip"), date_vars("payrecdt")),
                    html_form::form_table ( array (
                                                __("Date of Rebill") =>
                                                                  fm_date_entry ("payrecdt"),
                                                __("Description") =>
                                                                  "<INPUT TYPE=TEXT NAME=\"payrecdescrip\" SIZE=30 ".
                                                                  "VALUE=\"".prepare($payrecdescrip)."\">\n"
                                            ) )
                );
                break; // end of rebills

            default: // we shouldn't be here
                // do nothing -- we haven't selected payments yet
                break;
            } // end switch payreccat

            if (!$wizard->is_done() and !$wizard->is_cancelled())
            {
                $display_buffer .= "<CENTER>".$wizard->display()."</CENTER>";
                return;
            }

            if ($wizard->is_cancelled())
            {
                // if the wizard was cancelled
                $display_buffer .= "<CENTER>CANCELLED<BR></CENTER><BR>\n";
            }

            if ($wizard->is_done())
            {
                //$page_title = __("Adding")." ".__($record_name);
                //if ($patient>0) $display_buffer .= freemed::patient_box ($this_patient);
                $display_buffer .= "<CENTER>\n";
                switch ($payreccat)
                { // begin category case (add)
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
                        $display_buffer .= "<B>NOT IMPLEMENTED YET!!!</B><BR>\n";
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
                        $display_buffer .= "$ERROR!!! payrectype not present<BR>\n";
                        $payrecnum = ""; // kill!!!
                        template_display();
                        break;
                    } // end switch payrectype

					if ($updatevisits)
					{
						$auth_query = "UPDATE authorizations SET authvisitsremain=authvisitsremain-1,".
								      "authvisitsused=authvisitsused+1";

					}
                    break; // end payment category (add)

                case ADJUSTMENT: // adjustment category 1
                case DEDUCTABLE: // adjustment category 8
                case WITHHOLD: // adjustment category 7
					$payrecamt = abs($payrecamt);
                    break; // end adjustment category 

				case COPAY: // copay
					$payreclink = $patient;
					$payrecsource = 0; // patient is source
					break;
                case FEEADJUST: // adjustment category (add) 1
					// calc the payrecamt
					$payreclink = $this->coverageIDFromType($proccovmap,$payrecsource);
                    $proccharges = freemed::get_link_field ($payrecproc, "procrec",
                                                               "proccharges");
					$payrecamt = $proccharges - abs($payrecamt);
                    break; // end adjustment category (add)

                case REFUND: // refund category (add) 2
                    break; // end refund category (add)

                case TRANSFER: // refund category 6
						// show as transferring the balance
                        $payrecamt = freemed::get_link_field ($payrecproc, "procrec",
                                                               "procbalcurrent");
						if ($payrecsource == 0)
							$payreclink = 0;
						else
							$payreclink = $this->coverageIDFromType($proccovmap,$payrecsource);
					
                    break; // end refund category (add)
                case DENIAL: // denial category (add) 3
					$payrecamt = 0; // default
                    if ($payreclink==1) // adjust to zero
                    {
                        $payrecamt = freemed::get_link_field ($payrecproc, "procrec",
                                                               "procbalcurrent");
                        //$payrecamt   = -(abs($amount_left));
                    }
                    break; // end denial category (add)

                case WRITEOFF: // writeoff entire balance
					$payrecamt = 0; // default
                    $payrecamt = freemed::get_link_field ($payrecproc, "procrec",
                                                               "procbalcurrent");
                    break; // end writeoff category (add)

                case REBILL: // rebill category (add) 4
						// save off the amount we are re billing
                        $payrecamt = freemed::get_link_field ($payrecproc, "procrec",
                                                               "procbalcurrent");
                    break; // end rebill category (add)
                } // end category switch (add)

		global $payrecdt, $payrecdt_y;
                if (empty($payrecdt_y) and empty($payrecdt))
                {
                    $payrecdt = $cur_date; // by default, the date is now...
                }
                else
                {
                    $payrecdt = fm_date_assemble("payrecdt");
                }
                $display_buffer .= __("Adding")." ... \n";
				$query = $sql->insert_query($this->table_name,
					array(
						"payrecdtadd" => $cur_date,
						"payrecdtmod" => $cur_date,
						"payrecpatient" => $patient,
						"payrecdt"      => fm_date_assemble("payrecdt"),
						"payreccat",
						"payrecproc",
						"payrecsource",
						"payreclink",
						"payrectype",
						"payrecnum",
						"payrecamt" => $payrecamt,
						"payrecdescrip",
						"payreclock" => "unlocked"
						));

                if ($debug) $display_buffer .= "<BR>(query = \"$query\")<BR>\n";
                $result = $sql->query($query);
            	if ($result) 
                { 
                    $display_buffer .= __("done")."."; 
                }
                else
                { 
                    $display_buffer .= __("ERROR");    
                }
                $display_buffer .= "  <BR>".__("Modifying procedural charges")." ... \n";
				$procrec = freemed::get_link_rec($payrecproc,"procrec");	
				if (!$procrec)
					$display_buffer .= __("ERROR");

				$proccharges = $procrec[proccharges];
				$procamtpaid = $procrec[procamtpaid];
				$procbalorig = $procrec[procbalorig];

                switch ($payreccat)
                {

                case FEEADJUST: // adjustment category (add) 1
					// we had to blowout the payrecamt above so we calc the original
					// amt that was entered.
					
					$allowed = $proccharges - $payrecamt;
					$proccharges = $allowed;
					$procbalcurrent = $proccharges - $procamtpaid;
                    $query = "UPDATE procrec SET
                             procbalcurrent = '$procbalcurrent',
							 proccharges = '$proccharges',
							 procamtallowed = '$allowed'
                             WHERE id='".addslashes($payrecproc)."'";
                    break; // end fee adjustment 

                case REFUND: // refund category (add) 2
					$proccharges = $proccharges + $payrecamt;
					$procbalcurrent = $proccharges - $procamtpaid;
                    $query = "UPDATE procrec SET
                             proccharges    = '$proccharges',
							 procbalcurrent = '$procbalcurrent'
                             WHERE id='$payrecproc'";
                    break; // end refund category (add)

                case DENIAL: // denial category (add) 3
                    if ($payreclink==1)  // adjust to zero?
                    {
						// this should force it to 0 naturally
						$proccharges = $proccharges - $payrecamt;
						$procbalcurrent = $proccharges - $procamtpaid;
                        $query = "UPDATE procrec SET
                             proccharges    = '$proccharges',
							 procbalcurrent = '$procbalcurrent'
                             WHERE id='$payrecproc'";
                    }
                    else
                    { // if no adjust
                        $query = "";
                    } // end checking for adjust to zero
                    break; // end denial category (add)

                case WRITEOFF: // writeoff entire balance
					// this should force it to 0 naturally
					$proccharges = $proccharges - $payrecamt;
					$procbalcurrent = $proccharges - $procamtpaid;
					$query = "UPDATE procrec SET
						 proccharges    = '$proccharges',
						 procbalcurrent = '$procbalcurrent'
						 WHERE id='$payrecproc'";
                    break; // end Writeoff 

                case REBILL: // rebill category (add) 4
                    $query = "UPDATE procrec SET procbilled='0' WHERE id='".addslashes($payrecproc)."'";
                    break; // end rebill category (add)

                case TRANSFER: // transfer (6)
					// here the link is an insurance type not an insco id.
                    $query = "UPDATE procrec SET proccurcovtp='".addslashes($payrecsource)."',
												 proccurcovid='".addslashes($payreclink)."'
							WHERE id='".addslashes($payrecproc)."'";
                    break; // end rebill category (add)

                case DEDUCTABLE: // adjustment category 8
                case WITHHOLD: // adjustment category 7
					$proccharges = $proccharges - $payrecamt;
					$procbalcurrent = $proccharges - $procamtpaid;
                    $query = "UPDATE procrec SET
                             procbalcurrent = '$procbalcurrent',
                             proccharges    = '$proccharges'
                             WHERE id='".addslashes($payrecproc)."'";
                    break;
                case ADJUSTMENT: // adjustment category (add) 1
					$procamtpaid = $procamtpaid + $payrecamt;
					$procbalcurrent = $proccharges - $procamtpaid;
                    $query = "UPDATE procrec SET
                             procbalcurrent = '$procbalcurrent',
                             procamtpaid    = '$procamtpaid'
                             WHERE id='".addslashes($payrecproc)."'";
					break;
                case PAYMENT: // payment category (add) 0
                case COPAY: // copay is a payment but we need to know the difference
                default:  // default is payment
					$procamtpaid = $procamtpaid + $payrecamt;
					$procbalcurrent = $proccharges - $procamtpaid;
                    $query = "UPDATE procrec SET
                             procbalcurrent = '$procbalcurrent',
                             procamtpaid    = '$procamtpaid'
                             WHERE id='".addslashes($payrecproc)."'";
                    break;
                } // end category switch (add)
                if ($debug) $display_buffer .= "<BR>(query = \"$query\")<BR>\n";
                if (!empty($query))
                {
                    $result = $sql->query($query);
                    if ($result) { $display_buffer .= __("done")."."; }
                    else        { $display_buffer .= __("ERROR");    }
                }
                else
                { // if there is no query, let the user know we did nothing
                    $display_buffer .= "unnecessary";
                } // end checking for null query
			
				if ($updatevisits)
				{
                	$display_buffer .= "  <BR>".__("Modifying Authorized visits")." ... \n";
                    $result = $sql->query($auth_query);
                    if ($result) { $display_buffer .= __("done")."."; }
                    else        { $display_buffer .= __("ERROR");    }
				}

            }  // end processing wizard done


            // we send this if cancelled or done.
            $display_buffer .= "
            </CENTER>
            <P>
            <CENTER>
            <A HREF=\"$this->page_name?been_here=1&viewaction=refresh".
            "&action=addform&item=$payrecproc&patient=$patient&module=$module\">
            ".__("Back")."</A>
            </CENTER>
            <P>
            ";

            return;


        } // wizard code

        function ledger($procid=0) {
		global $display_buffer;
            reset ($GLOBALS);
            while (list($k,$v)=each($GLOBALS))
            {
                global $$k;
                //$display_buffer .= "$$k $v<BR>";
            }

            if ($procid)
            {
                $pay_query  = "SELECT * FROM payrec
                              WHERE payrecpatient='".addslashes($patient)."' AND payrecproc='".addslashes($procid)."'
                              ORDER BY payrecdt,id";
            }
            else
            {
                $pay_query  = "SELECT * FROM payrec AS a, procrec AS b
                              WHERE b.procbalcurrent".$this->view_query." AND
                              b.id = a.payrecproc AND
                              a.payrecpatient='".addslashes($patient)."'
                              ORDER BY payrecproc,payrecdt,a.id";
            }
            $pay_result = $sql->query ($pay_query);

            if (!$sql->results($pay_result)) {
                $display_buffer .= "
                <CENTER>
                <P>
                <B>
                ".__("There are no records for this patient.")."
                </B>
                <P>
                <A HREF=\"manage.php?id=$patient\"
                >".__("Manage_Patient")."</A>
                <P>
                </CENTER>
                ";
                template_display(); // kill!!
            } // end/if there are no results

            // if there is something, show it...
            $display_buffer .= "
            <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
            <TR>
            <TD><B>".__("Date")."</B></TD>
            <TD><B>".__("Type")."</B></TD>
            <TD><B>".__("Description")."</B></TD>
            <TD ALIGN=RIGHT><B>".__("Charges")."</B></TD>
            <TD ALIGN=RIGHT><B>".__("Payments")."</B></TD>
            <TD ALIGN=RIGHT><B>".__("Balance")."</B></TD>
            <TD ALIGN=RIGHT><B>".__("Action")."</B></TD>
            </TR>
            ";

            $total_payments = 0.00; // initially no payments
            $total_charges  = 0.00; // initially no charges

            while ($r = $sql->fetch_array ($chg_result)) {
                $procdate        = fm_date_print ($r["procdt"]);
                $procbalorig     = $r["procbalorig"];
                $id              = $r["id"];
                $total_charges  += $procbalorig;
                $display_buffer .= "
                <TR CLASS=\"".freemed_alternate()."\">
                <TD>$procdate</TD>
                <TD><I>".( (!empty($r[proccomment])) ?
					prepare($r[proccomment]) : "&nbsp;" )."</I></TD>
                <TD>charge</TD>
                <TD ALIGN=RIGHT>
                <FONT COLOR=\"#ff0000\">
                <TT><B>".bcadd($procbalorig, 0, 2)."</B></TT>
                </FONT>
                </TD>
                <TD ALIGN=RIGHT>
                <FONT COLOR=\"$paycolor\">
                <TT>&nbsp;</TT>
                </FONT>
                </TD>
                <TD>
                ";
                $display_buffer .= "\n   &nbsp;</TD></TR>";
            } // wend?
            $prev_proc = "$";
            while ($r = $sql->fetch_array ($pay_result)) {
                $payrecdate      = fm_date_print ($r["payrecdt"]);
                $payrecdescrip   = prepare ($r["payrecdescrip"]);
                $payrecamt       = prepare ($r["payrecamt"]);
                $payrectype      = $r["payrectype"];

                // start control break processing
                // first time in
                if ($prev_proc=="$")
                {
                    $prev_proc = $r["payrecproc"];
                    $proc_charges = 0.00;
                    $proc_payments = 0.00;
                }
                if ($prev_proc != $r["payrecproc"])
                {  // control break
                    $proc_total = $proc_charges - $proc_payments;
                    $proc_total = bcadd ($proc_total, 0, 2);
                    if ($proc_total<0)
                    {
                        $prc_total = "<FONT COLOR=\"#000000\">".
                                     $proc_total."</FONT>";
                    }
                    else
                    {
                        $prc_total = "<FONT COLOR=\"#ff0000\">".
                                     $proc_total."</FONT>";
                    } // end of creating total string/color

                    // display the total payments
                    $display_buffer .= "
                    <TR CLASS=\"".freemed_alternate()."\">
                    <TD><B><FONT SIZE=\"-1\">SUBTOT</FONT></B></TD>
                    <TD>&nbsp;</TD>
                    <TD>&nbsp;</TD>
                    <TD ALIGN=RIGHT>
                    <FONT COLOR=\"#ff0000\"><TT>".bcadd($proc_charges,0,2)."</TT></FONT>
                    </TD>
                    <TD ALIGN=RIGHT>
                    <TT>".bcadd($proc_payments,0,2)."</TT>
                    </TD>
                    <TD ALIGN=RIGHT>
                    <B><TT>$prc_total</TT></B>
                    </TD>
                    <TD>&nbsp;</TD>
                    </TR>
                    <TR CLASS=\"".freemed_alternate()."\">
                    <TD COLSPAN=7>&nbsp;</TD>
                    </TR>
                    ";
                    $prev_proc = $r["payrecproc"];
                    $proc_charges = 0.00;
                    $proc_payments = 0.00;
                } // end control break

                // end control break processing
                switch ($r["payreccat"]) { // category switch
                case REFUND: // refunds 2
                case PROCEDURE: // charges 5
                    $pay_color       = "#000000";
                    $payment         = "&nbsp;";
                    $charge          = bcadd($payrecamt, 0, 2);
                    $total_charges  += $payrecamt;
                    $proc_charges   += $payrecamt;
                    break;
                case REBILL: // rebills 4
                    $payment         = "&nbsp;";
                    $charge          = "&nbsp;";
                    break;
                case WRITEOFF: // writeoff 12
                case DENIAL: // denials 3
                    $pay_color       = "#000000";
                    $charge          = bcadd(-$payrecamt, 0, 2);
                    $payment         = "&nbsp;";
                    $total_charges  += $charge;
                    $proc_charges   += $charge;
                    break;
                case TRANSFER: // transfer 6
                    $payment         = "&nbsp;";
                    $charge          = "&nbsp;";
                    break;
                case WITHHOLD: // withhold 7
                case DEDUCTABLE: // deductable 8
                    $pay_color       = "#000000";
                    $charge          = bcadd(-$payrecamt, 0, 2);
                    $payment         = "&nbsp;";
                    $total_charges  += $charge;
                    $proc_charges   += $charge;
                    break;
                case FEEADJUST: // feeadjust 9
                    $pay_color       = "#000000";
                    $charge          = bcadd(-$payrecamt, 0, 2);
                    $payment         = "&nbsp;";
                    $total_charges  += $charge;
                    $proc_charges   += $charge;
                    break;
                case BILLED: // billed on 10
                    $payment         = "&nbsp;";
                    $charge          = "&nbsp;";
                    break;
                case ADJUSTMENT: // adjustments 1
                    $pay_color       = "#ff0000";
                    $payment         = bcadd($payrecamt, 0, 2);
                    $charge          = "&nbsp;";
                    $total_payments += $payment;
                    $proc_payments += $payment;
                    break;
            	case PAYMENT: default: // default is payments 0
            	case COPAY: default: // default is payments 0
                    $pay_color       = "#ff0000";
                    $payment         = bcadd($payrecamt, 0, 2);
                    $charge          = "&nbsp;";
                    $total_payments += $payrecamt;
                    $proc_payments += $payrecamt;
                    break;
                } // end of category switch (for totals)
                switch ($r["payreccat"]) {
                case ADJUSTMENT: // adjustments 1
                    $this_type = __("Adjustment");
                    break;
                case REFUND: // refunds 2
                    $this_type = __("Refund");
                    break;
                case DENIAL: // denial 3
                    $this_type = __("Denial");
                    break;
                case WRITEOFF: // writeoff 12 
                    $this_type = __("Writeoff");
                    break;
                case REBILL: // rebill 4
                    $this_type = __("Rebill");
                    break;
                case PROCEDURE: // charge 5
                    $this_type = __("Charge");
                    break;
                case TRANSFER: // transfer 6
                    $this_type = __("Transfer to")." ".$PAYER_TYPES[$r["payrecsource"]];
                    break;
                case WITHHOLD: // withhold 7
                    $this_type = __("Withhold");
                    break;
                case DEDUCTABLE: // deductable 8
                    $this_type = __("Deductable");
                    break;
                case FEEADJUST: // feeadjust 9
                    $this_type = __("Fee Adjust");
                    break;
                case BILLED: // billed 10
                    $this_type = __("Billed")." ".$PAYER_TYPES[$r["payrecsource"]];
                    break;
                case COPAY: // COPAY 11
                    $this_type = __("Copay");
                    break;
                case PAYMENT: // payment 0
                default:  // default is payment
                    $this_type = __("Payment")." ".$PAYER_TYPES[$r["payrecsource"]];
                    break;
                } // end of categry switch (name)
                $id              = $r["id"];
                if (empty($payrecdescrip)) $payrecdescrip="NO DESCRIPTION";
                $display_buffer .= "
                <TR CLASS\"".freemed_alternate()."\">
                <TD>$payrecdate</TD>
                <TD><B>$this_type</B></TD>
                <TD><I>$payrecdescrip</I></TD>
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
                <TD ALIGN=RIGHT>&nbsp;</TD>
                <TD ALIGN=RIGHT>
                ";

                //if (($this_user->getLevel() > $delete_level) and
                //	 ($r[payreclock] != "locked"))
                //  $display_buffer .= "
                //  <A HREF=\"$page_name?id=$id&patient=$patient&action=del\"
                //  >".__("DEL")."</A>
                //  ";

                $display_buffer .= "&nbsp;</TD></TR>";
            } // wend?

            // process last subtotal
            // calc last proc subtotal
            $proc_total = $proc_charges - $proc_payments;
            $proc_total = bcadd ($proc_total, 0, 2);
            if ($proc_total<0)
            {
                $prc_total = "<FONT COLOR=\"#000000\">".
                             $proc_total."</FONT>";
            }
            else
            {
                $prc_total = "<FONT COLOR=\"#ff0000\">".
                             $proc_total."</FONT>";
            } // end of creating total string/color

            // display the total payments
            $display_buffer .= "
            <TR CLASS=\"".freemed_alternate()."\">
            <TD><B><FONT SIZE=\"-1\">SUBTOT</FONT></B></TD>
            <TD>&nbsp;</TD>
            <TD>&nbsp;</TD>
            <TD ALIGN=RIGHT>
            <FONT COLOR=\"#ff0000\"><TT>".bcadd($proc_charges,0,2)."</TT></FONT>
            </TD>
            <TD ALIGN=RIGHT>
            <TT>".bcadd($proc_payments,0,2)."</TT>
            </TD>
            <TD ALIGN=RIGHT>
            <B><TT>$prc_total</TT></B>
            </TD>
            <TD>&nbsp;</TD>
            </TR>
            <TR CLASS=\"".freemed_alternate()."\">
            <TD COLSPAN=7>&nbsp;</TD>
            </TR>
            ";
            // end calc last proc subtotal

            // calculate patient ledger total
            $patient_total = $total_charges - $total_payments;
            $patient_total = bcadd ($patient_total, 0, 2);
            if ($patient_total<0) {
                $pat_total = "<FONT COLOR=\"#000000\">".
                             $patient_total."</FONT>";
            } else {
                $pat_total = "<FONT COLOR=\"#ff0000\">".
                             $patient_total."</FONT>";
            } // end of creating total string/color

            // display the total payments
            $display_buffer .= "
            <TR CLASS=\"".freemed_alternate()."\">
            <TD><B><FONT SIZE=\"-1\">TOTAL</FONT></B></TD>
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
            <TD>&nbsp;</TD>
            </TR>
			</TABLE>

		<div align=\"center\">
		<a class=\"button\" href=\"module_loader.php?module=".get_class($this)."&patient=".urlencode($patient)."&return=".$_REQUEST['return']."\">".__("Return to Ledger")."</a>
		</div>
            ";
        } // end ledger

		// FIXME: end of code block to migrate
	*/

	// Method: RemoveProcedureAsMistake
	//
	//	Removes procedure record and all payment/transaction records
	//	with it.
	//
	// Parameters:
	//
	//	$procid - Procedure record id
	//
        public function RemoveProcedureAsMistake ( $procid ) {
		$query = "DELETE FROM procrec WHERE id='".addslashes($procid)."'";
		$result = $GLOBALS['sql']->query( $query );
		$query = "DELETE FROM payrec WHERE payrecproc='".addslashes($procid)."'";
		$result = $GLOBALS['sql']->query( $query );
        } // end method RemoveAsMistake

	// Method: GetLedger
	//
	//	Get ledger records for a patient.
	//
	// Parameters:
	//
	//	$patient - Patient record id
	//
	//	$type - Type of query to return. Values are:
	//	* all - All records
	//	* closed - All closed records
	//	* nonclosed - All non closed, whether underpaid or overpaid
	//	* unpaid - All records with an unpaid balance
	//
	// Returns:
	//
	//	Ledger records for patient matching specified parameters as
	//	an array of hashes.
	//
	public function GetLedger ( $patient, $type ) {
		switch ($type) {
			case 'closed':
			// see paid procedures when closed is selected
			$view_query = "procbalcurrent = '0'"; 
			break; // end closed

			case 'nonclosed':
			// by default see unpaid and overpaid
			$view_query = "procbalcurrent !='0'";
			break; // end nonclosed

			case 'unpaid':
  			// we use this when being called from the unpaid procs report
			$view_unpaid = "procbalcurrent >'0'";
			break; // end unpaid

			case 'all': default:
			$view_query = "1 == 1";
			break; // end all
		} // end switch for view_query

		$query = "SELECT ".
				"pr.id AS id, ".
				"c.cptname AS cpt_code, ".
				"c.cptnameint AS cpt_name, ".
				"cm.cptmod AS cpt_modifier, ".
				"pr.procbalorig AS balance_original, ".
				"pr.procamtallowed AS amount_allowed, ".
				"pr.procamtpaid AS amount_paid, ".
				"pr.proccharges AS charges, ".
				"pr.procbalcurrent AS balance_current, ".
				"IF(pr.procbilled > 0, 'Yes', 'No') AS billed, ".
				"pr.procdtbileld AS billed_date ".
			"FROM procrec pr ".
				"LEFT OUTER JOIN cpt c ON pr.proccpt=c.id ". 
				"LEFT OUTER JOIN cptmod cm ON pr.proccptmod=cm.id ". 
			"WHERE ".
				"( procpatient = '".addslashes($patient)."' AND  ${view_query} ) ".
			"ORDER BY procdt,id";
		$result = $sql->queryAll ($query);

		return $result;
        } // end method GetLedger

	// Method: CoverageToInsuranceName
	//
	// Parameters:
	//
	//	$coverage - Coverage id
	//
	// Returns:
	//
	//	Textual name of insurance company / payer.
	//
	public function CoverageToInsuranceName( $coverage ) {
		$query = "SELECT i.insconame FROM coverage c LEFT OUTER JOIN insco i ON i.id=c.covinsco WHERE c.id='".addslashes($coverage)."'";
		return $GLOBALS['sql']->queryOne( $query );
	} // end method CoverageToInsuranceName

	// Method: CoverageIdFromType
	//
	//	Get coverage record id from procedure record.
	//
	// Parameters:
	//
	//	$proc - Procedure id
	//
	//	$type - "Type" of coverage, from 1 to 4
	//
	// Returns:
	//
	//	Coverage record number or 0 if invalid or nonexistent.
	//
	public function CoverageIdFromType( $proc, $type ) {
		switch ($type+0) {
			case 1: case 2: case 3: case 4:
			$query = "SELECT proccov".($type+0)." FROM procrec WHERE id='".addslashes($proc)."'";
			return $GLOBALS['sql']->queryOne( $query );
			break;

			default: return 0; break;
		}
	} // end method CoverageIdFromType

	// Method: PayerSelection
	//
	//	Get selection of payers for a particular procedure record.
	//
	// Parameters:
	//
	//	$proc - Procedure id
	//
	// Returns:
	//
	//	Hash of available payer/coverage options
	//
	public function PayerSelection ( $proc ) {
		$query = "SELECT p.id AS proc_id, c.id AS cov_id, i.insconame AS insconame FROM proc p LEFT OUTER JOIN coverage c ON ( c.id=p.proccov1 OR c.id=p.proccov2 OR c.id=p.proccov3 OR c.id=p.proccov4 ) LEFT OUTER JOIN insco i ON c.covinsco=i.id WHERE p.id='".addslashes( $proc )."'";
		$result = $GLOBALS['sql']->queryAll ( $query );
		foreach ( $result AS $r ) {
			$count++;
			if ( $r['proc_id'] ) {
				$return[$r['cov_id']] = $r['insconame'];			
			}
		}
		return $return;
	} // end method PayerSelection

	// Method: IsAuthorized
	//
	//	Determine authorization information for a procedure record.
	//
	// Parameters:
	//
	//	$proc - Procedure record (procrec) id
	//
	// Returns:
	//
	// 	Hash containing:
	//	* remain : authorization visits remaining
	//	* used : authorization visits used
	//
	public function IsAuthorized ( $proc ) {
		$query = "SELECT p.procauth, a.authvisitsremain AS remain, a.authvisitsused AS used FROM procrec p LEFT OUTER JOIN authorizations a ON a.id=p.procauth WHERE p.id='".addslashes($proc)."'";
		$auth = $GLOBALS['sql']->queryRow( $query );
		if ( $auth['procauth'] == 0 ) {
			return array ( 'remain' => 0, 'used' => 0 );
		}
		return array (
			'remain' => $auth['remain'],
			'used' => $auth['used']
		);
	} // end method IsAuthorized

} // end class PaymentModule

register_module("PaymentModule");

?>
