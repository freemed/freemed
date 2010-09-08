# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2010 FreeMED Software Foundation
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

CREATE TABLE IF NOT EXISTS `enotes` (                                    
          pnotesdt DATE DEFAULT NULL,                               
          pnotesdtadd DATE DEFAULT NULL,                            
          pnotesdtmod DATE DEFAULT NULL,                            
          pnotespat INT(10) UNSIGNED DEFAULT NULL,                  
          pnotesdescrip VARCHAR(100) DEFAULT NULL,  
          pnotestype enum('Encounter Note','Progress Note') DEFAULT NULL,                  
          pnotesdoc INT(10) UNSIGNED DEFAULT NULL,                  
          pnoteseoc INT(10) UNSIGNED DEFAULT NULL,                  
          pnotestemplate INT(10) UNSIGNED DEFAULT NULL,             
          pnotes_S BLOB,                                            
          pnotes_O BLOB,                                            
          pnotes_A BLOB,                                            
          pnotes_P BLOB,                                            
          pnotes_I BLOB,                                            
          pnotes_E BLOB,                                            
          pnotes_R BLOB,                                            
          pnotessbp INT(10) UNSIGNED DEFAULT NULL,                  
          pnotesdbp INT(10) UNSIGNED DEFAULT NULL,                  
          pnotestemp DOUBLE DEFAULT NULL,                           
          pnotesheartrate INT(10) UNSIGNED DEFAULT NULL,            
          pnotesresprate INT(10) UNSIGNED DEFAULT NULL,             
          pnotesweight INT(10) UNSIGNED DEFAULT NULL,               
          pnotesheight INT(10) UNSIGNED DEFAULT NULL,               
          pnotesgeneral BLOB,                                       
          pnotesbmi INT(10) UNSIGNED DEFAULT NULL,                         
          pnotescc BLOB,                                            
          pnoteshpi BLOB,                                           
          pnotesrosgenralstatus BINARY(1) DEFAULT 0,              
	      pnotesrosgenral VARCHAR(250) DEFAULT NULL,  
	      pnotesrosheadstatus BINARY(1) DEFAULT 0,              
      	  pnotesroshead VARCHAR(250) DEFAULT NULL,                      
	      pnotesroseyesstatus BINARY(1) DEFAULT 0,                
	      pnotesroseyes VARCHAR(250) DEFAULT NULL, 
	      pnotesroseyescmnts BLOB,                   
	      pnotesrosentstatus BINARY(1) DEFAULT 0,                 
	      pnotesrosent VARCHAR(250) DEFAULT NULL, 
	      pnotesrosentcmnts BLOB,                   
	      pnotesroscvstatus BINARY(1) DEFAULT 0,                  
	      pnotesroscv VARCHAR(250) DEFAULT NULL,   
	      pnotesroscvsmnts BLOB,                 
	      pnotesrosrespstatus BINARY(1) DEFAULT 0,                
	      pnotesrosresp VARCHAR(250) DEFAULT NULL, 
	      pnotesrosrespcmnts BLOB,                 
	      pnotesrosgistatus BINARY(1) DEFAULT 0,                  
	      pnotesroshgi VARCHAR(250) DEFAULT NULL, 
	      pnotesrosgicmnts BLOB,                    
	      pnotesrosgustatus BINARY(1) DEFAULT 0,                  
	      pnotesrosgu VARCHAR(250) DEFAULT NULL,  
	      pnotesrosgucmnts BLOB,                   
	      pnotesrosmusclestatus BINARY(1) DEFAULT 0,              
	      pnotesrosmuscles VARCHAR(250) DEFAULT NULL,    
	      pnotesrosmusclescmnts BLOB,            
	      pnotesrosskinstatus BINARY(1) DEFAULT 0,                
	      pnotesrosskin VARCHAR(250) DEFAULT NULL, 
	      pnotesrosskincmnts BLOB,                  
	      pnotesrospsychstatus BINARY(1) DEFAULT 0,               
	      pnotesrospsych VARCHAR(250) DEFAULT NULL,    
	      pnotesrospsychcmnts BLOB,              
	      pnotesrosendostatus BINARY(1) DEFAULT 0,                
	      pnotesrosendo VARCHAR(250) DEFAULT NULL,    
	      pnotesrosendocmnts BLOB,              
	      pnotesroshemlympstatus BINARY(1) DEFAULT 0,             
	      pnotesroshemlymp VARCHAR(250) DEFAULT NULL, 
	      pnotesroshemlympcmnts BLOB,              
	      pnotesrosneurostatus BINARY(1) DEFAULT 0,               
	      pnotesrosneuro VARCHAR(250) DEFAULT NULL,    
	      pnotesrosneurocmnts BLOB,              
	      pnotesrosimmallrgstatus BINARY(1) DEFAULT 0,            
	      pnotesrosimmallrg VARCHAR(250) DEFAULT NULL,                                
          pnotesph BLOB,                                            
          pnotesfh BLOB,                                            
          pnotesshalcoholstatus BINARY(1) DEFAULT 0,                     
	      pnotesshalcoholcmnt VARCHAR(250) DEFAULT NULL,                   
	      pnotesshtobaccostatus BINARY(1) DEFAULT 0,                     
	      pnotesshtobaccocmnt VARCHAR(250) DEFAULT NULL,                   
	      pnotesshtcounseled BINARY(1) DEFAULT 0,                        
	      pnotesshilctdrugstatus BINARY(1) DEFAULT 0,                    
	      pnotesshilctdrugscmnt VARCHAR(250) DEFAULT NULL,                 
	      pnotesshliveswithstatus BINARY(1) DEFAULT 0,                   
	      pnotesshliveswithcmnt VARCHAR(250) DEFAULT NULL,                 
	      pnotesshoccupation VARCHAR(250) DEFAULT NULL,                    
	      pnotesshivrskfacstatus BINARY(1) DEFAULT 0,                    
	      pnotesshivrskfaccmnt VARCHAR(250) DEFAULT NULL,                  
	      pnotesshtravelstatus BINARY(1) DEFAULT 0,                      
	      pnotesshtravelcmnt VARCHAR(250) DEFAULT NULL,                    
	      pnotesshpetsstatus BINARY(1) DEFAULT 0,                        
	      pnotesshpetscmnt VARCHAR(250) DEFAULT NULL,                      
	      pnotesshhobbiesstatus BINARY(1) DEFAULT 0,                     
	      pnotesshhobbiescmnt VARCHAR(250) DEFAULT NULL,                   
	      pnotesshhousing VARCHAR(250) DEFAULT NULL,   
	      pnotespeheadfreecmnt BLOB,            
          pnotespeeyeclpistatus INT(10) UNSIGNED DEFAULT 0,              
	      pnotespeeyeclpicmnt BLOB,                                        
	      pnotespeeyedesstatus INT(10) UNSIGNED DEFAULT 0,               
	      pnotespeeyedescmnt BLOB,                                         
	      pnotespeeyevpsstatus INT(10) UNSIGNED DEFAULT 0,               
	      pnotespeeyevpscmnt BLOB,                                         
	      pnotespeeyeavnstatus INT(10) UNSIGNED DEFAULT 0,               
	      pnotespeeyeavncmnt BLOB,                                         
	      pnotespeeyehemstatus INT(10) UNSIGNED DEFAULT 0,               
	      pnotespeeyehemcmnt BLOB,                                         
	      pnotespeeyeexustatus INT(10) UNSIGNED DEFAULT 0,               
	      pnotespeeyeexucmnt BLOB,                                         
	      pnotespeeyecupdiscratio VARCHAR(50) DEFAULT NULL,  
	      pnotespeeyefreecmnt BLOB,                
	      pnotespeentectstatus INT(10) UNSIGNED DEFAULT 0,               
	      pnotespeentectcmnt BLOB,                                         
	      pnotespeentnmsstatus INT(10) UNSIGNED DEFAULT 0,               
	      pnotespeentnmscmnt BLOB,                                         
	      pnotespeentlgtstatus INT(10) UNSIGNED DEFAULT 0,               
	      pnotespeentlgtcmnt BLOB,                                         
	      pnotespeentomsgstatus INT(10) UNSIGNED DEFAULT 0,              
	      pnotespeentomsgcmnt BLOB,                                        
	      pnotespeenthttpstatus INT(10) UNSIGNED DEFAULT 0,              
	      pnotespeenthttpcmnt BLOB,                                        
	      pnotespeentthyrostatus INT(10) UNSIGNED DEFAULT 0,             
	      pnotespeentthyrocmnt BLOB,  
	      pnotespeentfreecmnt BLOB,                                      
	      pnotespeneckbrjvdstatus INT(10) UNSIGNED DEFAULT 0,            
	      pnotespeneckbrjvdcmnt BLOB,     
	      pnotespeneckfreecmnt BLOB,                                 
	      pnotespebrstddmstatus INT(10) UNSIGNED DEFAULT 0,              
	      pnotespebrstddmcmnt BLOB,
	      pnotespebrstfreecmnt BLOB,                                         
	      pnotesperespeffstatus INT(10) UNSIGNED DEFAULT 0,              
	      pnotesperespeffcmnt BLOB,                                        
	      pnotesperesplungstatus INT(10) UNSIGNED DEFAULT 0,             
	      pnotesperesplungcmnt BLOB,        
	      pnotesperespfreecmnt BLOB,                                
	      pnotespecvregrhystatus INT(10) UNSIGNED DEFAULT 0,             
	      pnotespecvregrhycmnt BLOB,                                       
	      pnotespecvs1consstatus INT(10) UNSIGNED DEFAULT 0,             
	      pnotespecvs1conscmnt BLOB,                                       
	      pnotespecvs2physplstatus INT(10) UNSIGNED DEFAULT 0,           
	      pnotespecvs2physplcmnt BLOB,                                     
	      pnotespecvmurstatus INT(10) UNSIGNED DEFAULT 0,                
	      pnotespecvmurcmnt BLOB,                                          
	      pnotespecvpalhrtstatus INT(10) UNSIGNED DEFAULT 0,             
	      pnotespecvpalhrtcmnt BLOB,                                       
	      pnotespecvabdaorstatus INT(10) UNSIGNED DEFAULT 0,             
	      pnotespecvabdaorcmnt BLOB,                                       
	      pnotespecvfemartstatus INT(10) UNSIGNED DEFAULT 0,             
	      pnotespecvfemartcmnt BLOB,                                       
	      pnotespecvpedpulstatus INT(10) UNSIGNED DEFAULT 0,             
	      pnotespecvpadpulcmnt BLOB,       
	      pnotespecvfreecmnt BLOB,                                 
	      pnotespegiscarsstatus INT(10) UNSIGNED DEFAULT 0,              
	      pnotespegiscarscmnt BLOB,                                        
	      pnotespegibruitstatus INT(10) UNSIGNED DEFAULT 0,              
	      pnotespegibruitcmnt BLOB,                                        
	      pnotespegimassstatus INT(10) UNSIGNED DEFAULT 0,               
	      pnotespegimasscmnt BLOB,                                         
	      pnotespegitendstatus INT(10) UNSIGNED DEFAULT 0,               
	      pnotespegitendcmnt BLOB,                                         
	      pnotespegiheptstatus INT(10) UNSIGNED DEFAULT 0,               
	      pnotespegiheptcmnt BLOB,                                         
	      pnotespegisplenstatus INT(10) UNSIGNED DEFAULT 0,              
	      pnotespegisplencmnt BLOB,                                        
	      pnotespegiaprsstatus INT(10) UNSIGNED DEFAULT 0,               
	      pnotespegiaprscmnt BLOB,                                         
	      pnotespegibowsndstatus INT(10) UNSIGNED DEFAULT 0,             
	      pnotespegibowsndcmnt BLOB,                                       
	      pnotespegistoolstatus INT(10) UNSIGNED DEFAULT 0,              
	      pnotespegistoolcmnt BLOB,   
	      pnotespegifreecmnt BLOB,                                      
	      pnotespegugender enum('Male','Female') DEFAULT NULL,             
	      pnotespegupenisstatus INT(10) UNSIGNED DEFAULT 0,              
	      pnotespegupeniscmnt BLOB,                                        
	      pnotespegutestesstatus INT(10) UNSIGNED DEFAULT 0,             
	      pnotespegutestescmnt BLOB,                                       
	      pnotespeguproststatus INT(10) UNSIGNED DEFAULT 0,              
	      pnotespeguprostcmnt BLOB,                                        
	      pnotespeguextgenstatus INT(10) UNSIGNED DEFAULT 0,             
	      pnotespeguextgencmnt BLOB,                                       
	      pnotespegucervixstatus INT(10) UNSIGNED DEFAULT 0,             
	      pnotespegucervixcmnt BLOB,                                       
	      pnotespeguutadnstatus INT(10) UNSIGNED DEFAULT 0,              
	      pnotespeguutadncmnt BLOB,    
	      pnotespegufreecmnt BLOB,                                     
	      pnotespelympnodesstatus INT(10) UNSIGNED DEFAULT 0,            
	      pnotespelympnodescmnt BLOB, 
	      pnotespelympfreecmnt BLOB,                                      
	      pnotespeskintissuestatus INT(10) UNSIGNED DEFAULT 0,           
	      pnotespeskintissuecmnt BLOB,  
	      pnotespeskinfreecmnt BLOB,                                   
	      pnotespemsgaitststatus INT(10) UNSIGNED DEFAULT 0,             
	      pnotespemsgaitstcmnt BLOB,                                       
	      pnotespemsdignlsstatus INT(10) UNSIGNED DEFAULT 0,             
	      pnotespemsdignlscmnt BLOB,                                       
	      pnotespemsromstbstatus INT(10) UNSIGNED DEFAULT 0,             
	      pnotespemsromstbcmnt BLOB,                                       
	      pnotespemsjntbnsmusstatus INT(10) UNSIGNED DEFAULT 0,          
	      pnotespemsjntbnsmuscmnt BLOB,                                    
	      pnotespemsmusstrtnstatus INT(10) UNSIGNED DEFAULT 0,           
	      pnotespemsmusstrtncmnt BLOB,  
	      pnotespemsfreecmnt BLOB,                                   
	      pnotespeneurocrnervstatus INT(10) UNSIGNED DEFAULT 0,          
	      pnotespeneurocrnervcmnt BLOB,                                    
	      pnotespeneurodtrsstatus INT(10) UNSIGNED DEFAULT 0,            
	      pnotespeneurodtrscmnt BLOB,                                      
	      pnotespeneuromotorstatus INT(10) UNSIGNED DEFAULT 0,           
	      pnotespeneuromotorcmnt BLOB,                                     
	      pnotespeneurosnststatus INT(10) UNSIGNED DEFAULT 0,            
	      pnotespeneurosnstcmnt BLOB,  
	      pnotespeneurofreecmnt BLOB,                                     
	      pnotespepsychjudinsstatus INT(10) UNSIGNED DEFAULT 0,          
	      pnotespepsychjudinscmnt BLOB,                                    
	      pnotespepsychmoodeffstatus INT(10) UNSIGNED DEFAULT 0,         
	      pnotespepsychmoodeffcmnt BLOB,                                   
	      pnotespepsychorntppstatus INT(10) UNSIGNED DEFAULT 0,          
	      pnotespepsychorntppcmnt BLOB,                                    
	      pnotespepsychmemorystatus INT(10) UNSIGNED DEFAULT 0,          
	      pnotespepsychmemorycmnt BLOB,       
	      pnotespepsychfreecmnt BLOB,                                              
          pnotesbillable BLOB,                                      
          pnoteshandp BLOB,
          iso VARCHAR(15) DEFAULT NULL,                             
          locked INT(10) UNSIGNED DEFAULT NULL,                         
	  	  id			SERIAL,
	
	  #	Define keys
	  KEY			( pnotespat),
	  FOREIGN KEY		( pnotespat ) REFERENCES patient.id ON DELETE CASCADE
);

