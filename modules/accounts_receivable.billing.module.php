<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.BillingModule');

class AccountsReceivable extends BillingModule {

	var $MODULE_NAME = "Accounts Receivable";
	var $MODULE_AUTHOR = "jeff@ourexchange.net";
	var $MODULE_VERSION = "0.1";
	var $MODULE_HIDDEN = true;
	
	var $MODULE_FILE = __FILE__;

	function AccountsReceivable ( ) {
		// Add appropriate handler information
		$this->_SetHandler('BillingFunctions', 'search_form');
		$this->_SetMetaInformation('BillingFunctionName', __("Accounts Receivable"));
		$this->_SetMetaInformation('BillingFunctionDescription', __("Manage all transactions in the system."));

		// Call parent constructor
		$this->BillingModule();
	} // end constructor AccountsReceivable

	// Methods for non-elegant module_loader.php loading
	function view ( ) { return $this->search_form(); }
	function display ( ) { return $this->search_form(); }

	function search_form ( ) {
		// Switchboard
		switch ($_REQUEST['submit_action']) {
			case __("Item Detail"):
			case 'item_detail':
			return $this->item_detail( );
			break; // item details

			case __("Add Event"):
			return $this->event_add( );
			break; // item event add

			case __("Post Payment"):
			return $this->post_payment_form( );
			break; // post check form

			case __("Post"):
			return $this->post_payment( );
			break; // actual post check

			case __("Writeoff"):
			return $this->writeoff_items( );
			break; // mark billed

			case __("Narrow Search"):
			case __("Search Items"):
			case __("Return to Search"):
			case 'search_items':
			if (is_array($_REQUEST['criteria']) and
				count($_REQUEST['criteria']) > 0 ) {
				return $this->search_engine( );
			}
			break; // search engine
		} // end switchboard

		// Show header
		global $display_buffer;
		$display_buffer .= "<div class=\"section\">".
			__("Accounts Receivable").": ".
			__("Search")."</div>\n";

		// Get rid of stale data from last search
		unset($GLOBALS['criteria']);
		unset($_REQUEST['criteria']);

		// Instantiate HTML_QuickForm
		$search_form = CreateObject('PEAR.HTML_QuickForm', 'aging', 'post');
		freemed::quickform_i18n(&$search_form);

		// Add module hidden variables
		$search_form->addElement('hidden', 'module', $_REQUEST['module']);
		$search_form->addElement('hidden', 'type', $_REQUEST['type']);
		$search_form->addElement('hidden', 'action', $_REQUEST['action']);

		$search_form->setDefaults(array(
			'criteria[last_name]' => '',
			'criteria[first_name]' => '',
			'criteria[patient]' => '',
			'criteria[provider]' => '',
			'criteria[aging]' => ''
		));

		$aging_radio[] = &HTML_QuickForm::createElement(
			'radio', 'criteria[aging]', '', '120+', '120+');
		$aging_radio[] = &HTML_QuickForm::createElement(
			'radio', 'criteria[aging]', '', '91-120', '91-120');
		$aging_radio[] = &HTML_QuickForm::createElement(
			'radio', 'criteria[aging]', '', '61-90', '61-90');
		$aging_radio[] = &HTML_QuickForm::createElement(
			'radio', 'criteria[aging]', '', '31-60', '31-60');
		$aging_radio[] = &HTML_QuickForm::createElement(
			'radio', 'criteria[aging]', '', '0-30', '0-30');
		$aging_radio[] = &HTML_QuickForm::createElement(
			'radio', 'critiera[aging]', '', __("No Search"), '');
		//$search_form->addGroup($aging_radio, null, __("Aging"),
		//	'&nbsp;');

		$search_form->addElement(
			'header', '', __("Patient Criteria") );
	
		$patient_element[] = &HTML_QuickForm::createElement(
			'text', 'criteria[last_name]', __("Last Name"),
			array ( 'size' => 25, 'maxlength' => '50' ) );
		$patient_element[] = &HTML_QuickForm::createElement(
			'text', 'criteria[first_name]', __("First Name"),
			array ( 'size' => 25, 'maxlength' => '50' ) );
		$search_form->addGroup($patient_element, null, 
			__("Name (Last, First)"), '&nbsp;');

		$search_form->addElement(
			'header', '', __("Claim Criteria") );
		
		// Sort by procedure status
		//$search_form->addElement(
		//	'select', 'criteria[status]', __("Claim Status"),
		//	$cl->procedure_status_list ( ) );
		$search_form->addElement(
			'select', 'criteria[provider]', __("Provider"),
			array_flip ( $this->_provider_list() )
		);

		// Date of service
		$search_form->addElement(
			'static', 'criteria[date]', __("Date of Service"),
			fm_date_entry('criteria[date]') );

		// Add search/submit group
		$submit_group[] = &HTML_QuickForm::createElement(
			'submit', 'submit_action', __("Search Items"));
		$submit_group[] = &HTML_QuickForm::createElement(
			'submit', 'submit_action', __("Cancel"));
		$search_form->addGroup($submit_group, null, null, '&nbsp;');

		// Dump the buffer back to the display
		$GLOBALS['display_buffer'] .= $search_form->toHtml( );
	} // end method search_form

