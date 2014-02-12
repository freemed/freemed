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

CREATE TABLE IF NOT EXISTS `modules` (
	  module_uid			CHAR (36) UNIQUE NOT NULL
	, module_name			VARCHAR (100) NOT NULL
	, module_class			VARCHAR (100) NOT NULL
	, module_table			VARCHAR (100) NOT NULL
	, module_version		VARCHAR (50) NOT NULL
	, module_version_installed	INT UNSIGNED NOT NULL DEFAULT 0
	, module_category		VARCHAR (50) NOT NULL
	, module_path			VARCHAR (250) NOT NULL
	, module_stamp			INT UNSIGNED NOT NULL
	, module_handlers		TEXT
	, module_associations		TEXT
	, module_meta			TEXT
	, module_hidden			TINYINT (3) NOT NULL DEFAULT 0

	, PRIMARY KEY 			( module_uid )
	, INDEX				( module_table )
	, INDEX				( module_class )
);

#----- Triggers

DROP TRIGGER IF EXISTS `modules_insert`;

DELIMITER //

CREATE TRIGGER `modules_insert` AFTER INSERT ON `modules` 
FOR EACH ROW BEGIN
	DECLARE section_exists INT UNSIGNED;
	DECLARE section_was_exists INT UNSIGNED;
	DECLARE section_order INT UNSIGNED;	
	DECLARE object_exists INT UNSIGNED;
	DECLARE seq_id INT UNSIGNED;
	SET object_exists = 0; # set default value
	SET section_was_exists = 1; # set default value

	# Querying ACO section
	SELECT count(s.id) AS abc INTO section_exists FROM acl_aco_sections s 
		WHERE s.value = NEW.module_class;
	
	# If ACO section does not exists then insert section first
	IF section_exists = 0 THEN
		SELECT id INTO seq_id FROM acl_aco_sections_seq;
		SELECT order_value INTO section_order FROM acl_aco_sections ORDER BY order_value DESC LIMIT 1;
		INSERT INTO acl_aco_sections (id,value,order_value,name,hidden) VALUES( seq_id+1, NEW.module_class, section_order+1, NEW.module_name, 0 );
		UPDATE acl_aco_sections_seq SET id = seq_id + 1;
		SET section_was_exists = 0;
	END IF;
	
	#if ACOs Section already exists then check for existence of read ACO Object
	IF section_was_exists = 1 THEN  
		# Query for READ value
		SELECT CASE WHEN o.id IS NULL THEN 0 ELSE 1 END AS abc INTO object_exists FROM acl_aco_sections s 
			LEFT JOIN acl_aco o ON (s.value=o.section_value AND o.value='read') 
			WHERE s.value = NEW.module_class;
	END IF;
	
	# If READ value not exists then adding it
	IF object_exists = 0 THEN
		SELECT id INTO seq_id FROM acl_aco_seq;
		INSERT INTO acl_aco (id,section_value,value,order_value,name,hidden) VALUES(seq_id+1,NEW.module_class,'read',1,CONCAT(NEW.module_name,' Read Access'),0);
		UPDATE acl_aco_seq SET id = seq_id + 1;
		
	END IF;
	# If ACOs Section already exists then check for existence of write ACO Object
	IF section_was_exists = 1 THEN   
		#query for WRITE value
		SELECT CASE WHEN o.id IS NULL THEN 0 ELSE 1 END AS abc INTO object_exists FROM acl_aco_sections s 
			LEFT JOIN acl_aco o ON (s.value=o.section_value AND o.value='write') 
			WHERE s.value=NEW.module_class;
	END IF;
	
	# If WRITE value not exists then adding it
	IF object_exists = 0 THEN
		SELECT id INTO seq_id FROM acl_aco_seq;
		INSERT INTO acl_aco (id,section_value,value,order_value,name,hidden) VALUES(seq_id+1,NEW.module_class,'write',2,CONCAT(NEW.module_name,' Write Access'),0);
		UPDATE acl_aco_seq SET id = seq_id+1;
	END IF;

	# If ACOs Section already exists then check for existence of delete ACO Object
	IF section_was_exists = 1 THEN  
		# Query for DELETE value
		SELECT CASE WHEN o.id IS NULL THEN 0 ELSE 1 END AS abc INTO object_exists FROM acl_aco_sections s 
			LEFT JOIN acl_aco o ON (s.value=o.section_value AND o.value='delete') 
			WHERE s.value = NEW.module_class;
	END IF;
	
	#if Delete value not exists then adding it
	IF object_exists = 0 THEN
		SELECT id INTO seq_id FROM acl_aco_seq;
		INSERT INTO acl_aco (id,section_value,value,order_value,name,hidden) VALUES(seq_id+1,NEW.module_class,'delete',3,CONCAT(NEW.module_name,' Delete Access'),0);
		UPDATE acl_aco_seq SET id = seq_id + 1;
	END IF;
	#if ACOs Section already exists then check for existence of MODIFY ACO Object
	IF section_was_exists = 1 THEN  
		# Query for MODIFY value
		SELECT CASE WHEN o.id IS NULL THEN 0 ELSE 1 END AS abc INTO object_exists FROM acl_aco_sections s 
			LEFT JOIN acl_aco o ON (s.value=o.section_value AND o.value='modify') 
			WHERE s.value = NEW.module_class;
	END IF;
	
	# If MODIFY value not exists then adding it
	IF object_exists = 0 THEN
		SELECT id INTO seq_id FROM acl_aco_seq;
		INSERT INTO acl_aco (id,section_value,value,order_value,name,hidden) VALUES(seq_id+1,NEW.module_class,'modify',4,CONCAT(NEW.module_name,' Modify Access'),0);
		UPDATE acl_aco_seq SET id = seq_id + 1;
	END IF;

	# If ACOs Section already exists then check for existence of Lock ACO Object
	IF section_was_exists = 1 THEN  
		# Query for Lock value
		SELECT CASE WHEN o.id IS NULL THEN 0 ELSE 1 END AS abc INTO object_exists FROM acl_aco_sections s 
			LEFT JOIN acl_aco o ON (s.value=o.section_value AND o.value='lock') 
			WHERE s.value = NEW.module_class;
	END IF;
	
	# If LOCK ACO not exists then adding it
	IF object_exists = 0 THEN
		SELECT id INTO seq_id FROM acl_aco_seq;
		INSERT INTO acl_aco (id,section_value,value,order_value,name,hidden) VALUES(seq_id+1,NEW.module_class,'lock',5,CONCAT(NEW.module_name,' LOCK Access'),0);
		UPDATE acl_aco_seq SET id = seq_id + 1;
	END IF;
    END;
//

DELIMITER ;

DROP TRIGGER IF EXISTS `modules_update`;

DELIMITER //

CREATE TRIGGER `modules_update` AFTER UPDATE ON `modules` 
    FOR EACH ROW BEGIN
	
	UPDATE acl_aco_sections SET value = NEW.module_class,name = NEW.module_name WHERE value = OLD.module_class;
	UPDATE acl_aco SET section_value = NEW.module_class,name = NEW.module_name WHERE section_value = OLD.module_class;
    END;
//

DELIMITER ;

DROP TRIGGER IF EXISTS `modules_delete`;

DELIMITER //

CREATE TRIGGER `modules_delete` AFTER DELETE ON `modules` 
    FOR EACH ROW BEGIN
	DELETE FROM acl_aco WHERE section_value = OLD.module_class;
	DELETE FROM acl_aco_sections WHERE value = OLD.module_class;
	DELETE FROM acl_aco_map WHERE section_value = OLD.module_class;
    END;
//

DELIMITER ;

