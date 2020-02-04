<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.0 (February 2020)
 * Copyright (C) 2020 Norbert de Jonge <mail@norbertdejonge.nl>
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

$GLOBALS['live'] = TRUE;
$GLOBALS['maintenance'] = FALSE;

if ($GLOBALS['live'] === FALSE)
{
	$GLOBALS['protocol'] = 'http';
	$GLOBALS['domain'] = 'yourdomain-debug.com';
	$GLOBALS['header_image_name'] = 'debug_208x30.png';
} else {
	$GLOBALS['protocol'] = 'https';
	$GLOBALS['domain'] = 'yourdomain.com';
	$GLOBALS['header_image_name'] = 'yourdomain_208x30.png';
}

/* If you modify 'private', also change
 * $sPrivate in (public_html)/fst_base.php
 */
$GLOBALS['public_html'] = 'public_html';
$GLOBALS['private'] = 'private';
$GLOBALS['home'] = '/home/'; /*** WITH slashes. ***/

$GLOBALS['name'] = 'Website name';
$GLOBALS['name_seo_alternative'] = 'Alternative name';
$GLOBALS['name_copyright'] = 'Copyright holder';
$GLOBALS['short_description'] = 'Website description.';
$GLOBALS['header_image_width'] = 208;
$GLOBALS['header_image_height'] = 30;
$GLOBALS['swift_dir'] = 'swift_random';
$GLOBALS['mail_host'] = 'SMTP hostname';
$GLOBALS['mail_from'] = 'SMTP email address';
$GLOBALS['mail_pass'] = 'SMTP password';
$GLOBALS['mail_admins'] = array ('info@yourdomain.com');
$GLOBALS['max_faillogin_hour'] = 10;

/* If you modify this, also modify post_max_size and
 * upload_max_filesize in Apache's php.ini.
 */
$GLOBALS['max_file_size'] = '500M';

$GLOBALS['max_accounts'] = 5;
$GLOBALS['warn_accounts'] = 3;

/*** All admins are automatically also mods. ***/
$GLOBALS['owners'] = array ('user1');
$GLOBALS['admins'] = array ('user2');
$GLOBALS['mods'] = array ('user3', 'user4');

$GLOBALS['disallowed_usernames'] = array ('yourdomain', 'admin', 'webmaster', 'postmaster', 'root', 'administrator', 'sysadmin', 'moderator', 'webadmin');
$GLOBALS['disallowed_email_ends'] = array ('.onion');
$GLOBALS['items_per_page'] = 24;
/***
$sGrep = 'grep -c processor /proc/cpuinfo';
$iCores = intval (shell_exec ($sGrep));
if ($iCores == 0) { print ('Unknown number of cores.'); exit(); }
***/
$GLOBALS['total_cpu_cores'] = 4;
$GLOBALS['ffmpeg_threads'] = 2; /*** Use two less than total_cpu_cores. ***/

/*** Filter settings. ***/
$GLOBALS['allowed_thresholds'] = array (0, 10, 30, 100);
$GLOBALS['default_threshold'] = 30;
$GLOBALS['allowed_nsfws'] = array (0, 1, 2, 3); /*** 3 = any ***/
$GLOBALS['default_nsfw'] = 0;

$GLOBALS['max_cpu_load'] = 0.95; /*** Out of 1 (even with mult. cores). ***/
$GLOBALS['default_theme'] = 'day';
$GLOBALS['no_embed_domains'] = array ('mytubes.xyz', 'nyuu.info', 'av4.xyz', 'mp44.us', 'av4.club', 'jpg4.xyz', 'jtube.space', 'jpger.info', 'fc2av.com', 'sagac.info', 'youtube4download.space');
$GLOBALS['default_pref']['user_pref_nsfw'] = 0;

/* Video (transcoding) quality. LOWER value is better.
 * Example values are 25, 20 (default), and 17 (visually near-lossless).
 * Lower values substantially increase file sizes.
 */
$GLOBALS['ffmpeg_crf'] = 20;

/* Audio (transcoding) quality. HIGHER value is better.
 * Example values are '64k', '96k', and '128k' (default).
 * The audio tracks take up less disk space than video.
 */
$GLOBALS['ffmpeg_ab'] = '128k';

/* Users who may publish content.
 * An empty array means everyone.
 */
$GLOBALS['may_add_videos'] = array();
$GLOBALS['may_add_texts'] = array();
$GLOBALS['may_add_topics'] = array();
?>