	function search_engine ( ) {
		global $display_buffer;
		$cl = CreateObject('FreeMED.ClaimLog');
		$l = CreateObject('FreeMED.Ledger');
		$display_buffer .= "<div class=\"section\">".
			__("Accounts Receivable").': '.
			__("Search")."</div>\n";
		$table = CreateObject('PEAR.HTML_Table', array (
			'width' => '90%',
			'cellspacing' => '0',
			'style' => 'border: 1px solid; border-color: #000000;'	
		));

		foreach ($_REQUEST['criteria'] AS $k => $v) {
//			print "(criteria) k = $k, v = $v<hr/>\n";
		}

		//----- Create main table
		$display_buffer .= "<table border=\"0\" width=\"90%\" ".
			"style=\"border: 1pt solid;\"><tr>".
			"<td width=\"65%\" style=\"border: 1pt solid;\">\n".
			"<center>".
			"<big><b><u>".__("Item Criteria")."</u></b></big>".
			"</center>".
			"<br/>\n";

		//----- Search form
		$search_form = CreateObject('PEAR.HTML_QuickForm', 'search', 'post');
		freemed::quickform_i18n(&$search_form);

		// Add module hidden variables
		$search_form->addElement('hidden', 'module', $_REQUEST['module']);
		$search_form->addElement('hidden', 'type', $_REQUEST['type']);
		$search_form->addElement('hidden', 'action', $_REQUEST['action']);

		// Hide elements to be passed along to give impression of persistence
		foreach ($_REQUEST['criteria'] AS $k => $v) {
			switch ($k) {
				case 'provider':
				case 'last_name':
					// All selectable items must have their
					// defaults set, otherwise they will
					// lose their values.
					$search_form->setDefaults(array(
						'criteria['.$k.']' => $v
					));
					break;

				default:
					// If not in the form, hide the value.
					$search_form->addElement('hidden', 'criteria['.$k.']', $v);
					break;
			} // end not supposed to be hidden
		} // end foreach

		// Actual form goes here
		/*
		$search_radio[] = &HTML_QuickForm::createElement(
			'radio', 'criteria[aging]', '', '120+', '120+',
			( $_REQUEST['criteria']['aging']=='120+' ?
			array('checked'=>'checked') : null ) );
		$search_radio[] = &HTML_QuickForm::createElement(
			'radio', 'criteria[aging]', '', '91-120', '91-120',
			( $_REQUEST['criteria']['aging']=='91-120' ?
			array('checked'=>'checked') : null ) );
		$search_radio[] = &HTML_QuickForm::createElement(
			'radio', 'criteria[aging]', '', '61-90', '61-90',
			( $_REQUEST['criteria']['aging']=='61-90' ?
			array('checked'=>'checked') : null ) );
		$search_radio[] = &HTML_QuickForm::createElement(
			'radio', 'criteria[aging]', '', '31-60', '31-60',
			( $_REQUEST['criteria']['aging']=='31-60' ?
			array('checked'=>'checked') : null ) );
		$search_radio[] = &HTML_QuickForm::createElement(
			'radio', 'criteria[aging]', '', '0-30', '0-30',
			( $_REQUEST['criteria']['aging']=='0-30' ?
			array('checked'=>'checked') : null ) );
		$search_radio[] = &HTML_QuickForm::createElement(
			'radio', 'criteria[aging]', '', __("No Search"), '',
			( $_REQUEST['criteria']['aging']=='' ?
			array('checked'=>'checked') : null ) );
		$search_form->addGroup($search_radio, null, 
			__("Aging"), '&nbsp;');
		*/
		$search_form->addElement('text', 'criteria[last_name]',
			__("Last Name"), array ( 'size' => 25, 'maxlength' => '50' ) );
		$search_form->addElement(
			'select', 'criteria[provider]', __("Provider"),
			array_flip($this->_provider_list( )) );

		$submit_group[] = &HTML_QuickForm::createElement(
			'submit', 'submit_action', __("Narrow Search") );
		$submit_group[] = &HTML_QuickForm::createElement(
			'submit', 'submit_action', __("New Search") );
		$search_form->addGroup($submit_group, null, null, '&nbsp;');
		
		$display_buffer .= $search_form->toHtml();

		$display_buffer .= "</td>\n";

		//----- Process criteria arguments ...
		$display_buffer .= "<td valign=\"top\" align=\"right\" ".
			"width=\"35%\" style=\"border: 1pt solid;\">\n".
			"<center>".
			"<big><b><u>".__("Current Criteria")."</u></b></big>".
			"</center>".
			"<br/>\n";
		if ($_REQUEST['criteria']['patient'] and !$_REQUEST['criteria']['last_name'] and !$_REQUEST['criteria']['first_name']) {
			$display_buffer .= "<b>".__("Patient").":".
				"</b> ".$this->_lookup_patient($_REQUEST['criteria']['patient'])."</b> ".
				"<a href=\"".$this->_search_link(array(
					'patient' => ''	
				))."\" class=\"remove_link\">X</a><br/>\n";
			$criteria['patient'] = $_REQUEST['criteria']['patient'];
		} // patient
		if ($_REQUEST['criteria']['last_name']) {
			$display_buffer .= "<b>".__("Last Name like").":".
				"</b> ".$_REQUEST['criteria']['last_name']."</b> ".
				"<a href=\"".$this->_search_link(array(
					'last_name' => ''	
				))."\" class=\"remove_link\">X</a><br/>\n";
			$criteria['last_name'] = $_REQUEST['criteria']['last_name'];
		} // last name
		if ($_REQUEST['criteria']['first_name']) {
			$display_buffer .= "<b>".__("First Name like").":".
				"</b> ".$_REQUEST['criteria']['first_name']."</b> ".
				"<a href=\"".$this->_search_link(array(
					'first_name' => ''	
				))."\" class=\"remove_link\">X</a><br/>\n";
			$criteria['first_name'] = $_REQUEST['criteria']['first_name'];
		} // first name
		if ($_REQUEST['criteria']['provider']) {
			$display_buffer .= "<b>".__("Provider").
				": </b>".$this->_lookup_provider($_REQUEST['criteria']['provider'])." ".
				"<a href=\"".$this->_search_link(array(
					'provider' => ''	
				))."\" class=\"remove_link\">X</a><br/>\n";
			$criteria['provider'] = $_REQUEST['criteria']['provider'];
		} // end date
		if ($_REQUEST['criteria']['aging']) {
			$display_buffer .= "<b>".__("Aged Items").
				": </b>".$_REQUEST['criteria']['aging']." ".
				"<a href=\"".$this->_search_link(array(
					'aging' => ''	
				))."\" class=\"remove_link\">X</a><br/>\n";
			$criteria['aging'] = $_REQUEST['criteria']['aging'];
		} // end aging
		if ($_REQUEST['criteria']['date']) {
			$display_buffer .= "<b>".__("Procedures On").
				": </b>".$_REQUEST['criteria']['date']." ".
				"<a href=\"".$this->_search_link(array(
					'date' => ''	
				))."\" class=\"remove_link\">X</a><br/>\n";
			$criteria['date'] = $_REQUEST['criteria']['date'];
		} // end date
		if ($_REQUEST['criteria']['procedure']) {
			$display_buffer .= "<b>".__("Procedure").
				": </b>".$_REQUEST['criteria']['procedure']." ".
				"<a href=\"".$this->_search_link(array(
					'procedure' => ''
				))."\" class=\"remove_link\">X</a><br/>\n";
			$criteria['procedure'] = $_REQUEST['criteria']['procedure'];
		} // end procedure
		if ($_REQUEST['criteria']['type']) {
			$display_buffer .= "<b>".__("Item Type").
				": </b>".$_REQUEST['criteria']['type']." ".
				"<a href=\"".$this->_search_link(array(
					'type' => ''	
				))."\" class=\"remove_link\">X</a><br/>\n";
			$criteria['type'] = $_REQUEST['criteria']['type'];
		} // end type
		$display_buffer .= "</td></tr></table><p/>\n";

		// New form, new buttons
		$display_buffer .= "<form method=\"post\" name=\"items\">\n".
			"<input type=\"hidden\" name=\"module\" value=\"".$_REQUEST['module']."\" />\n".
			"<input type=\"hidden\" name=\"action\" value=\"".$_REQUEST['action']."\" />\n".
			"<input type=\"hidden\" name=\"type\" value=\"".$_REQUEST['type']."\" />\n".
			"<script language=\"javascript\"><!-- \n".
			"function checkAll ( f, c ) { \n".
			"	for (var i=0; i < f.elements.length; i++ ) {\n".
			"		if (f.elements[i].type == 'checkbox') { f.elements[i].checked = c; } \n".
			"	}\n".
			"}\n".
			"--></script>\n";
		foreach ($_REQUEST['criteria'] AS $k => $v) {
			$display_buffer .= "<input type=\"hidden\" ".
				"name=\"criteria[".$k."]\" value=\"".
				prepare($v)."\" />\n";
		} // end looping through search criteria

		$display_buffer .= 
			"<div align=\"center\">\n".
			__("For checked items").": ".
			//"<input type=\"submit\" name=\"submit_action\" ".
			//	"value=\"".__("Post Payment")."\" />\n".
			//"<input type=\"submit\" name=\"submit_action\" ".
			//	"value=\"".__("Writeoff")."\" />\n".
			"<input type=\"button\" ".
				"onClick=\"checkAll(this.form, true);\" ".
				"value=\"".__("Select All")."\" />\n".
			"<input type=\"button\" ".
				"onClick=\"checkAll(this.form, false);\" ".
				"value=\"".__("Select None")."\" />\n".
			"</div>\n";

		// Set up the table layout
		$table->setHeaderContents(0,  0, ' ');
		$table->setHeaderContents(0,  1, __("Date"));
		$table->setHeaderContents(0,  2, __("Acct Bal"));
		$table->setHeaderContents(0,  3, __("Provider"));
		$table->setHeaderContents(0,  4, __("Patient"));
		$table->setHeaderContents(0,  5, __("Item"));
		$table->setHeaderContents(0,  6, __("Type"));
		$table->setHeaderContents(0,  7, __("Svc"));
		$table->setHeaderContents(0,  8, __("Svc Date"));
		$table->setHeaderContents(0,  9, __("Adjs"));
		$table->setHeaderContents(0, 10, __("Charges"));

		// Get aging summary
		//print "<hr/><b>debug criteria : <br/>"; print_r($criteria); print "<hr/>\n";
		$matrix = $l->aging_report_qualified ( $criteria );
		//print "what is the matrix? "; print_r($matrix); print "<hr/>\n";

		$count = 0;
		foreach ($matrix AS $hash) {
			$count = $count + 1;
			$table->setCellContents($count, 0,
				'<input type="checkbox" '.
				'name="check['.prepare($hash['item']).']" '.
				( $_REQUEST['check'][$hash['item']] ? 'checked="checked" ' : '' ).
				'value="'.prepare($hash['item']).'" />');
			$table->setCellContents($count, 1, $hash['payment_date']); 
			$table->setCellContents($count, 2, $hash['total_balance']);
			$table->setCellContents($count, 3, 
				'<a href="'.$this->_search_link(array(
					'provider' => $hash['provider_id'],
				)).'"><acronym TITLE="'.
				__("Filter by this provider").'">'.
				'<small>'.
				$this->_lookup_provider($hash['provider_id']).
				'</small>'.
				'</acronym></a>');
			$table->setCellContents($count, 4, 
				'<a href="'.$this->_search_link(array(
					'patient' => $hash['patient_id'],
					'payer' => '' // disable payer
				)).'"><acronym TITLE="'.
				__("Filter by this patient").'">'.
				'<small>'.
				$this->_lookup_patient($hash['patient_id']).
				'</small></acronym></a> '.
				'<a href="manage.php?id='.$hash['patient_id'].
				'">['.__("EMR").']</a>'); 
			// Show all items
			$table->setCellContents($count, 5, '<a href="'.
				$this->_detail_link(array(
					'item' => $hash['item']
				)).'"><acronym TITLE="'.
				__("View item details").'">'.
				$hash['item'].'</acronym></a>');
			$table->setCellContents($count, 6, 
				( empty($hash['item_type']) ? '&nbsp;' :
				'<a href="'.
				$this->_search_link(array(
					'type' => $hash['item_type_id']+0
				)).'"><acronym TITLE="'.
				__("Filter by this item type").'">'.
				'<small>'.$hash['item_type'].'</small>'.
				'</acronym></a>') );
			$table->setCellContents($count, 7, 
				'<a href="'.
				$this->_search_link(array(
					'procedure' => $hash['procedure_id']
				)).'"><acronym TITLE="'.
				__("Filter by this procedure").'">'.
				$hash['procedure_id'].
				'</acronym></a>');
			$table->setCellContents($count, 8, 
				'<a href="'.$this->_search_link(array(
					'date' => $hash['procedure_date']
				)).'"><acronym TITLE="'.
				__("Filter by this date").'">'.
				$hash['procedure_date'].'</acronym></a>');
			$table->setCellContents($count, 9, 
				bcadd($hash['money_in'], 0, 2));
			$table->setCellContents($count, 10, 
				bcadd($hash['money_out'], 0, 2));
			$total_balance += $hash['balance'];
		}

		// Set alignment on money columns
		$money_cols = array ( 2, 9, 10 );
		foreach ($money_cols AS $i) {
			$table->setColAttributes($i, array('align'=>'right'), true);
		}

		// Set alternating row hilighting
		$table->altRowAttributes( 
			1, 
			array( 'class' => 'cell_alt' ),
			array( 'class' => 'cell' ),
			false
		);

		$table->updateAllAttributes(array( 'style' => 'border: 0px 1px 0px 1px; border-color: #000000;' ) );

		$display_buffer .= $table->toHtml();
	} // end method search_engine

