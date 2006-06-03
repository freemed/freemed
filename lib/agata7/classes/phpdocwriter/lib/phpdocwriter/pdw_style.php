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
require_once('pdw_base.php');

class pdw_style extends pdw_base
{

var $textno;	// Text style number
var $parano;	// Paragraph style number
var $frameno;	// Frame style number. 
var $grno;		// Graphic style number of frame
var $pmasterno;	//
var $style;
var $styleprop;

function _style(&$params,&$styleprop)
{
	foreach($params as $key => $value)
	{
		switch ($key)
		{
			case 'borders':
				$border = explode(',', $value);
				$styleprop->setTagAttribute("fo:border-left", $border[0]);
				$styleprop->setTagAttribute("fo:border-right", $border[1]);
				$styleprop->setTagAttribute("fo:border-top", $border[2]);
				$styleprop->setTagAttribute("fo:border-bottom", $border[3]);
			break;
			case 'padding':
				$pad = explode(',', $value);
				$styleprop->setTagAttribute('fo:padding-left', $pad[0].'cm');
				$styleprop->setTagAttribute('fo:padding-right', $pad[1].'cm');
				$styleprop->setTagAttribute('fo:padding-top', $pad[2].'cm');
				$styleprop->setTagAttribute('fo:padding-bottom', $pad[3].'cm');
			break;
			case 'margins':
				$margin = explode(',', $value);
				$styleprop->setTagAttribute('fo:margin-left', $margin[0].'cm');
				$styleprop->setTagAttribute('fo:margin-right', $margin[1].'cm');
				$styleprop->setTagAttribute('fo:margin-top', $margin[2].'cm');
				$styleprop->setTagAttribute('fo:margin-bottom', $margin[3].'cm');
			break;
			case 'bgimg':
				$bgimage = new XMLBranch('style:background-image');
				$bgimage->setTagAttribute('xlink:href','');
				$bgimage->setTagAttribute('xlink:type','');
				$bgimage->setTagAttribute('xlink:actuate','onLoad');
				$styleprop->addXMLBranch($bgimage);
			break;
			case 'bgcolor':
				$styleprop->setTagAttribute('fo:background-color', $value);
			break;
			case 'shadow':
				$styleprop->setTagAttribute('style:shadow', $value);
			break;
			case 'wrap';
				$styleprop->setTagAttribute('style:wrap', $value);
			break;
			case 'h-pos';
				$styleprop->setTagAttribute('style:horizontal-pos', $value);
			break;
			case 'h-rel';
				$styleprop->setTagAttribute('style:horizontal-rel', $value);
			break;
			case 'v-pos';
				$styleprop->setTagAttribute('style:vertical-pos', $value);
			break;
			case 'v-rel';
				$styleprop->setTagAttribute('style:vertical-rel', $value);
			break;
			case "stroke":
				$styleprop->setTagAttribute('draw:stroke', $value);
			break;
			case "strokewidth":
				$styleprop->setTagAttribute('svg:stroke-width', $value.'cm');
			break;
			case "fillgradient":
				$styleprop->setTagAttribute('draw:fill', 'gradient');
				$styleprop->setTagAttribute('draw:fill-gradient-name', $value);
			break;
			case "fillcolor":
				$styleprop->setTagAttribute('draw:fill', 'solid');
				$styleprop->setTagAttribute('draw:fill-color', $value);
			break;
		}
	}
}

function _getNo($no)
{
	switch ($no)
	{
		case 't':
			return $this->textno++;
		break;
		case 'p':
			return $this->parano++;
		break;
		case 'f':
			return $this->frameno++;
		break;
		case 'g':
			return $this->grno++;
		break;
		
	}
}

function _searchstyle(&$astyle)
{
	$i=0;
	foreach($this->stylelist as $style) {
		$i++;
		if ($style==$astyle)
			return 'gr'.$i;
	}
	return 0;
}

}
?>
