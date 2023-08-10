ALTER TABLE `pre_channel`
ADD COLUMN `mode` int(1) DEFAULT 0;

ALTER TABLE `pre_channel`
ADD COLUMN `daytop` int(10) DEFAULT 0,
ADD COLUMN `daystatus` int(1) DEFAULT 0;

ALTER TABLE `pre_user`
ADD COLUMN `channelinfo` text DEFAULT NULL;

ALTER TABLE `pre_group`
ADD COLUMN `settle_open` int(1) DEFAULT 0,
ADD COLUMN `settle_type` int(1) DEFAULT 0,
ADD COLUMN `settings` text DEFAULT NULL;

ALTER TABLE `pre_channel`
ADD COLUMN `paymin` varchar(10) DEFAULT NULL,
ADD COLUMN `paymax` varchar(10) DEFAULT NULL;

ALTER TABLE `pre_order`
ADD COLUMN `notifytime` datetime DEFAULT NULL;

ALTER TABLE `pre_order`
ADD COLUMN `param` varchar(255) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `pre_alipayrisk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel` int(10) unsigned NOT NULL,
  `pid` varchar(40) NOT NULL,
  `smid` varchar(40) DEFAULT NULL,
  `tradeNos` varchar(40) DEFAULT NULL,
  `risktype` varchar(40) DEFAULT NULL,
  `risklevel` varchar(60) DEFAULT NULL,
  `riskDesc` varchar(500) DEFAULT NULL,
  `complainTime` varchar(128) DEFAULT NULL,
  `complainText` varchar(500) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `process_code` varchar(2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `pre_user`
ADD COLUMN `certtype` tinyint(4) NOT NULL DEFAULT '0',
ADD COLUMN `certtoken` varchar(64) DEFAULT NULL;

ALTER TABLE `pre_order`
ADD COLUMN `domain2` varchar(64) DEFAULT NULL;

ALTER TABLE `pre_user`
CHANGE COLUMN `wxid` `wx_uid` varchar(32) DEFAULT NULL;

ALTER TABLE `pre_user`
ADD COLUMN `certmethod` tinyint(4) NOT NULL DEFAULT '0',
ADD COLUMN `certcorpno` varchar(30) DEFAULT NULL,
ADD COLUMN `certcorpname` varchar(80) DEFAULT NULL,
ADD COLUMN `ordername` varchar(255) DEFAULT NULL;

ALTER TABLE `pre_regcode`
ADD COLUMN `errcount` int(11) NOT NULL DEFAULT '0';

ALTER TABLE `pre_channel`
ADD COLUMN `appwxmp` int(11) DEFAULT NULL,
ADD COLUMN `appwxa` int(11) DEFAULT NULL,
ADD COLUMN `appswitch` tinyint(4) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `pre_weixin` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `type` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `name` varchar(30) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `appid` varchar(150) DEFAULT NULL,
  `appsecret` varchar(250) DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `pre_type` VALUES (6, 'paypal', 0, 'PayPal', 0);

ALTER TABLE `pre_group`
MODIFY COLUMN `info` varchar(1024) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `pre_domain` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` int(11) NOT NULL DEFAULT '0',
  `domain` varchar(128) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `addtime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `domain` (`domain`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `pre_user`
ADD COLUMN `refund` tinyint(1) NOT NULL DEFAULT '0';

ALTER TABLE `pre_order`
ADD COLUMN `combine` tinyint(1) NOT NULL DEFAULT '0',
ADD COLUMN `profits` int(11) NOT NULL DEFAULT '0';

ALTER TABLE `pre_user`
ADD COLUMN `endtime` datetime DEFAULT NULL;

ALTER TABLE `pre_order`
ADD COLUMN `profits2` int(11) NOT NULL DEFAULT '0';

ALTER TABLE `pre_order`
ADD COLUMN `settle` tinyint(1) NOT NULL DEFAULT '0';

CREATE TABLE IF NOT EXISTS `pre_psreceiver` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `channel` int(11) NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `account` varchar(128) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `rate` varchar(10) DEFAULT NULL,
  `minmoney` varchar(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `addtime` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `channel` (`channel`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pre_psorder` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `rid` int(11) NOT NULL,
  `trade_no` char(19) NOT NULL,
  `api_trade_no` varchar(150) NOT NULL,
  `settle_no` varchar(150) DEFAULT NULL,
  `money` decimal(10,2) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `result` text DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `trade_no` (`trade_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pre_psreceiver2` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `channel` int(11) NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `bank_type` tinyint(4) NOT NULL,
  `card_id` varchar(128) NOT NULL,
  `card_name` varchar(128) NOT NULL,
  `tel_no` varchar(20) NOT NULL,
  `cert_id` varchar(30) DEFAULT NULL,
  `bank_code` varchar(20) DEFAULT NULL,
  `prov_code` varchar(20) DEFAULT NULL,
  `area_code` varchar(20) DEFAULT NULL,
  `settleid` varchar(50) DEFAULT NULL,
  `rate` varchar(10) DEFAULT NULL,
  `minmoney` varchar(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `addtime` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `channel` (`channel`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pre_subchannel` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `channel` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `info` text DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  `usetime` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `channel` (`channel`),
 KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `pre_order`
ADD COLUMN `subchannel` int(11) NOT NULL DEFAULT '0';

ALTER TABLE `pre_order`
ADD COLUMN `payurl` varchar(500) DEFAULT NULL,
ADD COLUMN `ext` text DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `pre_blacklist` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `content` varchar(50) NOT NULL,
  `addtime` datetime NOT NULL,
  `endtime` datetime DEFAULT NULL,
  `remark` varchar(80) DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `content`(`content`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `pre_group`
ADD COLUMN `settle_rate` varchar(10) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `pre_wework` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(30) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `appid` varchar(150) DEFAULT NULL,
  `appsecret` varchar(250) DEFAULT NULL,
  `access_token` varchar(300) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  `updatetime` datetime DEFAULT NULL,
  `expiretime` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pre_wxkfaccount` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `wid` int(11) unsigned NOT NULL,
  `openkfid` varchar(60) NOT NULL,
  `url` varchar(100) DEFAULT NULL,
  `cursor` varchar(30) DEFAULT NULL,
  `name` varchar(300) DEFAULT NULL,
  `addtime` datetime NOT NULL,
  `usetime` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `wid`(`wid`),
 UNIQUE KEY `openkfid`(`openkfid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pre_wxkflog` (
  `trade_no` char(19) NOT NULL,
  `aid` int(11) unsigned NOT NULL,
  `sid` char(32) NOT NULL,
  `payurl` varchar(500) NOT NULL,
  `addtime` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
 PRIMARY KEY (`trade_no`),
 KEY `sid`(`sid`),
 KEY `addtime`(`addtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `pre_weixin`
ADD COLUMN `access_token` varchar(300) DEFAULT NULL,
ADD COLUMN `addtime` datetime DEFAULT NULL,
ADD COLUMN `updatetime` datetime DEFAULT NULL,
ADD COLUMN `expiretime` datetime DEFAULT NULL;

ALTER TABLE `pre_wxkfaccount`
ADD COLUMN `name` varchar(300) DEFAULT NULL;