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

ALTER TABLE `fst_user` ADD user_pref_cwidth int(11) NOT NULL DEFAULT 0 AFTER user_pref_nsfw;
ALTER TABLE `fst_user` ADD user_pref_tsize int(11) NOT NULL DEFAULT 80 AFTER user_pref_cwidth;

CREATE TABLE `fst_request` (
	`request_id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id_requestor` int(11) NOT NULL,
	`user_id_recipient` int(11) NOT NULL,
	`request_type` tinyint(1) NOT NULL,
	`request_adddate` DATETIME NOT NULL,
	`request_status` tinyint(1) NOT NULL DEFAULT '2',
	PRIMARY KEY (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `fst_monetization`
ADD COLUMN `monetization_crypto1_name` VARCHAR(30) NOT NULL,
ADD COLUMN `monetization_crypto1_address` VARCHAR(250) NOT NULL,
ADD COLUMN `monetization_crypto1_qr` tinyint(1) NOT NULL DEFAULT '2',
ADD COLUMN `monetization_crypto2_yn` tinyint(1) NOT NULL DEFAULT '0',
ADD COLUMN `monetization_crypto2_name` VARCHAR(30) NOT NULL,
ADD COLUMN `monetization_crypto2_address` VARCHAR(250) NOT NULL,
ADD COLUMN `monetization_crypto2_qr` tinyint(1) NOT NULL DEFAULT '2',
ADD COLUMN `monetization_crypto3_yn` tinyint(1) NOT NULL DEFAULT '0',
ADD COLUMN `monetization_crypto3_name` VARCHAR(30) NOT NULL,
ADD COLUMN `monetization_crypto3_address` VARCHAR(250) NOT NULL,
ADD COLUMN `monetization_crypto3_qr` tinyint(1) NOT NULL DEFAULT '2',
ADD COLUMN `monetization_crypto4_yn` tinyint(1) NOT NULL DEFAULT '0',
ADD COLUMN `monetization_crypto4_name` VARCHAR(30) NOT NULL,
ADD COLUMN `monetization_crypto4_address` VARCHAR(250) NOT NULL,
ADD COLUMN `monetization_crypto4_qr` tinyint(1) NOT NULL DEFAULT '2',
ADD COLUMN `monetization_crypto1_yn` tinyint(1) NOT NULL DEFAULT '0'
AFTER monetization_bitbacker_url;

ALTER TABLE `fst_board` ADD COLUMN `board_description` varchar(100) NOT NULL AFTER `board_name`;
