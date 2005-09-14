<?
/**
 *  dbdebug.php
 *
 * +===========================================================================================+
 * | This file provides the debug and profiling methods for our databases - both phpxmldb and
 * | phpdbasedb.
 * |
 * +-------------------------------------------------------------------------------------------+
 * | Copyright:
 * |
 * | xmldb.php: A Php.XPath database class that uses xml files.
 * |
 * | Copyright (C) 2001 Nigel Swinson, Nigel@Swinson.com
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
 * +===========================================================================================+
 *
 * @author  Nigel Swinson / Jeremy Jones
 * @link    http://sourceforge.net/projects/phpxmldb/
 * @version 0.1 develop
 */


/***********************************************************************************************
================================== C l a s s   d e f i n i t i o n =============================
***********************************************************************************************/
class CDebug {

	// do we want to do profiling?
	var $bClassProfiling = FALSE;

	// The name of the class we are debugging, as communicated by each call stack frame
	var $ClassName = '';

	// Used to help navigate through the begin/end debug calls
	var $iDebugNextLinkNumber = 1;
	var $aDebugOpenLinks = array();

	/*******************************************************************************************
	=============================  D e b u g   M e t h o d s  ==================================
	*******************************************************************************************/

	function CDebug($ClassName = __FILE__) {
		$this->ClassName = $ClassName;
	}

	/**
	 * Call to begin the debug output.
	 */
	function _BeginDebugFunction($FunctionName, $bDebugFlag) {
		if ($bDebugFlag) {
			static $aColor = array('green','blue','red','lime','fuchsia', 'aqua');
			static $iColourIndex = -1;
			$iColourIndex++;
			$pre = '<pre STYLE="border:solid thin '. $aColor[$iColourIndex % 6] . '; padding:5">';
			$out = '<div align="left"> ' . $pre . "<STRONG>{$this->ClassName} : {$FunctionName}</STRONG>";
			echo $out;
			echo '<a style="float:right" name="'.$this->iDebugNextLinkNumber.'Open" href="#'.$this->iDebugNextLinkNumber.'Close">Function Close '.$this->iDebugNextLinkNumber.'</a>';
			echo '<hr style="clear:both">';
			array_push($this->aDebugOpenLinks, $this->iDebugNextLinkNumber);
			$this->iDebugNextLinkNumber++;
		} 

		if ($this->bClassProfiling)
			$this->_ProfBegin($FunctionName);

		return TRUE;
	}


	/**
	 * Call to end the debug output.
	 */
	function _CloseDebugFunction($FunctionName, $ReturnValue = "", $bDebugFlag) {
		if ($bDebugFlag) {
			echo "<hr>";
			if (isSet($ReturnValue)) {
				if (is_array($ReturnValue))
					echo "Return Value: ".print_r($ReturnValue)."\n";
				else if (is_numeric($ReturnValue)) 
					echo "Return Value: '".(string)$ReturnValue."'\n";
				else if (is_bool($ReturnValue)) 
					echo "Return Value: ".($ReturnValue ? "TRUE" : "FALSE")."\n";
				else 
					echo "Return Value: \"".htmlspecialchars($ReturnValue)."\"\n";
			}
			$iOpenLinkNumber = array_pop($this->aDebugOpenLinks);
			echo '<a style="float:right" name="'.$iOpenLinkNumber.'Close" href="#'.$iOpenLinkNumber.'Open">Function Open '.$iOpenLinkNumber.'</a>';
			echo '<br style="clear:both">';
			echo " \n</pre></div>";
		}

		if ($this->bClassProfiling)
			$this->_ProfEnd($FunctionName);

		return TRUE;
	}


	/*******************************************************************************************
	=========================  P r o f i l i n g   M e t h o d s  ==============================
	*******************************************************************************************/

