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

import('phpdocwriter.pdw_document');

class pdw_textbox extends pdw_document
{

function pdw_textbox(&$obj,$params)
{
// 	if (is_object($obj) && (get_class($obj)=="document" || get_class($obj)=="image" || get_class($obj)=="textbox"))
// 	{
// 		parent::pdw_document;
		$this->parent =& $obj;
		$this->paragstyle = "Frame Contents";
		$this->spanstyle = "";
		
		$this->frameno =& $obj->frameno;
		$frameno = $this->frameno++;
		$this->name = 'Frame'.$this->frameno;
		
		$this->imglist =& $this->parent->imglist;
		$this->zip =& $this->parent->zip;
		$this->autostyle =& $this->parent->autostyle;
		$this->lang =& $this->parent->lang;
		$this->country =& $this->parent->country;
		$this->fontdef =& $this->parent->fontdef;
		$this->fontlist =& $this->parent->fontlist;
		$this->fontdecls =& $this->parent->fontdecls;
		
		/////<style:style>
		$this->style = new XMLBranch("style:style");
		$this->style->setTagAttribute("style:name", 'fr'.$frameno);
		$this->style->setTagAttribute("style:family", "graphics");
		$this->style->setTagAttribute("style:parent-style-name", "Frame");
		
		/////<style:propieties>
		$this->styleprop = new XMLBranch("style:properties");
		
		/////<draw:text-box>
		$this->office = new XMLBranch("draw:text-box");
		$this->office->setTagAttribute("draw:style-name", 'fr'.$frameno);
		$this->office->setTagAttribute("draw:name", "Frame".$frameno);
		$this->frameprop =& $this->office;
	
	if (!array_key_exists('anchor', $params))
		$params['anchor']='paragraph';
	if (!array_key_exists('h-pos', $params))
		$params['h-pos']='from-left';
	if (!array_key_exists('h-rel', $params))
		$params['h-rel']='paragraph';
	if (!array_key_exists('v-pos', $params))
		$params['v-pos']='from-top';
	if (!array_key_exists('v-rel', $params))
		$params['v-rel']='paragraph';
		
	foreach($params as $key => $value)
	{
		switch ($key)
		{
			case 'columns':
				$col = new XMLBranch('style:columns');
				$col->setTagAttribute('fo:column-count', $value);
				$col->setTagAttribute('fo:column-gap', '0cm');
				$this->styleprop->addXMLBranch($col);
			break;
			case 'chain':
				$this->styleprop->setTagAttribute('style:chain-next-name', $value);
			break;
		}
	}
	$this->_style($params,$this->styleprop);
	$this->_frame($params,$this->office);
	
// 	$this->frameno++;
// 	}
// 	else $this->_error('Not an document object');
}

function Insert()
{
	/////<style:style> <- <style:propieties>
	$this->style->addXMLBranch($this->styleprop);
	/////<office:automatic-styles> <- <style:style>
	$this->autostyle->addXMLBranch($this->style);
// 	$this->cursor=$this->parent->_getBranch();
	$this->office->addXMLBranch($this->cursor);
	$this->parent->cursor->addXMLBranch($this->office);
}

}
?>
