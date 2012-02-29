DROP TABLE IF EXISTS `#__pf_comments`;
CREATE TABLE IF NOT EXISTS `#__pf_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Comment ID',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
  `project_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
  `item_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent item ID',
  `context` varchar(32) NOT NULL COMMENT 'Context reference',
  `title` varchar(128) NOT NULL COMMENT 'Comment title',
  `content` text NOT NULL COMMENT 'Comment content',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Comment creation date',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Comment author',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Comment modify date',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last user to modify the comment',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User who is currently editing the comment',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Check-out date and time',
  `attribs` text NOT NULL COMMENT 'Comment attributes in JSON format',
  `access` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Comment ACL access level ID',
  `state` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Comment state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed ',
  PRIMARY KEY (`id`),
  KEY `idx_projectid` (`project_id`),
  KEY `idx_access` (`access`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_checkedout` (`checked_out`),
  KEY `idx_contextitemid` (`context`,`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Stores Projectfork item comments';

DROP TABLE IF EXISTS `#__pf_files`;
CREATE TABLE IF NOT EXISTS `#__pf_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'File ID',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
  `project_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
  `folder_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent folder ID',
  `name` varchar(255) NOT NULL COMMENT 'File name',
  `prefix` varchar(33) NOT NULL COMMENT 'File name prefix',
  `alias` varchar(255) NOT NULL COMMENT 'Title alias',
  `description` text NOT NULL COMMENT 'File description',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'File upload date',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'File author',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'File modify date',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last user to modify the file',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User who is currently editing the file',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Check-out date and time',
  `attribs` text NOT NULL COMMENT 'File attributes in JSON format',
  `access` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'File ACL access level ID',
  `state` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'File state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed ',
  PRIMARY KEY (`id`),
  KEY `idx_projectid` (`project_id`),
  KEY `idx_access` (`access`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_checkedout` (`checked_out`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Stores Projectfork file info. The actual files are stored in';

DROP TABLE IF EXISTS `#__pf_file_folders`;
CREATE TABLE IF NOT EXISTS `#__pf_file_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Folder ID',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `project_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Primary key of the parent node',
  `lft` int(11) NOT NULL DEFAULT '0' COMMENT 'Left value of the tree node',
  `rgt` int(11) NOT NULL DEFAULT '0' COMMENT 'Right value of the tree node',
  `level` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Tree depth level',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT 'Tree path',
  `title` varchar(64) NOT NULL COMMENT 'Folder title',
  `alias` varchar(64) NOT NULL DEFAULT '' COMMENT 'Folder alias',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT 'Folder description',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User who is currently editing the folder',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Check-out time and date',
  `access` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Folder Access level ID',
  `params` text NOT NULL COMMENT 'Folder params in JSON format',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Folder author',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Folder creation date',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last user to modify the folder',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Modify date and time',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_path` (`path`),
  KEY `idx_left_right` (`lft`,`rgt`),
  KEY `idx_alias` (`alias`),
  KEY `idx_projectid` (`project_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Stores Projectfork folder structure';

DROP TABLE IF EXISTS `#__pf_milestones`;
CREATE TABLE IF NOT EXISTS `#__pf_milestones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Milestone ID',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
  `project_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
  `title` varchar(128) NOT NULL COMMENT 'Milestone title',
  `alias` varchar(128) NOT NULL COMMENT 'Title alias. Used in SEF URL''s',
  `description` varchar(255) NOT NULL COMMENT 'Milestone description',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Milestone creation date',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Milestone author',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Milestone modify date',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last user to modify the milestone',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User who is currently editing the milestone',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Check-out date and time',
  `attribs` text NOT NULL COMMENT 'Milestone attributes in JSON format',
  `access` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Milestone ACL access level ID',
  `state` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Milestone state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed ',
  `ordering` int(10) NOT NULL DEFAULT '0' COMMENT 'Milestone ordering',
  `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Milestone start date',
  `end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Milestone end date',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_alias` (`alias`),
  KEY `idx_projectid` (`project_id`),
  KEY `idx_access` (`access`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_checkedout` (`checked_out`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Stores Projectfork milestone data';

DROP TABLE IF EXISTS `#__pf_milestone_map`;
CREATE TABLE IF NOT EXISTS `#__pf_milestone_map` (
  `id` int(10) unsigned NOT NULL COMMENT 'Dependency Map',
  `milestone_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Milestone ID reference',
  `dependency` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent Milestone dependency ID reference',
  PRIMARY KEY (`id`),
  KEY `idx_milestoneid` (`milestone_id`),
  KEY `idx_dependency` (`dependency`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Stores Projectfork milestone dependency';

DROP TABLE IF EXISTS `#__pf_projects`;
CREATE TABLE IF NOT EXISTS `#__pf_projects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Project ID',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
  `title` varchar(128) NOT NULL COMMENT 'Project title',
  `alias` varchar(128) NOT NULL COMMENT 'Title alias. Used in SEF URL''s',
  `description` text NOT NULL COMMENT 'Project description',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Project creation date',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Project owner',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Project modify date',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last user to modify the project',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User who is currently editing the project',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Check-out date and time',
  `attribs` text NOT NULL COMMENT 'Project attributes in JSON format',
  `access` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Project ACL access level ID',
  `state` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Project state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed',
  `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Project start date',
  `end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Project end date',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_alias` (`alias`),
  KEY `idx_access` (`access`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_checkedout` (`checked_out`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Stores Projectfork project data';

DROP TABLE IF EXISTS `#__pf_ref_tags`;
CREATE TABLE IF NOT EXISTS `#__pf_ref_tags` (
  `id` int(10) unsigned NOT NULL COMMENT 'Item ID reference',
  `context` varchar(32) NOT NULL COMMENT 'Reference context',
  `tag_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Tag ID reference',
  PRIMARY KEY (`context`,`id`,`tag_id`),
  KEY `idx_tagid` (`tag_id`),
  KEY `idx_contextid` (`context`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Stores Projectfork tag references';

DROP TABLE IF EXISTS `#__pf_ref_users`;
CREATE TABLE IF NOT EXISTS `#__pf_ref_users` (
  `id` int(10) unsigned NOT NULL COMMENT 'Item ID reference',
  `context` varchar(32) NOT NULL COMMENT 'Reference context',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User ID reference',
  PRIMARY KEY (`context`,`id`,`user_id`),
  KEY `idx_userid` (`user_id`),
  KEY `idx_contextid` (`context`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Stores Projectfork user references';

DROP TABLE IF EXISTS `#__pf_tags`;
CREATE TABLE IF NOT EXISTS `#__pf_tags` (
  `id` int(10) unsigned NOT NULL COMMENT 'Tag ID',
  `title` varchar(64) NOT NULL COMMENT 'Tag title',
  `alias` varchar(64) NOT NULL COMMENT 'Tag alias',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_alias` (`alias`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Stores Projectfork tags';

DROP TABLE IF EXISTS `#__pf_tasks`;
CREATE TABLE IF NOT EXISTS `#__pf_tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Task ID',
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
  `project_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
  `list_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent task list ID',
  `milestone_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent milestone ID',
  `title` varchar(128) NOT NULL COMMENT 'Task title',
  `alias` varchar(128) NOT NULL COMMENT 'Title alias. Used in SEF URL''s',
  `description` text NOT NULL COMMENT 'Task description',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Task creation date',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Task author',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Task modify date',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last user to modify the task',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User who is currently editing the task',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Check-out date and time',
  `attribs` text NOT NULL COMMENT 'Task attributes in JSON format',
  `access` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Task ACL access level ID',
  `state` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Task state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed ',
  `priority` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Task priority ID',
  `complete` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Task complete state',
  `ordering` int(10) NOT NULL DEFAULT '0' COMMENT 'Task ordering in a task list',
  `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Task start date',
  `end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Task end date',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_alias` (`project_id`,`alias`),
  KEY `idx_projectid` (`project_id`),
  KEY `idx_listid` (`list_id`),
  KEY `idx_milestone` (`milestone_id`),
  KEY `idx_access` (`access`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_checkedout` (`checked_out`),
  KEY `idx_priority` (`priority`),
  KEY `idx_complete` (`complete`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Stores Projectfork task data';

DROP TABLE IF EXISTS `#__pf_task_lists`;
CREATE TABLE IF NOT EXISTS `#__pf_task_lists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Task list ID',
  `asset_id` int(10) NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
  `project_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
  `milestone_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent milestone ID',
  `title` varchar(64) NOT NULL COMMENT 'Task list title',
  `alias` varchar(64) NOT NULL COMMENT 'Title alias. Used in SEF URL''s',
  `description` varchar(255) NOT NULL COMMENT 'Task list description',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Task list creation date',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Task list creator',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Task list modify date',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last user to modify the task list',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User who is currently editing the task list',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Check-out date and time',
  `attribs` text NOT NULL COMMENT 'Task list attributes in JSON format',
  `access` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Task List ACL access level ID',
  `state` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Task list state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed ',
  `ordering` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Task list ordering',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_alias` (`project_id`,`alias`),
  KEY `idx_projectid` (`project_id`),
  KEY `idx_milestoneid` (`milestone_id`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_checkedout` (`checked_out`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Stores Projectfork task list data';

DROP TABLE IF EXISTS `#__pf_task_map`;
CREATE TABLE IF NOT EXISTS `#__pf_task_map` (
  `id` int(10) unsigned NOT NULL COMMENT 'Dependency Map',
  `task_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Task ID reference',
  `dependency` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent Task dependency ID reference',
  PRIMARY KEY (`id`),
  KEY `idx_taskid` (`task_id`),
  KEY `idx_dependency` (`dependency`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Stores Projectfork task dependency';