	/**
 	 * Profile begin call
 	 */
	function _ProfBegin($sonFuncName) {
		static $entryTmpl = array ( 'start' => array(),
									'recursiveCount' => 0,
									'totTime' => 0,
									'callCount' => 0	);
		$now = explode(' ', microtime());

		if (empty($this->callStack)) {
			$fatherFuncName = '';
		}
		else {
			$fatherFuncName = $this->callStack[sizeOf($this->callStack)-1];
			$fatherEntry = &$this->profile[$fatherFuncName];
		}
		$this->callStack[] = $sonFuncName;

		if (!isSet($this->profile[$sonFuncName])) {
			$this->profile[$sonFuncName] = $entryTmpl;
		}

		$sonEntry = &$this->profile[$sonFuncName];
		$sonEntry['callCount']++;
		// if we call the t's the same function let the time run, otherwise sum up
		if ($fatherFuncName == $sonFuncName) {
			$sonEntry['recursiveCount']++;
		}
		if (!empty($fatherFuncName)) {
			$last = $fatherEntry['start'];
		$fatherEntry['totTime'] += round( (($now[1] - $last[1]) + ($now[0] - $last[0]))*10000 );
			$fatherEntry['start'] = 0;
		}
		$sonEntry['start'] = explode(' ', microtime());
	}

	/**
	 * Profile end call
	 */
	function _ProfEnd($sonFuncName) {
		$now = explode(' ', microtime());

		array_pop($this->callStack);
		if (empty($this->callStack)) {
			$fatherFuncName = '';
		}
		else {
			$fatherFuncName = $this->callStack[sizeOf($this->callStack)-1];
			$fatherEntry = &$this->profile[$fatherFuncName];
		}
		$sonEntry = &$this->profile[$sonFuncName];
		if (empty($sonEntry)) {
			echo "ERROR in profEnd(): '$funcNam' not in list. Seams it was never started ;o)";
		}

		$last = $sonEntry['start'];
		$sonEntry['totTime'] += round( (($now[1] - $last[1]) + ($now[0] - $last[0]))*10000 );
		$sonEntry['start'] = 0;
		if (!empty($fatherEntry)) $fatherEntry['start'] = explode(' ', microtime());
	}


	/**
	 * Show profile gathered so far as HTML table
	 */
	function _ProfileToHtml() {
		$sortArr = array();
		if (empty($this->profile)) return '';
		reset($this->profile);
		while (list($funcName) = each($this->profile)) {
			$sortArrKey[] = $this->profile[$funcName]['totTime'];
			$sortArrVal[] = $funcName;
		}
		//echo '<pre>';var_dump($sortArrVal);echo '</pre>';
		array_multisort ($sortArrKey, SORT_DESC, $sortArrVal );
		//echo '<pre>';var_dump($sortArrVal);echo '</pre>';

		$totTime = 0;
		$size = sizeOf($sortArrVal);
		for ($i=0; $i<$size; $i++) {
			$funcName = &$sortArrVal[$i];
			$totTime += $this->profile[$funcName]['totTime'];
		}
		$out = '<table border="1">';
		$out .='<tr align="center" bgcolor="#bcd6f1"><th>Function</th><th> % </th><th>Total [ms]</th><th># Call</th><th>[ms] per Call</th><th># Recursive</th></tr>';
		for ($i=0; $i<$size; $i++) {
			$funcName = &$sortArrVal[$i];
			$row = &$this->profile[$funcName];
			$procent = round($row['totTime']*100/$totTime);
			if ($procent>20) $bgc = '#ff8080';
			elseif ($procent>15) $bgc = '#ff9999';
			elseif ($procent>10) $bgc = '#ffcccc';
			elseif ($procent>5) $bgc = '#ffffcc';
			else $bgc = '#66ff99';

			$out .="<tr align='center' bgcolor='{$bgc}'>";
			$out .='<td>'. $funcName .'</td><td>'. $procent .'% '.'</td><td>'. $row['totTime']/10 .'</td><td>'. $row['callCount'] .'</td><td>'. round($row['totTime']/10/$row['callCount'],2) .'</td><td>'. $row['recursiveCount'].'</td>';
			$out .='</tr>';
		}
		$out .= '</table> Total Time [' . $totTime/10 .'ms]' ;

		echo $out;
		return TRUE;
	}
}

?>