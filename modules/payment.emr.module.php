<?php
// $Id$
// desc: payment module 
// lic : GPL, v2

LoadObjectDependency('FreeMED.EMRModule');

class PaymentModule extends EMRModule {

	var $MODULE_NAME    = "Payments";
	var $MODULE_AUTHOR  = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name     = "payrec";
	var $record_name    = "Payments";
	var $patient_field  = "payrecpatient";

	var $item;
	var $view_query = "!='0'";  // by default see unpaid and overpaid
	var $view_closed = "='0'";  // see paid procedures when closed is selected
	var $view_unpaid = ">'0'";  // we use this when being called from the unpaid procs report

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

	// contructor method
	function PaymentModule ($nullvar = "") {
		// Table definition
		$this->table_definition = array (
			'payrecdtadd' => SQL__DATE,
			'payrecdtmod' => SQL__DATE,
			'payrecpatient' => SQL__INT_UNSIGNED(0),
			'payrecdt' => SQL__DATE,
			'payreccat' => SQL__INT_UNSIGNED(0),
			'payrecproc' => SQL__INT_UNSIGNED(0),
			'payrecsource' => SQL__INT_UNSIGNED(0),
			'payreclink' => SQL__INT_UNSIGNED(0),
			'payrectype' => SQL__INT_UNSIGNED(0),
			'payrecnum' => SQL__VARCHAR(100),
			'payrecamt' => SQL__REAL,
			'payrecdescrip' => SQL__TEXT,
			'payreclock' => SQL__ENUM(array('unlocked', 'locked')),
			'id' => SQL__SERIAL
		);
	
		// Summary box information
		$this->summary_vars = array (
			"Date" => "payrecdt",
			"Amount" => "payrecamt"
		);

		// Call parent constructor
		$this->EMRModule();
	} // end function paymentModule

	function modform() {
		global $display_buffer;
		$display_buffer .= "
			Temporarily, please use the <A HREF=\"billing_functions.php\"
			>Billing Functions</A> menu to access this portion of FreeMED.
		";
	}

	function addform() {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		// special circumstances when being called from the unpaid
		// reports module
		if ($viewaction=="paidledger")  
		{
			$this->view_query = $this->view_closed;
			$this->ledger();
			return;
		}

            if (!$been_here)
            {
                $this->item = 0;
                $this->view();
                return;
            }

            if ($viewaction=="ledgerall")
            {
                $this->ledger();
                return;
            }

            if ($viewaction=="ledger") // triggered from the ledger link
            {
                $this->ledger($item);
                return;
            }


            // everything else needs the proc id.

            $this->item = $item;
            if ($viewaction=="refresh" OR $viewaction=="closed" OR $item==0)
            {
				if ($viewaction=="closed")
					$this->view_query = $this->view_closed;
                $this->view();
                return;

            }

            if ($viewaction=="mistake")
            {
                $this->mistake($item);
            }

            //$this->view();  // always show the procs

            // from here on we should be processing the request type
            // start a wizard


            if ($viewaction=="rebill")
            {
                $this->transaction_wizard($item, REBILL);
            }

            if ($viewaction=="copay")
            {
                $this->transaction_wizard($item, COPAY);
            }

            if ($viewaction=="payment")
            {
                $this->transaction_wizard($item, PAYMENT);
            } 

            if ($viewaction=="transfer")
            {
                $this->transaction_wizard($item, TRANSFER);
            }

            if ($viewaction=="adjustment")
            {
                $this->transaction_wizard($item, ADJUSTMENT);
            }

            if ($viewaction=="deductable")
            {
                $this->transaction_wizard($item, DEDUCTABLE);
            }

            if ($viewaction=="withhold")
            {
                $this->transaction_wizard($item, WITHHOLD);
            }

            if ($viewaction=="denial")
            {
                $this->transaction_wizard($item, DENIAL);
            }

            if ($viewaction=="writeoff")
            {
                $this->transaction_wizard($item, WRITEOFF);
            }

            if ($viewaction=="refund")
            {
                $this->transaction_wizard($item, REFUND);
            }

            if ($viewaction=="allowedamt")
            {
                $this->transaction_wizard($item, FEEADJUST);
            }

	} // end function paymentModule->addform


