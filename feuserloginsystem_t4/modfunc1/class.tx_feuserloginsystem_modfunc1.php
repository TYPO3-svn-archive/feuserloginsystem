<?php
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




/**
 * Module extension (addition to function menu) 'Loginsystem' for the 'feuserloginsystem' extension.
 *
 * @author	Andre Obereigner <andre@obereigner.de>
 * @package	TYPO3
 * @subpackage	tx_feuserloginsystem
 */
class tx_feuserloginsystem_modfunc1 extends mod_user_task {
					/**
					 * Makes the content for the overview frame...
					 *
					 * @return	HTML
					 */
					function overview_main()	{
						$icon = '<img src="'.$this->backPath.t3lib_extMgm::extRelPath("feuserloginsystem").'ext_icon.gif" width=18 height=16 class="absmiddle">';
						$content = $this->mkMenuConfig($icon.$this->headLink(tx_feuserloginsystem_modfunc1,1),'',$this->overviewContent());
						
						return $content;
					}

					/**
					 * Main method
					 *
					 * @return	HTML
					 */
					function main() {
						global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

						return $this->mainContent();
					}

					/**
					 * Returns content in overview frame
					 *
					 * @return	Content for overview frame
					 */
					function overviewContent()	{
						$content = 'Content in overview frame...';  
						return '<a href="index.php?SET[function]=tx_feuserloginsystem_modfunc1"  onClick="this.blur();"><img src="'.$this->backPath.'gfx/edit2.gif" style="float: left;"></a><div><a href="index.php?SET[function]=tx_feuserloginsystem_modfunc1"  onClick="this.blur();">'.$content.'</a></div>';
					}

					/**
					 * Main content method
					 *
					 * @return	Main content for the module
					 */
					function mainContent()	{
						return "Content in main frame...";
					}
				}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feuserloginsystem/modfunc1/class.tx_feuserloginsystem_modfunc1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feuserloginsystem/modfunc1/class.tx_feuserloginsystem_modfunc1.php']);
}

?>