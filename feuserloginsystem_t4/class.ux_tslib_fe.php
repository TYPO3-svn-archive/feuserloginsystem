<?php
declare(encoding = 'utf-8');

/***************************************************************
*  Copyright notice
*
*  (c) 2006 Andre Obereigner <feuserloginsystem@obereigner.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


class ux_tslib_fe extends tslib_fe	{

  /**
	 * Checks if config-array exists already but if not, gets it
	 *
	 * ### feuserloginsystem extension ###
	 * I added a feature in order to get some statistics about a user
	 * while is logged in.
	 * ### feuserloginsystem extension ###
	 *
	 * @return	void
	 */
  function getConfigArray()	{

			# Keep the existing content of this function.
		parent::getConfigArray();

		# Check if the User Statistic feature is enabled in Setup.
		if ($this->config['config']['tx_feuserloginsystem.']['enableUserStatistics']) {
		  # Do only write in statistic database table if a user is logged in!
			if ($this->loginUser)	{
				if (t3lib_div::GPvar("logintype") == 'login')	{
					$this->feuserloginsystem_addUserStatisticEntry();
				} else {
					$this->feuserloginsystem_updateUserStatisticEntry();
				}
			}
		}

	}

	/**
  * The method writes statistic information in an own statistic database table
  * each time a user logs in.
  *
  * @return void
  *
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
	function feuserloginsystem_addUserStatisticEntry() {

    $timeStamp = time();

    # For the page tracking feature get the time and the page ID.
    $pagetrackingArray = array();
    $pagetrackingArray[] = array('time' => $timeStamp, 'pageID' => intval($this->id));

    # Get commom information for statistics.
		$insertDataArray = array();
		$insertDataArray['feuserid']      = intval($this->fe_user->user["uid"]);
		$insertDataArray['sessionstart']  = $timeStamp;
		$insertDataArray['lastpageview']  = $timeStamp;
		$insertDataArray['pagecounter']   = intval(1);
		$insertDataArray['pagetracking']  = serialize($pagetrackingArray);

		# Create statistic entry in the database.
    $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_feuserloginsystem_userstatistics',$insertDataArray);
  }

  /**
  * The method updates already existing statistic information in an own statistic database table
  * each time a user opens a new page.
  *
  * @return void
  *
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
  function feuserloginsystem_updateUserStatisticEntry() {

    # Get existing statistic information for the current user and session.
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid,feuserid,sessionstart,lastpageview,pagecounter,pagetracking',
			'tx_feuserloginsystem_userstatistics',
			sprintf('feuserid=\'%s\'',addslashes(intval($this->fe_user->user["uid"]))),
			'',
			'sessionstart DESC',
			'1'
  	);
  	$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

  	# Existing statistic information could be found.
  	if(is_array($row)) {

      $timeStamp = time();

      # Unserialize page tracking information.
      $pagetrackingArray = unserialize($row['pagetracking']);
      # Add new page tracking information to the already existing page tracking information.
      $pagetrackingArray[] = array('time' => $timeStamp, 'pageID' => intval($this->id));

			#Update commom information for statistics.
		  $insertDataArray = array();
		  $insertDataArray['lastpageview']  = $timeStamp;
		  $insertDataArray['pagecounter']   = intval($row['pagecounter'] + 1);
		  $insertDataArray['pagetracking']  = serialize($pagetrackingArray);

		  # Update statistic information in the database.
      $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
        'tx_feuserloginsystem_userstatistics',
        sprintf('uid=\'%s\'',addslashes(intval($row['uid']))),
        $insertDataArray
      );

    }

  }


}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feuserloginsystem/class.ux_tslib_fe.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feuserloginsystem/class.ux_tslib_fe.php']);
}
?>
