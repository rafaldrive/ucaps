-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 27, 2012 at 11:48 AM
-- Server version: 5.0.41-community-nt
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ucaps_codebase`
--

-- --------------------------------------------------------

--
-- Table structure for table `rel_tags`
--

CREATE TABLE IF NOT EXISTS `rel_tags` (
  `idtag` int(11) NOT NULL,
  `idtarget` int(11) NOT NULL,
  `stargettype` enum('document') collate utf8_polish_ci NOT NULL,
  KEY `idtag` (`idtag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stat_published_documents`
--

CREATE TABLE IF NOT EXISTS `stat_published_documents` (
  `iddocument` int(11) NOT NULL,
  `idwebsite` int(11) NOT NULL,
  `surl` varchar(255) collate utf8_polish_ci NOT NULL,
  `stitle` text collate utf8_polish_ci NOT NULL,
  `sintro` text COLLATE utf8_polish_ci,
  `shtml` text COLLATE utf8_polish_ci NOT NULL,
  `stemplate` varchar(64) COLLATE utf8_polish_ci DEFAULT NULL,
  `smetakeywords` varchar(256) COLLATE utf8_polish_ci DEFAULT NULL,
  `smetadescription` text COLLATE utf8_polish_ci,
  `tcreated` datetime DEFAULT NULL,
  `tpublished` datetime DEFAULT NULL,
  KEY `surl` (`surl`,`idwebsite`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tab_documents`
--

CREATE TABLE IF NOT EXISTS `tab_documents` (
  `iddocument` int(11) NOT NULL auto_increment,
  `idwebsite` int(11) NOT NULL default '1',
  `sstatus` enum('draft','published') collate utf8_polish_ci NOT NULL default 'draft',
  `tlastupdate` datetime NOT NULL,
  `iduserlastupdate` int(11) NOT NULL,
  `surl` text collate utf8_polish_ci,
  `stitle` text collate utf8_polish_ci NOT NULL,
  `sintro` text collate utf8_polish_ci,
  `shtml` text collate utf8_polish_ci NOT NULL,
  `stemplate` varchar(64) collate utf8_polish_ci default NULL,
  `smetakeywords` varchar(256) collate utf8_polish_ci default NULL,
  `smetadescription` text collate utf8_polish_ci,
  `tpublishstart` datetime default NULL,
  `tpublishend` datetime default NULL,
  `tlastpublished` datetime default NULL,
  `iduserlastpublished` int(11) default NULL,
  `tcreated` datetime NOT NULL,
  `idusercreated` int(11) NOT NULL,
  PRIMARY KEY  (`iddocument`),
  KEY `idwebsite` (`idwebsite`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tab_menus`
--

CREATE TABLE IF NOT EXISTS `tab_menus` (
  `idmenu` int(11) NOT NULL auto_increment,
  `idwebsite` int(11) NOT NULL,
  `sname` text collate utf8_polish_ci NOT NULL,
  PRIMARY KEY  (`idmenu`),
  KEY `idwebsite` (`idwebsite`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tab_menu_items`
--

CREATE TABLE IF NOT EXISTS `tab_menu_items` (
  `iditem` int(11) NOT NULL auto_increment,
  `idmenu` int(11) NOT NULL,
  `idup` int(11) NOT NULL default '0',
  `norder` int(11) NOT NULL default '0',
  `sname` text collate utf8_polish_ci NOT NULL,
  `sdescription` text collate utf8_polish_ci,
  `surl` text collate utf8_polish_ci,
  `iddocument` int(11) default NULL,
  `sicon` text collate utf8_polish_ci,
  PRIMARY KEY  (`iditem`),
  KEY `idmenu` (`idmenu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tab_sessions`
--

CREATE TABLE IF NOT EXISTS `tab_sessions` (
  `idsession` bigint(20) NOT NULL auto_increment,
  `iduser` int(11) default NULL,
  `tlastaction` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `sip` varchar(15) collate utf8_polish_ci NOT NULL,
  `shost` text collate utf8_polish_ci,
  `sbrowser` text collate utf8_polish_ci,
  `ssessioncode` varchar(32) collate utf8_polish_ci NOT NULL,
  PRIMARY KEY  (`idsession`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tab_tags`
--

CREATE TABLE IF NOT EXISTS `tab_tags` (
  `idtag` int(11) NOT NULL auto_increment,
  `sname` varchar(127) collate utf8_polish_ci NOT NULL,
  PRIMARY KEY  (`idtag`),
  UNIQUE KEY `sname` (`sname`),
  UNIQUE KEY `sname_2` (`sname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tab_users`
--

CREATE TABLE IF NOT EXISTS `tab_users` (
  `iduser` int(11) NOT NULL auto_increment,
  `bactive` tinyint(4) NOT NULL default '0',
  `semail` varchar(100) collate utf8_polish_ci NOT NULL,
  `slogin` varchar(32) collate utf8_polish_ci NOT NULL,
  `spassword` varchar(32) collate utf8_polish_ci NOT NULL,
  `tlastlogin` timestamp NULL default NULL,
  `tcreated` datetime NOT NULL,
  `tlastpaid` datetime default NULL,
  `tpaiduntil` date default NULL,
  `scode` varchar(32) collate utf8_polish_ci default NULL,
  PRIMARY KEY  (`iduser`),
  UNIQUE KEY `semail` (`semail`),
  UNIQUE KEY `slogin` (`slogin`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Table structure for table `tab_vhosts`
--

CREATE TABLE IF NOT EXISTS `tab_vhosts` (
  `idvhost` int(11) NOT NULL auto_increment,
  `idwebsite` int(11) NOT NULL,
  `sname` text collate utf8_polish_ci NOT NULL,
  PRIMARY KEY  (`idvhost`),
  KEY `idwebsite` (`idwebsite`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tab_websites`
--

CREATE TABLE IF NOT EXISTS `tab_websites` (
  `idwebsite` int(11) NOT NULL auto_increment,
  `sname` varchar(32) collate utf8_polish_ci NOT NULL,
  `tcreated` datetime NOT NULL,
  PRIMARY KEY  (`idwebsite`),
  UNIQUE KEY `sname` (`sname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `rel_tags`
--
ALTER TABLE `rel_tags`
  ADD CONSTRAINT `rel_tags_ibfk_1` FOREIGN KEY (`idtag`) REFERENCES `tab_tags` (`idtag`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stat_published_documents`
--
ALTER TABLE `stat_published_documents`
  ADD CONSTRAINT `stat_published_documents_ibfk_1` FOREIGN KEY (`iddocument`) REFERENCES `tab_documents` (`iddocument`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tab_documents`
--
ALTER TABLE `tab_documents`
  ADD CONSTRAINT `tab_documents_ibfk_1` FOREIGN KEY (`idwebsite`) REFERENCES `tab_websites` (`idwebsite`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tab_menus`
--
ALTER TABLE `tab_menus`
  ADD CONSTRAINT `tab_menus_ibfk_1` FOREIGN KEY (`idwebsite`) REFERENCES `tab_websites` (`idwebsite`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tab_menu_items`
--
ALTER TABLE `tab_menu_items`
  ADD CONSTRAINT `tab_menu_items_ibfk_1` FOREIGN KEY (`idmenu`) REFERENCES `tab_menus` (`idmenu`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tab_vhosts`
--
ALTER TABLE `tab_vhosts`
  ADD CONSTRAINT `tab_vhosts_ibfk_1` FOREIGN KEY (`idwebsite`) REFERENCES `tab_websites` (`idwebsite`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
