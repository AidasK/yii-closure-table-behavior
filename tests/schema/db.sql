DROP TABLE IF EXISTS `folder_tree`;
DROP TABLE IF EXISTS `folder`;

CREATE TABLE IF NOT EXISTS `folder` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `folder_tree` (
  `parent` int(10) unsigned NOT NULL,
  `child` int(10) unsigned NOT NULL,
  `depth` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`parent`,`child`),
  KEY `fk_folder_tree_child_folder` (`child`),
  CONSTRAINT `fk_folder_tree_child_folder` FOREIGN KEY (`child`) REFERENCES `folder` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_folder_tree_parent_folder` FOREIGN KEY (`parent`) REFERENCES `folder` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*------------ For tests only ---------------------*/

DROP TABLE IF EXISTS `related`;

CREATE TABLE IF NOT EXISTS `related` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_folder_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_related_parent_folder` FOREIGN KEY (`parent_folder_id`) REFERENCES `folder` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;