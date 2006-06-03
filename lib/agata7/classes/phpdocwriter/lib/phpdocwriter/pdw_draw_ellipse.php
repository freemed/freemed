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

class pdw_draw_ellipse extends pdw_drawing
{

function pdw_draw_ellipse(&$obj,$params)
{
	$this->parent =& $obj;
	$this->params =& $params; 
	parent::pdw_drawing();
	
	$this->office = new XMLBranch('draw:ellipse');
	$this->office->setTagAttribute('draw:style-name', 'gr'.$this->grno);
	$this->office->setTagAttribute('draw:text-style-name', 'P1');
	
	$this->_style($params,$this->styleprop);
	$this->_frame($params,$this->office);
}

}
?>
