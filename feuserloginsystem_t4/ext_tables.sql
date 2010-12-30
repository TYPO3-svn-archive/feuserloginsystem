#
# Table structure for table 'fe_groups'
#
CREATE TABLE fe_groups (
    tx_feuserloginsystem_redirectionafterlogin blob NOT NULL,
    tx_feuserloginsystem_redirectionafterlogout blob NOT NULL
);



#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
    tx_feuserloginsystem_redirectionafterlogin blob NOT NULL,
    tx_feuserloginsystem_redirectionafterlogout blob NOT NULL
);

#
# Table structure for table 'tx_feuserloginsystem_userstat'
#

CREATE TABLE tx_feuserloginsystem_userstatistics (
    uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
    feuserid int(11) unsigned DEFAULT '0' NOT NULL,
    sessionstart int(11) unsigned DEFAULT '0' NOT NULL,
    lastpageview int(11) unsigned DEFAULT '0' NOT NULL,
    pagecounter int(11) unsigned DEFAULT '0' NOT NULL,
    pagetracking blob NOT NULL,

    PRIMARY KEY (uid),
    KEY feuserid (feuserid)
);

#
# Table structure for table 'tx_feuserloginsystem_loginlog'
#

CREATE TABLE tx_feuserloginsystem_loginlog (
    uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
    feuserid int(11) unsigned DEFAULT '0' NOT NULL,
    success int(11) unsigned DEFAULT '0' NOT NULL,
    timeofdisable int(11) unsigned DEFAULT '0' NOT NULL,
    firstloginattempt int(11) unsigned DEFAULT '0' NOT NULL,
    lastloginattempt int(11) unsigned DEFAULT '0' NOT NULL,
    counter int(11) unsigned DEFAULT '0' NOT NULL,
    ip tinytext NOT NULL,
    hash tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY feuserid (feuserid)
);