DROP PROCEDURE IF EXISTS enotes_Upgrade;
DELIMITER //
CREATE PROCEDURE enotes_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotesuser INT(10) UNSIGNED DEFAULT 0 AFTER pnotestemplate;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotespeheadfreecmnt BLOB AFTER pnotespeeyeclpistatus;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotespeeyefreecmnt BLOB AFTER pnotespeeyecupdiscratio;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotespeentfreecmnt  BLOB AFTER pnotespeentthyrocmnt;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotespeneckfreecmnt BLOB AFTER pnotespeneckbrjvdcmnt;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotespebrstfreecmnt BLOB AFTER pnotespebrstddmcmnt;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotesperespfreecmnt BLOB AFTER pnotesperesplungcmnt;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotespecvfreecmnt BLOB AFTER pnotespecvpadpulcmnt;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotespegifreecmnt BLOB AFTER pnotespegistoolcmnt;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotespegufreecmnt BLOB AFTER pnotespeguutadncmnt;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotespelympfreecmnt BLOB AFTER pnotespelympnodescmnt;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotespeskinfreecmnt BLOB AFTER pnotespeskintissuecmnt;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotespemsfreecmnt BLOB AFTER pnotespemsmusstrtncmnt;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotespeneurofreecmnt BLOB AFTER pnotespeneurosnstcmnt;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotespepsychfreecmnt BLOB AFTER pnotespepsychmemorycmnt;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotesrosheadstatus BINARY(1) DEFAULT 0 AFTER pnotesrosgenral;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotesroshead  VARCHAR(250) DEFAULT NULL AFTER pnotesrosheadstatus;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotesroseyescmnts  BLOB AFTER pnotesroseyes;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotesrosentcmnts  BLOB AFTER pnotesrosent;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotesroscvsmnts  BLOB AFTER pnotesroscv;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotesrosrespcmnts  BLOB AFTER pnotesrosresp;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotesrosgicmnts  BLOB AFTER pnotesroshgi;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotesrosgucmnts  BLOB AFTER pnotesrosgu;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotesrosmusclescmnts  BLOB AFTER pnotesrosmuscles;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotesrosskincmnts  BLOB AFTER pnotesrosskin;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotesrospsychcmnts  BLOB AFTER pnotesrospsych;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotesrosendocmnts  BLOB AFTER pnotesrosendo;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotesroshemlympcmnts  BLOB AFTER pnotesroshemlymp;
	ALTER IGNORE TABLE enotes ADD COLUMN pnotesrosneurocmnts  BLOB AFTER pnotesrosneuro;
	#----- Upgrades

END
//
DELIMITER ;
CALL enotes_Upgrade( );

CALL config_Register (
	'max_billable',
	'4',
	'Maximum Number of Billable Fields',
	'Encounter/Progress Notes',
	'Number',
	''
);

