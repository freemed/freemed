<!--{* Smarty *}-->
<!--{*
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2009 FreeMED Software Foundation
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
	dojo.require("dojo.widget.UsTextbox");
</script>

<!--{assign var='module' value='practices'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Practice<!--{/t}-->
<!--{/assign_block}-->

<!--{assign_block var='validation'}-->
	if ( content.pracname.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a valid name.<!--{/t}-->\n";
		r = false;
	}
<!--{/assign_block}-->

<!--{assign_block var='initialLoad'}-->
<!--{/assign_block}-->

<!--{assign_block var='collectDataArray'}-->
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->
<div dojoType="TabContainer" id="mainTabContainer" style="width: 100%; height: 30em; overflow-y: scroll;">

	<div dojoType="ContentPane" id="practiceMainPane" label="<!--{t|escape:'javascript'}-->Primary Information<!--{/t}-->">

		<table style="border: 0; padding: 1em;">

			<tr>
				<td><!--{t}-->Practice Name<!--{/t}--></td>
				<td><input type="text" id="pracname" name="pracname" size="50" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Address<!--{/t}--></td>
				<td><input type="text" id="addr1a" name="addr1a" size="50" /><br />
				<input type="text" id="addr2a" name="addr2a" size="50" /></td>
			</tr>

			<tr>
				<td><!--{t}-->City, State<!--{/t}--></td>
				<td><input type="text" id="citya" name="citya" size="25" /><b>, </b>
				<td><input type="text" id="statea" name="statea" size="25" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Postal Code<!--{/t}-->, <!--{t}-->Country<!--{/t}--></td>
				<td><input type="text" id="zipa" name="zipa" size="10" /><br />
				<td><input type="text" id="countrya" name="countrya" size="50" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Phone #<!--{/t}--></td>
				<td><input type="text" id="phonea" name="phonea" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Facsimile #<!--{/t}--></td>
				<td><input type="text" id="faxa" name="faxa" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Employer Identification Number<!--{/t}--></td>
				<td><input type="text" id="ein" name="ein" size="50" /></td>
			</tr>

		</table>

	</div>

	<div dojoType="ContentPane" id="practiceContactPane" label="<!--{t|escape:'javascript'}-->Contact<!--{/t}-->">

		<table style="border: 0; padding: 1em; width: auto;">

			<tr>
				<td><!--{t}-->Email<!--{/t}--></td>
				<td><input dojoType="EmailTextbox" type="text" id="email" name="email" size="45" /></td>
			</tr>


			<tr>
				<td><!--{t}-->Cellular Phone #<!--{/t}--></td>
				<td><input type="text" id="cellular" name="cellular" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Beeper / Pager #<!--{/t}--></td>
				<td><input type="text" id="pager" name="pager" /></td>
			</tr>

		</table>

	</div>

	<div dojoType="ContentPane" id="practiceSecondaryPane" label="<!--{t|escape:'javascript'}-->Secondary Location<!--{/t}-->">

		<table style="border: 0; padding: 1em; width: auto;">

			<tr>
				<td><!--{t}-->Address<!--{/t}--></td>
				<td><input type="text" id="addr1b" name="addr1b" size="50" /><br />
				<input type="text" id="addr2b" name="addr2b" size="50" /></td>
			</tr>

			<tr>
				<td><!--{t}-->City, State<!--{/t}--></td>
				<td><input type="text" id="cityb" name="cityb" size="25" /><b>, </b>
				<td><input type="text" id="stateb" name="stateb" size="25" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Postal Code<!--{/t}-->, <!--{t}-->Country<!--{/t}--></td>
				<td><input type="text" id="zipb" name="zipb" size="10" /><br />
				<td><input type="text" id="countryb" name="countryb" size="50" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Phone #<!--{/t}--></td>
				<td><input type="text" id="phoneb" name="phoneb" /></td>
			</tr>

			<tr>
				<td><!--{t}-->Facsimile #<!--{/t}--></td>
				<td><input type="text" id="faxb" name="faxb" /></td>
			</tr>

		</table>

	</div>

</div>
</form>
<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.supportmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation}-->

