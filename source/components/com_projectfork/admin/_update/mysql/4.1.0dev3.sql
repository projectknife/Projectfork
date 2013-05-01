CREATE TABLE IF NOT EXISTS `#__pf_repo_note_revs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Note ID',
  `project_id` int(10) unsigned NOT NULL COMMENT 'Parent project ID',
  `parent_id` int(10) NOT NULL COMMENT 'Parent Note ID',
  `title` varchar(56) NOT NULL COMMENT 'Note title',
  `alias` varchar(56) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Title alias. Used in SEF URL''s',
  `description` text NOT NULL COMMENT 'Note content text',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Note creation date',
  `created_by` int(10) unsigned NOT NULL COMMENT 'Note author',
  `attribs` text NOT NULL COMMENT 'Note attributes in JSON format',
  `ordering` int(10) NOT NULL COMMENT 'Note revision number',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_alias` (`alias`,`parent_id`),
  KEY `idx_projectid` (`project_id`),
  KEY `idx_createdby` (`created_by`)
) DEFAULT CHARSET=utf8 COMMENT='Stores Projectfork note revisions';