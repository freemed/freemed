#!/usr/bin/env php
<?php
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

if (!$_SERVER['argc']) { die ("cannot be called via web"); }

ini_set('include_path', dirname(dirname(__FILE__)).':'.ini_get('include_path'));
include_once ( 'lib/freemed.php' );

print "Upgrade from 0.8.x Tool\n";
print "(c) 2007 FreeMED Software Foundation\n\n";

function getInput ( $mask ) { fscanf(STDIN, "${mask}\n", $x); return $x; }
function execSql  ( $s    ) { print " - Executing \"$s\" : "; $GLOBALS['sql']->query( $s ); print " ... [done]\n"; }
function printHeader ( $x ) { print "\n\n ----->> ${x} <<-----\n\n"; }
function loadSchema ( $s, $sk=false  ) { $c="./scripts/load_schema.sh 'mysql' '${s}' '".DB_USER."' '".DB_PASSWORD."' '".DB_NAME."' ".($sk ? '1' : '' ); print `$c`; print "\n\n"; }

if ( ! file_exists ( './scripts/upgrade.php' ) ) {
	print "You must run this from the root directory of your FreeMED install.\n\n";
	die();
}

print "Please type 'yes' if you're *sure* you want to do this : ";
if ( getInput( '%s' ) != 'yes' ) {
	print "\nI didn't think so. :(\n";
	die();
}

printHeader( "Upgrade Keys" );
execSql( "ALTER TABLE patient CHANGE COLUMN id id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;" );
execSql( "ALTER TABLE physician CHANGE COLUMN id id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;" );
execSql( "ALTER TABLE procrec CHANGE COLUMN id id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;" );
execSql( "ALTER TABLE rx CHANGE COLUMN id id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;" );

printHeader( "Include aggregation table definition" );
loadSchema( 'patient', true );
loadSchema( 'patient_emr', true );

printHeader( "Load admin table definitions" );
loadSchema( 'modules' );
loadSchema( 'user' );

printHeader( "Build aggregation tables" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, status ) SELECT 'allergies', patient, id, reviewed, allergy, 'active' FROM allergies;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, status ) SELECT 'authorizations', authpatient, id, authdtadd, CONCAT(authdtbegin, ' - ', authdtend, ' (', authnum, ')', 'active' FROM authorizations;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, status ) SELECT 'certifications', certpatient, id, NOW(), certdesc, 'active' FROM certifications;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, status ) SELECT 'chronic_problems', FROM chronic_problems;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, status ) SELECT 'coverage', covpatient, id, covdtadd, CONCAT( covplanname, '[', covrel, ']' ), IF(covstatus=1, 'inactive', 'active') FROM coverage;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, status ) SELECT 'current_problems', ppatient, id, pdate, problem, 'active' FROM current_problems;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, status ) SELECT 'eoc', eocpatient, id, NOW(), eocdescrip, 'active' FROM eoc;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, status ) SELECT 'financial_demographics', fdpatient, id, fdtimestamp, fdentry, 'active' FROM financialdemographics;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, status ) SELECT 'form_results', fr_patient, id, fr_timestamp, fr_template, 'active' FROM form_results;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, locked, status ) SELECT 'images', imagepat, id, imagedt, imagedesc, locked, 'active' FROM images;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, status ) SELECT 'labs', labpatient, id, labtimestamp, CONCAT( labordercode, ' - ', laborderdescrip ), 'active' FROM labs;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, locked, status ) SELECT 'letters', letterpatient, l.id, letterdt, CONCAT( p.phyfname, ' ', p.phylname), locked, 'active' FROM letters l LEFT OUTER JOIN physician p ON letters.letterto=p.id;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, status ) SELECT 'medications', mpatient, id, mdate, CONCAT( mdrug, ' ', mdosage ), 'active' FROM medications;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, user, status ) SELECT 'messages', msgpatient, id, msgtime, msgsubject, msgby, 'active' FROM messages GROUP BY msgunique;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, user, status ) SELECT 'notification', npatient, id, noriginal, ndescrip, nuser, 'active' FROM notification;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, locked, status ) SELECT 'patletter', letterpatient, l.id, letterdt, CONCAT( p.phyfname, ' ', p.phylname ), locked, 'active' FROM patletter l LEFT OUTER JOIN physician p ON patletter.letterfrom=p.id;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, locked, status ) SELECT 'payrec', payrecpatient, id, NOW(), payrecdescrip, payreclock, 'active' FROM payrec;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, locked, provider, status ) SELECT 'pnotes', pnotespat, id, pnotesdt, pnotesdescrip, locked, pnotesdoc, 'active' FROM pnotes;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, status ) SELECT 'previous_operations', opatient, id, odate, operation, 'active' FROM previous_operations;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, status ) SELECT 'procrec', procpatient, p.id, procdt, CONCAT( c.cptcode, ' - ', c.cptdescrip), 'active' FROM procrec p LEFT OUTER JOIN cpt c ON procrec.proccpt=c.id;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, locked, status ) SELECT 'rx', rxpatient, id, rxdatefrom, rxdrug, locked, 'active' FROM rx;" );
execSql( "INSERT INTO patient_emr ( module, patient, oid, stamp, summary, status ) SELECT 'scheduler', calpatient, id, caldateof, calprenote, 'active' FROM scheduler WHERE caltype='pat';" );

printHeader( "Update Djvu storage paths" );
execSql( "UPDATE images SET imagefile=REPLACE(imagefile, 'img/store/', 'data/store/');" );

printHeader( "Wipe and upgrade ACL tables" );
loadSchema( 'acl' );
include_once( dirname(__FILE__).'/../modules/acl.module.php' );
$acl = new ACL();
$q = "SELECT username, id FROM user WHERE id > 0";
$r = $GLOBALS['sql]->queryAll( $q );
foreach ( $r AS $user ) {
	print " - Adding ACL record for user $r[username] ($r[id]) \n";
	$acl->UserAdd( $r['id'] );
}

printHeader( "Create 'healthy system' status" );
`touch ./data/cache/healthy`;

printHeader( "Force module definition upgrades" );
$modules = CreateObject( 'org.freemedsoftware.core.ModuleIndex', true, false );

?>