	function transaction_wizard($procid, $paycat) {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
            global $payrecproc, $payreccat;

            $payreccat = $paycat;
            $payrecproc = $procid;

            if ($patient>0) {
                $this_patient = CreateObject('FreeMED.Patient', $patient);
            } else {
		$display_buffer .= __("No patient");
                template_display();
	    }

			$proc_rec = freemed::get_link_rec($procid, "procrec");
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
            if (empty($payrecdt_y))
			{
				global $payrecdt;
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

                if (empty($payrecdt_y))
                {
                    global $payrecdt;
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
					$procamtpaid = $procamtpaid - $payrecamt;
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

        function mod() {
		global $display_buffer;
            $display_buffer .= "Mod<BR>";
        }

        function add() {
		global $display_buffer;
            reset ($GLOBALS);
            while (list($k,$v)=each($GLOBALS))
            {
                global $$k;
                $display_buffer .= "$$k $v<BR>";
            }
            $display_buffer .= "Add<BR>";
        }

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
                              WHERE payrecpatient='$patient' AND payrecproc='$procid'
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
                    $payment         = bcadd(-$payrecamt, 0, 2);
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
            ";

        } // end ledger

        function mistake($procid) {
		global $display_buffer;
            reset ($GLOBALS);
            while (list($k,$v)=each($GLOBALS))
            {
                global $$k;
                //$display_buffer .= "$$k $v<BR>";
            }

            if (isset($delete))
            {
                $query = "DELETE FROM procrec WHERE id='".addslashes($procid)."'";
                $result = $sql->query($query);
                $query = "DELETE FROM payrec WHERE payrecproc='".addslashes($procid)."'";
                $result = $sql->query($query);
                $display_buffer .= "
                <p/>
                <div align=\"CENTER\">
                <B>".__("All records for this procedure have been deleted.")."</B><BR><BR>
                <a class=\"button\" HREF=\"$this->page_name?been_here=1&viewaction=refresh".
                "&action=addform&item=$payrecproc&patient=$patient&module=$module\">
                ".__("Back")."</a>
                </div>
                <p/>
                ";
                return;

            } // end mistake

            $display_buffer .= "
            <p/>
            <div align=\"CENTER\">
            ".__("Confirm delete request or cancel?")."<p/>
	    </div>
	    ".template::link_bar(array(
	    	__("Confirm") =>
			"$this->page_name?been_here=1&viewaction=mistake".
			"&action=addform&delete=1&item=$procid&patient=$patient&module=$module",
		__("Cancel") =>
			"$this->page_name?been_here=1&viewaction=refresh".
			"&action=addform&item=$procid&patient=$patient&module=$module"
		))."
            <p/>
            ";



        }
        function view() {
		global $display_buffer;
            global $sql,$patient,$module;

            $display_buffer .= "<FORM ACTION=\"$this->page_name\" METHOD=POST>
            <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">
            <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"addform\">
            <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"$module\">
            ";

            // initialize line item count
            $line_item_count = 0;

            $query = "SELECT * FROM procrec
                     WHERE ( (procpatient = '".addslashes($patient)."') AND
                     (procbalcurrent ".$this->view_query.") )
                     ORDER BY procdt,id";

            $result = $sql->query ($query);

            $display_buffer .= "
            <table BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"3\" WIDTH=\"100%\">
            <tr CLASS=\"thinbox\" style=\"text-size: 8pt;\">
            <td>&nbsp;</td>
            <td ALIGN=\"LEFT\"><b><small>".__("Date")."</small></b></td>
            <td ALIGN=\"LEFT\"><b><small>".__("Proc Code")."</small></b></td>
            <td ALIGN=\"LEFT\"><b><small>".__("Provider")."</small></b></td>
            <td ALIGN=\"RIGHT\"><b><small>".__("Charged")."</small></b></td>
            <td ALIGN=\"RIGHT\"><b><small>".__("Allowed")."</small></b></td>
            <td ALIGN=\"RIGHT\"><b><small>".__("Charges")."</small></b></td>
            <td ALIGN=\"RIGHT\"><b><small>".__("Paid")."</small></b></td>
            <td ALIGN=\"RIGHT\"><b><small>".__("Balance")."</small></b></td>
            <td ALIGN=\"LEFT\"><b><small>".__("Billed")."</small></b></td>
            <td ALIGN=\"LEFT\"><b><small>".__("Date Billed")."</small></b></td>
            <td ALIGN=\"LEFT\"><b><small>".__("View")."</small></b></td>
            </tr>
            ";

            // loop for all "line items"
            while ($r = $sql->fetch_array ($result))
            {
                $line_item_count++;
                $this_cpt = freemed::get_link_field ($r[proccpt], "cpt", "cptnameint");
                $this_cptcode = freemed::get_link_field ($r[proccpt], "cpt", "cptcode");
                $this_cptmod = freemed::get_link_field ($r[proccptmod],
                                                       "cptmod", "cptmod");
                $this_physician = CreateObject('FreeMED.Physician', $r[procphysician]);
                $display_buffer .= "
                <tr CLASS=".( ($this->item == $r['id']) ?  "#00ffff" :
                                freemed_alternate()).">
                <td>
                <input TYPE=\"RADIO\" NAME=\"item\" VALUE=\"".prepare($r['id'])."\"
                ".( ($r['id'] == $this->item) ?  "CHECKED": "" )." /></td>
                <td ALIGN=\"LEFT\"><small>".fm_date_print ($r['procdt'])."</small></td>
                <td ALIGN=\"LEFT\"><small>".
			prepare($this_cptcode." (".$this_cpt.")")."</small></td>
                <td ALIGN=\"LEFT\"><small>".
			prepare($this_physician->fullName())."&nbsp;</small></td>
                <td ALIGN=\"RIGHT\"><small>".bcadd ($r['procbalorig'], 0, 2)."</small></td>
                <td ALIGN=\"RIGHT\"><small>".bcadd ($r['procamtallowed'], 0, 2)."</small></td>
                <td ALIGN=\"RIGHT\"><small>".bcadd ($r['proccharges'], 0, 2)."</small></td>
                <td ALIGN=\"RIGHT\"><small>".bcadd ($r['procamtpaid'], 0, 2)."</small></td>
                <td ALIGN=\"RIGHT\"><small>".bcadd ($r['procbalcurrent'], 0, 2)."</small></td>
                <td ALIGN=\"LEFT\"><small>".(($r['procbilled']) ? __("Yes") : __("No") )."</small></td>
                <td ALIGN=\"LEFT\"><small>".( !empty($r['procdtbilled']) ?
					prepare($r['procdtbilled']) : "&nbsp;" )."</small></td>
                <td ALIGN=\"LEFT\"><a class=\"button\" ".
		"HREF=\"$this->page_name?action=addform".
                "&module=$module&been_here=1&patient=$patient&viewaction=ledger&item=".$r['id']."\"
                >Ledger</a>
                </tr>
                ";
            } // end looping for results

