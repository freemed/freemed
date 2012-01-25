<!--

$Id$

Authors:
	Jeff Buchbinder <jeff@freemedsoftware.org>

FreeMED Electronic Medical Record and Practice Management System
Copyright (C) 1999-2012 FreeMED Software Foundation

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

<!--{if $pnotesbmi}-->
<div>
<em>BMI</em>
<!--{$pnotesbmi}-->
</div>
<!--{/if}-->
<!--{if $pnotesheight}-->
<div>
<em>Height</em>
<!--{$pnotesheight}-->
</div>
<!--{/if}-->
<!--{if $pnotesweight}-->
<div>
<em>Weight</em>
<!--{$pnotesweight}-->
</div>
<!--{/if}-->
<!--{if $pnotessbp}-->
<div>
<em>Blood Pressure</em>
<!--{$pnotessbp}--> over <!--{$pnotesdbp}-->
</div>
<!--{/if}-->
<!--{if $pnotesheartrate}-->
<div>
<em>Heart Rate</em>
<!--{$pnotesheartrate}-->
</div>
<!--{/if}-->
<!--{if $pnotesresprate}-->
<div>
<em>Respiratory Rate</em>
<!--{$pnotesresprate}-->
</div>
<!--{/if}-->
<!--{if $pnotestemp}-->
<div>
<em>Temperature</em>
<!--{$pnotestemp}-->
</div>
<!--{/if}-->

<table>

<!--{if strlen($pnotes_S) ge 10}-->
<tr>
	<th>SUBJECTIVE</th>
	<td><!--{$pnotes_S}--></td>
</tr>
<!--{/if}-->

<!--{if strlen($pnotes_O) ge 10}-->
<tr>
	<th>OBJECTIVE</th>
	<td><!--{$pnotes_O}--></td>
</tr>
<!--{/if}-->

<!--{if strlen($pnotes_A) ge 10}-->
<tr>
	<th>ASSESSMENT</th>
	<td><!--{$pnotes_A}--></td>
</tr>
<!--{/if}-->

<!--{if strlen($pnotes_P) ge 10}-->
<tr>
	<th>PLAN</th>
	<td><!--{$pnotes_P}--></td>
</tr>
<!--{/if}-->

<!--{if strlen($pnotes_I) ge 10}-->
<tr>
	<th>INTERVAL</th>
	<td><!--{$pnotes_I}--></td>
</tr>
<!--{/if}-->

<!--{if strlen($pnotes_E) ge 10}-->
<tr>
	<th>EDUCATION</th>
	<td><!--{$pnotes_E}--></td>
</tr>
<!--{/if}-->

<!--{if strlen($pnotes_R) ge 10}-->
<tr>
	<th>RX</th>
	<td><!--{$pnotes_R}--></td>
</tr>
<!--{/if}-->

</table>

