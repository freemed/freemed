<?php
	// $Id$

LoadObjectDependency('_FreeMED.BillingModule');

class PostChecks extends BillingModule {

	// override variables
	var $MODULE_NAME = "Post Checks";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	function PostChecks ( ) {
		$this->_SetHandler('BillingFunctions', 'view');
		$this->_SetMetaInformation('BillingFunctionName', __("Post Checks"));
		$this->_SetMetaInformation('BillingFunctionDescription', __("Assign money from received checks to patient accounts and procedures in the system."));
		$this->BillingModule ( );
	} // end method PostChecks

	function view () {
		global $display_buffer;
		global $patient;
		global $action; global $billing_action;
		global $first_name;
		global $last_name;
		global $DOS;

		if($_POST['unpostable_amount']!=null)// This means that we are searching for the first time
		{

			$unpostable_amount=$_POST['unpostable_amount'];
			$_SESSION['unpostable_amount']=$unpostable_amount;

		}else{
			global $unpostable_amount;

		}

		if($_POST['checkpayer']!=null)// This means that we are searching for the first time
		{
			$_SESSION['checkpayer']=$_POST['checkpayer'];	// so we set all the session variables
			$_SESSION['checkpayername']=$_POST['checkpayername'];	
			$_SESSION['check_amount']=$_POST['check_amount'];	
			$_SESSION['check_number']=$_POST['check_number'];
			$checkpayer=$_POST['checkpayer'];	// and all the local variables
			$checkpayername=$_POST['checkpayername'];	
			$check_amount=$_POST['check_amount'];	
			$check_number=$_POST['check_number'];
				
		}else{
			$checkpayer=$_SESSION['checkpayer'];	//Otherwise we simply use the Session variables...
			$checkpayername=$_SESSION['checkpayername'];	
			$check_amount=$_SESSION['check_amount'];	
			$check_number=$_SESSION['check_number'];
		}
		global $check_amount;
		global $check_number;
		global $procedure_string;
//		global $SPostArray;// I need to move away from Posting this core variable to using Sessions
		global $payment;
		global $copay;
		global $disallow;
		global $procedure;
		global $procname;
		global $removeproc;
		global $total;
		global $cancel;

		global $selectpayer;
		if($selectpayer==null)
		{
			$selectpayer=$checkpayer;
		}	

		$buffer = "";

	// Display header

	$display_buffer .= $this->calinclude();
	$display_buffer .= "

	<div align='center'>
	<table cellpadding='0' cellspacing='0' width='740'>
		<tr>
			<td class='section'>".__("Post Checks")."</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
	</table>
	<br>
	";

// This section is the initial posting screen...
if((($billing_action==NULL)||($billing_action=="poststart")) and !$_SESSION['SPostArray_SESSION']){

        $display_buffer .= "
		</table>
			<form method='POST' name='billingpost'>
			<input type=\"hidden\" name=\"module\" value=\"".$_REQUEST['module']."\" />
			<input type=\"hidden\" name=\"action\" value=\"".$_REQUEST['action']."\" />
			<input type=\"hidden\" name=\"type\" value=\"".$_REQUEST['type']."\" />
			<input type=\"hidden\" name=\"billing_action\" value=\"postsearch\" />
		<table border='0' cellpadding='0' cellspacing='1' width='740'>
	        <tr bgcolor='#ffffff'>
                        <td class='Data' width='25%'> Select Payor </td>
                        <td class='Data' width='10%'> </td>
                        <td class='Data' width='20%'> Enter Payment Amount</td>
                        <td class='Data' width='10%'> </td>
                        <td class='Data' width='20%'> Enter Check Number </td>
                </tr>
	        <tr bgcolor='#ffffff'>
                        <td class='Data' width='25%'>	";

	$cov = CreateObject('FreeMED.Coverage');
	$payers = $cov->get_payers();

	$display_buffer .= $this->payer_selector($payers,"checkpayer");
				
	$display_buffer .= "
 	</td>
	
                        <td class='Data' width='10%'> </td>
                        <td class='Data' width='20%'> <input type='text' name='check_amount'></td>
                        <td class='Data' width='10%'> </td>
                        <td class='Data' width='20%'> <input type='text' name='check_number'></td>
                </tr>
		</table><br\><br\><br\>

			</table>
		<table border='0' cellpadding='0' cellspacing='1' width='740'>
	        <tr bgcolor='#ffffff'>
                        <td class='Data' width='20%'> Patient Last Name </td>
                        <td class='Data' width='20%'> First Name </td>
                        <td class='Data' width='20%'> </td>
                        <td class='Data' width='20%'> Enter DOS </td>
                </tr>
	        <tr bgcolor='#ffffff'>
                        <td class='Data' width='20%'> <input type='text' name='last_name'></td>
                        <td class='Data' width='20%'> <input type='text' name='first_name'></td>
                        <td class='Data' width='20%'> </td>
                        <td class='Data' width='20%'> ";

	$display_buffer .= $this->calhtml("DOS");

	$display_buffer .= "
					</td>
                </tr>
		</table><br\><br\><br\>


		<table>
		<tr bgcolor='#ffffff'>
                        <td class='Data' width='65%'>&nbsp;</td>
                        <td class='Data' width='15%'><input type='submit' name='initiate' value=' Initiate Posting  '></td>
                        <td class='Data' width='20%'></td>
		</tr>	
		</table>
</form>
        ";

	$display_buffer .= $this->caljavascript("DOS");

}                                                                                                         
if($billing_action=="postsearch" ||($billing_action==null&&$_SESSION['SPostArray_SESSION']!=null)) {

		LoadObjectDependency('FreeMED.Patient');
		$Proc = CreateObject('FreeMED.Procedure');
		$patientidlist = Patient::get_list_by_name( $last_name, $first_name );
		//print "patient id list = "; print_r($patientidlist); print "<hr/>\n";
		$pay = CreateObject('FreeMED.InsuranceCompany', $checkpayer);
		$checkpayername=$pay->get_name();
		$unposted=$check_amount;
	
		//print "<br/><br/><hr/>\n";
		foreach($patientidlist as $patientrecord)
		{
		$patientid=$patientrecord;
		$_procs = $Proc->get_open_procedures_by_date_and_patient($patientid,$DOS);
		//if (is_array($_procs)) { print "_procs = "; print_r($_procs); print "<hr/>\n"; }
		$proclist = array_merge($proclist, $_procs);

		}
		foreach($proclist as $procrecord)
		{
			$procdate=$procrecord["procdt"];
			$procpatient=$procrecord["procpatient"];

			//TODO this should be replaced with 
			// the SELECTED Payer as opposed to checkpayer...
			if($Proc->check_for_payer($procrecord["id"],$selectpayer))
			{
			//print "!      This procedure matches the payer <br>"; //visualize the search!
			$print_procs[]=$procrecord;
			}

		}

	$debug=false;
	if($debug)
	{
		$display_buffer .= " billing_action $billing_action<br>";
		$display_buffer .= " check_amount $check_amount <br>";
		$display_buffer .= " check_number $check_number<br>";
		$display_buffer .= " patient first name $first_name<br>";
		$display_buffer .= " patient last name $last_name <br>";
		$display_buffer .= " calculated patient id $patientid <br>";
		$display_buffer .= " DOS $DOS<br>";	
		$display_buffer .= " checkpayer $checkpayer<br>";	
		$display_buffer .= " checkpayername $checkpayername<br>";	
		$display_buffer .= " payment $payment<br>";	
		$display_buffer .= " disallow $disallow<br>";	
		$display_buffer .= " copay $copay<br>";	
		$display_buffer .= " total $total<br>";	
		$display_buffer .= " procedure $procedure<br>";	
		$display_buffer .= " procname $procname<br>";	
	//	$display_buffer .= " SPostArray id $SPostArray<br>";	
		$display_buffer .= " SPostArray_SESSION id ".$_SESSION['SPostArray_SESSION']."<br>";	
	}
	//$PostArray=unserialize(urldecode($SPostArray));
	$PostArray=unserialize(urldecode($_SESSION['SPostArray_SESSION']));	
if($procedure!=NULL&&(strcmp($cancel,' Cancel ')!=0))
{
	//print "inside the adder";
	//print_r($PostArray);
	if(($disallow<0)||($copay<0)||($payment>$total))
	{

		$display_buffer .= "<H1>Post Impossible</H1><br>
			It is not possible to post where <br>
				the disallowance is $disallow <br>
				the copay is $copay <br>
				or the payment is $payement of the total $total<br>
				please try again<br><br>";	
		
	}else{
	$PostArray[]=array("name"=>$procname,"total"=>$total,"disallow"=>$disallow,"copay"=>$copay,"paid"=>$payment,"procedure"=>$procedure);
	}
//	$SPostArray=urlencode(serialize($PostArray));
	$_SESSION['SPostArray_SESSION']=urlencode(serialize($PostArray));
}

if($removeproc!=NULL)// this procedure is to be removed.
{

		$remove_ar=split(" ",$removeproc);
		$removeme=$remove_ar["1"];

	//print_r($PostArray);
	foreach($PostArray as $key => $procarray)
	{
		if($procarray["procedure"]==$removeme)
		{
			unset($PostArray[$key]);
		}
	}
//	$SPostArray=urlencode(serialize($PostArray));
	if(count($PostArray)>0)// if it is empty we need to erase it...
	{
		$_SESSION['SPostArray_SESSION']=urlencode(serialize($PostArray));
	}else{
		$display_buffer .= "Unset SPostArray <br>";	
		$_SESSION['SPostArray_SESSION']=null;
	}
}

	foreach($PostArray as $procarray)
	{
		// this is so that we do not display any procedures that we have already choosen...
		$procdiffarray[]=$procarray["procedure"];	
	}

	
$display_buffer .= $this->post_progress($_SESSION['SPostArray_SESSION'],$check_number,$check_amount,$checkpayername,$checkpayer,$unpostable_amount);



                       $display_buffer .= "
</br>
		  
		<table border='1' cellpadding='1' cellspacing='1' width='740'>
		 <tr bgcolor='#ffffff'>
                        <td class='Data' width='25%'> Procedure </td>
                        <td class='Data' width='30%'> Patient (ID) </td>
                        <td class='Data' width='15%'> Date of Service</td>
                        <td class='Data' width='15%'> CPT </td>
                        <td class='Data' width='15%'> Amount Outstanding </td>
                </tr>
		</table>
	";



       $display_buffer .= "

		<form method='POST' name='billingpost'>
			<input type=\"hidden\" name=\"module\" value=\"".$_REQUEST['module']."\" />
			<input type=\"hidden\" name=\"action\" value=\"".$_REQUEST['action']."\" />
			<input type=\"hidden\" name=\"type\" value=\"".$_REQUEST['type']."\" />
			<input type=\"hidden\" name=\"checkpayername\" value=\"".prepare($checkpayername)."\" />"

	/*	."<input type='hidden' name='check_amount' value='$check_amount'>
		<input type='hidden' name='check_number' value='$check_number'>
		<input type='hidden' name='checkpayer' value='$checkpayer'>
		<input type='hidden' name='SPostArray' value='$SPostArray'>"*/

		."<table border='0' cellpadding='0' cellspacing='1' width='740'>";

	$i=0;

	if($print_procs==NULL)
	{
		$post_line= "<br><br> No Procedures Found! <br> Please Re-Search <br><br>";
	}

	foreach($print_procs as $proc)
	{
		
		if(!(in_array($proc["id"],$procdiffarray)))// if we have already choosen this procedure, lets not choose it again.
		{
		$pat_obj=CreateObject('FreeMED.Patient',$proc["procpatient"]);
		
		$name = $pat_obj->fullname()."(".$proc["procpatient"].")";	
		$date_ar=split("-",$proc["procdt"]);
		$print_date=$date_ar["0"]."/".$date_ar["1"]."/".$date_ar["2"];
		if($i==1)
		{
			$i=0;
			$color="#E4CDC3";
		}else{
			
			$i=1;
			$color="#ffffff";
		}	

		$cpt_code= freemed::get_link_field('cptnameint', 'cpt', $proc["proccpt"]);
	
		$post_line .= $this->posting_tr($name,$print_date,$proc["procpatient"],$proc["id"],$cpt_code,$proc["procbalcurrent"],$color);
		}
	}


       $display_buffer .= $post_line;

       $display_buffer .= "
		</table></form>";
	

       $display_buffer .= "
		<form method='POST' name='billingpost'>
			<input type=\"hidden\" name=\"billing_action\" value=\"postsearch\" />
			<input type=\"hidden\" name=\"module\" value=\"".$_REQUEST['module']."\" />
			<input type=\"hidden\" name=\"action\" value=\"".$_REQUEST['action']."\" />
			<input type=\"hidden\" name=\"type\" value=\"".$_REQUEST['type']."\" />"

	/*	."<input type='hidden' name='SPostArray' value='$SPostArray'>
		<input type='hidden' name='check_amount' value='$check_amount'>
		<input type='hidden' name='check_number' value='$check_number'>
		<input type='hidden' name='checkpayer' value='$checkpayer'>
		<input type='hidden' name='checkpayername' value='$checkpayername'>"*/

		."<table border='0' cellpadding='0' cellspacing='1' width='740'>
		<tr bgcolor='#ffffff'>
			   <td class='Data' width='20%'> 
					";

	$cov = CreateObject('FreeMED.Coverage');
	$payers = $cov->get_payers();

	$display_buffer .= $this->payer_selector($payers,"selectpayer");

       $display_buffer .= "
                     							</td>
                        <td class='Data' width='20%'> <input type='text' name='last_name'></td>
                        <td class='Data' width='20%'> <input type='text' name='first_name'></td>
                        <td class='Data' width='20%'> ";


	$display_buffer .= $this->calhtml("DOS");

	$display_buffer .= "</td>
			<td class='Data' width='20%'><input type='submit' value='Modify Search'></td>
		</tr>
		<tr bgcolor='#ffffff'>
		        <td class='Data' width='20%'>Payer</td>
		        <td class='Data' width='20%'>Last Name</td>
                        <td class='Data' width='20%'>First Name</td>
                        <td class='Data' width='20%'>DOS</td>
                        <td class='Data' width='20%'></td>
		</tr>
		</table>


		</form>
		";


	
	$display_buffer .= $this->caljavascript("DOS");

} 

if($billing_action=="finalpost") {

	$debug=false;

	//$PostArray=unserialize(urldecode($SPostArray));	
	$PostArray=unserialize(urldecode($_SESSION['SPostArray_SESSION']));	

	if($debug)
	{
		$display_buffer .= " paymentamount $check_amount <br>";
		$display_buffer .= " checknumber $check_number<br>";
		$display_buffer .= " checkpayer $checkpayer<br>";	
		$display_buffer .= " checkpayername $checkpayername<br>";	
//		$display_buffer .= " SPostArray $SPostArray<br>";	
		$display_buffer .= " SPostArray_SESSION id ".$_SESSION['SPostArray_SESSION']."<br>";	
		foreach($PostArray as $postrecord)
		{
		$display_buffer .= " SPostArray record id ".$postrecord["procedure"]."<br>";	
		}			
	}

		$Proc = CreateObject('FreeMED.Procedure');
		$Ledger = CreateObject('FreeMED.Ledger');
		$cl = CreateObject('FreeMED.ClaimLog');
	
		 $totalposted = 0;
		$display_buffer .= " <table width='300'>";
		if(strcmp($unpostable_amount,'')!=0)
		{
		$totalposted=$unpostable_amount;
		$display_buffer .= " <tr><td>".__("Unpostable")."</td>\n".
			"<td>".__("Amount Unpostable")."</td>\n";
		$display_buffer .= " <tr><td>&nbsp;</td>\n".
			"<td>$".bcadd($unpostable_amount,0,2)."</td>\n";
			//add unpostable value here...
		/*
			$Ledger->unpostable(
				$check_number,
				$unpostable_amount,
				"unpostable from $check_number");
		*/	

		}
		
		$display_buffer .= "<tr>\n".
			"<td class=\"DataHead\">".__("Procedure")."</td>\n".
			"<td class=\"DataHead\">".__("Amount Posted")."</td>\n";
		foreach($PostArray as $postrecord)
		{
			$procrecord=$Proc->get_procedure($postrecord["procedure"]);
			//$coverageid = $Proc->coverage_of_payer($procrecord["id"],$checkpayer);
			// Post the check in the ledger
			$Ledger->post_payment_check(
				$procrecord['id'],
				$procrecord['proccurcovid'], //$coverageid,
				$check_number,
				$postrecord['total'],
				"bulk post from $check_number"
			);
			// Log check received in the claim log
			$cl->log_event(
				$procrecord['id'],
				array(
					'action' => __("Check Received"),
					'comment' => sprintf(__("Check #%s"),
						$check_number)
				)
			);

			// Reload the procedure record, so we can get the
			// amount left
			$procrecord = $Proc->get_procedure($postrecord["procedure"]);
			if ($procrecord['procbalcurrent'] > 0) {
				// Move to next payer and rebill
				$Ledger->move_to_next_coverage($procrecord['id'], $postrecord['disallow']);
			} // end move to next payer
				
			$display_buffer .= " <tr>\n";
			$display_buffer .= " <td>".
				$postrecord["procedure"]."</td>\n".
				"<td>".bcadd($postrecord['total'],0,2)."</td>\n";	
			$display_buffer .= " </tr>\n";
			$totalposted = $totalposted + $postrecord["total"];			
		}
		
		$display_buffer .= "<tr><td><b>".__("Total Posted").
			":</td><td>".bcadd($totalposted,0,2)."</td></tr>";	
		$display_buffer .= " </table><br><br><br><br><br><br>";

$display_buffer .= " 
	<table>
	<tr>
		<td><a class=\"button\" href=\"".page_name()."?".
			"module=".$_REQUEST['module']."&".
			"action=".$_REQUEST['action']."&".
			"type=".$_REQUEST['type']."\">".
			__("Post Another")."</a></td>
		<td><a class=\"button\" href=\"main.php\">Main Menu</a></td>
		<td><a class=\"button\" href=\"#\">Posting Report</a></td>
	</tr>
	</table>	";


		$_SESSION['SPostArray_SESSION'] = null;
		$_SESSION['unpostable_amount'] = null;

}


if($billing_action=="unpostable") {


		$display_buffer .= "
		<form method='POST' name='unpostable'>
			<input type=\"hidden\" name=\"module\" value=\"".$_REQUEST['module']."\" />
			<input type=\"hidden\" name=\"type\" value=\"".$_REQUEST['type']."\" />
			<input type=\"hidden\" name=\"action\" value=\"".$_REQUEST['action']."\" />
			<input type=\"hidden\" name=\"billing_action\" value=\"postsearch\" />
		<table border='0' cellpadding='0' cellspacing='1' width='740'>
		<tr>
		<td class='Data' width='15%'>
		Unpostable
		</td>
		<td class='Required' width='85%'>
		Warning: If a portion of a check is marked as unpostable, direct Ledger access will be required to undo this
		</td>
		</tr>
		<tr>
		<td class='Data' width='15%'>
		Unpostable Amount
		</td>
		<td class='Data' width='15%'>
		<input type='text' name='unpostable_amount' id='unpostable_amount'>	
		</td>
		</tr>
		<tr><td>
		<input type='submit' value='Unpostable'>
		</td></tr>
		</table>
		</form>
			";

}

         
if($billing_action=="postcheck") {


		$proc_ar=split(" ",$procedure_string);
		$proc_id=$proc_ar["2"];
	$debug=false;
	if($debug)
	{
		$display_buffer .= " billing_action $billing_action<br>";
		$display_buffer .= " paymentamount $check_amount <br>";
		$display_buffer .= " checknumber $check_number<br>";
		$display_buffer .= " name $name <br>";
		$display_buffer .= " calculated patient id $patientid <br>";
		$display_buffer .= " DOS $DOS<br>";	
		$display_buffer .= " checkpayer $checkpayer<br>";	
		$display_buffer .= " checkpayername $checkpayername<br>";	
		$display_buffer .= " calculated procedure id $proc_id<br>";	
	//	$display_buffer .= " SPostArray id $SPostArray<br>";	
		$display_buffer .= " SPostArray_SESSION id ".$_SESSION['SPostArray_SESSION']."<br>";	

			
	}


		$Proc_obj = CreateObject('FreeMED.Procedure', $proc_id);
		$proc=$Proc_obj->get_procedure();
		//print "proc ($proc_id) = "; print_r($proc); print "<br/>\n";
		//print "proc[procpatient] = ".$proc['procpatient']."<br/>\n";
		$pat_obj=CreateObject('FreeMED.Patient',$proc["procpatient"]);
		$name = $pat_obj->fullname()."(".$proc["procpatient"].")";	
		$print_date=str_replace('-', '/', $proc['procdt']);
		$patientid=$proc["procpatient"];
		$owed=$proc["procbalcurrent"];
		


	
		$display_buffer .= $this->post_progress (
			$_SESSION['SPostArray_SESSION'],
			$check_number,
			$check_amount,
			$checkpayername,
			$checkpayer,
			$unpostable_amount
		);


//Payment Summary...


                       $display_buffer .= "
<form METHOD='POST' id='MyForm' name='MyForm'>	
			<input type=\"hidden\" name=\"module\" value=\"".$_REQUEST['module']."\" />
			<input type=\"hidden\" name=\"type\" value=\"".$_REQUEST['type']."\" />
			<input type=\"hidden\" name=\"action\" value=\"".$_REQUEST['action']."\" />
		<table border='0' cellpadding='0' cellspacing='1' width='740'>
		 <tr >"

	/*	."<input type='hidden' name='check_amount' value='$check_amount'>
		<input type='hidden' name='check_number' value='$check_number'>
		<input type='hidden' name='checkpayer' value='$checkpayer'>
		<input type='hidden' name='checkpayername' value='$checkpayername'>
		<input type='hidden' name='SPostArray' value='$SPostArray'>"*/

                        ."<td class='Data' width='20%'>
<a href=\"manage.php?&patient=&action=display&id=$patientid\"> $name </a></td>
                        <td class='Data' width='20%'><a href=\"module_loader.php?module=proceduremodule&patient=$patientid&action=modform&id=$proc_id\">09/09/03 </a> </td>
                        <td class='Data' width='20%'>$$owed</td>
                        <td class='Data' width='20%'>
				<input type='text' name='payment' id='payment' onChange='calculate_disallow();'></td>
                        <td class='Data' width='20%'>
				<input type='text' name='copay' id='copay' onChange='calculate_disallow();'></td>
                        <td class='Data' width='20%'>
				<input type='text' name='disallow' id='disallow' readonly='1' value='0'></td>
			
                </tr>
		 <tr >
                        <td class='Data' width='20%'>&nbsp;</td>
                        <td class='Data' width='20%'>&nbsp;</td>
                        <td class='Data' width='20%'>Charges</td>
                        <td class='Data' width='20%'>Payments</td>
                        <td class='Data' width='20%'>Copay</td>
                        <td class='Data' width='20%'>Disallowances</td>
			
                </tr>

		</table> </br>
		<table>
		<tr bgcolor='#ffffff'>
                        <td class='Data' width='10%'><input type='hidden' name='billing_action' value='postsearch'/></td>
                        <td class='Data' width='10%'><input type='hidden' name='procedure' value='$proc_id'/></td>
                        <td class='Data' width='10%'><input type='hidden' name='procname' value='$name'/></td>
                        <td class='Data' width='10%'><input type='hidden' name='total' value='$owed'/></td>
                        <td class='Data' width='10%'><input type='submit' value=' Post '></td>
                        <td class='Data' width='50%'><input type='submit' name='cancel' value='".__("Cancel")."'></td>
		</tr>	
		</table>
</form>
<script type=\"text/javascript\">


function calculate_disallow(){

	var my_payments_object=document.MyForm.payment;
	var my_payments=my_payments_object.value;
	var my_copay_object=document.MyForm.copay;
	var my_copay=my_copay_object.value;
	var my_disallow_object=document.MyForm.disallow;
	
// I can just drop the total value in with php :)

	if((my_copay=='') && (my_payments==''))
	{
		var my_subtract = 0;
	}else{
		if(my_payments=='')
		{	
			var my_subtract = parseInt(my_copay);
		} else {
			if(my_copay=='')
			{	
				var my_subtract = parseInt(my_payments);
			}else{
				var my_subtract = parseInt(my_copay)+parseInt(my_payments);	
			}	
		}
	}
	var my_owed = $owed;
	my_disallow_raw = Math.round((my_owed - my_subtract) * 100);
	if (my_disallow_raw != 0) {
		my_disallow = my_disallow_raw / 100;
	} else {
		my_disallow = '0.00';
	}
	my_disallow_object.value=my_disallow;
}

</script>

";
}

		return $buffer;
	} // end function view
// Functions

function payer_selector($payer_list,$selectname)
{


$return = "	
	<select name='$selectname' id='$selectname'>
 ";
	//print_r($payer_list);
foreach($payer_list as $payer_id)
{
	$checkpayer = CreateObject('FreeMED.InsuranceCompany', $payer_id);
        $name=$checkpayer->get_name();
$return .=	" 		<option value='$payer_id' >$name</option>
 ";
}
$return .=	"
	</select>";
return($return);
}




function posting_tr($Patient,$ProcDOS,$PatientID,$ProcID,$CPT,$amount,$color)
{

return ("
		<table border='0' cellpadding='0' cellspacing='1' width='740'>
		 <tr bgcolor=$color>
                        <td class='Data' width='25%'>
<input type='hidden' name='billing_action' value='postcheck'>
<input type='hidden' name='action' value='".$_REQUEST['action']."'/>
<input type='hidden' name='type' value='".$_REQUEST['type']."'/>
<input type='hidden' name='module' value='".$_REQUEST['module']."'/>

<input type='submit' name='procedure_string' value='Choose Procedure $ProcID'></td>
                        <td class='Data' width='30%'>
<a href=\"manage.php?&patient=&action=display&id=$PatientID&module\"> $Patient </a></td>
                        <td class='Data' width='15%'><a href=\"module_loader.php?module=proceduremodule&patient=$PatientID&action=modform&id=$ProcID\">$ProcDOS </a> </td>
                        <td class='Data' width='15%'>$CPT</td>
                        <td class='Data' width='15%' align='right'>$".bcadd($amount,0,2)."</td>
			
                </tr>
		</table>
");
}

/*
   Function: calinclude
	generates html <script> include tags needed by coolest DHTML calendar.

   Returns:
   	returns the html needed to link in the stylesheets and the javascript need to make the coolest DHTML
	calendar go. 
*/
function calinclude()
{
	return( "
  <link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"lib/template/default/calendar-system.css\" title=\"win2k-cold-1\" />

<script type=\"text/javascript\" src=\"lib/template/default/calendar_stripped.js\"></script>
<script type=\"text/javascript\" src=\"lib/template/default/calendar-en.js\"></script>
<script type=\"text/javascript\" src=\"lib/template/default/calendar-setup_stripped.js\"></script>
");
}

/*
   Function: post_progress

   Returns:
*/
function post_progress($My_S_Array,$checkid,$checkamount,$checkpayername,$checkpayerid,$unpostable_amount)
{

$My_Array=unserialize(urldecode($My_S_Array));

	$total_paid=0;
foreach($My_Array as $row_array)
{
	$total_paid=$total_paid+$row_array["paid"];

}

if(strcmp($unpostable_amount,'')!=0)
{
	$total_paid=$total_paid+$unpostable_amount;
}
	$total_unpaid=$checkamount-$total_paid;


if($total_unpaid==0)
{
$to_return .= "<H1> Check Balanced </H1>";
}

$to_return .= "<H1>".sprintf(__("Check #%s [ From %s ]"), $checkid, $checkpayername)."</H1><br/>
	<form method='POST' name='billingpost'>
		<input type=\"hidden\" name=\"module\" value=\"".$_REQUEST['module']."\" />
		<input type=\"hidden\" name=\"type\" value=\"".$_REQUEST['type']."\" />
		<input type=\"hidden\" name=\"action\" value=\"".$_REQUEST['action']."\" />
		"
		/*."<input type='hidden' name='check_amount' value='$checkamount'>
		<input type='hidden' name='check_number' value='$checkid'>
		<input type='hidden' name='checkpayer' value='$checkpayerid'>
		<input type='hidden' name='checkpayername' value='$checkpayername'>
		<input type='hidden' name='SPostArray' value='$My_S_Array'>"*/

		."<table border='0' cellpadding='0' cellspacing='1' width='740'>
	       	<tr bgcolor='#ffffff'>
                        <td class='Data' width='20%' align='right'>Total Payments</td>
                        <td class='Data' width='20%' align='right'>$$checkamount </td>
                        <td class='Data' width='25%' align='right'>Total Unposted </td>
                        <td class='Data' width='25%' align='right'><font color='red'>$".bcadd($total_unpaid,0,2)."</font></td>
                </tr>	
		</table>";

$to_return .= "

 		</br>
		<table border='0' cellpadding='0' cellspacing='1' width='740'>
		<tr>
                        <td class='Data' width='20%'>Remove Line</td>
                        <td class='Data' width='20%'>Name</td>
                        <td class='Data' width='20%'>Orginal Charge</td>
                        <td class='Data' width='20%'>Disallowed</td>
                        <td class='Data' width='20%'>Copay</td>
                        <td class='Data' width='20%'>Paid</td>
                        <td class='Data' width='20%'></td>
		</tr>";

foreach($My_Array as $row_array)
{

	$loopdisallow = $row_array["disallow"];
	$loopcopay = $row_array["copay"];
	if(!isset($loopdisallow)){$loopdisallow="0";}
	if(!isset($loopcopay)){$loopcopay="0";}



	$to_return = $to_return."
		<tr>
                        <td class='Data' width='20%'> <input type='submit' name='removeproc' value='remove ".$row_array["procedure"]."'></td>
                        <td class='Data' width='20%'>".$row_array["name"]."</td>
                        <td class='Data' width='20%'>$".bcadd($row_array["total"],0,2)."</td>
                        <td class='Data' width='20%'>$".$loopdisallow."</td>
                        <td class='Data' width='20%'>$".$loopcopay."</td>
                        <td class='Data' width='20%'>$".$row_array["paid"]."</td>
                        <td class='Data' width='20%'></td>
		</tr>";
}

$to_return = $to_return."</table></form>";

if(strcmp($unpostable_amount,'')==0)
{
$to_return = $to_return."<table border='0' cellpadding='0' cellspacing='1' width='740'>
		<tr>
		<td class='Data' width='15%'>
		<form method='POST' name='unpostable'>	
		<input type=\"hidden\" name=\"action\" value=\"".$_REQUEST['action']."\"/>
		<input type=\"hidden\" name=\"module\" value=\"".$_REQUEST['module']."\"/>
		<input type=\"hidden\" name=\"type\" value=\"".$_REQUEST['type']."\"/>
		<input type=\"hidden\" name=\"billing_action\" value=\"unpostable\"/>
		<input type='submit' value='Unpostable'></td>
		</form>
		</td> <td class='Data' width='85%'></td></tr>
		</table>
			";
}else{

$to_return = $to_return."
			<table border='0' cellpadding='0' cellspacing='1' width='740'>
			<tr>
  <td class='Data' width='20%'> <input type='submit' name='removeunpostable' value='".__("Remove Unpostable")."'></td>
                        <td class='Data' width='60%'>&nbsp;</td>
                        <td align='right' class='Data' width='20%'>$".bcadd($unpostable_amount,0,2)."</td>
			</tr></table>
			";


}

if($total_unpaid==0)
{


$to_return = $to_return."<H1> Check Balanced </H1><br>
		<form method='POST' name='billingpost'>
		<input type=\"hidden\" name=\"action\" value=\"".$_REQUEST['action']."\"/>
		<input type=\"hidden\" name=\"module\" value=\"".$_REQUEST['module']."\"/>
		<input type=\"hidden\" name=\"type\" value=\"".$_REQUEST['type']."\"/>
		<input type=\"hidden\" name=\"billing_action\" value=\"finalpost\" />"
	/*	."<input type='hidden' name='check_amount' value='$checkamount'>
		<input type='hidden' name='check_number' value='$checkid'>
		<input type='hidden' name='checkpayer' value='$checkpayerid'>
		<input type='hidden' name='checkpayername' value='$checkpayername'>
		<input type='hidden' name='SPostArray' value='$My_S_Array'>".*/

."<input type='submit' value='".__("Post This Check")."'></td>
</form>
";
}

	if($total_unpaid<0) {
		$to_return = $to_return."<H1>".__("Check Misposted")."</H1><br/>".
			__("It is not possible to post more than the check amount.")."<br/>".
			__("Please remove procedures until the check is balanced")."<br/>\n";
	} // end if total_unpaid < 0


	$to_return = $to_return."<br><br><br>";
	return($to_return);
} // end method view






function calhtml($name)
{

$trigger = $name."_trigger";
return("
<table cellspacing='0' cellpadding='0' style='border-collapse: collapse'><tr>
 <td><input type='text' name='$name' id='$name' readonly='1' /></td>
 <td><img src='lib/template/default/img/calendar_widget.gif' id='$trigger' style='cursor: pointer; ' title='Date Selector'
      onmouseover=\"this.style.background='red';\" onmouseout=\"this.style.background='';\" /></td>
</table>");




}

function caljavascript($name)
{

$trigger = $name."_trigger";


return("
<script type=\"text/javascript\">
	

    Calendar.setup({
        inputField     :    \"$name\",     // id of the input field
        ifFormat       :    \"%Y-%m-%d\",      // format of the input field
        button         :    \"$trigger\",  // trigger for the calendar (button ID)
        align          :    \"Tl\",           // alignment (defaults to 'Bl')
        singleClick    :    true
    });
</script");

}


} // end class PostChecks

register_module("PostChecks");

?>
