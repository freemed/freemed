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

<!--{method var='preferencesSections' namespace="org.freemedsoftware.module.UserPreferences.GetConfigSections"}-->

<style type="text/css">

	.configPane {
		margin: 1ex;
		}

</style>

<script language="javascript">

	var config = {
		corpus: [],
		vars: [],
		appendItem: function( table, item ) {
			var tR = document.createElement( 'tr' );
			var tD1 = document.createElement( 'td' );
			var tD2 = document.createElement( 'td' );

			tD1.style.align = 'right';
			tD1.style.align = 'left';

			tD1.innerHTML = item.u_title;

			// Add to list of variables we have to deal with
			config.vars.push( item.u_option );

			switch ( item.u_type ) {
				case 'Number':
				case 'Text':
				var widget = document.createElement( 'input' );
				widget.id = item.u_option;
				widget.type = 'text';
				widget.value = item.u_value;
				tD2.appendChild( widget );
				break;

				case 'Select': 
				var widget = document.createElement( 'select' );
				widget.id = item.u_option;
				widget.options.length = 0;
				for ( var t=0; t<item.options.length; t++ ) {
					if ( item.options[t].match('/') ) {
						var o = item.options[t].split('/');
						widget.options[widget.options.length] = new Option( o[1], o[0] );
					} else {
						widget.options[widget.options.length] = new Option( item.options[t], item.options[t] );
					}
				}
				try { widget.value = item.u_value; } catch (err) { }
				tD2.appendChild( widget );
				break;

				case 'YesNo': 
				var widget = document.createElement( 'select' );
				widget.id = item.u_option;
				widget.options.length = 0;
				widget.options[widget.options.length] = new Option( 'No', 0 );
				widget.options[widget.options.length] = new Option( 'Yes', 1 );
				widget.selectedIndex = item.u_value;
				tD2.appendChild( widget );
				break;

				default:
				alert('Unimplemented widget type ' + item.ctype + '!');
				return false;
				break;
			}

			tR.appendChild( tD1 );
			tR.appendChild( tD2 );
			table.appendChild( tR );
		},
		getValue: function ( key ) {
			var item;
			for ( var i=0; i<config.corpus.length; i++) {
				if ( config.corpus[i].u_option == key ) {
					item = config.corpus[i];
				}
			}
			switch ( item.u_type ) {
				case 'Number':
				case 'Select':
				case 'Text':
				case 'YesNo':
				return document.getElementById( item.u_option ).value;
				break;

				default:
				return 'FIXME: NO VALUE';
				break;
			}
		},
		populateTabContainer: function( data ) {
			for (var i=0; i<data.length; i++) {
				var item = data[i];

				// Figure out tab, do we have to make it?
				var tabId;
				var tableId;

				try {
					tabId = item.u_section.replace(' ', '');
					tableId = tabId + '_table';
				} catch ( e ) { }

				if ( ! item.u_section ) {
					tabId = 'mainTab';
					tableId = 'mainTab_table';
				}

				var p = document.getElementById( tableId );
				config.appendItem( p, item );
			}
		},
		init: function () {
			dojo.io.bind({
				method: 'POST',
				content: { },
				url: '<!--{$relay}-->/org.freemedsoftware.module.UserPreferences.GetAll',
				load: function( type, data, evt ) {
					try {
						config.corpus = data;
						config.populateTabContainer( data );
					} catch (err) { }
				},
				mimetype: 'text/json'
			});
		},
		onCommit: function ( ) {
			// Form information, collate
			var hash = { };
			for( var i=0; i<config.vars.length; i++ ) {
				hash[ config.vars[i] ] = config.getValue( config.vars[i] );
			}
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: hash
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.UserPreferences.SetValues',
				load: function( type, data, evt ) {
					if ( data ) {
						freemedMessage( "<!--{t|escape:'javascript'}-->Saved preferences.<!--{/t}-->", 'INFO' );
					} else {
						freemedMessage( "<!--{t|escape:'javascript'}-->Failed to change preferences.<!--{/t}-->", 'ERROR' );
					}
				},
				mimetype: 'text/json'
			});
		}
	};

	_container_.addOnLoad(function(){
		config.init();
		dojo.event.connect( dojo.widget.byId( 'configCommitButton' ), 'onClick', config, 'onCommit' );
	});
	_container_.addOnUnload(function(){
		dojo.event.disconnect( dojo.widget.byId( 'configCommitButton' ), 'onClick', config, 'onCommit' );
	});

</script>

<h3><!--{t}-->User Preferences<!--{/t}--></h3>

<div dojoType="TabContainer" widgetId="preferencesTabContainer" style="height: 85%; width: 100%;">

	<div dojoType="ContentPane" label="System" widgetId="mainTab">
		<table style="configPane" id="mainTab_table"></table>
	</div>

	<!--{foreach from=$preferencesSections item='val'}-->
	<div dojoType="ContentPane" label="<!--{$val|escape}-->" widgetId="<!--{$val|escape|replace:' ':''}-->Tab">
		<table style="configPane" id="<!--{$val|escape|replace:' ':''}-->_table"></table>
	</div>
	<!--{/foreach}-->

</div>

<div align="center">
	<table border="0" style="width: auto;">
		<tr>
			<td>
				<button dojoType="Button" type="button" id="configCommitButton" widgetId="configCommitButton">
					<div><img src="<!--{$htdocs}-->/images/teak/check_go.16x16.png" border="0" width="16" height="16" /> <!--{t}-->Save Changes<!--{/t}--></div>
				</button>
			</td>
		</tr>
	</table>
</div>

