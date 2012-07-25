-- phpMyAdmin SQL Dump
-- version 3.4.1
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Erstellungszeit: 09. Jun 2012 um 12:03
-- Server Version: 5.1.41
-- PHP-Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Datenbank: `slc24_v2`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `account`
--

CREATE TABLE IF NOT EXISTS `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(10) NOT NULL,
  `currency` enum('SLC','BTC','NMC','EUR','USD','GBP','CHF','CAD','AUD') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(28,12) NOT NULL,
  `active` enum('yes','no') NOT NULL DEFAULT 'yes',
  `prior` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED AUTO_INCREMENT=1553 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `crypto_transaction`
--

CREATE TABLE IF NOT EXISTS `crypto_transaction` (
  `id` int(13) NOT NULL AUTO_INCREMENT,
  `txid` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=36166 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `deposit_address`
--

CREATE TABLE IF NOT EXISTS `deposit_address` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `currency` enum('SLC','BTC','NMC') NOT NULL,
  `address` varchar(255) NOT NULL,
  `user` int(10) NOT NULL,
  `booked` decimal(28,12) NOT NULL,
  `creation_date` datetime NOT NULL,
  `used` enum('yes','no') NOT NULL DEFAULT 'no',
  `last_checked` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1498 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `safe_addresses`
--

CREATE TABLE IF NOT EXISTS `safe_addresses` (
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `currency` enum('SLC','BTC') COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(15,8) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `trade`
--

CREATE TABLE IF NOT EXISTS `trade` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `type` enum('buy','sell') NOT NULL,
  `buy_trade_order` int(12) NOT NULL,
  `sell_trade_order` int(12) NOT NULL,
  `currency` enum('BTC','NMC','EUR','USD','GBP','CHF','CAD','AUD') NOT NULL,
  `slc_transaction` int(13) NOT NULL,
  `cur_transaction` int(13) NOT NULL,
  `refund` int(13) NOT NULL,
  `trade_time` datetime NOT NULL,
  `price` decimal(28,12) NOT NULL,
  `amount` decimal(28,12) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1773 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `trade_order`
--

CREATE TABLE IF NOT EXISTS `trade_order` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `type` enum('buy','sell') NOT NULL,
  `user` int(10) NOT NULL,
  `currency` enum('BTC','NMC','EUR','USD','GBP','CHF','CAD','AUD') NOT NULL,
  `price` decimal(28,12) NOT NULL,
  `amount` decimal(28,12) NOT NULL,
  `completed` decimal(28,12) NOT NULL,
  `active` enum('yes','no') NOT NULL,
  `finished` enum('yes','no') NOT NULL,
  `filing_time` datetime NOT NULL,
  `change_time` datetime NOT NULL,
  `finishing_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5069 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `transaction`
--

CREATE TABLE IF NOT EXISTS `transaction` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `direction` enum('in','out') NOT NULL,
  `type` enum('intern','extern') NOT NULL,
  `user` int(10) NOT NULL,
  `amount` decimal(28,12) NOT NULL,
  `currency` enum('SLC','BTC','NMC','EUR','USD','GBP','CHF','CAD','AUD') NOT NULL,
  `balance` decimal(28,12) NOT NULL,
  `fee` decimal(28,12) NOT NULL,
  `filing_time` datetime NOT NULL,
  `info` varchar(255) NOT NULL,
  `info_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=49434 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `transfer_deposit`
--

CREATE TABLE IF NOT EXISTS `transfer_deposit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deposit_address` int(10) NOT NULL,
  `txid` int(13) NOT NULL,
  `amount` decimal(20,4) NOT NULL,
  `filing_time` datetime NOT NULL,
  `type` enum('intern','extern') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13697 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `transfer_deposit_address`
--

CREATE TABLE IF NOT EXISTS `transfer_deposit_address` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user` int(10) NOT NULL,
  `group` varchar(255) NOT NULL,
  `address` varchar(40) NOT NULL,
  `callback` varchar(1024) NOT NULL,
  `send_mail` enum('yes','no') NOT NULL,
  `creation_time` datetime NOT NULL,
  `data` varchar(1024) NOT NULL,
  `last_check_amount` decimal(20,4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=643 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `transfer_withdrawal`
--

CREATE TABLE IF NOT EXISTS `transfer_withdrawal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `withdrawal_address` int(10) NOT NULL,
  `txid` int(13) NOT NULL,
  `amount` decimal(20,4) NOT NULL,
  `filing_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=32494 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `transfer_withdrawal_address`
--

CREATE TABLE IF NOT EXISTS `transfer_withdrawal_address` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user` int(10) NOT NULL,
  `group` varchar(255) NOT NULL,
  `address` varchar(40) NOT NULL,
  `creation_time` datetime NOT NULL,
  `data` varchar(1024) NOT NULL,
  `type` enum('intern','extern') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=12693 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `actu` varchar(255) NOT NULL,
  `hashed_password` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `hash_salt` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `hash_mode` enum('a','b') NOT NULL,
  `register_time` datetime NOT NULL,
  `id_string` varchar(7) NOT NULL,
  `last_action` datetime NOT NULL,
  `allow_api_withdrawals` enum('yes','no') NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=886 ;
