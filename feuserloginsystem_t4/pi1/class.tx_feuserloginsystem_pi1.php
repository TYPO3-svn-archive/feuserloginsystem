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

require_once(PATH_tslib.'class.tslib_pibase.php');

 /**
 * Plugin 'FeUserLoginsystem' for the 'feuserloginsystem' extension.
 *
 * @version     $Id$
 * @package     TYPO3
 * @subpackage  tx_feuserloginsystem
 * @copyright   Copyright belongs to the respective authors
 * @author      Andre Obereigner <feuserloginsystem@obereigner.de>
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

class tx_feuserloginsystem_pi1 extends tslib_pibase {

  /**
  * Same as class name.
  *
  * @var String
  */
	var $prefixId      = 'tx_feuserloginsystem_pi1';

	/**
	* Path to this script relative to the extension dir.
	*
  * @var String
  */
	var $scriptRelPath = 'pi1/class.tx_feuserloginsystem_pi1.php';

	/**
	* The extension key.
	*
  * @var String
  */
	var $extKey        = 'feuserloginsystem';

  /**
  * If set, then caching is disabled if piVars are incoming while no cHash was set.
  *
  * @var Boolean
  */
	var $pi_checkCHash = true;

	/**
  * The temporary PlugIn configuration. This variable will be unset
  * after for a certain plugin mode necessary information were loaded into $config.
  *
  * @var Array
  */
	var $tempConf;

	/**
  * PlugIn configuration. This variable contains all necessary
  * information to execute a certain plugin mode like "smallLogin" or "passwordRecovery" .
  *
  * @var Array
  */
  var $config;

	/**
  * Instance of the class "tslib_cObj" which contains some useful methods.
  *
  * @var tslib_cObj
  */
	var $local_cObj;

	/**
  * Pre-Configuration for the typolinks.
  *
  * @var array
  */
	var $typolink_conf;

	/**
  * If a user is logged in, $userIsOnline is set to "1".
  *
  * @var string
  */
	var $userIsOnline;

	/**
	* Contains the freecap object if the extension "sr_freecap" is installed
	*
	* @var tx_srfreecap_pi2
	*/
	var $freeCap;

	/**
	* Contains the current module
	*
	* @var String
	*/
	var $module;

	/**
	* Contains the path to the extension's upload folder
	*
	* @var String
	*/
	var $uploadFolder;



  /**
  * The main method of the PlugIn
  *
  * @param  string    $content          The PlugIn content
  * @param  array     $conf             The PlugIn configuration
  * @return string                      The content that is displayed on the website
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
	function main($content, $conf)	{
	  global $TSFE, $TCA, $TYPO3_CONF_VARS, $LOCAL_LANG;

		$this->tempConf = $conf;
		$this->local_cObj = t3lib_div::makeInstance('tslib_cObj');

    $this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		$this->uploadFolder = $TYPO3_CONF_VARS['EXTCONF'][$this->extKey]['uploadFolder'];

		# Get the flexform data of the plugin.
		$this->pi_initPIflexForm();

		# Get important variables that are necessary to run this script.
    $this->initalizeGeneralSettings();
		$this->initalizeLogoutButtonSettings();
		$this->initalizeRegularLoginboxSettings();
		$this->initalizeSmallLoginboxSettings();
		$this->initalizePasswordRecoverySettings();
		$this->initalizePasswordForcedChangeSettings();
		$this->initalizeLoginProtectionSettings();
		#unset($this->tempConf);

		# Select action which shall be performed
    switch ($this->config['code']) {
      # REGULAR LOGINBOX
		  case 'regularlogin':
		  	$this->module = 'regularlogin';
        $content .= $this->regularLogin();
			break;
			# SMALL LOGINBOX
			case 'smalllogin':
				$this->module = 'smalllogin';
			  $content .= $this->smallLogin();
			break;
			# PASSWORD RECOVERY
			case 'passwordrecovery':
				$this->module = 'passwordrecovery';
				if($this->config['passwordRecovery.']['enable']) {
			  	$content .= $this->passwordRecovery();
			  }
			break;
			# DEFAULT IF NO CODE
			# ERROR MESSAGE
		  default:
		    $content .= 'What shall I do! Select smalllogin, regularlogin or passwordrecovery!';
		}

	  #t3lib_div::debug($this->tempConf);
		#echo "<br /><br />";
    #t3lib_div::debug($this->config);
	  #echo "<br /><br />";
		#t3lib_div::debug($GLOBALS["TSFE"]);
		#echo "<br /><br />";

		return $this->pi_wrapInBaseClass($content);
	}

	/**
  * Initalizing of the settings for the LogoutButton (selectable in PageTyp).
  *
  * @return void
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
	function initalizeLogoutButtonSettings() {

  }

  /**
  * Initalizing of the settings for the module "RegularLoginbox".
  *
  * @return void
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
  function initalizeRegularLoginboxSettings() {

  	# Check if permalogin is enabled or disabled.
  	$ffShowPermalogin = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showPermalogin', 'sRegularLoginbox');
  	if($ffShowPermalogin == '' OR $ffShowPermalogin == 'typoscript') {
			$showPermalogin = $this->tempConf['regularLoginbox.']['showPermalogin'];
			$this->config['regularLoginbox.']['showPermalogin'] = intval($showPermalogin) ? intval($showPermalogin) : intval(0);
		} elseif($ffShowPermalogin != 'typoscript') {
			$showPermalogin = $ffShowPermalogin;
			$this->config['regularLoginbox.']['showPermalogin'] = $showPermalogin ? intval($showPermalogin) : intval(0);
		}

		# Check if the PasswordRecovery link shall be displayed or not.
		$ffShowPwRecoveryLink = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showPwRecoveryLink', 'sRegularLoginbox');
		if($ffShowPwRecoveryLink == '' OR $ffShowPwRecoveryLink == 'typoscript') {
			$showPwRecoveryLink = $this->tempConf['regularLoginbox.']['showPwRecoveryLink'];
			$this->config['regularLoginbox.']['showPwRecoveryLink'] = intval($showPwRecoveryLink) ? intval($showPwRecoveryLink) : intval(0);
		} elseif($ffShowPwRecoveryLink != 'typoscript') {
			$showPwRecoveryLink = $ffShowPwRecoveryLink;
			$this->config['regularLoginbox.']['showPwRecoveryLink'] = $showPwRecoveryLink ? intval($showPwRecoveryLink) : intval(0);
		}

		# Get the "pwRecoveryPID" (containing the Regular Loginbox) that tells us
    # where to display the template for "Forgot Password".
    # Especially needed if SmallLoginbox is displayed.
    # Standard: current page ID
    $pwRecoveryPID = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pwRecoveryPID', 'sRegularLoginbox');
    $pwRecoveryPID = $pwRecoveryPID ? $pwRecoveryPID : $this->tempConf['regularLoginbox.']['pwRecoveryPID'];
    $this->config['regularLoginbox.']['pwRecoveryPID'] = $pwRecoveryPID ? intval($pwRecoveryPID) : intval(0);

  	# Check if the header shall be displayed or not.
  	$ffHideHeader = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'hideHeader', 'sRegularLoginbox');
		if($ffHideHeader == '' OR $ffHideHeader == 'typoscript') {
			$hideHeader = $this->tempConf['regularLoginbox.']['hideHeader'];
			$this->config['regularLoginbox.']['hideHeader'] = intval($hideHeader) ? intval($hideHeader) : intval(0);
		} elseif($ffHideHeader != 'typoscript') {
			$hideHeader = $ffHideHeader;
			$this->config['regularLoginbox.']['hideHeader'] = $hideHeader ? intval($hideHeader) : intval(0);
		}

		# Check if the message shall be displayed or not.
		$ffHideMessage = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'hideMessage', 'sRegularLoginbox');
		if($ffHideMessage == '' OR $ffHideMessage == 'typoscript') {
			$hideMessage = $this->tempConf['regularLoginbox.']['hideMessage'];
			$this->config['regularLoginbox.']['hideMessage'] = intval($hideMessage) ? intval($hideMessage) : intval(0);
		} elseif($ffHideMessage != 'typoscript') {
			$hideMessage = $ffHideMessage;
			$this->config['regularLoginbox.']['hideMessage'] = $hideMessage ? intval($hideMessage) : intval(0);
		}

		# Check if a error message for a user who has been disabled the by the system/admin shall be shown.
		$ffShowSysDisabledUserErrMsg = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showSysDisabledUserErrMsg', 'sRegularLoginbox');
		if($ffShowSysDisabledUserErrMsg == '' OR $ffShowSysDisabledUserErrMsg == 'typoscript') {
			$showSysDisabledUserErrMsg = $this->tempConf['regularLoginbox.']['showSysDisabledUserErrMsg'];
			$this->config['regularLoginbox.']['showSysDisabledUserErrMsg'] = intval($showSysDisabledUserErrMsg) ? intval($showSysDisabledUserErrMsg) : intval(0);
		} elseif($ffShowSysDisabledUserErrMsg != 'typoscript') {
			$showSysDisabledUserErrMsg = $ffShowSysDisabledUserErrMsg;
			$this->config['regularLoginbox.']['showSysDisabledUserErrMsg'] = $showSysDisabledUserErrMsg ? intval($showSysDisabledUserErrMsg) : intval(0);
		}

		# Check if a error message shall be shown if a username was submitted which does not exist.
		$ffShowNotExistingUserErrMsg = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showNotExistingUserErrMsg', 'sRegularLoginbox');
		if($ffShowNotExistingUserErrMsg == '' OR $ffShowNotExistingUserErrMsg == 'typoscript') {
			$showNotExistingUserErrMsg = $this->tempConf['regularLoginbox.']['showNotExistingUserErrMsg'];
			$this->config['regularLoginbox.']['showNotExistingUserErrMsg'] = intval($showNotExistingUserErrMsg) ? intval($showNotExistingUserErrMsg) : intval(0);
		} elseif($ffShowNotExistingUserErrMsg != 'typoscript') {
			$showNotExistingUserErrMsg = $ffShowNotExistingUserErrMsg;
			$this->config['regularLoginbox.']['showNotExistingUserErrMsg'] = $showNotExistingUserErrMsg ? intval($showNotExistingUserErrMsg) : intval(0);
		}

		$this->config['regularLoginbox.']['_LOCAL_LANG.'] = $this->tempConf['regularLoginbox.']['_LOCAL_LANG.'];

  }

  /**
  * Initalizing of the settings for the module "SmallLoginbox".
  *
  * @return void
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
  function initalizeSmallLoginboxSettings() {

		# Check if permalogin is enabled or disabled.
		$ffShowPermalogin = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showPermalogin', 'sSmallLoginbox');
		if($ffShowPermalogin == '' OR $ffShowPermalogin == 'typoscript') {
			$showPermalogin = $this->tempConf['smallLoginbox.']['showPermalogin'];
			$this->config['smallLoginbox.']['showPermalogin'] = intval($showPermalogin) ? intval($showPermalogin) : intval(0);
		} elseif($ffShowPermalogin != 'typoscript') {
			$showPermalogin = $ffShowPermalogin;
			$this->config['smallLoginbox.']['showPermalogin'] = $showPermalogin ? intval($showPermalogin) : intval(0);
		}

		# Check if the PasswordRecovery link shall be displayed or not.
		$ffShowPwRecoveryLink = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showPwRecoveryLink', 'sSmallLoginbox');
		if($ffShowPwRecoveryLink == '' OR $ffShowPwRecoveryLink == 'typoscript') {
			$showPwRecoveryLink = $this->tempConf['smallLoginbox.']['showPwRecoveryLink'];
			$this->config['smallLoginbox.']['showPwRecoveryLink'] = intval($showPwRecoveryLink) ? intval($showPwRecoveryLink) : intval(0);
		} elseif($ffShowPwRecoveryLink != 'typoscript') {
      $showPwRecoveryLink = $ffShowPwRecoveryLink;
			$this->config['smallLoginbox.']['showPwRecoveryLink'] = $showPwRecoveryLink ? intval($showPwRecoveryLink) : intval(0);
		}

		# Get the "pwRecoveryPID" that tells us
    # where to display the template for "Forgot Password".
    # Especially needed if SmallLoginbox is displayed.
    # Standard: current page ID
    $pwRecoveryPID = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pwRecoveryPID', 'sSmallLoginbox');
    $pwRecoveryPID = $pwRecoveryPID ? $pwRecoveryPID : $this->tempConf['smallLoginbox.']['pwRecoveryPID'];
    $this->config['smallLoginbox.']['pwRecoveryPID'] = $pwRecoveryPID ? intval($pwRecoveryPID) : intval(0);

    # Get the "regularLoginPID" (containing the Regular Loginbox) that tells us
    # where to display the template for the "RegularLoginbox".
    # Especially needed if SmallLoginbox is displayed.
    # Standard: current page ID
    $regularLoginPID = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'regularLoginPID', 'sSmallLoginbox');
    $regularLoginPID = $regularLoginPID ? $regularLoginPID : $this->tempConf['smallLoginbox.']['regularLoginPID'];
    $this->config['smallLoginbox.']['regularLoginPID'] = $regularLoginPID ? intval($regularLoginPID) : intval(0);

    $this->config['smallLoginbox.']['_LOCAL_LANG.'] = $this->tempConf['smallLoginbox.']['_LOCAL_LANG.'];

  }

  /**
  * Initalizing of the settings for the module "PasswordRecovery".
  *
  * @return void
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
  function initalizePasswordRecoverySettings() {

  	# Check if the Forgot-Password-Feature is enabled or disabled and thus activate Password Recovery.
    # Standard: 0 (= disabled).
    $ffEnable = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'enable', 'sPasswordRecovery');
		if($ffEnable == '' OR $ffEnable == 'typoscript') {
			$enable = $this->tempConf['passwordRecovery.']['enable'];
			$this->config['passwordRecovery.']['enable'] = $enable ? intval($enable) : intval(0);
		} elseif($ffEnable != 'typoscript') {
			$enable = $ffEnable;
			$this->config['passwordRecovery.']['enable'] = $enable ? intval($enable) : intval(0);
		}

		# Get the "mode" for PasswordRecovery that tells us how to recover the user's password.
    # Standard: currentPassword
    $ffPwRecoveryMode = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'mode', 'sPasswordRecovery');
		if(empty($ffPwRecoveryMode) OR $ffPwRecoveryMode == 'typoscript') {
			$pwRecoverMode = $this->tempConf['passwordRecovery.']['mode'];
			$this->config['passwordRecovery.']['mode'] = $pwRecoverMode ? strToLower($pwRecoverMode) : 'currentpassword';
		} elseif($ffPwRecoveryMode != 'typoscript') {
			$pwRecoverMode = $ffPwRecoveryMode;
			$this->config['passwordRecovery.']['mode'] = $pwRecoverMode ? strToLower($pwRecoverMode) : 'currentpassword';
		}

		# Get the email address which is displayed as the sender address of a password email.
    # Standard: ''
		$emailFrom = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'emailFrom', 'sPasswordRecovery');
		$emailFrom = $emailFrom ? $emailFrom : $this->tempConf['passwordRecovery.']['emailFrom'];
		$this->config['passwordRecovery.']['emailFrom'] = $emailFrom ? trim($emailFrom) : '';

		# Get the name which is displayed as the sender name of a password email.
    # Standard: ''
		$nameFrom = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'nameFrom', 'sPasswordRecovery');#);
		$nameFrom = $nameFrom ? $nameFrom : $this->tempConf['passwordRecovery.']['nameFrom'];
		$this->config['passwordRecovery.']['nameFrom'] = $nameFrom ? trim($nameFrom) : '';

		# Check if FreeCap (sr_freecap) is enabled or disabled.
		$ffEnableFreeCap = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'enableFreeCap', 'sPasswordRecovery');
		if($ffEnableFreeCap == '' OR $ffEnableFreeCap == 'typoscript') {
			$enableFreeCap = $this->tempConf['passwordRecovery.']['enableFreeCap'];
			$this->config['passwordRecovery.']['enableFreeCap'] = $enableFreeCap ? intval($enableFreeCap) : intval(0);
		} elseif($ffEnableFreeCap != 'typoscript') {
			$enableFreeCap = $ffEnableFreeCap;
			$this->config['passwordRecovery.']['enableFreeCap'] = $enableFreeCap ? intval($enableFreeCap) : intval(0);
		}

  }

  /**
  * Initalizing of the settings for the module "PasswordForcedChange".
  *
  * @return void
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
  function initalizePasswordForcedChangeSettings() {

  }

  /**
  * Initalizing of the settings for the feature "LoginProtection".
  *
  * @return void
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
  function initalizeLoginProtectionSettings() {

    # Check if the "UserDisable" feature is enabled or disabled.
    # Standard: 0 (= disabled).
    $ffEnableUserDisable = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'enableUserDisable', 'sLoginProtection');
		if($ffEnableUserDisable == '' OR $ffEnableUserDisable == 'typoscript') {
			$enableUserDisable = $this->tempConf['loginProtection.']['enableUserDisable'];
			$this->config['loginProtection.']['enableUserDisable'] = $enableUserDisable ? intval($enableUserDisable) : intval(0);
		} elseif($ffEnableUserDisable != 'typoscript') {
			$enableUserDisable = $ffEnableUserDisable;
			$this->config['loginProtection.']['enableUserDisable'] = $enableUserDisable ? intval($enableUserDisable) : intval(0);
		}

		# Check if the the user may reactivate himself after being disabled or not.
    # Standard: 0 (= disabled).
    $ffReactivationByUser = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'reactivationByUser', 'sLoginProtection');
		if($ffReactivationByUser == '' OR $ffReactivationByUser == 'typoscript') {
			$reactivationByUser = $this->tempConf['loginProtection.']['reactivationByUser'];
			$this->config['loginProtection.']['reactivationByUser'] = $reactivationByUser ? intval($reactivationByUser) : intval(0);
		} elseif($ffReactivationByUser != 'typoscript') {
			$reactivationByUser = $ffReactivationByUser;
			$this->config['loginProtection.']['reactivationByUser'] = $reactivationByUser ? intval($reactivationByUser) : intval(0);
		}

		# Check if the admin shall be informed by email when a user was disabled.
    # Standard: 0 (= disabled).
    $ffInformAdmin = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'informAdmin', 'sLoginProtection');
		if($ffInformAdmin == '' OR $ffInformAdmin == 'typoscript') {
			$informAdmin = $this->tempConf['loginProtection.']['informAdmin'];
			$this->config['loginProtection.']['informAdmin'] = $informAdmin ? intval($informAdmin) : intval(0);
		} elseif($ffInformAdmin != 'typoscript') {
			$informAdmin = $ffInformAdmin;
			$this->config['loginProtection.']['informAdmin'] = $informAdmin ? intval($informAdmin) : intval(0);
		}

		# Get the admin's email address in order to inform him about disabled users.
    # Standard: ''
		$emailFrom = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'emailFrom', 'sLoginProtection');
		$emailFrom = $emailFrom ? $emailFrom : $this->tempConf['loginProtection.']['emailFrom'];
		$this->config['loginProtection.']['emailFrom'] = $emailFrom ? trim($emailFrom) : '';

		# Get the admin's name which is shown in the email that informs him about disabled users.
    # Standard: ''
		$nameFrom = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'nameFrom', 'sLoginProtection');#);
		$nameFrom = $nameFrom ? $nameFrom : $this->tempConf['loginProtection.']['nameFrom'];
		$this->config['loginProtection.']['nameFrom'] = $nameFrom ? trim($nameFrom) : '';

		# Get the number of unsuccessful login attempts after which a user will be disabled.
    # Standard: ''
		$disableUserAfter = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'disableUserAfter', 'sLoginProtection');#);
		$disableUserAfter = $disableUserAfter ? $disableUserAfter : $this->tempConf['loginProtection.']['disableUserAfter'];
		$this->config['loginProtection.']['disableUserAfter'] = $disableUserAfter ? trim($disableUserAfter) : '';

  }

	/**
  * Initalizing of general settings in order to run this script.
  *
  * @return void
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
	function initalizeGeneralSettings() {

    $this->userIsOnline = $GLOBALS['TSFE']->loginUser;

		# Get the submitted "logintype".
    # Standard: ''.
		$logintype = t3lib_div::_GP('logintype');
		$this->config['submittedData']['logintype'] = $logintype ? $logintype : '';

		# Get the CAPTCHA string which was entered in the the input field
		# in order to prevent spamming.
		$this->config['submittedData']['captchaResponse'] = t3lib_div::_GP('captcha_response');

		# Get the submitted URL "redirect_url" to which the user will be redirected after logout.
		$redirectToUrlAfterLogout = t3lib_div::_GP('redirect_url');
		$this->config['submittedData']['redirectToUrlAfterLogout'] = $redirectToUrlAfterLogout ? intval($redirectToUrlAfterLogout) : '';

		# If the user has been disabled because there were to many unsuccessful login attempts,
		# inform him about the disable.
		$informDisabledUser = $this->piVars['informDisabledUser'];
		$this->config['submittedData']['informDisabledUser'] = $informDisabledUser ? intval($informDisabledUser) : '';

		# If the user has already been disabled because there were to many unsuccessful login attempts,
		# inform him about the disable.
		$informAlreadyDisabledUser = $this->piVars['informAlreadyDisabledUser'];
		$this->config['submittedData']['informAlreadyDisabledUser'] = $informAlreadyDisabledUser ? intval($informAlreadyDisabledUser) : '';

		# If the user was disabled by the administrator
		# inform him about the disable.
		$informSysDisabledUser = $this->piVars['informSysDisabledUser'];
		$this->config['submittedData']['informSysDisabledUser'] = $informSysDisabledUser ? intval($informSysDisabledUser) : '';

		# If the user entered a not existing username
		# inform him about it.
		$informNotExistingUser = $this->piVars['informNotExistingUser'];
		$this->config['submittedData']['informNotExistingUser'] = $informNotExistingUser ? intval($informNotExistingUser) : '';

		# If the user entered a wrong password in the SmallLoginbox redirect him to the RegularLoginbox.
		$loginError = $this->piVars['loginError'];
		$this->config['submittedData']['loginError'] = $loginError ? intval($loginError) : '';

		# If the user entered a wrong password and is redirected to the SmallLoginbox,
		# also add the current login attempts.
		$loginAttempts = $this->piVars['loginAttempts'];
		$this->config['submittedData']['loginAttempts'] = $loginAttempts ? intval($loginAttempts) : '';

		# That's the hash string that is necessary to reactivate a user's acount in combination
		# with setting a new password.
		$hashString = $this->piVars['hash'];
		$this->config['submittedData']['hashString'] = $hashString ? str_replace('.','',$hashString) : '';

		$module = t3lib_div::_GP('module');
		$this->config['submittedData']['module'] = $module ? $module : '';

		# Check if username and email address were entered.
    # Check if email address is valid.
    $this->config['submittedData']['forgotEmail'] = t3lib_div::validEmail($this->piVars['DATA']['forgotEmail']) ? trim($this->piVars['DATA']['forgotEmail']) : '';
    $this->config['submittedData']['username'] = $this->piVars['DATA']['username'] ? trim($this->piVars['DATA']['username']) : '';
		$this->config['submittedData']['user'] = t3lib_div::_GP('user') ? t3lib_div::_GP('user') : '';
		$this->config['submittedData']['pass'] = t3lib_div::_GP('pass') ? t3lib_div::_GP('pass') : '';

		# Check is "sr_freecap" is installed
		$this->config['freecapIsInstalled'] = t3lib_extMgm::isLoaded('sr_freecap') ? intval(1) : intval(0);
		if ($this->config['freecapIsInstalled']) {
			require_once(t3lib_extMgm::extPath('sr_freecap').'pi2/class.tx_srfreecap_pi2.php');
			$this->freeCap = t3lib_div::makeInstance('tx_srfreecap_pi2');
		}

		# Check is "kb_md5fepw" is installed
		$this->config['md5fepwIsInstalled'] = t3lib_extMgm::isLoaded('kb_md5fepw') ? intval(1) : intval(0);

		# Get the "code" that tells us what to display.
    # Standard: ''.
		$code = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'module', 'sDEF');
		$code = $code ? $code : $this->tempConf['code'];
		$this->config['code'] = $code ? strtolower($code) : '';

		# Get the selected template file.
		#$uploadFolder = $TYPO3_CONF_VARS['EXTCONF'][$this->extKey]['uploadFolder'];
		$templateFile = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'templatefile', 'sDEF');
		$templateFile = $templateFile ? ($this->uploadFolder .'/'. $templateFile) : $this->tempConf['templateFile'];
		$this->config['templateFile'] = $templateFile;

		# Get the starting point where the user information are stored.
		$this->config['userPIDList'] = $this->initUserPIDList();

		# Check if redirection at logout is disabled.
    # Standard: 0
		$forceNoLogoutRedirect = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'forcenologoutredirect', 'sDEF');
		$forceNoLogoutRedirect = $forceNoLogoutRedirect ? $forceNoLogoutRedirect : $this->tempConf['forceNoLogoutRedirect'];
		$this->config['forceNoLogoutRedirect'] = $forceNoLogoutRedirect ? intval($forceNoLogoutRedirect) : intval(0);

		# Check if redirection at login is disabled.
    # Standard: 0
		$forceNoLoginRedirect = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'forcenologinredirect', 'sDEF');
		$forceNoLoginRedirect = $forceNoLoginRedirect ? $forceNoLoginRedirect : $this->tempConf['forceNoLoginRedirect'];
		$this->config['forceNoLoginRedirect'] = $forceNoLoginRedirect ? intval($forceNoLoginRedirect) : intval(0);

		# Get the selected page (assigned in the Constant Editor or Page Setup)
    # where the user shall be redirected after login and logout.
		$this->config['loginRedirectAssInConstEditor'] = intval($this->tempConf['loginRedirectPID']);
		$this->config['logoutRedirectAssInConstEditor'] = intval($this->tempConf['logoutRedirectPID']);

		# Get the selected page (assigned in the Plugin Flexform)
    # where the user shall be redirected after login and logout.
    $this->config['loginRedirectAssInPlugFlex'] = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'loginredirectpid', 'sDEF'));
		$this->config['logoutRedirectAssInPlugFlex'] = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'logoutredirectpid', 'sDEF'));

	  # Get the selected pages (assigned in table "fe_users")
    # where the user shall be redirected after login and logout.
	  if($GLOBALS['TSFE']->loginUser == 1) {
      $this->config['loginRedirectForUser'] = intval($GLOBALS['TSFE']->fe_user->user['tx_feuserloginsystem_redirectionafterlogin']);
	    $this->config['logoutRedirectForUser'] = intval($GLOBALS['TSFE']->fe_user->user['tx_feuserloginsystem_redirectionafterlogout']);
	  }

	  # Get the selected pages (assigned in table "fe_groups")
    # where the user shall be redirected after login and logout.
	  if($GLOBALS['TSFE']->loginUser == 1) {
      $groupData = $GLOBALS["TSFE"]->fe_user->groupData;
		  reset($groupData);

		  # Get LOGIN redirection PID
		  $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_feuserloginsystem_redirectionafterlogin', $GLOBALS["TSFE"]->fe_user->usergroup_table, 'tx_feuserloginsystem_redirectionafterlogin!=\'\' OR NOT(tx_feuserloginsystem_redirectionafterlogin IS NULL) AND uid IN ('.implode(',',$groupData['uid']).')');
		  $this->config['loginRedirectForUsergroup'] = intval(0);
		  if($row=$GLOBALS['TYPO3_DB']->sql_fetch_row($res)) {
        $this->config['loginRedirectForUsergroup'] = intval($row[0]);
		  }

		  # Get LOGOUT redirection PID
		  $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_feuserloginsystem_redirectionafterlogout', $GLOBALS["TSFE"]->fe_user->usergroup_table, 'tx_feuserloginsystem_redirectionafterlogout!=\'\' OR NOT(tx_feuserloginsystem_redirectionafterlogout IS NULL) AND uid IN ('.implode(',',$groupData['uid']).')');
      $this->config['logoutRedirectForUsergroup'] = intval(0);
		  if($row=$GLOBALS['TYPO3_DB']->sql_fetch_row($res)) {
        $this->config['logoutRedirectForUsergroup'] = intval($row[0]);
		  }
		}

		$this->config['_LOCAL_LANG.'] = $this->tempConf['_LOCAL_LANG.'];

		# Preconfigure the typolink.
		$this->local_cObj->setCurrentVal($GLOBALS['TSFE']->id);
		$this->typolink_conf = $this->tempConf['typolink.'];
		$this->typolink_conf['parameter.']['current'] = 1;
		$this->typolink_conf['additionalParams'] = $this->cObj->stdWrap($this->typolink_conf['additionalParams'],$this->typolink_conf['additionalParams.']);
		unset($this->typolink_conf['additionalParams.']);

		# Check if caching is enabled or not.
		$this->config['allowCaching'] = $this->conf['allowCaching']?1:0;
		if (!$this->config['allowCaching']) {
			$GLOBALS['TSFE']->set_no_cache();
		}

	}

	/**********************************
	*
	* The method is responsible for displaying the "regularlogin" module.
	*
	* @return	The module content that is displayed on the website
	*
	***********************************/
	function regularLogin() {
    $tempContent = '';

    $onSubmit = '';
    $extraHidden = '';

    # Include the selected template file.
		$templateCode = $this->local_cObj->fileResource($this->config['templateFile']);

    $actionUri = htmlspecialchars($this->pi_getPageLink($GLOBALS['TSFE']->id,'_top',$this->addAdditionalParams()));
    #$actionUri = htmlspecialchars($GLOBALS["TSFE"]->siteScript);

    $extraHidden .= '<input type="hidden" name="module" value="rl" />'."\n";

    # Check if permalogin is enabled or disabled
    # If YES, set attributes for the template
    $permaHiddenAttributes = '';
    $permaCheckboxAttributes = '';
    if ($this->config['regularLoginbox.']['showPermalogin'] && $GLOBALS['TYPO3_CONF_VARS']['FE']['lifetime'] > 0 &&
		($GLOBALS['TYPO3_CONF_VARS']['FE']['permalogin'] == 0 || $GLOBALS['TYPO3_CONF_VARS']['FE']['permalogin'] == 1) ) {
			if($GLOBALS['TYPO3_CONF_VARS']['FE']['permalogin'] == 1) {
				$permaHiddenAttributes = 'disabled="disabled"';
				$permaCheckboxAttributes = 'checked="checked"';
			}
		}

    // Hook (used by kb_md5fepw extension by Kraft Bernhard <kraftb@gmx.net>)
		// This hook allows to call User JS functions.
		// The methods should also set the required JS functions to get included
		if($this->config['md5fepwIsInstalled']) {

      $_params = array ();

			list($onSubmitValue, $extraHiddenValue) = t3lib_div::callUserFunction('EXT:kb_md5fepw/pi1/class.tx_kbmd5fepw_newloginbox.php:&tx_kbmd5fepw_newloginbox->loginFormOnSubmit', $_params, $this);

		  $onSubmit .= $onSubmitValue.'; return true;';
			$extraHidden .= $extraHiddenValue;

    }

    # A user is just opening the page with this plugin
    # or pressing the login button without entering username and password
    if ($GLOBALS['TSFE']->loginUser == 0
        AND $this->config['submittedData']['logintype'] == ''
        AND !$this->config['submittedData']['informDisabledUser']
        AND !$this->config['submittedData']['informAlreadyDisabledUser']
        AND !$this->config['submittedData']['informSysDisabledUser']
        AND !$this->config['submittedData']['informNotExistingUser']
        AND !$this->config['submittedData']['loginError']) {

			# Rendering the feuserloginsystem template.
      $template['total'] = $this->cObj->getSubpart($templateCode, '###TEMPLATE_REGULARLOGIN###');
      $template['headerValid'] = $this->cObj->getSubpart($template['total'], '###HEADER_VALID###');
		  $template['messageValid'] = $this->cObj->getSubpart($template['total'], '###MESSAGE_VALID###');
			$template['forgotPasswordValid'] = $this->cObj->getSubpart($template['total'], '###FORGOT_PASSWORD_VALID###');

      $markerArray['###TEXT_HEADER###']     	= $this->getContent('text_headerLogin');
		  $markerArray['###TEXT_MESSAGE###']    	= $this->getContent('text_messageLogin');
      $markerArray['###LABEL_USERNAME###']  	= $this->getContent('label_username');
		  $markerArray['###LABEL_PASSWORD###']  	= $this->getContent('label_password');
		  $markerArray['###LABEL_LOGIN###']     	= $this->getContent('label_login');
		  $markerArray['###LABEL_PERMALOGIN###'] 	= $this->getContent('label_permalogin');
		  $markerArray['###PERMALOGIN_HIDDEN_ATT###'] = $permaHiddenAttributes;
			$markerArray['###PERMALOGIN_CHECKBOX_ATT###'] 		= $permaCheckboxAttributes;
		  $markerArray['###LABEL_FORGOT_PASSWORD###'] = $this->getForgotPasswordLink($this->module);

		  $markerArray['###ACTION_URI###']      = $actionUri;
		  $markerArray['###ON_SUBMIT###']       = $onSubmit;
		  $markerArray['###STORAGE_PID###']     = $this->config['userPIDList'];
		  $markerArray['###REDIRECT_URL###']    = '';
		  $markerArray['###EXTRA_HIDDEN###']    = $extraHidden;

    }
    # A user caused an error message.
		elseif (($GLOBALS['TSFE']->loginUser == 0
        AND $this->config['submittedData']['logintype'] == 'login')
        OR $this->config['submittedData']['informDisabledUser']
        OR $this->config['submittedData']['informAlreadyDisabledUser']
        OR $this->config['submittedData']['informSysDisabledUser']
        OR $this->config['submittedData']['informNotExistingUser']
        OR $this->config['submittedData']['loginError']) {

		  # Log (write) login attempt in own database table (tx_feuserloginsystem_loginlog).
		  $userDisabled = $this->checkLoginAttempt();

		  # Get the error message for disabled user if the user has been disabled.
		  if($userDisabled == 1 OR $this->config['submittedData']['informDisabledUser']) {
        $textMessageError = $this->getContent('text_messageErrorUserHasBeenDisabled');
      } elseif ($userDisabled == 2 OR $this->config['submittedData']['informAlreadyDisabledUser']) {
        $textMessageError = $this->getContent('text_messageErrorUserHasAlreadyBeenDisabled');
      } elseif (($userDisabled == 3 OR $this->config['submittedData']['informSysDisabledUser']) AND $this->config['regularLoginbox.']['showSysDisabledUserErrMsg']) {
        $textMessageError = $this->getContent('text_messageErrorDisabledUser');
      } elseif(($userDisabled == 4 OR $this->config['submittedData']['informNotExistingUser']) AND $this->config['regularLoginbox.']['showNotExistingUserErrMsg']) {
        $textMessageError = $this->getContent('text_messageErrorNotExistingUser');
      } elseif ($this->config['submittedData']['loginError']) {
        $textMessageError = $this->getContent('text_messageError');
      } else {
        $textMessageError = $this->getContent('text_messageError');
      }

	    # Rendering the feuserloginsystem template.
      $template['total'] = $this->cObj->getSubpart($templateCode, '###TEMPLATE_REGULARLOGIN###');
      $template['headerValid'] = $this->cObj->getSubpart($template['total'], '###HEADER_VALID###');
		  $template['messageValid'] = $this->cObj->getSubpart($template['total'], '###MESSAGE_VALID###');

      $markerArray['###TEXT_HEADER###']     = $this->getContent('text_headerError');
		  $markerArray['###TEXT_MESSAGE###']    = $textMessageError;
      $markerArray['###LABEL_USERNAME###']  = $this->getContent('label_username');
		  $markerArray['###LABEL_PASSWORD###']  = $this->getContent('label_password');
		  $markerArray['###LABEL_LOGIN###']     = $this->getContent('label_login');
		  $markerArray['###LABEL_PERMALOGIN###'] 	= $this->getContent('label_permalogin');
		  $markerArray['###PERMALOGIN_HIDDEN_ATT###'] = $permaHiddenAttributes;
			$markerArray['###PERMALOGIN_CHECKBOX_ATT###'] 		= $permaCheckboxAttributes;
		  $markerArray['###LABEL_FORGOT_PASSWORD###'] = $this->getForgotPasswordLink($this->module);

		  $markerArray['###ACTION_URI###']      = $actionUri;
		  $markerArray['###ON_SUBMIT###']       = $onSubmit;
		  $markerArray['###STORAGE_PID###']     = $this->config['userPIDList'];
		  $markerArray['###REDIRECT_URL###']    = '';
		  $markerArray['###EXTRA_HIDDEN###']    = $extraHidden;
		}
    # A user was logged in successfullly and gets to see a welcome message.
    elseif ($GLOBALS['TSFE']->loginUser == 1 && $this->config['submittedData']['logintype'] == 'login') {

      # Log (write) login attempt in own database table (tx_feuserloginsystem_loginlog).
		  $this->checkLoginAttempt();

      # LOGIN REDIRECTION
    	# Redirect user to another page if redirection is enabled and configured.
			$this->checkRedirection();


      # Rendering the feuserloginsystem template.
      $template['total'] = $this->cObj->getSubpart($templateCode, '###TEMPLATE_SUCCESS###');
      $template['headerValid'] = $this->cObj->getSubpart($template['total'], '###HEADER_VALID###');
		  $template['messageValid'] = $this->cObj->getSubpart($template['total'], '###MESSAGE_VALID###');

      $markerArray['###TEXT_HEADER###'] 		= $this->getContent('text_headerSuccess');
		  $markerArray['###TEXT_MESSAGE###'] 		= $this->getContent('text_messageSuccess');

    }
    # A user gets to see his online status or wants to log out himself.
    elseif ($GLOBALS['TSFE']->loginUser == 1 && $this->config['submittedData']['logintype'] == '') {

      # Rendering the feuserloginsystem template.
      $template['total'] = $this->cObj->getSubpart($templateCode, '###TEMPLATE_LOGOUT###');
      $template['headerValid'] = $this->cObj->getSubpart($template['total'], '###HEADER_VALID###');
		  $template['messageValid'] = $this->cObj->getSubpart($template['total'], '###MESSAGE_VALID###');

      $markerArray['###TEXT_HEADER###']     = $this->getContent('text_headerStatus');
		  $markerArray['###TEXT_MESSAGE###']    = $this->getContent('text_messageStatus');
      $markerArray['###LABEL_USERNAME###']  = $this->getContent('label_username');
		  $markerArray['###LABEL_LOGOUT###']    = $this->getContent('label_logout');

      $markerArray['###ACTION_URI###'] 			= $actionUri;
		  $markerArray['###STORAGE_PID###'] 		= $this->config['userPIDList'];
		  $markerArray['###REDIRECT_URL###'] 		= $this->getRedirectionPID('logout');

    }
    # A user logged out successfully.
    elseif ($GLOBALS['TSFE']->loginUser == 0 && $this->config['submittedData']['logintype'] == 'logout') {

    	# LOGOUT REDIRECTION
      # Redirect user to another page if redirection is enabled and configured.
			$this->checkRedirection();

      # Rendering the feuserloginsystem template.
      $template['total'] = $this->cObj->getSubpart($templateCode, '###TEMPLATE_REGULARLOGIN###');
      $template['headerValid'] = $this->cObj->getSubpart($template['total'], '###HEADER_VALID###');
		  $template['messageValid'] = $this->cObj->getSubpart($template['total'], '###MESSAGE_VALID###');

      $markerArray['###TEXT_HEADER###']     = $this->getContent('text_headerLogout');
		  $markerArray['###TEXT_MESSAGE###']    = $this->getContent('text_messageLogout');
		  $markerArray['###LABEL_USERNAME###']  = $this->getContent('label_username');
		  $markerArray['###LABEL_PASSWORD###']  = $this->getContent('label_password');
		  $markerArray['###LABEL_LOGIN###']     = $this->getContent('label_login');
		  $markerArray['###LABEL_FORGOT_PASSWORD###'] = $this->getForgotPasswordLink($this->module);
		  $markerArray['###PERMALOGIN_HIDDEN_ATT###'] = $permaHiddenAttributes;
			$markerArray['###PERMALOGIN_CHECKBOX_ATT###'] 		= $permaCheckboxAttributes;
		  $markerArray['###LABEL_PERMALOGIN###'] 	= $this->getContent('label_permalogin');

		  $markerArray['###ACTION_URI###'] 			= $actionUri;
		  $markerArray['###ON_SUBMIT###'] 			= $onSubmit;
		  $markerArray['###STORAGE_PID###'] 		= $this->config['userPIDList'];
		  $markerArray['###REDIRECT_URL###'] 		= '';
		  $markerArray['###EXTRA_HIDDEN###'] 		= $extraHidden;

    }

    # Rendering the feuserloginsystem template.
    # Render subparts.
		$temp_headerValid = $this->cObj->substituteMarkerArrayCached($template['headerValid'], $markerArray, array(), array());
		$subpartArray['###HEADER_VALID###'] = $temp_headerValid;
		$temp_messageValid = $this->cObj->substituteMarkerArrayCached($template['messageValid'], $markerArray, array(), array());
		$subpartArray['###MESSAGE_VALID###'] = $temp_messageValid;
		$temp_forgotPasswordValid = $this->cObj->substituteMarkerArrayCached($template['forgotPasswordValid'], $markerArray, array(), array());
		$subpartArray['###FORGOT_PASSWORD_VALID###'] = $temp_forgotPasswordValid;

    # Rendering the feuserloginsystem template.
		# Remove subpart that are not used or disabled.
		$template['total'] = $this->cObj->substituteSubpart($template['total'], '###FORGOT_PASSWORD_VALID###', (($this->config['regularLoginbox.']['showPwRecoveryLink'] && $this->config['regularLoginbox.']['pwRecoveryPID'] != 0) ? array('', '') : ''), 0);
		$template['total'] = $this->cObj->substituteSubpart($template['total'], '###HEADER_VALID###', (!$this->config['regularLoginbox.']['hideHeader'] ? array('', '') : ''), 0);
		$template['total'] = $this->cObj->substituteSubpart($template['total'], '###MESSAGE_VALID###', (!$this->config['regularLoginbox.']['hideMessage'] ? array('', '') : ''), 0);
		$template['total'] = $this->cObj->substituteSubpart($template['total'], '###PERMALOGIN_VALID###', (($this->config['regularLoginbox.']['showPermalogin'] AND $GLOBALS['TYPO3_CONF_VARS']['FE']['lifetime'] > 0) ? array('', '') : ''), 0);

		$tempContent = $this->cObj->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray, array());

		$tempContent = $this->replaceSpecialCharMarkers($tempContent,'html');
		$tempContent = $this->substituteExtraMarkers($tempContent);

		return $tempContent;
	}


	/**********************************
	*
	* The method is responsible for displaying the "smalllogin" module.
	*
	* @return	The module content that is displayed on the website
	*
	***********************************/
	function smallLogin() {
	  $tempContent = '';

	  $onSubmit = '';
    $extraHidden = '';

	  # Include the selected template file.
		$templateCode = $this->local_cObj->fileResource($this->config['templateFile']);

	  $actionUri = htmlspecialchars($this->pi_getPageLink($GLOBALS['TSFE']->id,'_top',$this->addAdditionalParams()));
	  #$actionUri = htmlspecialchars($GLOBALS["TSFE"]->siteScript);

    $extraHidden .= '<input type="hidden" name="module" value="sl" />'."\n";

    # Check if permalogin is enabled or disabled
    # If YES, set attributes for the template
    $permaHiddenAttributes = '';
    $permaCheckboxAttributes = '';
    if ($this->config['regularLoginbox.']['showPermalogin'] && $GLOBALS['TYPO3_CONF_VARS']['FE']['lifetime'] > 0 &&
		($GLOBALS['TYPO3_CONF_VARS']['FE']['permalogin'] == 0 || $GLOBALS['TYPO3_CONF_VARS']['FE']['permalogin'] == 1) ) {
			if($GLOBALS['TYPO3_CONF_VARS']['FE']['permalogin'] == 1) {
				$permaHiddenAttributes = 'disabled="disabled"';
				$permaCheckboxAttributes = 'checked="checked"';
			}
		}

		// Hook (used by kb_md5fepw extension by Kraft Bernhard <kraftb@gmx.net>)
		// This hook allows to call User JS functions.
		// The methods should also set the required JS functions to get included
		if($this->config['md5fepwIsInstalled']) {
			$_params = array ();

			list($onSubmitValue, $extraHiddenValue) = t3lib_div::callUserFunction('EXT:kb_md5fepw/pi1/class.tx_kbmd5fepw_newloginbox.php:&tx_kbmd5fepw_newloginbox->loginFormOnSubmit', $_params, $this);

		  $onSubmit .= $onSubmitValue.'; return true;';
			$extraHidden .= $extraHiddenValue;
    }

		# A user is just opening the page with this plugin
    # or pressing the login button without entering username and password
    if ($GLOBALS['TSFE']->loginUser == 0) {

      # Log (write) login attempt in own database table (tx_feuserloginsystem_loginlog).
		  $userDisabled = $this->checkLoginAttempt();

		  # Redirect to the page with the Regular Loginbox in order to show the different error messages.
		  if($userDisabled == 1 AND $this->config['smallLoginbox.']['regularLoginPID']) {
        $redirectUrl = $this->pi_linkTP_keepPIvars_url(array('informDisabledUser' => 1),0,0,$this->config['smallLoginbox.']['regularLoginPID']);
        header('Location: '.t3lib_div::makeRedirectUrl($redirectUrl));
        exit;
      } elseif ($userDisabled == 2 AND $this->config['smallLoginbox.']['regularLoginPID']) {
        $redirectUrl = $this->pi_linkTP_keepPIvars_url(array('informAlreadyDisabledUser' => 1),0,0,$this->config['smallLoginbox.']['regularLoginPID']);
        header('Location: '.t3lib_div::makeRedirectUrl($redirectUrl));
        exit;
      } elseif ($userDisabled == 3 AND $this->config['smallLoginbox.']['regularLoginPID']) {
        $redirectUrl = $this->pi_linkTP_keepPIvars_url(array('informSysDisabledUser' => 1),0,0,$this->config['smallLoginbox.']['regularLoginPID']);
        header('Location: '.t3lib_div::makeRedirectUrl($redirectUrl));
        exit;
      } elseif ($userDisabled == 4 AND $this->config['smallLoginbox.']['regularLoginPID']) {
        $redirectUrl = $this->pi_linkTP_keepPIvars_url(array('informNotExistingUser' => 1),0,0,$this->config['smallLoginbox.']['regularLoginPID']);
        header('Location: '.t3lib_div::makeRedirectUrl($redirectUrl));
        exit;
      } elseif ($this->config['submittedData']['logintype'] == 'login'
          AND $this->config['smallLoginbox.']['regularLoginPID']
          AND $this->config['submittedData']['module'] == 'sl') {
        $userArray = $this->getUserArray($this->config['submittedData']['user']);
        $currentLoginAttempts = $this->getCurrentLoginAttemptsOfUser($userArray['uid']);
        $redirectUrl = $this->pi_linkTP_keepPIvars_url(array('loginError' => 1,'loginAttempts' => $currentLoginAttempts),0,0,$this->config['smallLoginbox.']['regularLoginPID']);
        header('Location: '.t3lib_div::makeRedirectUrl($redirectUrl));
        exit;
      }


      # LOGOUT REDIRECTION
    	# Redirect user to another page if redirection is enabled and configured.
    	if($this->config['submittedData']['logintype'] == 'logout') {
        $this->checkRedirection();
      }

      # Rendering the feuserloginsystem template.
      $template['total'] = $this->cObj->getSubpart($templateCode, '###TEMPLATE_SMALLLOGIN###');

      $markerArray['###LABEL_USERNAME###'] 	= $this->getContent('label_username');
		  $markerArray['###LABEL_PASSWORD###'] 	= $this->getContent('label_password');
		  $markerArray['###LABEL_LOGIN###'] 		= $this->getContent('label_login');
		  $markerArray['###LABEL_PERMALOGIN###'] 	= $this->getContent('label_permalogin');
		  $markerArray['###PERMALOGIN_HIDDEN_ATT###'] = $permaHiddenAttributes;
			$markerArray['###PERMALOGIN_CHECKBOX_ATT###'] 		= $permaCheckboxAttributes;
		  $markerArray['###LABEL_FORGOT_PASSWORD###'] = $this->getForgotPasswordLink($this->module);

		  $markerArray['###ACTION_URI###'] 			= $actionUri;
		  $markerArray['###ON_SUBMIT###'] 			= $onSubmit;
		  $markerArray['###STORAGE_PID###'] 		= $this->config['userPIDList'];
		  $markerArray['###REDIRECT_URL###'] 		= '';
		  $markerArray['###EXTRA_HIDDEN###'] 		= $extraHidden;

    }
    # a user was logged in successfullly and gets to see a status message
    elseif ($GLOBALS['TSFE']->loginUser == 1) {

      # Log (write) login attempt in own database table (tx_feuserloginsystem_loginlog).
		  $this->checkLoginAttempt();

      # LOGIN REDIRECTION
      # Redirect user to another page if redirection is enabled and configured.
      if($this->config['submittedData']['logintype'] == 'login') {
			 $this->checkRedirection();
			}

      # Rendering the feuserloginsystem template.
      $template['total'] = $this->cObj->getSubpart($templateCode, '###TEMPLATE_SMALLLOGIN_LOGGEDIN###');

      $markerArray['###TEXT_LOGGEDINAS###'] = $this->getContent('text_loggedInAs');
		  $markerArray['###LABEL_LOGOUT###'] 		= $this->getContent('label_logout');

		  $markerArray['###ACTION_URI###'] 			= $actionUri;
		  $markerArray['###ON_SUBMIT###'] 			= $onSubmit;
		  $markerArray['###STORAGE_PID###'] 		= $this->config['userPIDList'];
		  $markerArray['###REDIRECT_URL###'] 		= $this->getRedirectionPID('logout');
		  $markerArray['###EXTRA_HIDDEN###'] 		= '';
    }

    # Rendering the feuserloginsystem template.
    # Render subparts.
		$temp_forgotPasswordValid = $this->cObj->substituteMarkerArrayCached($template['forgotPasswordValid'], $markerArray, array(), array());
		$subpartArray['###FORGOT_PASSWORD_VALID###'] = $temp_forgotPasswordValid;

    # Rendering the feuserloginsystem template.
		# Remove subpart that are not used or disabled.
		$template['total'] = $this->cObj->substituteSubpart($template['total'], '###FORGOT_PASSWORD_VALID###', (($this->config['smallLoginbox.']['showPwRecoveryLink'] && $this->config['smallLoginbox.']['pwRecoveryPID'] != 0) ? array('', '') : ''), 0);
		$template['total'] = $this->cObj->substituteSubpart($template['total'], '###PERMALOGIN_VALID###', ($this->config['smallLoginbox.']['showPermalogin'] ? array('', '') : ''), 0);

    $tempContent = $this->cObj->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray, array());

    $tempContent = $this->replaceSpecialCharMarkers($tempContent,'html');
    $tempContent = $this->substituteExtraMarkers($tempContent);

		return $tempContent;

	}

	/**
  * The method is responsible for displaying the "passwordRecovery" module.
  *
  * @return string                      The module content that is displayed on the website
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
	function passwordRecovery() {
    $tempContent = '';

    $extraHidden = '';

    # Include the selected template file.
		$templateCode = $this->local_cObj->fileResource($this->config['templateFile']);

    # Make sure that from now on the variable "forgotPw" with the value "1" is
    # always added to the URL in order to run the module "PasswordRecovery"
		#$this->piVars['forgotPw'] = '1';

    # Display instruction page for password recovery
    # only if either the username or the email address were not entered correctly.
    # or if the CaptchaResponse did not match the CaptchaImage
    if($this->config['submittedData']['forgotEmail'] == ''
				OR $this->config['submittedData']['username'] == ''
				OR ($this->config['freecapIsInstalled']
						AND $this->config['passwordRecovery.']['enableFreeCap']
						AND !$this->checkCaptchaWord($this->config['submittedData']['captchaResponse'])
					)
			) {

      $actionUri = htmlspecialchars($this->pi_linkTP_keepPIvars_url());

      $textHeaderForgotPass = '';
      $textMessageSendPass = '';
      $labelSendPass = '';

      # Get instructions depending on the mode (new password or current password).
      # Show instructions for new password if the extension kb_md5fepw is installed.
      if($this->config['passwordRecovery.']['mode'] == 'newpassword'
        OR $this->config['md5fepwIsInstalled']
        OR !empty($this->config['submittedData']['hashString'])) {
        if(!empty($this->config['submittedData']['hashString'])) {
          $textHeaderForgotPass = $this->getContent('text_headerForgotPwInstEnableDisAcc');
        } else {
          $textHeaderForgotPass = $this->getContent('text_headerForgotPasswordInstruction');
        }
    	  $textMessageSendPass = $this->getContent('text_messageSendNewPasswordInstruction');
    	  $labelSendPass = $this->getContent('label_sendNewPassword');
		  } elseif ($this->config['passwordRecovery.']['mode'] == 'currentpassword'
        AND !$this->config['md5fepwIsInstalled']
        AND empty($this->config['submittedData']['hashString'])) {
        $textHeaderForgotPass = $this->getContent('text_headerForgotPasswordInstruction');
    	  $textMessageSendPass = $this->getContent('text_messageSendCurrentPasswordInstruction');
    	  $labelSendPass = $this->getContent('label_sendCurrentPassword');
		  }

      # Rendering the feuserloginsystem template.
      $template['total'] = $this->cObj->getSubpart($templateCode, '###TEMPLATE_FORGOT_PASSWORD###');

      $markerArray['###TEXT_HEADER###']     = $textHeaderForgotPass;
		  $markerArray['###TEXT_MESSAGE###']    = $textMessageSendPass;
		  $markerArray['###LABEL_USERNAME###']  = $this->getContent('label_username');
		  $markerArray['###LABEL_EMAIL###'] 	  = $this->getContent('label_email');
		  $markerArray['###LABEL_SEND_PASSWORD###'] 	= $labelSendPass;

		  $markerArray['###PREFIXID###']        = $this->prefixId;
      $markerArray['###ACTION_URI###'] 			= $actionUri;
      $markerArray['###EXTRA_HIDDEN###'] 		= $extraHidden;

			# This following part takes care of the CAPTCHA marker
			if ($this->config['passwordRecovery.']['enableFreeCap'] AND is_object($this->freeCap)) {
				$markerArray = array_merge($markerArray, $this->freeCap->makeCaptcha());
			} else {
				$template['total'] = $this->cObj->substituteSubpart($template['total'],'###CAPTCHA_INSERT###','');
			}

		  $tempContent = $this->cObj->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray, array());

    # Perform desired action (send current password or a new one)
    # and display a confirmation page.
    # Do also check if there is an hash string submitted which means that a
    # user tries to enable again his currently disabled acount. If so, perform
    # the necessary actions.
		} else {

			$tempUsername = $this->config['submittedData']['username'];
			$tempEmail = $this->config['submittedData']['forgotEmail'];

			# Check if a user call the PasswordRecovery module with an hash string attached.
			# That happens if a user wants to enable his account again after it was disabled.
			# This user should find the link with the hash string in an email sent to him.
			if(empty($this->config['submittedData']['hashString'])) {

        # There is no hash string.
        # Check if the entered username and email are correct anyway.
        # If no, send the user back to the input form.
        if(!$this->userExistsInDB($tempUsername, $tempEmail)) {
				  header('Location: '.t3lib_div::makeRedirectUrl($this->pi_linkTP_keepPIvars_url()));
				  exit;
        }

			# A hash string is available.
			} else {

        # Check if the user entered a correct username and email address.
        # Also check disabled user
        if($this->userExistsInDB($tempUsername, $tempEmail, TRUE)) {

          # Get the user information.
          $userArray = $this->getUserArray($tempUsername, $tempEmail, TRUE);

          # CHECK HERE if the user has currently been disabled by the system
          # because there were to many unsuccessful login attempts. Check therefore
          # if the user is linked to an entry in the tx_feuserloginsystem_loginlog
          # database table containing the submitted hash string.
          $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'tx_feuserloginsystem_loginlog.*',
            'tx_feuserloginsystem_loginlog,fe_users',
            'tx_feuserloginsystem_loginlog.feuserid = fe_users.uid' .
                ' AND tx_feuserloginsystem_loginlog.feuserid=\'' . $userArray['uid'] . '\'' .
                ' AND tx_feuserloginsystem_loginlog.success=\'' . '2' . '\'' .
                ' AND tx_feuserloginsystem_loginlog.hash=\'' . $this->config['submittedData']['hashString'] . '\'' .
                ' AND fe_users.deleted=\'' . '0' . '\'' .
                ' AND fe_users.disable=\'' . '1' . '\'',
            '',
            'tx_feuserloginsystem_loginlog.lastloginattempt DESC ',
            '1'
          );
          $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

          if ($GLOBALS['TYPO3_DB']->sql_error() ) {
				    t3lib_div::debug(array('SQL error:',
				    $GLOBALS['TYPO3_DB']->sql_error() ) );
          }

          # The user with the corresponding hash string was found in the database.
		      if(is_array($row)) {

    		    # Enable the user's acount.
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
              'fe_users',
              sprintf('uid=\'%s\'',
                addslashes($userArray['uid'])
                ),
              array('disable' => 0)
            );

            # Update information for login log.
		        $updateDataArray = array();
		        $updateDataArray['success']  = intval(3);

		        # Update login log in the database.
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
              'tx_feuserloginsystem_loginlog',
              sprintf('uid=\'%s\'',addslashes(intval($row['uid']))),
              $updateDataArray
            );


    		  # The user entered a correct username and email but there wasn't found
    		  # a corresponding hash string in the database.
          # Send him back to the input form.
          } else {
            header('Location: '.t3lib_div::makeRedirectUrl($this->pi_linkTP_keepPIvars_url()));
				    exit;
          }

				# The user didn't enter a correct username and email even though there is
				# a hash string available.
				# Send him back to the input form.
        } else {
          header('Location: '.t3lib_div::makeRedirectUrl($this->pi_linkTP_keepPIvars_url()));
				  exit;
        }

      }

		  $textHeaderPassSent = $this->getContent('text_headerForgotPasswordEmailSent');
      $textMessagePassSent = '';

			#
		  # Perform actions for sending a new password to the user.
		  # DO also send a new password if kb_md5fepw is installed.
		  # Do also send a new password if a user wants to enable is account again after checking hash string.
		  #
		  if($this->config['passwordRecovery.']['mode'] == 'newpassword'
         OR $this->config['md5fepwIsInstalled']
         OR !empty($this->config['submittedData']['hashString'])) {

		    # Generate a new password.
		    $newPassword = substr(md5(uniqid (rand())), 2, 10);

        # Encode the new password if the extension kb_md5fepw is installed.
        if($this->config['md5fepwIsInstalled']) {
          $newMD5Password = md5($newPassword);
        }

        # Update user in the database with the new password.
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
					'fe_users',
					sprintf('username=\'%s\' AND email=\'%s\' and pid IN (%s) %s',
					  addslashes($this->config['submittedData']['username']),
					  addslashes($this->config['submittedData']['forgotEmail']),
						$this->config['userPIDList'],
 						$this->cObj->enableFields('fe_users') ),
 					array('password' => $this->config['md5fepwIsInstalled'] ? $newMD5Password : $newPassword)
        );

        if ($GLOBALS['TYPO3_DB']->sql_error() ) {
          t3lib_div::debug(array('SQL error:',
          $GLOBALS['TYPO3_DB']->sql_error() ) );
        }

        # Email message.
        $message = $this->replaceSpecialCharMarkers($this->getContent('text_emailMessageNewPassword'),'text');

        # Replace markers with content in the Password-Sent-Message.
        $message = $this->substituteExtraMarkers($message, array('/###PASSWORD###/' => $newPassword));
        $message = $this->substituteExtraMarkers($message);

        # Send email with the new password to the user.
        $this->cObj->sendNotifyEmail($message, $this->config['submittedData']['forgotEmail'], '',$this->config['passwordRecovery.']['emailFrom'],$this->config['passwordRecovery.']['nameFrom'], '');

        # Confirmation message for sending new password.
    	  $textMessagePassSent = $this->getContent('text_messageForgotPasswordNewPwSent');

		  #
		  # Perform actions for sending the current password to the user.
		  # DO NOT send current password if kb_md5fepw extension is installed.
		  # DO NOT send current password if a user wants to enable is account again after checking hash string.
		  #
      } elseif ($this->config['passwordRecovery.']['mode'] == 'currentpassword'
        AND !$this->config['md5fepwIsInstalled']
        AND empty($this->config['submittedData']['hashString'])) {

				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'password',
					'fe_users',
					sprintf('username=\'%s\' AND email=\'%s\' and pid IN (%s) %s',
						addslashes($this->config['submittedData']['username']),
					  addslashes($this->config['submittedData']['forgotEmail']),
						$this->config['userPIDList'],
 						$this->cObj->enableFields('fe_users') )
  			);

  			if ($GLOBALS['TYPO3_DB']->sql_error() ) {
				  t3lib_div::debug(array('SQL error:',
				  $GLOBALS['TYPO3_DB']->sql_error() ) );
        }

  			# Get the current password
      	$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
      	$currentPassword = $row['password'];

        # Email message.
        $message = $this->replaceSpecialCharMarkers($this->getContent('text_emailMessageCurrentPassword'),'text');

        # Replace markers with content in the Password-Sent-Message.
        $message = $this->substituteExtraMarkers($message, array('/###PASSWORD###/' => $currentPassword));
        $message = $this->substituteExtraMarkers($message);

        # Send email with the current password to the user.
        $this->cObj->sendNotifyEmail($message, $this->config['submittedData']['forgotEmail'], '',$this->config['passwordRecovery.']['emailFrom'],$this->config['passwordRecovery.']['nameFrom'], '');

        # Confirmation message for sending current password.
        $textMessagePassSent = $this->getContent('text_messageForgotPasswordCurPwSent');

		  }

      # Rendering the feuserloginsystem template.
      $template['total'] = $this->cObj->getSubpart($templateCode, '###TEMPLATE_FORGOT_PASSWORD_SENT###');

      $markerArray['###TEXT_HEADER###']     = $textHeaderPassSent;
		  $markerArray['###TEXT_MESSAGE###']    = $textMessagePassSent;

		  $tempContent = $this->cObj->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray, array());

    }

    $tempContent = $this->replaceSpecialCharMarkers($tempContent,'html');
	  $tempContent = $this->substituteExtraMarkers($tempContent);

		return $tempContent;

  }


  /**
  * This function is responsible for recording sucessfull and unsuccessful login attempts.
  * The function also checks if there were to many unsuccessful login attempts of an user
  * and disables the user if the limit of unsuccessful login attemps was reached. If a
  * user has been disabled, inform the user and the administrator depending on the configuration.
  *
  * @return void
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
  function checkLoginAttempt() {

    # These lines make sure that only login attempts will be recorded that are started by
    # the Regular Loginbox.
    if($this->config['submittedData']['module'] == 'rl' AND $this->module == 'smalllogin') {
      return FALSE;
    }

    # These lines make sure that only login attempts will be recorded that are started by
    # the Small Loginbox.
    if($this->config['submittedData']['module'] == 'sl' AND $this->module == 'regularlogin') {
      return FALSE;
    }

    # A user was logged in successfully.
    if($this->config['submittedData']['logintype'] == 'login' AND $GLOBALS['TSFE']->loginUser == 1) {

      $this->writeLoginSuccessIntoDB();

      return FALSE;

    }

    # A user caused an error during his login attempt.
    if($this->config['submittedData']['logintype'] == 'login' AND $GLOBALS['TSFE']->loginUser == 0) {

      # Record unsuccessful login attempt.

      list($errorRecordInfo,$userStatus) = $this->writeLoginErrorIntoDB();
      # User is disabled because of too many unsuccessful login attempts.
      if($userStatus == 2) {
        return intval(2);
      # User was disabled by the adminstrator manually.
      } elseif($userStatus == 3) {
        return intval(3);
      # User does not exist.
      } elseif($userStatus == 4) {
        return intval(4);
      # User is not (yet) disabled.
      } else {
        $feUserID = $errorRecordInfo['feUserID'];
        $currentLoginAttempts = $errorRecordInfo['currentLoginAttempts'];
        $uid = $errorRecordInfo['rowID'];
      }

      # Check if UserDisable is activated and if the limit
      # of unsuccessful login attempt was reached.
      if($this->config['loginProtection.']['enableUserDisable']
        AND $currentLoginAttempts != 0
        AND $currentLoginAttempts >= $this->config['loginProtection.']['disableUserAfter']) {

        # Get user data of the user who performed repeatedly an unsuccessfull login attempt.
        $userArray = $this->getUserArray($feUserID);

        # Get a hash string which will be written into the database table row of the user.
        $hashString = md5(uniqid (rand()));

        $this->userDisabled = TRUE;

        # Update information for the user in fe_users.
        $updateDataArray = array();
		    $updateDataArray['disable'] = intval(1);

  			# Finally disable user in fe_users database table.
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
          'fe_users',
          sprintf('uid=\'%s\' AND pid IN (%s) %s',
						addslashes($feUserID),
						$this->config['userPIDList'],
 						$this->cObj->enableFields('fe_users') ),
          $updateDataArray
        );
        unset($updateDataArray);

        if ($GLOBALS['TYPO3_DB']->sql_error() ) {
				  t3lib_div::debug(array('SQL error:',
				  $GLOBALS['TYPO3_DB']->sql_error() ) );
        }

        # Update information for login log.
        # Change success to "2". It means the user has just been disabled.
		    $updateDataArray = array();
		    $updateDataArray['success']           = intval(2);
		    $updateDataArray['timeofdisable']     = time();
		    $updateDataArray['hash']              = $hashString;

		    # Update login log in the database.
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
          'tx_feuserloginsystem_loginlog',
          sprintf('uid=\'%s\'',addslashes(intval($uid))),
          $updateDataArray
        );
        unset($updateDataArray);

        if ($GLOBALS['TYPO3_DB']->sql_error() ) {
				  t3lib_div::debug(array('SQL error:',
				  $GLOBALS['TYPO3_DB']->sql_error() ) );
        }

        ####
        # SEND EMAIL TO BLOCKED USER AND ADMIN depending on the configuration.
        ###

        $adminEmail = $this->config['loginProtection.']['emailFrom'];
        $adminName = $this->config['loginProtection.']['emailName'];

        # SEND EMAIL TO ADMIN
        # Check if the admin may be informed about the user disable.
        if($this->config['loginProtection.']['informAdmin'] AND $adminEmail != '') {

          # Check if the user may reactivate his account on his own and adjust the email content.
          if($this->config['loginProtection.']['reactivationByUser']) {

            $emailMessage = $this->getContent('text_emailMsgDisabledAccAdminInfo');

            $pattern[0] = '/###CURRENTLOGINATTEMPTS###/';
            $replacement[0] = $currentLoginAttempts;

            $emailMessage = preg_replace($pattern,$replacement,$emailMessage);

            $emailMessage = $this->replaceSpecialCharMarkers($emailMessage,'text');
            $emailMessage = $this->substituteExtraMarkers($emailMessage);

            $this->cObj->sendNotifyEmail($emailMessage, $adminEmail, '',$adminEmail,t3lib_div::getIndpEnv('HTTP_HOST'), '');

          # The user may not reactivate his account on his own. Adjust the email content.
          } else {

            $emailMessage = $this->getContent('text_emailMsgDisabledAccAdminInfoReact');

            $pattern[0] = '/###CURRENTLOGINATTEMPTS###/';
            #$pattern[1] = '/###REACTIVATIONLINK###/';
            $replacement[0] = $currentLoginAttempts;
            #$replacement[1] = 'href://www.obereigner.de/';

            $emailMessage = preg_replace($pattern,$replacement,$emailMessage);

            $emailMessage = $this->replaceSpecialCharMarkers($emailMessage,'text');
            $emailMessage = $this->substituteExtraMarkers($emailMessage);

            $this->cObj->sendNotifyEmail($emailMessage, $adminEmail, '',$adminEmail,t3lib_div::getIndpEnv('HTTP_HOST'), '');

          }

        }

        # SEND EMAIL TO USER

        $userEmail = $userArray['email'];
        $userName = $userArray['name'];

        # Check if the user may reactivate his acount on his own and adjust email content.
        if($this->config['loginProtection.']['reactivationByUser'] AND $userEmail != '') {

          $emailMessage = $this->getContent('text_emailMsgDisabledAccUserInfoReact');

          # Create the link attached with the hash string to the page with the PasswordRecovery module.
          $pwRecoveryPID = 0;
          if($this->module == 'regularlogin' AND $this->config['regularLoginbox.']['pwRecoveryPID']) {
            $pwRecoveryPID = $this->config['regularLoginbox.']['pwRecoveryPID'];
          }
          if($this->module == 'smalllogin' AND $this->config['smallLoginbox.']['pwRecoveryPID']) {
            $pwRecoveryPID = $this->config['smallLoginbox.']['pwRecoveryPID'];
          }
          if($pwRecoveryPID > 0) {
            $accountActivationUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
            $accountActivationUrl .= $this->pi_linkTP_keepPIvars_url(array('hash' => $hashString),0,0,$pwRecoveryPID);
          } else {
            $accountActivationUrl = '### NO PID TO THE PASSWORD RECOVERY MODULE AVAILABLE ###';
          }

          $pattern[0] = '/###CURRENTLOGINATTEMPTS###/';
          $pattern[1] = '/###REACTIVATIONLINK###/';
          $replacement[0] = $currentLoginAttempts;
          $replacement[1] = $accountActivationUrl;

          $emailMessage = preg_replace($pattern,$replacement,$emailMessage);

          $emailMessage = $this->replaceSpecialCharMarkers($emailMessage,'text');
          $emailMessage = $this->substituteExtraMarkers($emailMessage);

          $this->cObj->sendNotifyEmail($emailMessage, $userEmail, '',$adminEmail,t3lib_div::getIndpEnv('HTTP_HOST'), '');

        # The user may not reactivate his account on his own. Adjust the email content.
        } elseif(!$this->config['loginProtection.']['reactivationByUser'] AND $userEmail != '') {

          $emailMessage = $this->getContent('text_emailMsgDisabledAccUserInfo');

          $pattern[0] = '/###CURRENTLOGINATTEMPTS###/';
          $replacement[0] = $currentLoginAttempts;

          $emailMessage = preg_replace($pattern,$replacement,$emailMessage);

          $emailMessage = $this->replaceSpecialCharMarkers($emailMessage,'text');
          $emailMessage = $this->substituteExtraMarkers($emailMessage);

          $this->cObj->sendNotifyEmail($emailMessage, $userEmail, '',$adminEmail,t3lib_div::getIndpEnv('HTTP_HOST'), '');

        }



  			# A user has been disabled (TRUE)
  			return intval(1);

  		}

      # UserDisable is not activated that's why no user has been disabled (FALSE)
      return intval(0);

    }

  }

  /**
  * This function is responsible for writing/updating entries in
  * tx_feuserloginsystem_loginlog of users who did sign in successfully.
  *
  * @return void
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
  function writeLoginSuccessIntoDB() {

    $timeStamp  = time();
    $feUserID   = $GLOBALS['TSFE']->fe_user->user['uid'];
    $ip         = t3lib_div::getIndpEnv('REMOTE_ADDR');

    # Check if there is already an existing user in tx_feuserloginsystem_loginlog
    # who did not sign in sucessfully the first (few) time(s).
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid,feuserid,success,firstloginattempt,lastloginattempt,counter,ip',
			'tx_feuserloginsystem_loginlog',
			sprintf('feuserid=\'%s\' AND success=\'0\' AND ip=\'%s\'',
        addslashes($feUserID),
        addslashes($ip)),
			'',
			'lastloginattempt DESC',
			'1'
  	);

    if ($GLOBALS['TYPO3_DB']->sql_error() ) {
			t3lib_div::debug(array('SQL error:',
			$GLOBALS['TYPO3_DB']->sql_error() ) );
		}

  	$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

  	# A user who tried to sign in before (without success) was found.
    # Set is success status to "1".
    if(is_array($row)) {

      # Update information for login log.
		  $insertDataArray = array();
		  $insertDataArray['success']           = intval(1);
		  $insertDataArray['lastloginattempt']  = intval($timeStamp);
		  $insertDataArray['counter']           = intval($row['counter'] + 1);

		  # Update login log in the database.
      $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
        'tx_feuserloginsystem_loginlog',
        sprintf('uid=\'%s\'',addslashes(intval($row['uid']))),
        $insertDataArray
      );

      if ($GLOBALS['TYPO3_DB']->sql_error() ) {
				t3lib_div::debug(array('SQL error:',
				$GLOBALS['TYPO3_DB']->sql_error() ) );
			}

    # No user who tried to sign in before (without success) was found.
    # Create new entry and set success status to "1".
    } else {

      # Get information for login log.
		  $insertDataArray = array();
		  $insertDataArray['feuserid']          = $feUserID;
		  $insertDataArray['success']           = intval(1);
		  $insertDataArray['timeofdisable']     = intval(0);
		  $insertDataArray['firstloginattempt'] = $timeStamp;
		  $insertDataArray['lastloginattempt']  = $timeStamp;
		  $insertDataArray['counter']           = intval(1);
		  $insertDataArray['ip']                = $ip;
		  $insertDataArray['hash']              = '';

		  # Create login log entry in the database.
      $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_feuserloginsystem_loginlog',$insertDataArray);

      if ($GLOBALS['TYPO3_DB']->sql_error() ) {
				t3lib_div::debug(array('SQL error:',
				$GLOBALS['TYPO3_DB']->sql_error() ) );
			}

    }

  }

  /**
  * This function is responsible for writing/updating entries in
  * tx_feuserloginsystem_loginlog of users who did NOT sign in successfully.
  *
  * @return array     Returns the FeUserID, the current amount of login attempts if available and the uid of tx_feuserloginsystem_loginlog.
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
  function writeLoginErrorIntoDB() {
    $submittedUsername    = $this->config['submittedData']['user'];

    $feUserID             = 0;
    $timeStamp            = time();
    $currentLoginAttempts = 0;
    $ip                   = t3lib_div::getIndpEnv('REMOTE_ADDR');

    # Check if the frontend user entered an existing username
    # whose account is not deleted.
    # BUT THE ACCOUNT MAY BE ALREADY DISABLED.
    if($this->userExistsInDB($submittedUsername,'',TRUE) AND !$this->userExistsInDB($submittedUsername,'',FALSE)) {

      $userArray = $this->getUserArray($submittedUsername,'',TRUE);

      # CHECK HERE if the user has currently been disabled by the system
      # because there were to many unsuccessful login attempts. Check therefore
      # if the user is linked to an entry in the tx_feuserloginsystem_loginlog
      # database table.
      $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
        'tx_feuserloginsystem_loginlog.uid,
            tx_feuserloginsystem_loginlog.counter,
            tx_feuserloginsystem_loginlog.success',
        'tx_feuserloginsystem_loginlog,fe_users',
        'tx_feuserloginsystem_loginlog.feuserid = fe_users.uid' .
            ' AND tx_feuserloginsystem_loginlog.feuserid=\'' . $userArray['uid'] . '\'' .
            #' AND tx_feuserloginsystem_loginlog.success=\'' . '2' . '\'' .
            ' AND fe_users.disable=\'' . '1' . '\'',
        '',
        'tx_feuserloginsystem_loginlog.lastloginattempt DESC ',
        '1'
      );

      if ($GLOBALS['TYPO3_DB']->sql_error() ) {
				t3lib_div::debug(array('SQL error:',
				$GLOBALS['TYPO3_DB']->sql_error() ) );
      }

      $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

      # The user tried to sign in with correct username and password.
      # His account is still disabled though, because he tried to sign in with too
      # many unsuccessful login attempts before.
      # Keep success status to "2", set new time of login attempt and increase counter.
      if($row['success'] == 2) {

        # Update information for login log.
		    $updateDataArray = array();
		    $updateDataArray['lastloginattempt']  = intval(time());
        $updateDataArray['counter']           = intval($row['counter'] + 1);

		    # Update login log in the database.
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
          'tx_feuserloginsystem_loginlog',
          sprintf('uid=\'%s\'',addslashes(intval($row['uid']))),
          $updateDataArray
        );
        unset($updateDataArray);

        if ($GLOBALS['TYPO3_DB']->sql_error() ) {
				  t3lib_div::debug(array('SQL error:',
				  $GLOBALS['TYPO3_DB']->sql_error() ) );
        }

        return array(array('feUserID' => 0, 'currentLoginAttempts' => 0, 'rowID' => 0),intval(2));

      } else {

        return array(array('feUserID' => 0, 'currentLoginAttempts' => 0, 'rowID' => 0),intval(3));

      }

    }

    # Check if the frontend user entered an existing username
    # whose account is not disabled in any way.
    if($this->userExistsInDB($submittedUsername)) {

      $userArray = $this->getUserArray($submittedUsername);
      $feUserID = $userArray['uid'];

      # Check if the user already exists in tx_feuserloginsystem_loginlog
      # without signing in successfully.
      $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
        'uid,feuserid,success,firstloginattempt,lastloginattempt,counter,ip',
        'tx_feuserloginsystem_loginlog',
        sprintf('feuserid=\'%s\' AND success=\'0\' AND ip=\'%s\'',
          addslashes($feUserID),
          addslashes($ip)),
        '',
        'lastloginattempt DESC',
        '1'
      );

      if ($GLOBALS['TYPO3_DB']->sql_error() ) {
				t3lib_div::debug(array('SQL error:',
				$GLOBALS['TYPO3_DB']->sql_error() ) );
      }

      $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

      # The user tried to sign in before (without success) was found.
      # Keep success status to "0", set new time of login attempt and increase counter.
      if(is_array($row)) {

        $currentLoginAttempts = intval($row['counter'] + 1);

        # Update information for login log.
        $updateDataArray = array();
        $updateDataArray['lastloginattempt']  = intval($timeStamp);
        $updateDataArray['counter']           = intval($row['counter'] + 1);

        # Update login log in the database.
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
          'tx_feuserloginsystem_loginlog',
          sprintf('uid=\'%s\'',addslashes(intval($row['uid']))),
          $updateDataArray
        );
        unset($updateDataArray);

        if ($GLOBALS['TYPO3_DB']->sql_error() ) {
				  t3lib_div::debug(array('SQL error:',
				  $GLOBALS['TYPO3_DB']->sql_error() ) );
        }

      # The user did not try to sign in before (without success) was found.
      # Create new entry with success status "0", time of first login attempt and counter "1".
      } else {

        $currentLoginAttempts = intval(1);

        # Get information for login log.
        $insertDataArray = array();
        $insertDataArray['feuserid']          = $feUserID;
        $insertDataArray['success']           = intval(0);
        $insertDataArray['timeofdisable']     = intval(0);
        $insertDataArray['firstloginattempt'] = $timeStamp;
        $insertDataArray['lastloginattempt']  = $timeStamp;
        $insertDataArray['counter']           = intval(1);
        $insertDataArray['ip']                = $ip;
        $insertDataArray['hash']              = '';

        # Create login log entry in the database.
        $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_feuserloginsystem_loginlog',$insertDataArray);

        if ($GLOBALS['TYPO3_DB']->sql_error() ) {
				  t3lib_div::debug(array('SQL error:',
				  $GLOBALS['TYPO3_DB']->sql_error() ) );
        }
        unset($insertDataArray);

      }

      return array(array('feUserID' => $feUserID, 'currentLoginAttempts' => $currentLoginAttempts, 'rowID' => $row['uid']),intval(0));


    # The frontend user did not enter an existing username.
    } else {

      # Get information for login log.
		  $insertDataArray = array();
		  $insertDataArray['feuserid']          = intval(0);
		  $insertDataArray['success']           = intval(0);
		  $insertDataArray['timeofdisable']     = intval(0);
		  $insertDataArray['firstloginattempt'] = $timeStamp;
		  $insertDataArray['lastloginattempt']  = $timeStamp;
		  $insertDataArray['counter']           = intval(1);
		  $insertDataArray['ip']                = $ip;
		  $insertDataArray['hash']              = '';

		  # Create login log entry in the database.
      $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_feuserloginsystem_loginlog',$insertDataArray);

      if ($GLOBALS['TYPO3_DB']->sql_error() ) {
				t3lib_div::debug(array('SQL error:',
				$GLOBALS['TYPO3_DB']->sql_error() ) );
      }
      unset($insertDataArray);

      return array(array('feUserID' => 0, 'currentLoginAttempts' => 0, 'rowID' => 0),intval(4));

    }

  }

  /**
  * The method generates the link to the "PasswordRecovery" module
  * depending on $module. $module can be either 'regularlogin' or
  * 'smalllogin'.
  *
  * @param string							The chosen module like smalllogin or regularlogin
  * @return string         		The link to the module "PasswordRecovery".
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
	function getForgotPasswordLink($module) {

		# Generate the link to get to PasswordRecovery.
		# In case that '$this->config['smallLoginbox.']['pwRecoveryPID']' is '0',
		# the function $this->pi_linkTP_keepPIvars_url() uses as 'id' the current page
		if($module == 'smalllogin') {
	  	$forgotPasswordUrl = $this->pi_linkTP_keepPIvars_url(array(),0,0,$this->config['smallLoginbox.']['pwRecoveryPID']);
	  	$forgotPasswordLink = '<a href="'.$forgotPasswordUrl.'">'.$this->getContent('label_forgotPassword').'</a>';
	  }

	  # Generate the link to get to PasswordRecovery.
		# In case that '$this->config['regularLoginbox.']['pwRecoveryPID']' is '0',
		# the function $this->pi_linkTP_keepPIvars_url() uses as 'id' the current page
		if($module == 'regularlogin') {
	  	$forgotPasswordUrl = $this->pi_linkTP_keepPIvars_url(array(),0,0,$this->config['regularLoginbox.']['pwRecoveryPID']);
	  	$forgotPasswordLink = '<a href="'.$forgotPasswordUrl.'">'.$this->getContent('label_forgotPassword').'</a>';
	  }

	  return $forgotPasswordLink;
	}

	/**
  * The method checks if redirection is enabled and configured. It checks redirection
  * not only for logins but also for logouts. In the case that redirection is
  * disabled or not configured, nothing is going to happen. Otherwise the user is
  * going to be send to the specified page.
  *
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
	function checkRedirection() {

		# LOGIN REDIRECTION
    # Redirect user to another page if redirection is enabled and configured.
    if($this->config['forceNoLoginRedirect'] != 1 && $this->config['submittedData']['logintype'] == 'login') {
			$redirectPID = $this->getRedirectionPID('login');
			if($redirectPID != 0) {
				$loginRedirectUrl = $this->pi_getPageLink($redirectPID);
    		header('Location: '.t3lib_div::makeRedirectUrl($loginRedirectUrl));
    		exit;;
			}
		}

		# LOGOUT REDIRECTION
    # Redirect user to another page if redirection is enabled and configured.
		if($this->config['forceNoLogoutRedirect'] != 1 && $this->config['submittedData']['redirectToUrlAfterLogout']) {
			$logoutRedirectUrl = $this->pi_getPageLink($this->config['submittedData']['redirectToUrlAfterLogout']);
    	header('Location: '.t3lib_div::makeRedirectUrl($logoutRedirectUrl));
    	exit;
		}

	}

  /**
  * Check if the submitted Captcha string matches the one in the Captcha Image.
  *
  * @return boolean
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
  function checkCaptchaWord($captchaResponse) {

		if(is_object($this->freeCap) && $this->freeCap->checkWord($captchaResponse)) {
			return TRUE;
		} else {
			return FALSE;
		}

	}

	/**
  * The method checks if a user with $username AND/OR $email exists in the DB.
  * If YES, return TRUE otherwise return FALSE.
  *
  * @param  string    $user         Username or UID
  * @param	string		$email				OPTIONAL: Email address
  * @param  boolean   $ignoreEnableFields OPTIONAL:
  * @return boolean                 TRUE or FALSE
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
	function userExistsInDB($user, $email = '',$ignoreEnableFields = FALSE) {

		#$userID   = (is_int($user) AND $user > 0) ? addslashes($user) : 0;
		#$userName = (!is_int($user) AND $user != '') ? addslashes($user) : '';
		$user    = $user ? addslashes($user) : '';
		$email    = $email ? addslashes($email) : '';

		if(($user) AND $email) {
      # Check if there is an entry in the database with
		  # submitted username and email address.
  	 $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			 '*',
			 'fe_users',
			 sprintf('(username=\'%s\' OR uid=\'%s\') AND email=\'%s\' and pid IN (%s) %s',
          $user,
				  $user,
				  $email,
				  $this->config['userPIDList'],
 				  ($ignoreEnableFields ? ' AND deleted=\'0\'' : $this->cObj->enableFields('fe_users')) )
      );

      if ($GLOBALS['TYPO3_DB']->sql_error() ) {
				t3lib_div::debug(array('SQL error:',
				$GLOBALS['TYPO3_DB']->sql_error() ) );
			}

			# If there is an entry in the database,
  	  # return TRUE otherwise FALSE.
		  if($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
    		return TRUE;
      }

    }

    if(($user) AND !$email) {
      # Check if there is an entry in the database with submitted username.
      $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
      '*',
      'fe_users',
      sprintf('(username=\'%s\' OR uid=\'%s\') AND pid IN (%s) %s',
        $user,
        $user,
        $this->config['userPIDList'],
        ($ignoreEnableFields ? ' AND deleted=\'0\'' : $this->cObj->enableFields('fe_users')) )
      );

      if ($GLOBALS['TYPO3_DB']->sql_error() ) {
				t3lib_div::debug(array('SQL error:',
				$GLOBALS['TYPO3_DB']->sql_error() ) );
			}

			# If there is an entry in the database,
  	  # return TRUE otherwise FALSE.
		  if($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
    		return TRUE;
      }

    }

    return FALSE;

	}

	/**
  * The method checks if a user with $username AND/OR $email exists in the DB.
  * If YES, return an array with the user information.
  *
  * @param  string    $user         Username or UID
  * @param	string		$email				OPTIONAL: Email address
  * @param  boolean   $ignoreEnableFields OPTIONAL:
  * @return array                 	Array with the user information
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
	function getUserArray($user, $email='', $ignoreEnableFields = FALSE) {

		#$userID   = (is_int($user) AND $user > 0) ? addslashes($user) : 0;
		#$userName = (!is_int($user) AND $user != '') ? addslashes($user) : '';
		$user    = $user ? addslashes($user) : '';
		$email    = $email ? addslashes($email) : '';

		if(($user) AND $email) {
      # Check if there is an entry in the database with
		  # submitted username/uid and email address.
  	 $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			 '*',
			 'fe_users',
			 sprintf('(username=\'%s\' OR uid=\'%s\') AND email=\'%s\' and pid IN (%s) %s',
				  $user,
				  $user,
				  $email,
				  $this->config['userPIDList'],
 				 ($ignoreEnableFields ? ' AND deleted=\'0\'' : $this->cObj->enableFields('fe_users')) )
      );

      if ($GLOBALS['TYPO3_DB']->sql_error() ) {
				t3lib_div::debug(array('SQL error:',
				$GLOBALS['TYPO3_DB']->sql_error() ) );
			}

			# If there is an entry in the database,
      # return TRUE otherwise FALSE.
		  if($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {

        # Get the array with the user information
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

        return $row;
      }

    }

    if(($user) AND !$email) {
      # Check if there is an entry in the database with submitted username.
      $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
      '*',
      'fe_users',
      sprintf('(username=\'%s\' OR uid=\'%s\') AND pid IN (%s) %s',
        $user,
        $user,
        $this->config['userPIDList'],
        ($ignoreEnableFields ? ' AND deleted=\'0\'' : $this->cObj->enableFields('fe_users')) )
      );

      if ($GLOBALS['TYPO3_DB']->sql_error() ) {
				t3lib_div::debug(array('SQL error:',
				$GLOBALS['TYPO3_DB']->sql_error() ) );
			}

			# If there is an entry in the database,
      # return TRUE otherwise FALSE.
		  if($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {

        # Get the array with the user information
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

        return $row;
      }

    }

    return array();

	}

	/**
  * The method is responsible for choosing the right redirection PID either for login or for logout
	* out of four given redirection PIDs.
  *
  * Priority
	* ------------------------------------------------------------------------------------------------
	* HIGH:      	$this->config['log[in/out]RedirectAssInPlugFlex']      # set in the plugin flexform
	*             $this->config['log[in/out]RedirectForUser']            # set in the table 'fe_users'
	*             $this->config['log[in/out]RedirectForUsergroup']       # set in the table 'fe_groups'
	* LOW:        $this->config['log[in/out]RedirectAssInConstEditor']   # set in the Constant Editor or in Setup
	*
	* @param string      $redirectType     'login' or 'logout' depending on the kind of redirection
  * @return string                        redirectPID either for login or for logout
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
	function getRedirectionPID($redirectType) {

		if ($redirectType == 'login') {

			# Get the page path where the user will be redirected to after login
			$loginRedirectPID = $this->config['loginRedirectAssInPlugFlex'] ? $this->config['loginRedirectAssInPlugFlex'] : $this->config['loginRedirectForUser'];
	  	$loginRedirectPID = $loginRedirectPID ? $loginRedirectPID : $this->config['loginRedirectForUsergroup'];
	  	$loginRedirectPID = $loginRedirectPID ? $loginRedirectPID : $this->config['loginRedirectAssInConstEditor'];
	  	$loginRedirectPID = $loginRedirectPID ? $loginRedirectPID : intval(0);

			return $loginRedirectPID;
	  }

	  if ($redirectType == 'logout') {

			 # Get the page path where the user will be redirected to after logout
	  	$logoutRedirectPID = $this->config['logoutRedirectAssInPlugFlex'] ? $this->config['logoutRedirectAssInPlugFlex'] : $this->config['logoutRedirectForUser'];
      $logoutRedirectPID = $logoutRedirectPID ? $logoutRedirectPID : $this->config['logoutRedirectForUsergroup'];
      $logoutRedirectPID = $logoutRedirectPID ? $logoutRedirectPID : $this->config['logoutRedirectAssInConstEditor'];
      $logoutRedirectPID = $logoutRedirectPID ? $logoutRedirectPID : intval(0);

    	return $logoutRedirectPID;
		}

  }

  /**
  * Returns a generated string with all the PIDs which will be used for the login and logout process.
	* The string is created by the given PIDs and the level of recursion.
  *
  * @return string                      The list of PIDs.
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
 	function initUserPIDList() {
		$pidList = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'users', 'sDEF');
		$pidList = $pidList ? $pidList : $this->tempConf['userPidList'];
		$pidList = $pidList ? implode(t3lib_div::intExplode(',', $pidList), ',') : $GLOBALS['TSFE']->id;

		$recursive = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'recursive', 'sDEF');
		$recursive = $recursive ? $recursive : $this->tempConf['recursive'];
		$recursive = $recursive ? intval($recursive) : intval(0);

		$storagePage = $GLOBALS['TSFE']->getStorageSiterootPids();

		# If a PID was also set in the Gerneral Record Storage page,
		# add it to the list of PIDs.
		if($storagePage['_STORAGE_PID']	> 0) {
			$pidList = $this->pi_getPidList($pidList . ',' . $storagePage['_STORAGE_PID'], $recursive);
		} else {
			$pidList = $this->pi_getPidList($pidList, $recursive);
		}

		return $pidList;
	}

	/**
  * The method replaces possible markers in the content of this extension. Markers
  * could be "NAME", "USERNAME" or "EMAIL" but also email messages.
  *
  * #@param boolean    $adminMode            ADMIN MODE: Return a user's password
  * @param  string    $content          		Content with possible markers.
  * @param	array			$specialSubstitution	OPTIONAL: substituion of special pattern; overriding pattern with special content
  * @return string                      		Content with replaced markers.
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
	function substituteExtraMarkers($content, $specialSubstitution = array()) {

    $tempContent = $content;

    if(!empty($specialSubstitution)) {

			foreach($specialSubstitution as $pattern => $replacement) {
				$tempContent = preg_replace($pattern,$replacement,$tempContent);
			}

			return $tempContent;
		}

    if($this->userIsOnline) {

		  $pattern[0] = '/###NAME###/';
      $pattern[1] = '/###EMAIL###/';
      $pattern[2] = '/###USERNAME###/';
      $pattern[3] = '/###PASSWORD###/';
      $pattern[4] = '/###TITLE###/';
      $pattern[6] = '/###FIRSTNAME###/';
      $pattern[7] = '/###LASTNAME###/';
      $pattern[8] = '/###CURRENTLOGINATTEMPTS###/';
      $replacement[0] = $GLOBALS['TSFE']->fe_user->user['name'];
      $replacement[1] = $GLOBALS['TSFE']->fe_user->user['email'];
      $replacement[2] = $GLOBALS['TSFE']->fe_user->user['username'];
      $replacement[3] = $GLOBALS['TSFE']->fe_user->user['password'];
      $replacement[4] = $GLOBALS['TSFE']->fe_user->user['title'];
      $replacement[6] = $GLOBALS['TSFE']->fe_user->user['first_name'];
      $replacement[7] = $GLOBALS['TSFE']->fe_user->user['last_name'];
      $replacement[8] = $this->getCurrentLoginAttemptsOfUser($GLOBALS['TSFE']->fe_user->user['uid']);

      $tempContent = preg_replace($pattern,$replacement,$tempContent);

      unset($pattern);
      unset($replacement);

    } else {

    	$submittedUsername = $this->config['submittedData']['username'] ? $this->config['submittedData']['username'] : $this->config['submittedData']['user'];
    	$submittedEmail = $this->config['submittedData']['forgotEmail'];

      if($submittedUsername != '' AND $this->userExistsInDB($submittedUsername,$submittedEmail,TRUE)) {

				$userInfo = $this->getUserArray($submittedUsername,$submittedEmail,TRUE);

        $pattern[0] = '/###EMAIL###/';
        $pattern[1] = '/###USERNAME###/';
        $pattern[2] = '/###NAME###/';
      	$pattern[3] = '/###TITLE###/';
      	$pattern[5] = '/###FIRSTNAME###/';
      	$pattern[6] = '/###LASTNAME###/';
      	$pattern[7] = '/###CURRENTLOGINATTEMPTS###/';
      	$replacement[0] = $userInfo['email'];
        $replacement[1] = $userInfo['username'];
				$replacement[2] = $userInfo['name'];
				$replacement[3] = $userInfo['title'];
				$replacement[5] = $userInfo['first_name'];
				$replacement[6] = $userInfo['last_name'];
				$replacement[7] = $this->getCurrentLoginAttemptsOfUser($userInfo['uid']);

				$tempContent = preg_replace($pattern,$replacement,$tempContent);

        unset($pattern);
        unset($replacement);
			}

		}

		$pattern[0] = '/###HTTP_HOST###/';
		$pattern[1] = '/###TYPO3_HOST_ONLY###/';
		$pattern[2] = '/###LOGINATTEMPTSUNTILDISABLE###/';
		$pattern[3] = '/###CURRENTLOGINATTEMPTS###/';
    $replacement[0] = t3lib_div::getIndpEnv('HTTP_HOST');
    $replacement[1] = t3lib_div::getIndpEnv('TYPO3_HOST_ONLY');
    $replacement[2] = $this->config['loginProtection.']['disableUserAfter'];
    $replacement[3] = $this->getCurrentLoginAttemptsOfUser('0');

    $tempContent = preg_replace($pattern,$replacement,$tempContent);

    unset($pattern);
    unset($replacement);

    return $tempContent;
  }

  /**
  * The method gets the number of current login attempts of a user.
  *
  * @param  string    $feUserID         User ID
  * @return integer                     The number of login attempts
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
  function getCurrentLoginAttemptsOfUser($feUserID) {

    # If $loginAttempts is already set in $this->config, return $loginAttempts.
    #($loginAttempts is set when a user was redirect to the Regular Loginbox
    # because he entered a wrong password.)
    if($this->config['submittedData']['loginAttempts']) {
      return $this->config['submittedData']['loginAttempts'];
    }

    # Return 0 if $feUserID is 0.
    # See function substituteExtraMarkers().
    if($feUserID == 0) { return 0; }

    $ip = t3lib_div::getIndpEnv('REMOTE_ADDR');

    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
      'counter',
      'tx_feuserloginsystem_loginlog',
      sprintf('feuserid=\'%s\' AND ip=\'%s\'',
        addslashes($feUserID),
        addslashes($ip)),
      '',
      'lastloginattempt DESC',
      '1'
    );

    if ($GLOBALS['TYPO3_DB']->sql_error() ) {
			t3lib_div::debug(array('SQL error:',
			$GLOBALS['TYPO3_DB']->sql_error() ) );
    }

    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

    return $row['counter'] ? $row['counter'] : '0';
  }

  /**
  * The method replaces possible special char markers in the content of this extension.
  * A markers could be "%br%". Markers are being replaced depending on the OutputType.
  * If $outputType is TEXT for example, the marker "%br%" will be replaced with "chr(10)"
  * (new line). If $outputType is HTML though, the marker "%br%" will be replaced with "<br>".
  *
  * @param  string    $content          Content with possible markers.
  * @param  string    $outputType       OutputTyp can either be TEXT or HTML.
  * @return string                      Content with replaced markers.
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
  function replaceSpecialCharMarkers($content, $outputType) {
    $tempContent = $content;

    # Marker replacement depending on the OutputTyp "text" or "html"
    if(strtolower($outputType) == 'text') {
      $pattern[0] = '/%br%/';
      $replacement[0] = chr(10);

    } elseif(strtolower($outputType) == 'html') {
      $pattern[0] = '/%br%/';
      #$pattern[1] = '/%p%/';
      #$pattern[2] = '/%/p%/';
      $replacement[0] = '<br />';
			#$replacement[1] = '<p>';
			#$replacement[2] = '</p>';
    }

    $tempContent = preg_replace($pattern,$replacement,$tempContent);

    unset($pattern);
    unset($replacement);

    return $tempContent;
  }

	/**
  * The method returns a string of additional parameters for the URL. This is especially
  * necessary if the Small Loginbox appears on many pages that have extension specific
  * parameters in the URL. To make sure that they don't get lost, they are added to all URLs
  * after removing standard parameters ($exludeList) and own extension parameters.
  *
  * @return array                     Returns additional parameters for the url.
  * @author Rupert Germann <rupi@gmx.li>
  *
  * @author Andre Obereigner <feuserloginsystem@obereigner.de> - I just adapted the function.
  */
	function addAdditionalParams() {

    $queryString = explode('&', t3lib_div::implodeArrayForUrl('', $GLOBALS['_GET'])) ;
    if ($queryString) {
        while (list(, $val) = each($queryString)) {
            $tmp = explode('=', $val);
            $paramArray[$tmp[0]] = $val;
        }

        $excludeList = 'id,L,no_cache';
        while (list($key, $val) = each($paramArray)) {
            if (!$val || ($excludeList && t3lib_div::inList($excludeList, $key))) {
                unset($paramArray[$key]);
            }
        }

        $excludeAlsoExtensionVar = 'tx_feuserloginsystem_pi1';
        reset($paramArray);
        while (list($key, $val) = each($paramArray)) {
            if (!$val || ($excludeAlsoExtensionVar && t3lib_div::isFirstPartOfStr($key, $excludeAlsoExtensionVar))) {
                unset($paramArray[$key]);
            }
        }

        reset($paramArray);
        while (list(, $val) = each($paramArray)) {
            $tmp = explode('=', $val);
            $finalParamArray[$tmp[0]] = $tmp[1];
        }

        return $finalParamArray;
    }

    return array();

  }


	/**
  * The method returns the personalized content of this extension.
  * The personalized content can be entered per TypoScript into the SETUP field
  * of an template.
  *
  * @param  string    $languageLabel    The Language Label corresponding to the Language Labels in locallang.xml
  * @param  boolean   $commonLabel      OPTIONAL: Set TRUE if the LanguageLabel is the same for the Small and the Regular Loginbox.
  * @return string                      The personalized label or content.
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
	function getLLContent($languageLabel,$commonLabel = FALSE) {

    if($commonLabel AND $this->module == 'regularlogin') {
      if($this->config['regularLoginbox.']['_LOCAL_LANG.'][$this->LLkey.'.'][$languageLabel]) {
        return $this->config['regularLoginbox.']['_LOCAL_LANG.'][$this->LLkey.'.'][$languageLabel];
      } else {
        return '';
      }
    }

    if($commonLabel AND $this->module == 'smalllogin') {
      if($this->config['smallLoginbox.']['_LOCAL_LANG.'][$this->LLkey.'.'][$languageLabel]) {
        return $this->config['smallLoginbox.']['_LOCAL_LANG.'][$this->LLkey.'.'][$languageLabel];
      } else {
        return '';
      }
    }

    if($this->config['_LOCAL_LANG.'][$this->LLkey.'.'][$languageLabel]) {
      return $this->config['_LOCAL_LANG.'][$this->LLkey.'.'][$languageLabel];
    } else {
      return '';
    }


  }

	/**
  * The method returns the personalized content of this extension.
  * The personalized content can be entered in the FlexForm. If no
  * personalized content exists in FlexForm, the standard content is used.
  *
  * @param  string    $languageLabel    The Language Label corresponding to the Language Labels in locallang.xml
  * @return string                      The personalized label or content.
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
	function getContent($languageLabel) {

    switch ($languageLabel) {
		  case 'text_messageLogin':
		    $textMessageLogin = $this->getLLContent($languageLabel, FALSE);
        $textMessageLogin = $textMessageLogin ? $textMessageLogin : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'welcomemessage', 'sRegularLoginbox');
        return $textMessageLogin = $textMessageLogin ? $textMessageLogin : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_headerLogin':
			  $textHeaderLogin = $this->getLLContent($languageLabel, FALSE);
        $textHeaderLogin = $textHeaderLogin ? $textHeaderLogin : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'welcomeheader', 'sRegularLoginbox');
        return $textHeaderLogin ? $textHeaderLogin : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_messageSuccess':
			  $textMessageSuccess = $this->getLLContent($languageLabel, FALSE);
        $textMessageSuccess = $textMessageSuccess ? $textMessageSuccess : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'successmessage', 'sRegularLoginbox');
        return $textMessageSuccess ? $textMessageSuccess : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_headerSuccess':
			  $textHeaderSuccess = $this->getLLContent($languageLabel, FALSE);
        $textHeaderSuccess = $textHeaderSuccess ? $textHeaderSuccess : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'successheader', 'sRegularLoginbox');
        return $textHeaderSuccess ? $textHeaderSuccess : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_messageStatus':
			  $textMessageStatus = $this->getLLContent($languageLabel, FALSE);
        $textMessageStatus = $textMessageStatus ? $textMessageStatus : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'statusmessage', 'sRegularLoginbox');
        return $textMessageStatus ? $textMessageStatus : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_headerStatus':
			  $textHeaderStatus = $this->getLLContent($languageLabel, FALSE);
        $textHeaderStatus = $textHeaderStatus ? $textHeaderStatus : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'statusheader', 'sRegularLoginbox');
        return $textHeaderStatus ? $textHeaderStatus : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_messageError':
			  $textMessageError = $this->getLLContent($languageLabel, FALSE);
        $textMessageError = $textMessageError ? $textMessageError : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'errormessage', 'sRegularLoginbox');
        return $textMessageError ? $textMessageError : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_messageErrorNotExistingUser':
			  $textMessageErrorNotExistingUser = $this->getLLContent($languageLabel, FALSE);
        $textMessageErrorNotExistingUser = $textMessageErrorNotExistingUser ? $textMessageErrorNotExistingUser : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'errormessagenotexistinguser', 'sRegularLoginbox');
        return $textMessageErrorNotExistingUser ? $textMessageErrorNotExistingUser : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_messageErrorDisabledUser':
			  $textMessageErrorDisabledUser = $this->getLLContent($languageLabel, FALSE);
        $textMessageErrorDisabledUser = $textMessageErrorDisabledUser ? $textMessageErrorDisabledUser : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'errormessagedisableduser', 'sRegularLoginbox');
        return $textMessageErrorDisabledUser ? $textMessageErrorDisabledUser : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_messageErrorUserHasBeenDisabled':
			  $textMessageErrorUserHasBeenDisabled = $this->getLLContent($languageLabel, FALSE);
        $textMessageErrorUserHasBeenDisabled = $textMessageErrorUserHasBeenDisabled ? $textMessageErrorUserHasBeenDisabled : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'errormessageuserhasbeendisabled', 'sRegularLoginbox');
        return $textMessageErrorUserHasBeenDisabled ? $textMessageErrorUserHasBeenDisabled : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_messageErrorUserHasAlreadyBeenDisabled':
			  $textMessageErrorUserHasAlreadyBeenDisabled = $this->getLLContent($languageLabel, FALSE);
        $textMessageErrorUserHasAlreadyBeenDisabled = $textMessageErrorUserHasAlreadyBeenDisabled ? $textMessageErrorUserHasAlreadyBeenDisabled : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'errormessageuserhasalreadybeendisabled', 'sRegularLoginbox');
        return $textMessageErrorUserHasAlreadyBeenDisabled ? $textMessageErrorUserHasAlreadyBeenDisabled : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_headerError':
			  $textHeaderError = $this->getLLContent($languageLabel, FALSE);
        $textHeaderError = $textHeaderError ? $textHeaderError : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'errorheader', 'sRegularLoginbox');
        return $textHeaderError ? $textHeaderError : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_messageLogout':
			  $textMessageLogout = $this->getLLContent($languageLabel, FALSE);
        $textMessageLogout = $textMessageLogout ? $textMessageLogout : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'logoutmessage', 'sRegularLoginbox');
        return $textMessageLogout ? $textMessageLogout : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_headerLogout':
			  $textHeaderLogout = $this->getLLContent($languageLabel, FALSE);
        $textHeaderLogout = $textHeaderLogout ? $textHeaderLogout : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'logoutheader', 'sRegularLoginbox');
        return $textHeaderLogout ? $textHeaderLogout : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_headerForgotPasswordInstruction':
			  $textHeaderForgotPw = $this->getLLContent($languageLabel, FALSE);
        $textHeaderForgotPw = $textHeaderForgotPw ? $textHeaderForgotPw : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'forgotpasswordheader', 'sPasswordRecovery');
        return $textHeaderForgotPw ? $textHeaderForgotPw : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_messageSendCurrentPasswordInstruction':
			  $textMessageSendCurPw = $this->getLLContent($languageLabel, FALSE);
        $textMessageSendCurPw = $textMessageSendCurPw ? $textMessageSendCurPw : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'sendcurrentpasswordinstruction', 'sPasswordRecovery');
        return $textMessageSendCurPw ? $textMessageSendCurPw : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_messageSendNewPasswordInstruction':
			  $textMessageSendNewPw = $this->getLLContent($languageLabel, FALSE);
        $textMessageSendNewPw = $textMessageSendNewPw ? $textMessageSendNewPw : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'sendnewpasswordinstruction', 'sPasswordRecovery');
        return $textMessageSendNewPw ? $textMessageSendNewPw : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_headerForgotPasswordEmailSent':
			  $textHeaderPwSent = $this->getLLContent($languageLabel, FALSE);
        $textHeaderPwSent = $textHeaderPwSent ? $textHeaderPwSent : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'passwordsentheader', 'sPasswordRecovery');
        return $textHeaderPwSent ? $textHeaderPwSent : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_messageForgotPasswordCurPwSent':
			  $textMessageCurPwSent = $this->getLLContent($languageLabel, FALSE);
        $textMessageCurPwSent = $textMessageCurPwSent ? $textMessageCurPwSent : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'currentpasswordsentmessage', 'sPasswordRecovery');
        return $textMessageCurPwSent ? $textMessageCurPwSent : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_messageForgotPasswordNewPwSent':
			  $textMessageNewPwSent = $this->getLLContent($languageLabel, FALSE);
        $textMessageNewPwSent = $textMessageNewPwSent ? $textMessageNewPwSent : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'newpasswordsentmessage', 'sPasswordRecovery');
        return $textMessageNewPwSent ? $textMessageNewPwSent : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'label_forgotPassword':
			  $labelForgotPassButton = $this->getLLContent('label_forgotPassword', TRUE);
        $labelForgotPassButton = $labelForgotPassButton ? $labelForgotPassButton : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'forgotpasswordbutton', 'sTranslation');
        return $labelForgotPassButton ? $labelForgotPassButton : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'label_logout':
			  $labelLogoutButton = $this->getLLContent('label_logout', TRUE);
        $labelLogoutButton = $labelLogoutButton ? $labelLogoutButton : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'logoutbutton', 'sTranslation');
        return $labelLogoutButton ? $labelLogoutButton : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'label_login':
        $labelLoginButton = $this->getLLContent('label_login', TRUE);
        $labelLoginButton = $labelLoginButton ? $labelLoginButton : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'loginbutton', 'sTranslation');
        return $labelLoginButton ? $labelLoginButton : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'label_username':
        $labelUsername = $this->getLLContent('label_username', TRUE);
        $labelUsername = $labelUsername ? $labelUsername : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'userlabel', 'sTranslation');
        return $labelUsername ? $labelUsername : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'label_password':
        $labelPassword = $this->getLLContent('label_password', TRUE);
        $labelPassword = $labelPassword ? $labelPassword : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'passwordlabel', 'sTranslation');
        return $labelPassword ? $labelPassword : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_loggedInAs':
			  $textLoggedInAs = $this->getLLContent($languageLabel, FALSE);
        $textLoggedInAs = $textLoggedInAs ? $textLoggedInAs : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'loggedinaslabel', 'sSmallLoginbox');
        return $textLoggedInAs ? $textLoggedInAs : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_emailMessageCurrentPassword':
			  $textEmailMessageCurPassword = $this->getLLContent($languageLabel, FALSE);
        $textEmailMessageCurPassword = $textEmailMessageCurPassword ? $textEmailMessageCurPassword : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'emailMessageCurrentPassword', 'sPasswordRecovery');
        return $textEmailMessageCurPassword ? $textEmailMessageCurPassword : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_emailMessageNewPassword':
			  $textEmailMessageNewPassword = $this->getLLContent($languageLabel, FALSE);
        $textEmailMessageNewPassword = $textEmailMessageNewPassword ? $textEmailMessageNewPassword : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'emailMessageNewPassword', 'sPasswordRecovery');
        return $textEmailMessageNewPassword ? $textEmailMessageNewPassword : $this->pi_getLL($languageLabel,'',1);
			break;
      case 'text_headerForgotPwInstEnableDisAcc':
        $textHeaderForgotPwInstEnableDisAcc = $this->getLLContent($languageLabel, FALSE);
        $textHeaderForgotPwInstEnableDisAcc = $textHeaderForgotPwInstEnableDisAcc ? $textHeaderForgotPwInstEnableDisAcc : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'enableDisabledAccountHeader', 'sPasswordRecovery');
        return $textHeaderForgotPwInstEnableDisAcc ? $textHeaderForgotPwInstEnableDisAcc : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_emailMsgDisabledAccUserInfo':
			  $textEmailMsgDisabledAccUserInfo = $this->getLLContent($languageLabel, FALSE);
        $textEmailMsgDisabledAccUserInfo = $textEmailMsgDisabledAccUserInfo ? $textEmailMsgDisabledAccUserInfo : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'emailMsgDisabledAccUserInfo', 'sLoginProtection');
        return $textEmailMsgDisabledAccUserInfo ? $textEmailMsgDisabledAccUserInfo : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_emailMsgDisabledAccUserInfoReact':
			  $textEmailMsgDisabledAccUserInfoReact = $this->getLLContent($languageLabel, FALSE);
        $textEmailMsgDisabledAccUserInfoReact = $textEmailMsgDisabledAccUserInfoReact ? $textEmailMsgDisabledAccUserInfoReact : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'emailMsgDisabledAccUserInfoReact', 'sLoginProtection');
        return $textEmailMsgDisabledAccUserInfoReact ? $textEmailMsgDisabledAccUserInfoReact : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_emailMsgDisabledAccAdminInfo':
			  $textEmailMsgDisabledAccAdminInfo = $this->getLLContent($languageLabel, FALSE);
        $textEmailMsgDisabledAccAdminInfo = $textEmailMsgDisabledAccAdminInfo ? $textEmailMsgDisabledAccAdminInfo : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'emailMsgDisabledAccAdminInfo', 'sLoginProtection');
        return $textEmailMsgDisabledAccAdminInfo ? $textEmailMsgDisabledAccAdminInfo : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'text_emailMsgDisabledAccAdminInfoReact':
			  $textEmailMsgDisabledAccAdminInfoReact = $this->getLLContent($languageLabel, FALSE);
        $textEmailMsgDisabledAccAdminInfoReact = $textEmailMsgDisabledAccAdminInfoReact ? $textEmailMsgDisabledAccAdminInfoReact : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'emailMsgDisabledAccAdminInfoReact', 'sLoginProtection');
        return $textEmailMsgDisabledAccAdminInfoReact ? $textEmailMsgDisabledAccAdminInfoReact : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'label_sendNewPassword':
			  $labelSendNewPassword = $this->getLLContent($languageLabel, FALSE);
        $labelSendNewPassword = $labelSendNewPassword ? $labelSendNewPassword : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'sendnewpasswordlabel', 'sPasswordRecovery');
        return $labelSendNewPassword ? $labelSendNewPassword : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'label_sendCurrentPassword':
			  $labelSendCurrentPassword = $this->getLLContent($languageLabel, FALSE);
        $labelSendCurrentPassword = $labelSendCurrentPassword ? $labelSendCurrentPassword : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'sendcurrentpasswordlabel', 'sPasswordRecovery');
        return $labelSendCurrentPassword ? $labelSendCurrentPassword : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'label_email':
			  $labelEmail = $this->getLLContent($languageLabel, TRUE);
        $labelEmail = $labelEmail ? $labelEmail : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'emaillabel', 'sTranslation');
        return $labelEmail ? $labelEmail : $this->pi_getLL($languageLabel,'',1);
			break;
			case 'label_permalogin':
			  $labelPermalogin = $this->getLLContent($languageLabel, TRUE);
        $labelPermalogin = $labelPermalogin ? $labelPermalogin : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'permaloginbutton', 'sTranslation');
        return $labelPermalogin ? $labelPermalogin : $this->pi_getLL($languageLabel,'',1);
			break;

			default:
        return 'Wrong $languageLabel';
		}
	}

	/**
  * The __set method deals the correct handling of class properties and their values.
  * That means you can assign a value to a class property only if the class property
  * is defined at the beginning of the class.
  *
  * @param  string    $name          The property (or member) of the class.
  * @param  array     $value         The value which shall be passed the the property (or member) of the class.
  * @return mixed
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
  function __set ($name,$value) {
    #try {
      // Check for setter method (throws an exception if none exists)
      if (!property_exists(__class__, $name)) { #throw new Exception('__set: ' . $name . ' is not a property of "' . __class__ . '"');
      $this->{$name} = $value;
      }
    #}
    #catch (Exception $e) {
    #  echo $e->getMessage(), "<br />";
    #}
  }

  /**
  * The __get method deals the correct handling of class properties and their values.
  * That means you can fetch the value of a class property only if the class property
  * is defined at the beginning of the class.
  *
  * @param  string    $name          The property (or member) of the class.
  * @return mixed
  * @author Andre Obereigner <feuserloginsystem@obereigner.de>
  */
  function __get($name) {
    #try {
      // Check for setter method (throws an exception if none exists)
      if (!property_exists(__class__, $name)) { #throw new Exception('__get: ' . $name . ' is not a property of "' . __class__ . '"');
      return $this->{$name};
      }
    #}
    #catch (Exception $e) {
    #  echo $e->getMessage(), "<br />";
    #}
  }

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feuserloginsystem/pi1/class.tx_feuserloginsystem_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feuserloginsystem/pi1/class.tx_feuserloginsystem_pi1.php']);
}

?>
