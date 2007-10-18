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

$ui = 'dojo';

if ( file_exists ( dirname(__FILE__).'/data/cache/healthy' ) ) {
	trigger_error("FreeMED is already installed!", E_USER_ERROR);
}

// TODO : Handle JSON requests for handling things here?

// i18n routines
include_once ("lib/i18n.php");

// Load smarty engine
unset ( $smarty );
include_once(dirname(__FILE__).'/lib/smarty/Smarty.class.php');
$smarty = new Smarty;

// Override Smarty defaults for FreeMED
$smarty->template_dir = dirname(__FILE__)."/ui/${ui}/view/";
$smarty->compile_dir = dirname(__FILE__)."/data/cache/smarty/templates_c/";
$smarty->cache_dir = dirname(__FILE__)."/data/cache/smarty/cache/";

// Change delimiters to be something a bit more sane
$smarty->left_delimiter = '<!--{';
$smarty->right_delimiter = '}-->';

$base_uri = dirname ( str_replace ( $_SERVER['PATH_INFO'], '', $_SERVER['REQUEST_URI'] ) );
$smarty->assign ( "base_uri", $base_uri );
$smarty->assign ( "htdocs", "${base_uri}/ui/${ui}/htdocs" );
$smarty->assign ( "ui", $ui );
$smarty->assign ( "webroot", dirname ( __FILE__ ) );
$smarty->assign ( "webuser", exec ( "whoami" ) );
$smarty->assign ( "configwrite", is_writable ( dirname(__FILE__).'/lib/settings.php' ) || ( ! file_exists ( dirname(__FILE__).'/lib/settings.php' ) && is_writable ( dirname(__FILE__).'/lib/' ) ) );
$smarty->assign ( "mysqlenabled", function_exists ( 'mysql_connect' ) );

$smarty->display ( 'install.tpl' );

?>
