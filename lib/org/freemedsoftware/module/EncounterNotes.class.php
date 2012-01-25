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

LoadObjectDependency('org.freemedsoftware.core.EMRModule');

class EncounterNotes extends EMRModule {

	var $MODULE_NAME = "Encounter Notes";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "a3c06741-e167-49c0-96ba-67de43a7ae0b";
	

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $table_name = "enotes";
	var $widget_hash   = "##pnotesdt## ##pnotesdescrip##";
	var $acl_category = 'emr';
	
	var $variables = array (	
		  'pnotesdt',                               
	          'pnotesdtadd',                            
	          'pnotesdtmod',                            
	          'pnotespat',                  
	          'pnotesdescrip',      
	          'pnotestype',            
	          'pnotesdoc',                  
	          'pnoteseoc',                  
	          'pnotestemplate',   
	          'pnotesuser',
	          'pnotes_S',                                            
	          'pnotes_O',                                            
	          'pnotes_A',                                            
	          'pnotes_P',                                            
	          'pnotes_I',                                            
	          'pnotes_E',                                            
	          'pnotes_R',                                            
	          'pnotessbp',                  
	          'pnotesdbp',                  
	          'pnotestemp',                           
	          'pnotesheartrate',            
	          'pnotesresprate',             
	          'pnotesweight',               
	          'pnotesheight',               
	          'pnotesgeneral',                                       
	          'pnotesbmi',                          
	          'pnotescc',                                            
	          'pnoteshpi',                                           
	          'pnotesph',                                            
	          'pnotesrosgenralstatus',              
	          'pnotesrosgenral',      
	          'pnotesrosheadstatus',
	          'pnotesroshead',          
	          'pnotesroseyesstatus',                
	          'pnotesroseyes',  
	          'pnotesroseyescmnts',                
	          'pnotesrosentstatus',                 
	          'pnotesrosent',  
	          'pnotesrosentcmnts',                  
	          'pnotesroscvstatus',                  
	          'pnotesroscv', 
	          'pnotesroscvsmnts',                    
	          'pnotesrosrespstatus',                
	          'pnotesrosresp',
	          'pnotesrosrespcmnts',                  
	          'pnotesrosgistatus',                  
	          'pnotesroshgi',
	          'pnotesrosgicmnts',                    
	          'pnotesrosgustatus',                  
	          'pnotesrosgu',       
	          'pnotesrosgucmnts',              
	          'pnotesrosmusclestatus',              
	          'pnotesrosmuscles', 
	          'pnotesrosmusclescmnts',              
	          'pnotesrosskinstatus',                
	          'pnotesrosskin',
	          'pnotesrosskincmnts',                   
	          'pnotesrospsychstatus',               
	          'pnotesrospsych',
	          'pnotesrospsychcmnts',                 
	          'pnotesrosendostatus',                
	          'pnotesrosendo',
	          'pnotesrosendocmnts',                  
	          'pnotesroshemlympstatus',             
	          'pnotesroshemlymp',               
	          'pnotesroshemlympcmnts', 
	          'pnotesrosneurostatus',               
	          'pnotesrosneuro',
	          'pnotesrosneurocmnts',                 
	          'pnotesrosimmallrgstatus',            
	          'pnotesrosimmallrg',              
	          'pnotesfh',                                            
	          'pnotesshalcoholstatus',              
	          'pnotesshalcoholcmnt',            
	          'pnotesshtobaccostatus',              
	          'pnotesshtobaccocmnt',            
	          'pnotesshtcounseled',                 
	          'pnotesshilctdrugstatus',             
	          'pnotesshilctdrugscmnt',          
	          'pnotesshliveswithstatus',            
	          'pnotesshliveswithcmnt',          
	          'pnotesshoccupation',             
	          'pnotesshivrskfacstatus',             
	          'pnotesshivrskfaccmnt',           
	          'pnotesshtravelstatus',               
	          'pnotesshtravelcmnt',             
	          'pnotesshpetsstatus',                 
	          'pnotesshpetscmnt',               
	          'pnotesshhobbiesstatus',              
	          'pnotesshhobbiescmnt',            
	          'pnotesshhousing',    
	          'pnotespeheadfreecmnt',            
	          'pnotespeeyeclpistatus',       
	          'pnotespeeyeclpicmnt',                                 
	          'pnotespeeyedesstatus',        
	          'pnotespeeyedescmnt',                                  
	          'pnotespeeyevpsstatus',        
	          'pnotespeeyevpscmnt',                                  
	          'pnotespeeyeavnstatus',        
	          'pnotespeeyeavncmnt',                                  
	          'pnotespeeyehemstatus',        
	          'pnotespeeyehemcmnt',                                  
	          'pnotespeeyeexustatus',        
	          'pnotespeeyeexucmnt',                                  
	          'pnotespeeyecupdiscratio',    
	          'pnotespeeyefreecmnt',     
	          'pnotespeentectstatus',        
	          'pnotespeentectcmnt',                                  
	          'pnotespeentnmsstatus',        
	          'pnotespeentnmscmnt',                                  
	          'pnotespeentlgtstatus',        
	          'pnotespeentlgtcmnt',                                  
	          'pnotespeentomsgstatus',       
	          'pnotespeentomsgcmnt',                                 
	          'pnotespeenthttpstatus',       
	          'pnotespeenthttpcmnt',                                 
	          'pnotespeentthyrostatus',      
	          'pnotespeentthyrocmnt',
	          'pnotespeentfreecmnt',                                 
	          'pnotespeneckbrjvdstatus',     
	          'pnotespeneckbrjvdcmnt',
	          'pnotespeneckfreecmnt',                                
	          'pnotespebrstddmstatus',       
	          'pnotespebrstddmcmnt',   
	          'pnotespebrstfreecmnt',                               
	          'pnotesperespeffstatus',       
	          'pnotesperespeffcmnt',                                 
	          'pnotesperesplungstatus',      
	          'pnotesperesplungcmnt',   
	          'pnotesperespfreecmnt',                              
	          'pnotespecvregrhystatus',      
	          'pnotespecvregrhycmnt',                                
	          'pnotespecvs1consstatus',      
	          'pnotespecvs1conscmnt',                                
	          'pnotespecvs2physplstatus',    
	          'pnotespecvs2physplcmnt',                              
	          'pnotespecvmurstatus',         
	          'pnotespecvmurcmnt',                                   
	          'pnotespecvpalhrtstatus',      
	          'pnotespecvpalhrtcmnt',                                
	          'pnotespecvabdaorstatus',      
	          'pnotespecvabdaorcmnt',                                
	          'pnotespecvfemartstatus',      
	          'pnotespecvfemartcmnt',                                
	          'pnotespecvpedpulstatus',      
	          'pnotespecvpadpulcmnt', 
	          'pnotespecvfreecmnt',                                
	          'pnotespegiscarsstatus',       
	          'pnotespegiscarscmnt',                                 
	          'pnotespegibruitstatus',       
	          'pnotespegibruitcmnt',                                 
	          'pnotespegimassstatus',        
	          'pnotespegimasscmnt',                                  
	          'pnotespegitendstatus',        
	          'pnotespegitendcmnt',                                  
	          'pnotespegiheptstatus',        
	          'pnotespegiheptcmnt',                                  
	          'pnotespegisplenstatus',       
	          'pnotespegisplencmnt',                                 
	          'pnotespegiaprsstatus',        
	          'pnotespegiaprscmnt',                                  
	          'pnotespegibowsndstatus',      
	          'pnotespegibowsndcmnt',
	          'pnotespegifreecmnt',                                 
	          'pnotespegistoolstatus',       
	          'pnotespegistoolcmnt',                                 
	          'pnotespegugender',      
	          'pnotespegupenisstatus',       
	          'pnotespegupeniscmnt',                                 
	          'pnotespegutestesstatus',      
	          'pnotespegutestescmnt',                                
	          'pnotespeguproststatus',       
	          'pnotespeguprostcmnt',                                 
	          'pnotespeguextgenstatus',      
	          'pnotespeguextgencmnt',                                
	          'pnotespegucervixstatus',      
	          'pnotespegucervixcmnt',                                
	          'pnotespeguutadnstatus',       
	          'pnotespeguutadncmnt', 
	          'pnotespegufreecmnt',                                 
	          'pnotespelympnodesstatus',     
	          'pnotespelympnodescmnt', 
	          'pnotespelympfreecmnt',                               
	          'pnotespeskintissuestatus',    
	          'pnotespeskintissuecmnt', 
	          'pnotespeskinfreecmnt',                              
	          'pnotespemsgaitststatus',      
	          'pnotespemsgaitstcmnt',                                
	          'pnotespemsdignlsstatus',      
	          'pnotespemsdignlscmnt',                                
	          'pnotespemsromstbstatus',      
	          'pnotespemsromstbcmnt',                                
	          'pnotespemsjntbnsmusstatus',   
	          'pnotespemsjntbnsmuscmnt',                             
	          'pnotespemsmusstrtnstatus',    
	          'pnotespemsmusstrtncmnt',                              
	          'pnotespemsfreecmnt', 
	          'pnotespeneurocrnervstatus',   
	          'pnotespeneurocrnervcmnt',                             
	          'pnotespeneurodtrsstatus',     
	          'pnotespeneurodtrscmnt',                               
	          'pnotespeneuromotorstatus',    
	          'pnotespeneuromotorcmnt',                              
	          'pnotespeneurosnststatus',     
	          'pnotespeneurosnstcmnt', 
	          'pnotespeneurofreecmnt',                               
	          'pnotespepsychjudinsstatus',   
	          'pnotespepsychjudinscmnt',                             
	          'pnotespepsychmoodeffstatus',  
	          'pnotespepsychmoodeffcmnt',                            
	          'pnotespepsychorntppstatus',   
	          'pnotespepsychorntppcmnt',                             
	          'pnotespepsychmemorystatus',   
	          'pnotespepsychmemorycmnt',    
	          'pnotespepsychfreecmnt',                         
	          'pnotesbillable',                                      
	          'pnoteshandp'
	);
	
