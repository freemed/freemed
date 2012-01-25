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

CREATE TABLE IF NOT EXISTS `entemplate` (                              
      pnotestproccode INT(10) UNSIGNED DEFAULT 0,                 
      pnotestdiag1 INT(10) UNSIGNED DEFAULT 0,                    
      pnotestdiag2 INT(10) UNSIGNED DEFAULT 0,                    
      pnotestdiag3 INT(10) UNSIGNED DEFAULT 0,                    
      pnotestdiag4 INT(10) UNSIGNED DEFAULT 0,                    
      pnotestmod1 INT(10) UNSIGNED DEFAULT 0,                     
      pnotestmod2 INT(10) UNSIGNED DEFAULT 0,                     
      pnotestmod3 INT(10) UNSIGNED DEFAULT 0,                     
      pnotestpos INT(10) UNSIGNED DEFAULT 0,     
      pnotestprocunits FLOAT DEFAULT 0,                               
      pnotest_S BLOB,                                      
      pnotest_O BLOB,                                      
      pnotest_A BLOB,                                      
      pnotest_P BLOB,                                      
      pnotest_I BLOB,                                      
      pnotest_E BLOB,                                      
      pnotest_R BLOB,                                      
      pnotestsbp INT(10) UNSIGNED DEFAULT NULL,            
      pnotestdbp INT(10) UNSIGNED DEFAULT NULL,            
      pnotesttemp double DEFAULT NULL,                     
      pnotestheartrate INT(10) UNSIGNED DEFAULT NULL,      
      pnotestresprate INT(10) UNSIGNED DEFAULT NULL,       
      pnotestweight INT(10) UNSIGNED DEFAULT NULL,         
      pnotestheight INT(10) UNSIGNED DEFAULT NULL,         
      pnotestgeneral BLOB,                                 
      pnotestbmi INT(10) UNSIGNED DEFAULT NULL,            
      pnotestcc BLOB,                                      
      pnotesthpi BLOB,                                     
      pnotestrosgenralstatus BINARY(1) DEFAULT 0,              
      pnotestrosgenral VARCHAR(250) DEFAULT NULL,     
      pnotestrosheadstatus BINARY(1) DEFAULT 0,              
      pnotestroshead VARCHAR(250) DEFAULT NULL,                
      pnotestroseyesstatus BINARY(1) DEFAULT 0,                
      pnotestroseyes VARCHAR(250) DEFAULT NULL,   
      pnotestroseyescmnts BLOB,                  
      pnotestrosentstatus BINARY(1) DEFAULT 0,                 
      pnotestrosent VARCHAR(250) DEFAULT NULL,
      pnotestrosentcmnts BLOB,                   
      pnotestroscvstatus BINARY(1) DEFAULT 0,                  
      pnotestroscv VARCHAR(250) DEFAULT NULL,
      pnotestroscvsmnts BLOB,                    
      pnotestrosrespstatus BINARY(1) DEFAULT 0,                
      pnotestrosresp VARCHAR(250) DEFAULT NULL, 
      pnotestrosrespcmnts BLOB,                 
      pnotestrosgistatus BINARY(1) DEFAULT 0,                  
      pnotestroshgi VARCHAR(250) DEFAULT NULL,
      pnotestrosgicmnts BLOB,                   
      pnotestrosgustatus BINARY(1) DEFAULT 0,                  
      pnotestrosgu VARCHAR(250) DEFAULT NULL,
      pnotestrosgucmnts BLOB,                    
      pnotestrosmusclestatus BINARY(1) DEFAULT 0,              
      pnotestrosmuscles VARCHAR(250) DEFAULT NULL, 
      pnotestrosmusclescmnts BLOB,              
      pnotestrosskinstatus BINARY(1) DEFAULT 0,                
      pnotestrosskin VARCHAR(250) DEFAULT NULL, 
      pnotestrosskincmnts BLOB,                 
      pnotestrospsychstatus BINARY(1) DEFAULT 0,               
      pnotestrospsych VARCHAR(250) DEFAULT NULL,
      pnotestrospsychcmnts BLOB,                 
      pnotestrosendostatus BINARY(1) DEFAULT 0,                
      pnotestrosendo VARCHAR(250) DEFAULT NULL,
      pnotestrosendocmnts BLOB,                  
      pnotestroshemlympstatus BINARY(1) DEFAULT 0,             
      pnotestroshemlymp VARCHAR(250) DEFAULT NULL,  
      pnotestroshemlympcmnts BLOB,             
      pnotestrosneurostatus BINARY(1) DEFAULT 0,               
      pnotestrosneuro VARCHAR(250) DEFAULT NULL,  
      pnotestrosneurocmnts BLOB,                
      pnotestrosimmallrgstatus BINARY(1) DEFAULT 0,            
      pnotestrosimmallrg VARCHAR(250) DEFAULT NULL,                              
      pnotestph BLOB,                                      
      pnotestfh BLOB,                                      
      pnotestshalcoholstatus BINARY(1) DEFAULT 0,                     
      pnotestshalcoholcmnt VARCHAR(250) DEFAULT NULL,                   
      pnotestshtobaccostatus BINARY(1) DEFAULT 0,                     
      pnotestshtobaccocmnt VARCHAR(250) DEFAULT NULL,                   
      pnotestshtcounseled BINARY(1) DEFAULT 0,                        
      pnotestshilctdrugstatus BINARY(1) DEFAULT 0,                    
      pnotestshilctdrugscmnt VARCHAR(250) DEFAULT NULL,                 
      pnotestshliveswithstatus BINARY(1) DEFAULT 0,                   
      pnotestshliveswithcmnt VARCHAR(250) DEFAULT NULL,                 
      pnotestshoccupation VARCHAR(250) DEFAULT NULL,                    
      pnotestshivrskfacstatus BINARY(1) DEFAULT 0,                    
      pnotestshivrskfaccmnt VARCHAR(250) DEFAULT NULL,                  
      pnotestshtravelstatus BINARY(1) DEFAULT 0,                      
      pnotestshtravelcmnt VARCHAR(250) DEFAULT NULL,                    
      pnotestshpetsstatus BINARY(1) DEFAULT 0,                        
      pnotestshpetscmnt VARCHAR(250) DEFAULT NULL,                      
      pnotestshhobbiesstatus BINARY(1) DEFAULT 0,                     
      pnotestshhobbiescmnt VARCHAR(250) DEFAULT NULL,                   
      pnotestshhousing VARCHAR(250) DEFAULT NULL,    
      pnotestpeheadfreecmnt BLOB,                                    
      pnotestpeeyeclpistatus INT(10) UNSIGNED DEFAULT 0,              
      pnotestpeeyeclpicmnt BLOB,                                        
      pnotestpeeyedesstatus INT(10) UNSIGNED DEFAULT 0,               
      pnotestpeeyedescmnt BLOB,                                         
      pnotestpeeyevpsstatus INT(10) UNSIGNED DEFAULT 0,               
      pnotestpeeyevpscmnt BLOB,                                         
      pnotestpeeyeavnstatus INT(10) UNSIGNED DEFAULT 0,               
      pnotestpeeyeavncmnt BLOB,                                         
      pnotestpeeyehemstatus INT(10) UNSIGNED DEFAULT 0,               
      pnotestpeeyehemcmnt BLOB,                                         
      pnotestpeeyeexustatus INT(10) UNSIGNED DEFAULT 0,               
      pnotestpeeyeexucmnt BLOB,                                         
      pnotestpeeyecupdiscratio VARCHAR(50) DEFAULT NULL,       
      pnotestpeeyefreecmnt BLOB,          
      pnotestpeentectstatus INT(10) UNSIGNED DEFAULT 0,               
      pnotestpeentectcmnt BLOB,                                         
      pnotestpeentnmsstatus INT(10) UNSIGNED DEFAULT 0,               
      pnotestpeentnmscmnt BLOB,                                         
      pnotestpeentlgtstatus INT(10) UNSIGNED DEFAULT 0,               
      pnotestpeentlgtcmnt BLOB,                                         
      pnotestpeentomsgstatus INT(10) UNSIGNED DEFAULT 0,              
      pnotestpeentomsgcmnt BLOB,                                        
      pnotestpeenthttpstatus INT(10) UNSIGNED DEFAULT 0,              
      pnotestpeenthttpcmnt BLOB,                                        
      pnotestpeentthyrostatus INT(10) UNSIGNED DEFAULT 0,             
      pnotestpeentthyrocmnt BLOB,  
      pnotestpeentfreecmnt BLOB,                                      
      pnotestpeneckbrjvdstatus INT(10) UNSIGNED DEFAULT 0,            
      pnotestpeneckbrjvdcmnt BLOB,  
      pnotestpeneckfreecmnt BLOB,                                    
      pnotestpebrstddmstatus INT(10) UNSIGNED DEFAULT 0,              
      pnotestpebrstddmcmnt BLOB,    
      pnotestpebrstfreecmnt BLOB,                                     
      pnotestperespeffstatus INT(10) UNSIGNED DEFAULT 0,              
      pnotestperespeffcmnt BLOB,                                        
      pnotestperesplungstatus INT(10) UNSIGNED DEFAULT 0,             
      pnotestperesplungcmnt BLOB, 
      pnotestperespfreecmnt BLOB,                                        
      pnotestpecvregrhystatus INT(10) UNSIGNED DEFAULT 0,             
      pnotestpecvregrhycmnt BLOB,                                       
      pnotestpecvs1consstatus INT(10) UNSIGNED DEFAULT 0,             
      pnotestpecvs1conscmnt BLOB,                                       
      pnotestpecvs2physplstatus INT(10) UNSIGNED DEFAULT 0,           
      pnotestpecvs2physplcmnt BLOB,                                     
      pnotestpecvmurstatus INT(10) UNSIGNED DEFAULT 0,                
      pnotestpecvmurcmnt BLOB,                                          
      pnotestpecvpalhrtstatus INT(10) UNSIGNED DEFAULT 0,             
      pnotestpecvpalhrtcmnt BLOB,                                       
      pnotestpecvabdaorstatus INT(10) UNSIGNED DEFAULT 0,             
      pnotestpecvabdaorcmnt BLOB,                                       
      pnotestpecvfemartstatus INT(10) UNSIGNED DEFAULT 0,             
      pnotestpecvfemartcmnt BLOB,                                       
      pnotestpecvpedpulstatus INT(10) UNSIGNED DEFAULT 0,             
      pnotestpecvpadpulcmnt BLOB,  
      pnotestpecvfreecmnt BLOB,                                     
      pnotestpegiscarsstatus INT(10) UNSIGNED DEFAULT 0,              
      pnotestpegiscarscmnt BLOB,                                        
      pnotestpegibruitstatus INT(10) UNSIGNED DEFAULT 0,              
      pnotestpegibruitcmnt BLOB,                                        
      pnotestpegimassstatus INT(10) UNSIGNED DEFAULT 0,               
      pnotestpegimasscmnt BLOB,                                         
      pnotestpegitendstatus INT(10) UNSIGNED DEFAULT 0,               
      pnotestpegitendcmnt BLOB,                                         
      pnotestpegiheptstatus INT(10) UNSIGNED DEFAULT 0,               
      pnotestpegiheptcmnt BLOB,                                         
      pnotestpegisplenstatus INT(10) UNSIGNED DEFAULT 0,              
      pnotestpegisplencmnt BLOB,                                        
      pnotestpegiaprsstatus INT(10) UNSIGNED DEFAULT 0,               
      pnotestpegiaprscmnt BLOB,                                         
      pnotestpegibowsndstatus INT(10) UNSIGNED DEFAULT 0,             
      pnotestpegibowsndcmnt BLOB,                                       
      pnotestpegistoolstatus INT(10) UNSIGNED DEFAULT 0,              
      pnotestpegistoolcmnt BLOB,    
      pnotestpegifreecmnt BLOB,                                     
      pnotestpegugender enum('Male','Female') DEFAULT NULL,             
      pnotestpegupenisstatus INT(10) UNSIGNED DEFAULT 0,              
      pnotestpegupeniscmnt BLOB,                                        
      pnotestpegutestesstatus INT(10) UNSIGNED DEFAULT 0,             
      pnotestpegutestescmnt BLOB,                                       
      pnotestpeguproststatus INT(10) UNSIGNED DEFAULT 0,              
      pnotestpeguprostcmnt BLOB,                                        
      pnotestpeguextgenstatus INT(10) UNSIGNED DEFAULT 0,             
      pnotestpeguextgencmnt BLOB,                                       
      pnotestpegucervixstatus INT(10) UNSIGNED DEFAULT 0,             
      pnotestpegucervixcmnt BLOB,                                       
      pnotestpeguutadnstatus INT(10) UNSIGNED DEFAULT 0,              
      pnotestpeguutadncmnt BLOB,  
      pnotestpegufreecmnt BLOB,                                       
      pnotestpelympnodesstatus INT(10) UNSIGNED DEFAULT 0,            
      pnotestpelympnodescmnt BLOB,    
      pnotestpelympfreecmnt BLOB,                                  
      pnotestpeskintissuestatus INT(10) UNSIGNED DEFAULT 0,           
      pnotestpeskintissuecmnt BLOB,    
      pnotestpeskinfreecmnt BLOB,                                 
      pnotestpemsgaitststatus INT(10) UNSIGNED DEFAULT 0,             
      pnotestpemsgaitstcmnt BLOB,                                       
      pnotestpemsdignlsstatus INT(10) UNSIGNED DEFAULT 0,             
      pnotestpemsdignlscmnt BLOB,                                       
      pnotestpemsromstbstatus INT(10) UNSIGNED DEFAULT 0,             
      pnotestpemsromstbcmnt BLOB,                                       
      pnotestpemsjntbnsmusstatus INT(10) UNSIGNED DEFAULT 0,          
      pnotestpemsjntbnsmuscmnt BLOB,                                    
      pnotestpemsmusstrtnstatus INT(10) UNSIGNED DEFAULT 0,           
      pnotestpemsmusstrtncmnt BLOB, 
      pnotestpemsfreecmnt BLOB,                                    
      pnotestpeneurocrnervstatus INT(10) UNSIGNED DEFAULT 0,          
      pnotestpeneurocrnervcmnt BLOB,                                    
      pnotestpeneurodtrsstatus INT(10) UNSIGNED DEFAULT 0,            
      pnotestpeneurodtrscmnt BLOB,                                      
      pnotestpeneuromotorstatus INT(10) UNSIGNED DEFAULT 0,           
      pnotestpeneuromotorcmnt BLOB,                                     
      pnotestpeneurosnststatus INT(10) UNSIGNED DEFAULT 0,            
      pnotestpeneurosnstcmnt BLOB,   
      pnotestpeneurofreecmnt BLOB,                                    
      pnotestpepsychjudinsstatus INT(10) UNSIGNED DEFAULT 0,          
      pnotestpepsychjudinscmnt BLOB,                                    
      pnotestpepsychmoodeffstatus INT(10) UNSIGNED DEFAULT 0,         
      pnotestpepsychmoodeffcmnt BLOB,                                   
      pnotestpepsychorntppstatus INT(10) UNSIGNED DEFAULT 0,          
      pnotestpepsychorntppcmnt BLOB,                                    
      pnotestpepsychmemorystatus INT(10) UNSIGNED DEFAULT 0,          
      pnotestpepsychmemorycmnt BLOB, 
      pnotestpepsychfreecmnt BLOB,                                    
	  pnotestbillable BLOB,                                  
      pnotesthandp BLOB,
      pnotestsections BLOB,
      pnotestfields BLOB,
      pnotestname VARCHAR(50),                                   
      iso VARCHAR(15) DEFAULT NULL,                        
      locked INT(10) UNSIGNED DEFAULT NULL,                
	  id			SERIAL
);

