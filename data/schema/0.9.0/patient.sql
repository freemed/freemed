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

CREATE TABLE `patient` (
	ptdtadd			DATE,
	ptdtmod			DATE,
	ptbal			REAL,
	ptbalfwd		REAL,
	ptunapp			REAL,
	ptdoc			VARCHAR (150),
	ptrefdoc		VARCHAR (150),
	ptpcp			VARCHAR (150),
	ptphy1			VARCHAR (150),
	ptphy2			VARCHAR (150),
	ptphy3			VARCHAR (150),
	ptphy4			VARCHAR (150),
	ptbilltype		ENUM( 'sta', 'mon', 'chg' ) NOT NULL,
	ptbudg			REAL,
	ptsalut			VARCHAR (8),
	ptlname			VARCHAR (50),
	ptmaidenname		VARCHAR (50),
	ptfname			VARCHAR (50),
	ptmname			VARCHAR (50),
	ptsuffix		VARCHAR (10),
	ptaddr1			VARCHAR (45),
	ptaddr2			VARCHAR (45),
	ptcity			VARCHAR (45),
	ptstate			VARCHAR (20),
	ptzip			CHAR (10),
	ptcountry		VARCHAR (50),
	pthphone		VARCHAR (16),
	ptwphone		VARCHAR (16),
	ptfax			VARCHAR (16),
	ptemail			VARCHAR (80),
	ptsex			ENUM( 'm', 'f', 't' ) NOT NULL,
	ptdob			DATE,
	ptssn			VARCHAR (9),
	ptdmv			VARCHAR (15),
	ptdtlpay		DATE,
	ptamtlpay		REAL,
	ptpaytype		INT UNSIGNED,
	ptdtbill		DATE,
	ptamtbill		REAL,
	ptstatus		INT UNSIGNED,
	ptytdchg		REAL,
	ptar			REAL,
	ptextinf		TEXT,
	ptdisc			REAL,
	ptdol			DATE,
	ptdiag1			INT UNSIGNED,
	ptdiag2			INT UNSIGNED,
	ptdiag3			INT UNSIGNED,
	ptdiag4			INT UNSIGNED,
	ptid			VARCHAR (10),
	pthistbal		REAL,
	ptmarital		ENUM ( 'single', 'married', 'divorced', 'separated', 'widowed', 'unknown' ),
	ptempl			ENUM ( 'y', 'n' ),
	ptemp1			INT UNSIGNED,
	ptemp2			INT UNSIGNED,
	ptnextofkin		TEXT,
	ptblood			CHAR (3),
	ptdead			INT UNSIGNED NOT NULL DEFAULT 0,
	ptdeaddt		DATE,
	pttimestamp		TIMESTAMP (16),
	ptemritimestamp		TIMESTAMP (16),
	ptemriversion		BLOB,
	ptpharmacy		INT UNSIGNED,
	ptrace			INT UNSIGNED,
	ptreligion		INT UNSIGNED,
	ptarchive		INT UNSIGNED,
	iso			VARCHAR (15),
	id			INT UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY 		( id ),

	#	Define keys
	KEY			( ptlname, ptfname, ptmname, ptid, ptdob )
);

