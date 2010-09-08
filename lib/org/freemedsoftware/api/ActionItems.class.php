<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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

// Class: org.freemedsoftware.api.ActionItems
//
//	Class to access ActionItems functions.
//
class ActionItems {

	public function __constructor ( ) { }

	public function getActionItems($patient = NULL){
		//intensified Treatment Notes 
		$intensifiedTreatmentNotes = createObject("org.freemedsoftware.module.IntensifiedTreatmentNotes");
		$intensifiedTreatmentNotesArr = $intensifiedTreatmentNotes->getActionItems($patient);
		
		
		//Treatment Clinical Notes 
		$treatmentClinicalNote = createObject("org.freemedsoftware.module.TreatmentClinicalNote");
		$treatmentClinicalNoteArr = $treatmentClinicalNote->getActionItems($patient);

		//Clinical Assesment Notes 
		$clinicalAssessmentNotes = createObject("org.freemedsoftware.module.ClinicalAssessmentNotes");
		$clinicalAssessmentNotesArr = $clinicalAssessmentNotes->getActionItems($patient);

		//Authorizations
		$authorizations = createObject("org.freemedsoftware.module.Authorizations");
		$authorizationsArr = $authorizations->getActionItems($patient);
	
		//Module Field Checker
		$moduleFieldChecker = createObject("org.freemedsoftware.module.ModuleFieldChecker");
		$moduleFieldCheckerArr = $moduleFieldChecker->getUncompletedItems($patient);
		
		return array_merge($intensifiedTreatmentNotesArr,$treatmentClinicalNoteArr,$clinicalAssessmentNotesArr,$authorizationsArr,$moduleFieldCheckerArr);
	}

	public function getActionItemsCount($patient = NULL){
		//intensified Treatment Notes 
		$intensifiedTreatmentNotes = createObject("org.freemedsoftware.module.IntensifiedTreatmentNotes");
		$intensifiedTreatmentNotesCount = $intensifiedTreatmentNotes->getActionItemsCount($patient);
		
		//Treatment Clinical Notes 
		$treatmentClinicalNote = createObject("org.freemedsoftware.module.TreatmentClinicalNote");
		$treatmentClinicalNoteCount = $treatmentClinicalNote->getActionItemsCount($patient);

		//Clinical Assesment Notes 
		$clinicalAssessmentNotes = createObject("org.freemedsoftware.module.ClinicalAssessmentNotes");
		$clinicalAssessmentNotesCount = $clinicalAssessmentNotes->getActionItemsCount($patient);

		//Authorizations 
		$authorizations = createObject("org.freemedsoftware.module.Authorizations");
		$authorizationsCount = $authorizations->getActionItemsCount($patient);
		
		//Work Flow
		$moduleFieldChecker = createObject("org.freemedsoftware.module.ModuleFieldChecker");
		$moduleFieldCheckerCount = $moduleFieldChecker->getUncompletedItemsCount($patient);
		
		return $intensifiedTreatmentNotesCount + $moduleFieldCheckerCount + $treatmentClinicalNoteCount + $authorizationsCount + $clinicalAssessmentNotesCount;
	}


} // end class ActionItems

?>
