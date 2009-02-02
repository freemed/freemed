<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // Copyright (C) 1999-2009 FreeMED Software Foundation
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

<script language="javascript">

	_container_.addOnLoad(function() {
		// Resize image properly
		try {
			var x = dojo.widget.byId( 'freemedContent' );
			var node = x.containerNode || x.domNode;
			var h = parseInt( node.style.height );
			var w = parseInt( node.style.width );
			var iH = document.getElementById( 'dicomImage' ).style.maxHeight = ( h ) + 'px';
			var iW = document.getElementById( 'dicomImage' ).style.maxWidth = ( w - document.getElementById( 'dicomInfo' ).style.width ) + 'px';

			//document.getElementById( 'dicomInfo' ).innerHTML += "h = " + h + "<br/>w = " + w + "<br/>";
			//document.getElementById( 'dicomInfo' ).innerHTML += "iH = " + iH + "<br/>iW = " + iW + "<br/>";
			
		} catch ( e ) { }
	});

</script>

<!--{method namespace="org.freemedsoftware.module.DicomModule.GetRecord" param0="$id" var="record"}-->

<h3><!--{t}-->DICOM<!--{/t}--> [ <a onClick="freemedPatientContentLoad('<!--{$controller}-->/org.freemedsoftware.ui.patient.overview.default?patient=<!--{$patient}-->');" id="viewClose">X</a> ]</h3>

<table border="0" style="height: 100%; width: 100%;">
<tr><td valign="middle" id="dicomInfo">

<div class="infoBox">

	<table border="0">

		<tr>
			<td><b><!--{t}-->Study<!--{/t}--></b></td>
			<td><small><!--{if $record.d_study_description}--><!--{$record.d_study_description|escape}--><!--{else}--><!--{t}-->NO DESCRIPTION<!--{/t}--><!--{/if}--></small></td>
		</tr>

		<tr>
			<td><b><!--{t}-->Date<!--{/t}--></b></td>
			<td><small><!--{$record.d_study_date|escape}--></small></td>
		</tr>

		<tr>
			<td><b><!--{t}-->Institution<!--{/t}--></b></td>
			<td><small><!--{$record.d_institution_name|escape}--></small></td>
		</tr>

		<tr>
			<td><b><!--{t}-->Storage<!--{/t}--></b></td>
			<td><small><!--{$record.storage_status|escape}--></small></td>
		</tr>

	</table>

	<div align="center">
		<a target="_newDicomWindow" href="<!--{$relay}-->/org.freemedsoftware.module.DicomModule.GetDICOM?param0=<!--{$patient}-->&param1=<!--{$id}-->&/image.dcm"><!--{t}-->Open DICOM Image<!--{/t}--></a>
	</div>

</div>

</td><td align="left" valign="top">
<img src="<!--{$relay}-->/org.freemedsoftware.module.DicomModule.GetDICOM?param0=<!--{$patient}-->&param1=<!--{$id}-->&param2=true" border="0" style="max-width: 100%; max-height: 100%;" id="dicomImage" />
</td></tr>
</table>

<!--
<applet archive="<!--{$htdocs}-->/radscaper/radscaper.jar" codebase="./" code="com.divinev.radscaper.Main.class" width="100%" height="100%">
<PARAM NAME="Config" VALUE="config.xml" />
<PARAM NAME="DicomImg1" VALUE="<!--{$relay}-->/org.freemedsoftware.module.DicomModule.GetDICOM?param0=<!--{$patient}-->&param1=<!--{$id}-->" />
</applet>
-->