	protected function add_pre ( &$data ) {
		$data['pnotesuser'] = freemed::user_cache()->user_number;
	} // end method add_pre
	
	protected function mod_pre ( &$data ) {
		$data['pnotesuser'] = freemed::user_cache()->user_number;
	} // end method add_pre
	
	protected function add_post ( $id, &$data ) {
		if($data['pnotesbillable']!=''){
			$q = "SELECT id,covtype from coverage WHERE covpatient = ".$GLOBALS['sql']->quote( $data['pnotespat'] )." AND covstatus =1 ORDER BY covtype ASC LIMIT 1";
			$cov= $GLOBALS['sql']->queryRow( $q );
			//return $cov['id']+0;
			if (function_exists( 'json_decode' )) {
				$pnotesbillables = json_decode( $data['pnotesbillable']);
			} else {
				$json = CreateObject('net.php.pear.Services_JSON');
				$pnotesbillables = $json->decode($data['pnotesbillable'] );
			}
			
			foreach ($pnotesbillables AS $k => $v) {
				foreach ($v AS $key => $val){
					if($key=='proccode')
						$proccode=$val;
					else if($key=='diagcode')
						$diagcode=$val;
				}
				//return $proccode.":".$diagcode;
				$proc=CreateObject('org.freemedsoftware.module.ProcedureModule');
				$fee=$proc->CalculateCharge($cov,1,$proccode,$data['pnotesdoc'],$data['pnotespat']);	
				$proc_data=array(
					"procpatient" => $data['pnotespat'],
					"procphysician" => $data['pnotesdoc'],
					"procdiag1" => $diagcode,
					"proccpt" => $proccode,
					"procunits" => "1",
					"procpos" => HTTP_Session2::get('facility_id'),
					"proccharges" => $fee,
					"procbalorig" => $fee,
					"procbalcurrent" => $fee,
					"proccurcovid" => $cov['id']+0,
					"proccurcovtp" => $cov['covtype']+0,
					"procbillable" => "1",
					"procdt" => $data['pnotesdt']					
				);	
				if(($cov['covtype']+0)==1)	
					$proc_data['proccov1']=$cov['id'];
				else if(($cov['covtype']+0)==2)	
					$proc_data['proccov2']=$cov['id'];
				else if(($cov['covtype']+0)==3)	
					$proc_data['proccov3']=$cov['id'];
				else if(($cov['covtype']+0)==4)	
					$proc_data['proccov4']=$cov['id'];
				$proc->add($proc_data);
			}
			
			
			
		}
	} // end method add_post
	
	public function getEncountersList($ptid){
		$query="SELECT en.id,en.pnotesdt AS note_date,en.pnotesdescrip AS note_desc,en.pnotestype AS note_type, u.userdescrip AS user FROM enotes en ".
				" LEFT OUTER JOIN user u ON u.id=en.pnotesuser WHERE en.pnotespat=".$GLOBALS['sql']->quote($ptid);
		return  $GLOBALS['sql']->queryAll ( $query );
	}
	
	public function getEncounterNoteInfo($enid){
		$query="SELECT * FROM enotes WHERE id=".$GLOBALS['sql']->quote($enid);
		return  $GLOBALS['sql']->queryRow ( $query );
	}
	
	
} 
register_module("EncounterNotes");

?>
