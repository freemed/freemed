<?php

/**
 *  xmldb.php
 *
 * +================================================================================================+
 * | A class for the manipulation of xml databases.
 * |
 * | An xml database is a file that contains a root tag, containing a number of "table" elements.
 * | Each "table" element contains a series of "records" and then each record contains one or more
 * | "elements" which are the values for this record.  A small xml database might look something like
 * | this:
 * |
 * |<-?xml version="1.0"?->
 * |<DB>
 * |	<Table1 created="01 Jul 2001 01:25:48">
 * |		<RecordName created="01 Jul 2001 01:25:48" RecordId="1">
 * |			<Element1>Property1</Element1>
 * |			<Element2>Property2</Element2>
 * |		</RecordName>
 * |		<RecordName created="01 Jul 2001 01:25:49" RecordId="2">
 * |			<Element1>Property1</Element1>
 * |			<Element2>Property2</Element2>
 * |		</RecordName>
 * |	</Table1>
 * |	<Table2 created="02 Jul 2001 01:26:48">
 * |		<OtherRecordName created="02 Jul 2001 01:26:48" RecordId="1">
 * |			<ElementA>Property1</ElementA>
 * |			<ElementB>Property2</ElementB>
 * |		</OtherRecordName>
 * |	</Table2>
 * |</DB>
 * +------------------------------------------------------------------------------------------------+
 * | Copyright:
 * |
 * | xmldb.php: A Php.XPath database class that uses xml files.
 * |
 * | Copyright (C) 2001-2002 Nigel Swinson, Nigel@Swinson.com
 * |
 * | This program is free software; you can redistribute it and/or
 * | modify it under the terms of the GNU General Public License
 * | as published by the Free Software Foundation; either version 2
 * | of the License, or (at your option) any later version.
 * |
 * | This program is distributed in the hope that it will be useful,
 * | but WITHOUT ANY WARRANTY; without even the implied warranty of
 * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * | GNU General Public License for more details.
 * |
 * | You should have received a copy of the GNU General Public License
 * | along with this program; if not, write to the Free Software
 * | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 * +================================================================================================+
 *
 * @author  Nigel Swinson / Jeremy Jones
 * @link    http://sourceforge.net/projects/phpxmldb/
 * @version 1.0 beta
 * @CVS $Id$
 */

/***************************************************************************************************
========================================= I n c l u d e s ==========================================
***************************************************************************************************/

require_once(dirname(__FILE__).'/class.XPath.php');
require_once(dirname(__FILE__).'/class.CDebug.php');


/***************************************************************************************************
====================================================================================================
                                P h p X m l D b   -   C l a s s
====================================================================================================
***************************************************************************************************/

class XmlDb {

	/*******************************************************************************************
	===========================  P r i v a t e   M e m b e r s  ================================
	*******************************************************************************************/

	//-----------------------------------------------------------------------------------------/
	//                                    Object Flags                                         /
	//-----------------------------------------------------------------------------------------/

	// A switch to say if a file has been opened with this class instance
	var $bFileOpen = FALSE;
	// Only want to modify a db that has been altered
	var $bModifyFlag = FALSE;
	// Do we want to report errors to the user?
	var $bErrorReportingFlag = TRUE;
	// If we opened for write access (TRUE) or just read access (FALSE).
	var $bWriteAccess = FALSE;
	// TRUE if the db is locked.
	var $bDbLocked = FALSE;
	// The options for this class.  See the SetOptions() call for details of valid
	// options.
	var $aOptions = array();

	//-----------------------------------------------------------------------------------------/
	//                                  Object Variables                                       /
	//-----------------------------------------------------------------------------------------/

	// The name of the database that we have open
	var $DbFileName = '';
	// The name of the lock file that we use for this db.
	var $hDbFile = '';

	var $XmlDb = array();

	/*******************************************************************************************
	===============================  C o n s t r u c t o r  ====================================
	*******************************************************************************************/

	/**
	 * Constructor
	 *
	 * Initialises the Debug Object
	 */
	function XmlDb() {
		// initialise the debug and profiling class
		$this->Debug = new CDebug();
		$this->bDbLocked = FALSE;
		$this->DbFileName = '';
		$this->DbLockFileName = '';
		$this->aOptions = array('TimeStampFlag' => TRUE);
	}

	/*******************************************************************************************
	============================  P u b l i c   M e t h o d s  =================================
	*******************************************************************************************/

	/**
	 * Sets options that affect behaviour
	 *
	 * Possible options are:
	 *		TimeStampFlag (bool)
	 *						Specifies if there should be a created=time attribute for
	 *						tables and records.  Default is TRUE: timestamp on.
	 *		XmlOptions (array)
	 *						An array of options that is used for sending to the XPath class.
	 *						See the XPath.class.php documentation for more detail.
	 *
	 * @param $aOptions (array)	Array of name = value options to set for the class.  If the
	 *							value is unset then the option will be unset.
	 * @return			(bool)	FALSE if one of the options couldn't be set.  TRUE otherwise.
	 */
	function SetOptions($aOptions) {
		if (!is_array($aOptions)) return FALSE;
		foreach($aOptions as $Key => $Val) {
			if (isset($Val))
				$this->aOptions[$Key] = $Val;
			else
				unset($this->aOptions[$Key]);
		}
	}


	/**
	 * Opens up an XmlDb from a file.
	 *
	 * @param $DbFileName		(string) The name of the file that contains the Xml database
	 * @param $bCreateDatabase	(bool)	 If the file does not exist, then this flag describes
	 *									 whether or not the function should create the databse,
	 *									 or fail The xml database is updated by a Close() call.
	 * @param $bNeedWriteAccess	(bool)	 Should we open this file for read/write or read/only
	 *									 access? Defaults to FALSE - read-only
	 * @return					(bool)	 TRUE if the database was opened successfully, FALSE if
	 *									 failure
	 */
	function Open($DbFileName, $bCreateDatabase = FALSE, $bNeedWriteAccess = FALSE) {

		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('Open', $bDebugFlag);
		if ($bDebugFlag) {
			echo "DbaseDbFileName: $DbFileName\n";
			echo "bCreateDatabase: $bCreateDatabase\n";
			echo "bNeedWriteAccess: $bNeedWriteAccess\n";
			echo "<hr>";
		}

		//////////////////////////////////////////////

		$bResult = FALSE;

		// do {} while (false) loop
		do {
			// If we have a db open already, then fail.
			if ($this->hDbFile) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("$this->DbFileName is already open, please Close() it first");
				break;
			}

			// save the filename for comparison when the file is closed
			$this->DbFileName = $DbFileName;

			// define the access flag that will be used to open the dbase file
			$this->bWriteAccess = $bNeedWriteAccess;

			// If we need write access, and the db is read only, then that only really comes
			// into play when we Close(), as we could be Closing() to a different file.  However
			// it probably makes for a more useful function if we need to ask if we want 
			// write access when we try to open the file.

			if ($bNeedWriteAccess && !is_writable($DbFileName) && file_exists($DbFileName)) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("$DbFileName is read-only, can't open for write access");
				break;
			}

			// If the db file doesn't exist already, then possibly create it.
			if (!file_exists($DbFileName)) {
				if (!$bCreateDatabase) {
					if ($this->bErrorReportingFlag || $bDebugFlag)
						trigger_error("$DbFileName does not exist");
						break;
				} 

				// Create the database then.
				if ($bDebugFlag) "Creating new database $DbFileName\n";

				// Create the basics of the file.
				if (!($this->hDbFile = fopen($DbFileName, "w+"))) {
					if ($this->bErrorReportingFlag || $bDebugFlag)
						trigger_error("Could not create new XML file $DbFileName");
					break;
				}

				$bFileLocked = $this->_LockFile(TRUE);
				if (!$bFileLocked) {
					if ($bDebugFlag) echo "\nFailed to lock db - we can't create it.\n";
					fclose($this->hDbFile);
					$this->hDbFile = FALSE;
					break;
				}

				// Add the xml header line.
				fwrite($this->hDbFile, "<?xml version=\"1.0\"?>\n<XmlDatabase/>");
				@fflush($this->hDbFile);

				$this->_LockFile(FALSE);

				// Close it again.
				if (!fclose($this->hDbFile)) {
					if ($this->bErrorReportingFlag || $bDebugFlag)
						trigger_error("Could not create new XML file $DbFileName");
					break;
				}
				$this->hDbFile = FALSE;
			}

			if ($this->bWriteAccess) {
				$XmlDbAccessFlag = 'r+';
			} else {
				$XmlDbAccessFlag = 'r';
				// ### Strictly speaking we'd like to make this 'r' for read only, but files that
				// are opened for "read only" can't be flock()ed on linux, and we'd
				// like to protect against other sessions opening and modifying the file
				// while we read.  So we have to open for r+ to make sure that we
				// protect the integrity of the read as much as possible.
				if (is_writable($DbFileName)) $XmlDbAccessFlag = 'r+';
			}