	// Method: item_detail
	//
	//	Provide item details and main editing functions menu.
	//
	function item_detail ( ) {
		global $display_buffer;
		$display_buffer .= "<div class=\"section\">".
			__("Accounts Receivable").": ".
			__("Item Details")."</div>\n";

		// Display patient and item information
		// (passing a second parameter to claim_information() causes it
		//  to qualify by payment record id (payrec) and not procedure)
		$cl = CreateObject('FreeMED.ClaimLog');
		$info = $cl->claim_information( 0, $_REQUEST['item'] );
		$display_buffer .= 
			"<table border=\"0\" width=\"75%\" class=\"thinbox\" ".
				"cellpadding=\"3\">".
				"<tr>\n".
			"<td valign=\"top\" align=\"left\">\n".
			"<b>".__("Patient").":</b> ".
				$info['patient_name']."<br/><br/>\n".
			"<b>".__("Resp. Party").":</b> ".
				$info['rp_name']."<br/><br/>\n".
			"</td><td valign=\"top\" align=\"left\">\n".
			"<b>".__("SSN").":</b>".
				$info['ssn']."<br/><br/>\n".
			"<b>".__("SSN").":</b>".
				$info['rp_ssn']."<br/><br/>\n".
			"</td><td valign=\"top\" align=\"left\">\n".
			"<b>".__("POS").":</b> ".
				$info['facility']."<br/><br/>\n".
			"<b>".__("ICD").":</b> ".
				$info['diagnosis']."<br/><br/>\n".
			"<b>".__("CPT").":</b> ".
				$info['cpt_code']."<br/><br/>\n".
			"</td><td valign=\"top\" align=\"right\">\n".
			"<b>".__("Charges")."</b>: ".
				bcadd($info['fee'], 0, 2)."<br/><br/>\n".
			"<b>".__("Paid")."</b>: ".
				bcadd($info['paid'], 0, 2)."<br/><br/>\n".
			"<b>".__("Balance")."</b>: ".
				bcadd($info['balance'], 0, 2)."<br/><br/>\n".
			"</tr></table>\n";

		// Display button bar
		$form = CreateObject('PEAR.HTML_QuickForm');
		freemed::quickform_i18n(&$form);
		$form->addElement(
			'hidden', 'module', $_REQUEST['module'] );
		$form->addElement(
			'hidden', 'action', $_REQUEST['action'] );
		$form->addElement(
			'hidden', 'type', $_REQUEST['type'] );
		$form->addElement(
			'hidden', 'item', $_REQUEST['item'] );
		// Hide criteria
		foreach ($_REQUEST['criteria'] AS $k => $v) {
			//print "k = $k, v = $v<br/>\n";
			$form->addElement('hidden', 'criteria['.$k.']', $v);
		}
		$submit_buttons[] = &HTML_QuickForm::createElement(
			'submit', 'submit_action', __("Add Event"));
		$submit_buttons[] = &HTML_QuickForm::createElement(
			'submit', 'submit_action', __("Return to Search"));
		$submit_buttons[] = &HTML_QuickForm::createElement(
			'submit', 'submit_action', __("New Search"));
		$form->addGroup($submit_buttons, null, null, '&nbsp;');
		$display_buffer .= $form->toHtml();

		// Get all events
		$events = $cl->events_for_procedure(0, $_REQUEST['item']);

		// Display form of all events
		$table = CreateObject('PEAR.HTML_Table', array(
			'width' => '75%',
			'cellspacing' => '0',
			'cellpadding' => '3',
			'border' => '0'
		));

		// Header
		$table->setHeaderContents(0, 0, __("Date"), array('align'=>'left'));
		$table->setHeaderContents(0, 1, __("User"), array('align'=>'left'));
		$table->setHeaderContents(0, 2, __("Action"), array('align'=>'left'));
		$table->setHeaderContents(0, 3, __("Comment"), array('align'=>'left'));

		$table->updateColAttributes(0, array('align'=>'left'));

		// Loop through results
		$count = 0;
	//	print "<pre>\n"; print_r($events); print "<br/>\n";
		foreach ($events AS $e) {
			$count = $count + 1;
			$table->setCellContents($count, 0, $e['date']);
			$table->setCellContents($count, 1, $e['user']);
			$table->setCellContents($count, 2, $e['action']);
			$table->setCellContents($count, 3, $e['comment']);
		} // end foreach events

		$display_buffer .= $table->toHtml();
	} // end method item_detail

