<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.1 (March 2021)
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
?>
