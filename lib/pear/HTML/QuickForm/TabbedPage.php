<?php

//	$Id$
//	Customized from "tabbed.php" example in PEAR

require_once 'HTML/QuickForm/Controller.php';

// Load some default action handlers
require_once 'HTML/QuickForm/Action/Submit.php';
require_once 'HTML/QuickForm/Action/Jump.php';
require_once 'HTML/QuickForm/Action/Display.php';
require_once 'HTML/QuickForm/Action/Direct.php';

class HTML_QuickForm_TabbedPage extends HTML_QuickForm_Page {

	function buildTabs() {
		$this->_formBuilt = true;
		// Here we get all page names in the controller
		$pages  = array();
		$myName = $current = $this->getAttribute('id');
		while (NULL !== ($current = $this->controller->getPrevName($current))) {
			$pages[] = $current;
		}
		$pages = array_reverse($pages);
		$pages[] = $current = $myName;
		while (NULL !== ($current = $this->controller->getNextName($current))) {
			$pages[] = $current;
		}
		// Here we display buttons for all pages, the current one's is disabled
		foreach ($pages as $pageName) {
			$tabs[] = $this->createElement(
				'submit',
				$this->getButtonName($pageName),
				$pageName,
				array('class' => 'flat') + ($pageName == $myName ? array('disabled' => 'disabled') : array() )
			);
		}
		$this->addGroup($tabs, 'tabs', null, '&nbsp;', false);
	}

	function addGlobalSubmit() {
		$this->addElement('submit', $this->getButtonName('submit'), __("Submit"), array('class' => 'button'));
		$this->setDefaultAction('submit');
	}

} // end class HTML_QuickForm_TabbedPage

?>
