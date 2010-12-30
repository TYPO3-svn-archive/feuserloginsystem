<?php

########################################################################
# Extension Manager/Repository config file for ext "feuserloginsystem".
#
# Auto generated 26-11-2010 15:14
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'FeUserLoginsystem',
	'description' => 'READ UPGRADE.txt BEFORE UPGRADING to 0.1.2 - So far it got Login and Logout functionality with multiple sysFolders, redirection, content personalization, permalogin and PasswordRecovery with Captcha images, User Disable and User Statistics.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '0.1.3',
	'dependencies' => 'taskcenter',
	'conflicts' => 'loginusertrack',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1',
	'state' => 'alpha',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Andre Obereigner',
	'author_email' => 'feuserloginsystem@obereigner.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'taskcenter' => '',
		),
		'conflicts' => array(
			'loginusertrack' => '',
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:36:{s:9:"ChangeLog";s:4:"077d";s:10:"README.txt";s:4:"ee2d";s:9:"Thumbs.db";s:4:"4ced";s:11:"UPGRADE.txt";s:4:"64a6";s:20:"class.ext_update.php";s:4:"b779";s:21:"class.ux_tslib_fe.php";s:4:"2bfe";s:12:"ext_icon.gif";s:4:"685b";s:17:"ext_localconf.php";s:4:"bfd8";s:14:"ext_tables.php";s:4:"ee95";s:14:"ext_tables.sql";s:4:"cfde";s:19:"flexform_ds_pi1.xml";s:4:"8a1d";s:13:"locallang.xml";s:4:"3d80";s:16:"locallang_db.xml";s:4:"7852";s:17:"locallang_tca.xml";s:4:"f599";s:8:"todo.txt";s:4:"cb76";s:14:"doc/manual.sxw";s:4:"7f25";s:19:"doc/wizard_form.dat";s:4:"af1d";s:20:"doc/wizard_form.html";s:4:"9781";s:14:"mod1/Thumbs.db";s:4:"ded3";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"847c";s:14:"mod1/index.php";s:4:"2603";s:18:"mod1/locallang.xml";s:4:"e740";s:22:"mod1/locallang_mod.xml";s:4:"639b";s:19:"mod1/moduleicon.png";s:4:"e7c6";s:48:"modfunc1/class.tx_feuserloginsystem_modfunc1.php";s:4:"c121";s:22:"modfunc1/locallang.xml";s:4:"c4b7";s:14:"pi1/ce_wiz.gif";s:4:"0139";s:38:"pi1/class.tx_feuserloginsystem_pi1.php";s:4:"49cd";s:46:"pi1/class.tx_feuserloginsystem_pi1_wizicon.php";s:4:"413b";s:13:"pi1/clear.gif";s:4:"cc11";s:35:"pi1/feuserloginsystem_template.html";s:4:"9ada";s:17:"pi1/locallang.xml";s:4:"1090";s:24:"pi1/static/constants.txt";s:4:"849d";s:24:"pi1/static/editorcfg.txt";s:4:"f6ce";s:20:"pi1/static/setup.txt";s:4:"6620";}',
	'suggests' => array(
	),
);

?>