	function event_add ( ) {
		// If we have something to add, do that instead of showing
		// the form
		if (!empty($_REQUEST['note']) and !empty($_REQUEST['event_action'])) {
			// Commit event
			$cl = CreateObject('FreeMED.ClaimLog');
			$cl->log_event(
				$this->_item_to_procedure($_REQUEST['item']),
				array(
					'item' => $_REQUEST['item'],
					'action' => $_REQUEST['event_action'],
					'comment' => $_REQUEST['note']
				)
			);
			// ... and show the item details
			return $this->item_detail( );
		} // end checking for stuff to add

		global $display_buffer;
		$display_buffer .= "<div class=\"section\">".
			__("Accounts Receivable").": ".
			__("Add Item Event")."</div>\n";

		// Create form
		$form = CreateObject('PEAR.HTML_QuickForm');

		$form->addElement('hidden', 'module', $_REQUEST['module']);
		$form->addElement('hidden', 'action', $_REQUEST['action']);
		$form->addElement('hidden', 'type', $_REQUEST['type']);
		$form->addElement('hidden', 'item', $_REQUEST['item']);

		// Hide criteria
		foreach ($_REQUEST['criteria'] AS $k => $v) {
			//print "k = $k, v = $v<br/>\n";
			$form->addElement('hidden', 'criteria['.$k.']', $v);
		}

		$form->addElement('select', 'event_action', 
			__("Action"), array (
				'' => '----',
				__("Call") => 
					__("Call"),
				__("Email") =>
					__("Email")
			)
		);
		$form->addElement('textarea', 'note', __("Comment"),
			array('rows'=>10, 'cols'=>40, 'wrap'=>'virtual') );

		$submit_buttons[] = &HTML_QuickForm::createElement(
			'submit', 'submit_action', __("Add Event"));
		$submit_buttons[] = &HTML_QuickForm::createElement(
			'submit', 'submit_action', __("Item Detail"));
		$form->addGroup($submit_buttons, null, null, '&nbsp;');

		$form->validate();

		// Dump back to the display buffer
		$display_buffer .= $form->toHtml();
	} // end method event_add

