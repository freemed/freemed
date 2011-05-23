<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
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
*}-->

<h3><!--{t}-->Photographic Identification<!--{/t}--></h3>

<div style="padding: 1em;">
	<!--{t}-->This is the upload form for photographic identification. It allows more recent photos to non-destructively replace identification photographs for patients. Select the picture (JPG, PNG, or GIF) and it will be uploaded.<!--{/t}-->
</div>

<div align="center">
<!--{include file='org.freemedsoftware.widget.uploadfiles.tpl' varname='file' completedCode="freemedPatientContentLoad('org.freemedsoftware.ui.patient.overview.default?patient=$patient');" relayPoint="$relay/org.freemedsoftware.module.photographicidentification.UploadPhotoID?param0=$patient"}-->
</div>

<div align="center">
        <table border="0" style="width:200px;">
        <tr><td align="center">
        	<button dojoType="Button" id="ModuleFormCancelButton" widgetId="ModuleFormCancelButton" onClick="freemedPatientContentLoad('org.freemedsoftware.ui.patient.overview.default?patient=<!--{$patient}-->');">
        	        <div><img src="<!--{$htdocs}-->/images/teak/x_stop.16x16.png" border="0" width="16" height="16" /> <!--{t}-->Cancel<!--{/t}--></div>
        	</button>
        </td></tr></table>
</div>

