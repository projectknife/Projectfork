CREATE TABLE IF NOT EXISTS `#__pf_repo_file_revs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'File ID',
  `project_id` int(10) unsigned NOT NULL COMMENT 'Parent project ID',
  `parent_id` int(10) unsigned NOT NULL COMMENT 'File head revision id',
  `title` varchar(56) NOT NULL COMMENT 'File title',
  `alias` varchar(56) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Title alias. Used in SEF URL''s',
  `description` varchar(128) NOT NULL COMMENT 'File description',
  `file_name` varchar(255) NOT NULL COMMENT 'The file name',
  `file_extension` varchar(32) NOT NULL COMMENT 'The file extension name',
  `file_size` int(10) unsigned NOT NULL COMMENT 'The file size in kilobyte',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'File creation date',
  `created_by` int(10) unsigned NOT NULL COMMENT 'File author',
  `attribs` text NOT NULL COMMENT 'File attributes in JSON format',
  `ordering` int(10) NOT NULL COMMENT 'File revision number',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_alias` (`alias`,`parent_id`),
  KEY `idx_projectid` (`project_id`),
  KEY `idx_createdby` (`created_by`)
) DEFAULT CHARSET=utf8 COMMENT='Stores Projectfork file version details';