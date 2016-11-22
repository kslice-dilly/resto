CREATE TABLE IF NOT EXISTS `visits` ( 
`user_agent` varchar(512) NOT NULL, 
`referer` varchar(512) NOT NULL,
`hostname` varchar(512) DEFAULT NULL,
`remote_addr` int(11) unsigned NOT NULL, 
`lastvisit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP, 
`firstvisit` datetime NOT NULL, 
`count` int(10) unsigned NOT NULL DEFAULT '1', 
PRIMARY KEY (`remote_addr`), 
KEY `remote_addr` (`remote_addr`));
