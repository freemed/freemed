<!--{* Smarty *}-->
<!--{*
 // $Id: org.freemedsoftware.module.icdcodes.form.tpl 3917 2007-10-05 00:46:42Z jeff $
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

<!--{assign var='module' value='icdcodes'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->ICD Codes<!--{/t}-->
<!--{/assign_block}-->

<!--{assign_block var='validation'}-->
	if ( content.icd9code.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a Code (ICD9).<!--{/t}-->\n";
		r = false;
	}
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->
		<table border="0">
			<tr>
				<td align="right"><!--{t}-->Meta Description<!--{/t}--></td>
				<td align="left"><input type="text" id="icdmetadesc" name="icdmetadesc" size="10" maxlength="30" /></td>
			</tr>
			
			<tr>
				<td align="right"><!--{t}-->Code (ICD9)<!--{/t}--></td>
				<td align="left"><input type="text" id="icd9code" name="icd9code" size="10" maxlength="6" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Code (ICD10)<!--{/t}--></td>
				<td align="left"><input type="text" id="icd10code" name="icd10code" size="10" maxlength="7" /></td>
			</tr>

			<tr>
				<td align="right"><!--{t}-->Description (ICD9)<!--{/t}--></td>
				<td align="left"><input type="text" id="icd9descrip" name="icd9descrip" size="20" maxlength="45" /></td>
			</tr>
			
			<tr>
				<td align="right"><!--{t}-->Description (ICD10)<!--{/t}--></td>
				<td align="left"><input type="text" id="icd10descrip" name="icd10descrip" size="20" maxlength="45" /></td>
			</tr>
		</table>
<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.supportmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation}-->