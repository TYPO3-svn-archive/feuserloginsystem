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


/**
 * Module 'Loginsystem' for the 'feuserloginsystem' extension.
 *
 * @author	Andre Obereigner <andre@obereigner.de>
 * @package	TYPO3
 * @subpackage	tx_feuserloginsystem
 */
	class ext_update {
    
    /**
    * Create the content of the UPDATE page.              
    *
    * @return string     Content
    * @author Andre Obereigner <feuserloginsystem@obereigner.de>
    */
		function main() {
			          
      # May the get the opportunity to transfer rows from tx_loginusertrack_stat
      # into tx_feuserloginsystem_userstatistics?            
      if($this->transferingRowsFromLoginusertrackOK()) {
      
        # Show instructions.
        if (!t3lib_div::GPvar('transferData')) {
        
          $count = $this->getNumberOfRowsFromLoginusertrack();
				
          $onClick = "document.location='".t3lib_div::linkThisScript(array('transferData' => 1))."'; return false;";

				  return 'There are '.$count.' rows in the table "tx_loginusertrack_stat" which belong to the extension loginusertrack. 
				      You can transfer all the rows into the feuserloginsystem extension. Do you want to do it now?

		          <form action=""><input type="submit" value="DO IT" onclick="'.htmlspecialchars($onClick).'"></form>
              ';
			
       # Finally, transfer all the rows into tx_feuserloginsystem_userstatistics.	
			 } else {
				  
          $count = $this->transferRowsFromLoginusertrack();
          
          return sprintf('%s rows transfered.', $count );
          
        }
      }
      
      
		}
		
		/**
    * Check if the UPDATE! menu item may be shown in the menu of the extension.              
    *
    * @return boolean     Show UPDATE! menu or not.
    * @author Andre Obereigner <feuserloginsystem@obereigner.de>
    */
		function access() {
			
			# Check if transfering of rows from tx_loginusertrack_stat is okay and has not been done yet.
			$transferRowsOK = $this->transferingRowsFromLoginusertrackOK();			
      
      return ($transferRowsOK) ? TRUE : FALSE;    
			
		}
		
		/**
    * Transfer all rows from tx_loginusertrack_stat into the table
    * tx_feuserloginsystem_userstatistics.              
    *
    * @return integer     Number of transfered rows.
    * @author Andre Obereigner <feuserloginsystem@obereigner.de>
    */
		function transferRowsFromLoginusertrack() {
		  
		  # Get all the rows from tx_loginusertrack_stat.
		  $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
        '*',
        'tx_loginusertrack_stat',
        ''
      );
      
      if ($GLOBALS['TYPO3_DB']->sql_error() ) {
				t3lib_div::debug(array('SQL error:',
				$GLOBALS['TYPO3_DB']->sql_error() ) );
			}
			
			# Transfer each row into the table tx_feuserloginsystem_userstatistics.
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) ) {
    					
        # Get information for login log.		
		    $insertDataArray = array();
		    $insertDataArray['feuserid']      = $row['fe_user'];
		    $insertDataArray['sessionstart']  = $row['session_login'];
		    $insertDataArray['lastpageview']  = $row['last_page_hit'];
		    $insertDataArray['pagecounter']   = $row['session_hit_counter'];
		    $insertDataArray['pagetracking']  = serialize(array());

        # Transfer statistic entry into own database.
        $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_feuserloginsystem_userstatistics',$insertDataArray);

      }
      
      # Get the number of transfered rows.
      $count = $GLOBALS['TYPO3_DB']->sql_num_rows($res );
      
      return $count;
			
		}
		
		/**
    * Return the number of existing rows in tx_loginusertrack_stat.          
    *
    * @return integer     Number of rows.
    * @author Andre Obereigner <feuserloginsystem@obereigner.de>
    */
		function getNumberOfRowsFromLoginusertrack() {
      
      $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
        '*',
        'tx_loginusertrack_stat',
        ''
      );
      
      $count = $GLOBALS['TYPO3_DB']->sql_num_rows($res );
      
      return $count;
    }
		
		/**
    * Check if the administrator may see the Backup Menu Item for 
    * transfering the rows from tx_loginusertrack_stat into
    * tx_feuserloginsystem_userstatistics.            
    *
    * @return boolean 
    * @author Andre Obereigner <feuserloginsystem@obereigner.de>
    */
	 function transferingRowsFromLoginusertrackOK() {
    
      # Get all existing tables in the database
      # in order to check if the table tx_loginusertrack_stat exists.
      $dbTables = array();
			$dbTables = $GLOBALS['TYPO3_DB']->admin_get_tables();
			
			$countLoginusertrack = 0;
      $countFeuserloginsystem = 0;
			
			# If tables exists, select all rows.
			if(array_key_exists('tx_loginusertrack_stat',$dbTables)) {  			
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
          '*',
          'tx_loginusertrack_stat',
          ''
        );  
        
        # Get the number of rows in tx_loginusertrack_stat.
        $countLoginusertrack = $GLOBALS['TYPO3_DB']->sql_num_rows($res );
    
        # Select all rows from tx_feuserloginsystem_userstatistics.
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
          '*',
          'tx_feuserloginsystem_userstatistics',
          ''
        );  
        # Get the number of rows in tx_feuserloginsystem_userstatistics.    
        $countFeuserloginsystem = $GLOBALS['TYPO3_DB']->sql_num_rows($res );
          
			}      
      
      # Transfering rows is OKAY if there are rows in tx_loginusertrack_stat and
      # if there are more rows in tx_loginusertrack_stat than in tx_feuserloginsystem_userstatistics 
      # (that would mean that no rows were transfered yet).
      return ($countLoginusertrack > 0 AND $countLoginusertrack > $countFeuserloginsystem) ? TRUE : FALSE;
    }

	}

	// Include extension?
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feuserloginsystem/class.ext_update.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feuserloginsystem/class.ext_update.php']);
	}

?>
