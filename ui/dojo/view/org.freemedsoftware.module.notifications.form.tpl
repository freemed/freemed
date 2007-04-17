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

<script type="text/javascript">
	dojo.require( 'dojo.event.*' );

	var notifications = {
		handleResponse: function ( data ) {
			if (data) {
				dojo.widget.byId('emrSimpleDialog').hide();
				freemedMessage( "<!--{t}-->Added notifications.<!--{/t}-->", "INFO" );
			} else {
				dojo.widget.byId('ModuleFormCommitChangesButton').enable();
			}
		},
		validate: function ( content ) {
			var r = true;
			var m = "";
/*
			if ( content.noffset == undefined ) {
				r = false;
				m += "<!--{t}-->No target date selected.<!--{/t}-->\n";
			}
			if ( content.nfor == undefined or content.nfor < 1 ) {
				r = false;
				m += "<!--{t}-->No target user selected.<!--{/t}-->\n";
			}
			if ( content.ndescrip == undefined or content.ndescrip.length < 4 ) {
				r = false;
				m += "<!--{t}-->No message text given.<!--{/t}-->\n";
			}
*/
			if ( m.length > 1 ) { alert( m ); }
			return r;
		},
		submit: function ( ) {
			try {
				dojo.widget.byId('ModuleFormCommitChangesButton').disable();
			} catch ( err ) { }
			var myContent = {
				noffset: dojo.widget.byId('notifications.targetDate').getValue(),
				nfor: dojo.widget.byId('notifications.targetUser').getValue(),
				ndescrip: document.getElementById('notifications.targetDescription').value,
				npatient: '<!--{$patient|escape}-->'
			};
			if (notifications.validate( myContent )) {
				dojo.io.bind({
					method: "POST",
					content: {
						param0: myContent
					},
					url: "<!--{$relay}-->/org.freemedsoftware.module.Notifications.add",
					load: function ( type, data, evt ) {
						notifications.handleResponse( data );
					},
					mimetype: "text/json"
				});
			}
		}
	};

	_container_.addOnLoad(function() {
		dojo.event.connect( dojo.widget.byId('ModuleFormCommitChangesButton'), 'onClick', notifications, 'submit' );
	});
	_container_.addOnUnLoad(function() {
		dojo.event.disconnect( dojo.widget.byId('ModuleFormCommitChangesButton'), 'onClick', notifications, 'submit' );
	});

</script>

<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Target Date<!--{/t}--></td>
		<select dojoType="ComboBox" id="notifications.targetDate" widgetId="notifications.targetDate">
			<option value="1">1 <!--{t}-->Day<!--{/t}--></option>
			<option value="2">2 <!--{t}-->Days<!--{/t}--></option>
			<option value="3">3 <!--{t}-->Days<!--{/t}--></option>
			<option value="4">4 <!--{t}-->Days<!--{/t}--></option>
			<option value="5">5 <!--{t}-->Days<!--{/t}--></option>
			<option value="6">6 <!--{t}-->Days<!--{/t}--></option>
			<option value="7">1 <!--{t}-->Week<!--{/t}--></option>
			<option value="14">2 <!--{t}-->Weeks<!--{/t}--></option>
			<option value="30">1 <!--{t}-->Month<!--{/t}--></option>
			<option value="60">2 <!--{t}-->Months<!--{/t}--></option>
			<option value="90">3 <!--{t}-->Months<!--{/t}--></option>
			<option value="120">4 <!--{t}-->Months<!--{/t}--></option>
			<option value="150">5 <!--{t}-->Months<!--{/t}--></option>
			<option value="180">6 <!--{t}-->Months<!--{/t}--></option>
			<option value="210">7 <!--{t}-->Months<!--{/t}--></option>
			<option value="240">8 <!--{t}-->Months<!--{/t}--></option>
			<option value="270">9 <!--{t}-->Months<!--{/t}--></option>
			<option value="300">10 <!--{t}-->Months<!--{/t}--></option>
			<option value="330">11 <!--{t}-->Months<!--{/t}--></option>
			<option value="365">1 <!--{t}-->Year<!--{/t}--></option>
			<option value="730">2 <!--{t}-->Years<!--{/t}--></option>
			<option value="1095">3 <!--{t}-->Years<!--{/t}--></option>
			<option value="1460">4 <!--{t}-->Years<!--{/t}--></option>
			<option value="1825">5 <!--{t}-->Years<!--{/t}--></option>
			<option value="2190">6 <!--{t}-->Years<!--{/t}--></option>
			<option value="2555">7 <!--{t}-->Years<!--{/t}--></option>
			<option value="2920">8 <!--{t}-->Years<!--{/t}--></option>
			<option value="3285">9 <!--{t}-->Years<!--{/t}--></option>
			<option value="3650">10 <!--{t}-->Years<!--{/t}--></option>
		</select>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Target User<!--{/t}--></td>
		<td>
			<select dojoType="ComboBox"
			 id="notifications.targetUser" widgetId="notifications.targetUser"
			 style="width: 150px;"
			 mode="remote" autocomplete="false"
			 dataUrl="<!--{$relay}-->/org.freemedsoftware.api.UserInterface.GetUsers?param0=%{searchString}"
			 /></select>
		</td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Description<!--{/t}--></td>
		<td><textarea id="notifications.targetDescription" rows="4" cols="40" wrap="virtual"></textarea></td>
	</tr>

</table>

<div align="center">
        <table border="0" style="width:200px;">
        <tr><td align="center">
	        <button dojoType="Button" id="ModuleFormCommitChangesButton" widgetId="ModuleFormCommitChangesButton">
	                <div><!--{t}-->Commit Changes<!--{/t}--></div>
	        </button>
        </td><td align="left">
        	<button dojoType="Button" id="ModuleFormCancelButton" widgetId="ModuleFormCancelButton" onClick="dojo.widget.byId('emrSimpleDialog').hide();">
        	        <div><!--{t}-->Cancel<!--{/t}--></div>
        	</button>
        </td></tr></table>
</div>

