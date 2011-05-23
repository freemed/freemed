<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

// Class: org.freemedsoftware.api.ModuleSearch
//
//	Class to deal with module picklist functions.
//
class ModuleSearch {

	public function __constructor ( ) { }

	public function picklist ( $keyword,$moduleType=NULL ) {
		$q = "";
		if($moduleType)
			$q = "module_associations='".$moduleType."' and ";
		$q = $q."module_name LIKE '" . $GLOBALS['sql']->escape( $keyword ) . "%'";
 		$query = "SELECT module_name,module_class FROM modules WHERE " . $q;
		syslog(LOG_INFO, "PICK| $query");
		$result = $GLOBALS['sql']->queryAll( $query );
		foreach($result AS $k){
			$ret[$k['module_class']] = $k['module_name'];
		}
		return $ret;
	} // end method picklist
	
	public function getFields( $moduleClass ){
		$s = CreateObject( 'org.freemedsoftware.module.'.$moduleClass );
		if ($s == NULL) { return NULL; }
		return $s->variables;
	} // end method getFields
	
	public function ToText($module){
		$query = "SELECT module_name,module_class FROM modules WHERE module_class = '".$GLOBALS['sql']->escape($module)."'";
		syslog(LOG_INFO, "ToText| $query");
		$result = $GLOBALS['sql']->queryRow( $query );	
		return $result?$result['module_class']:NULL;
	} // end method ToText

} // end class ModuleSearch

?>