	// Method: writeoff_items
	//
	//	Allow interface to write off items
	//
	function writeoff_items ( ) {
		global $display_buffer;
		$display_buffer .= "<div class=\"section\">".
			__("Accounts Receivable").": ".
			__("Writeoff Items")."</div>\n";

		// Perform the actual marking ...
		//print "<br/><br/><hr/>"; print_r($_REQUEST['check']);
		$l = CreateObject('FreeMED.Ledger');
		if (count($_REQUEST['check']) < 1) {
			$display_buffer .= __("Please select an item  before attempting to write off.");
		} else {
			$res = $l->writeoff_array($_REQUEST['check']);
			if ($res) {
				$display_buffer .= count($_REQUEST['check']).__(" items were successfully written off").".";
			} else {
				$display_buffer .= __("An error occured while attempting to write these items off.");
			}
		}

		// Display button bar
		$form = CreateObject('PEAR.HTML_QuickForm');
		freemed::quickform_i18n(&$form);
		$form->addElement(
			'hidden', 'module', $_REQUEST['module'] );
		$form->addElement(
			'hidden', 'action', $_REQUEST['action'] );
		$form->addElement(
			'hidden', 'type', $_REQUEST['type'] );
		$form->addElement(
			'hidden', 'item', $_REQUEST['item'] );
		// Hide criteria
		foreach ($_REQUEST['criteria'] AS $k => $v) {
			//print "k = $k, v = $v<br/>\n";
			$form->addElement('hidden', 'criteria['.$k.']', $v);
		}
		$submit_buttons[] = &HTML_QuickForm::createElement(
			'submit', 'submit_action', __("Return to Search"));
		$submit_buttons[] = &HTML_QuickForm::createElement(
			'submit', 'submit_action', __("New Search"));
		$form->addGroup($submit_buttons, null, null, '&nbsp;');
		$display_buffer .= $form->toHtml();
	} // end method writeoff_items

