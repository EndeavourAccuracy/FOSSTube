/* SPDX-License-Identifier: Zlib */
/* FOSSTube v1.6 (December 2022)
 * Copyright (C) 2020-2022 Norbert de Jonge <nlmdejonge@gmail.com>
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

ALTER TABLE `fst_issue` ADD COLUMN issue_ind_mbpost tinyint NOT NULL DEFAULT '0' AFTER issue_ind_comment;
UPDATE `fst_issue` SET issue_ind_mbpost=issue_ind_comment;
ALTER TABLE `fst_report` ADD COLUMN mbpost_id int NOT NULL DEFAULT '0' AFTER comment_id;
ALTER TABLE `fst_user` ADD COLUMN user_warnings_mbpost int NOT NULL DEFAULT '0' AFTER user_warnings_comment;

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

ALTER TABLE `fst_user` ADD COLUMN user_patron tinyint NOT NULL DEFAULT '0' AFTER user_lastlogindt;
