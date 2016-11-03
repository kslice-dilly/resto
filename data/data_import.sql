SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+05:00"

DELIMITER $$

CREATE DATABASE IF NOT EXISTS resto;

--
-- Database: `resto`
--

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`yoshi`@`localhost` FUNCTION `GuidToBinary`(
    $Data VARCHAR(36)
) RETURNS binary(16)
    NO SQL
    DETERMINISTIC
BEGIN
    DECLARE $Result BINARY(16) DEFAULT NULL;
    IF $Data IS NOT NULL THEN
        SET $Data = REPLACE($Data,'-','');
        SET $Result =
            CONCAT( UNHEX(SUBSTRING($Data,7,2)), UNHEX(SUBSTRING($Data,5,2)),
                    UNHEX(SUBSTRING($Data,3,2)), UNHEX(SUBSTRING($Data,1,2)),
                    UNHEX(SUBSTRING($Data,11,2)),UNHEX(SUBSTRING($Data,9,2)),
                    UNHEX(SUBSTRING($Data,15,2)),UNHEX(SUBSTRING($Data,13,2)),
                    UNHEX(SUBSTRING($Data,17,16)));
    END IF;
    RETURN $Result;
END$$

CREATE DEFINER=`yoshi`@`localhost` FUNCTION `ToGuid`(
    $Data BINARY(16)
) RETURNS char(36) CHARSET utf8
    NO SQL
    DETERMINISTIC
BEGIN
    DECLARE $Result CHAR(36) DEFAULT NULL;
    IF $Data IS NOT NULL THEN
        SET $Result =
            CONCAT(
                HEX(SUBSTRING($Data,4,1)), HEX(SUBSTRING($Data,3,1)),
                HEX(SUBSTRING($Data,2,1)), HEX(SUBSTRING($Data,1,1)), '-', 
                HEX(SUBSTRING($Data,6,1)), HEX(SUBSTRING($Data,5,1)), '-',
                HEX(SUBSTRING($Data,8,1)), HEX(SUBSTRING($Data,7,1)), '-',
                HEX(SUBSTRING($Data,9,2)), '-', HEX(SUBSTRING($Data,11,6)));
    END IF;
    RETURN $Result;
END$$

DELIMITER ;

CREATE TABLE IF NOT EXISTS `business` (
	`id`		TEXT NOT NULL,
	`name`		VARCHAR(40) CHARACTER SET utf8 NOT NULL,
	`address`	VARCHAR(50) NOT NULL,
	`city`		VARCHAR(30) NOT NULL,
	`state`		VARCHAR(8) NOT NULL,
	`postal_code`	VARCHAR(7) NOT NULL,
	`lat`		DECIMAL(11,7) NOT NULL
	`long`		DECIMAL(11,7) NOT NULL
	`tel`		VARCHAR(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8

CREATE TABLE IF NOT EXISTS `geocode` (
	`bus_guid` 	BINARY(16) NOT NULL,
	`lat`		DECIMAL(11,7) NOT NULL,
	`long`		DECIMAL(11,7) NOT NULL,
	`addr`		VARCHAR(50) NOT NULL,
	`place_id`	VARCHAR(28) NOT NULL,
	`hit`		TINYINT(1) NOT NULL,
	PRIMARY KEY (`bus_guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `geocode`
	ADD CONSTRAINT `geocode_ibfk_1` FOREIGN KEY (`bus_guid`) REFERENCES `business` (`guid`) ON UPDATE CASCADE;
 
CREATE TABLE IF NOT EXISTS `inspections` (
	`bus_guid`	BINARY(16) NOT NULL,
	`business_id`	TEXT NOT NULL,
	`score`		TINYINT(3) unsigned NOT NULL,
	`datetext`	VARCHAR(8) NOT NULL,
	KEY `bus_guid` (`bus_guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `inspections`
	ADD CONSTRAINT `inspections_ibfk_1` FOREIGN KEY (`bus_guid`) REFERENCES `business` (`guid`) ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS `violations` (
	`bus_guid`	BINARY(16) NOT NULL,
	`business_id`	VARCHAR(36) NOT NULL,
	`datetext`	VARCHAR(8) NOT NULL,
	`code`		VARCHAR(5) NOT NULL,
	`description`	VARCHAR(80) NOT NULL,
	KEY `bus_guid` (`bus_guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `violations`
	ADD CONSTRAINT `violations_ibfk_1` FOREIGN KEY (`bus_guid`) REFERENCES `business` (`guid`) ON UPDATE CASCADE;
