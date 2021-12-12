/* SPDX-License-Identifier: Zlib */
/* FSTube v1.4 (December 2021)
 * Copyright (C) 2020-2021 Norbert de Jonge <mail@norbertdejonge.nl>
 *
 * This software is provided 'as-is', without any express or implied
 * warranty.  In no event will the authors be held liable for any damages
 * arising from the use of this software.
 *
 * Permission is granted to anyone to use this software for any purpose,
 * including commercial applications, and to alter it and redistribute it
 * freely, subject to the following restrictions:
 *
 * 1. The origin of this software must not be misrepresented; you must not
 *    claim that you wrote the original software. If you use this software
 *    in a product, an acknowledgment in the product documentation would be
 *    appreciated but is not required.
 * 2. Altered source versions must be plainly marked as such, and must not be
 *    misrepresented as being the original software.
 * 3. This notice may not be removed or altered from any source distribution.
 */

CREATE DATABASE `fst` CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;
USE `fst`;

CREATE USER 'fst_user'@'%' IDENTIFIED BY 'YOURPASS';
GRANT ALL PRIVILEGES ON fst.* TO 'fst_user';
FLUSH PRIVILEGES;

CREATE TABLE `fst_user` (
	`user_id` int(11) NOT NULL AUTO_INCREMENT,
	`user_email` VARCHAR(200) UNIQUE NOT NULL,
	`user_username` VARCHAR(50) UNIQUE NOT NULL,
	`user_username_old1` VARCHAR(50) NOT NULL DEFAULT '',
	`user_username_old2` VARCHAR(50) NOT NULL DEFAULT '',
	`user_hash` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
	`user_priv_customthumbnails` tinyint(1) NOT NULL DEFAULT '1',
	`user_avatarset` tinyint(1) NOT NULL DEFAULT '0',
	`user_avatarip` VARCHAR(100) NOT NULL DEFAULT '',
	`user_information` text NOT NULL,
	`user_warnings_video` int(11) NOT NULL DEFAULT '0',
	`user_warnings_comment` int(11) NOT NULL DEFAULT '0',
	`user_warnings_mbpost` int NOT NULL DEFAULT '0',
	`user_warnings_avatar` int(11) NOT NULL DEFAULT '0',
	`user_deleted` tinyint(1) NOT NULL DEFAULT '0',
	`user_deleted_reason` text NOT NULL,
	`user_regip` VARCHAR(100) NOT NULL,
	`user_regdt` DATETIME NOT NULL,
	`user_lastlogindt` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
	`user_patron` tinyint NOT NULL DEFAULT '0',
	`user_pref_nsfw` tinyint(1) NOT NULL DEFAULT '0',
	`user_pref_cwidth` int(11) NOT NULL DEFAULT 0,
	`user_pref_tsize` int(11) NOT NULL DEFAULT 80,
	`user_pref_musers` mediumtext NOT NULL,
	PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_faillogin` (
	`faillogin_id` int(11) NOT NULL AUTO_INCREMENT,
	`faillogin_username` VARCHAR(50) NOT NULL,
	`faillogin_ip` VARCHAR(100) NOT NULL,
	`faillogin_dt` DATETIME NOT NULL,
	PRIMARY KEY (`faillogin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_video` (
	`video_id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL,
	`user_id_old` int(11) NOT NULL DEFAULT '0',
	`video_visibility` tinyint(1) NOT NULL DEFAULT '1',
	`video_title` varchar(100) NOT NULL,
	`video_description` text NOT NULL,
	`video_thumbnail` tinyint(1) NOT NULL DEFAULT '3',
	`video_tags` varchar(400) NOT NULL,
	`video_license` tinyint(1) NOT NULL DEFAULT '1',
	`category_id` tinyint(1) NOT NULL DEFAULT '0',
	`video_restricted` tinyint(1) NOT NULL DEFAULT '0',
	`video_comments_allow` tinyint(1) NOT NULL DEFAULT '1',
	`video_comments_show` tinyint(1) NOT NULL DEFAULT '1',
	`language_id` int(11) NOT NULL DEFAULT '0',
	`video_nsfw` tinyint(1) NOT NULL DEFAULT '2',
	`video_subtitles` mediumtext NOT NULL,
	`video_seconds` int(11) NOT NULL DEFAULT '0',
	`video_fps` decimal(6,2) NOT NULL DEFAULT '0.00',
	`video_preview` tinyint(1) NOT NULL DEFAULT '2',
	`video_preview_bytes` int(11) NOT NULL DEFAULT '0',
	`video_360` tinyint(1) NOT NULL DEFAULT '2',
	`video_360_bytes` int(11) NOT NULL DEFAULT '0',
	`video_360_width` int(11) NOT NULL DEFAULT '0',
	`video_360_height` int(11) NOT NULL DEFAULT '0',
	`video_720` tinyint(1) NOT NULL DEFAULT '2',
	`video_720_bytes` int(11) NOT NULL DEFAULT '0',
	`video_720_width` int(11) NOT NULL DEFAULT '0',
	`video_720_height` int(11) NOT NULL DEFAULT '0',
	`video_1080` tinyint(1) NOT NULL DEFAULT '2',
	`video_1080_bytes` int(11) NOT NULL DEFAULT '0',
	`video_1080_width` int(11) NOT NULL DEFAULT '0',
	`video_1080_height` int(11) NOT NULL DEFAULT '0',
	`video_ip` VARCHAR(100) NOT NULL,
	`video_views` int(11) NOT NULL DEFAULT '0',
	`video_likes` int NOT NULL DEFAULT '0',
	`video_comments` int NOT NULL DEFAULT '0',
	`video_deleted` tinyint(1) NOT NULL DEFAULT '0',
	`video_deletedate` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
	`video_adddate` datetime NOT NULL,
	`video_uploadedmd5` varchar(50) NOT NULL,
	`video_text` mediumtext NOT NULL,
	`video_textsavedt` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
	`video_istext` tinyint(1) NOT NULL DEFAULT '0',
	`board_id` int(11) NOT NULL DEFAULT '0',
	`video_sph_mpprojection` varchar(100) NOT NULL,
	`video_sph_stereo3dtype` varchar(100) NOT NULL,
	`projection_id` int NOT NULL DEFAULT '0',
	`poll_id` int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`video_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_comment` (
	`comment_id` int(11) NOT NULL AUTO_INCREMENT,
	`video_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`comment_text` varchar(4000) NOT NULL,
	`comment_loved` tinyint(1) NOT NULL DEFAULT '0',
	`comment_pinned` tinyint(1) NOT NULL DEFAULT '0',
	`comment_hidden` tinyint(1) NOT NULL DEFAULT '0',
	`comment_approved` tinyint(1) NOT NULL DEFAULT '0',
	`comment_ip` VARCHAR(100) NOT NULL,
	`comment_adddate` datetime NOT NULL,
	`comment_notify_publisher` int(11) NOT NULL DEFAULT '0',
	`comment_parent_id` int(11) NOT NULL DEFAULT '0',
	`comment_notify_parent` int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_likevideo` (
	`likevideo_id` int(11) NOT NULL AUTO_INCREMENT,
	`video_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`likevideo_adddate` datetime NOT NULL,
	PRIMARY KEY (`likevideo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_likecomment` (
	`likecomment_id` int(11) NOT NULL AUTO_INCREMENT,
	`comment_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`likecomment_adddate` datetime NOT NULL,
	PRIMARY KEY (`likecomment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_recentviews` (
	`recentviews_id` int(11) NOT NULL AUTO_INCREMENT,
	`video_id` int(11) NOT NULL,
	`recentviews_ip` VARCHAR(100) NOT NULL,
	`recentviews_date` DATE NOT NULL,
	PRIMARY KEY (`recentviews_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_category` (
	`category_id` int(11) NOT NULL AUTO_INCREMENT,
	`category_name` varchar(100) UNIQUE NOT NULL,
	PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `fst_category` SET category_name='Film & Animation';
INSERT INTO `fst_category` SET category_name='Autos & Vehicles';
INSERT INTO `fst_category` SET category_name='Music';
INSERT INTO `fst_category` SET category_name='Pets & Animals';
INSERT INTO `fst_category` SET category_name='Sports';
INSERT INTO `fst_category` SET category_name='Travel & Events';
INSERT INTO `fst_category` SET category_name='Gaming';
INSERT INTO `fst_category` SET category_name='People & Blogs';
INSERT INTO `fst_category` SET category_name='Comedy';
INSERT INTO `fst_category` SET category_name='Entertainment';
INSERT INTO `fst_category` SET category_name='News & Politics';
INSERT INTO `fst_category` SET category_name='Howto & Style';
INSERT INTO `fst_category` SET category_name='Education';
INSERT INTO `fst_category` SET category_name='Science & Technology';
INSERT INTO `fst_category` SET category_name='Nonprofits & Activism';
CREATE TABLE `fst_issue` (
	`issue_id` int(11) NOT NULL AUTO_INCREMENT,
	`issue_name_long` varchar(200) UNIQUE NOT NULL,
	`issue_name_short` varchar(50) UNIQUE NOT NULL,
	`issue_ind_video` tinyint(1) NOT NULL DEFAULT '0',
	`issue_ind_text` tinyint(1) NOT NULL DEFAULT '0',
	`issue_ind_text_forum` tinyint(1) NOT NULL DEFAULT '0',
	`issue_ind_comment` tinyint(1) NOT NULL DEFAULT '0',
	`issue_ind_mbpost` tinyint NOT NULL DEFAULT '0',
	`issue_ind_user` tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`issue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `fst_issue` VALUES (NULL, 'Heavy spammer or scammer', 'spammer', 0, 0, 0, 0, 1);
INSERT INTO `fst_issue` VALUES (NULL, 'Impersonation', 'impersonation', 0, 0, 0, 0, 1);
INSERT INTO `fst_issue` VALUES (NULL, 'Graphic violence', 'violence', 1, 0, 0, 0, 1);
INSERT INTO `fst_issue` VALUES (NULL, 'Sexually explicit content', 'explicit', 1, 1, 1, 1, 1);
INSERT INTO `fst_issue` VALUES (NULL, 'Promotes terrorism', 'terrorism', 1, 1, 1, 1, 1);
INSERT INTO `fst_issue` VALUES (NULL, 'Infringes my rights', 'rights', 1, 1, 1, 0, 1);
INSERT INTO `fst_issue` VALUES (NULL, 'Hate speech against a protected group', 'hate', 1, 1, 1, 1, 1);
INSERT INTO `fst_issue` VALUES (NULL, 'Severe threat/intimidation', 'threat', 1, 1, 1, 1, 1);
INSERT INTO `fst_issue` VALUES (NULL, 'Unlawful (e.g. libel)', 'unlawful', 1, 1, 1, 1, 1);
INSERT INTO `fst_issue` VALUES (NULL, 'Sexy/sexualized minor', 'minor', 1, 0, 0, 0, 1);
INSERT INTO `fst_issue` VALUES (NULL, 'Problematic thumbnail', 'thumbnail', 1, 1, 0, 0, 0);
CREATE TABLE `fst_report` (
	`report_id` int(11) NOT NULL AUTO_INCREMENT,
	`report_type` tinyint(1) NOT NULL,
	`issue_id` INT(11) NOT NULL,
	`report_occursattime` varchar(10) NOT NULL DEFAULT '',
	`video_id` int(11) NOT NULL,
	`comment_id` int(11) NOT NULL,
	`mbpost_id` int NOT NULL DEFAULT '0',
	`user_id` int(11) NOT NULL,
	`message` text NOT NULL,
	`report_email` VARCHAR(200) NOT NULL,
	`report_ip` VARCHAR(100) NOT NULL,
	`report_dt` DATETIME NOT NULL,
	`report_action` tinyint(2) NOT NULL DEFAULT '0',
	`report_action_dt` DATETIME NOT NULL,
	`report_action_user_id` int(11) NOT NULL,
	PRIMARY KEY (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_banned` (
	`banned_id` int(11) NOT NULL AUTO_INCREMENT,
	`banned_ip_from` VARBINARY(16) NOT NULL,
	`banned_ip_to` VARBINARY(16) NOT NULL,
	`banned_ip` VARBINARY(16) NOT NULL,
	`banned_istor` tinyint(1) NOT NULL DEFAULT '0',
	`banned_dt` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
	PRIMARY KEY (`banned_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_queue` (
	`queue_id` int(11) NOT NULL AUTO_INCREMENT,
	`queue_empty` tinyint(1) NOT NULL DEFAULT '1',
	PRIMARY KEY (`queue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `fst_queue` SET queue_empty='1';
CREATE TABLE `fst_subscribe` (
	`subscribe_id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id_channel` int(11) NOT NULL,
	`user_id_subscriber` int(11) NOT NULL,
	`subscribe_adddate` DATETIME NOT NULL,
	PRIMARY KEY (`subscribe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_message` (
	`message_id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id_sender` int(11) NOT NULL,
	`user_id_recipient` int(11) NOT NULL,
	`message_text` text NOT NULL,
	`video_id` int(11) NOT NULL,
	`message_adddate` DATETIME NOT NULL,
	`message_cleared` tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_search` (
	`search_id` int(11) NOT NULL AUTO_INCREMENT,
	`search_text` varchar(200) NOT NULL,
	`search_matchest` int(11) NOT NULL DEFAULT '0',
	`search_matcheso` int(11) NOT NULL DEFAULT '0',
	`search_adddate` DATETIME NOT NULL,
	PRIMARY KEY (`search_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_language` (
	`language_id` int(11) NOT NULL AUTO_INCREMENT,
	`language_nameeng` varchar(100) NOT NULL,
	`language_namelocal` varchar(100) NOT NULL,
	`language_iso639-1` varchar(2) NOT NULL,
	PRIMARY KEY (`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `fst_language` VALUES (NULL, 'Arabic', 'العَرَبِيَّة', 'ar');
INSERT INTO `fst_language` VALUES (NULL, 'Bengali', 'বাংলা', 'bn');
INSERT INTO `fst_language` VALUES (NULL, 'Bhojpuri', 'भोजपुरी', 'bh');
INSERT INTO `fst_language` VALUES (NULL, 'Burmese', 'မြန်မာဘာသာ', 'my');
INSERT INTO `fst_language` VALUES (NULL, 'Dutch', 'Nederlands', 'nl');
INSERT INTO `fst_language` VALUES (NULL, 'Eastern Punjabi', 'ਪੰਜਾਬੀ', 'pa');
INSERT INTO `fst_language` VALUES (NULL, 'English', 'English', 'en');
INSERT INTO `fst_language` VALUES (NULL, 'French', 'Français', 'fr');
INSERT INTO `fst_language` VALUES (NULL, 'German', 'Deutsch', 'de');
INSERT INTO `fst_language` VALUES (NULL, 'Gujarati', 'ગુજરાતી', 'gu');
INSERT INTO `fst_language` VALUES (NULL, 'Hakka', '客家話', 'zh');
INSERT INTO `fst_language` VALUES (NULL, 'Hausa', 'هَوُسَ', 'ha');
INSERT INTO `fst_language` VALUES (NULL, 'Hindi', 'हिन्दी', 'hi');
INSERT INTO `fst_language` VALUES (NULL, 'Igbo', 'Ìgbò', 'ig');
INSERT INTO `fst_language` VALUES (NULL, 'Indonesian', 'B Indonesia', 'id');
INSERT INTO `fst_language` VALUES (NULL, 'Italian', 'Italiano', 'it');
INSERT INTO `fst_language` VALUES (NULL, 'Japanese', '日本語', 'ja');
INSERT INTO `fst_language` VALUES (NULL, 'Javanese', 'Basa Jawa', 'jv');
INSERT INTO `fst_language` VALUES (NULL, 'Jin', '晋语', 'zh');
INSERT INTO `fst_language` VALUES (NULL, 'Kannada', 'ಕನ್ನಡ', 'kn');
INSERT INTO `fst_language` VALUES (NULL, 'Korean', '한국어', 'ko');
INSERT INTO `fst_language` VALUES (NULL, 'Maithili', 'मैथिली', 'bh');
INSERT INTO `fst_language` VALUES (NULL, 'Malayalam', 'മലയാളം', 'ml');
INSERT INTO `fst_language` VALUES (NULL, 'Mandarin', '官话', 'zh');
INSERT INTO `fst_language` VALUES (NULL, 'Marathi', 'मराठी', 'mr');
INSERT INTO `fst_language` VALUES (NULL, 'Min Nan', 'Bân-lâm-gú', 'zh');
INSERT INTO `fst_language` VALUES (NULL, 'Odia', 'ଓଡ଼ିଆ', 'or');
INSERT INTO `fst_language` VALUES (NULL, 'Persian', 'فارسی', 'fa');
INSERT INTO `fst_language` VALUES (NULL, 'Polish', 'Polski', 'pl');
INSERT INTO `fst_language` VALUES (NULL, 'Portuguese', 'Português', 'pt');
INSERT INTO `fst_language` VALUES (NULL, 'Romanian', 'Română', 'ro');
INSERT INTO `fst_language` VALUES (NULL, 'Russian', 'Русский', 'ru');
INSERT INTO `fst_language` VALUES (NULL, 'Sindhi', 'سِنڌِي', 'sd');
INSERT INTO `fst_language` VALUES (NULL, 'Spanish', 'Español', 'es');
INSERT INTO `fst_language` VALUES (NULL, 'Sunda', 'Basa Sunda', 'su');
INSERT INTO `fst_language` VALUES (NULL, 'Tagalog', 'ᜆᜄᜎᜓᜄ᜔', 'tl');
INSERT INTO `fst_language` VALUES (NULL, 'Tamil', 'தமிழ்', 'ta');
INSERT INTO `fst_language` VALUES (NULL, 'Telugu', 'తెలుగు', 'te');
INSERT INTO `fst_language` VALUES (NULL, 'Turkish', 'Türkçe', 'tr');
INSERT INTO `fst_language` VALUES (NULL, 'Ukrainian', 'Українська', 'uk');
INSERT INTO `fst_language` VALUES (NULL, 'Urdu', 'اردو', 'ur');
INSERT INTO `fst_language` VALUES (NULL, 'Uzbek', 'O‘zbek', 'uz');
INSERT INTO `fst_language` VALUES (NULL, 'Vietnamese', 'Tiếng Việt', 'vi');
INSERT INTO `fst_language` VALUES (NULL, 'Western Punjabi', 'S Pañjābī', 'pa');
INSERT INTO `fst_language` VALUES (NULL, 'Wu', '吴语', 'zh');
INSERT INTO `fst_language` VALUES (NULL, 'Xiang Chinese', '湘', 'zh');
INSERT INTO `fst_language` VALUES (NULL, 'Yoruba', 'Yorùbá', 'yo');
INSERT INTO `fst_language` VALUES (NULL, 'Yue', '粤语', 'zh');
CREATE TABLE `fst_mute` (
	`mute_id` int(11) NOT NULL AUTO_INCREMENT,
	`mute_user_publisher` int(11) NOT NULL,
	`mute_user_commenter` int(11) NOT NULL,
	`mute_dt` DATETIME NOT NULL,
	PRIMARY KEY (`mute_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_referrer` (
	`referrer_id` int(11) NOT NULL AUTO_INCREMENT,
	`video_id` int(11) NOT NULL,
	`referrer_url` VARCHAR(500) NOT NULL,
	`referrer_date` DATE NOT NULL,
	`referrer_count` int(11) NOT NULL DEFAULT '1',
	PRIMARY KEY (`referrer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE `fst_referrer` ADD UNIQUE INDEX `referrer_index` (`video_id`,`referrer_url`,`referrer_date`);
CREATE TABLE `fst_monetization` (
	`monetization_id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL,
	`monetization_information` text NOT NULL,
	`monetization_patreon_yn` tinyint(1) NOT NULL DEFAULT '0',
	`monetization_patreon_url` VARCHAR(250) NOT NULL,
	`monetization_paypalme_yn` tinyint(1) NOT NULL DEFAULT '0',
	`monetization_paypalme_url` VARCHAR(250) NOT NULL,
	`monetization_subscribestar_yn` tinyint(1) NOT NULL DEFAULT '0',
	`monetization_subscribestar_url` VARCHAR(250) NOT NULL,
	`monetization_bitbacker_yn` tinyint(1) NOT NULL DEFAULT '0',
	`monetization_bitbacker_url` VARCHAR(250) NOT NULL,
	`monetization_crypto1_yn` tinyint(1) NOT NULL DEFAULT '0',
	`monetization_crypto1_name` VARCHAR(30) NOT NULL,
	`monetization_crypto1_address` VARCHAR(250) NOT NULL,
	`monetization_crypto1_qr` tinyint(1) NOT NULL DEFAULT '2',
	`monetization_crypto2_yn` tinyint(1) NOT NULL DEFAULT '0',
	`monetization_crypto2_name` VARCHAR(30) NOT NULL,
	`monetization_crypto2_address` VARCHAR(250) NOT NULL,
	`monetization_crypto2_qr` tinyint(1) NOT NULL DEFAULT '2',
	`monetization_crypto3_yn` tinyint(1) NOT NULL DEFAULT '0',
	`monetization_crypto3_name` VARCHAR(30) NOT NULL,
	`monetization_crypto3_address` VARCHAR(250) NOT NULL,
	`monetization_crypto3_qr` tinyint(1) NOT NULL DEFAULT '2',
	`monetization_crypto4_yn` tinyint(1) NOT NULL DEFAULT '0',
	`monetization_crypto4_name` VARCHAR(30) NOT NULL,
	`monetization_crypto4_address` VARCHAR(250) NOT NULL,
	`monetization_crypto4_qr` tinyint(1) NOT NULL DEFAULT '2',
	PRIMARY KEY (`monetization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_board` (
	`board_id` int(11) NOT NULL AUTO_INCREMENT,
	`board_name` varchar(100) NOT NULL,
	`board_description` varchar(100) NOT NULL,
	`board_order` int(11) NOT NULL,
	PRIMARY KEY (`board_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `fst_board` VALUES (NULL, 'Entertainment', '', '10');
INSERT INTO `fst_board` VALUES (NULL, 'Environment', '', '20');
INSERT INTO `fst_board` VALUES (NULL, 'FSTube', '', '30');
INSERT INTO `fst_board` VALUES (NULL, 'General', '', '40');
INSERT INTO `fst_board` VALUES (NULL, 'History', '', '50');
INSERT INTO `fst_board` VALUES (NULL, 'Law and order', '', '60');
INSERT INTO `fst_board` VALUES (NULL, 'Media and culture', '', '70');
INSERT INTO `fst_board` VALUES (NULL, 'Philosophy', '', '80');
INSERT INTO `fst_board` VALUES (NULL, 'Politics and economics', '', '90');
INSERT INTO `fst_board` VALUES (NULL, 'Psychiatry', '', '100');
INSERT INTO `fst_board` VALUES (NULL, 'Religion', '', '110');
INSERT INTO `fst_board` VALUES (NULL, 'Science, biology, and health', '', '120');
INSERT INTO `fst_board` VALUES (NULL, 'Sexuality', '', '130');
INSERT INTO `fst_board` VALUES (NULL, 'Sports', '', '140');
INSERT INTO `fst_board` VALUES (NULL, 'Technology', '', '150');
CREATE TABLE `fst_folder` (
	`folder_id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL,
	`folder_title` varchar(100) NOT NULL,
	`folder_description` text NOT NULL,
	`folder_public` tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`folder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_folderitem` (
	`folderitem_id` int(11) NOT NULL AUTO_INCREMENT,
	`folder_id` int(11) NOT NULL,
	`video_id` int(11) NOT NULL,
	`folderitem_order` int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`folderitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_request` (
	`request_id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id_requestor` int(11) NOT NULL,
	`user_id_recipient` int(11) NOT NULL,
	`request_type` tinyint(1) NOT NULL,
	`request_adddate` DATETIME NOT NULL,
	`request_status` tinyint(1) NOT NULL DEFAULT '2',
	PRIMARY KEY (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_projection` (
	`projection_id` int(11) NOT NULL AUTO_INCREMENT,
	`projection_name` varchar(100) NOT NULL,
	`projection_videojs` varchar(100) NOT NULL,
	`projection_order` int(11) NOT NULL,
	PRIMARY KEY (`projection_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `fst_projection` VALUES (NULL, '180 monoscopic', '180_MONO', '10');
INSERT INTO `fst_projection` VALUES (NULL, '180 stereoscopic - side by side', '180_LR', '20');
INSERT INTO `fst_projection` VALUES (NULL, '180 stereoscopic - top and bottom', '180', '30');
INSERT INTO `fst_projection` VALUES (NULL, '360 monoscopic', '360', '40');
INSERT INTO `fst_projection` VALUES (NULL, '360 stereoscopic - side by side', '360_LR', '50');
INSERT INTO `fst_projection` VALUES (NULL, '360 stereoscopic - top and bottom', '360_TB', '60');
INSERT INTO `fst_projection` VALUES (NULL, 'Cube monoscopic', 'Cube', '70');
INSERT INTO `fst_projection` VALUES (NULL, 'Cube stereoscopic - side by side', 'Cube', '80');
INSERT INTO `fst_projection` VALUES (NULL, 'Cube stereoscopic - top and bottom', 'Cube', '90');
INSERT INTO `fst_projection` VALUES (NULL, 'EAC monoscopic', 'EAC', '100');
INSERT INTO `fst_projection` VALUES (NULL, 'EAC stereoscopic - side by side', 'EAC_LR', '110');
INSERT INTO `fst_projection` VALUES (NULL, 'EAC stereoscopic - top and bottom', 'EAC', '120');
CREATE TABLE `fst_poll` (
	`poll_id` int(11) NOT NULL AUTO_INCREMENT,
	`poll_question` varchar(100) NOT NULL,
	`poll_options` mediumtext NOT NULL,
	`poll_nroptions` int(11) NOT NULL,
	`poll_maxvotesperuser` int(11) NOT NULL,
	`poll_nrdays` int(11) NOT NULL,
	`poll_dt` DATETIME NOT NULL,
	PRIMARY KEY (`poll_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_vote` (
	`vote_id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL,
	`poll_id` int(11) NOT NULL,
	`vote_option` int(11) NOT NULL,
	`vote_ip` VARCHAR(100) NOT NULL,
	PRIMARY KEY (`vote_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_commentslastviewed` (
	`commentslastviewed_id` BIGINT NOT NULL AUTO_INCREMENT,
	`video_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`commentslastviewed_dt` datetime NOT NULL,
	PRIMARY KEY (`commentslastviewed_id`),
	UNIQUE KEY `video_and_user` (`video_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `fst_tdone` (
	`tdone_id` BIGINT NOT NULL AUTO_INCREMENT,
	`tdone_date` DATE UNIQUE NOT NULL,
	PRIMARY KEY (`tdone_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_top10_views` (
	`top10v_id` BIGINT NOT NULL AUTO_INCREMENT,
	`top10v_date` DATE NOT NULL,
	`top10v_rank` int(11) NOT NULL,
	`video_id` int(11) NOT NULL,
	`top10v_count` int(11) NOT NULL,
	`top10v_title` varchar(100) NOT NULL,
	PRIMARY KEY (`top10v_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_top10_likes` (
	`top10l_id` BIGINT NOT NULL AUTO_INCREMENT,
	`top10l_date` DATE NOT NULL,
	`top10l_rank` int(11) NOT NULL,
	`video_id` int(11) NOT NULL,
	`top10l_count` int(11) NOT NULL,
	`top10l_title` varchar(100) NOT NULL,
	PRIMARY KEY (`top10l_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_top10_commenters` (
	`top10c_id` BIGINT NOT NULL AUTO_INCREMENT,
	`top10c_date` DATE NOT NULL,
	`top10c_rank` int(11) NOT NULL,
	`video_id` int(11) NOT NULL,
	`top10c_count` int(11) NOT NULL,
	`top10c_title` varchar(100) NOT NULL,
	PRIMARY KEY (`top10c_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_top10_referrers` (
	`top10r_id` BIGINT NOT NULL AUTO_INCREMENT,
	`top10r_date` DATE NOT NULL,
	`top10r_rank` int(11) NOT NULL,
	`video_id` int(11) NOT NULL,
	`top10r_count` int(11) NOT NULL,
	`top10r_title` varchar(100) NOT NULL,
	PRIMARY KEY (`top10r_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fst_trending` (
	`trending_id` BIGINT NOT NULL AUTO_INCREMENT,
	`trending_date` DATE NOT NULL,
	`trending_rank` int(11) NOT NULL,
	`video_id` int(11) NOT NULL,
	`trending_total` int(11) NOT NULL,
	`trending_title` varchar(100) NOT NULL,
	PRIMARY KEY (`trending_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `fst_microblog_post` (
	`mbpost_id` int NOT NULL AUTO_INCREMENT,
	`user_id` int NOT NULL,
	`mbpost_ip` VARCHAR(100) NOT NULL,
	`mbpost_dt` datetime NOT NULL,
	`mbpost_text` VARCHAR(280) NOT NULL,
	`mbpost_likes` int NOT NULL DEFAULT '0',
	`mbpost_reblogs` int NOT NULL DEFAULT '0',
	`mbpost_id_reblog` int NOT NULL DEFAULT '0',
	`mbpost_hidden` tinyint NOT NULL DEFAULT '0',
	PRIMARY KEY (`mbpost_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `fst_follow` (
	`follow_id` int NOT NULL AUTO_INCREMENT,
	`user_id_microblog` int NOT NULL,
	`user_id_follower` int NOT NULL,
	`follow_adddate` DATETIME NOT NULL,
	PRIMARY KEY (`follow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `fst_likembpost` (
	`likembpost_id` int NOT NULL AUTO_INCREMENT,
	`mbpost_id` int NOT NULL,
	`user_id` int NOT NULL,
	`likembpost_adddate` datetime NOT NULL,
	PRIMARY KEY (`likembpost_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `fst_updatecounts` (
	`updatecounts_id` int NOT NULL AUTO_INCREMENT,
	`user_id_deleted` int NOT NULL,
	PRIMARY KEY (`updatecounts_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
