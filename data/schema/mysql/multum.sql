# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2011 FreeMED Software Foundation
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

#----- Drop all old tables -----

DROP TABLE IF EXISTS icd_9;
DROP TABLE IF EXISTS multum_combination_drug;
DROP TABLE IF EXISTS multum_dose_form;
DROP TABLE IF EXISTS multum_drug_id;
DROP TABLE IF EXISTS multum_product_strength;
DROP TABLE IF EXISTS multum_route;
DROP TABLE IF EXISTS multum_units;
DROP TABLE IF EXISTS ndc_active_ingredient;
DROP TABLE IF EXISTS ndc_active_ingredient_list;
DROP TABLE IF EXISTS ndc_brand_name;
DROP TABLE IF EXISTS ndc_core_description;
DROP TABLE IF EXISTS ndc_ingredient_strength;
DROP TABLE IF EXISTS ndc_main_multum_drug_code;
DROP TABLE IF EXISTS ndc_orange_book;
DROP TABLE IF EXISTS ndc_pregnancy_category;
DROP TABLE IF EXISTS ndc_source;
DROP TABLE IF EXISTS multum;

#----- Table definitions -----

CREATE TABLE IF NOT EXISTS icd_9 (
	icd_9			VARCHAR (20), 
	disease_name		VARCHAR (150), 
	valid			VARCHAR (2)
);

CREATE TABLE IF NOT EXISTS multum_combination_drug (
	drug_id			VARCHAR (12) NOT NULL, 
	member_drug_id		VARCHAR (12)

	, INDEX ( drug_id )
);

CREATE TABLE IF NOT EXISTS multum_dose_form (
	dose_form_code			INT, 
	dose_form_abbr			VARCHAR (60), 
	dose_form_description		TEXT

	, INDEX ( dose_form_code )
);

CREATE TABLE IF NOT EXISTS multum_drug_id (
	drug_id			VARCHAR (12), 
	drug_name		VARCHAR (250)

	, INDEX ( drug_id )
);

CREATE TABLE IF NOT EXISTS multum_product_strength (
	product_strength_code			INT, 
	product_strength_description		VARCHAR (250)

	, INDEX ( product_strength_code )
);

CREATE TABLE IF NOT EXISTS multum_route (
	route_code			INT NOT NULL, 
	route_abbr			VARCHAR (60), 
	route_description		VARCHAR (250)

	, PRIMARY KEY ( route_code )
);

CREATE TABLE IF NOT EXISTS multum_units (
	unit_id			INT NOT NULL, 
	unit_abbr		VARCHAR (60), 
	unit_description	VARCHAR (250)

	, PRIMARY KEY ( unit_id )
);

CREATE TABLE IF NOT EXISTS ndc_active_ingredient (
	active_ingredient_code			INT NOT NULL, 
	active_ingredient			VARCHAR (250)

	, PRIMARY KEY ( active_ingredient_code )
);

CREATE TABLE IF NOT EXISTS ndc_active_ingredient_list (
	main_multum_drug_code			INT, 
	active_ingredient_code			INT, 
	ingredient_strength_code		INT

	, KEY ( main_multum_drug_code )
	, KEY ( active_ingredient_code )
	, KEY ( ingredient_strength_code )
);

CREATE TABLE IF NOT EXISTS ndc_brand_name (
	brand_code			INT NOT NULL, 
	brand_description		VARCHAR (250)

	, INDEX ( brand_code )
	, INDEX ( brand_description )
);

CREATE TABLE IF NOT EXISTS ndc_core_description (
	ndc_code			VARCHAR (22), 
	main_multum_drug_code		INT, 
	brand_code			INT, 
	otc_status			VARCHAR (2), 
	inner_package_size		FLOAT, 
	inner_package_desc_code		INT, 
	outer_package_size		FLOAT, 
	obsolete_date			DATE, 
	source_id			INT, 
	orange_book_id			INT, 
	unit_dose_code			VARCHAR (2), 
	repackaged			VARCHAR (2), 
	gbo				VARCHAR (2)

	, INDEX ( brand_code )
	, INDEX ( main_multum_drug_code )
	, INDEX ( inner_package_desc_code )
);

CREATE TABLE IF NOT EXISTS ndc_ingredient_strength (
	ingredient_strength_code		INT, 
	strength_num_amount			FLOAT, 
	strength_num_unit			INT, 
	strength_denom_amount			FLOAT, 
	strength_denom_unit			INT

	, INDEX ( ingredient_strength_code )
);

