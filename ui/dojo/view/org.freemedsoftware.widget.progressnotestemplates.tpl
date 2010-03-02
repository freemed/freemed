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

	File:	org.freemedsoftware.widget.progressnotestemplates

	Reusable ProgressNotesTemplates widget.

	Parameters:

		$varname - Variable name

*}-->
<script language="javascript">

	var <!--{$varname}-->_namespace = {
		add: function() {
			// Ask the user for a name
			var l = prompt( "<!--{t|escape:'javascript'}-->Please enter a descriptive name for this template.<!--{/t}-->" );
			if ( l.length == 0 ) { return false; }

			dojo.io.bind({
				method: 'POST',
				content: {
					param0: {
						pntname: l,
<!--{if $SESSION.authdata.user_record.userrealphy}--> 
						pntphy: <!--{$SESSION.authdata.user_record.userrealphy}-->,
<!--{/if}-->
						pnt_S: dojo.byId( 'note_S_value' ).innerHTML,
						pnt_O: dojo.byId( 'note_O_value' ).innerHTML,
						pnt_A: dojo.byId( 'note_A_value' ).innerHTML,
						pnt_P: dojo.byId( 'note_P_value' ).innerHTML,
						pnt_I: dojo.byId( 'note_I_value' ).innerHTML,
						pnt_E: dojo.byId( 'note_E_value' ).innerHTML,
						pnt_R: dojo.byId( 'note_R_value' ).innerHTML
					}
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.ProgressNotesTemplates.add',
				load: function ( type, data, evt ) {
					freemedMessage( "<!--{t|escape:'javascript'}-->Added template.<!--{/t}-->", "INFO" );
				},
				mimetype: 'text/json'
			});
		
		},
		mod: function() {
			// Check for an id
			var v;
			try {
				v = document.getElementById('<!--{$varname}-->').value;
			} catch (err) {
				alert("<!--{t|escape:'javascript'}-->No template was chosen!<!--{/t}-->");
				return false;
			}
			if ( parseInt( v ) < 1 ) {
				alert("<!--{t|escape:'javascript'}-->No template was chosen!<!--{/t}-->");
				return false;
			}

			dojo.io.bind({
				method: 'POST',
				content: {
					param0: {
						id: v,
						pnt_S: dojo.byId( 'note_S_value' ).innerHTML,
						pnt_O: dojo.byId( 'note_O_value' ).innerHTML,
						pnt_A: dojo.byId( 'note_A_value' ).innerHTML,
						pnt_P: dojo.byId( 'note_P_value' ).innerHTML,
						pnt_I: dojo.byId( 'note_I_value' ).innerHTML,
						pnt_E: dojo.byId( 'note_E_value' ).innerHTML,
						pnt_R: dojo.byId( 'note_R_value' ).innerHTML
					}
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.ProgressNotesTemplates.mod',
				load: function ( type, data, evt ) {
					freemedMessage( "<!--{t|escape:'javascript'}-->Updated template.<!--{/t}-->", "INFO" );
				},
				mimetype: 'text/json'
			});
		
		},
		use: function() {
			var v;
			try {
				v = document.getElementById('<!--{$varname}-->').value;
			} catch (err) {
				alert("<!--{t|escape:'javascript'}-->No template was chosen!<!--{/t}-->");
				return false;
			}
			if ( parseInt( v ) < 1 ) {
				alert("<!--{t|escape:'javascript'}-->No template was chosen!<!--{/t}-->");
				return false;
			}
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: v
				},
				url: "<!--{$relay}-->/org.freemedsoftware.module.ProgressNotesTemplates.GetTemplate",
				load: function ( type, data, evt ) {
					if (data) {
						dojo.byId( 'note_S_value' ).innerHTML = data.pnt_S;
						dojo.byId( 'note_O_value' ).innerHTML = data.pnt_O;
						dojo.byId( 'note_A_value' ).innerHTML = data.pnt_A;
						dojo.byId( 'note_P_value' ).innerHTML = data.pnt_P;
						dojo.byId( 'note_I_value' ).innerHTML = data.pnt_I;
						dojo.byId( 'note_E_value' ).innerHTML = data.pnt_E;
						dojo.byId( 'note_R_value' ).innerHTML = data.pnt_R;
						freemedMessage( "<!--{t|escape:'javascript'}-->Loaded template.<!--{/t}-->", "INFO" );
					}
				},
				mimetype: 'text/json'
			});
		}
	};
	_container_.addOnLoad(function(){
		dojo.event.connect( dojo.widget.byId('inject_<!--{$varname}-->'), 'onClick', <!--{$varname}-->_namespace, 'use' );
		dojo.event.connect( dojo.widget.byId('add_<!--{$varname}-->'), 'onClick', <!--{$varname}-->_namespace, 'add' );
		dojo.event.connect( dojo.widget.byId('mod_<!--{$varname}-->'), 'onClick', <!--{$varname}-->_namespace, 'mod' );
	});
	_container_.addOnUnload(function(){
		dojo.event.disconnect( dojo.widget.byId('inject_<!--{$varname}-->'), 'onClick', <!--{$varname}-->_namespace, 'use' );
		dojo.event.disconnect( dojo.widget.byId('add_<!--{$varname}-->'), 'onClick', <!--{$varname}-->_namespace, 'add' );
		dojo.event.disconnect( dojo.widget.byId('mod_<!--{$varname}-->'), 'onClick', <!--{$varname}-->_namespace, 'mod' );
	});
</script>
<table border="0" cellspacing="0" style="width: auto;">
	<tr>
		<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" varname=$varname module="ProgressNotesTemplates"}--></td>
		<td>
			<button dojoType="Button" id="inject_<!--{$varname}-->" widgetId="inject_<!--{$varname}-->">
				<!--{t}-->Use<!--{/t}-->
			</button>
		</td>
		<td>
			<button dojoType="Button" id="add_<!--{$varname}-->" widgetId="add_<!--{$varname}-->">
				<!--{t}-->Add<!--{/t}-->
			</button>
		</td>
		<td>
			<button dojoType="Button" id="add_<!--{$varname}-->" widgetId="mod_<!--{$varname}-->">
				<!--{t}-->Update<!--{/t}-->
			</button>

		</td>
	</tr>
</table>