DROP PROCEDURE IF EXISTS entemplate_Upgrade;
DELIMITER //
CREATE PROCEDURE entemplate_Upgrade ( )
BEGIN
	DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestuser INT(10) UNSIGNED DEFAULT 0 AFTER pnotestname;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotesttype enum('Encounter Note','Progress Note') DEFAULT NULL AFTER pnotestuser;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestpeheadfreecmnt BLOB AFTER pnotestpeeyeclpistatus;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestpeeyefreecmnt BLOB AFTER pnotestpeeyecupdiscratio;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestpeentfreecmnt  BLOB AFTER pnotestpeentthyrocmnt;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestpeneckfreecmnt BLOB AFTER pnotestpeneckbrjvdcmnt;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestpebrstfreecmnt BLOB AFTER pnotestpebrstddmcmnt;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestperespfreecmnt BLOB AFTER pnotestperesplungcmnt;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestpecvfreecmnt BLOB AFTER pnotestpecvpadpulcmnt;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestpegifreecmnt BLOB AFTER pnotestpegistoolcmnt;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestpegufreecmnt BLOB AFTER pnotestpeguutadncmnt;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestpelympfreecmnt BLOB AFTER pnotestpelympnodescmnt;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestpeskinfreecmnt BLOB AFTER pnotestpeskintissuecmnt;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestpemsfreecmnt BLOB AFTER pnotestpemsmusstrtncmnt;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestpeneurofreecmnt BLOB AFTER pnotestpeneurosnstcmnt;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestpepyschfreecmnt BLOB AFTER pnotestpepsychmemorycmnt;	
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestrosheadstatus BINARY(1) DEFAULT 0 AFTER pnotestrosgenral;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestroshead  VARCHAR(250) DEFAULT NULL AFTER pnotestrosheadstatus;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestroseyescmnts  BLOB AFTER pnotestroseyes;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestrosentcmnts  BLOB AFTER pnotestrosent;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestroscvsmnts  BLOB AFTER pnotestroscv;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestrosrespcmnts  BLOB AFTER pnotestrosresp;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestrosgicmnts  BLOB AFTER pnotestroshgi;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestrosgucmnts  BLOB AFTER pnotestrosgu;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestrosmusclescmnts  BLOB AFTER pnotestrosmuscles;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestrosskincmnts  BLOB AFTER pnotestrosskin;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestrospsychcmnts  BLOB AFTER pnotestrospsych;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestrosendocmnts  BLOB AFTER pnotestrosendo;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestroshemlympcmnts  BLOB AFTER pnotestroshemlymp;
	ALTER IGNORE TABLE entemplate ADD COLUMN pnotestrosneurocmnts  BLOB AFTER pnotestrosneuro;
	#----- Upgrades
END
//
DELIMITER ;
CALL entemplate_Upgrade( );

