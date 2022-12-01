<?php
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

/*** This file is used by both the CMS and the cron. ***/

/*****************************************************************************/
function MakeDir ($sDir)
/*****************************************************************************/
{
	if (file_exists ($sDir) === FALSE)
	{
		if (mkdir ($sDir, 0700) === TRUE)
		{
			return (TRUE);
		} else {
			return (FALSE);
		}
	} else {
		return (TRUE);
	}
}
/*****************************************************************************/
function SendEmail ($arTo, $arBcc, $sSubject, $sMessage)
/*****************************************************************************/
{
	/*** Returns the number of sent messages. ***/

	if ($GLOBALS['live'] === FALSE) { return (1); /*** Success. ***/ }

	include_once (dirname (__FILE__) .
		'/' . $GLOBALS['swift_dir'] . '/lib/swift_required.php');

	$sBody = file_get_contents (dirname (__FILE__) . '/templates/default.htm');
	$sBody = str_replace ('[body]', $sMessage, $sBody);
	$sBody = str_replace ('[protocol]', $GLOBALS['protocol'], $sBody);
	$sBody = str_replace ('[domain]', $GLOBALS['domain'], $sBody);

	$transport = Swift_SmtpTransport::newInstance
		($GLOBALS['mail_host'], 465, 'ssl')
		->setUsername($GLOBALS['mail_from'])
		->setPassword($GLOBALS['mail_pass'])
		;
	$mailer = Swift_Mailer::newInstance($transport);

	$message = Swift_Message::newInstance()
		->setSubject($sSubject)
		->setFrom(array($GLOBALS['mail_from'] => $GLOBALS['name']))
		->setTo($arTo)
		->setBcc($arBcc)
		->setBody($sBody, 'text/html');

	try {
		$iSent = $mailer->send($message);
	} catch (Exception $e) {
		file_put_contents (dirname (__FILE__) . '/../' .
			$GLOBALS['private'] . '/error_log.txt',
			$e->getMessage(), FILE_APPEND);
		$iSent = 0;
	}

	return ($iSent);
}
/*****************************************************************************/
function VideoURL ($sCode, $sHeight)
/*****************************************************************************/
{
	$sURL = '/mp4/';
	$sURL .= $sCode[-1];
	$sURL .= '/';
	$sURL .= $sCode[-2];
	$sURL .= '/';
	$sURL .= $sCode;
	$sURL .= '_';
	$sURL .= $sHeight;
	$sURL .= '.mp4';

	return ($sURL);
}
/*****************************************************************************/
function CreateMessage ($iSenderID, $iRecipientID, $sText)
/*****************************************************************************/
{
	/* $iSenderID:
	 * -1 = system
	 * 0 = "an administrator"
	 * positive number = user ID
	 */

	$sDTNow = date ('Y-m-d H:i:s');

	$query_message = "INSERT INTO `fst_message` SET
		user_id_sender='" . $iSenderID . "',
		user_id_recipient='" . $iRecipientID . "',
		message_text='" . mysqli_real_escape_string
			($GLOBALS['link'], $sText) . "',
		video_id='0',
		message_adddate='" . $sDTNow . "',
		message_cleared='0'";
	Query ($query_message);
}
/*****************************************************************************/
function Sanitize ($sUserInput)
/*****************************************************************************/
{
	$sReturn = htmlentities ($sUserInput, ENT_QUOTES);
	$sReturn = str_ireplace ('javascript', 'JS', $sReturn);

	return ($sReturn);
}
/*****************************************************************************/
function UpdateCountCommentsVideo ($iVideoID)
/*****************************************************************************/
{
	$query_update = "UPDATE `fst_video` fv SET
			video_comments=(
				SELECT
					COUNT(*)
				FROM `fst_comment` fc
				WHERE (fc.video_id='" . $iVideoID . "')
				AND (fc.comment_hidden='0')
				AND (fv.video_comments_allow='1')
				AND (
					(fv.video_comments_show='1')
					OR (
						(fv.video_comments_show='2')
						AND (fc.comment_approved='1')
					)
				)
			)
		WHERE (fv.video_id='" . $iVideoID . "')";
	Query ($query_update);
}
/*****************************************************************************/
function UpdateCountReblogsMBPost ($iPostID)
/*****************************************************************************/
{
	/* Two queries, because MySQL disallows using a table that is
	 * being updated in a FROM clause, even in a subquery.
	 */
	$query_count = "SELECT
			COUNT(*) AS count
		FROM `fst_microblog_post`
		WHERE (mbpost_id_reblog='" . $iPostID . "')
		AND (mbpost_hidden='0')";
	$result_count = Query ($query_count);
	$row_count = mysqli_fetch_assoc ($result_count);
	$iCount = intval ($row_count['count']);

	$query_update = "UPDATE `fst_microblog_post` SET
			mbpost_reblogs='" . $iCount . "'
		WHERE (mbpost_id='" . $iPostID . "')";
	Query ($query_update);
}
/*****************************************************************************/
function SettingSave ($sKey, $sValue)
/*****************************************************************************/
{
	/* This function does NOT - and should NOT - Sanitize() $sValue.
	 * Whatever calls this function may sanitize the value.
	 */

	$query_save = "INSERT INTO `fst_setting` SET
			setting_key='" . $sKey . "',
			setting_value='" . $sValue . "'
		ON DUPLICATE KEY UPDATE
			setting_value='" . $sValue . "'";
	Query ($query_save);
}
/*****************************************************************************/
function SettingLoad ($sKey)
/*****************************************************************************/
{
	/*** Returns FALSE or a value. ***/

	$query_load = "SELECT
			setting_value
		FROM `fst_setting`
		WHERE (setting_key='" . $sKey . "')";
	$result_load = Query ($query_load);
	if (mysqli_num_rows ($result_load) != 0)
	{
		$row_load = mysqli_fetch_assoc ($result_load);
		$sValue = $row_load['setting_value'];
		return ($sValue);
	} else {
		return (FALSE);
	}
}
/*****************************************************************************/
?>
