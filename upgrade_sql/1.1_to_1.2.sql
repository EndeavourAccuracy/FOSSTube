/* SPDX-License-Identifier: Zlib */
/* FOSSTube v1.5 (February 2022)
 * Copyright (C) 2020-2022 Norbert de Jonge <mail@norbertdejonge.nl>
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

ALTER TABLE `fst_video` ADD video_sph_mpprojection varchar(100) NOT NULL AFTER board_id;
ALTER TABLE `fst_video` ADD video_sph_stereo3dtype varchar(100) NOT NULL AFTER video_sph_mpprojection;
ALTER TABLE `fst_video` ADD projection_id int(11) NOT NULL DEFAULT '0' AFTER video_sph_stereo3dtype;

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

ALTER TABLE `fst_video` ADD poll_id int(11) NOT NULL DEFAULT '0' AFTER projection_id;

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
