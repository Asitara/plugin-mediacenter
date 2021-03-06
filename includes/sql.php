<?php
/*	Project:	EQdkp-Plus
 *	Package:	MediaCenter Plugin
 *	Link:		http://eqdkp-plus.eu
 *
 *	Copyright (C) 2006-2015 EQdkp-Plus Developer Team
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU Affero General Public License as published
 *	by the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Affero General Public License for more details.
 *
 *	You should have received a copy of the GNU Affero General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
if (!defined('EQDKP_INC'))
{
  header('HTTP/1.0 404 Not Found');exit;
}

$mediacenterSQL = array(

  'uninstall' => array(
    1     => 'DROP TABLE IF EXISTS `__mediacenter_categories`',
	2     => 'DROP TABLE IF EXISTS `__mediacenter_media`',
	3     => 'DROP TABLE IF EXISTS `__mediacenter_albums`',
  ),

  'install'   => array(
	1 => "CREATE TABLE `__mediacenter_categories` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
	`alias` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
	`description` TEXT NOT NULL COLLATE 'utf8_bin',
	`per_page` INT(3) UNSIGNED NOT NULL DEFAULT '25',
	`permissions` TEXT NOT NULL COLLATE 'utf8_bin',
	`published` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
	`parent` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`sort_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`types` TEXT NOT NULL COLLATE 'utf8_bin',
  	`layout` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`notify_on_onpublished` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`default_published_state` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`allow_comments` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`allow_voting` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  	
	PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
",
	2 => "CREATE TABLE `__mediacenter_media` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`album_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  	`category_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`name` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
	`description` TEXT NOT NULL COLLATE 'utf8_bin',
	`localfile` TEXT NULL COLLATE 'utf8_bin',
  	`filename` TEXT NOT NULL COLLATE 'utf8_bin',
	`externalfile` TEXT NULL COLLATE 'utf8_bin',
	`previewimage` TEXT NULL COLLATE 'utf8_bin',
	`type` INT(3) UNSIGNED NOT NULL DEFAULT '1',
	`tags` TEXT NOT NULL COLLATE 'utf8_bin',
	`votes_count` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`votes_sum` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`votes_users` TEXT NULL COLLATE 'utf8_bin',
	`featured` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
  	`reported` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
  	`reported_by` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  	`reported_text` TEXT NULL COLLATE 'utf8_bin',
	`published` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	`additionaldata` TEXT NULL COLLATE 'utf8_bin',
	`date` INT(10) UNSIGNED NOT NULL,
	`views` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  	`downloads` INT(10) UNSIGNED NOT NULL DEFAULT '0',	
  	`user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
",
	
	3 => "CREATE TABLE `__mediacenter_albums` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`description` TEXT NOT NULL COLLATE 'utf8_bin',
	`personal_album` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	`user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`date` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`category_id` INT(10) UNSIGNED NOT NULL,
	PRIMARY KEY (`id`)
)
DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
",
  		
  4 => "INSERT INTO `__mediacenter_categories` (`id`, `name`, `alias`, `description`, `per_page`, `permissions`, `published`, `parent`, `sort_id`, `types`, `layout`, `notify_on_onpublished`, `default_published_state`, `allow_comments`, `allow_voting`) VALUES (1, 'Gallery', 'gallery', '&lt;p&gt;Diese Kategorie ist nur für Bilder.&lt;/p&gt;', 25, 'a:6:{s:3:\"rea\";a:6:{i:1;s:1:\"1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"1\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"cre\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"1\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"ada\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"1\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"upd\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"0\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"del\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"0\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"chs\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"0\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}}', 1, 0, 0, 'a:1:{i:0;s:1:\"2\";}', 0, 0, 1, 1, 1);",
  5 => "INSERT INTO `__mediacenter_categories` (`id`, `name`, `alias`, `description`, `per_page`, `permissions`, `published`, `parent`, `sort_id`, `types`, `layout`, `notify_on_onpublished`, `default_published_state`, `allow_comments`, `allow_voting`) VALUES (2, 'Downloads', 'downloads', '&lt;p&gt;Diese Kategorie ist nur für Dateien.&lt;/p&gt;', 25, 'a:6:{s:3:\"rea\";a:6:{i:1;s:1:\"1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"1\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"cre\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"1\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"ada\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"1\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"upd\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"0\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"del\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"0\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"chs\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"0\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}}', 1, 0, 2, 'a:1:{i:0;s:1:\"0\";}', 0, 0, 1, 1, 1);",
  6 => "INSERT INTO `__mediacenter_categories` (`id`, `name`, `alias`, `description`, `per_page`, `permissions`, `published`, `parent`, `sort_id`, `types`, `layout`, `notify_on_onpublished`, `default_published_state`, `allow_comments`, `allow_voting`) VALUES (3, 'Videos', 'videos', '&lt;p&gt;Diese Kategorie ist nur für Videos gedacht.&lt;/p&gt;', 25, 'a:6:{s:3:\"rea\";a:6:{i:1;s:1:\"1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"1\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"cre\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"1\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"ada\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"1\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"upd\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"0\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"del\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"0\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"chs\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"0\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}}', 1, 0, 3, 'a:1:{i:0;s:1:\"1\";}', 0, 0, 1, 1, 1);",
  7 => "INSERT INTO `__mediacenter_categories` (`id`, `name`, `alias`, `description`, `per_page`, `permissions`, `published`, `parent`, `sort_id`, `types`, `layout`, `notify_on_onpublished`, `default_published_state`, `allow_comments`, `allow_voting`) VALUES (4, 'Medien', 'media', '&lt;p&gt;In dieser Kategorie können sämtliche Medien abgelegt werden.&lt;/p&gt;', 25, 'a:6:{s:3:\"rea\";a:6:{i:1;s:1:\"1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"1\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"cre\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"1\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"ada\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"1\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"upd\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"0\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"del\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"0\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}s:3:\"chs\";a:6:{i:1;s:2:\"-1\";i:2;s:1:\"1\";i:3;s:1:\"1\";i:4;s:1:\"0\";i:5;s:2:\"-1\";i:6;s:2:\"-1\";}}', 1, 0, 5, 'a:3:{i:0;s:1:\"0\";i:1;s:1:\"1\";i:2;s:1:\"2\";}', 0, 0, 1, 1, 1);",
  
  ));

?>