<!--

$Id$

Authors:
	Jeff Buchbinder <jeff@freemedsoftware.org>

FreeMED Electronic Medical Record and Practice Management System
Copyright (C) 1999-2010 FreeMED Software Foundation

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

-->

<!--{link table='drugquantityqual' field='description' link=$rxquantityqual var='quanqual'}-->
<!--{method namespace='org.freemedsoftware.module.ProviderModule.fullName' param0=$rxphy var='phyname'}-->
<!--{method namespace='org.freemedsoftware.api.PatientInterface.PatientInformation' param0=$rxpatient var='info'}-->
<!--{link table='physician' link=$rxphy field='phydea' var='dea'}-->
<!--{link table='physician' link=$rxphy field='phynpi' var='npi'}-->
<!--{method namespace='org.freemedsoftware.module.MultumDrugLexicon.DrugDosageToText' param0=$rxunit var='dosage'}-->

<!--{* Figure substitution *}-->
<!--{if $rxsubstitute == 0}-->
	<!--{assign var='substitute' value='Substitution Allowed By Prescriber'}-->
<!--{elseif $rxsubstitute == 1}-->
	<!--{assign var='substitute' value='Substitution Not Allowed By Prescriber'}-->
<!--{elseif $rxsubstitute == 2}-->
	<!--{assign var='substitute' value='Substitution Allowed - Patient Requested Product Dispensed'}-->
<!--{elseif $rxsubstitute == 3}-->
	<!--{assign var='substitute' value='Substitution Allowed - Pharmacist Selected Product Dispensed'}-->
<!--{elseif $rxsubstitute == 4}-->
	<!--{assign var='substitute' value='Substitution Allowed - Generic Drug Not In Stock'}-->
<!--{elseif $rxsubstitute == 5}-->
	<!--{assign var='substitute' value='Substitution Allowed - Brand Drug Dispensed as a Generic'}-->
<!--{elseif $rxsubstitute == 8}-->
	<!--{assign var='substitute' value='Substitution Allowed - Generic Drug Not Available in Marketplace'}-->
<!--{/if}-->

<table cellpadding="5">
<tr>
	<th>Rx Date</th>
	<td><!--{$rxdtfrom|date_format:"%B %e, %Y"}--></td>
</tr>
<tr>
	<th>Patient</th>
	<td><!--{$ptfname}--> <!--{if $ptmname}--><!--{$ptmname}-->.<!--{/if}--> <!--{$ptlname}--></td>
</tr>
<tr>
	<th>Date of Birth</th>
	<td><!--{$ptdob|date_format:"%B %e, %Y"}--></td>
</tr>
<!--{if $ptssn}-->
<tr>
	<th>SSN</th>
	<td><!--{$ptssn}--></td>
</tr>
<!--{/if}-->
<tr>
	<th>Account #</th>
	<td><!--{$ptid}--></td>	
</tr>
<tr>
	<th>Medication</th>
	<td><!--{$rxdrug}--> <!--{$dosage}--></td>
</tr>
<tr>
	<th>Disp</th>
	<td><!--{$rxquantity}--> <!--{if $rxquantityqual}--><!--{$quanqual}--><!--{/if}--></td>
</tr>
<!--{if $rxsig}-->
<tr>
	<th>Sig</th>
	<td><!--{$rxsig}--></td>
</tr>
<!--{/if}-->
<tr>
	<th>Refill</th>
	<td><!--{$_refills}--> refill(s)</td>
</tr>
</table>

