# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2012 FreeMED Software Foundation
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

DROP TABLE IF EXISTS growthchart_bmiagerev;
DROP TABLE IF EXISTS growthchart_hcageinf;
DROP TABLE IF EXISTS growthchart_lenageinf;
DROP TABLE IF EXISTS growthchart_statage;
DROP TABLE IF EXISTS growthchart_wtage;
DROP TABLE IF EXISTS growthchart_wtageinf;
DROP TABLE IF EXISTS growthchart_wtleninf;
DROP TABLE IF EXISTS growthchart_wtstat;

#----- Table definitions -----

CREATE TABLE IF NOT EXISTS growthchart_bmiagerev (
	  sex			ENUM ( '1', '2' ) NOT NULL
	, agemos		DECIMAL ( 5, 2 ) NOT NULL
	, L			REAL NOT NULL
	, M			REAL NOT NULL
	, S			REAL NOT NULL
	, P3			REAL
	, P5			REAL
	, P10			REAL
	, P25			REAL
	, P50			REAL
	, P75			REAL
	, P85			REAL
	, P90			REAL
	, P95			REAL
	, P97			REAL
);

CREATE TABLE IF NOT EXISTS growthchart_hcageinf (
	  sex			ENUM ( '1', '2' ) NOT NULL
	, agemos		DECIMAL ( 5, 2 ) NOT NULL
	, L			REAL NOT NULL
	, M			REAL NOT NULL
	, S			REAL NOT NULL
	, P3			REAL
	, P5			REAL
	, P10			REAL
	, P25			REAL
	, P50			REAL
	, P75			REAL
	, P90			REAL
	, P95			REAL
	, P97			REAL
);

CREATE TABLE IF NOT EXISTS growthchart_lenageinf (
	  sex			ENUM ( '1', '2' ) NOT NULL
	, agemos		DECIMAL ( 5, 2 ) NOT NULL
	, L			REAL NOT NULL
	, M			REAL NOT NULL
	, S			REAL NOT NULL
	, P3			REAL
	, P5			REAL
	, P10			REAL
	, P25			REAL
	, P50			REAL
	, P75			REAL
	, P90			REAL
	, P95			REAL
	, P97			REAL
	, pub3			REAL
	, pub5			REAL
	, pub10			REAL
	, pub25			REAL
	, pub50			REAL
	, pub75			REAL
	, pub90			REAL
	, pub95			REAL
	, pub97			REAL
	, diff3			REAL
	, diff5			REAL
	, diff10		REAL
);

CREATE TABLE IF NOT EXISTS growthchart_statage (
	  sex			ENUM ( '1', '2' ) NOT NULL
	, agemos		DECIMAL ( 5, 2 ) NOT NULL
	, L			REAL NOT NULL
	, M			REAL NOT NULL
	, S			REAL NOT NULL
	, P3			REAL
	, P5			REAL
	, P10			REAL
	, P25			REAL
	, P50			REAL
	, P75			REAL
	, P90			REAL
	, P95			REAL
	, P97			REAL
);

CREATE TABLE IF NOT EXISTS growthchart_wtage (
	  sex			ENUM ( '1', '2' ) NOT NULL
	, agemos		DECIMAL ( 5, 2 ) NOT NULL
	, L			REAL NOT NULL
	, M			REAL NOT NULL
	, S			REAL NOT NULL
	, P3			REAL
	, P5			REAL
	, P10			REAL
	, P25			REAL
	, P50			REAL
	, P75			REAL
	, P90			REAL
	, P95			REAL
	, P97			REAL
);

CREATE TABLE IF NOT EXISTS growthchart_wtageinf (
	  sex			ENUM ( '1', '2' ) NOT NULL
	, agemos		DECIMAL ( 5, 2 ) NOT NULL
	, L			REAL NOT NULL
	, M			REAL NOT NULL
	, S			REAL NOT NULL
	, P3			REAL
	, P5			REAL
	, P10			REAL
	, P25			REAL
	, P50			REAL
	, P75			REAL
	, P90			REAL
	, P95			REAL
	, P97			REAL
);

CREATE TABLE IF NOT EXISTS growthchart_wtleninf (
	  sex			ENUM ( '1', '2' ) NOT NULL
	, len			DECIMAL ( 5, 2 ) NOT NULL
	, L			REAL NOT NULL
	, M			REAL NOT NULL
	, S			REAL NOT NULL
	, P3			REAL
	, P5			REAL
	, P10			REAL
	, P25			REAL
	, P50			REAL
	, P75			REAL
	, P90			REAL
	, P95			REAL
	, P97			REAL
);

CREATE TABLE IF NOT EXISTS growthchart_wtstat (
	  sex			ENUM ( '1', '2' ) NOT NULL
	, height		DECIMAL ( 5, 2 ) NOT NULL
	, L			REAL NOT NULL
	, M			REAL NOT NULL
	, S			REAL NOT NULL
	, P3			REAL
	, P5			REAL
	, P10			REAL
	, P25			REAL
	, P50			REAL
	, P75			REAL
	, P85			REAL
	, P90			REAL
	, P95			REAL
	, P97			REAL
);

#----- Import database from CSV export files -----

LOAD DATA LOCAL INFILE "data/source/growthcharts/bmiagerev.csv"
	INTO TABLE growthchart_bmiagerev
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/source/growthcharts/hcageinf.csv"
	INTO TABLE growthchart_hcageinf
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/source/growthcharts/lenageinf.csv"
	INTO TABLE growthchart_lenageinf
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/source/growthcharts/statage.csv"
	INTO TABLE growthchart_statage
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/source/growthcharts/wtage.csv"
	INTO TABLE growthchart_wtage
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/source/growthcharts/wtageinf.csv"
	INTO TABLE growthchart_wtageinf
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/source/growthcharts/wtleninf.csv"
	INTO TABLE growthchart_wtleninf
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/source/growthcharts/wtstat.csv"
	INTO TABLE growthchart_wtstat
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;

