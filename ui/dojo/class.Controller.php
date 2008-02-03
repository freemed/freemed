<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
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

class Controller {

	protected $smarty;
	protected $vars;
	protected $default = 'org.freemedsoftware.ui.login';

	public function __construct ( ) {
		// Wrap initialize function
		$this->initialize ( );
	}	

	protected function initialize ( ) {
		// Figure out "base URL"
		unset ( $base_uri );
		$base_uri = dirname ( str_replace ( $_SERVER['PATH_INFO'], '', $_SERVER['REQUEST_URI'] ) );

		unset ( $ui );
		$ui = basename ( dirname ( __FILE__ ) );

		// Load smarty engine
		unset ( $smarty );
		$this->smarty = CreateObject( 'net.php.smarty.Smarty' );

		// Override Smarty defaults for FreeMED
		$this->smarty->template_dir = dirname(__FILE__)."/view/";
		$this->smarty->compile_dir = dirname(__FILE__)."/../../data/cache/smarty/templates_c/";
		$this->smarty->cache_dir = dirname(__FILE__)."/../../data/cache/smarty/cache/";

		// Change delimiters to be something a bit more sane
		$this->smarty->left_delimiter = '<!--{';
		$this->smarty->right_delimiter = '}-->';

		// Load global passed data in whichever order it needs
		$this->load_data ( $_GET );
		$this->load_data ( $_POST );
		$this->load_data ( $_COOKIE );
		$this->load_data ( $_SESSION );

		// Master overrides
		$this->smarty->assign ( "INSTALLATION", INSTALLATION );
		$this->smarty->assign ( "VERSION", DISPLAY_VERSION );
		$this->smarty->assign ( "base_uri", $base_uri );
		$this->smarty->assign ( "htdocs", "${base_uri}/ui/${ui}/htdocs" );
		$this->smarty->assign ( "ui", $ui );
		$this->smarty->assign ( "controller", "${base_uri}/controller.php/${ui}" );
		$this->smarty->assign ( "relay", "${base_uri}/relay.php/json" );
		$this->smarty->assign ( "SESSION", $_SESSION );
		$this->smarty->assign ( "ISOSET", $GLOBALS['ISOSET'] );
		$this->smarty->assign ( "unique", '_' . substr(md5(microtime()), 0, rand(5, 12)) . '_' );
		$this->smarty->assign ( "paneLoading", '<div align="center"><img src="'.$base_uri.'/ui/'.$ui.'/htdocs/images/loading.gif" border="0"/></div>' );

		// Theming options
		if ( defined ( 'LOGIN_IMAGE' ) ) {
			$this->smarty->assign ( "LOGIN_IMAGE", LOGIN_IMAGE );
		} else {
			$this->smarty->assign ( "LOGIN_IMAGE", false );
		}
	} // end public function initialize

	public function load ( $template ) {
		// Wrapper for loading Smarty template
		$this->smarty->display ( "${template}.tpl" );
	} // end public function load

	public function load_default ( ) {
		$this->load ( $this->default );
	} // end public function load_default

	private function load_data ( $data ) {
		if ( is_array ( $data ) ) {
			foreach ( $data AS $k => $v ) {
				// Ignore anything beginning with an underscore
				if (substr($k, 0, 1) != '_') {
					// Store in protected data
					$this->vars[$k] = $v;

					// Pass to Smarty engine
					$this->smarty->assign ( $k, $v );
				}
			}
		}
	} // end private function load_data

	protected function export_date ( $data ) {
		switch (true) {
			case ereg("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $data, $regs):
			return sprintf('%d/%d/%04d', $regs[2], $regs[3], $regs[1]);
			break;

			case ereg("([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})", $data, $regs):
			return $data;

			default: return $data;
		}
	} // end protected function export_date

	protected function import_date ( $varname, $default = NULL ) {
		$data = $_REQUEST[$varname] ? $_REQUEST[$varname] : $default;
		switch (true) {
			case ereg("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $data, $regs):
			return sprintf('%04d-%02d-%02d', $regs[1], $regs[2], $regs[3]);
			break;

			case ereg("([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})", $data, $regs):
			if ($regs[3] < 30) {
				$regs[3] += 2000;
			} elseif ($regs[3] < 1800) {
				$regs[3] += 1900;
			}
			return sprintf('%04d-%02d-%02d', $regs[3], $regs[1], $regs[2]);
			break;

			default:
			return false;
			break;
		}
	} // end protected function import_date

} // end class Controller

?>
