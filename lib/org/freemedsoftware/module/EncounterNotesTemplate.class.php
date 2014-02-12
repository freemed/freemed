<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class EncounterNotesTemplate extends SupportModule {

	var $MODULE_NAME = "Encounter Notes Template";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "709a8d12-3799-46a7-8648-567b904349f2";
	

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';
	var $widget_hash = "##pnotestname##";
	var $table_name = "entemplate";	
	
	var $variables = array (	
			  'pnotestuser',
			  'pnotesttype',
		      'pnotestproccode',
		      'pnotestdiag1',
		      'pnotestdiag2',
		      'pnotestdiag3',
		      'pnotestdiag4',
		      'pnotestmod1',
		      'pnotestmod2',  
		      'pnotestmod3',
		      'pnotestpos',  
		      'pnotestprocunits',
		      'pnotest_S',
		      'pnotest_O',
		      'pnotest_A',
		      'pnotest_P',
		      'pnotest_I',
		      'pnotest_E',
		      'pnotest_R',                                      
		      'pnotestsbp',            
		      'pnotestdbp',            
		      'pnotesttemp',                     
		      'pnotestheartrate',      
		      'pnotestresprate',       
		      'pnotestweight',         
		      'pnotestheight',         
		      'pnotestgeneral',                                 
		      'pnotestbmi',
		      'pnotestcc',
		      'pnotesthpi',                                     
		      'pnotestrosgenralstatus',              
	          'pnotestrosgenral',       
	          'pnotestrosheadstatus',
	          'pnotestroshead',         
	          'pnotestroseyesstatus',                
	          'pnotestroseyes',
	          'pnotestroseyescmnts',
	          'pnotestrosentstatus',                 
	          'pnotestrosent',
	          'pnotestrosentcmnts',                   
	          'pnotestroscvstatus',                  
	          'pnotestroscv', 
	          'pnotestroscvsmnts',                     
	          'pnotestrosrespstatus',                
	          'pnotestrosresp',
	          'pnotestrosrespcmnts',                    
	          'pnotestrosgistatus',                  
	          'pnotestroshgi',
	          'pnotestrosgicmnts',                    
	          'pnotestrosgustatus',                  
	          'pnotestrosgu',
	          'pnotestrosgucmnts',                     
	          'pnotestrosmusclestatus',              
	          'pnotestrosmuscles',
	          'pnotestrosmusclescmnts',                
	          'pnotestrosskinstatus',                
	          'pnotestrosskin',
	          'pnotestrosskincmnts',                  
	          'pnotestrospsychstatus',               
	          'pnotestrospsych',
	          'pnotestrospsychcmnts',                  
	          'pnotestrosendostatus',                
	          'pnotestrosendo', 
	          'pnotestrosendocmnts',                 
	          'pnotestroshemlympstatus',             
	          'pnotestroshemlymp',
	          'pnotestroshemlympcmnts',                
	          'pnotestrosneurostatus',               
	          'pnotestrosneuro',
	          'pnotestrosneurocmnts',                  
	          'pnotestrosimmallrgstatus',            
	          'pnotestrosimmallrg',                         
		      'pnotestph',                                      
		      'pnotestfh',                                      
		      'pnotestshalcoholstatus',              
	          'pnotestshalcoholcmnt',            
	          'pnotestshtobaccostatus',              
	          'pnotestshtobaccocmnt',            
	          'pnotestshtcounseled',                 
	          'pnotestshilctdrugstatus',             
	          'pnotestshilctdrugscmnt',          
	          'pnotestshliveswithstatus',            
	          'pnotestshliveswithcmnt',          
	          'pnotestshoccupation',             
	          'pnotestshivrskfacstatus',             
	          'pnotestshivrskfaccmnt',           
	          'pnotestshtravelstatus',               
	          'pnotestshtravelcmnt',             
	          'pnotestshpetsstatus',                 
	          'pnotestshpetscmnt',               
	          'pnotestshhobbiesstatus',              
	          'pnotestshhobbiescmnt',            
	          'pnotestshhousing',  
	          'pnotestpeheadfreecmnt',
		      'pnotestpeeyeclpistatus',       
	          'pnotestpeeyeclpicmnt',                                 
	          'pnotestpeeyedesstatus',        
	          'pnotestpeeyedescmnt',                                  
	          'pnotestpeeyevpsstatus',        
	          'pnotestpeeyevpscmnt',                                  
	          'pnotestpeeyeavnstatus',        
	          'pnotestpeeyeavncmnt',                                  
	          'pnotestpeeyehemstatus',        
	          'pnotestpeeyehemcmnt',                                  
	          'pnotestpeeyeexustatus',        
	          'pnotestpeeyeexucmnt',                                  
	          'pnotestpeeyecupdiscratio', 
	          'pnotestpeeyefreecmnt',        
	          'pnotestpeentectstatus',        
	          'pnotestpeentectcmnt',                                  
	          'pnotestpeentnmsstatus',        
	          'pnotestpeentnmscmnt',                                  
	          'pnotestpeentlgtstatus',        
	          'pnotestpeentlgtcmnt',                                  
	          'pnotestpeentomsgstatus',       
	          'pnotestpeentomsgcmnt',                                 
	          'pnotestpeenthttpstatus',       
	          'pnotestpeenthttpcmnt',                                 
	          'pnotestpeentthyrostatus',      
	          'pnotestpeentthyrocmnt', 
	          'pnotestpeentfreecmnt',                                
	          'pnotestpeneckbrjvdstatus',     
	          'pnotestpeneckbrjvdcmnt',  
	          'pnotestpeneckfreecmnt',                              
	          'pnotestpebrstddmstatus',       
	          'pnotestpebrstddmcmnt', 
	          'pnotestpebrstfreecmnt',                                 
	          'pnotestperespeffstatus',       
	          'pnotestperespeffcmnt',                                 
	          'pnotestperesplungstatus',      
	          'pnotestperesplungcmnt', 
	          'pnotestperespfreecmnt',                                
	          'pnotestpecvregrhystatus',      
	          'pnotestpecvregrhycmnt',                                
	          'pnotestpecvs1consstatus',      
	          'pnotestpecvs1conscmnt',                                
	          'pnotestpecvs2physplstatus',    
	          'pnotestpecvs2physplcmnt',                              
	          'pnotestpecvmurstatus',         
	          'pnotestpecvmurcmnt',                                   
	          'pnotestpecvpalhrtstatus',      
	          'pnotestpecvpalhrtcmnt',                                
	          'pnotestpecvabdaorstatus',      
	          'pnotestpecvabdaorcmnt',                                
	          'pnotestpecvfemartstatus',      
	          'pnotestpecvfemartcmnt',                                
	          'pnotestpecvpedpulstatus',      
	          'pnotestpecvpadpulcmnt',  
	          'pnotestpecvfreecmnt',                               
	          'pnotestpegiscarsstatus',       
	          'pnotestpegiscarscmnt',                                 
	          'pnotestpegibruitstatus',       
	          'pnotestpegibruitcmnt',                                 
	          'pnotestpegimassstatus',        
	          'pnotestpegimasscmnt',                                  
	          'pnotestpegitendstatus',        
	          'pnotestpegitendcmnt',                                  
	          'pnotestpegiheptstatus',        
	          'pnotestpegiheptcmnt',                                  
	          'pnotestpegisplenstatus',       
	          'pnotestpegisplencmnt',                                 
	          'pnotestpegiaprsstatus',        
	          'pnotestpegiaprscmnt',                                  
	          'pnotestpegibowsndstatus',      
	          'pnotestpegibowsndcmnt',                                
	          'pnotestpegistoolstatus',       
	          'pnotestpegistoolcmnt',
	          'pnotestpegifreecmnt',                                  
	          'pnotestpegugender',      
	          'pnotestpegupenisstatus',       
	          'pnotestpegupeniscmnt',                                 
	          'pnotestpegutestesstatus',      
	          'pnotestpegutestescmnt',                                
	          'pnotestpeguproststatus',       
	          'pnotestpeguprostcmnt',                                 
	          'pnotestpeguextgenstatus',      
	          'pnotestpeguextgencmnt',                                
	          'pnotestpegucervixstatus',      
	          'pnotestpegucervixcmnt',                                
	          'pnotestpeguutadnstatus',       
	          'pnotestpeguutadncmnt',
	          'pnotestpegufreecmnt',                                  
	          'pnotestpelympnodesstatus',     
	          'pnotestpelympnodescmnt',  
	          'pnotestpelympfreecmnt',                              
	          'pnotestpeskintissuestatus',    
	          'pnotestpeskintissuecmnt',  
	          'pnotestpeskinfreecmnt',                             
	          'pnotestpemsgaitststatus',      
	          'pnotestpemsgaitstcmnt',                                
	          'pnotestpemsdignlsstatus',      
	          'pnotestpemsdignlscmnt',                                
	          'pnotestpemsromstbstatus',      
	          'pnotestpemsromstbcmnt',                                
	          'pnotestpemsjntbnsmusstatus',   
	          'pnotestpemsjntbnsmuscmnt',                             
	          'pnotestpemsmusstrtnstatus',    
	          'pnotestpemsmusstrtncmnt',
	          'pnotestpemsfreecmnt',                               
	          'pnotestpeneurocrnervstatus',   
	          'pnotestpeneurocrnervcmnt',                             
	          'pnotestpeneurodtrsstatus',     
	          'pnotestpeneurodtrscmnt',                               
	          'pnotestpeneuromotorstatus',    
	          'pnotestpeneuromotorcmnt',                              
	          'pnotestpeneurosnststatus',     
	          'pnotestpeneurosnstcmnt', 
	          'pnotestpeneurofreecmnt',                               
	          'pnotestpepsychjudinsstatus',   
	          'pnotestpepsychjudinscmnt',                             
	          'pnotestpepsychmoodeffstatus',  
	          'pnotestpepsychmoodeffcmnt',                            
	          'pnotestpepsychorntppstatus',   
	          'pnotestpepsychorntppcmnt',                             
	          'pnotestpepsychmemorystatus',   
	          'pnotestpepsychmemorycmnt', 
	          'pnotestpepsychfreecmnt',  
		      'pnotestbillable',                                      
              'pnotesthandp',                                         
              'pnotestsections',                                      
              'pnotestfields',                                        
              'pnotestname'     
	);
	
	protected function add_pre ( $data ) {
		$data['pnotestuser'] = freemed::user_cache()->user_number;
	} // end method add_pre
	
	protected function mod_pre ( $data ) {
		$data['pnotestuser'] = freemed::user_cache()->user_number;
	} // end method add_pre
	
	public function getTemplates($type=NULL){
		if($type==NULL)
			$query="SELECT id,pnotestname AS tempname, pnotestuser AS tempuser,pnotesttype AS notetype FROM entemplate";
		else
			$query="SELECT id,pnotestname AS tempname, pnotestuser AS tempuser,pnotesttype AS notetype FROM entemplate WHERE pnotesttype='".$type."'";
		return  $GLOBALS['sql']->queryAll ( $query );
	}
	public function getTemplateInfo($tid){
		$query="SELECT * FROM entemplate WHERE id=".$GLOBALS['sql']->quote($tid);
		return  $GLOBALS['sql']->queryRow ( $query );
	}
} 
register_module("EncounterNotesTemplate");

?>
