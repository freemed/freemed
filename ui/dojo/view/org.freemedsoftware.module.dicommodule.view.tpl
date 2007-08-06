<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // Copyright (C) 1999-2007 FreeMED Software Foundation
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
*}-->

<style type="text/css">
	#viewClose {
		color: #555555;
		text-decoration: underline;
		}
	#viewClose:hover {
		color: #ff5555;
		cursor: pointer;
		}
</style>

<h3><!--{t}-->DICOM<!--{/t}--> [ <a onClick="freemedPatientContentLoad('<!--{$controller}-->/org.freemedsoftware.ui.patient.overview.default?patient=<!--{$patient}-->');" id="viewClose">X</a> ]</h3>

<table border="0">
<tr><td align="center">
<img src="<!--{$relay}-->/org.freemedsoftware.module.DicomModule.GetDICOM?param0=<!--{$patient}-->&param1=<!--{$id}-->&param2=true" border="0" style="max-width: 100%; max-height: 95%;" />
</td></tr>
</table>

<!--
<applet archive="<!--{$htdocs}-->/radscaper/radscaper.jar" codebase="./" code="com.divinev.radscaper.Main.class" width="100%" height="100%">
<PARAM NAME="Config" VALUE="config.xml" />
<PARAM NAME="DicomImg1" VALUE="<!--{$relay}-->/org.freemedsoftware.module.DicomModule.GetDICOM?param0=<!--{$patient}-->&param1=<!--{$id}-->" />
</applet>
-->

