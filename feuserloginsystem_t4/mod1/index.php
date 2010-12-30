<?php
declare(encoding = 'utf-8');

/***************************************************************
*  Copyright notice
*
*  (c) 2006 Andre Obereigner <andre@obereigner.de>
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


	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

$LANG->includeLLFile('EXT:feuserloginsystem/mod1/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * Module 'Loginsystem' for the 'feuserloginsystem' extension.
 *
 * @author	Andre Obereigner <andre@obereigner.de>
 * @package	TYPO3
 * @subpackage	tx_feuserloginsystem
 */
class  tx_feuserloginsystem_module1 extends t3lib_SCbase {
				var $pageinfo;

				/**
				* Initializes the Module
				* 				
				* @return	void
				*/
        function init()	{
				  global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

				  parent::init();

				  /*
				  if (t3lib_div::_GP('clear_all_cache'))	{
					 $this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
				  }
				  */
        }

				/**
				* Adds items to the ->MOD_MENU array. Used for the function menu selector.
				*
				* @return	void
				*/
				function menuConfig()	{
					global $LANG;
					$this->MOD_MENU = Array (
						'function' => Array (
							'1' => $LANG->getLL('function1'),
							'2' => $LANG->getLL('function2'),
						)
					);
					parent::menuConfig();
				}