CREATE TABLE IF NOT EXISTS ndc_main_multum_drug_code (
	main_multum_drug_code			INT, 
	principal_route_code			INT, 
	dose_form_code				INT, 
	product_strength_code			INT, 
	drug_id					VARCHAR (12), 
	csa_schedule				VARCHAR (2), 
	j_code					VARCHAR (20), 
	j_code_description			VARCHAR (100)

	, INDEX ( main_multum_drug_code, dose_form_code, product_strength_code )
	, INDEX ( drug_id )
);

CREATE TABLE IF NOT EXISTS ndc_orange_book (
	orange_book_id				INT, 
	orange_book_desc_ab			VARCHAR (4), 
	orange_book_description			VARCHAR (100)
);

CREATE TABLE IF NOT EXISTS ndc_pregnancy_category (
	drug_id					VARCHAR (12), 
	pregnancy_category			VARCHAR (2)
);

CREATE TABLE IF NOT EXISTS ndc_source (
	source_id			INT, 
	source_desc			VARCHAR (240), 
	address1			VARCHAR (200), 
	address2			VARCHAR (100), 
	city				VARCHAR (100), 
	state				VARCHAR (20), 
	province			VARCHAR (60), 
	zip				VARCHAR (20), 
	country				VARCHAR (100)
);

#----- Aggregation table definition -----

CREATE TABLE IF NOT EXISTS multum (
	multum_id			VARCHAR (12) NOT NULL,
	description			VARCHAR (100) NOT NULL,
	brand_description		VARCHAR (250),
	dose_size			TEXT,
	dose_size_link			TEXT,
	units				VARCHAR (50),
	form				VARCHAR (50),
	brand_id			BIGINT,
	main_multum_drug_code		BIGINT,
	id				CHAR (20) NOT NULL

	, KEY ( id )
	, KEY ( description, multum_id )
);

#----- Import multum database from CSV export files -----

LOAD DATA LOCAL INFILE "data/multum/icd_9.csv"
	INTO TABLE icd_9
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/multum/multum_combination_drug.csv"
	INTO TABLE multum_combination_drug
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/multum/multum_dose_form.csv"
	INTO TABLE multum_dose_form
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/multum/multum_drug_id.csv"
	INTO TABLE multum_drug_id
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/multum/multum_product_strength.csv"
	INTO TABLE multum_product_strength
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/multum/multum_route.csv"
	INTO TABLE multum_route
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/multum/multum_units.csv"
	INTO TABLE multum_units
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/multum/ndc_active_ingredient.csv"
	INTO TABLE ndc_active_ingredient
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/multum/ndc_active_ingredient_list.csv"
	INTO TABLE ndc_active_ingredient_list
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/multum/ndc_brand_name.csv"
	INTO TABLE ndc_brand_name
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/multum/ndc_core_description.csv"
	INTO TABLE ndc_core_description
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/multum/ndc_ingredient_strength.csv"
	INTO TABLE ndc_ingredient_strength
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/multum/ndc_main_multum_drug_code.csv"
	INTO TABLE ndc_main_multum_drug_code
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/multum/ndc_orange_book.csv"
	INTO TABLE ndc_orange_book
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/multum/ndc_pregnancy_category.csv"
	INTO TABLE ndc_pregnancy_category
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;
LOAD DATA LOCAL INFILE "data/multum/ndc_source.csv"
	INTO TABLE ndc_source
	FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' IGNORE 1 LINES;

#----- Recomposite table from individual components -----
INSERT INTO multum
	SELECT
		  mdc.drug_id
		, drug_name
		, brand_description
		, GROUP_CONCAT( DISTINCT ps.product_strength_description )
		, GROUP_CONCAT( DISTINCT ps.product_strength_code )
		, GROUP_CONCAT( DISTINCT unit_abbr )
		, dose_form_description
		, cd.brand_code
		, cd.main_multum_drug_code
		, CONCAT( cd.main_multum_drug_code, '-', cd.brand_code )
	FROM ndc_core_description cd
		LEFT OUTER JOIN ndc_main_multum_drug_code mdc ON mdc.main_multum_drug_code = cd.main_multum_drug_code
		LEFT OUTER JOIN ndc_brand_name bn ON bn.brand_code = cd.brand_code
		LEFT OUTER JOIN multum_units u ON u.unit_id = cd.inner_package_desc_code
		LEFT OUTER JOIN multum_drug_id mdi ON mdi.drug_id = mdc.drug_id
		LEFT OUTER JOIN multum_dose_form df ON df.dose_form_code = mdc.dose_form_code
		LEFT OUTER JOIN multum_product_strength ps ON ps.product_strength_code = mdc.product_strength_code
	GROUP BY bn.brand_code, mdc.dose_form_code;

