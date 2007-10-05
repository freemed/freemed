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
<!--{*

	File:	org.freemedsoftware.widget.letterstemplates

	Reusable Letters Templates widget.

	Parameters:

		$varname - Variable name

		$inject - Name of Dojo widget to inject content into

*}-->
<script language="javascript">

	var <!--{$varname}-->_namespace = {
		usingTemplate: 0,
		add: function() {
			//dojo.widget.byId('lettersTemplateAddDialog_<!--{$varname}-->').show();
			// Use the injection target as the source
			var s = dojo.widget.byId("<!--{$inject}-->").getValue();

			// Ask the user for a name
			var l = prompt( "<!--{t|escape:'javascript'}-->Please enter a descriptive name for this template.<!--{/t}-->" );
			if ( l.length == 0 ) { return false; }

			dojo.io.bind({
				method: 'POST',
				content: {
					param0: {
						ltname: l,
						lttext: s
					}
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.LettersTemplates.add',
				load: function ( type, data, evt ) {
					freemedMessage( "<!--{t|escape:'javascript'}-->Added template.<!--{/t}-->", "INFO" );
				},
				mimetype: 'text/json'
			});
		
		},
		mod: function() {
			// Use the injection target as the source
			var s = dojo.widget.byId("<!--{$inject}-->").getValue();
			var l = dojo.widget.byId("<!--{$varname}-->_widget").getLabel();
                        if ( ! <!--{$varname}-->_namespace.usingTemplate ) {
				alert( "<!--{t|escape:'javascript'}-->You must load a template before updating it!<!--{/t}-->" );
				return false;
			}

			dojo.io.bind({
				method: 'POST',
				content: {
					param0: {
						id: <!--{$varname}-->_namespace.usingTemplate,
						ltname: l,
						lttext: s
					}
				},
				url: '<!--{$relay}-->/org.freemedsoftware.module.LettersTemplates.mod',
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
			<!--{$varname}-->_namespace.usingTemplate = parseInt( v );
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: v
				},
				url: "<!--{$relay}-->/org.freemedsoftware.module.LettersTemplates.GetTemplate",
				load: function ( type, data, evt ) {
					dojo.widget.byId("<!--{$inject}-->").setValue( data );
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
		<td><!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" varname=$varname module="LettersTemplates"}--></td>
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
			<button dojoType="Button" id="mod_<!--{$varname}-->" widgetId="mod_<!--{$varname}-->">
				<!--{t}-->Update<!--{/t}-->
			</button>
		</td>
	</tr>
</table>

