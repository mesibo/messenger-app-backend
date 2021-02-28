--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `phone` bigint(20) unsigned NOT NULL DEFAULT '0',
  `subscribed` tinyint(4) DEFAULT '0',
  UNIQUE KEY `uphone` (`uid`,`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `gid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` blob,
  `status` char(2) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `photo` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `flag` int(11) unsigned DEFAULT '0',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ts` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`gid`),
  KEY `ts` (`ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `gid` int(10) unsigned NOT NULL DEFAULT '0',
  `phone` bigint(20) unsigned NOT NULL DEFAULT '0',
  `role` tinyint(4) DEFAULT '0',
  UNIQUE KEY `gid_2` (`gid`,`phone`),
  KEY `gid` (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `names`
--

CREATE TABLE `names` (
  `name` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `status` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `notifytokens`
--

CREATE TABLE `notifytokens` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `device` tinyint(3) unsigned DEFAULT '0',
  `token` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `production` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `tokens`
--

CREATE TABLE `tokens` (
  `uid` int(10) unsigned DEFAULT NULL,
  `token` char(64) NOT NULL DEFAULT '0',
  `updatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expirytime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ipaddr` int(11) unsigned NOT NULL DEFAULT '0',
  `device` int(10) unsigned DEFAULT '0',
  `createdby` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`token`),
  UNIQUE KEY `uid_index` (`uid`),
  KEY `ip_index` (`ipaddr`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` blob,
  `phone` bigint(20) unsigned NOT NULL DEFAULT '0',
  `cc` int(10) unsigned DEFAULT '0',
  `status` blob,
  `photo` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `credits` float(9,4) NOT NULL DEFAULT '0.0000',
  `flag` int(11) unsigned DEFAULT '0',
  `ts` int(10) unsigned DEFAULT '0',
  `n` blob,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `phone` (`phone`),
  KEY `ts` (`ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
