<?php
/*
	This file is part of PHP DocWriter (http://ciclope.info/~jmsanchez)
	Copyright (c) 2003-2004 José Manuel Sánchez Rivero

	You can contact the author of this software via E-mail at
	jmsanchez@laurel.datsi.fi.upm.es

	PHP DocWriter is free software; you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation; either version 2.1 of the License, or
	(at your option) any later version.

	PHP DocWriter is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Lesser General Public License for more details.

	You should have received a copy of the GNU Lesser General Public License
	along with PHP DocWriter; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require_once('pdw_document.php');

class pdw_header extends pdw_document
{
var $tempname;
var $parent;

function pdw_header(&$obj, $tempname)
{
	if (is_object($obj) && get_class($obj)=='pdw_document')
	{
		$this->parent=& $obj;
		$this->frameno =& $this->parent->frameno;
		$this->styles =& $this->parent->styles;
		$this->parano = $this->parent->parano;
		$this->textno = $this->parent->textno;
		$this->paragstyle='Header';
		$this->spanstyle='';
		$this->cursor = '';
		$this->imglist =& $this->parent->imglist;
		$this->zip =& $this->parent->zip;
		$this->autostyle =& $this->parent->autostyle;
		$this->objlist =& $this->parent->objlist;
		$this->objlist[] = $this;
		$this->fontdef =& $this->parent->fontdef;
		$this->fontlist =& $this->parent->fontlist;
		$this->fontdecls =& $this->parent->fontdecls;
		
		$this->tempname = $tempname;
		

		$this->office = new XMLBranch('style:header');

		}
		else $this->_error('Not an document object');

}

function Insert()
{
	$this->office->addXMLBranch($this->cursor);
	$headlines = $this->parent->masterstyles->getBranches('office:master-styles', 'style:master-page', 'style:name', $this->tempname);
	$headlines[0]->addXMLBranch($this->office);
}


}
?>
