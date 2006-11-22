# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2006 FreeMED Software Foundation
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

SOURCE data/schema/mysql/patient.sql
SOURCE data/schema/mysql/physician.sql
SOURCE data/schema/mysql/cpt.sql

CREATE TABLE IF NOT EXISTS `procrec` (
	procpatient		INT UNSIGNED NOT NULL DEFAULT 0,
	proceoc			TEXT,
	proccpt			INT UNSIGNED DEFAULT 0,
	proccptmod		INT UNSIGNED DEFAULT 0,
	proccptmod2		INT UNSIGNED DEFAULT 0,
	proccptmod3		INT UNSIGNED DEFAULT 0,
	procdiag1		INT UNSIGNED DEFAULT 0,
	procdiag2		INT UNSIGNED DEFAULT 0,
	procdiag3		INT UNSIGNED DEFAULT 0,
	procdiag4		INT UNSIGNED DEFAULT 0,
	proccharges		REAL DEFAULT 0.0,
	procunits		REAL DEFAULT 1.0,
	procvoucher		VARCHAR (25),
	procphysician		INT UNSIGNED DEFAULT 0,
	procdt			DATE,
	procpos			INT UNSIGNED DEFAULT 0,
	proccomment		TEXT,
	procbalorig		REAL,
	procbalcurrent		REAL,
	procamtpaid		REAL,
	procbilled		INT UNSIGNED DEFAULT 0,
	procbillable		INT UNSIGNED DEFAULT 0,
	procauth		INT UNSIGNED DEFAULT 0,
	procrefdoc		INT UNSIGNED DEFAULT 0,
	procrefdt		DATE,
	procamtallowed		REAL,
	procdtbilled		TEXT,
	proccurcovid		INT UNSIGNED DEFAULT 0,
	proccurcovtp		INT UNSIGNED DEFAULT 0,
	proccov1		INT UNSIGNED DEFAULT 0,
	proccov2		INT UNSIGNED DEFAULT 0,
	proccov3		INT UNSIGNED DEFAULT 0,
	proccov4		INT UNSIGNED DEFAULT 0,
	procclmtp		INT UNSIGNED DEFAULT 0,
	procmedicaidref		VARCHAR (20),
	procmedicaidresub	VARCHAR (20),
	proclabcharges		REAL DEFAULT 0.0,
	procstatus		VARCHAR (50),
	procslidingscale	CHAR (1),
	proctosoverride		INT UNSIGNED DEFAULT 0,
	id			INT UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY 		( id ),

	#	Define keys

	FOREIGN KEY		( procpatient ) REFERENCES patient ( id ) ON DELETE CASCADE,
	FOREIGN KEY		( proccpt ) REFERENCES cpt ( id ),
	FOREIGN KEY		( procphysician ) REFERENCES physician ( id )
) ENGINE=InnoDB;