	function post_payment () {
		global $display_buffer;

		// Display heading
		$display_buffer .= "<div class=\"section\">".
			__("Accounts Receivable").': '.
			__("Post Payment")."</div>\n";

		$display_buffer .= "<form method=\"post\">\n".
			"<input type=\"hidden\" name=\"module\" value=\"".$_REQUEST['module']."\" />\n".
			"<input type=\"hidden\" name=\"type\" value=\"".$_REQUEST['type']."\" />\n".
			"<input type=\"hidden\" name=\"action\" value=\"".$_REQUEST['action']."\" />\n".
			"";

		// Retain all criteria (hidden)
		if (is_array($_REQUEST['criteria'])) {
			foreach ($_REQUEST['criteria'] AS $k => $v) {
				$display_buffer .= "<input type=\"hidden\" name=\"criteria[".$k."]\" value=\"".prepare($v)."\" />\n";
			}
		}

		// Pass all checked items back to the engine
		if (is_array($_REQUEST['check'])) {
			foreach ($_REQUEST['check'] AS $k => $v) {
				$display_buffer .= "<input type=\"hidden\" name=\"check[".$k."]\" value=\"".prepare($v)."\" />\n";
			}
		}

		// TODO: Verify that the amounts are not over the total check amt, etc
			
		// Create ledger and claimlog
		$l = CreateObject('FreeMED.Ledger');
		$cl = CreateObject('FreeMED.ClaimLog');
			
		// Loop through the posted checks
		foreach ($_REQUEST['post'] AS $k => $v) {
			// Get original procedure values
			$proc = freemed::get_link_rec($k, 'procrec', true);

			// Check for payment
			if ($_REQUEST['pay'][$k] > 0) {
				// Post check to ledger
				$l->post_payment_check (
					$k,
					$proc['proccurcovid'],
					$_REQUEST['c_checkno'],
					($_REQUEST['pay'][$k] + 0),
					__("Payment from Payer")
				);
				
				// Record in individual claim log
				$cl->log_event (
					$k,
					array (
						'action' => __("Payment Received"),
						'comment' => sprintf(__("Check #%s"), $_REQUEST['c_checkno'])
					)
				);
			} // end if pay > 0

			// Check for copay
			if ($_REQUEST['copay'][$k] > 0) {
				$l->post_copay (
					$k,
					($_REQUEST['copay'][$k] + 0),
					__("Copay")
				);

				// Record in individual claim log
				$cl->log_event (
					$k,
					array (
						'action' => __("Copay")
					)
				);
			} // end if copay > 0

			// Determine disallowed amount manually 
			if (($_REQUEST['pay'][$k]+$_REQUEST['copay'][$k]) > 0) {
				// Where is this going?
				$where = $l->next_coverage($k);
			
				// Move to the next coverage in the ledger
				$l->move_to_next_coverage (
					$k,
					// Calculate disallow ...
					$proc['procbalcurrent'] - (
						$_REQUEST['pay'][$k] +
						$_REQUEST['copay'][$k]
					)
				);

				switch ($where) {
					case -1: // crap, has nowhere to go
					$cl->log_event (
						$k,
						array (
							'action' => __("Junk"),
							'comment' => __("Claim has nowhere to go ... no more coverages")
						)
					);
					break; // -1
					
					case 0: // onus on patient
					$cl->log_event (
						$k,
						array (
							'action' => __("Patient Pay"),
							'comment' => __("Moved to patient responsibility")
						)
					);
					break; // 0

					default: // somewhere else
					$cl->log_event (
						$k,
						array (
							'action' => __("Coverage"),
							'comment' => sprintf(__("Moved to coverage %s"), $where)
						)
					);
					break; // default
				} // end switch where
			} // end if disallowed amount
		} // end foreach post var

		// Show buttons at the bottom
		$display_buffer .= "<div align=\"center\">\n".
			"<input type=\"submit\" name=\"submit_action\" ".
				"value=\"".__("Return to Search")."\" />\n".
			"<input type=\"submit\" name=\"submit_action\" ".
				"value=\"".__("New Search")."\" />\n".
			"</div>\n".
			"</form>\n";
	} // end method post_payment

