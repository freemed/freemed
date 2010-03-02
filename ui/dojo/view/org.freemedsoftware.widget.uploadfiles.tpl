<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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
<!--{*

	File:	org.freemedsoftware.widget.uploadfiles.tpl

	Reusable upload widget

	Parameters:

		$varname - Variable name to use for uploaded file. Defaults
		to 'file'.

		$relayPoint - Data relay point for upload.

		$completedCode - Code to execute when upload has completed.
*}-->

<!--{if not $varname}-->
<!--{assign var='varname' value='file'}-->
<!--{/if}-->

<script type="text/javascript">

	var <!--{$varname}-->UploadWidget = {
		onChange: function ( ) {
			<!--{$varname}-->UploadWidget.setStatus( true );
			document.getElementById( '<!--{$varname}-->Form' ).submit();
			<!--{$varname}-->UploadWidget.setStatus( false );
			freemedMessage( "<!--{t|escape:'javascript'}-->Uploaded file<!--{/t}-->", 'INFO' );
			<!--{if $completedCode}--><!--{$completedCode}--><!--{/if}-->
		},
		setStatus: function ( s ) {
			var sW = document.getElementById( '<!--{$varname}-->Status' );
			if ( s ) {
				sW.innerHTML = '<img src="<!--{$htdocs}-->/images/loading.gif" border="0" />';
			} else {
				sW.innerHTML = '';
			}
		}
	};

	_container_.addOnLoad(function(){
		document.getElementById( '<!--{$varname}-->' ).onchange = <!--{$varname}-->UploadWidget.onChange;
	});

</script>

<form action="<!--{$relayPoint}-->" method="post" enctype="multipart/form-data" target="<!--{$varname}-->IFrame" id="<!--{$varname}-->Form">
	<input type="file" name="<!--{$varname}-->" id="<!--{$varname}-->" />
	<span id="<!--{$varname}-->Status"></span>
</form>

<!--{* iFrame Container *}-->

<iframe name="<!--{$varname}-->IFrame" style="width: 600px; height: 400px;<!--{if not $DEBUG}--> display: none;<!--{/if}-->"></iframe>