            $display_buffer .= "
            </table>
            <p/>
            <div ALIGN=\"CENTER\">
            <select NAME=\"viewaction\">
            <option VALUE=\"refresh\"  >".__("Refresh")."</option>
            <option VALUE=\"rebill\"  >".__("Rebill")."</option>
            <option VALUE=\"payment\" >".__("Payment")."</option>
            <option VALUE=\"copay\" >".__("Copay")."</option>
            <option VALUE=\"adjustment\" >".__("Adjustment")."</option>
            <option VALUE=\"deductable\" >".__("Deductable")."</option>
            <option VALUE=\"withhold\" >".__("Withhold")."</option>
            <option VALUE=\"transfer\">".__("Transfer")."</option>
            <option VALUE=\"allowedamt\">".__("Allowed Amount")."</option>
            <option VALUE=\"denial\"  >".__("Denial")."</option>
            <option VALUE=\"writeoff\"  >".__("Writeoff")."</option>
            <option VALUE=\"refund\">".__("Refund")."</option>
            <option VALUE=\"mistake\" >".__("Mistake")."</option>
            <option VALUE=\"ledgerall\">".__("Ledger")."</option>
            <option VALUE=\"paidledger\">".__("Ledger Closed")."</option>
            <option VALUE=\"closed\">".__("Closed")."</option>
            </select>
            <input class=\"button\" TYPE=\"SUBMIT\" ".
	    "VALUE=\"".__("Select Line Item")."\"/>
            <input TYPE=\"HIDDEN\" NAME=\"been_here\" VALUE=\"1\"/>
            </div>
            </form>
            ";
        } // end view function
		
		function insuranceSelectionByType($proccovmap) {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
			$returned_string = "";
			
			$cov_ids = explode(":",$proccovmap);

			$cnt = count($cov_ids);
			for ($i=0;$i<$cnt;$i++)
			{
				if ($i != 0)
				{
					if ($cov_ids[$i] != 0)
					{
						$insid = freemed::get_link_field($cov_ids[$i],"coverage","covinsco");
						$insname = freemed::get_link_field($insid,"insco","insconame");
						$returned_string .= "<OPTION VALUE=\"".$i."\">".$insname."\n";
					}
				}
				else
				{
					$returned_string .= "<OPTION VALUE=\"".$i."\">".__("Patient")."\n";
				}
			}
			return $returned_string;

		}

	function insuranceName($coverage) {
		$insid = freemed::get_link_field($coverage,"coverage","covinsco");
		return freemed::get_link_field($insid,"insco","insconame");
	}

	function coverageIDFromType($proccovmap, $type) {
		$cov_ids = explode(":",$proccovmap);
		return $cov_ids[$type];
	}

	function insuranceSelection($proccovmap) {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) global ${$k};
		$returned_string = "";
			
		$cov_ids = explode(":",$proccovmap);
		$cnt = count($cov_ids);
		for ($i=0;$i<$cnt;$i++) {
			if (($i != 0) and ($cov_ids[$i] != 0)) {
				$insid = freemed::get_link_field($cov_ids[$i],"coverage","covinsco");
				$insname = freemed::get_link_field($insid,"insco","insconame");
				$returned_string .= "<OPTION VALUE=\"".$cov_ids[$i]."\">".$insname."\n";
			}
		}
		return $returned_string;
	}

	function IsAuthorized($proc,&$remain,&$used) {
		global $display_buffer;
		global $sql;

		if ($proc[procauth] == 0)
		{
			$remain = $used = 0;
			return;
		}

		$auth_rec = freemed::get_link_rec($proc['procauth'],"authorizations");
		$remain = $auth_rec['authvisitsremain'];
		$used = $auth_rec['authvisitsused'];
	}

} // end class PaymentModule

register_module("PaymentModule");

?>
