ALTER TABLE `[#DB_PREFIX#]invitation` DROP `object_type`;
ALTER TABLE `[#DB_PREFIX#]invitation` DROP `object_id`;
DROP TABLE `[#DB_PREFIX#]users_apply`;
DROP TABLE `[#DB_PREFIX#]admin_menu`;

ALTER TABLE `[#DB_PREFIX#]system_setting` DROP `desc`,DROP `detail`,DROP `groupid`,DROP `sort`,DROP `type`,DROP `status`;
INSERT INTO `[#DB_PREFIX#]system_setting` (`id`, `varname`, `value`) VALUES 
(83, 'db_version', ''),
(84, 'statistic_code', 's:0:"";'),
(85, 'upload_enable', 's:1:"Y";'),
(86, 'answer_length_lower', 's:1:"0";'),
(87, 'quick_publish', 's:1:"Y";'),
(88, 'invite_reg_only', 's:1:"N";'),
(89, 'question_title_limit', 's:3:"100";');

CREATE TABLE `[#DB_PREFIX#]question_comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(11) DEFAULT '0',
  `uid` int(11) DEFAULT '0',
  `message` varchar(255) DEFAULT NULL,
  `time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`)
) ENGINE=[#DB_ENGINE#] DEFAULT CHARSET=utf8;

ALTER TABLE `[#DB_PREFIX#]users` ADD `level` TINYINT( 3 ) NULL DEFAULT '0' COMMENT '用户声望级别' AFTER `admin_id`;

CREATE TABLE IF NOT EXISTS `[#DB_PREFIX#]users_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) DEFAULT '0',
  `group_name` varchar(50) NOT NULL,
  `reputation_lower` int(11) DEFAULT '0',
  `reputation_higer` int(11) DEFAULT '0',
  `stars` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`group_id`)
) ENGINE=[#DB_ENGINE#]  DEFAULT CHARSET=utf8;

INSERT INTO `[#DB_PREFIX#]users_group` (`group_id`, `type`, `group_name`, `reputation_lower`, `reputation_higer`, `stars`) VALUES
(1, 0, '初级会员', 0, 10, 0),
(2, 0, 'VIP_1', 10, 50, 1),
(3, 0, 'VIP_2', 50, 200, 2),
(4, 0, 'VIP_3', 200, 500, 3),
(5, 0, 'VIP_4', 500, 1000, 4),
(6, 0, 'VIP_5', 1000, 99999, 5);