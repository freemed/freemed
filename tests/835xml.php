#!/usr/bin/env php
<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
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

include_once ( dirname(__FILE__).'/bootstrap.test.php' );

$xml = '
<remittance>
   <payers class="java.util.ArrayList">
      <payer idNumber="23009VSDF3" idQualifier="EO">
         <name>DISNEY BENEFITS INCORPORATED</name>
         <address>
            <streetAddressLine1>5760 HILLVIEW DRIVE</streetAddressLine1>
            <city>FLORISSANT</city>
            <stateProvince>MO</stateProvince>
            <postalCode>63031</postalCode>
         </address>
         <payees class="java.util.ArrayList">
            <payee idNumber="233458322" idQualifier="FI">
               <address>
                  <streetAddressLine1>2391 LANTERN LANE</streetAddressLine1>
                  <city>FLORISSANT</city>
                  <stateProvince>MO</stateProvince>
                  <postalCode>63031</postalCode>
               </address>
               <identification class="java.util.ArrayList"/>
               <providerClaimGroups class="java.util.ArrayList">
                  <providerClaimGroup>
                     <claimPayments class="java.util.ArrayList">
                        <claimPayment claimId="D92093134" claimCode="1" claimStatus="PROCESSED: PRIMARY">
                           <claimTotalAmount>287.0</claimTotalAmount>
                           <claimPaidAmount>124.2</claimPaidAmount>
                           <claimPatientResponsibilityAmount>10.0</claimPatientResponsibilityAmount>
                           <claimType>12</claimType>
                           <patient idQualifier="MI" idNumber="239230493">
                              <lastName>SQUAREPANTS</lastName>
                              <firstName>SPONGEBOB</firstName>
                              <middleName></middleName>
                              <suffix></suffix>
                           </patient>
                           <insured idQualifier="MI" idNumber="123901283">
                              <lastName>SQUAREPANTS</lastName>
                              <firstName>JANE</firstName>
                              <middleName>Q</middleName>
                              <suffix></suffix>
                           </insured>
                        </claimPayment>
                        <claimPayment claimId="3249DS903" claimCode="4" claimStatus="DENIED">
                           <claimTotalAmount>780.23</claimTotalAmount>
                           <claimPaidAmount>0.0</claimPaidAmount>
                           <claimPatientResponsibilityAmount>50.0</claimPatientResponsibilityAmount>
                           <claimType>12</claimType>
                           <patient idQualifier="MI" idNumber="309201131">
                              <lastName>HARTLEY</lastName>
                              <firstName>SUE</firstName>
                              <middleName></middleName>
                              <suffix></suffix>
                           </patient>
                           <insured idQualifier="34" idNumber="104296742">
                              <lastName>BUNNY</lastName>
                              <firstName>MISSY</firstName>
                              <middleName></middleName>
                              <suffix></suffix>
                           </insured>
                        </claimPayment>
                        <claimPayment claimId="0906502334" claimCode="2" claimStatus="PROCESSED: SECONDARY">
                           <claimTotalAmount>455.0</claimTotalAmount>
                           <claimPaidAmount>400.0</claimPaidAmount>
                           <claimPatientResponsibilityAmount>55.0</claimPatientResponsibilityAmount>
                           <claimType>12</claimType>
                           <patient idQualifier="MI" idNumber="23560569083">
                              <lastName>REN</lastName>
                              <firstName>STIMPY</firstName>
                              <middleName></middleName>
                              <suffix></suffix>
                           </patient>
                           <insured/>
                        </claimPayment>
                     </claimPayments>
                     <claimAdjustments class="java.util.ArrayList">
                        <claimAdjustment adjustmentGroupCode="PR">
                           <adjustmentGroup>PATIENT RESPONSIBILITY</adjustmentGroup>
                           <adjustmentReasonCode>3</adjustmentReasonCode>
                           <adjustmentAmount>10.0</adjustmentAmount>
                           <reasons class="java.util.ArrayList"/>
                        </claimAdjustment>
                        <claimAdjustment adjustmentGroupCode="PR">
                           <adjustmentGroup>PATIENT RESPONSIBILITY</adjustmentGroup>
                           <adjustmentReasonCode>1</adjustmentReasonCode>
                           <adjustmentAmount>50.0</adjustmentAmount>
                           <reasons class="java.util.ArrayList"/>
                        </claimAdjustment>
                        <claimAdjustment adjustmentGroupCode="PR">
                           <adjustmentGroup>PATIENT RESPONSIBILITY</adjustmentGroup>
                           <adjustmentReasonCode>3</adjustmentReasonCode>
                           <adjustmentAmount>55.0</adjustmentAmount>
                           <reasons class="java.util.ArrayList"/>
                        </claimAdjustment>
                     </claimAdjustments>
                  </providerClaimGroup>
               </providerClaimGroups>
            </payee>
         </payees>
      </payer>
   </payers>
</remittance>
';

print "Creating parser\n";
$parser = CreateObject( 'org.freemedsoftware.core.Parser_835XML', $xml, array( 'debug' => 1, 'testmode' => 1 ));
print "Loading parser Handle() method\n";
$parser->Handle();

?>
