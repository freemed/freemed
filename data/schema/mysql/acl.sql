# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2007 FreeMED Software Foundation
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

#
#	FIXME FIXME: THIS IS NOT UPGRADE-SAFE ... IT WIPES ACL TABLES
#

--
-- Table structure for table `acl_acl`
--

DROP TABLE IF EXISTS `acl_acl`;
CREATE TABLE `acl_acl` (
  `id` int(11) NOT NULL default '0',
  `section_value` varchar(230) NOT NULL default 'system',
  `allow` int(11) NOT NULL default '0',
  `enabled` int(11) NOT NULL default '0',
  `return_value` text,
  `note` text,
  `updated_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `acl_enabled_acl` (`enabled`),
  KEY `acl_section_value_acl` (`section_value`),
  KEY `acl_updated_date_acl` (`updated_date`)
);

--
-- Dumping data for table `acl_acl`
--

LOCK TABLES `acl_acl` WRITE;
/*!40000 ALTER TABLE `acl_acl` DISABLE KEYS */;
INSERT INTO `acl_acl` VALUES (10,'user',1,1,'1','Scheduler Access',1172674043),(11,'user',1,1,'1','Administrator Access',1172674341),(12,'user',1,1,'1','Biller Access',1172674393),(13,'user',1,1,'1','Provider Access',1172674432);
/*!40000 ALTER TABLE `acl_acl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_acl_sections`
--

DROP TABLE IF EXISTS `acl_acl_sections`;
CREATE TABLE `acl_acl_sections` (
  `id` int(11) NOT NULL default '0',
  `value` varchar(230) NOT NULL,
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL,
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `acl_value_acl_sections` (`value`),
  KEY `acl_hidden_acl_sections` (`hidden`)
);

--
-- Dumping data for table `acl_acl_sections`
--

LOCK TABLES `acl_acl_sections` WRITE;
/*!40000 ALTER TABLE `acl_acl_sections` DISABLE KEYS */;
INSERT INTO `acl_acl_sections` VALUES (1,'system',1,'System',0),(2,'user',2,'User',0);
/*!40000 ALTER TABLE `acl_acl_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_acl_sections_seq`
--

DROP TABLE IF EXISTS `acl_acl_sections_seq`;
CREATE TABLE `acl_acl_sections_seq` (
  `id` int(11) NOT NULL
);

--
-- Dumping data for table `acl_acl_sections_seq`
--

LOCK TABLES `acl_acl_sections_seq` WRITE;
/*!40000 ALTER TABLE `acl_acl_sections_seq` DISABLE KEYS */;
INSERT INTO `acl_acl_sections_seq` VALUES (10);
/*!40000 ALTER TABLE `acl_acl_sections_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_acl_seq`
--

DROP TABLE IF EXISTS `acl_acl_seq`;
CREATE TABLE `acl_acl_seq` (
  `id` int(11) NOT NULL
);

--
-- Dumping data for table `acl_acl_seq`
--

LOCK TABLES `acl_acl_seq` WRITE;
/*!40000 ALTER TABLE `acl_acl_seq` DISABLE KEYS */;
INSERT INTO `acl_acl_seq` VALUES (13);
/*!40000 ALTER TABLE `acl_acl_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_aco`
--

DROP TABLE IF EXISTS `acl_aco`;
CREATE TABLE `acl_aco` (
  `id` int(11) NOT NULL default '0',
  `section_value` varchar(240) NOT NULL default '0',
  `value` varchar(240) NOT NULL,
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `acl_section_value_value_aco` (`section_value`,`value`),
  KEY `acl_hidden_aco` (`hidden`)
);

--
-- Dumping data for table `acl_aco`
--

LOCK TABLES `acl_aco` WRITE;
/*!40000 ALTER TABLE `acl_aco` DISABLE KEYS */;
INSERT INTO `acl_aco` VALUES (10,'admin','menu',1,'Menu Access',0),(11,'admin','config',2,'System Configuration',0),(12,'scheduling','view',1,'View Scheduler',0),(13,'scheduling','book',2,'Book Appointments',0),(14,'scheduling','move',3,'Move Appointments',0),(15,'emr','search',1,'Patient Search',0),(16,'emr','demographics',2,'View Demographics',0),(17,'emr','entry',3,'Patient Entry',0),(18,'emr','modify',4,'Modify Entries',0),(19,'emr','delete',5,'Delete Entries',0),(20,'emr','lock',6,'Lock Entries',0),(21,'financial','menu',1,'Menu Access',0),(22,'financial','summary',2,'Practice/Total Summary Information',0),(23,'reporting','menu',1,'Menu Access',0),(24,'reporting','generate',2,'Generate Reports',0);
/*!40000 ALTER TABLE `acl_aco` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_aco_map`
--

DROP TABLE IF EXISTS `acl_aco_map`;
CREATE TABLE `acl_aco_map` (
  `acl_id` int(11) NOT NULL default '0',
  `section_value` varchar(230) NOT NULL default '0',
  `value` varchar(230) NOT NULL,
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
);

--
-- Dumping data for table `acl_aco_map`
--

LOCK TABLES `acl_aco_map` WRITE;
/*!40000 ALTER TABLE `acl_aco_map` DISABLE KEYS */;
INSERT INTO `acl_aco_map` VALUES (10,'emr','search'),(10,'scheduling','book'),(10,'scheduling','move'),(10,'scheduling','view'),(11,'admin','config'),(11,'admin','menu'),(11,'emr','delete'),(11,'emr','demographics'),(11,'emr','entry'),(11,'emr','lock'),(11,'emr','modify'),(11,'emr','search'),(11,'financial','menu'),(11,'financial','summary'),(11,'reporting','generate'),(11,'reporting','menu'),(11,'scheduling','book'),(11,'scheduling','move'),(11,'scheduling','view'),(12,'emr','demographics'),(12,'emr','search'),(12,'financial','menu'),(12,'financial','summary'),(12,'reporting','generate'),(12,'reporting','menu'),(13,'emr','delete'),(13,'emr','demographics'),(13,'emr','entry'),(13,'emr','lock'),(13,'emr','modify'),(13,'emr','search'),(13,'financial','menu'),(13,'reporting','menu'),(13,'scheduling','book'),(13,'scheduling','move'),(13,'scheduling','view');
/*!40000 ALTER TABLE `acl_aco_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_aco_sections`
--

DROP TABLE IF EXISTS `acl_aco_sections`;
CREATE TABLE `acl_aco_sections` (
  `id` int(11) NOT NULL default '0',
  `value` varchar(230) NOT NULL,
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL,
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `acl_value_aco_sections` (`value`),
  KEY `acl_hidden_aco_sections` (`hidden`)
);

--
-- Dumping data for table `acl_aco_sections`
--

LOCK TABLES `acl_aco_sections` WRITE;
/*!40000 ALTER TABLE `acl_aco_sections` DISABLE KEYS */;
INSERT INTO `acl_aco_sections` VALUES (10,'admin',1,'Administration',0),(11,'emr',2,'EMR',0),(12,'financial',3,'Billing and Financial',0),(13,'reporting',4,'Reporting',0),(14,'scheduling',5,'Scheduling',0);
/*!40000 ALTER TABLE `acl_aco_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_aco_sections_seq`
--

DROP TABLE IF EXISTS `acl_aco_sections_seq`;
CREATE TABLE `acl_aco_sections_seq` (
  `id` int(11) NOT NULL
);

--
-- Dumping data for table `acl_aco_sections_seq`
--

LOCK TABLES `acl_aco_sections_seq` WRITE;
/*!40000 ALTER TABLE `acl_aco_sections_seq` DISABLE KEYS */;
INSERT INTO `acl_aco_sections_seq` VALUES (14);
/*!40000 ALTER TABLE `acl_aco_sections_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_aco_seq`
--

DROP TABLE IF EXISTS `acl_aco_seq`;
CREATE TABLE `acl_aco_seq` (
  `id` int(11) NOT NULL
);

--
-- Dumping data for table `acl_aco_seq`
--

LOCK TABLES `acl_aco_seq` WRITE;
/*!40000 ALTER TABLE `acl_aco_seq` DISABLE KEYS */;
INSERT INTO `acl_aco_seq` VALUES (24);
/*!40000 ALTER TABLE `acl_aco_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_aro`
--

DROP TABLE IF EXISTS `acl_aro`;
CREATE TABLE `acl_aro` (
  `id` int(11) NOT NULL default '0',
  `section_value` varchar(240) NOT NULL default '0',
  `value` varchar(240) NOT NULL,
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `acl_section_value_value_aro` (`section_value`,`value`),
  KEY `acl_hidden_aro` (`hidden`)
);

--
-- Dumping data for table `acl_aro`
--

LOCK TABLES `acl_aro` WRITE;
/*!40000 ALTER TABLE `acl_aro` DISABLE KEYS */;
INSERT INTO `acl_aro` VALUES (10,'user','1',0,'admin',0);
/*!40000 ALTER TABLE `acl_aro` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_aro_groups`
--

DROP TABLE IF EXISTS `acl_aro_groups`;
CREATE TABLE `acl_aro_groups` (
  `id` int(11) NOT NULL default '0',
  `parent_id` int(11) NOT NULL default '0',
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`,`value`),
  UNIQUE KEY `acl_value_aro_groups` (`value`),
  KEY `acl_parent_id_aro_groups` (`parent_id`),
  KEY `acl_lft_rgt_aro_groups` (`lft`,`rgt`)
);

--
-- Dumping data for table `acl_aro_groups`
--

LOCK TABLES `acl_aro_groups` WRITE;
/*!40000 ALTER TABLE `acl_aro_groups` DISABLE KEYS */;
INSERT INTO `acl_aro_groups` VALUES (10,0,1,10,'Users','users'),(11,10,2,3,'Administrator','admin'),(12,10,4,5,'Scheduler','scheduler'),(13,10,6,7,'Provider','provider'),(14,10,8,9,'Biller','biller');
/*!40000 ALTER TABLE `acl_aro_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_aro_groups_id_seq`
--

DROP TABLE IF EXISTS `acl_aro_groups_id_seq`;
CREATE TABLE `acl_aro_groups_id_seq` (
  `id` int(11) NOT NULL
);

--
-- Dumping data for table `acl_aro_groups_id_seq`
--

LOCK TABLES `acl_aro_groups_id_seq` WRITE;
/*!40000 ALTER TABLE `acl_aro_groups_id_seq` DISABLE KEYS */;
INSERT INTO `acl_aro_groups_id_seq` VALUES (14);
/*!40000 ALTER TABLE `acl_aro_groups_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_aro_groups_map`
--

DROP TABLE IF EXISTS `acl_aro_groups_map`;
CREATE TABLE `acl_aro_groups_map` (
  `acl_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`acl_id`,`group_id`)
);

--
-- Dumping data for table `acl_aro_groups_map`
--

LOCK TABLES `acl_aro_groups_map` WRITE;
/*!40000 ALTER TABLE `acl_aro_groups_map` DISABLE KEYS */;
INSERT INTO `acl_aro_groups_map` VALUES (10,12),(11,11),(12,14),(13,13);
/*!40000 ALTER TABLE `acl_aro_groups_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_aro_map`
--

DROP TABLE IF EXISTS `acl_aro_map`;
CREATE TABLE `acl_aro_map` (
  `acl_id` int(11) NOT NULL default '0',
  `section_value` varchar(230) NOT NULL default '0',
  `value` varchar(230) NOT NULL,
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
);

--
-- Dumping data for table `acl_aro_map`
--

LOCK TABLES `acl_aro_map` WRITE;
/*!40000 ALTER TABLE `acl_aro_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `acl_aro_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_aro_sections`
--

DROP TABLE IF EXISTS `acl_aro_sections`;
CREATE TABLE `acl_aro_sections` (
  `id` int(11) NOT NULL default '0',
  `value` varchar(230) NOT NULL,
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL,
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `acl_value_aro_sections` (`value`),
  KEY `acl_hidden_aro_sections` (`hidden`)
);

--
-- Dumping data for table `acl_aro_sections`
--

LOCK TABLES `acl_aro_sections` WRITE;
/*!40000 ALTER TABLE `acl_aro_sections` DISABLE KEYS */;
INSERT INTO `acl_aro_sections` VALUES (10,'user',1,'User',0);
/*!40000 ALTER TABLE `acl_aro_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_aro_sections_seq`
--

DROP TABLE IF EXISTS `acl_aro_sections_seq`;
CREATE TABLE `acl_aro_sections_seq` (
  `id` int(11) NOT NULL
);

--
-- Dumping data for table `acl_aro_sections_seq`
--

LOCK TABLES `acl_aro_sections_seq` WRITE;
/*!40000 ALTER TABLE `acl_aro_sections_seq` DISABLE KEYS */;
INSERT INTO `acl_aro_sections_seq` VALUES (10);
/*!40000 ALTER TABLE `acl_aro_sections_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_aro_seq`
--

DROP TABLE IF EXISTS `acl_aro_seq`;
CREATE TABLE `acl_aro_seq` (
  `id` int(11) NOT NULL
);

--
-- Dumping data for table `acl_aro_seq`
--

LOCK TABLES `acl_aro_seq` WRITE;
/*!40000 ALTER TABLE `acl_aro_seq` DISABLE KEYS */;
INSERT INTO `acl_aro_seq` VALUES (10);
/*!40000 ALTER TABLE `acl_aro_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_axo`
--

DROP TABLE IF EXISTS `acl_axo`;
CREATE TABLE `acl_axo` (
  `id` int(11) NOT NULL default '0',
  `section_value` varchar(240) NOT NULL default '0',
  `value` varchar(240) NOT NULL,
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `acl_section_value_value_axo` (`section_value`,`value`),
  KEY `acl_hidden_axo` (`hidden`)
);

--
-- Dumping data for table `acl_axo`
--

LOCK TABLES `acl_axo` WRITE;
/*!40000 ALTER TABLE `acl_axo` DISABLE KEYS */;
/*!40000 ALTER TABLE `acl_axo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_axo_groups`
--

DROP TABLE IF EXISTS `acl_axo_groups`;
CREATE TABLE `acl_axo_groups` (
  `id` int(11) NOT NULL default '0',
  `parent_id` int(11) NOT NULL default '0',
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`,`value`),
  UNIQUE KEY `acl_value_axo_groups` (`value`),
  KEY `acl_parent_id_axo_groups` (`parent_id`),
  KEY `acl_lft_rgt_axo_groups` (`lft`,`rgt`)
);

--
-- Dumping data for table `acl_axo_groups`
--

LOCK TABLES `acl_axo_groups` WRITE;
/*!40000 ALTER TABLE `acl_axo_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `acl_axo_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_axo_groups_map`
--

DROP TABLE IF EXISTS `acl_axo_groups_map`;
CREATE TABLE `acl_axo_groups_map` (
  `acl_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`acl_id`,`group_id`)
);

--
-- Dumping data for table `acl_axo_groups_map`
--

LOCK TABLES `acl_axo_groups_map` WRITE;
/*!40000 ALTER TABLE `acl_axo_groups_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `acl_axo_groups_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_axo_map`
--

DROP TABLE IF EXISTS `acl_axo_map`;
CREATE TABLE `acl_axo_map` (
  `acl_id` int(11) NOT NULL default '0',
  `section_value` varchar(230) NOT NULL default '0',
  `value` varchar(230) NOT NULL,
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
);

--
-- Dumping data for table `acl_axo_map`
--

LOCK TABLES `acl_axo_map` WRITE;
/*!40000 ALTER TABLE `acl_axo_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `acl_axo_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_axo_sections`
--

DROP TABLE IF EXISTS `acl_axo_sections`;
CREATE TABLE `acl_axo_sections` (
  `id` int(11) NOT NULL default '0',
  `value` varchar(230) NOT NULL,
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL,
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `acl_value_axo_sections` (`value`),
  KEY `acl_hidden_axo_sections` (`hidden`)
);

--
-- Dumping data for table `acl_axo_sections`
--

LOCK TABLES `acl_axo_sections` WRITE;
/*!40000 ALTER TABLE `acl_axo_sections` DISABLE KEYS */;
/*!40000 ALTER TABLE `acl_axo_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_groups_aro_map`
--

DROP TABLE IF EXISTS `acl_groups_aro_map`;
CREATE TABLE `acl_groups_aro_map` (
  `group_id` int(11) NOT NULL default '0',
  `aro_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`aro_id`),
  KEY `acl_aro_id` (`aro_id`)
);

--
-- Dumping data for table `acl_groups_aro_map`
--

LOCK TABLES `acl_groups_aro_map` WRITE;
/*!40000 ALTER TABLE `acl_groups_aro_map` DISABLE KEYS */;
INSERT INTO `acl_groups_aro_map` VALUES (11,10);
/*!40000 ALTER TABLE `acl_groups_aro_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_groups_axo_map`
--

DROP TABLE IF EXISTS `acl_groups_axo_map`;
CREATE TABLE `acl_groups_axo_map` (
  `group_id` int(11) NOT NULL default '0',
  `axo_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`axo_id`),
  KEY `acl_axo_id` (`axo_id`)
);

--
-- Dumping data for table `acl_groups_axo_map`
--

LOCK TABLES `acl_groups_axo_map` WRITE;
/*!40000 ALTER TABLE `acl_groups_axo_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `acl_groups_axo_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_phpgacl`
--

DROP TABLE IF EXISTS `acl_phpgacl`;
CREATE TABLE `acl_phpgacl` (
  `name` varchar(230) NOT NULL,
  `value` varchar(230) NOT NULL,
  PRIMARY KEY  (`name`)
);

--
-- Dumping data for table `acl_phpgacl`
--

LOCK TABLES `acl_phpgacl` WRITE;
/*!40000 ALTER TABLE `acl_phpgacl` DISABLE KEYS */;
INSERT INTO `acl_phpgacl` VALUES ('version','3.3.7'),('schema_version','2.1');
/*!40000 ALTER TABLE `acl_phpgacl` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2007-02-28 17:08:00