	function post_payment_form () {
		global $display_buffer;

		// Display heading
		$display_buffer .= "<div class=\"section\">".
			__("Accounts Receivable").': '.
			__("Post Payment")."</div>\n";

		$display_buffer .= "<form method=\"post\">\n".
			"<input type=\"hidden\" name=\"module\" value=\"".$_REQUEST['module']."\" />\n".
			"<input type=\"hidden\" name=\"type\" value=\"".$_REQUEST['type']."\" />\n".
			"<input type=\"hidden\" name=\"action\" value=\"".$_REQUEST['action']."\" />\n".
			"";

		// Retain all criteria (hidden)
		if (is_array($_REQUEST['criteria'])) {
			foreach ($_REQUEST['criteria'] AS $k => $v) {
				$display_buffer .= "<input type=\"hidden\" name=\"criteria[".$k."]\" value=\"".prepare($v)."\" />\n";
			}
		}

		// Pass all checked claims back to the engine
		if (is_array($_REQUEST['check'])) {
			foreach ($_REQUEST['check'] AS $k => $v) {
				$display_buffer .= "<input type=\"hidden\" name=\"check[".$k."]\" value=\"".prepare($v)."\" />\n";
			}
		}
			
		// Pull default for payer if it exists
		global $c_payer;
		if ($_REQUEST['criteria']['payer']) {
			$c_payer = $_REQUEST['criteria']['payer'];
		}

		$cl = CreateObject('FreeMED.ClaimLog');
		$display_buffer .= html_form::form_table(array(
		
			__("Payer") => html_form::select_widget(
				'c_payer',
				$cl->aging_insurance_companies( )
				),

			__("Check Number") => html_form::text_widget( 'c_checkno' ),

			__("Total Amount") => html_form::text_widget( 'c_total' )

			));

		// Display claims table
		$table = CreateObject('PEAR.HTML_Table', array (
			'width' => '90%',
			'cellspacing' => '0',
			'style' => 'border: 1px solid; border-color: #000000;'	
		));

		// Set up the table layout
		$table->setHeaderContents(0, 0, __("Patient"));
		$table->setHeaderContents(0, 1, __("Claim"));
		$table->setHeaderContents(0, 2, __("CPT"));
		$table->setHeaderContents(0, 3, __("Service Date"));
		$table->setHeaderContents(0, 4, __("Paid"));
		$table->setHeaderContents(0, 5, __("Outstanding"));
		$table->setHeaderContents(0, 6, __("Payment"));
		$table->setHeaderContents(0, 7, __("Copay"));
		$table->setHeaderContents(0, 8, __("Left Over"));

		$count = 0;
		foreach ($_REQUEST['check'] AS $c) {
			$ci = $cl->claim_information($c);
			// Deal with null claims by skipping them
			if ($ci['patient_id']) {
				$count = $count + 1;
				$table->setCellContents($count, 0, 
					'<a href="manage.php?id='.$ci['patient_id']
					.'">'.$ci['patient_name'].'</a>'.
					'<input type="hidden" name="post['.$c.']" value="'.$c.'" />');
				$table->setCellContents($count, 1, $ci['proc']);
				$table->setCellContents($count, 2, $ci['cpt_cote']);
				$table->setCellContents($count, 3, $ci['service_date']);
				$table->setCellContents($count, 4, bcadd($ci['paid'],0,2));
				$table->setCellContents($count, 5, bcadd($ci['balance'],0,2));
				$table->setCellContents($count, 6, '<input type="text" id="id_pay_'.$c.'" name="pay['.$c.']" size="6" value="0" '.
					'onChange="this.form.id_dis_'.$c.'.value = eval('.$ci['balance'].') - ( eval(this.form.id_pay_'.$c.'.value) + eval(this.form.id_copay_'.$c.'.value) ); return true;" '.
					'/>');
				$table->setCellContents($count, 7, '<input type="text" id="id_copay_'.$c.'" name="copay['.$c.']" size="6" value="0" '.
					'onChange="this.form.id_dis_'.$c.'.value = eval('.$ci['balance'].') - ( eval(this.form.id_pay_'.$c.'.value) + eval(this.form.id_copay_'.$c.'.value) ); return true;" '.
					'/>');
				$table->setCellContents($count, 8, '<input type="text" id="id_dis_'.$c.'" name="dis['.$c.']" size="6" value="'.$ci['balance'].'" disabled="1" />');
			}
		}

		// Set alignment on money columns
		for($i=4;$i<=8;$i++) {
			$table->setColAttributes($i, array('align'=>'right'), true);
		}

		// Set alternating row hilighting
		$table->altRowAttributes( 
			1, 
			array( 'class' => 'cell_alt' ),
			array( 'class' => 'cell' ),
			false
		);

		$table->updateAllAttributes(array( 'style' => 'border: 0px 1px 0px 1px; border-color: #000000;' ) );

		$display_buffer .= $table->toHtml();

		$display_buffer .= "<div align=\"center\">\n".
			"<input type=\"submit\" name=\"submit_action\" ".
				"value=\"".__("Post")."\" />\n".
			"<input type=\"submit\" name=\"submit_action\" ".
				"value=\"".__("Return to Search")."\" />\n".
			"<input type=\"submit\" name=\"submit_action\" ".
				"value=\"".__("New Search")."\" />\n".
			"</div>\n".
			"</form>\n";
	} // end method post_payment_form

