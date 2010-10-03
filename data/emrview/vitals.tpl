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

<table cellpadding="5">

<!--{if $v_temp_status = 'recorded'}-->
<tr>
	<th>Temp</th>
	<td><!--{$v_temp_value}--> <!--{$v_temp_units}--></td>
	<td><!--{$v_temp_qualifier}--></td>
</tr>
<!--{/if}-->

<!--{if $v_pulse_status = 'recorded'}-->
<tr>
	<th>Pulse</th>
	<td><!--{$v_pulse_value}--></td>
	<td><!--{$v_pulse_location}--> <!--{$v_pulse_method}--> <!--{$v_pulse_site}--></td>
</tr>
<!--{/if}-->

<!--{if $v_pulseox_status = 'recorded'}-->
<tr>
	<th>Pulse OX</th>
	<td><!--{$v_pulseox_flowrate}--></td>
	<td><!--{$v_pulseox_o2conc}--></td>
	<td><!--{$v_pulseox_method}--></td>
</tr>
<!--{/if}-->

<!--{if $v_glucose_status = 'recorded'}-->
<tr>
	<th>Glucose</th>
	<td><!--{$v_glucose_value}--> <!--{$v_glucose_units}--></td>
	<td><!--{$v_glucose_qualifier}--></td>
</tr>
<!--{/if}-->

<!--{if $v_resp_status = 'recorded'}-->
<tr>
	<th>Respiration</th>
	<td><!--{$v_resp_value}--></td>
	<td><!--{$v_resp_method}--></td>
	<td><!--{$v_resp_position}--></td>
</tr>
<!--{/if}-->

<!--{if $v_bp_status = 'recorded'}-->
<tr>
	<th>BP</th>
	<td><!--{$v_bp_s_value}--> / <!--{$v_bp_d_value}--></td>
	<td><!--{$v_bp_location}--> <!--{$v_bp_method}--> <!--{$v_bp_position}--></td>
</tr>
<!--{/if}-->

<!--{if $v_cvp_status = 'recorded'}-->
<tr>
	<th>CVP</th>
	<td><!--{$v_cvp_value}--></td>
	<td><!--{$v_cvp_por}--></td>
</tr>
<!--{/if}-->

<!--{if $v_cg_status = 'recorded'}-->
<tr>
	<th>CG</th>
	<td><!--{$v_cg_value}--> <!--{$v_cg_units}--></td>
	<td><!--{$v_cg_location}--> <!--{$v_cg_site}--></td>
</tr>
<!--{/if}-->

<!--{if $v_h_status = 'recorded'}-->
<tr>
	<th>Height</th>
	<td><!--{$v_h_value}--> <!--{$v_h_units}--></td>
	<td><!--{$v_h_quality}--></td>
</tr>
<!--{/if}-->

<!--{if $v_w_status = 'recorded'}-->
<tr>
	<th>Weight</th>
	<td><!--{$v_w_value}--> <!--{$v_w_units}--></td>
	<td><!--{$v_w_quality}--></td>
</tr>
<!--{/if}-->

<!--{if $v_pain_status = 'recorded'}-->
<tr>
	<th>Pain</th>
	<td><!--{$v_pain_value}--></td>
	<td><!--{$v_pain_scale}--></td>
</tr>
<!--{/if}-->

</table>

