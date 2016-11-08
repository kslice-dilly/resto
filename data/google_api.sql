-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 07, 2016 at 03:39 PM
-- Server version: 5.5.53-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `google_api`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(50) NOT NULL,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `api_keys`
--

INSERT INTO `api_keys` (`id`, `key`, `name`) VALUES
(1, '$geocode', 'geocode'),
(3, '$embed', 'maps_embed'),
(4, '$js', 'maps_javascript');

-- --------------------------------------------------------

--
-- Table structure for table `quotas`
--

CREATE TABLE IF NOT EXISTS `quotas` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `api_id` int(11) unsigned NOT NULL,
  `quota` int(11) NOT NULL,
  `type` varchar(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `api_id` (`api_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `quotas`
--

INSERT INTO `quotas` (`id`, `api_id`, `quota`, `type`) VALUES
(1, 1, 2500, 'day');

-- --------------------------------------------------------

--
-- Table structure for table `quota_counters`
--

CREATE TABLE IF NOT EXISTS `quota_counters` (
  `quota_id` int(11) unsigned NOT NULL,
  `count` smallint(5) unsigned NOT NULL,
  `latest` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `startdate` date DEFAULT NULL,
  KEY `quota_id` (`quota_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `quota_counters`
--

INSERT INTO `quota_counters` (`quota_id`, `count`, `latest`, `startdate`) VALUES
(1, 0, '0000-00-00 00:00:00', NULL);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `quotas`
--
ALTER TABLE `quotas`
  ADD CONSTRAINT `quotas_ibfk_1` FOREIGN KEY (`api_id`) REFERENCES `api_keys` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `quota_counters`
--
ALTER TABLE `quota_counters`
  ADD CONSTRAINT `quota_counters_ibfk_1` FOREIGN KEY (`quota_id`) REFERENCES `quotas` (`id`) ON UPDATE CASCADE;

