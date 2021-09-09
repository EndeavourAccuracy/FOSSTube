/* SPDX-License-Identifier: Zlib */
/* FSTube v1.3 (September 2021)
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

ALTER TABLE `fst_video` ADD COLUMN video_likes int(11) NOT NULL DEFAULT '0' AFTER video_views;
ALTER TABLE `fst_video` ADD COLUMN video_comments int(11) NOT NULL DEFAULT '0' AFTER video_likes;
UPDATE `fst_video` fv SET video_likes=(SELECT COUNT(*) FROM `fst_likevideo` fl WHERE (fv.video_id = fl.video_id));
UPDATE `fst_video` fv SET video_comments=(SELECT COUNT(*) FROM `fst_comment` fc WHERE (fv.video_id = fc.video_id) AND (fc.comment_hidden='0') AND (fv.video_comments_allow='1') AND ((fv.video_comments_show='1') OR ((fv.video_comments_show='2') AND (fc.comment_approved='1'))));

ALTER TABLE `fst_user` ADD user_pref_musers mediumtext NOT NULL AFTER user_pref_tsize;
