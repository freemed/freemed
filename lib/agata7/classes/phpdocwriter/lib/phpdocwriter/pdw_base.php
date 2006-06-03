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

require_once(dirname(dirname(dirname(__FILE__))).'/conf/config.php');

import('active-link-xml.XML');
import('ziplib');

define('phpdocwriter_version','0.3');

class pdw_base
{
var $tmpdir;
var $filename;
var $filtername;
var $filterset;
var $compress;
var $debug;

function SetFileName($name)
{
	$this->filename = $name;
}

function CompressOutput($comp=1)
{
	$this->compress = $comp;
}

function SetExportFilter($filter)
{
	$this->filtername = strtoupper ($filter);
}

function _fillFilterData()
{
	switch ($this->filtername)
	{
		case 'SXW':
			$this->filterset = array ('application/vnd.sun.xml.writer', 'sxw');
		break;

		case 'PDF':
			$this->filterset = array ("application/pdf", "pdf");
		break;
		
		case 'MSXP':
		case 'MS95':
		case 'MS60':
			$this->filterset = array ("application/msword", "doc");
		break;
		
		case 'SW5':
		case 'SW4':
		case 'SW3':
			$this->filterset = array ('application/vnd.sun.xml.writer', 'sdw');
		break;
		
		case 'TXT':
		case 'TXTE':
			$this->filterset = array ("text/plain", "txt");
		break;
		
		case 'RTF':
			if ($this->images)
				$this->filterset = array ("application/zip", "zip");
			else
				$this->filterset = array ("application/rtf", "rtf");
		break;
		
		case 'HTML':
			if ($this->images)
				$this->filterset = array ("application/zip", "zip");
			else
				$this->filterset = array ("text/html", "html");
		break;
				
		case 'TEX':
			if ($this->images)
				$this->filterset = array ("application/zip", "zip");
			else
				$this->filterset = array ("application/x-tex", "tex");
		break;
		
		case 'XHTML10':
			if ($this->images)
				$this->filterset = array ("application/zip", "zip");
			else
				$this->filterset = array ("text/html", "html");
		break;
		
		case 'XHTML11':
			if ($this->images)
				$this->filterset = array ("application/zip", "zip");
			else
				$this->filterset = array ("text/html", "xhtml");
		break;
		
		default:
			$this->_error('Bad export format name.');
	}
}

function Output($dest='D')
{
	global $HTTP_SERVER_VARS;
	$this->Insert();
	$this->_gendoc();
	
	if ($this->compress && $this->filtername!='SXW')
		$this->filterset = array ("application/zip", "zip");
	else
		$this->_fillFilterData();
	
	if ($this->filtername=='SXW')
	{
		switch ($dest)
		{
			case 'D':
				Header('Content-Type: '.$this->filterset[0]);
				if(headers_sent())
					$this->_error('Some data has already been output to browser, can\'t send the file');
				Header('Cache-control: private');
				Header('Content-Length: '.strlen($this->zip->file()));
				Header('Content-Disposition: attachment; filename='.$this->filename.'.'.$this->filterset[1]);
				Header('Pragma: no-cache');
				Header('Expires: 0');
				echo $this->zip->file();
			break;
			case 'S':
				echo $this->zip->file();
			break;
			case 'F':
				$fp = fopen($path.$this->filename.'.'.$this->filterset[1], 'w+');
				fputs($fp, $this->zip->file());
				fclose($fp);
			break;
			default:
                # Implemented by Pablo Dall'Oglio 2004-06-17
                # in order to export the contents into a file
                $fd = fopen($dest, 'w');
                fputs($fd, $this->zip->file());
                fclose($fd);
            break;
			
		}
	}
	else
	{
		@mkdir (pdw_tmpdir.$this->tmpdir) or $this->_error ('Can\'t create temporary directories');
		chmod (pdw_tmpdir.$this->tmpdir, 0777);
		$fp = fopen(pdw_tmpdir.$this->tmpdir.$this->filename.'.sxw', 'w+');
		fputs($fp, $this->zip->file());
		fclose($fp);
		
		$command = export_script_path.' --'.$this->filtername.' '.pdw_tmpdir.$this->tmpdir.$this->filename.' 2>&1';
		
		$output = shell_exec($command);

		$this->filename .= '.'.$this->filterset[1];

		if ($output!='')
		{
			$this->_deldir(pdw_tmpdir.$this->tmpdir);
			if(isset($HTTP_SERVER_VARS['SERVER_NAME']))
				$this->_error(nl2br ($output));
			else
				$this->_error($output);
		}
		if($this->filterset[1]=='zip')
			$this->_zipdir(pdw_tmpdir.$this->tmpdir);
		
		// 
// 		$fp = fopen(pdw_tmpdir.$this->tmpdir.$this->filename, 'r');
//		readfile (pdw_tmpdir.$this->tmpdir.$this->filename);
		$aa = file_get_contents (pdw_tmpdir.$this->tmpdir.$this->filename);

		switch ($dest)
		{
			case 'D':
				if(isset($HTTP_SERVER_VARS['SERVER_NAME']))
				{
					header('Content-Type: '.$this->filterset[0]);
					if(headers_sent())
						$this->_error('Some data has already been output to browser, can\'t send the file');
					header('Cache-control: private');
					header('Content-Length: '.filesize(pdw_tmpdir.$this->tmpdir.$this->filename));
					header('Content-Disposition: attachment; filename='.$this->filename);
					header('Pragma: no-cache');
					header('Expires: 0');
// 					fpassthru($fp);
					echo $aa;
				}
			break;
			case 'S':
				fpassthru($fp);
			break;
			case 'F':
				@copy (pdw_tmpdir.$this->tmpdir.$this->filename, $this->filename) or $this->_error ('Can\'t create the output file: '.$this->filename);
			break;
		}
		$this->_deldir(pdw_tmpdir.$this->tmpdir);
	}

	if ($this->debug)
	{
		$this->tmpdir = 'dbgdoc/';
		$this->_deldir(pdw_tmpdir.$this->tmpdir);
		mkdir (pdw_tmpdir.$this->tmpdir);
		$fp = fopen(pdw_tmpdir.$this->tmpdir.$this->filename.'.sxw', 'w+');
		fputs($fp, $this->zip->file());
		fclose($fp);
		$command = 'unzip '.pdw_tmpdir.$this->tmpdir.$this->filename.'.sxw -d '.pdw_tmpdir.$this->tmpdir;
		shell_exec($command);
	}
// 	return '';
}

function _gendoc()
{

	$this->fontdecls .= '</office:font-decls>';
	$this->sfontdecls .= '</office:font-decls>';

	$this->zip->addFile(utf8_encode (
		"<?xml version=\"1.0\" encoding=\"UTF-8\"?".">\n".
		"<!DOCTYPE office:document-content PUBLIC \"-//OpenOffice.org//DTD OfficeDocument 1.0//EN\" \"office.dtd\">\n".
		'<office:document-content xmlns:office="http://openoffice.org/2000/office" xmlns:style="http://openoffice.org/2000/style" xmlns:text="http://openoffice.org/2000/text" xmlns:table="http://openoffice.org/2000/table" xmlns:draw="http://openoffice.org/2000/drawing" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:number="http://openoffice.org/2000/datastyle" xmlns:svg="http://www.w3.org/2000/svg" xmlns:chart="http://openoffice.org/2000/chart" xmlns:dr3d="http://openoffice.org/2000/dr3d" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="http://openoffice.org/2000/form" xmlns:script="http://openoffice.org/2000/script" office:class="text" office:version="1.0">'.
		$this->script->getXMLString().
		$this->fontdecls.
		$this->autostyle->getXMLString().
		$this->office->getXMLString().
		'</office:document-content>'
		), "content.xml");

	///// Creamos el archivo 'styles.xml'
	$this->zip->addFile(utf8_encode (
		"<?xml version=\"1.0\" encoding=\"UTF-8\"?".">\n".
		"<!DOCTYPE office:document-styles PUBLIC \"-//OpenOffice.org//DTD OfficeDocument 1.0//EN\" \"office.dtd\">\n".
		'<office:document-styles xmlns:office="http://openoffice.org/2000/office" xmlns:style="http://openoffice.org/2000/style" xmlns:text="http://openoffice.org/2000/text" xmlns:table="http://openoffice.org/2000/table" xmlns:draw="http://openoffice.org/2000/drawing" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:number="http://openoffice.org/2000/datastyle" xmlns:svg="http://www.w3.org/2000/svg" xmlns:chart="http://openoffice.org/2000/chart" xmlns:dr3d="http://openoffice.org/2000/dr3d" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="http://openoffice.org/2000/form" xmlns:script="http://openoffice.org/2000/script" office:version="1.0">'.
		$this->sfontdecls.
		$this->sstyle->getXMLString().
		$this->sautostyle->getXMLString().
		$this->masterstyles->getXMLString()).
		'</office:document-styles>', "styles.xml");
		
		
	///// Creamos el archivo 'meta.xml'
// 	$this->zip->addFile(utf8_encode (
// 		"<?xml version=\"1.0\" encoding=\"UTF-8\"?".">\n".
// 		"<!DOCTYPE office:document-meta PUBLIC \"-//OpenOffice.org//DTD OfficeDocument 1.0//EN\" \"office.dtd\">\n".
// 		$this->meta->getXMLString()), "meta.xml");
		$this->zip->addFile(utf8_encode (
		"<?xml version=\"1.0\" encoding=\"UTF-8\"?".">\n".
		"<!DOCTYPE office:document-meta PUBLIC \"-//OpenOffice.org//DTD OfficeDocument 1.0//EN\" \"office.dtd\">\n".
		'<office:document-meta xmlns:office="http://openoffice.org/2000/office" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="http://openoffice.org/2000/meta" office:version="1.0"><office:meta>'.$this->meta.'</office:meta></office:document-meta>'), "meta.xml");
		
		
	/// Creamos el archivo 'manifest.xml'
	$this->zip->addFile(utf8_encode (
		"<?xml version=\"1.0\" encoding=\"UTF-8\"?".">\n".
		"<!DOCTYPE manifest:manifest PUBLIC \"-//OpenOffice.org//DTD Manifest 1.0//EN\" \"Manifest.dtd\">\n"
		), "META-INF/manifest.xml");
	
	///// Creamos el archivo 'mimetype'
	$this->zip->addFile("application/vnd.sun.xml.writer\n", "mimetype");
}

function _debug()
{
	$this->debug = 1;
}

function _uniquename()
{
	return 'pdw'.rand().'/';
}

function _zipdir($dir)
{
	$zip = new zipfile();
	$current_dir = opendir($dir);
	while($entryname = readdir($current_dir))
	{
		if(!is_dir("$dir$entryname") and (substr($entryname, strlen($entryname)-3)!='sxw'))
			$zip->addFile(file_get_contents("$dir$entryname"), $entryname);
	}
	closedir($current_dir);
	$fp = fopen($dir.$this->filename, 'w+');
	fputs($fp, $zip->file());
	fclose($fp);
}

function _deldir($dir)
{
	if (is_dir($dir))
	{
		$current_dir = opendir($dir);
		while($entryname = readdir($current_dir))
		{
			if(is_dir("$dir/$entryname") and ($entryname != "." and $entryname!=".."))
				$this->_deldir("${dir}/${entryname}");
	
			elseif($entryname != "." and $entryname!="..")
				unlink("${dir}/${entryname}");
		}
		closedir($current_dir);
		rmdir(${dir});
	}
}

function _register()
{
	$this->objlist[] =& $this;
}

function _error($msg)
{
	adie('<b>PHP DocWriter error:</b> '.$msg);
}

}
?>
