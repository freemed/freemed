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

<style type="text/css">
	.messageButton {
		padding: 5px;
		margin: 5px;
		}

	#messagesBar {
		border: 1px solid #0000ff;
		background: #aaaaff;
		-moz-border-radius: 10px;
		}
</style>

<script type="text/javascript">
	dojo.require("dojo.event.*");
	dojo.require("dojo.widget.RichText");

	// Special scope variable because of ContentPane
	// see http://manual.dojotoolkit.org/WikiHome/DojoDotBook/Book30
	var o = {
		createMessageCallback: function ( ) {
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: {
						user: dojo.widget.byId('msgfor').getValue(),
						subject: document.getElementById('msgsubject').value,
						patient: document.getElementById('msgpatient').value,
						person: document.getElementById('msgperson').value,
						text: dojo.widget.byId('msgtext').getValue()
					}
				},
				url: '<!--{$relay}-->/org.freemedsoftware.api.Messages.send',
				error: function( type, data, evt ) {
					//alert("<!--{t}-->Unable to complete.<!--{/t}-->");
					dojo.widget.byId('newMessageDialog').hide();
					this.loadMessages();
				},
				load: function( type, data, evt ) {
					freemedMessage( "<!--{t}-->Message sent successfully.<!--{/t}-->", 'INFO' );
					freemedLoad( 'org.freemedsoftware.ui.messaging' );
				},
				mimetype: "text/json"
			});
		}
	};

	// Handle in context loading for these widgets
	_container_.addOnLoad(function(){
		dojo.event.connect(dojo.widget.byId('createMessageButton'), "onClick", o, "createMessageCallback");
	});
	_container_.addOnUnload(function(){
		dojo.event.disconnect(dojo.widget.byId('createMessageButton'), "onClick", o, "createMessageCallback");
	});

</script>

<h3><!--{t}-->Messaging<!--{/t}-->: <!--{t}-->Compose Message<!--{/t}--></h3>

	<table border="0">
		<tr>
			<td valign="top" align="right"><!--{t}-->Recipient<!--{/t}--> : </td>
			<td>
				<select dojoType="Select"
				 id="msgfor" widgetId="msgfor"
				 style="width: 150px;"
				 mode="remote" autocomplete="false"
				 dataUrl="<!--{$relay}-->/org.freemedsoftware.api.UserInterface.GetUsers?param0=%{searchString}"
				 /></select>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right"><!--{t}-->Patient<!--{/t}--> : </td>
			<td>
				<!--{include file="org.freemedsoftware.widget.patientpicklist.tpl" varname="msgpatient"}-->
			</td>
		</tr>
		<tr>
			<td valign="top" align="right"><!--{t}-->Person (non-patient)<!--{/t}--> : </td>
			<td>
				<input type="text" id="msgperson" name="msgperson" size="50" />
			</td>
		</tr>
		<tr>
			<td valign="top" align="right"><!--{t}-->Subject<!--{/t}--> : </td>
			<td><input type="text" id="msgsubject" name="msgsubject" size="50" value="" /></td>
		</tr>
		<tr>
			<td valign="top" align="right"><!--{t}-->Message<!--{/t}--> : </td>
			<td><div dojoType="RichText" id="mgtext" widgetId="msgtext" style="border: 1px solid black; background-color: #ffffff; width: 30em;" height="15em" inheritWidth="true"></div></td>
		</tr>
	</table>

	<div align="center">
		<table border="0"><tr>
			<td align="right"><button dojoType="Button" id="createMessageButton" widgetId="createMessageButton"><!--{t}-->Create<!--{/t}--></button></td>
			<td align="left"><button dojoType="Button" onClick="if(confirm('<!--{t}-->Are you sure you want to cancel this message?<!--{/t}-->')) { freemedLoad('org.freemedsoftware.ui.messaging'); }"><!--{t}-->Cancel<!--{/t}--></button></td>
		</tr></table>
	</div>

