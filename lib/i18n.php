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

if (defined('DISABLE_I18N')) {
	function __($x) { return $x; }
} else {

include_once ( dirname(__FILE__).'/php-gettext/gettext.inc' );
include_once ( dirname(__FILE__).'/iso-set.php' );

if (!defined('SESSION_DISABLE')) {
	LoadObjectDependency( 'net.php.pear.HTTP_Session2' );
	$lang = HTTP_Session2::get( 'language', DEFAULT_LANGUAGE );
} else {
	$lang = DEFAULT_LANGUAGE;
}
$locale_dir = dirname(dirname(__FILE__)).'/locale';

$__domains = array (
	'freemed',
	UI
);

_setlocale ( LC_MESSAGES, $lang );

$GLOBALS['ISOSET'] = language2isoset ( $lang );
foreach ( $__domains AS $_v ) {
	_bindtextdomain ( $_v, $locale_dir );
	_bind_textdomain_codeset ( $_v, language2isoset ( $lang ) );
	_textdomain ( $_v );
}

function get_translation_matrix( $domain ) {
	global $default_domain;
	$default_domain = $domain;
	$l10n = _get_reader();
	return $l10n->cache_translations;
} // end method get_translation_matrix

}

?>
