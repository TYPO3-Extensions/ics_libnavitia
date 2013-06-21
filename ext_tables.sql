#
# Table structure for table 'tx_icslibnavitia_cachedrequests'
#
CREATE TABLE tx_icslibnavitia_cachedrequests (
    hash VARCHAR(32) NOT NULL,
    updated int(11) DEFAULT '0' NOT NULL,
    usedLast int(11) DEFAULT '0' NOT NULL,
	url VARCHAR(255) NOT NULL,
	login VARCHAR(32) NOT NULL,
	`function` VARCHAR(10) NOT NULL,
	action VARCHAR(40) NOT NULL,
	`parameters` TEXT NOT NULL,
    
    PRIMARY KEY (hash)
) ENGINE=InnoDB;
