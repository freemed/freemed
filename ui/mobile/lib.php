<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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

if (!defined('UI_MOBILE_LIB')) {

define('UI_MOBILE_LIB', true);
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . "/../.." );
include_once "lib/freemed.php";

$login = CreateObject('org.freemedsoftware.public.Login');

class UiMobileForm {
	protected $target;
	protected $name;
	protected $method;
	protected $fields;

	public function __construct ( $_target, $_name, $_method, $_fields ) {
		$this->target = $_target;
		$this->name = $_name;
		$this->method = $_method;
		$this->fields = $_fields;
	}

	public function toString() {
		$s = "<form action=\"". $this->target ."\" method=\"". $this->method ."\"> ".
			"<fieldset>";
		foreach ($this->fields AS $k => $v) {
			if ($v instanceof UiMobileFormElement) {
				$s .= "<div data-role=\"fieldcontain\">".
					"<label for=\"" . $v->getName() . "\" class=\"" . $v->getClass() ."\">" . htmlentities( $k ) . "</label>".
					$v->toString().
					"</div>\n";
			}
		}
		$s .= "	<button type=\"submit\" data-theme=\"a\" name=\"submit\" value=\"Submit\">Submit</button>".
			"</fieldset> ".
			"</form>\n";
		return $s;
	} // end function toString()

} // end class UiMobileForm

interface UiMobileFormElement {
	public function toString();
	public function getClass();
	public function getName();
}

class UiMobileFormSelect implements UiMobileFormElement {

	protected $name;
	protected $values;

	public function __construct( $_name, $_values ) {
		$this->name = $_name;
		$this->values = $_values;
	}

	public function getName() {
		return $this->name;
	}

	public function getClass() {
		return "select";
	}

	public function toString() {
		$s = "<select id=\"" . htmlentities( $this->name ) . "\" name=\"" . htmlentities( $this->name ) . "\">\n";
		foreach ($this->values AS $k => $v) {
			$s .= "\t<option value=\"" . htmlentities( $v ) . "\">" . htmlentities( $k ) . "</option>\n";
		}
		$s .= "</select>\n";
		return $s;
	}

} // end class UiMobileFormSelect

class UiMobileFormTextbox implements UiMobileFormElement {

	protected $name;
	protected $length;
	protected $value;

	public function __construct( $_name, $_length, $_value = "" ) {
		$this->name = $_name;
		$this->length = $_length;
		$this->value = $_value;
	}

	public function getName() {
		return $this->name;
	}

	public function getClass() {
		return "text";
	}

	public function toString() {
		return "<input type=\"text\" name=\"" . htmlentities( $this->name ) . "\" id=\"" . htmlentities( $this->name ) . "\" value=\"" . htmlentities( $this->value ) ."\"  />\n";
	}

} // end class UiMobileFormTextbox

class UiMobileListItem {
	var $type, $label, $target;

	public function __construct ( $_type, $_label, $_target ) {
		$this->type = $_type;
		$this->label = $_label;
		$this->target = $_target;
	}

	public function toString() {
		return "<li><a href=\"" . $this->target . "\">" . $this->label . "</a></li>\n";
	}
}

class UiMobileLib {

	public function header ($title) {
		print "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
		print "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n";
		print "<head>\n";
		print "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
		print "<title>${title}</title>\n";
		print "<link rel=\"stylesheet\" href=\"css/jquery.mobile-1.0b1.min.css\" />\n";
		print "<script src=\"js/jquery-1.6.2.min.js\"></script>\n";
		print "<script src=\"js/jquery.mobile-1.0b1.min.js\"></script>\n";
		print "</head>\n";
		print "<body>\n";
	} // end method header

	public function pageHeader ($title, $id) {
		print "<div data-role=\"page\" id=\"${id}\">\n";
		print "<div data-role=\"header\">\n";
		print "<h1>${title}</h1>\n";
		print "</div><!-- /header -->\n";
		print "<div data-role=\"content\">\n";
	} // end function pageHeader

	public function displayList ($title, $list) {
		print "<ul data-role=\"listview\" data-inset=\"true\" data-theme=\"c\" data-dividertheme=\"b\">\n";
		print "<li data-role=\"list-divider\">${title}</li>\n";
		foreach ($list AS $item) {
			print $item->toString();
		}
		print "</ul>\n";
	} // end function displayList

	public function pageFooter () {
		print "</div><!-- /content -->\n";
		print "<div data-role=\"footer\">\n";
		print "\t<h4>&copy; 2011 FreeMED Software Foundation</h4>\n";
		print "</div><!-- /footer -->\n";
		print "</div><!-- /page -->\n";
	} // end function pageFooter

	public function footer () {
		print "</body>\n";
		print "</html>\n";
	} // end method footer

	public function checkLogin($url) {
		if ( ! $GLOBALS['login']->loggedIn() ) {
			Header("Location: $url", true, 307);
			die();
		}
	} // end method checkLogin

} // end class UiMobileLib

} // end if !defined

?>