				/**
				* Main function of the module. Write the content to $this->content
				* If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
				*
				* @return	[type]		...
				*/
        function main()	{
		      global $AB,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$HTTP_GET_VARS,$HTTP_POST_VARS,$CLIENT,$TYPO3_CONF_VARS;
		
		      // Access check!
		      // The page will show only if there is a valid page and if this page may be viewed by the user
		      $this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		      $access = is_array($this->pageinfo) ? 1 : 0;
		
		      if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id) || ($BE_USER->user["uid"] && !$this->id))	{
	
				    // Draw the header.
			      $this->doc = t3lib_div::makeInstance('mediumDoc');
            $this->doc->backPath = $BACK_PATH;
            $this->doc->form='<form action="" method="POST">';

            // JavaScript
            $this->doc->JScode = '
              <script language="javascript">
              script_ended = 0;
              function jumpToUrl(URL)	{
                document.location = URL;
              }
				      </script>
            ';
            $this->doc->postCode='
            <script language="javascript">
              script_ended = 1;
              if (top.theMenu) top.theMenu.recentuid = '.intval($this->id).';
            </script>
            ';

            $headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br>'.$LANG->php3Lang['labels']['path'].': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

            $this->content.=$this->doc->startPage($LANG->getLL('title'));
            $this->content.=$this->doc->header($LANG->getLL('title'));
            $this->content.=$this->doc->spacer(5);
            $this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
            $this->content.=$this->doc->divider(5);

			
            // Render content:
            $this->moduleContent();

			
            // ShortCut
            if ($BE_USER->mayMakeShortcut())	{
              $this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
            }
		
            $this->content.=$this->doc->spacer(10);
            
          } else {
            // If no access or if ID == zero
		
            $this->doc = t3lib_div::makeInstance('mediumDoc');
            $this->doc->backPath = $BACK_PATH;
		
            $this->content.=$this->doc->startPage($LANG->getLL('title'));
            $this->content.=$this->doc->header($LANG->getLL('title'));
            $this->content.=$this->doc->spacer(5);
            $this->content.=$this->doc->spacer(10);
		      }
        }

				/**
        * Prints out the module content HTML.              
        *
        * @return void        
        */
				function printContent()	{
          global $SOBE;
          
          $this->content.=$this->doc->middle();
					$this->content.=$this->doc->endPage();
					echo $this->content;
				}

				/**
        * Generates the module content.              
        *
        * @return void        
        */
				function moduleContent() {
				global $LANG;
				
				  $userId=intval(t3lib_div::GPvar('useruid'));
				  
					switch((string)$this->MOD_SETTINGS['function'])	{
						case 1:
							$content = $this->listView();
							$this->content .= $this->doc->section($LANG->getLL('listViewMessage'),$content,0,1);
						break;
						case 2:
							$content = $this->userView();
							$this->content .= $this->doc->section($LANG->getLL('userViewMessage'),$content,0,1);
						break;
						case 3:
							$content='<div align=center><strong>Menu item #3...</strong></div>';
							$this->content .= $this->doc->section('Message #3:',$content,0,1);
						break;
					}
				}
				
				/**
        * Generate the content for the list view.              
        *
        * @return string    The generated content.        
        */
        function listView() {
          global $LANG;
          
          $tempContent = '';
          
          $yearSelection = t3lib_div::_GP('feuserls_yearselection');
          $monthSelection = t3lib_div::_GP('feuserls_monthselection');
          $ignorePageSelection = t3lib_div::_GP('feuserls_ignorepageselection');
          
          $yearSelection = $yearSelection ? $yearSelection : date('Y',time());
          $monthSelection = $monthSelection ? $monthSelection : date('n',time());
          
          list($minTimeStamp, $maxTimeStamp) = $this->getTimePeriodOfSelection($monthSelection,$yearSelection);
            
          $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'tx_feuserloginsystem_userstatistics.sessionstart AS sessionstart,
                tx_feuserloginsystem_userstatistics.lastpageview AS lastpageview,   
                tx_feuserloginsystem_userstatistics.pagecounter AS pagecounter,
                fe_users.username AS username,
                fe_users.name AS name',
            'tx_feuserloginsystem_userstatistics,fe_users',
            'tx_feuserloginsystem_userstatistics.feuserid = fe_users.uid' .
                ($ignorePageSelection ? '' : ' AND fe_users.pid ='.$this->id) .
                ' AND tx_feuserloginsystem_userstatistics.sessionstart >= '. $minTimeStamp .
                ' AND tx_feuserloginsystem_userstatistics.sessionstart <= '. $maxTimeStamp .
                t3lib_BEfunc::deleteClause('fe_users'),
            '',
            'sessionstart DESC ',
            '1000'
          );  	
          
          $tempContent .= '<br />';
          $tempContent .= $this->getPeriodSelector('3');
          $tempContent .= '<br /><br />' . $LANG->getLL('periodChoosingInstruction');
          $tempContent .= '<br /><br />';
          $tempContent .= '<input type="checkbox" name="feuserls_ignorepageselection" value="1"> ' . $LANG->getLL('ignorePageSelection');
          $tempContent .= '<br /><br />';
          $tempContent .= '<strong>'. $LANG->getLL('timePeriod'). ': '.date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'],$minTimeStamp).' - '.date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'],$maxTimeStamp).'</strong>';
          $tempContent .= '<br /><br />';
          $tempContent .= '<table border="1" cellpadding="2" cellspacing="0">';
          $tempContent .= '<tr>
                <td nowrap><strong>'.$LANG->getLL('loginOnAt').':</strong></td>
                <td nowrap><strong>'.$LANG->getLL('user').':</strong></td>
                <td nowrap><strong>'.$LANG->getLL('name').':</strong></td>
                <td nowrap><strong>'.$LANG->getLL('visitedPages').':</strong></td>
                <td nowrap><strong>'.$LANG->getLL('durationOfStay').':</strong></td>
                <td nowrap><strong>'.$LANG->getLL('timeThatPassedBy').':</strong></td>    
            </tr>';
          while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $tempContent .= '<tr>
                <td nowrap>'.date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'].' '.$GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'],$row['sessionstart']).'</td>
                <td nowrap>'.($row['username'] ? $row['username'] : "&nbsp;").'</td>
                <td nowrap>'.($row['name'] ? $row['name'] : "&nbsp;").'</td>
                <td nowrap>'.$row['pagecounter'].'</td>
                <td nowrap>'.t3lib_BEfunc::calcAge($row['lastpageview']-$row['sessionstart'],$GLOBALS["LANG"]->sL("LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears")).'</td>
                <td nowrap>'.t3lib_BEfunc::calcAge(time()-$row['sessionstart'],$GLOBALS["LANG"]->sL("LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears")).'</td>
            </tr>';
          }
          $tempContent .= '</table>';
        
          return $tempContent;
          
        }
        
        /**
        * Generates the content for the user view.              
        *
        * @return string    The generated content.        
        */
        function userView() {
          global $LANG;
         
          $tempContent = '';
          
          $yearSelection = t3lib_div::_GP('feuserls_yearselection');
          $monthSelection = t3lib_div::_GP('feuserls_monthselection');
          $ignorePageSelection = t3lib_div::_GP('feuserls_ignorepageselection');
          
          $yearSelection = $yearSelection ? $yearSelection : date('Y',time());
          $monthSelection = $monthSelection ? $monthSelection : 'ALL';
          
          $tempContent .= '<br />';
          $tempContent .= $this->getPeriodSelector('3');
          $tempContent .= '<br /><br />' . $LANG->getLL('periodChoosingInstruction');
          $tempContent .= '<br /><br />';
          $tempContent .= '<input type="checkbox" name="feuserls_ignorepageselection" value="1"> ' . $LANG->getLL('ignorePageSelection');
          $tempContent .= '<br /><br />';
          
          list($minTimeStamp, $maxTimeStamp) = $this->getTimePeriodOfSelection($monthSelection,$yearSelection);
            
          $firstYear = date('Y', $minTimeStamp);
          $lastYear = date('Y', $maxTimeStamp);
            
            
          for($year = $lastYear; $year >= $firstYear; $year--) {
            
            $tempContent .= '<h4>'.strtoupper($LANG->getLL('year')).': '.$year.'</h4>';
            $tempContent .= '<table border="1">';
            
            $firstMonth = date('n', $minTimeStamp);
            $lastMonth = date('n', $maxTimeStamp);
              
            for($month = $lastMonth; $month >= $firstMonth; $month--) {
              
              list($minTime, $maxTime) = $this->getTimePeriodOfSelection($month,$year);
              
              $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                'DISTINCT fe_users.username AS username,
                    fe_users.name AS name,
                    fe_users.uid AS uid,
                    MIN(tx_feuserloginsystem_userstatistics.sessionstart) AS firstlogin,
                    MAX(tx_feuserloginsystem_userstatistics.lastpageview) AS lastpageview,
                    SUM(tx_feuserloginsystem_userstatistics.pagecounter) AS pagecounter',
                'tx_feuserloginsystem_userstatistics,fe_users',
                'tx_feuserloginsystem_userstatistics.feuserid = fe_users.uid' .
                    ($ignorePageSelection ? '' : ' AND fe_users.pid ='.$this->id) .
                    ' AND tx_feuserloginsystem_userstatistics.sessionstart >= '. $minTime .
                    ' AND tx_feuserloginsystem_userstatistics.sessionstart <= '. $maxTime .
                    t3lib_BEfunc::deleteClause('fe_users'),
                'username',
                'lastpageview DESC ',
                '1000'
              );
              
              #$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
              
              $tempContent .= '<tr><td colspan="5"><strong>'.strtoupper($LANG->getLL('month')).': '.date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'],$minTime).' - '.date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'],$maxTime).'</strong></td></tr>';
              $tempContent .= '<tr>
                    <td nowrap><strong>'.$LANG->getLL('lastPageViewOnAt').':</strong></td>
                    <td nowrap><strong>'.$LANG->getLL('user').':</strong></td>
                    <td nowrap><strong>'.$LANG->getLL('name').':</strong></td>
                    <td nowrap><strong>'.$LANG->getLL('visitedPages').':</strong></td>
                    <td nowrap><strong>'.$LANG->getLL('durationOfStay').':</strong></td>
                </tr>';
              while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                
                $res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                  'tx_feuserloginsystem_userstatistics.sessionstart AS sessionstart,
                      tx_feuserloginsystem_userstatistics.lastpageview AS lastpageview',
                  'tx_feuserloginsystem_userstatistics,fe_users',
                  'tx_feuserloginsystem_userstatistics.feuserid = fe_users.uid' .
                      ($ignorePageSelection ? '' : ' AND fe_users.pid ='.$this->id) .
                      ' AND fe_users.uid ='.$row['uid'] .
                      ' AND tx_feuserloginsystem_userstatistics.sessionstart >= '. $minTime .
                      ' AND tx_feuserloginsystem_userstatistics.sessionstart <= '. $maxTime .
                      t3lib_BEfunc::deleteClause('fe_users'),
                  '',
                  '',
                  ''
                );
                $spentTime = 0;
                while($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2)) {
                  $spentTime += $row2['lastpageview'] - $row2['sessionstart'];
                }
                 
                $tempContent .= '<tr>
                    <td nowrap>'.date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'].' '.$GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'],$row['lastpageview']).'</td>
                    <td nowrap>'.($row['username'] ? $row['username'] : "&nbsp;").'</td>
                    <td nowrap>'.($row['name'] ? $row['name'] : "&nbsp;").'</td>
                    <td nowrap>'.$row['pagecounter'].'</td>
                    <td nowrap>'.t3lib_BEfunc::calcAge($spentTime,$GLOBALS["LANG"]->sL("LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears")).'</td>
                  </tr>';
              }
              $tempContent .= '<tr><td colspan="5">&nbsp;</td></tr>';
             
            }
              
            $tempContent .= '</table>';
            $tempContent .= '<br /><br />';
            
          }
      
          return $tempContent;
        }
        
        /**
        * This method returns an array with the first year and the last year
        * recorded in the tx_feuserloginsystem_userstatistics database table.                 
        *
        * @return array    Returns an array ($firstYear,$lastYear)        
        */
        function getFirstAndLastYearOfStatistics() {
          
          $ignorePageSelection = t3lib_div::_GP('feuserls_ignorepageselection');
          
          $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'MIN(tx_feuserloginsystem_userstatistics.sessionstart) AS firstlogin,
                MAX(tx_feuserloginsystem_userstatistics.lastpageview) AS lastpageview',
            'tx_feuserloginsystem_userstatistics,fe_users',
            'tx_feuserloginsystem_userstatistics.feuserid = fe_users.uid' .
                ($ignorePageSelection ? '' : ' AND fe_users.pid ='.$this->id) .
                t3lib_BEfunc::deleteClause('fe_users')
          );
          
          $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

          if($row['firstlogin'] != '' AND $row['lastpageview'] != '') {
            $firstYear = date('Y', $row['firstlogin']);
            $lastYear = date('Y', $row['lastpageview']);
          } else {
            $firstYear = 0;
            $lastYear = 0;
          }          
            
          return array($firstYear,$lastYear);
          
        }
        
        /**
        * This method returns an array with two time stamps which describe
        * a time range between $minTimeStamp and $maxTimeStamp. These two
        * time stamps are generated by submitted month and year in order
        * to limit the table rows.                                 
        *
        * @return array    Returns an array ($minTimeStamp,$maxTimeStamp)        
        */
        function getTimePeriodOfSelection($month, $year) {
  
          if(($month == 'ALL' AND $year == 'ALL') OR 
             (is_numeric($month) AND $year == 'ALL')) {

            list($firstYear, $lastYear) = $this->getFirstAndLastYearOfStatistics();
          
            $minTimeStamp = mktime(0,0,0,1,1,$firstYear);
            $maxTimeStamp = mktime(0,0,0,1,0,$lastYear+1);
            
            return array($minTimeStamp,$maxTimeStamp);
          
          }
          
          if($month == 'ALL' AND is_numeric($year)) {
  
            $minTimeStamp = mktime(0,0,0,1,1,$year);
            $maxTimeStamp = mktime(0,0,0,1,0,$year+1);
            
            return array($minTimeStamp,$maxTimeStamp);
            
          }
          
          if(is_numeric($month) AND is_numeric($year)) {
         
            $minTimeStamp = mktime(0,0,0,$month,1,$year);
            $maxTimeStamp = mktime(23,59,59,$month+1,0,$year);
            
            return array($minTimeStamp,$maxTimeStamp);
            
          }
          
        }
				
				/**
        * This method returns a selectorbox with all the years found in
        * the database table tx_feuserloginsystem_userstatistics.                                        
        *
        * @return string    Selectorbox with years.      
        */
        function getYEARSelectorBox()	{
      
          list($firstYear, $lastYear) = $this->getFirstAndLastYearOfStatistics();
          
          $optionYear = array();
          $optionYear[] = '<option value="">[ Select Year ]</option>';
          if($firstYear != 0 AND $lastYear != 0) {
            for($a = $firstYear; $a <= $lastYear; $a++) {
              $optionYear[] = '<option value="'.$a.'">'.$a.'</option>';
            }
            $optionYear[] = '<option value="ALL">ALL</option>';
          }
          $selectorYear = '<select name="feuserls_yearselection">'.implode('',$optionYear).'</select>';
      
          return $selectorYear;
      
        }
        
        /**
        * This method returns a selectorbox with months.                                       
        *
        * @return string    Selectorbox with years.      
        */
        function getMonthSelectorBox()	{
      
          list($firstYear, $lastYear) = $this->getFirstAndLastYearOfStatistics();
      
          $optionMonth = array();
          $optionMonth[] = '<option value="">[ Select Month ]</option>';
          if($firstYear != 0 AND $lastYear != 0) {
            $optionMonth[] = '<option value="ALL">ALL</option>';
            for($a = 1; $a <= 12; $a++) {
              $optionMonth[] = '<option value="'.$a.'">'.$a.'</option>';
            }
          }
          $selectorMonth = '<select name="feuserls_monthselection">'.implode('',$optionMonth).'</select>';
      
          return $selectorMonth;
      
        }
        
        /**
        * This method returns (a) selectorbox(es) with an additional button
        * in order to send the request to show the selection.                                       
        *
        * @return string    Selectorbox(es) with a button to show the selection.      
        */
        function getPeriodSelector($type) {
          # type 1: month selector box only
          # type 2: year selector box only
          # type 3: both selector boxes
        
          #$onClick = "document.location='".t3lib_div::linkThisScript(array('showSelection' => 1))."'; return false;";
        
          switch($type)	{
						case '1':
							$tempContent = '';
							$tempContent .= '<form action="'.t3lib_div::linkThisScript().'" method="POST">'.$this->getMonthSelectorBox().'<input type="submit" value="Show Selection"><input type="submit" value="Show Selection"><input type="hidden" name="showSelection" value="1"></form>';
							
							return $tempContent;
						break;
						case 2:
							$tempContent = '';
							$tempContent .= '<form action="'.t3lib_div::linkThisScript().'" method="POST">'.$this->getYearSelectorBox().'<input type="submit" value="Show Selection"><input type="submit" value="Show Selection"><input type="hidden" name="showSelection" value="1"></form>';
							
							return $tempContent;
						break;
						case 3:
							$tempContent = '';
							$tempContent .= '<form action="'.t3lib_div::linkThisScript().'" method="POST">'.$this->getYearSelectorBox().$this->getMonthSelectorBox().'<input type="submit" value="Show Selection"><input type="hidden" name="showSelection" value="1"></form>';
							
							return $tempContent;
						break;
					}
					
        }
				
			}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feuserloginsystem/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feuserloginsystem/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_feuserloginsystem_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>
