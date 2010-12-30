<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


t3lib_div::loadTCA('tt_content');

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist']['feuserloginsystem_pi1']='pi_flexform';
# hier unten
$TCA['tt_content']['types'][$_EXTKEY.'_pi1']['showitem']='CType;;4;button;1-1-1, header;;3;;2-2-2';

t3lib_extMgm::addPlugin(Array('LLL:EXT:feuserloginsystem/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","FeUserLoginsystem");
t3lib_extMgm::addPiFlexFormValue('feuserloginsystem_pi1', 'FILE:EXT:feuserloginsystem/flexform_ds_pi1.xml');
# hier unten für PageTyp
#t3lib_extMgm::addPlugin(array('LLL:EXT:feuserloginsystem/locallang_db.xml:tt_content.CType_pi1', $_EXTKEY.'_pi1'),'CType');

if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_feuserloginsystem_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_feuserloginsystem_pi1_wizicon.php';


if (TYPO3_MODE == 'BE')	{
		
	t3lib_extMgm::addModule('web','txfeuserloginsystemM1','',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
}


if (TYPO3_MODE=="BE")	{
	t3lib_extMgm::insertModuleFunction(
		"user_task",		
		"tx_feuserloginsystem_modfunc1",
		t3lib_extMgm::extPath($_EXTKEY)."modfunc1/class.tx_feuserloginsystem_modfunc1.php",
		"LLL:EXT:feuserloginsystem/locallang_db.xml:moduleFunction.tx_feuserloginsystem_modfunc1"
	);
}

$tempColumns = Array (
    "tx_feuserloginsystem_redirectionafterlogin" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:feuserloginsystem/locallang_db.xml:fe_groups.tx_feuserloginsystem_redirectionafterlogin",        
        "config" => Array (
            "type" => "group",    
            "internal_type" => "db",    
            "allowed" => "pages",    
            "size" => 1,    
            "minitems" => 0,
            "maxitems" => 1,
        )
    ),
    "tx_feuserloginsystem_redirectionafterlogout" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:feuserloginsystem/locallang_db.xml:fe_groups.tx_feuserloginsystem_redirectionafterlogout",        
        "config" => Array (
            "type" => "group",    
            "internal_type" => "db",    
            "allowed" => "pages",    
            "size" => 1,    
            "minitems" => 0,
            "maxitems" => 1,
        )
    ),
);

t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_feuserloginsystem_redirectionafterlogin;;;;1-1-1, tx_feuserloginsystem_redirectionafterlogout");

$tempColumns = Array (
    "tx_feuserloginsystem_redirectionafterlogin" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:feuserloginsystem/locallang_db.xml:fe_users.tx_feuserloginsystem_redirectionafterlogin",        
        "config" => Array (
            "type" => "group",    
            "internal_type" => "db",    
            "allowed" => "pages",    
            "size" => 1,    
            "minitems" => 0,
            "maxitems" => 1,
        )
    ),
    "tx_feuserloginsystem_redirectionafterlogout" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:feuserloginsystem/locallang_db.xml:fe_users.tx_feuserloginsystem_redirectionafterlogout",        
        "config" => Array (
            "type" => "group",    
            "internal_type" => "db",    
            "allowed" => "pages",    
            "size" => 1,    
            "minitems" => 0,
            "maxitems" => 1,
        )
    ),
);

t3lib_div::loadTCA("fe_groups");
t3lib_extMgm::addTCAcolumns("fe_groups",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_groups","tx_feuserloginsystem_redirectionafterlogin;;;;1-1-1, tx_feuserloginsystem_redirectionafterlogout");


?>
