<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2008 FreeMED Software Foundation
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

	File:	org.freemedsoftware.widget.patientpicklist

	Reusable patient widget.

	Parameters:

		$varname - Variable name

*}-->

<script language="javascript">

	var <!--{$varname|replace:'.':''}--> = {
		onAssign: function ( id ) {
			// Don't assign if we have no value
			try {
				if ( parseInt( id ) < 1 ) {
					dojo.widget.byId('<!--{$varname|escape}-->_widget').setValue( 0 );
					dojo.widget.byId('<!--{$varname|escape}-->_widget').setLabel( '' );
					return false;
				}
			} catch ( err ) { }

			// Do reverse lookup from assignment
			dojo.io.bind({
				method: "POST",
				content: {
					param0: id
				},
				url: "<!--{$relay}-->/org.freemedsoftware.api.PatientInterface.ToText",
				load: function ( type, data, evt ) {
					dojo.widget.byId('<!--{$varname|escape}-->_widget').setValue( id );
					dojo.widget.byId('<!--{$varname|escape}-->_widget').setLabel( data );
					document.getElementById( '<!--{$varname|escape}-->' ).value = id;
				},
				mimetype: "text/json"
			});
		}
	};

	_container_.addOnLoad(function(){
		dojo.event.topic.subscribe( "<!--{$varname|escape}-->-assign", <!--{$varname|replace:'.':''}-->, "onAssign" );
	});
	_container_.addOnUnload(function(){
		dojo.event.topic.unsubscribe( "<!--{$varname|escape}-->-assign", <!--{$varname|replace:'.':''}-->, "onAssign" );
	});

</script>

<input dojoType="Select" value=""
	autocomplete="false"
	id="<!--{$varname|escape}-->_widget" widgetId="<!--{$varname|escape}-->_widget"
	setValue="if (arguments[0]) { document.getElementById('<!--{$varname|escape}-->').value = arguments[0]; }"
	style="width: 300px;"
	dataUrl="<!--{$base_uri}-->/relay.php/json/org.freemedsoftware.api.PatientInterface.picklist?param0=%{searchString}"
	mode="remote" />
<input type="hidden" id="<!--{$varname|escape}-->" name="<!--{$varname|escape}-->" value="0" />

