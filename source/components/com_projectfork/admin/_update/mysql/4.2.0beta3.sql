CREATE TABLE IF NOT EXISTS `#__pf_emailqueue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL COMMENT 'Recipient email address',
  `subject` text NOT NULL COMMENT 'Email subject',
  `message` text NOT NULL COMMENT 'Email message',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COMMENT='Queues email notifications';
