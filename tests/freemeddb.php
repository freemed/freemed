<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
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

include_once ( dirname(__FILE__).'/bootstrap.test.php' );

// t("resolve_module(systemreports)", resolve_module('systemreports'));

$db = CreateObject('org.freemedsoftware.core.FreemedDb');
t("db creation (is object)", is_object($db));
t("sql->queryAll('select * from config')", $db->queryAll("select * from config"));
t("sql->load_data", $db->load_data(array('col3' => 'loaded_data')));
t("sql->insert_query", $db->insert_query (
	'test',
	array (
		'col1' => 'something',
		'col2' => array ( '1', '2', '3' ),
		'col3'
	)
));
t("sql->update_query", $db->update_query(
	'test',
	array (
		'col1' => 'something',
		'col2' => array ( '1', '2', '3' ),
		'col3'
	),
	array ( 'id' => 3 )
));
t("sql->distinct_values(messages, msgby)", $db->distinct_values('messages', 'msgby'));

?>
