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

<!--{assign var='module' value='providercertifications'}-->

<!--{assign_block var='moduleName'}-->
	<!--{t}-->Provider Certifications<!--{/t}-->
<!--{/assign_block}-->

<!--{assign_block var='validation'}-->
	if ( content.degdegree.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a degree.<!--{/t}-->\n";
		r = false;
	}
	if ( content.degname.length < 2 ) {
		m += "<!--{t|escape:'javascript'}-->You must enter a name for this degree.<!--{/t}-->\n";
		r = false;
	}
<!--{/assign_block}-->

<!--{assign_block var='moduleForm'}-->
<table border="0" style="width: auto;">

	<tr>
		<td align="right"><!--{t}-->Degree Abbreviation<!--{/t}--></td>
		<td><input type="text" id="degdegree" name="degdegree" size="10" maxlength="10" /></td>
	</tr>

	<tr>
		<td align="right"><!--{t}-->Degree Name<!--{/t}--></td>
		<td><input type="text" id="degname" name="degname" size="50" maxlength="50" /></td>
	</tr>

</table>
<!--{/assign_block}-->

<!--{include file="org.freemedsoftware.module.supportmodule.form.tpl" module=$module moduleName=$moduleName moduleForm=$moduleForm collectDataArray=$collectDataArray initialLoad=$initialLoad validation=$validation}-->

