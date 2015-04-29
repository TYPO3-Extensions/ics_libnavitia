#
# Table structure for table 'tx_icslibnavitia_cachedrequests'
#
CREATE TABLE tx_icslibnavitia_cachedrequests (
    hash varchar(64) DEFAULT '' NOT NULL,
    updated int(11) DEFAULT '0' NOT NULL,
    usedLast int(11) DEFAULT '0' NOT NULL,
	url varchar(255) DEFAULT '' NOT NULL,
	login varchar(32) DEFAULT '' NOT NULL,
	`function` varchar(10) DEFAULT '' NOT NULL,
	action varchar(40) DEFAULT '' NOT NULL,
	`parameters` text NOT NULL,
    
    PRIMARY KEY (hash)
) ENGINE=InnoDB;