	// ----- Internal functions

	// Method: _detail_link
	//
	//	Creates link for insertion in anchor tags with
	//	detail criteria for the engine.
	//
	// Parameters:
	//
	//	$criteria - Associative array with keys containing 
	//	the variables to be passed.
	//
	// Returns:
	//
	//	Link text.
	//
	function _detail_link ( $param ) {
		static $_page;
		if (!isset($_page)) { $_page = page_name(); }

		// Fold in current requests
		foreach ($_REQUEST['criteria'] AS $k => $v) {
			$_param['criteria['.$k.']'] = $v;
		}
		foreach ($param AS $k => $v) { $_param[$k] = $v; }

		foreach ($_param AS $name => $value) {
			if ($name != 'action') {
				$p[] = urlencode($name).'='.urlencode($value);
			}
		}

		return $_page . '?' .
			'module='.urlencode($_REQUEST['module']).'&'.
			'type='.urlencode($_REQUEST['type']).'&'.
			'action='.urlencode($_REQUEST['action']).'&'.
			'submit_action='.urlencode(
			( $param['action'] ? $action : 'item_detail' )).'&'.
			join('&', $p);
	} // end method _detail_link

	// Method: _search_link
	//
	//	Creates link for insertion in anchor tags with
	//	search criteria for the engine.
	//
	// Parameters:
	//
	//	$criteria - Associative array with keys containing the
	//	search criteria type and values containing the search
	//	criteria.
	//
	// Returns:
	//
	//	Link text.
	//
	function _search_link ( $criteria ) {
		static $_page;
		if (!isset($_page)) { $_page = page_name(); }

		// Fold in current requests
		if (is_array($_REQUEST['criteria'])) {
			$_criteria = $_REQUEST['criteria'];
			foreach ($criteria AS $k => $v) {
				$_criteria[$k] = $v;
			}
		} else {
			$_criteria = $criteria;
		}

		foreach ($_criteria AS $name => $value) {
			if (!empty($value)) {
				$c[] = 'criteria['.urlencode($name).']='.
					urlencode($value);
			}
		}
		return $_page . '?' .
			'module='.urlencode($_REQUEST['module']).'&'.
			'type='.urlencode($_REQUEST['type']).'&'.
			'action='.urlencode($_REQUEST['action']).'&'.
			'submit_action=search_items&'.
			join('&', $c);
	} // end method _search_link

	// Method: _item_to_procedure
	//
	//	Lookup procedure id from item (payment record) id
	//
	// Parameters:
	//
	//	$pid - Payment record id
	//
	// Returns:
	//
	//	Integer, procedure id
	//
	function _item_to_procedure ( $pid ) {
		$r = freemed::get_link_rec($pid, 'payrec');
		return $r['payrecproc'];
	} // end method _item_to_procedure

	// Method: _lookup_patient
	//
	//	Get textual name of patient from id, with caching.
	//
	// Parameters:
	//
	//	$pid - Patient ID
	//
	// Returns:
	//
	//	Full text name of patient
	//
	function _lookup_patient ( $pid ) {
		static $_cache;
		if (!isset($_cache[$pid])) {
			$_cache[$pid] = CreateObject('_FreeMED.Patient', $pid);
		}
		return $_cache[$pid]->fullName();
	} // end method _lookup_patient

	// Method: _lookup_provider
	//
	//	Get textual name of provider from id, with caching.
	//
	// Parameters:
	//
	//	$pid - Provider ID
	//
	// Returns:
	//
	//	Full text name of provider
	//
	function _lookup_provider ( $pid ) {
		static $_cache;
		if (!isset($_cache[$pid])) {
			$_cache[$pid] = CreateObject('_FreeMED.Physician', $pid);
		}
		return $_cache[$pid]->fullName();
	} // end method _lookup_provider

	// Method: _provider_list
	//
	//	Get key and value pairs with full text names for providers
	//
	// Returns:
	//
	//	Hash, key = provider name, value = id
	//
	function _provider_list ( ) {
		// Make sure we only show people who do procedures in the system.
		// Perhaps this isn't the *most* efficient way of doing it, but
		// it keeps those pesky referring docs out of the lists.
		$query = "SELECT ".
			"DISTINCT pr.id AS id ".
			"FROM ".
				"procrec AS p, ".
				"physician AS pr, ".
				"payrec AS pa ".
			"WHERE ".
				"pa.payrecproc = p.id AND ".
				"p.procphysician = pr.id ".
			"ORDER BY pr.phylname, pr.phyfname";
		$result = $GLOBALS['sql']->query ( $query );
		$return = array ( '----' => '' );
		while ( $r = $GLOBALS['sql']->fetch_array ( $result ) ) {
			$return[$this->_lookup_provider($r['id'])] = $r['id'];
		}
		return $return;
	} // end method _provider_list

} // end class AccountsReceivable

register_module('AccountsReceivable');

?>
