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

class pdw_drawing extends pdw_document
{

function pdw_drawing()
{
	$this->paragstyle =& $this->parent->paragstyle;
	$this->grno = $this->parent->_getNo('g');
	
	$this->parent->images = 1;
	$this->imglist =& $this->parent->imglist;
	$this->autostyle =& $this->parent->autostyle;
	
	$this->textno = $this->parent->textno;
	$this->fontdef =& $this->parent->fontdef;
	$this->fontlist =& $this->parent->fontlist;
	$this->fontdecls =& $this->parent->fontdecls;
	
	if (!array_key_exists('stroke', $this->params))
		$params['stroke']='solid';
	if (!array_key_exists('anchor', $this->params))
		$params['anchor']='paragraph';
		
	$this->style = new XMLBranch('style:style');
	$this->style->setTagAttribute('style:name', 'gr'.$this->grno);
	$this->style->setTagAttribute('style:family', 'graphics');
	
	$this->styleprop = new XMLBranch('style:properties');
	$this->styleprop->setTagAttribute('draw:textarea-horizontal-align', 'center');
	$this->styleprop->setTagAttribute('draw:textarea-vertical-align', 'middle');
}

function Insert()
{
	/////<style:style> <- <style:propieties>
	$this->style->addXMLBranch($this->styleprop);
	/////<office:automatic-styles> <- <style:style>
	$this->autostyle->addXMLBranch($this->style);
	$this->office->addXMLBranch($this->cursor);
	$this->parent->cursor->addXMLBranch($this->office);
}

}

import('phpdocwriter.pdw_draw_rectangle');
import('phpdocwriter.pdw_draw_line');
import('phpdocwriter.pdw_draw_path');
import('phpdocwriter.pdw_draw_caption');
import('phpdocwriter.pdw_draw_ellipse');
import('phpdocwriter.pdw_draw_circle');
?>
