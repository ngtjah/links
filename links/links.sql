
--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
CREATE TABLE `links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site` text,
  `announcer` varchar(25) DEFAULT NULL,
  `edate` datetime DEFAULT NULL,
  `type` varchar(15) DEFAULT NULL,
  `filename` text,
  `title` text,
  `category` varchar(200) DEFAULT NULL,
  `appid` bigint(20) DEFAULT NULL,
  `twidth` int(10) unsigned DEFAULT NULL,
  `theight` int(10) unsigned DEFAULT NULL,
  `width` int(10) unsigned DEFAULT NULL,
  `height` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


--
-- Table structure for table `pocketusers`
--

DROP TABLE IF EXISTS `pocketusers`;
CREATE TABLE `pocketusers` (
  `username` varchar(100) NOT NULL,
  `consumer_key` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

