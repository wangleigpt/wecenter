ALTER TABLE `[#DB_PREFIX#]answer_comments` CHANGE `message` `message` TEXT NULL DEFAULT NULL;
ALTER TABLE `[#DB_PREFIX#]question_comments` CHANGE `message` `message` TEXT NULL DEFAULT NULL;
DROP TABLE `[#DB_PREFIX#]users_search`;
DROP TABLE `[#DB_PREFIX#]login`;
ALTER TABLE `[#DB_PREFIX#]user_action_history` ADD `fold_status` TINYINT( 1 ) NULL DEFAULT '0';
ALTER TABLE `[#DB_PREFIX#]user_action_history` ADD INDEX (  `fold_status` );

INSERT INTO `[#DB_PREFIX#]system_setting` (`varname`, `value`) VALUES ('reader_questions_last_days', 's:2:"30";');
INSERT INTO `[#DB_PREFIX#]system_setting` (`varname`, `value`) VALUES ('reader_questions_agree_count', 's:2:"10";');

ALTER TABLE `[#DB_PREFIX#]topic` ADD `merged_id` INT( 11 ) NULL DEFAULT '0';
ALTER TABLE `[#DB_PREFIX#]topic` ADD INDEX (`merged_id`);

CREATE TABLE `[#DB_PREFIX#]topic_merge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_id` int(11) NOT NULL DEFAULT '0',
  `target_id` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) DEFAULT '0',
  `time` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `source_id` (`source_id`),
  KEY `target_id` (`target_id`),
  KEY `uid` (`uid`)
) ENGINE=[#DB_ENGINE#]  DEFAULT CHARSET=utf8 ;