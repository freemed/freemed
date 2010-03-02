<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
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

<script type="text/javascript">
	dojo.require("dojo.widget.Form");
	dojo.require("dojo.widget.TabContainer");
	dojo.require("dojo.widget.ContentPane");
	dojo.require("dojo.widget.InternetTextbox");
</script>

<!--{assign var='module' value='pharmacy'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Pharmacy<!--{/t}-->
<!--{/assign_block}-->

<!--{assign_block var='validation'}-->
	if ( content.phname.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a valid name.<!--{/t}-->\n";
		r = false;
	}
<!--{/assign_block}-->

<!--{assign_block var='initialLoad'}-->
	// Assign CSZ widget
	dojo.widget.byId('phcsz_widget').setLabel( data.phcity + ', ' + data.phstpr + ' ' + data.phzip );
	document.getElementById('phcsz').value = data.phcity + ', ' + data.phstpr + ' ' + data.phzip;
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->
<table style="border: 0; padding: 1em;">

	<tr>
		<td><!--{t}-->Pharmacy Name<!--{/t}--></td>
		<td><input type="text" id="phname" name="phname" size="50" /></td>
	</tr>

	<tr>
		<td><!--{t}-->Address Line 1<!--{/t}--></td>
		<td><input type="text" id="phaddr1" name="phaddr1" size="75" maxlength="150" /></td>
	</tr>

	<tr>
		<td><!--{t}-->Address Line 2<!--{/t}--></td>
		<td><input type="text" id="phaddr2" name="phaddr2" size="75" maxlength="150" /></td>
	</tr>

	<tr>
		<td><!--{t}-->City, State Zip<!--{/t}--></td>
		<td>
			<input dojoType="Select"
			autocomplete="false"
			id="phcsz_widget"
			style="width: 300px;"
			dataUrl="<!--{$relay}-->/org.freemedsoftware.module.Zipcodes.CityStateZipPicklist?param0='%{searchString}'"
			setValue="document.getElementById('phcsz').value = arguments[0];"
			mode="remote" />
			<input type="hidden" id="phcsz" name="phcsz" value="" />
		</td>
	</tr>

	<tr>
		<td><!--{t}-->Transmission Method<!--{/t}--></td>
		<td><select id="phmethod" name="phmethod">
			<option value="fax"><!--{t}-->Fax<!--{/t}--></option>
			<option value="email"><!--{t}-->Email<!--{/t}--></option>
			<option value="ncpdp"><!--{t}-->NCPDP SCRIPT<!--{/t}--></option>
		</select></td>
	</tr>

	<tr>
		<td><!--{t}-->Facsimile Number (as dialed)<!--{/t}--></td>
		<td><input type="text" id="phfax" name="phfax" size="20" /></td>
	</tr>

	<tr>
		<td><!--{t}-->Email<!--{/t}--></td>
		<td><input dojoType="EmailTextbox" type="text" id="phemail" name="phemail" size="45" /></td>
	</tr>

	<tr>
		<td><!--{t}-->NCPDP ID Number<!--{/t}--></td>
		<td><input type="text" id="phncpdp" name="phncpdp" size="20" /></td>
	</tr>

</table>
<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.supportmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation}-->

