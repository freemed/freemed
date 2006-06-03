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

require_once('pdw_style.php');

class pdw_document extends pdw_style
{
var $tableno;				//
var $spanstyle;				// Span style name
var $paragstyle;			// Paragraph name
var $fontlist;				//
var $pagelist;				//
var $imglist;				//

var $newpage;				//
var $fnt;					// Footnote number
var $lang;					//
var $country;				//
var $fontdef;				//
var $ctemplate;
var $images;				//
// Branches
var $meta;
var $script;
var $fontdecls;
var $autostyle;
var $sequencedecls;
var $office;
var $cursor;
var $span;
var $sautostyle;

function pdw_document()
{
	$this->tmpdir = $this->_uniquename();
	$this->filename = 'document';
	$this->filtername = 'SXW';
	$this->debug = 0;
	$this->zip = new zipfile();
	$this->compress = 0;
	///// Inicialización de propiedades
	$this->tableno = 'Table1';
	$this->frameno = 1;
	$this->grno = 1;
	$this->paragstyle='Standard';
	$this->spanstyle='';
	$this->lastspan='';
	$this->fontlist=array();
	$this->imglist=array();
	$this->textno=1;
	$this->parano=1;
	$this->autopara=array();
	$this->newpage=0;
	$this->pmasterno = 'pm1';
	$this->fnt=0;
	$this->lang='';
	$this->country='';
	$this->fontdef=0;
	$this->images=1;
	$this->ctemplate='Standard';
	$this->cursorend = '/>';
	
	
	// content.xml
	$this->script = new XMLBranch('office:script');
	$this->fontdecls = '<office:font-decls>';
	$this->autostyle = new XMLBranch('office:automatic-styles');
	$this->office = new XMLBranch('office:body');
	$this->sequencedecls = new XMLBranch('text:sequence-decls');

	$headline1 = new XMLBranch('text:sequence-decl');
	$headline1->setTagAttribute('text:display-outline-level', '0');
	$headline1->setTagAttribute('text:name', 'Illustration');
	
	$headline2 = new XMLBranch('text:sequence-decl');
	$headline2->setTagAttribute('text:display-outline-level', '0');
	$headline2->setTagAttribute('text:name', 'Table');
	
	$headline3 = new XMLBranch('text:sequence-decl');
	$headline3->setTagAttribute('text:display-outline-level', '0');
	$headline3->setTagAttribute('text:name', 'Text');
	
	$headline4 = new XMLBranch('text:sequence-decl');
	$headline4->setTagAttribute('text:display-outline-level', '0');
	$headline4->setTagAttribute('text:name', 'Drawing');

	$this->sequencedecls->addXMLBranch($headline1);
	$this->sequencedecls->addXMLBranch($headline2);
	$this->sequencedecls->addXMLBranch($headline3);
	$this->sequencedecls->addXMLBranch($headline4);
	
	$this->office->addXMLBranch($this->sequencedecls);
		
	$this->cursor = '';
// 	$this->cursor->setTagAttribute('text:style-name', $this->paragstyle);
	// End content.xml

	// styles.xml
	$this->sfontdecls = '<office:font-decls>';
	$this->sstyle = new XMLBranch('office:styles');
	$this->sautostyle = new XMLBranch('office:automatic-styles');
	$this->masterstyles = new XMLBranch('office:master-styles');
	// End styles.xml

	// meta.xml
	$this->meta = '<meta:generator>PHP DocWriter '.phpdocwriter_version.'</meta:generator>';
	$this->meta .= '<meta:creation-date>'.date ("Y-m-d\\TH:i:s").'</meta:creation-date>';
	// End meta.xml
	
	// settings.xml
	// End settings.xml
	
	// manifest.xml
	$this->manifest = new XML('manifest:manifest');
	$this->manifest->setTagAttribute('xmlns:manifest', 'http://openoffice.org/2001/manifest');
	// End manifest.xml
}

function Font($params)
{
// 	if (!array_key_exists('family', $params))
// 		$this->_error('You must define a font family');
	// Load defaults
// 	if (!array_key_exists('size', $params))
// 		$params['size']=12;
	if (!is_int(strpos($this->sfontdecls,$params['family'])))
	{
		$this->fontdecls .= '<style:font-decl style:name="'.$params['family'].'" fo:font-family="'.$params['family'].'" />';
		$this->sfontdecls .= '<style:font-decl style:name="'.$params['family'].'" fo:font-family="'.$params['family'].'"/>';
	}
	foreach($params as $key => $value)
	{
		switch ($key)
		{
			case "family":
				$this->styleprop->setTagAttribute('style:font-name', $value);
			break;
			case "size":
				$this->styleprop->setTagAttribute('fo:font-size', $value.'pt');
			break;
			case "style":
				if(is_int(strpos($value,'U')))
				{
					$this->styleprop->setTagAttribute('style:text-underline' ,'single');
					$this->styleprop->setTagAttribute('style:text-underline-color', 'font-color');
				}
				if(is_int(strpos($value,'B')))
				{
					$this->styleprop->setTagAttribute('fo:font-weight', 'bold');
					$this->styleprop->setTagAttribute('style:font-weight-asian', 'bold');
					$this->styleprop->setTagAttribute('style:font-weight-complex', 'bold');
					$this->styleprop->setTagAttribute('style:font-size-asian', $value.'pt');
					$this->styleprop->setTagAttribute('style:font-size-complex', $value.'pt');
				}
				if(is_int(strpos($value,'I')))
				{
					$this->styleprop->setTagAttribute('fo:font-style', 'italic');
					$this->styleprop->setTagAttribute('style:font-style-asian', 'italic');
					$this->styleprop->setTagAttribute('style:font-style-complex', 'italic');
				}
				if(is_int(strpos($value,'S')))
					$this->styleprop->setTagAttribute('fo:text-shadow');
				
				if(is_int(strpos($value,'R-emb')))
					$this->styleprop->setTagAttribute('style:font-relief', 'embossed');
	
				if(is_int(strpos($value,'R-eng')))
					$this->styleprop->setTagAttribute('style:font-relief', 'engraved');
	
				if(is_int(strpos($value,'CO')))
					$this->styleprop->setTagAttribute('style:text-crossing-out', 'single-line');
	
				if(is_int(strpos($value,'CO-d')))
					$this->styleprop->setTagAttribute('style:text-crossing-out', 'double-line');
	
				if(is_int(strpos($value,'CO-t')))
					$this->styleprop->setTagAttribute('style:text-crossing-out', 'thick-line');
				
				if(is_int(strpos($value,'CO-sl')))
					$this->styleprop->setTagAttribute('style:text-crossing-out', 'slash');
	
				if(is_int(strpos($value,'CO-x')))
					$this->styleprop->setTagAttribute('style:text-crossing-out', 'X');
	
				if(is_int(strpos($value,'TO')))
					$this->styleprop->setTagAttribute('style:text-outline', 'true');
			break;
			case "color":
				$this->styleprop->setTagAttribute('fo:color', $value);
			break;
			case "bgcolor":
				$this->styleprop->setTagAttribute('style:text-background-color', $value);
			break;
		}
	}
}

function SetStdFont($family='', $size='')
{
	if ($this->lang=='')
		$this->_error('Please, set the language and country of the document first');
	if (($family=='') && ($size==''))
	{
		$this->lastspan = $this->spanstyle;
		$this->spanstyle = '';
	}
	if (($family!='') && ($size!=''))
	{
		$this->fontdecls .= '<style:font-decl style:name="'.$family.'" fo:font-family="'.$family.'" />';

		/////<style:default-style style:family='graphics'>
		$style = new XMLBranch('style:default-style');
		$style->setTagAttribute('style:family', 'graphics');

		$prop = new XMLBranch('style:properties');
		$prop->setTagAttribute('draw:start-line-spacing-horizontal', '0.283cm');
		$prop->setTagAttribute('draw:start-line-spacing-vertical', '0.283cm');
		$prop->setTagAttribute('draw:end-line-spacing-horizontal', '0.283cm');
		$prop->setTagAttribute('draw:end-line-spacing-vertical', '0.283cm');
		$prop->setTagAttribute('style:use-window-font-color', 'true');
		$prop->setTagAttribute('style:font-name', $family);
		$prop->setTagAttribute('fo:font-size', $size.'pt');
		$prop->setTagAttribute('fo:language', $this->lang);
		$prop->setTagAttribute('fo:country', $this->country);
		$prop->setTagAttribute('style:font-name-asian', $family);
		$prop->setTagAttribute('style:font-size-asian', $size.'pt');
		$prop->setTagAttribute('style:language-asian', 'none');
		$prop->setTagAttribute('style:country-asian', 'none');
		$prop->setTagAttribute('style:font-name-complex', $family);
		$prop->setTagAttribute('style:font-size-complex', $size.'pt');
		$prop->setTagAttribute('style:language-complex', 'none');
		$prop->setTagAttribute('style:country-complex', 'none');
		$prop->setTagAttribute('style:text-autospace', 'ideograph-alpha');
		$prop->setTagAttribute('style:line-break', 'strict');
		$prop->setTagAttribute('style:writing-mode', 'lr-tb');

		$tab = new XMLBranch('style:tab-stops');

		$prop->addXMLBranch($tab);
		$style->addXMLBranch($prop);


		/////<style:default-style style:family='paragraph'>
		$style2 = new XMLBranch('style:default-style');
		$style2->setTagAttribute('style:family', 'paragraph');

		$prop = new XMLBranch('style:properties');
		$prop->setTagAttribute('style:use-window-font-color', 'true');
		$prop->setTagAttribute('style:font-name', $family);
		$prop->setTagAttribute('fo:font-size', $size.'pt');
		$prop->setTagAttribute('fo:language', $this->lang);
		$prop->setTagAttribute('fo:country', $this->country);
		$prop->setTagAttribute('style:font-name-asian', $family);
		$prop->setTagAttribute('style:font-size-asian', $size.'pt');
		$prop->setTagAttribute('style:language-asian', 'none');
		$prop->setTagAttribute('style:country-asian', 'none');
		$prop->setTagAttribute('style:font-name-complex', $family);
		$prop->setTagAttribute('style:font-size-complex', $size.'pt');
		$prop->setTagAttribute('style:language-complex', 'none');
		$prop->setTagAttribute('style:country-complex', 'none');
		$prop->setTagAttribute('fo:hyphenate', 'false');
		$prop->setTagAttribute('fo:hyphenation-remain-char-count', '2');
		$prop->setTagAttribute('fo:hyphenation-push-char-count', '2');
		$prop->setTagAttribute('fo:hyphenation-ladder-count', 'no-limit');
		$prop->setTagAttribute('style:text-autospace', 'ideograph-alpha');
		$prop->setTagAttribute('style:punctuation-wrap', 'hanging');
		$prop->setTagAttribute('style:line-break', 'strict');
		$prop->setTagAttribute('style:tab-stop-distance', '1.251cm');
		$prop->setTagAttribute('style:writing-mode', 'page');

		$style2->addXMLBranch($prop);
		
		$this->sstyle->addXMLBranch($style);
		$this->sstyle->addXMLBranch($style2);
		
		/////<style:style>
		$style = new XMLBranch('style:style');
		$style->setTagAttribute('style:name', 'Standard');
		$style->setTagAttribute('style:family', 'paragraph');
		$style->setTagAttribute('style:class', 'text');

		$this->sstyle->addXMLBranch($style);
		
		$this->fontdef=1;
		
		/////<style:style>
		$style = new XMLBranch('style:style');
		$style->setTagAttribute('style:name', 'Header');
		$style->setTagAttribute('style:family', 'paragraph');
		$style->setTagAttribute('style:parent-style-name', 'Standard');
		$style->setTagAttribute('style:class', 'extra');
	
		/////<style:propieties>
		$headline2 = new XMLBranch('style:properties');
		$headline2->setTagAttribute('text:number-lines', 'false');
		$headline2->setTagAttribute('text:line-number', '0');
	
		$headline3 = new XMLBranch('style:tab-stops');
	
		$headline4 = new XMLBranch('style:tab-stops');
		$headline4->setTagAttribute('style:position', '8.498cm');
		$headline4->setTagAttribute('style:type', 'center');
	
		$headline5 = new XMLBranch('style:tab-stops');
		$headline5->setTagAttribute('style:position', '16.999cm');
		$headline5->setTagAttribute('style:type', 'right');
	
		$headline3->addXMLBranch($headline4);
		$headline3->addXMLBranch($headline5);
		$headline2->addXMLBranch($headline3);
		$style->addXMLBranch($headline2);
		
		$this->sstyle->addXMLBranch($style);
		
		/////<style:style>
		$style = new XMLBranch('style:style');
		$style->setTagAttribute('style:name', 'Footer');
		$style->setTagAttribute('style:family', 'paragraph');
		$style->setTagAttribute('style:parent-style-name', 'Standard');
		$style->setTagAttribute('style:class', 'extra');
	
		/////<style:propieties>
		$headline2 = new XMLBranch('style:properties');
		$headline2->setTagAttribute('text:number-lines', 'false');
		$headline2->setTagAttribute('text:line-number', '0');
	
		$headline3 = new XMLBranch('style:tab-stops');
	
		$headline4 = new XMLBranch('style:tab-stops');
		$headline4->setTagAttribute('style:position', '8.498cm');
		$headline4->setTagAttribute('style:type', 'center');
	
		$headline5 = new XMLBranch('style:tab-stops');
		$headline5->setTagAttribute('style:position', '16.999cm');
		$headline5->setTagAttribute('style:type', 'right');
	
		$headline3->addXMLBranch($headline4);
		$headline3->addXMLBranch($headline5);
		$headline2->addXMLBranch($headline3);
		$style->addXMLBranch($headline2);
		
		$this->sstyle->addXMLBranch($style);
	}
}

function SetFont($params)
{
	if (!$style_no = $this->_searchfont ($params))
	{
		/////<style:style>
		$this->style = new XMLBranch('style:style');
		$this->style->setTagAttribute('style:name', 'T'.$this->textno);
		$this->style->setTagAttribute('style:family', 'text');
		/////<style:propieties>
		$this->styleprop = new XMLBranch('style:properties');
		/////<style:style> <- <style:propieties>
		$this->style->addXMLBranch($this->styleprop);
		/////<office:automatic-styles> <- <style:style>
		$this->autostyle->addXMLBranch($this->style);

		array_push ($this->fontlist, &$params);
		$this->spanstyle='T'.$this->textno;
		$this->textno++;
		
		$this->Font($params);
	}
	else $this->spanstyle=$style_no;
}

function AddPageDef($params)
{
	if (!array_key_exists('name', $params))
		$this->_error('Please, specify a name for all pages');

	// Load defaults
	if (!array_key_exists('w', $params))
		$params['w']=21;
	if (!array_key_exists('h', $params))
		$params['h']=29.7;
	if (!array_key_exists('header-margin', $params))
		$params['header-margin']=0.5;
	if (!array_key_exists('footer-margin', $params))
		$params['footer-margin']=0.5;
	if (!array_key_exists('margins', $params))
		$params['margins']='2,2,2,2';
		
	$pmasterno = $this->_getPageno();
	$style = new XMLBranch('style:page-master');
	$style->setTagAttribute('style:name', $pmasterno);
		
	$styleprop = new XMLBranch('style:properties');
	$style->addXMLBranch($styleprop);
	
	$hstyle = new XMLBranch('style:header-style');
	$hstyleprop = new XMLBranch('style:properties');
	$hstyle->addXMLBranch($hstyleprop);
	$style->addXMLBranch($hstyle);
	$fstyle = new XMLBranch('style:footer-style');
	$fstyleprop = new XMLBranch('style:properties');
	$fstyle->addXMLBranch($fstyleprop);
	$style->addXMLBranch($fstyle);
	
	$this->sautostyle->addXMLBranch($style);
	$temp = new XMLBranch('style:master-page');
	$this->masterstyles->addXMLBranch($temp);
	
	foreach($params as $key => $value)
	{
		switch ($key)
		{
			case 'w':
				$styleprop->setTagAttribute('fo:page-width', $value.'cm');
			break;
			case 'h':
				$styleprop->setTagAttribute('fo:page-height', $value.'cm');
			break;
			case 'margins':
					$margin = explode(',', $value);
					$styleprop->setTagAttribute('fo:margin-left', $margin[0].'cm');
					$styleprop->setTagAttribute('fo:margin-right', $margin[1].'cm');
					$styleprop->setTagAttribute('fo:margin-top', $margin[2].'cm');
					$styleprop->setTagAttribute('fo:margin-bottom', $margin[3].'cm');
			break;
// 			case 'border':
// 			break;
			case 'numformat':
				$styleprop->setTagAttribute('style:num-format', $value);
			break;
			case 'header-margin':
				$hstyleprop->setTagAttribute('fo:margin-bottom', $value.'cm');
			break;
			case 'footer-margin':
				$fstyleprop->setTagAttribute('fo:margin-top', $value.'cm');
			break;
			case 'name':
				$temp->setTagAttribute('style:name', $value);
				$temp->setTagAttribute('style:page-master-name', $pmasterno);
			break;
			case 'next':
				$temp->setTagAttribute('style:next-style-name', $value);
			break;
		}
	}
}

function AddParaDef($params)
{
	$gname = 0;
	$family = 'paragraph';
	$stylebranch =& $this->sstyle;
	$this->style =& new XMLBranch('style:style');
	$this->styleprop =& new XMLBranch('style:properties');
	if (!array_key_exists('name', $params))
	{
		$gname = 'P'.$this->parano++;
		$this->style->setTagAttribute('style:name', $gname);
		$stylebranch =& $this->autostyle;
	}
	elseif(is_int(strpos(strtolower($params['name']),'internet link')))
		$family = 'text';

	$this->style->addXMLBranch($this->styleprop);
	$this->style->setTagAttribute('style:family', $family);
	$this->style->setTagAttribute('style:parent-style-name', 'Standard');
	$this->style->setTagAttribute('style:class', 'text');
	$this->style->setTagAttribute('style:master-page-name', '');
	foreach($params as $key => $value)
	{
		switch ($key)
		{
			case 'name':
				$this->style->setTagAttribute('style:name', $value);
			break;
			case 'align':
				$this->styleprop->setTagAttribute("fo:text-align", $value);
			break;
			case 'indent':
				$this->styleprop->setTagAttribute('fo:text-indent', $value.'cm');
			break;
			case 'font':
				$this->Font($params['font']);
			break;
		}
	}
	$this->_style($params,$this->styleprop);
	$stylebranch->addXMLBranch($this->style);
	
	if ($gname) 
		return $gname; 
}

function SetParagraph($name='')
{
	if ($name == '')
		$name = 'Standard';
	
	$this->paragstyle = $name;
	
	if (is_object ($this->cursor))
	{
		if ($this->cursor->getTagContent()!='') {
			$this->cursor = $this->_getbranch();
		}
		else {
			if ($this->newpage) {
// 				$this->pagestyle->setTagAttribute('style:parent-style-name', $this->paragstyle);
				}
			else
				$this->cursor->setTagAttribute('text:style-name', $this->paragstyle);
		}
	}
	$this->spanstyle='';
	$this->lastspan='';
}

function AddPage($template='Standard')
{
	$this->template = $template;
	$this->cursor = $this->_getbranch();
	$this->newpage = 1;
}

function Ln($num=1) {
	for ($i = 1; $i <= $num; $i++)
		$this->cursor = $this->_getbranch();
}

function _getbranch($branch='text:p')
{
	if ($this->newpage) {
		$parano = $this->parano++;
		$this->pagestyle = new XMLBranch('style:style');
		$this->pagestyle->setTagAttribute('style:name', 'P'.$parano);
		$this->pagestyle->setTagAttribute('style:family', 'paragraph');
		$this->pagestyle->setTagAttribute('style:parent-style-name', $this->paragstyle);
		$this->pagestyle->setTagAttribute('style:master-page-name', $this->template);
		$this->autostyle->addXMLBranch($this->pagestyle);
		$this->cursor->setTagAttribute('text:style-name', 'P'.$parano);
		$this->newpage = 0;
	}
	if (is_object($this->cursor))
		$this->office->addXMLBranch($this->cursor);
	$newbranch = new XMLBranch($branch);
	if ($branch=='text:p')
		$newbranch->setTagAttribute('text:style-name', $this->paragstyle);
	return $newbranch;
}

function _pushbranch() {
	$this->office->addXMLBranch($this->cursor);
}

function Write($text)
{
// 	print get_class($this)."<br>";
	if (!$this->fontdef)
		$this->_error('You must set the standard font first');
	
	if ($this->cursor == '')
		$this->cursor = $this->_getbranch();

// 	$text = $this->_replacechars($text);
	
	///// span declarado
	if ($this->spanstyle!='')
	{
		if ($this->spanstyle == $this->lastspan)
		{
			if ($spans = $this->cursor->getBranches('text:p', 'text:span', 'text:style-name', $this->spanstyle))
			{
				$add = new XMLLeaf($text);
				$spans[(count ($spans) - 1)]->addXMLLeaf($add);
			}
		}
			
		else
		{
			$span = new XMLBranch('text:span');
			$span->setTagContent($text);
			$span->setTagAttribute('text:style-name', $this->spanstyle);
			$this->cursor->addXMLBranch($span);
		}
	}
	///// no span
	else
	{
		$add = new XMLLeaf($text);
		$this->cursor->addXMLLeaf($add);
		$this->lastspan='';
	}
}

function AddLink($text,$url,$name='')
{
	$link = new XMLBranch('text:a');
	$link->setTagAttribute('xlink:type', 'simple');
	$link->setTagAttribute('xlink:href', $url);
	$link->setTagContent($text);
	if ($name!='') $link->setTagAttribute('office:name', $name);
	
	if ($this->spanstyle=='')
		$this->cursor->addXMLBranch($link);
	else
	{
		if ($this->spanstyle == $this->lastspan)
		{
			if ($cursor = $this->cursor->getBranches('text:p', 'text:span', 'text:style-name', $this->spanstyle))
				$cursor[(count ($cursor)-1)]->addXMLBranch($link);
		}
		else
		{
			$span = new XMLBranch('text:span');
			$span->setTagAttribute('text:style-name', $this->spanstyle);
			$span->addXMLBranch($link);
			$this->cursor->addXMLBranch($span);
			
			$this->lastspan='';
		}
	}
}

function AddBookmark($name)
{
	$bookmark = new XMLBranch('text:bookmark');
	$bookmark->setTagAttribute('text:name', $name);
	$this->cursor->addXMLBranch($bookmark);
}

function _frame(&$params,&$frameprop)
{
	foreach($params as $key => $value)
	{
		switch ($key)
		{
			case 'x':
				$frameprop->setTagAttribute('svg:x', $value.'cm');
			break;
			case 'y':
				$frameprop->setTagAttribute('svg:y', $value.'cm');
			break;
			case 'w':
				$frameprop->setTagAttribute('svg:width', $value.'cm');
			break;
			case 'h':
				$frameprop->setTagAttribute('svg:height', $value.'cm');
			break;
			case 'z':
				$frameprop->setTagAttribute('draw:z-index', $value);
			break;
			case 'anchor':
				$frameprop->setTagAttribute('text:anchor-type', $value);
			break;
			case 'min-h':
				$frameprop->setTagAttribute("fo:min-height", $value."cm");
			break;
			case 'max-h':
				$frameprop->setTagAttribute("fo:max-height", $value."cm");
			break;
			case 'min-w':
				$frameprop->setTagAttribute("fo:min-width", $value."cm");
			break;
			case 'max-w':
				$frameprop->setTagAttribute("fo:max-width", $value."cm");
			break;
		}
	}
}

function Image($params)
{
	$tmpfile = $this->_addimage($params[path]);
	if (!array_key_exists('path', $params) || !array_key_exists('w', $params) || !array_key_exists('h', $params))
		$this->_error('You must define path, width and height for images');
	// Load defaults
	if (!array_key_exists('anchor', $params))
		$params['anchor']='paragraph';
	if (!array_key_exists('wrap', $params))
		$params['wrap']='none';
	if (!array_key_exists('h-pos', $params))
		$params['h-pos']='center';
	if (!array_key_exists('h-rel', $params))
		$params['h-rel']='paragraph';
	if (!array_key_exists('v-pos', $params))
		$params['h-pos']='from-top';
	if (!array_key_exists('v-rel', $params))
		$params['h-rel']='paragraph';
		
	$frame_no = $this->frameno++;
	
	$style = new XMLBranch('style:style');
	$style->setTagAttribute('style:name', 'fr'.$frame_no);
	$style->setTagAttribute('style:family', 'graphics');
	$style->setTagAttribute('style:parent-style-name', 'Graphics');
	
	$styleprop = new XMLBranch('style:properties');
	$style->addXMLBranch($styleprop);
	$this->autostyle->addXMLBranch($style);
	
	$styleprop->setTagAttribute('style:mirror', 'Graphics');
	$styleprop->setTagAttribute('fo:clip', 'rect(0cm 0cm 0cm 0cm)');
	$styleprop->setTagAttribute('draw:color-mode', 'standard');
	
	$drawimage = new XMLBranch('draw:image');
	$drawimage->setTagAttribute('draw:style-name', 'fr'.$frame_no);
	$drawimage->setTagAttribute('draw:name', 'Image1');
	$drawimage->setTagAttribute('xlink:type', 'simple');
	$drawimage->setTagAttribute('xlink:show', 'embed');
	$drawimage->setTagAttribute('xlink:actuate', 'onLoad');
	if ($this->cursor == '')
		$this->cursor = $this->_getbranch();
	$this->cursor->addXMLBranch($drawimage);
	
	foreach($params as $key => $value)
	{
		switch ($key)
		{
			case 'path':
				$drawimage->setTagAttribute('xlink:href', '#Pictures/'.$tmpfile);
			break;
			case 'lum':
				$styleprop->setTagAttribute('draw:luminance', $value.'%');
			break;
			case 'con':
				$styleprop->setTagAttribute('draw:contrast', $value.'%');
			break;
			case 'red':
				$styleprop->setTagAttribute('draw:red', $value.'%');
			break;
			case 'green':
				$styleprop->setTagAttribute('draw:green', $value.'%');
			break;
			case 'blue':
				$styleprop->setTagAttribute('draw:blue', $value.'%');
			break;
			case 'gamma':
				$styleprop->setTagAttribute('draw:gamma', $value);
			break;
			case 'inv':
				$styleprop->setTagAttribute('draw:color-inversion', $value);
			break;
			case 'trans':
				$styleprop->setTagAttribute('draw:transparency', $value.'%');
			break;
		}
	}
	$this->_style($params,$styleprop);
	$this->_frame($params,$drawimage);
}


function Table($header, $data='', $familyh='', $sizeh='', $styleh='', $alignh='', $familyd='', $sized='', $styled='', $alignd='')
{

	///// <automatic>
	
	$style = new XMLBranch('style:style');
	$style->setTagAttribute('style:name', $this->tableno);

	$headline2 = new XMLBranch('style:properties');
	$headline2->setTagAttribute('style:width', '16.999cm');
	$headline2->setTagAttribute('table:align', 'margins');

	$style->addXMLBranch ($headline2);
	$this->autostyle->addXMLBranch($style);

	$style->setTagAttribute('style:name', $this->tableno.'.A');
	$style->setTagAttribute('style:family', 'table-column');

	$headline2->setTagAttribute('style:column-width', '16.999cm');
	$headline2->setTagAttribute('style:rel-column-width', '13107*');

	$style->addXMLBranch ($headline2);
	$this->autostyle->addXMLBranch($style);
	

	$style = new XMLBranch('style:style');
	$style->setTagAttribute('style:name', $this->tableno.'.A1');
	$style->setTagAttribute('style:family', 'table-cell');

	$headline2 = new XMLBranch('style:properties');
	$headline2->setTagAttribute('fo:padding', '0.097cm');
	$headline2->setTagAttribute('fo:border-left', '0.002cm solid #000000');
	$headline2->setTagAttribute('fo:border-right', 'none');
	$headline2->setTagAttribute('fo:border-top', '0.002cm solid #000000');
	$headline2->setTagAttribute('fo:border-bottom', '0.002cm solid #000000');

	$style->addXMLBranch ($headline2);
	$this->autostyle->addXMLBranch($style);
	
	$headline1 = new XMLBranch('style:style');
	$headline1->setTagAttribute('style:name', $this->tableno.'.E1');
	$headline1->setTagAttribute('style:family', 'table-cell');

	$headline2 = new XMLBranch('style:properties');
	$headline2->setTagAttribute('fo:padding', '0.097cm');
	$headline2->setTagAttribute('fo:border', '0.002cm solid #000000');

	$headline1->addXMLBranch ($headline2);
	$this->autostyle->addXMLBranch($headline1);

	$headline1 = new XMLBranch('style:style');
	$headline1->setTagAttribute('style:name', $this->tableno.'.A2');
	$headline1->setTagAttribute('style:family', 'table-cell');

	$headline2 = new XMLBranch('style:properties');
	$headline2->setTagAttribute('fo:padding', '0.097cm');
	$headline2->setTagAttribute('fo:border-left', '0.002cm solid #000000');
	$headline2->setTagAttribute('fo:border-right', 'none');
	$headline2->setTagAttribute('fo:border-top', 'none');
	$headline2->setTagAttribute('fo:border-bottom', '0.002cm solid #000000');

	$headline1->addXMLBranch ($headline2);
	$this->autostyle->addXMLBranch($headline1);
	
	$headline1 = new XMLBranch('style:style');
	$headline1->setTagAttribute('style:name', $this->tableno.'.E2');
	$headline1->setTagAttribute('style:family', 'table-cell');

	$headline2 = new XMLBranch('style:properties');
	$headline2->setTagAttribute('fo:padding', '0.097cm');
	$headline2->setTagAttribute('fo:border-left', '0.002cm solid #000000');
	$headline2->setTagAttribute('fo:border-right', '0.002cm solid #000000');
	$headline2->setTagAttribute('fo:border-top', 'none');
	$headline2->setTagAttribute('fo:border-bottom', '0.002cm solid #000000');

	$headline1->addXMLBranch ($headline2);
	$this->autostyle->addXMLBranch($headline1);

	///// <>
	$this->cursor = $this->_getbranch('table:table');
	$this->cursor->setTagAttribute('table:name', $this->tableno);
	$this->cursor->setTagAttribute('style:family', 'table');

	$headline1_2 = new XMLBranch('table:table-column');
	$headline1_2->setTagAttribute('table:style-name', $this->tableno.'.A');
	$headline1_2->setTagAttribute('table:number-columns-repeated', count($header));

	///// Encabezado de la tabla
	$headline1_3 = new XMLBranch('table:table-header-rows');

	$headline3_1 = new XMLBranch('table:table-row');

	$i = count ($header);
	foreach ($header as $head)
	{
		$i--;
		$headline3_1_1 = new XMLBranch('table:table-cell');
		if ($i) $headline3_1_1->setTagAttribute('table:style-name', $this->tableno.'.A1');
		else $headline3_1_1->setTagAttribute('table:style-name', $this->tableno.'.E1');
		$headline3_1_1->setTagAttribute('table:value-type', 'string');

		$headline3_1_1_1 = new XMLBranch('text:p');
		$headline3_1_1_1->setTagAttribute('text:style-name', 'Table Heading');
		$headline3_1_1_1->setTagContent($head);

		$headline3_1_1->addXMLBranch ($headline3_1_1_1);
		$headline3_1->addXMLBranch ($headline3_1_1);
	}
	$headline1_3->addXMLBranch ($headline3_1);
	///// Fin del encabezado
	$this->cursor->addXMLBranch ($headline1_2);
	$this->cursor->addXMLBranch ($headline1_3);
	///// Datos de la tabla
	foreach ($data as $dataarray)
	{
		$headline1_4 = new XMLBranch('table:table-row');
		$j = count ($dataarray);
		foreach ($dataarray as $row)
		{
			$j--;
			$headline4_1 = new XMLBranch('table:table-cell');
			if ($j) $headline4_1->setTagAttribute('table:style-name', $this->tableno.'.A2');
			else $headline4_1->setTagAttribute('table:style-name', $this->tableno.'.E2');
			$headline4_1->setTagAttribute('table:value-type', 'string');

			$headline4_1_1 = new XMLBranch('text:p');
			$headline4_1_1->setTagAttribute('text:style-name', 'Table Contents');
			$headline4_1_1->setTagContent($row);

			$headline4_1->addXMLBranch ($headline4_1_1);
			$headline1_4->addXMLBranch ($headline4_1);
		}
		$this->cursor->addXMLBranch ($headline1_4);
	}

	$this->cursor = $this->_getbranch();
	
	/////style:name='Text body'
	$headline1 = new XMLBranch('style:style');
	$headline1->setTagAttribute('style:name', 'Text body');
	$headline1->setTagAttribute('style:family', 'paragraph');
	$headline1->setTagAttribute('style:parent-style-name', 'Standard');
	$headline1->setTagAttribute('style:class', 'text');

	$headline2 = new XMLBranch('style:properties');
	$headline2->setTagAttribute('fo:margin-top', '0cm');
	$headline2->setTagAttribute('fo:margin-bottom', '0.212cm');

	$headline1->addXMLBranch ($headline2);
	$this->sstyle->addXMLBranch($headline1);
	/////style:name='Table Contents'
	$headline1 = new XMLBranch('style:style');
	$headline1->setTagAttribute('style:name', 'Table Contents');
	$headline1->setTagAttribute('style:family', 'paragraph');
	$headline1->setTagAttribute('style:parent-style-name', 'Text body');
	$headline1->setTagAttribute('style:class', 'extra');

	$headline2 = new XMLBranch('style:properties');
	$headline2->setTagAttribute('text:number-lines', 'false');
	$headline2->setTagAttribute('text:line-number', '0');

	$headline1->addXMLBranch ($headline2);
	$this->sstyle->addXMLBranch($headline1);
	/////style:name='Table Heading'
	$headline1 = new XMLBranch('style:style');
	$headline1->setTagAttribute('style:name', 'Table Heading');
	$headline1->setTagAttribute('style:family', 'paragraph');
	$headline1->setTagAttribute('style:parent-style-name', 'Table Contents');
	$headline1->setTagAttribute('style:class', 'extra');

	$headline2 = new XMLBranch('style:properties');
	$headline2->setTagAttribute('fo:font-style', 'italic');
	$headline2->setTagAttribute('fo:font-weight', 'bold');
	$headline2->setTagAttribute('style:font-style-asian', 'italic');
	$headline2->setTagAttribute('style:font-weight-asian', 'bold');
	$headline2->setTagAttribute('style:font-style-complex', 'italic');
	$headline2->setTagAttribute('style:font-weight-complex', 'bold');
	$headline2->setTagAttribute('fo:text-align', 'center');
	$headline2->setTagAttribute('style:justify-single-word', 'false');
	$headline2->setTagAttribute('text:number-lines', 'false');
	$headline2->setTagAttribute('text:line-number', '0');

	$headline1->addXMLBranch ($headline2);
	$this->sstyle->addXMLBranch($headline1);

	$this->tableno++;
}

function PageNo($adjust=0, $select='current')
{
	if ($this->cursor == '')
		$this->cursor = $this->_getbranch();
	$page_no = new XMLBranch('text:page-number');
	if ($adjust) $page_no->setTagAttribute('text:page-adjust', $adjust);
	if ($select!='current') $page_no->setTagAttribute('text:select-page', $select);

	$this->cursor->addXMLBranch($page_no);
}

function NoPages()
{
	if ($this->cursor == '')
		$this->cursor = $this->_getbranch();
	$no_pages = new XMLBranch('text:page-count');
	$this->cursor->addXMLBranch($no_pages);
}

function SetLanguage($lang, $country)
{
	if (!is_int(strpos($this->meta,'<dc:language>')))
		$this->meta .= '<dc:language>'.$lang.'-'.$country.'</dc:language>';
	
	$this->lang = $lang;
	$this->country = $country;
}

function SetTitle($title)
{
	if (!is_int(strpos($this->meta,'<dc:title>')))
		$this->meta .= '<dc:title>'.$title.'</dc:title>';
}

function SetAuthor($author)
{
	if (!is_int(strpos($this->meta,'<meta:initial-creator>')))
		$this->meta .= '<meta:initial-creator>'.$author.'</meta:initial-creator>';
}

function SetSubject($subject)
{
	if (!is_int(strpos($this->meta,'<dc:subject>')))
		$this->meta .= '<dc:subject>'.$subject.'</dc:subject>';
}

function SetDescription($description)
{
	if (!is_int(strpos($this->meta,'<dc:description>')))
		$this->meta .= '<dc:description>'.$description.'</dc:description>';
}

function SetKeywords($keywords)
{
	if (!is_int(strpos($this->meta,'<meta:keywords>')))
	{
		$keyword = explode(',', $keywords);
		$this->meta .= '<meta:keywords>';
		foreach($keyword as $word)
		{
			$this->meta .= '<meta:keyword>'.$word.'</meta:keyword>';
		}
		$this->meta .= '</meta:keywords>';
	}
}

function AddFootnote($text,$paragstyle='')
{
	$headline1 = new XMLBranch('text:footnote');
	$headline1->setTagAttribute('text:id', 'ftn'.$this->fnt++);
	
	$headline12 = new XMLBranch('text:footnote-citation');
	$headline12->setTagContent($this->fnt);
	
	$headline13 = new XMLBranch('text:footnote-body');
	$headline131 = new XMLBranch('text:p');
	$headline131->setTagAttribute('text:style-name', $this->paragstyle);
	$headline131->setTagContent($text);

	$headline13->addXMLBranch($headline131);
	$headline1->addXMLBranch($headline12);
	$headline1->addXMLBranch($headline13);
		
	$this->cursor->addXMLBranch($headline1);
}

function SetFootnoteStyle($mleft = 0.499, $mright = 0, $fontsize = 10, $numformat = 1)
{
	if (!$headlines = $this->styles->getBranches('office:document-styles/office:styles', 'style:style', 'style:name', 'Footnote'));
	{
		$headline1 = new XMLBranch('style:style');
		$headline1->setTagAttribute('style:name', $this->paragstyle);
		$headline1->setTagAttribute('style:family', 'paragraph');
		$headline1->setTagAttribute('style:parent-style-name', 'Standard');
		$headline1->setTagAttribute('style:class', 'extra');
		
		
		$headline11 = new XMLBranch('style:properties');
		$headline11->setTagAttribute('fo:margin-left', $mleft.'cm');
		$headline11->setTagAttribute('fo:margin-right', $mright.'cm');
		$headline11->setTagAttribute('fo:font-size', $fontsize.'pt');
		$headline11->setTagAttribute('style:font-size-asian', $fontsize.'pt');
		$headline11->setTagAttribute('style:font-size-complex', $fontsize.'pt');
		$headline11->setTagAttribute('fo:text-indent', '-0.499cm');
		$headline11->setTagAttribute('style:auto-text-indent', 'false');
		$headline11->setTagAttribute('text:number-lines', 'false');
		$headline11->setTagAttribute('text:line-number', '0');

		$headline1->addXMLBranch($headline11);
		
		$headline2 = new XMLBranch('style:style');
		$headline2->setTagAttribute('style:name', 'Footnote Symbol');
		$headline2->setTagAttribute('style:family', 'text');
		
		$headline3 = new XMLBranch('style:style');
		$headline3->setTagAttribute('style:name', 'Footnote anchor');
		$headline3->setTagAttribute('style:family', 'text');
		
		$headline31 = new XMLBranch('style:properties');
		$headline31->setTagAttribute('style:text-position', 'super 58%');

		$headline3->addXMLBranch($headline31);
		
		$headline4 = new XMLBranch('text:footnotes-configuration');
		$headline4->setTagAttribute('text:citation-style-name', 'Footnote Symbol');
		$headline4->setTagAttribute('text:citation-body-style-name', 'Footnote anchor');
		$headline4->setTagAttribute('style:num-format', $numformat);
		$headline4->setTagAttribute('text:start-value', '0');
		$headline4->setTagAttribute('text:footnotes-position', 'page');
		$headline4->setTagAttribute('text:start-numbering-at', 'document');
		
		$headlines = $this->styles->getBranches('office:document-styles', 'office:styles');
		$end = count ($headlines) - 1;
		$headlines[0]->addXMLBranch($headline1);
		$headlines[0]->addXMLBranch($headline2);
		$headlines[0]->addXMLBranch($headline3);
		$headlines[0]->addXMLBranch($headline4);
	}
}


//////
//////     Métodos privados
//////

function _searchfont(&$afont)
{
	$i=0;
	foreach($this->fontlist as $font) {
		$i++;
		if ($font==$afont)
			return 'T'.$i;
	}
	return 0;
}

function _searchpage(&$apage)
{
	$i=0;
	foreach($this->pagelist as $page) {
		$i++;
		if ($page==$apage)
			return 'pm'.$i;
	}
	return 0;
}

function _addimage($file)
{
	if (!$tmpfile = array_search($file, $this->imglist))
	{
		$tmpfile = rand().'.'.substr($file, strlen($file)-3);
		$filecontent = @file_get_contents($file) or $this->_error('Can\'t find the image "'.$file.'"');
		$this->zip->addFile ($filecontent, 'Pictures/'.$tmpfile);
		$this->imglist[$tmpfile] = $file;
		$this->images=1;
	}
	return $tmpfile;
}

function _getFrameno()
{
	return $this->frameno++;
}

function _getPageno()
{
	return $this->pmasterno++;
}

function Insert()
{
	$this->office->addXMLBranch($this->cursor);
}

function _replacechars($text)
{
	return str_replace('<','&lt;', str_replace('>','&gt;', $text));
}

}
?>
