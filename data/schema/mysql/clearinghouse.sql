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

CREATE TABLE IF NOT EXISTS `clearinghouse` (
    chname varchar(50) NOT NULL,                         
    chaddr varchar(45) default NULL,                     
    chcity varchar(30) default NULL,                     
    chstate char(3) default NULL,                        
    chzip varchar(10) default NULL,                      
    chphone varchar(16) default NULL,                    
    chetin varchar(24) default NULL,                     
    chx12gssender varchar(20) default NULL,              
    chx12gsreceiver varchar(20) default NULL,
	stamp	TIMESTAMP (14) NOT NULL DEFAULT NOW(),
	user	INT UNSIGNED,
	id			SERIAL,
	#keys
	PRIMARY KEY  (`id`)
);