			// Open the db
			$this->hDbFile = fopen($this->DbFileName, $XmlDbAccessFlag);
			if (!$this->hDbFile) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("Could not open the XML file $DbFileName");
				break;
			}

			// If the file is writable, then we must lock it to protect against other 
			// processes writing to the file while we are reading from it.
			if (is_writable($DbFileName)) {
				$bFileLocked = $this->_LockFile(TRUE);
				if (!$bFileLocked) {
					if ($bDebugFlag) echo "\nFailed to lock db - we can't open it.\n";
					break;
				}

				if ($bDebugFlag) echo "Locked db.\n";
			}

			// try to open the Xml database
			$this->XmlDb = new XPath();
			if (isset($this->aOptions['XmlOptions'])) {
				if ($bDebugFlag) {
					echo "Setting the following Xml options:\n";
					print_r($this->aOptions['XmlOptions']);
					echo "\n";
				}
				$this->XmlDb->setXmlOptions($this->aOptions['XmlOptions']);
			}
		
			// We currently have the file locked, so no other file pointer is going to work,
			// so we have to read the data in, and then pass it to the XPath class.
			$XmlString = fread($this->hDbFile, filesize($DbFileName));

			// Import the data stream.
			$this->XmlDb->importFromString($XmlString);

			if ($bDebugFlag) {
				echo $this->XmlDb->exportAsHtml();

				echo "$DbFileName: ".htmlspecialchars(implode('',file($DbFileName)));
			}

			// if we have got this far, the function has passed
			// set the FileOpen switch to TRUE since we have opened a file
			$bResult = TRUE;
			$this->bFileOpen = TRUE;

			// If we are only opening for read access, we can unlock it now.
			if (!$this->bWriteAccess) {
				$this->_LockFile(FALSE);

				// No point in keeping the file open either.
				fclose($this->hDbFile);
				$this->hDbFile = FALSE;
			}
		} while (false);

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('Open', $bResult, $bDebugFlag);
		return $bResult;
	}


	/**
	 * Closes an XmlDb by writing it out to a file.
	 *
	 * @param $DbFileName	(string) The name of the file that the Xml database should be
	 *								 written to.  If empty, then the name that was supplied on
	 *								 Open() will be used.
	 * @return				(bool)	 TRUE if the database was closed and written to successfully,
	 *								 TRUE if it was not necesary to close the database because
	 *								 no alterations were made FALSE with error if failure,
	 */
	function Close($DbFileName = '') {

		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;
		// Set to TRUE to JUST show the stack frame
		$bShowStackFrame = FALSE;

		$this->Debug->_BeginDebugFunction('Close', $bDebugFlag || $bShowStackFrame);
		if ($bDebugFlag) {
			echo "XmlDbFileName: $DbFileName\n";
			//print_r($this->XmlDb);
			echo "<hr>";
		}

		//////////////////////////////////////////////

		// do {} while (false)
		$bResult = FALSE;
		do {
			if ($bDebugFlag) echo "\nModify Flag = $this->bModifyFlag\n\n";

			if (empty($DbFileName)) $DbFileName = $this->DbFileName;

			// If they are closing the db to a different file, then we MUST write out...
			$bMustWrite = FALSE;
			if (strcmp($this->DbFileName,$DbFileName)) {
				$bMustWrite = TRUE;
			}
			// Else if they made modifications then we should write out.
			elseif ($this->bModifyFlag) {
				$bMustWrite = TRUE;
			}

			if (!$bMustWrite) {
				if ($bDebugFlag) echo "Changes were not made - no point saving the database";
				$bResult = TRUE;
				break;
			}

			// Did we lock the db?
			if (!$this->bDbLocked) {
				if ($bDebugFlag) echo "Changes were made, but the db had not been locked.  Changes aborted.";
				$bResult = FALSE;
				break;
			} 

			// Write it out
			if ($bDebugFlag) {
				echo $this->XmlDb->exportAsHtml();
				echo "\nDidn't write to file, as we are debugging\n";
			} else {
				// Are we writing back out to the file we opened?
				if (!strcmp($this->DbFileName,$DbFileName)) {
					$XmlOut = $this->XmlDb->exportAsXml();
					if ($XmlOut === FALSE) {
						// otherwise, we didn't modify the file properly
						if ($this->bErrorReportingFlag || $bDebugFlag)
							trigger_error("Write error when writing back the $DbFileName database file.");
						break;
					}

					if ($bDebugFlag) echo htmlspecialchars($XmlOut);

					ftruncate($this->hDbFile, 0);
					rewind($this->hDbFile);
					$iBytesWritten = fwrite($this->hDbFile, $XmlOut);
					if ($iBytesWritten != strlen($XmlOut)) {
						if ($this->bErrorReportingFlag || $bDebugFlag)
							trigger_error("Write error when writing back the $DbFileName database file.");
						break;						
					}
					@fflush($this->hDbFile);
				} else {
					// Just export it straight to file then
					$this->XmlDb->exportToFile($DbFileName);
				}
/*
				// ### It would be nice to do this, but for some reason the return value from fwrite()
				// is always 0, even when it did write bytes out!
				if ($iBytesWritten != strlen($XmlOut)) {
					if ($this->bErrorReportingFlag || $bDebugFlag)
						trigger_error("Expected to write ".strlen($XmlOut)." bytes, but only $iBytesWritten were written.");
					break;
				}
*/
				if ($bDebugFlag) echo "\nFile was modified and saved\n";
				$bResult = TRUE;
			}
		} while (false);

		// unlock and close the file if we had it open.
		if ($this->bDbLocked) {
			$this->_LockFile(FALSE);
			fclose($this->hDbFile);
			$this->hDbFile = FALSE;
		}

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('Close', $bResult, $bDebugFlag || $bShowStackFrame);
		return $bResult;
	}


	/**
	 * Add a record to the XmlDb.
	 *
	 * Add a new record of type $RecordTag to the $TableTag table with <name>value</name>
	 * entries for each of the entries in the associate array $aElementData.
	 *
	 * Example: The following command would open up the database.xml file, and
	 *			add the follwing fragment of xml.
	 *		AddRecord("Users", "User", array("Firstname" => "Nigel", "Surname" => "Swinson"));
	 *
	 *	<XmlDataBase>
	 *		<Users>
	 *			<User>
	 *				<Firstname>Nigel</FirstName>
	 *				<Surname>Swinson</Surname>
	 *			</User>
	 *		</Users>
	 *	</XmlDataBase>
	 *
	 * @param $TableTag		(string) the second level tag that contains all the XML we are to
	 *								 search in
	 * @param $RecordTag	(string) the tag that represents the record within the DatabaseTag
	 * @param $aElementData	(array)	 an associative array of name=value pairs which constitue
	 *								 the data of this record
	 * @return				(bool)	 TRUE if the record was added, FALSE if failure
	 */
	function AddRecord($TableTag, $RecordTag, $aElementData) {

		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('AddRecord', $bDebugFlag);
		if ($bDebugFlag) {
			echo "TableTag: $TableTag\n";
			echo "RecordTag: $RecordTag\n";
			echo "ElementData:\n";
			print_r($aElementData);
			echo "<hr>";
		}

		//////////////////////////////////////////////

		$bResult = FALSE;

		// do {} while (false) loop for mini exception handling
		do {
			// Numpty check
			if (!$TableTag || !$RecordTag) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The TableTag and RecordTag arguments must have a value.");
				break;
			}

			// if a file hasn't been opened, we can't proceed
			if (!$this->bFileOpen) {
				if ($bDebugFlag) echo "The Open call did not open a file successfully - we can't proceed";
				break;
			}

			// if we didn't open for write access, how can we try to add a record?
			if (!$this->bWriteAccess) {
				if ($bDebugFlag) echo "To alter the database, you need to open it with write access";
				break;
			}

			// Call the internal version of the function to add the record.
			$bResult = $this->_AddRecord($TableTag, $RecordTag, $aElementData);

			// If record was added, set the Modify Flag to TRUE
			if ($bResult) {
				$this->bModifyFlag = TRUE;
				if ($bDebugFlag) echo "ModifyFlag set to TRUE";
			}
		} while (FALSE);

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('AddRecord', $bResult, $bDebugFlag);
		return $bResult;
	}


	/**
	 * Add a record to the XmlDb, but only if the $XPathSearch returns no records for the database.
	 *
	 * This function is slower than AddRecord(), so you should only use it where the user will
	 * either be a numpty, or does not have access to RemoveRecord() in order to clean up the
	 * mistake that they have made.
	 *
	 * @param $TableTag		(string) the second level tag that contains all the XML we are to
	 *								 search in
	 * @param $RecordTag	(string) the tag that represents the record within the DatabaseTag
	 * @param $aElementData	(array)  an associative array of name=value pairs which constitue
	 *								 the data of this record.  See AddRecord() for more details.
	 * @param $XPathSearch	(string) The unique search criteria.  If a record exists that
	 *								 matches this search, then the new record will not be added.
	 *								 The context will select all the records, and this value
	 *								 refines the search further. A typical XPathSearch might be
	 *								 "contains(FieldName,'substring')" or "*".
	 * @return				(bool)	 TRUE if the record was added, FALSE if it wasn't as a
	 *								 record already existed matching the XPathUniqueCriteria, or
	 *								 a failure occurred.
	 */
	function AddUniqueRecord($TableTag, $RecordTag, $aElementData, $XPathSearch) {

		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('AddUniqueRecord', $bDebugFlag);
		if ($bDebugFlag) {
			echo "TableTag: $TableTag\n";
			echo "RecordTag: $RecordTag\n";
			echo "XPathSearch: $XPathSearch\n";
			echo "aElementData:\n";
			print_r($aElementData);
			echo "<hr>";
		}

		//////////////////////////////////////////////

		$bResult = FALSE;

		// do {} while (false) loop for mini exception handling
		do {
			// Numpty check
			if (!$TableTag || !$RecordTag) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The TableTag and RecordTag arguments must have a value.");
				break;
			}
			if (!$XPathSearch) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The ElementData and XPathSearch must have values.");
				break;
			}

			// if a file hasn't been opened, we can't proceed
			if (!$this->bFileOpen) {
				if ($bDebugFlag) echo "The Open call did not open a file successfully - we can't proceed";
				break;
			}

			// if we didn't open for write access, how can we try to add a record?
			if (!$this->bWriteAccess) {
				if ($bDebugFlag) echo "To alter the database, you need to open it with write access";
				break;
			}

			/////////////////////////////////////////////////

			// Check in the database for records that match the search criteria.
			$aSearchResults = $this->_Search($TableTag, $RecordTag, $XPathSearch);

			// Add the record only if it wasn't there already
			if (count($aSearchResults)) {
				if ($bDebugFlag) {
					echo "Entry already in database:\n";
					print_r($aSearchResults);
					echo "\n";
				}
				break;
			}

			// Add the record.
			if ($bDebugFlag) echo "Adding entry to the database\n";
			$bResult = $this->_AddRecord($TableTag, $RecordTag, $aElementData);

			// If record was added, set the Modify Flag to TRUE
			if ($bResult) {
				$this->bModifyFlag = TRUE;
				if ($bDebugFlag) echo "ModifyFlag set to TRUE";
			}
		} while (false);

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('AddUniqueRecord', $bResult, $bDebugFlag);
		return $bResult;
	}


	/**
	 * Modify a record in the the XmlDb.
	 *
	 * Modify a record by rewriting the contents of the record.  Keeping the original
	 * record XML tags and RecordId means that the record keeps it's place in the file
	 *
	 * Example: The following command would open up the database.xml file, and
	 *			add the follwing fragment of xml.
	 *		ModifyRecord("database.xml", "Users", "User", "1",
	 *			array("Firstname" => "Jeremy", "Surname" => "Jones"));
	 *
	 * regardless of the record's content previously, it would only have 2 fields after this call
	 *
	 * After modification:
	 *	<XmlDataBase>
	 *		<Users>
	 *			<User RecordId="1">
	 *				<Firstname>Jeremy</FirstName>
	 *				<Surname>Jones</Surname>
	 *			</User>
	 *		</Users>
	 *	</XmlDataBase>
	 *
	 * @param $TableTag			(string)  the second level tag that contains all the XML we are
	 *									  to search in
	 * @param $RecordTag		(string)  the tag that represents the record within the
	 *									  DatabaseTag.
	 * @param $RecordId			(string)  the RecordId of the record
	 * @param $aElementData		(array)	  an associative array of name=value pairs which
	 *									  constitue the data of this record.
	 * @param $bPreserveContent (boolean) A flag which specifies whether record data is removed
	 *									  or not.  If the flag is FALSE (default), all data is
	 *									  removed from the record and replaced with the
	 *									  $aElementData. If it is TRUE, the data in the record
	 *									  is kept and overwritten if needbe.
	 * @return					(bool)	  TRUE if the record was modified, FALSE if failure
	 */
	function ModifyRecord($TableTag, $RecordTag, $RecordId, $aElementData, $bPreserveContent = FALSE) {

		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('ModifyRecord', $bDebugFlag);
		if ($bDebugFlag) {
			echo "TableTag: $TableTag\n";
			echo "RecordTag: $RecordTag\n";
			echo "RecordId: $RecordId\n";
			echo "ElementData: ";
			print_r($aElementData);
			echo "PreserveContent: $bPreserveContent\n";
			echo "<hr>";
		}

		//////////////////////////////////////////////

		$bResult = FALSE;

		// do {} while (false) loop for mini exception handling
		do {
			// Numpty check
			// the TableTag, RecordTag and RecordId tags cannot be null
			// neither can any of the keys in the ElementData array!! Now can be add field data into a field with no name?
			if (!$TableTag || !$RecordTag) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The TableTag and RecordTag arguments must have a value.");
				break;
			}
			if (!$RecordId) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The RecordId argument must have a value!");
				break;
			}
			if (!$aElementData) {
				$aElementData = array();
			} else {
				$bFaultyElement = FALSE;
				foreach ($aElementData as $Field => $FieldData) {
					if (!$Field) {
						if ($this->bErrorReportingFlag || $bDebugFlag)
							trigger_error("You cannot modify a record where the Field name is not specified!");
						$bFaultyElement = TRUE;
						break;
					}
				}
				if ($bFaultyElement) break;
			}
			// if a file hasn't been opened, we can't proceed
			if (!$this->bFileOpen) {
				if ($bDebugFlag) echo "The Open call did not open a file successfully - we can't proceed";
				break;
			}

			// if we didn't open for write access, how can we try to add a record?
			if (!$this->bWriteAccess) {
				if ($bDebugFlag) echo "To alter the database, you need to open it with write access";
				break;
			}

			//////////////////////////////////////////////

			// Call the internal version of the function to add the record.
			$bResult = $this->_ModifyRecord($TableTag, $RecordTag, $RecordId, $aElementData, $bPreserveContent);

			// If record was added, set the Modify Flag to TRUE
			if ($bResult) {
				$this->bModifyFlag = TRUE;
				if ($bDebugFlag) echo "\nModifyFlag set to TRUE\n";
			}
		} while (FALSE);

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('ModifyRecord', $bResult, $bDebugFlag);
		return $bResult;
	}


	/**
	 * Remove a record from the XmlDb.
	 *
	 * Remove the record(s) which match the XPath expression which is passed to the funciton.
	 * If a node to be deleted has contents or children these are deleted also.
	 *
	 * Example: The following command would open up the database.xml file, and
	 *			remove the following node.
	 *		RemoveRecord("Users", "User", "contains(Firstname,'Nigel')");
	 *
	 *	<XmlDataBase>
	 *		<Users>
	 *			<User>							// this node and contents would be removed
	 *				<Firstname>Nigel</FirstName>
	 *				<Surname>Swinson</Surname>
	 *			</User>
	 *		</Users>
	 *	</XmlDataBase>
	 *
	 * @param $TableTag		(string) the second level tag that contains all the XML we are to
	 *								 search in
	 * @param $RecordTag	(string) the tag that represents the record within the DatabaseTag.
	 * @param $XPathSearch	(string) The unique search criteria.  If a record exists that
	 *								 matches this search, then the new record will not be
	 *								 added.  The context will select all the records, and this
	 *								 value refines the search further.  A typical XPathSearch
	 *								 might be "contains(FieldName,'substring')" or "*".
	 * @return				(bool)	 TRUE/FALSE value describing the result of attempted removal
	 */
	function RemoveRecord($TableTag, $RecordTag, $XPathSearch) {

		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('RemoveRecord', $bDebugFlag);
		if ($bDebugFlag) {
			echo "TableTag: $TableTag\n";
			echo "RecordTag: $RecordTag\n";
			echo "XPathSearch: $XPathSearch\n";
			echo "<hr>";
		}

		//////////////////////////////////////////////

		$bResult  = FALSE;

		// do {} while (false);
		do {
			// Numpty check
			if (!$TableTag || !$RecordTag) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The TableTag and RecordTag arguments must have a value.");
				break;
			}
			if (!$XPathSearch) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The XPathSearch variable must have content.");
				break;
			}

			// if a file hasn't been opened, we can't proceed
			if (!$this->bFileOpen) {
				if ($bDebugFlag) echo "The Open call did not open a file successfully - we can't proceed";
				break;
			}

			// if we didn't open for write access, how can we try to add a record?
			if (!$this->bWriteAccess) {
				if ($bDebugFlag) echo "To alter the database, you need to open it with write access";
				break;
			}

			//////////////////////////////////////////////

			// Find address of Table node
			$TablePath = $this->_FindTable($TableTag);

			// Search the table match nodes with XPathSearch criteria
			$aSearchResults = $this->XmlDb->evaluate($TablePath."/".$RecordTag."[".$XPathSearch."]");

			// Call the private function that will delete the records but only if there are records to delete
			if (empty($aSearchResults)) break;

			$bResult = $this->_RemoveRecord($aSearchResults);
		} while (false);

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('RemoveRecord', $bResult, $bDebugFlag);
		return $bResult;
	}


	/**
	 * Remove records from the XmlDb that are specified by their RecordIds
	 *
	 * Remove the record(s) which are described by an array of RecordIds
	 * If a node to be deleted has contents or children these are deleted also.
	 *
	 * Example: The following command would open up the database.xml file, and
	 *			remove the following node.
	 *		RemoveRecord("Users", "User", array ("101", "102"));
	 *
	 *	<XmlDataBase>
	 *		<Users>
	 *			<User RecordId="101">			 // this node and contents would be removed
	 *				<Firstname>Nigel</FirstName>
	 *				<Surname>Swinson</Surname>
	 *			</User>
	 *			<User RecordId="102">			 // this node and contents would also be removed
	 *				<Firstname>Nigel</FirstName>
	 *				<Surname>Swinson</Surname>
	 *			</User>
	 *		</Users>
	 *	</XmlDataBase>
	 *
	 * @param $TableTag		(string) the second level tag that contains all the XML we are to
	 *								 search in
	 * @param $RecordTag	(string) the tag that represents the record within the DatabaseTag.
	 * @param $aRecordIds	(array)	 an array of RecordIds
	 *
	 * @return				(bool)	 TRUE/FALSE value describing theresult of attempted removal
	 */
	function RemoveRecordId($TableTag, $RecordTag, $aRecordIds) {

		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('RemoveRecordId', $bDebugFlag);
		if ($bDebugFlag) {
			echo "TableTag: $TableTag\n";
			echo "RecordTag: $RecordTag\n";
			echo "RecordIds: ";
			print_r($aRecordIds);
			echo "<hr>";
		}

		//////////////////////////////////////////////

		$bResult = FALSE;

		// do {} while (false) loop
		do {
			// Numpty check
			// We must not let the function continue if there are null values in the arguments that are passed to it
			if (!$TableTag || !$RecordTag) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The TableTag and RecordTag arguments must have a value.");
				break;
			}
			if (empty($aRecordIds)) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The aRecordIds array cannot be empty!.");
				break;
			}

			{
				$bBadRecordId = FALSE;
				foreach ($aRecordIds as $RecordId) {
					if (!$RecordId) {
						if ($this->bErrorReportingFlag || $bDebugFlag)
							trigger_error("The aRecordIds array cannot have an entry of 0!.");
						$bBadRecordId = TRUE;
						break;
					}
				}

				if ($bBadRecordId) break;
			}

			// if a file hasn't been opened, we can't proceed
			if (!$this->bFileOpen) {
				if ($bDebugFlag) echo "The Open call did not open a file successfully - we can't proceed";
				break;
			}

			// if we didn't open for write access, how can we try to add a record?
			if (!$this->bWriteAccess) {
				if ($bDebugFlag) echo "To alter the database, you need to open it with write access";
				break;
			}

			//////////////////////////////////////////////

			$RecordIdName = 'RecordId';

			// Find address of Table node
			$TablePath = $this->_FindTable($TableTag);

			// Search the table match nodes with XPathSearch criteria
			$aRecordIdPaths = $this->XmlDb->evaluate($TablePath."/".$RecordTag."[@$RecordIdName]");

			// Get a list of the XPaths that we have to remove.
			$aRemovePaths = array();
			{
				// Cycle round each record ID one at a time.
				$iRecordIdMax = count($aRecordIdPaths);
				$iRemoveRecordIdMax = count($aRecordIds);
				// We'll bail as soon as we've found all we need.
				$bFoundAllRecords = FALSE;
				for ($iRecordIdIndex = 0; $iRecordIdIndex < $iRecordIdMax; $iRecordIdIndex++) {
					// Get the attributes of this record id.
					$Attributes = $this->XmlDb->getAttributes($aRecordIdPaths[$iRecordIdIndex]);
					// Should never happend given our XPath search.
					if (empty($Attributes[$RecordIdName])) continue;

					// Were we looking to delete this record?
					for ($iRemoveRecordIdIndex = 0; $iRemoveRecordIdIndex < $iRemoveRecordIdMax; $iRemoveRecordIdIndex++) {
						if ($Attributes[$RecordIdName] != $aRecordIds[$iRemoveRecordIdIndex]) continue;

						// Store this path then, it's a record we've to remove
						$aRemovePaths[] = $aRecordIdPaths[$iRecordIdIndex];

						// Are we finished?
						if (count($aRemovePaths) == count($aRecordIds))
							$bFoundAllRecords = TRUE;

						break;
					}

					// Bail if we found all the records.
					if ($bFoundAllRecords) break;
				}
			}

			// If we didn't find all the records we were to remove, then don't remove any of them
			if (count($aRemovePaths) != count($aRecordIds)) break;

			// Call the private function that will delete the records
			$bResult = $this->_RemoveRecord($aRemovePaths);
		} while (false);

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('RemoveRecordId', $bResult, $bDebugFlag);
		return ($bResult);
	}


	/**
	 * Get all the table names from the XML Database
	 *
	 *
	 * Example: The following command might return an array as follows:
	 *		GetTableNames();
	 *
	 *		Array (
	 *			[0] => TableOne,
	 *			[1] => TableTwo,
	 *			[2] => TableThree,
	 *			[3] => TableFour
	 *		)
	 *
	 * @return (array) an array of the table names within the XML file
	 */
	function GetTableNames() {

		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('GetTableNames', $bDebugFlag);

		//////////////////////////////////////////////

		$aResults = FALSE;

		// do {} while (false) loop
		do {
			// if a file hasn't been opened, we can't proceed
			if (!$this->bFileOpen) {
				if ($bDebugFlag)
					echo "The Open call did not open a file successfully - we can't proceed";
				break;
			}
			//////////////////////////////////////////////

			// Get the tables of the current database
			$aTableXPaths = $this->XmlDb->evaluate("/*/*");

			foreach ($aTableXPaths as $Key => $XPathAddress) {
				$ExplodedAddress = explode('/', $XPathAddress);
				$TableName = explode('[', $ExplodedAddress[count($ExplodedAddress) - 1]);
				$aResults[] = $TableName[0];

				if ($bDebugFlag) echo "\nTable '$TableName[0]' added to the returns array";

			}

			if ($bDebugFlag) {
				echo "\nThe returns array: ";
				print_r($aResults);
			}
		} while (false);

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('GetTableNames', $aResults, $bDebugFlag);
		return $aResults;
	}


	/**
	 * Get all the unique record names within a table of an XML file
	 *
	 *
	 * Example: The following command might return an array as follows:
	 *		GetRecordNames($TableTag);
	 *
	 *		Array (
	 *			[0] => RecordOne,
	 *			[1] => RecordTwo,
	 *			[2] => RecordThree,
	 *			[3] => RecordFour
	 *		)
	 * @param $TableTag (string) the second level tag that contains all the XML we are to
	 *							 search in
	 * @return			(array)	 an array of the record names within the $TableTag
	 */
	function GetRecordNames($TableTag) {

		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('GetRecordNames', $bDebugFlag);
		if ($bDebugFlag) {
			echo "TableTag: $TableTag\n";
			echo "<hr>";
		}

		//////////////////////////////////////////////

		$aResults = FALSE;

		// do {} while (false) loop
		do {
			// Numpty check
			if (!$TableTag) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The TableTag argument must have a value.");
				break;
			}

			// if a file hasn't been opened, we can't proceed
			if (!$this->bFileOpen) {
				if ($bDebugFlag)
					echo "The Open call did not open a file successfully - we can't proceed";
				break;
			}

			//////////////////////////////////////////////

			// Either we will return results then, or the table isn't found.
			$aResults = array();

			// Search for all the record tags within the table in question
			$aSearchResults = $this->XmlDb->evaluate("/*/".$TableTag."/*");

			foreach($aSearchResults as $Result)
				$aRecordNames[] = $this->XmlDb->nodeName($Result);

			// Remove any results within the array which are duplicate record names
			if (!empty($aRecordNames)) $aResults = array_values(array_unique($aRecordNames));

			if ($bDebugFlag) {
				echo "\nArray of record names:\n";
				print_r($aResults);
			}
		} while (false);

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('GetRecordNames', $aResults, $bDebugFlag);
		return $aResults;

	}


	/**
	 * Get all the unique field names within a table of a db.
	 *
	 * @param $TableTag  (string) the second level tag that contains all the XML we are to
	 *							  search in
	 * @param $RecordTag (string) the third level tag that contains all the XML we are to
	 *							  search in
	 * @return			 (array)  an array of the field names within the $TableTag/$RecordTag.
	 *							  FALSE on failure
	 */
	function GetFieldNames($TableTag, $RecordTag) {
		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('GetFieldNames', $bDebugFlag);
		if ($bDebugFlag) {
			echo "TableTag: $TableTag\n";
			echo "RecordTag: $RecordTag\n";
			echo "<hr>";
		}

		//////////////////////////////////////////////

		$aResults = FALSE;

		// do {} while (false) loop
		do {
			// Numpty check
			if (empty($TableTag) || empty($RecordTag)) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The TableTag and RecordTag arguments must have a value.");
				break;
			}

			// if a file hasn't been opened, we can't proceed
			if (!$this->bFileOpen) {
				if ($bDebugFlag)
					echo "The Open call did not open a file successfully - we can't proceed";
				break;
			}

			//////////////////////////////////////////////

			// Search for all the record tags within the table in question
			$aSearchResults = $this->XmlDb->evaluate("/*/".$TableTag."/".$RecordTag."/*");

			foreach ($aSearchResults as $Result)
				$aFieldNames[] = $this->XmlDb->nodeName($Result);

			if ($bDebugFlag) {
				echo "\nThe Search Results:\n";
				print_r($aSearchResults);
			}

			// Remove any results within the array which are duplicate record names
			if (!empty($aFieldNames))	$aResults = array_values(array_unique($aFieldNames));
			else						$aResults = array();

			if ($bDebugFlag) {
				echo "\nThe Results:\n";
				print_r($aResults);
			}
		} while (false);

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('GetFieldNames', $aResults, $bDebugFlag);
		return $aResults;
	}


	/**
	 * Search an XmlDb file and return an array of associative arrays of element=content data
	 *
	 * @param $TableTag		(string) The second level tag that contains all the XML we are to
	 *								 search in
	 * @param $RecordTag	(string) The tag that represents the record within the DatabaseTag
	 * @param $XPathSearch	(string) The unique search criteria.  If a record exists that
	 *								 matches this search, then the new record will not be
	 *								 added.  The context will select all the records, and this
	 *								 value refines the search further. A typical XPathSearch
	 *								 might be "contains(FieldName,'substring')" or "*".
	 * @return				(array)  Associative  array with entries at record ids for each
	 *								 matching record found. Each entry itself will be an
	 *								 associative array of name=value pairs where the key is the
	 *								 element name and the value is the element content.  An
	 *								 empty array if no records are found.
	 */
	function Search($TableTag, $RecordTag, $XPathSearch) {

		// If you are having difficulty using this function.  Then set this to true and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('Search', $bDebugFlag);
		if ($bDebugFlag) {
			echo "TableTag: $TableTag\n";
			echo "RecordTag: $RecordTag\n";
			echo "XPathSearch: $XPathSearch\n";
			echo "<hr>";
		}

		//////////////////////////////////////////////

		$aResults = FALSE;

		// do {} while (false) loop
		do {
			// Numpty check
			if (!$TableTag || !$RecordTag) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The TableTag and RecordTag arguments must have a value.");
				break;
			}
			// if a file hasn't been opened, we can't proceed
			if (!$this->bFileOpen) {
				if ($bDebugFlag)
					echo "The Open call did not open a file successfully - we can't proceed";
				break;
			}

			/////////////////////////////////////////////////

			// Call the internal function.
			$aResults = $this->_Search($TableTag, $RecordTag, $XPathSearch);

			if (!count($aResults)) $aResults = array();
		} while (false);

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('Search', $aResults, $bDebugFlag);
		return $aResults;
	}


	/**
	 * Sort an array of data which has been generated by the Search() function
	 *
	 * @param $aArrayForSorting (array)  The array of data to be searched.
	 * @param $SortByKey		(string) The key by which the search of the array will be driven.
	 *									 the key must be a second dimention key ie.
	 *									 $array[first][second] Default is set to 0
	 * @param $SortOrder		(string) Defines the order in which the array is sorted.  The
	 *									 default value of "" sorts the array in ascending
	 *									 order.  Any non empty value will cause the array to be
	 *									 sorted in reverse order.
	 * @return					(array)	 Associative array of sorted data where a record key
	 *									 will be it's RecordId and the content will be the
	 *									 record data
	 */
	function SortSearch($aArrayForSorting, $SortByKey = 0, $SortOrder = '') {

		// If you are having difficulty using this function.  Then set this to true and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('SortSearch', $bDebugFlag);
		if ($bDebugFlag) {
			echo "Array for Sorting:\n";
			print_r($aArrayForSorting);
			echo "SortByKey: $SortByKey\n";
			echo "SortOrder: $SortOrder\n";
			echo "<hr>";
		}

		//////////////////////////////////////////////

		$aResults = FALSE;

		// do {} while (false) loop
		do {
			// if a file hasn't been opened, we can't proceed
			if (!$this->bFileOpen) {
				if ($bDebugFlag)
					echo "The Open call did not open a file successfully - we can't proceed";
				break;
			}

			/////////////////////////////////////////////////

			// only sort the array if there is content!
			if ($aArrayForSorting) {
				// we need to take the array of data and extract the key which we want to sort by
				// the assumption is that the array is a 2 dimentional array $array[blah][key]
				$aSortingArray = array();
				$aRemainder = array();
				foreach($aArrayForSorting as $RecordId => $ArrayContents) {
					// we need to create an array of the first dimension keys and the
					// second dimention keys which we will be sorting by
					if (isset($ArrayContents[$SortByKey])) {
						$aSortingArray[$RecordId] = $ArrayContents[$SortByKey];
					} else {
						$aRemainder[$RecordId] = $RecordId;
					}
				}

				if ($bDebugFlag) {
					echo "\n\nThese entries don't have that field set\n";
					print_r($aRemainder);
				}

				// now we need to sort the array created above as the user specified
				if (empty($SortOrder))	{
					if (!empty($aSortingArray)) {
						asort($aSortingArray);
						if (!empty($aRemainder)) {
							foreach ($aSortingArray as $Key => $Value) {
								$aRemainder[$Key] = $Value;
							}
							$aSortingArray = $aRemainder;
						}
					} else {
						$aSortingArray = $aRemainder;
					}
				} else {
					if (!empty($aSortingArray)) {
						arsort($aSortingArray);
						if (!empty($aRemainder)) {
							foreach ($aRemainder as $Key => $Value) {
								$aSortingArray[$Key] = $Value;
							}
						}
					} else {
						$aSortingArray = $aRemainder;
					}
				}

				// we should now have an array where the keys are the keys of the first
				// dimension of the array passed to the function for sorting

				if ($bDebugFlag) {
					echo "\n\nThe sorted array (containing the RecordId and sorted Fields)\n";
					print_r($aSortingArray);
				}

				// using the array of previously sorted keys, we need to create an array of
				// sorted data that will be return
				foreach($aSortingArray as $SortedKey => $SortedContent)
					// add content to the array which will be returned
					$aSortedArray[$SortedKey] = $aArrayForSorting[$SortedKey];
			}

			if (empty($aSortedArray)) $aSortedArray = array();

			$aResults = $aSortedArray;
		} while (false);

		/////////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('SortSearch', $aResults, $bDebugFlag);
		return $aResults;
	}


	/**
	 * Display search results in a series of Javascript blocks.
	 *
	 * Using a previously generated array of data, output a selection of javascript blocks
	 * containing a list of javacript function calls.
	 *
	 * @param $aSearchResults		(array)  an array of search results of which a portion is
	 *										 to be displayed.  Use the Search function to search
	 *										 the XML file and produce an array of specific data
	 *										 and then use the $aFunctionParameters parameter
	 *										 to define the subset of data that you want to
	 *										 display.
	 * @param $FunctionName			(string) the name of the function that we are going to call
	 *										 for each matching record found
	 * @param $aFunctionParameters	(array)  an array of property names that represent the
	 *										 argument list for the $FunctionName function.
	 *										 $FunctionName will be called with exactly this
	 *										 many arguments, and where an element exists in
	 *										 the record that matches the name of the parameter,
	 *										 then it's text value will be used.  Where no
	 *										 element exists "" will be passed as the parameter
	 *										 instead.
	 * @param $bTestFlag			(boolean) a boolean which when set to true, outputs, not
	 *										 to the standard output, but to a string which can
	 *										 be used in the test harness to test the integrity
	 *										 of the function
	 * @return						(mixed)  the number of entries that were displayed. FALSE
	 *										 is no entries exist
	 */
	function Display($aSearchResults, $FunctionName, $aFunctionParameters, $bTestFlag = FALSE) {

		// If you are having difficulty using this function.  Then set this to true and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('Display', $bDebugFlag);
		if ($bDebugFlag) {
			if ($bTestFlag) echo "\nTEST ENVIRONMENT\n";
			echo "aSearchResults: ";
			print_r($aSearchResults);
			echo "\nFunctionName: $FunctionName";
			echo "\naFunctionParameters: ";
			print_r($aFunctionParameters);
			echo "<hr>";
		}

		//////////////////////////////////////////////

		$iResult = FALSE;

		// do {} while (false) loop
		do {

			// Numpty check
			if ($aSearchResults === NULL) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The SearchResults array must have a value.");
				break;
			}
			if (!$FunctionName || !$aFunctionParameters) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The FuctionName and aFunctionParameters arguments must have a value.");
				break;
			}

			// if a file hasn't been opened, we can't proceed
			if (!$this->bFileOpen) {
				if ($bDebugFlag)
					echo "The Open call did not open a file successfully - we can't proceed";
				break;
			}

			/////////////////////////////////////////////////

			// We're gonna succeed then... even if we print nuffin'.
			$iCount = 0;
			$DebugContent = "";
			$TestResults = '';

			if ($aSearchResults) {
				if (!$bTestFlag) $this->_OpenScriptTag();
				// For every value in the array, output a function line.
				$Line = "";
				foreach ($aSearchResults as $RecordId => $aMatch) {
					// Start the function.
					$Line = $FunctionName."(";
					// Cycle through the parameters
					reset($aFunctionParameters);
					while ($ParameterName = current($aFunctionParameters)) {
						if (isset($aMatch[$ParameterName])) {
							$Value = $aMatch[$ParameterName];
							// Add slashes so that we don't break the javascript.
							$Value = addslashes($Value);
							// Escape EOL in the string.
							$Value = addcslashes($Value, "\10..\14");
							$Line .= "\"$Value\"";
						}
						else if ($ParameterName == 'RecordId') {
							$Line .= "\"$RecordId\"";
						} else
							$Line .= "\"\"";
						if (next($aFunctionParameters)) $Line .= ",";
					}
					// End the function.
					$Line .= ");\n";

					// Output the line: if bTestFlag is TRUE, output is appended to the return string,
					// otherwise it is echoed to standard output
					if ($bTestFlag) $TestResults .= $Line;
					else			echo $Line;

					if ($bDebugFlag) $DebugContent .= htmlspecialchars($Line);

					// Every 25 entries close and re-open the script block.  Only if this is not a Test run
					if (!$bTestFlag)
						if (($iCount % 25) == 24) {
							// Give the user an echo of the javascript created
							if ($bDebugFlag) {
								$DebugContent .= "\nRe-opening the script block\n";
							}
							$this->_CloseScriptTag();
							$this->_OpenScriptTag();
						}
					$iCount++;
				}

				if (!$bTestFlag) $this->_CloseScriptTag();

				// Give the user an echo of the javascript created
				if ($bDebugFlag) echo "\n\nThe JavaScript that has been created:\n".$DebugContent;
			}

			// set the return value.  Note if we are "debugging" we have to fudge this a bit.
			if ($bTestFlag)				$iResult = $TestResults;
			elseif ($aSearchResults)	$iResult = count($aSearchResults);
			else						$iResult = FALSE;
		} while (false);

		/////////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('Display', $iResult, $bDebugFlag);
		return $iResult;
	}


	/**
	 * Search a database and output javascript blocks containing a list of javacript function calls.
	 *
	 * Equivalent to calling Search() and passing the results into Display()
	 *
	 * @param $TableTag				(string) the second level tag that contains all the XML we
	 *										 are to search in
	 * @param $RecordTag			(string) the tag that represents the record within the
	 *										 DatabaseTag
	 * @param $XPathSearch			(string) The unique search criteria.  If a record exists
	 *										 that matches this search, then the new record
	 *										 will not be added.  The context will select all
	 *										 the records, and this value refines the search
	 *										 further.  A typical XPathSearch might be
	 *										 "contains(FieldName,'substring')" or "*".
	 * @param $FunctionName			(string) the name of the function that we are going to call
	 *										 for each matching record found
	 * @param $aFunctionParameters	(array)  an array of property names that represent the
	 *										 argument list for the $FunctionName function.
	 *										 $FunctionName will be called with exactly this
	 *										 many arguments, and where an element exists in
	 *										 the record that matches the name of the parameter,
	 *										 then it's text value will be used.  Where no element
	 *										 exists "" will be passed as the parameter instead.
	 * @return						(int)	 the number of entries that were displayed.
	 */
	function SearchAndDisplay($TableTag, $RecordTag, $XPathSearch, $FunctionName, $aFunctionParameters) {
		// Use the Search and Display functions to do the work
		$aSearchResults = $this->Search($TableTag, $RecordTag, $XPathSearch);
		$this->Display($aSearchResults, $FunctionName, $aFunctionParameters);
		return count($aSearchResults);
	}


	/*******************************************************************************************
	==========================  P r i v a t e    M e t h o d s  ================================
	*******************************************************************************************/
	// Auxilliary private functions.
	// You should not need to read these functions unless you are debugging or developing with this file.

	/**
	 * Simple helper to draw an open <SCRIPT> block
	 */
	function _OpenScriptTag() {
		// Output the <SCRIPT> tag.
	?>
<SCRIPT LANGUAGE="JAVASCRIPT">
<!--
<?
	}


	/**
	 * Simple helper to draw a close </SCRIPT> block
	 */
	function _CloseScriptTag() {
		// Output the </SCRIPT> tag.
	?>
// -->
</SCRIPT>
<?
	}


	/**
	 * Returns the date and time for storage as a element or attribute in the xml document.
	 */
	function _GetDbTime() {
		return strftime("%d %b %Y %H:%M:%S");
	}


	/**
	 * Maintains the lock status of the files
	 *
	 * @param $Action	(bool) 	 TRUE (default) locks the file, FALSE unlocks it
	 * @return			(bool)	 FALSE if the operation failed.
	 */
	function _LockFile($Action = TRUE) {
		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('_LockFile', $bDebugFlag);
		if ($bDebugFlag) {
			if ($Action === TRUE)	echo "Action: lock\n";
			else					echo "Action: unlock\n";
			echo "Object->bWriteAccess: $this->bWriteAccess";
			echo "<hr>";
		}

		//////////////////////////////////////////////

		// what we are going to do depends on the action and the write access we opened the file with
		$bResult = FALSE;
		switch ($Action) {
			case TRUE:
			// lock the file
			{
				if ($bDebugFlag) echo "Lock the file ".$this->hDbFile."\n";

				// We can only lock the db if it is open
				if (!$this->hDbFile) break;

				// produce an exclusive non-blocking lock
				if (!flock($this->hDbFile, LOCK_EX + LOCK_NB)) {
					if ($this->bErrorReportingFlag || $bDebugFlag) 
						echo "Could not obtain exclusive lock on db\n";
					fclose($this->hDbFile);
					$this->hDbFile = FALSE;
					break;
				}

				// if we have got this far, we have managed to lock the file
				$this->bDbLocked = TRUE;
				$bResult = TRUE;

				break;
			}
			case FALSE:
			// unlock the file
			{
				if ($bDebugFlag) echo "Unlock the file\n";

				// do we have a pointer to the file?
				if (!$this->hDbFile) break;

				// unlock the file which has been held open and close it
				flock($this->hDbFile, LOCK_UN);

				// if we have got this far, we have managed to unlock the file
				$this->bDbLocked = FALSE;
				$bResult = TRUE;

				break;
			}
			default:
				// Not supported.
				break;
		}

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('_LockFile', $bResult, $bDebugFlag);
		return $bResult;
	}


	/**
	 * Finds the table of XmlDb corresponding to TableTag and returns it's complete XPath address.
	 *
	 * @param $TableTag		(string) The name of the table we are trying to locate
	 * @param $bCreateTable (bool)	 If TRUE, the table will be created if it doesn't exist.
	 * @return				(string) The absolute XPath address to the table
	 */
	function _FindTable($TableTag, $bCreateTable = FALSE) {

		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('_FindTable', $bDebugFlag);
		if ($bDebugFlag) {
			echo "TableTag: $TableTag\n";
			echo "bCreateTable: $bCreateTable\n";
			echo "<hr>";
		}

		//////////////////////////////////////////////

		$Result = "";

		// do {} while (false) loop
		do {
			// Locate the absolute path of the xml db root tag.
			if ($bDebugFlag) echo "\nLocating root tag of db...\n";
			$aRootPath = $this->XmlDb->evaluate("/*");

			// There should be only one root node
			$RootPath = "";
			if (count($aRootPath) > 1) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The database has ".count($aRootPath)." root tags.  Xml demands there be only one.");
				break;
			}

			// Was there a root node?  If not we must create one.
			if (!count($aRootPath)) {
				// We must create the table then.
				$RootPath = $this->XmlDb->appendChild("", "<XmlDatabase/>");
				$aRootPath = $this->XmlDb->evaluate("/*");
				if (count($aRootPath) != 1)
					if ($this->bErrorReportingFlag || $bDebugFlag)
						trigger_error("Creating a root \"XmlDatabase\" tag didn't work :o(\n");

				$RootPath = $aRootPath[0];
				if ($bDebugFlag) echo "Created root tag in db at $RootPath\n";

				// only add the created attribute is the appropriate flag is set to TRUE
				if ($this->aOptions['TimeStampFlag']) $AddAttributes ['created'] = $this->_GetDbTime();
				$this->XmlDb->setAttributes($RootPath, $AddAttributes);
			}
			else {
				$RootPath = $aRootPath[0];
				if ($bDebugFlag) echo "Found root tag at $RootPath\n";
			}

			if ($bDebugFlag) echo "\n";

			//////////////////////////////////////////////

			// Locate the absolute path to the table tag in the database.
			if ($bDebugFlag) echo "Locating $TableTag table...\n";
			$aTablePath = $this->XmlDb->evaluate($RootPath."/".$TableTag);

			// If there were more than 1 tables in the entry, then this is an error.
			$TablePath = "";
			if (count($aTablePath) > 1) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The database has ".count($aTablePath)." $TableTag tables in it.  We accept only 1 table of each type.");
				break;
			}

			// If there was no table, then create one.
			if (!count($aTablePath)) {
				// We must create the table then.
				$TablePath = $this->XmlDb->appendChild($RootPath, "<$TableTag/>");
				$aTablePath = $this->XmlDb->evaluate($RootPath."/".$TableTag);
				if (count($aTablePath) != 1)
					if ($this->bErrorReportingFlag || $bDebugFlag)
						trigger_error("Adding a $TableTag table didn't work :o(\n");

				$TablePath = $aTablePath[0];
				if ($bDebugFlag) echo "Created $TableTag table at $TablePath\n";

				// only add the created attribute is the appropriate flag is set to TRUE
				if ($this->aOptions['TimeStampFlag']) $AddAttributes ['created'] = $this->_GetDbTime();
				else $AddAttributes = '';
				$this->XmlDb->setAttributes($TablePath, $AddAttributes);

				// We kinda modified the database then.
				$this->bModifyFlag = TRUE;
			}
			else {
				$TablePath = $aTablePath[0];
				if ($bDebugFlag) echo "Found $TableTag table at $TablePath\n";
			}

			// Success!
			$Result = $TablePath;
		} while (false);

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('_FindTable', $Result, $bDebugFlag);
		return $Result;
	}


	/**
	 * Set all the record ids in the given table to unique values
	 *
	 * Ensure that the record ids increase in value in the order they appear in the xml file.
	 *
	 * @param $TablePath (string) The absolute XPath address of the table we are querying
	 * @return			 (int)	  The largest record ID in the file
	 */
	function _SetRecordIds($TablePath) {

		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('_SetRecordIds', $bDebugFlag);
		if ($bDebugFlag) {
			echo "TableTag: $TableTag\n";
			echo "<hr>";
		}

		//////////////////////////////////////////////

		// First of all do a search of all the records in the table.
		$aRecordList = $this->XmlDb->evaluate($TablePath."/*");
		if ($bDebugFlag) echo "Found ".count($aRecordList)." records in the table\n";

		// For each record, ensure that it has a record id.
		$CurrentRecordId = 0;
		foreach ($aRecordList as $Record) {
			$aAttributes = $this->XmlDb->getAttributes($Record);

			// If this record id is set, and it's number is increasing then we don't
			// need to alter this record id.
			if (isset($aAttributes['RecordId'])) {
				$ThisRecordId = $aAttributes['RecordId'];
				if (is_numeric($ThisRecordId) && ($ThisRecordId > $CurrentRecordId)) {
					$CurrentRecordId = $ThisRecordId;
					if ($bDebugFlag) echo "Record $Record has RecordId $ThisRecordId\n";
					continue;
				}
				if ($bDebugFlag) echo "Record $Record has an invalid RecordId of $ThisRecordId\n";
			}

			// We need to increment the current record id and set it for this record.
			$CurrentRecordId++;
			$aAttributes['RecordId'] = $CurrentRecordId;
			$this->XmlDb->setAttributes($Record, $aAttributes);
			if ($bDebugFlag) echo "Record $Record now has RecordId $CurrentRecordId\n";
		}

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('_SetRecordIds', $CurrentRecordId, $bDebugFlag);
		return $CurrentRecordId;
	}


	/**
	 * Obtain the record id for a record specified by it's XPath addresss.
	 *
	 * It will regenerate the record ids if necessary.
	 *
	 * @param $TablePath  (string)	The absolute XPath address of the table we are querying
	 * @param $RecordPath (string)	The absolute XPath address of the record whose ID we want
	 * @return			  (int)		The record ID
	 */
	function _GetRecordId($TablePath, $RecordPath) {

		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('_GetRecordId', $bDebugFlag);
		if ($bDebugFlag) {
			echo "TablePath: $TablePath\n";
			echo "RecordPath: $RecordPath\n";
			echo "<hr>";
		}

		//////////////////////////////////////////////

		// Get the attributes of the record.
		$aAttributes = $this->XmlDb->getAttributes($RecordPath);

		// If the Record Id property isn't set, then we need to go through the table and set all
		// the record ids.
		$RecordId = "";
		if (!isset($aAttributes['RecordId'])) {
			if ($bDebugFlag)
				echo "Record id not set for record $RecordPath.  Regenerating record ids.\n";
			// Set all the record ids in the table
			$this->_SetRecordIds($TablePath);
		} else {
			$RecordId = $aAttributes['RecordId'];
			// If it's not a number, it's not valid
			if (!is_numeric($RecordId)) {
				if ($bDebugFlag) echo "Record id ".$aAttributes['RecordId'].
					" is not an number for record $RecordPath.  Regenerating record ids.\n";
				// Set all the record ids in the table
				$this->_SetRecordIds($TablePath);
				$RecordId = 0;
			}
		}

		// If we don't have a record id yet, then we must have just rebuild the record ids,
		// so can safely get one now.
		if (!$RecordId) {
			$aAttributes = $this->XmlDb->getAttributes($RecordPath);
			$RecordId = $aAttributes['RecordId'];
		}

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('_GetRecordId', $RecordId, $bDebugFlag);
		return $RecordId;
	}


	/**
	 * Obtain a new record id for the specified table
	 *
	 * @param $TablePath  (string)	The absolute XPath address of the table we are querying
	 * @return			  (int)		A new record ID
	 */
	function _GetNewRecordId($TablePath) {

		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('_GetNewRecordId', $bDebugFlag);
		if ($bDebugFlag) {
			echo "TablePath: $TablePath\n";
			echo "<hr>";
		}

		//////////////////////////////////////////////

		// First of all get the last record in the table.
		$aLastRecord = $this->XmlDb->evaluate($TablePath."/*[last()]");

		// If there was a last entry, then evaluate the next record id from this record's id.
		$NewRecordId = "";
		if (count($aLastRecord) > 1) {
			if ($this->bErrorReportingFlag || $bDebugFlag)
				trigger_error("\nThe last() XPath function returned a node set with more than one entry! [".count($aLastRecord)." ] in ".$TablePath);
			if ($bDebugFlag) $this->CloseDebugFunction($aStartTime);
			return FALSE;
		}
		elseif (count($aLastRecord) == 1) {
			// Get the record id of the last record.
			$LastRecordId = $this->_GetRecordId($TablePath, $aLastRecord[0]);

			if ($bDebugFlag) echo "Last Record id is $LastRecordId\n";

			$NewRecordId = $LastRecordId + 1;
		}
		else {
			// It's the first record then.
			$NewRecordId = 1;
		}

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('_GetNewRecordId', $NewRecordId, $bDebugFlag);
		return $NewRecordId;
	}


	/**
	 * Check to see if a particular recordId is taken.
	 *
	 * @param $TableTag	 (string) The name of the table we are interested in
	 * @param $RecordTag (string) The name of the records we are interested in
	 * @param $RecordId  (int)	  The recordID we would like to use
	 * @return			 (int)	  A valid record ID for the database, either $RecordID if it
	 *							  isn't already taken, else a new record Id.
	 */
	function _CheckRecordId($TableTag, $RecordTag, $RecordId) {

		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('_CheckRecordId', $bDebugFlag);
		if ($bDebugFlag) {
			echo "TableTag: $TableTag\n";
			echo "RecordTag: $RecordTag\n";
			echo "RecordId: $RecordId\n";
			echo "<hr>";
		}

		//////////////////////////////////////////////
		// Obtain the last record ID.

		// Get the table path, creating the table if it doesn't exist.
		$TablePath = $this->_FindTable($TableTag, TRUE);

		// First of all get the last record in the table.
		$aLastRecord = $this->XmlDb->evaluate($TablePath."/*[last()]");

		// If there was a last entry, then evaluate the next record id from this record's id.
		$NewRecordId = "";
		if (count($aLastRecord) > 1) {
			if ($this->bErrorReportingFlag || $bDebugFlag)
				trigger_error("\nThe last() XPath function returned a node set with more than one entry! [".count($aLastRecord)." ] in ".$TablePath);
			if ($bDebugFlag) $this->CloseDebugFunction($aStartTime);
			return FALSE;
		}

		if (count($aLastRecord) == 1) {
			// Get the record id of the last record.
			$LastRecordId = $this->_GetRecordId($TablePath, $aLastRecord[0]);

			if ($bDebugFlag) echo "Last Record id is $LastRecordId\n";
		}
		// we will use the RecordId here to generate the NewRecordId if the one passed to the function is invalid

		//////////////////////////////////////////////

		// search for a record with this ID
		$aResult = $this->_Search($TableTag, $RecordTag, "@RecordId='".$RecordId."'");

		if (!$aResult) {
			// assign the stated recordId
			$NewRecordId = $RecordId;
			if ($bDebugFlag) echo "\nRecordId was set in the element data.\nNew record id is $NewRecordId\n";
		}
		else {
			// the RecordId cannot be used - it needs to be unique
			if ($LastRecordId)	$NewRecordId = $LastRecordId + 1;
			else				$NewRecordId = 1;

			if ($bDebugFlag) echo "\nRecordId was NOT set - a record with this Id already exists\n";
		}

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('_CheckRecordId', $NewRecordId, $bDebugFlag);
		return $NewRecordId;
	}


	/**
	 * Internal version of AddRecord.
	 *
	 * @param $TableTag		(string) The name of the table we are adding to
	 * @param $RecordTag	(string) The name of the records we are adding to
	 * @param $aElementData (array)  Associative array of element data to set.
	 * @return				(bool)	 TRUE on success, FALSE on failure.
	 */
	function _AddRecord($TableTag, $RecordTag, $aElementData) {

		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('_AddRecord', $bDebugFlag);
		if ($bDebugFlag) {
			echo "TableTag: $TableTag\n";
			echo "RecordTag: $RecordTag\n";
			echo "ElementData:\n";
			print_r($aElementData);
			echo "<hr>";
		}

		//////////////////////////////////////////////

		$bResult = FALSE;

		// do {} while (false) loop
		do {
			// Numpty check
			if (!$TableTag || !$RecordTag) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The TableTag and RecordTag arguments must have a value.");
				break;
			}

			//////////////////////////////////////////////

			// Get the table path, creating the table if it doesn't exist.
			$TablePath = $this->_FindTable($TableTag, TRUE);

			// we sometimes want to add record with a particular ID
			// with phpdbasedb this is done by specifying a [_RecordId] => 5 in element data
			// this feature will be supported by phpxmldb in the same way

			// loop through the element data and see if the user has specified a _RecordId
			if (is_array($aElementData)) {
				foreach ($aElementData as $Name => $Value) {
					if ($Name == '_RecordId') {
						// use the internal member to set the new ID
						if ($bDebugFlag) echo "\nUsing the internal member to set the ID";
						$NewRecordId = $this->_CheckRecordId($TableTag, $RecordTag, $Value);
						if ($bDebugFlag) echo "\nNewRecordId: $NewRecordId\n";

						// remove the recordId parameter irrespective of whether the RecordId was valid
						unset($aElementData['_RecordId']);
						if ($bDebugFlag) echo "\nThe _RecordId element has been removed from the element data\n";
					}
				}
			}

			// if we haven't already set the recordId, go and do it now
			if (empty($NewRecordId)) {
				$NewRecordId = $this->_GetNewRecordId($TablePath);
				if ($bDebugFlag) echo "New record id is $NewRecordId\n";

				// Add the record
				if ($bDebugFlag) echo "Adding new record at $TablePath\n";
				$RecordPath = $this->XmlDb->appendChild($TablePath, "<$RecordTag/>");
			} else {
				// We need to make sure that we insert the record at the right place, rather than just
				// appending it, so that we are sure the record IDs are incrementing.

				// Search through the table for all records with a RecordID that is greater than our record
				$Predicate = "@RecordId > $NewRecordId";
				$aSearchResults = $this->XmlDb->evaluate($TablePath."/".$RecordTag."[".$Predicate."]");

				if (count($aSearchResults)) {
					// Insert the record
					if ($bDebugFlag) echo "Inserting new record at $aSearchResults[0]\n";
					$RecordPath = $this->XmlDb->insertChild($aSearchResults[0], "<$RecordTag/>");
				} else {
					// Add the record
					if ($bDebugFlag) echo "Adding new record at $TablePath\n";
					$RecordPath = $this->XmlDb->appendChild($TablePath, "<$RecordTag/>");
				}
			}

			//////////////////////////////////////////////

			// define the attributes that we want to add to the record tag
			// only include the created timestamp if the appropriate flag is set to TRUE
			if ($this->aOptions['TimeStampFlag']) $AddAttributes['created'] = $this->_GetDbTime();
			$AddAttributes['RecordId'] = $NewRecordId;

			$this->XmlDb->setAttributes($RecordPath, $AddAttributes);
			if ($bDebugFlag) echo "New record now at $RecordPath\n";

			//////////////////////////////////////////////

			// Add the content.
			if ($aElementData) {
				foreach($aElementData as $Name => $Value) {
					$EntryPath = $this->XmlDb->appendChild($RecordPath, "<$Name/>");
					if ($bDebugFlag) echo "Added new entry $EntryPath\n";

					// Prepare the value by stripping \r's and calling htmlspecialchars.
					$Value = htmlspecialchars($Value);
					$Value = str_replace("\r", "", $Value);

					$this->XmlDb->appendData($EntryPath, $Value);

					if ($bDebugFlag) echo "Added new content to $EntryPath: ".$this->XmlDb->substringData($EntryPath)."\n";
				}
				if ($bDebugFlag) echo "\n";
			}

			$bResult = TRUE;
		} while (false);

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('_AddRecord', $bResult, $bDebugFlag);
		return $bResult;
	}


	/**
	 * Internal version of Modify Record.
	 *
	 * The contents of the record are removed and replaced with the name => value pairs held in
	 * the $aElementData array.
	 *
	 * @param $TableTag			(string) The name of the table containing our target
	 * @param $RecordTag		(string) The name of the records containing our target
	 * @param $RecordId			(int)	 The ID of the record we are modifying
	 * @param $aElementData		(array)  Associative array of element data to modify to
	 * @param $bPreserveContent (bool)	 If FALSE, all existing data in the record will be
	 *									 deleted.  If TRUE, existing data will be kept, and any
	 *									 records overwritten as necessary.
	 * @return					(bool)	 TRUE on success, FALSE on failure.
	 */
	function _ModifyRecord($TableTag, $RecordTag, $RecordId, $aElementData, $bPreserveContent) {

		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('_ModifyRecord', $bDebugFlag);
		if ($bDebugFlag) {
			echo "TableTag: $TableTag\n";
			echo "RecordTag: $RecordTag\n";
			echo "RecordId: $RecordId\n";
			echo "ElementData: ";
			print_r($aElementData);
			echo "PreserveContent: $bPreserveContent\n";
			echo "<hr>";
		}

		//////////////////////////////////////////////

		$bResult = FALSE;

		// do {} while (false) loop
		do {
			// Numpty check
			if (!$TableTag || !$RecordTag) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The TableTag and RecordTag arguments must have a value.");
				break;
			}

			//////////////////////////////////////////////

			// Get the table path, creating the table if it doesn't exist.
			$TablePath = $this->_FindTable($TableTag, TRUE);

			//////////////////////////////////////////////

			// we need to find the XPath address of the record to be modified
			$XPathSearch = "@RecordId=$RecordId";
			$aRecordPath = $this->XmlDb->evaluate($TablePath."/".$RecordTag."[".$XPathSearch."]");
			if (count($aRecordPath) > 0) $RecordPath = $aRecordPath[0];
			else						 $RecordPath = '';

			if (empty($RecordPath)) {
				$bResult = FALSE;
				if ($bDebugFlag) echo "\nNo record was found with the specified RecordId\n";
				break;
			}

			if ($bDebugFlag) echo "\nThe XPath address of $XPathSearch has been evaluated as $RecordPath\n\n";

			// now we need to modify the content of the record
			// first we remove the content
			$aRemoveNodes = ($this->XmlDb->evaluate($TablePath."/".$RecordTag."[".$XPathSearch."]/*"));

			// we need to remove nodes in reverse order, since, if we remove node 1, node 2 becomes node 1
			// if we then try to remove node 2, it won't be what we expect
			$aRemoveNodes = array_reverse($aRemoveNodes);

			if ($bDebugFlag) echo "\nA list of the fields in the record and their status follow:\n";
			foreach($aRemoveNodes as $RemoveNode) {
				if ($bPreserveContent) {
					// we only need to removed the nodes which will need to be set later because they
					// are contained in the array of data passed to the function
					foreach($aElementData as $FieldName => $Value) {
						$aExplodedNode = explode('/', $RemoveNode);
						$aNode = explode('[', $aExplodedNode[count($aExplodedNode)-1]);
						if ($FieldName == $aNode[0]) {
							$this->XmlDb->replaceChild($RemoveNode, "<$FieldName>".$Value."</$FieldName>");
							if ($bDebugFlag) echo "\tNode replaced - ($RemoveNode)\n";
							// Unset the value from the element array now to be sure that we don't set it twice
							unset($aElementData[$FieldName]);
						}
						elseif ($bDebugFlag) {
							echo "\tNOT removed\n";
						}
					}
				}
				else {
					// we must remove all data from the record so that it can be re-populated
					$this->XmlDb->removeChild($RemoveNode);
					if ($bDebugFlag) echo "\tNode removed - ($RemoveNode)";
				}
			}

			if ($bDebugFlag) echo "\nThe following nodes have been added:\n";
			// then add new content to the record using the data in $aElementData
			foreach($aElementData as $Name => $Value) {
				$EntryPath = $this->XmlDb->appendChild($RecordPath, "<$Name/>");
				if ($bDebugFlag) echo "\t$EntryPath\n";

				// Prepare the value by stripping \r's and calling htmlspecialchars.
				$Value = htmlspecialchars($Value);
				$Value = str_replace("\r", "", $Value);

				$this->XmlDb->appendData($EntryPath, $Value);

				if ($bDebugFlag) echo "\tnew content added to $EntryPath: ".$this->XmlDb->substringData($EntryPath)."\n\n";
			}

			$bResult = TRUE;
		} while (false);

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('_ModifyRecord', $bResult, $bDebugFlag);
		return $bResult;
	}


	/**
	 * Internal version of RemoveRecord.
	 *
	 * Locates the XPath address within the file which match those in the array passed to it
	 * and removes them.
	 *
	 * @param $aXPathAddresses (array) An array of XPath addresses to remove
	 * @return				   (bool)  TRUE on success, FALSE on error.
	 */
	function _RemoveRecord($aXPathAddresses) {

		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('_RemoveRecord', $bDebugFlag);
		if ($bDebugFlag) {
			echo "Removing ".count($aXPathAddresses)." record(s) to be removed:\n";
			print_r($aXPathAddresses);
			echo "<hr>";
		}

		//////////////////////////////////////////////

		$bResult = FALSE;

		// do {} while (false) loop
		do {
			// How many records have been returned?
			$CountRecords = count($aXPathAddresses);

			// The removeChild object is too slow for large files
			// The time_out must be lengthened to take this into account
			// This is a fudge.  And will eventually be removed.
			if (get_cfg_var('safe_mode')) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("Safe mode ON; time out cannot be increased!");
				break;
			}

			set_time_limit(30);

			// Remove all the records.
			if ($bDebugFlag) echo "\nDeleting records...\n";
			while ($CountRecords) {
				// Remove the node
	   			$RemoveAddress = $aXPathAddresses[$CountRecords-1];
	    		$bRemoveResult = $this->XmlDb->removeChild($RemoveAddress);

				// Create an a array of TRUE/FALSE values depending on whether node is removed
				$aResults[$CountRecords-1]['Path'] = $RemoveAddress;
				$aResults[$CountRecords-1]['Removed'] = $bRemoveResult;

				// If we have successfully removed a node from the tree, then set the modify flag
				if ($bRemoveResult) {
					if ($bDebugFlag && !$this->bModifyFlag)
						echo "ModifyFlag set to TRUE\n";
					$this->bModifyFlag = TRUE;
				}

				// If we fail one, then stop trying... damn.. we've left the xml file in a half way
				// state which ain't good. :o(
				if (!$bRemoveResult) break;

				$CountRecords--;
			}

			// Did we delete them all?
			$bResult = ($CountRecords == 0);
		} while (false);

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('_RemoveRecord', $bResult, $bDebugFlag);
		return $bResult;
	}


	/**
	 * Internal version of Search.
	 *
	 * @param $TableTag		(string) The name of the table to search
	 * @param $RecordTag	(string) The name of the records to search
	 * @param $XPathSearch	(string) The search string, an XPath predicate
	 * @return				(array)  An associative array where the key is the record ID,
	 *								 and the value is an associative array of name = value
	 *								 pairs containing the data of the record.
	 */
	function _Search($TableTag, $RecordTag, $XPathSearch) {

		// If you are having difficulty using this function.  Then set this to TRUE and
		// you'll get diagnostic info displayed to the output.
		$bDebugFlag = FALSE;

		$this->Debug->_BeginDebugFunction('_Search', $bDebugFlag);
		if ($bDebugFlag) {
			echo "TableTag: $TableTag\n";
			echo "RecordTag: $RecordTag\n";
			echo "XPathSearch: $XPathSearch\n";
			echo "<hr>";
		}

		//////////////////////////////////////////////

		$aResults = array();

		// do {} while (false) loop
		do {
			// Numpty check
			if (!$TableTag || !$RecordTag) {
				if ($this->bErrorReportingFlag || $bDebugFlag)
					trigger_error("The TableTag and RecordTag arguments must have a value.");
				break;
			}

			/////////////////////////////////////////////////

			// If there is no database, then the search will produce no results!
			$Result = "";
			if (!$this->XmlDb) break;

			// Get the table path
			$TablePath = $this->_FindTable($TableTag);

			// If the table doesn't exist then the search isn't going to produce any results is it?
			if (empty($TablePath)) break;

			if ($bDebugFlag) echo "Found $TableTag table in database.\n";

			// Confirm they wanted to search for something.
			if (!$XPathSearch) {
				// Easy way out then.  We can't send this to XPath, as a predicate of [] won't parse
				$aResults = array();
				break;
			}

			// Search below this table path then for records matching the XPath search criteria.
			$aSearchResults = $this->XmlDb->evaluate($TablePath."/".$RecordTag."[".$XPathSearch."]");

			if ($bDebugFlag) {
				echo "Search produced the following entries:\n";
				print_r($aSearchResults);
				echo "\n";
			}

			// If there were any search results, then build the array.
			if (!count($aSearchResults)) break;

			// As search is a slow operation, search for all the child tags of the matches and
			// we can use a substring search to extract them.
			$aSearchResultsElements = $this->XmlDb->evaluate($TablePath."/".$RecordTag."[".$XPathSearch."]/*");

			if ($bDebugFlag) {
				echo "Search results have the following elements:\n";
				print_r($aSearchResultsElements);
				echo "\n";
			}

			// For each matching record.
			reset($aSearchResultsElements);
			foreach ($aSearchResults as $Record) {
				// Obtain the record id.
				$RecordId = $this->_GetRecordId($TablePath, $Record);

				// Add an entry to the result array.
				$aResults[$RecordId] = array();

				if ($bDebugFlag) echo "Processing record: $Record\n";

				// While we have an element that relates to this $Record
				while (strstr(($Element = current($aSearchResultsElements)),$Record)) {
					// Get it's content
					$Value = $this->XmlDb->substringData($Element);
					// If it had content, then get it's element name.
					if ($Value) {
						preg_match("/^\/(([^\/]*\/){3,})(.*)\[[\d]*\]$/i", $Element, $aMatches);
						// If we successfully found the tag name, then add it last in the array.
						$Name = $aMatches[3];
						if ($Name) {
							if ($bDebugFlag) echo "$Record has $Name data of $Value\n";
							$aResults[$RecordId][$aMatches[3]] = $Value;
						}
					}

					next($aSearchResultsElements);
				}
			}
		} while (false);

		//////////////////////////////////////////////

		$this->Debug->_CloseDebugFunction('_Search', $aResults, $bDebugFlag);
		return $aResults;
	}

} // End of class definition

?>
