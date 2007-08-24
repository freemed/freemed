<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
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
<!--{*

	File:	org.freemedsoftware.widget.supportpicklist

	Reusable SupportModule widget.

	Parameters:

		$varname - Variable name

		$module - Module class name

		$methodName - (optional) Name of method. Defaults to 'picklist'

		$defaultValue - (optional) Default value. If 0, is zero.

*}-->

<script language="javascript">

	var <!--{$varname|replace:'.':''}--> = {
		onAssign: function ( id ) {
			this.assign( id, false );
		},
		assign: function ( id, dontset ) {
			// Don't assign if we have no value
			try {
				if ( parseInt( id ) < 1 ) {
					dojo.widget.byId('<!--{$varname|escape}-->_widget').setValue( 0 );
					dojo.widget.byId('<!--{$varname|escape}-->_widget').setLabel( '' );
					document.getElementById( '<!--{$varname|escape}-->' ).value = 0;
					return true;
				}
			} catch ( err ) { }

			// Do reverse lookup from assignment
			dojo.io.bind({
				method: "POST",
				content: {
					param0: id
				},
				url: "<!--{$relay}-->/org.freemedsoftware.module.<!--{$module|escape}-->.to_text",
				load: function ( type, data, evt ) {
					if ( !dontset ) {
						dojo.widget.byId('<!--{$varname|escape}-->_widget').setValue( id );
					} else {
						document.getElementById( '<!--{$varname|escape}-->' ).value = id;
					}
					dojo.widget.byId('<!--{$varname|escape}-->_widget').setLabel( data );
				},
				mimetype: "text/json"
			});
		}
	};

	_container_.addOnLoad(function(){
		<!--{if $defaultValue gt 0}--><!--{$varname|replace:'.':''}-->.assign( <!--{$defaultValue}-->, true );<!--{/if}-->
		dojo.event.topic.subscribe( "<!--{$varname|escape}-->-assign", <!--{$varname|replace:'.':''}-->, "onAssign" );
	});
	_container_.addOnUnload(function(){
		dojo.event.topic.unsubscribe( "<!--{$varname|escape}-->-assign", <!--{$varname|replace:'.':''}-->, "onAssign" );
	});

</script>

<input dojoType="Select" value=""
	autocomplete="false"
	id="<!--{$varname|escape}-->_widget" widgetId="<!--{$varname|escape}-->_widget"
	setValue="if (arguments[0]) { document.getElementById('<!--{$varname|escape}-->').value = arguments[0]; dojo.event.topic.publish( '<!--{$varname|escape}-->-setValue', arguments[0] ); }"
	style="width: 300px;"
	dataUrl="<!--{$relay}-->/org.freemedsoftware.module.<!--{$module|escape}-->.<!--{if not $methodName}-->picklist<!--{else}--><!--{$methodName}--><!--{/if}-->?param0=%{searchString}"
	mode="remote" />
<input type="hidden" id="<!--{$varname|escape}-->" name="<!--{$varname|escape}-->" value="0" />

