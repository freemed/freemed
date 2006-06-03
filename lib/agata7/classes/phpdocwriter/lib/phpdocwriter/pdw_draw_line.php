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

import('phpdocwriter.pdw_drawing');

class pdw_draw_line extends pdw_drawing
{

function pdw_draw_line(&$obj,$params)
{
	$this->parent =& $obj;
	$this->params =& $params;
	parent::pdw_drawing();

	if (!array_key_exists('x1', $params) || !array_key_exists('y1', $params) || !array_key_exists('x2', $params) || !array_key_exists('y2', $params))
		$this->_error('You must define x1,y1,x2,y2 to draw a line');
	if (!array_key_exists('stroke', $params))
		$params['stroke']='solid';
	
	$this->office = new XMLBranch('draw:line');
	$this->office->setTagAttribute('text:anchor-type', 'paragraph');
	$this->office->setTagAttribute('draw:z-index', 0);
	$this->office->setTagAttribute('draw:style-name', 'gr'.$this->grno);
	$this->office->setTagAttribute('draw:text-style-name', 'P1');
	$this->office->setTagAttribute('svg:width', $params['w']);
	$this->office->setTagAttribute('svg:height', $params['h']);
	$this->office->setTagAttribute('svg:x1', $params['x1']);
	$this->office->setTagAttribute('svg:y1', $params['y1']);
	$this->office->setTagAttribute('svg:x2', $params['x2']);
	$this->office->setTagAttribute('svg:y2', $params['y2']);
	
	$this->_style($params,$this->styleprop);
	$this->_frame($params,$this->office);
}

}
?>
