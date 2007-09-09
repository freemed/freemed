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
			var x = {
				//user: parseInt( dojo.widget.byId('msgfor').getValue() ),
				for: msgfor.getValue(),
				subject: document.getElementById('msgsubject').value,
				patient: parseInt( document.getElementById('msgpatient').value ),
				person: document.getElementById('msgperson').value,
				text: dojo.widget.byId('msgtext').getValue()
			};
			if ( x.user < 1 ) {
				alert( "<!--{t}-->You must select a valid recipient.<!--{/t}-->" );
				return false;
			}
			if ( x.patient < 1 && x.person.length < 1 ) {
				alert( "<!--{t}-->You must enter a patient or person value to create a message.<!--{/t}-->" );
				return false;
			}
			if ( parseInt( document.getElementById( 'msggroup' ) > 0 ) {
				x.group = parseInt( document.getElementById( 'msggroup' ) );
			}
			dojo.io.bind({
				method: 'POST',
				content: {
					param0: x
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
			<td><!--{include file="org.freemedsoftware.widget.multisupportpicklist.tpl" overrideNamespace="org.freemedsoftware.api.UserInterface" method="GetUsers" varname="msgfor"}--></td>
		</tr>
		<tr>
			<td valign="top" align="right"><!--{t}-->Group<!--{/t}--> : </td>
			<td>
				<!--{include file="org.freemedsoftware.widget.supportpicklist.tpl" module='UserGroups' varname="msggroup"}-->
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
			<td align="right"><button dojoType="Button" id="createMessageButton" widgetId="createMessageButton">
		                <div><img src="<!--{$htdocs}-->/images/teak/check_go.16x16.png" border="0" width="16" height="16" /> <!--{t}-->Create<!--{/t}--></div>
			</button></td>
			<td align="left"><button dojoType="Button" onClick="if(confirm('<!--{t}-->Are you sure you want to cancel this message?<!--{/t}-->')) { freemedLoad('org.freemedsoftware.ui.messaging'); }">
        	        	<div><img src="<!--{$htdocs}-->/images/teak/x_stop.16x16.png" border="0" width="16" height="16" /> <!--{t}-->Cancel<!--{/t}--></div>
		</button></td>
		</tr></table>
	</div>

