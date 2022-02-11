<?php
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

include_once (dirname (__FILE__) . '/fst_base.php');

/*****************************************************************************/

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if ((isset ($_POST['video'])) &&
		(isset ($_POST['comment'])) &&
		(isset ($_POST['mbpost'])) &&
		(isset ($_POST['user'])) &&
		(isset ($_POST['problem'])) &&
		(isset ($_POST['occursattime'])) &&
		(isset ($_POST['captcha'])) &&
		(isset ($_POST['email'])) &&
		(isset ($_POST['message'])) &&
		(isset ($_POST['agree'])))
	{
		$sCaptcha = str_replace ('−', '-', $_POST['captcha']);

		if (($GLOBALS['live'] === FALSE) ||
			($sCaptcha == VerifyAnswer()))
		{
			$sError = '';
			$iReportType = 0;
			/***/
			$arVideo = VideoExists ($_POST['video']);
			if ($arVideo !== FALSE)
			{
				$iReportType = 1;
				$iUserID = $arVideo['user_id'];
				$iVideoID = $arVideo['id'];
			} else { $iVideoID = 0; }
			/***/
			$arComment = CommentExists ($_POST['comment'], 0);
			if ($arComment !== FALSE)
			{
				$iReportType = 2;
				$iUserID = $arComment['user_id'];
				$iCommentID = $arComment['id'];
			} else { $iCommentID = 0; }
			/***/
			$arPost = MBPostExists ($_POST['user'], $_POST['mbpost']);
			if ($arPost !== FALSE)
			{
				$iReportType = 5;
				$iUserID = $arPost['user_id'];
				$iPostID = $arPost['id'];
			} else { $iPostID = 0; }
			/***/
			if ($iReportType != 5)
			{
				$arUser = UserExists ($_POST['user']);
				if ($arUser !== FALSE)
				{
					$iReportType = 3;
					$iUserID = $arUser['id'];
				}
			}
			/***/
			$sMessage = $_POST['message'];
			if ($sMessage != '')
			{
				$iReportType = 4;
				$iUserID = 0;
			}
			/***/
			if ($iReportType == 0)
				{ $sError = 'Unknown report type.'; }
			if (strlen ($sMessage) > 5000)
				{ $sError = 'A message must be 1-5000 chars.'; }
			$arIssue = IssueExists ($_POST['problem']);
			if ($arIssue === FALSE)
			{
				if (in_array ($iReportType, array (1, 2, 3, 5)) === TRUE)
				{
					$sError = 'You must select an issue.';
				} else { $iIssueID = 0; }
			} else { $iIssueID = $arIssue['id']; }
			/***/
			$sOccursAtTime = $_POST['occursattime'];
			if (strlen ($sOccursAtTime) > 10)
				{ $sError = 'Time is too long.'; }
			if ((!preg_match
				('/^(([01]?[0-9]|2[0-3]):)?([0-5]?[0-9]):([0-5][0-9])$/',
				$sOccursAtTime)) && ($sOccursAtTime != ''))
				{ $sError = 'Invalid time value.'; }
			/***/
			$sEmail = $_POST['email'];
			if (IsEmail ($sEmail) === FALSE)
				{ $sError = 'Not a valid email address.'; }
			if (strlen ($sEmail) > 100)
				{ $sError = 'Email address is too long.'; }
			foreach ($GLOBALS['disallowed_email_ends'] AS $sEnd)
			{
				if (substr (strtolower ($sEmail), 0 - strlen ($sEnd)) == $sEnd)
				{
					$sError = 'Addresses that end with "' . $sEnd . '" are not allowed.';
				}
			}
			/***/
			$iAgree = intval ($_POST['agree']);
			if ($iAgree != 1)
				{ $sError = 'You must check the checkbox.'; }

			if ($sError == '')
			{
				$sIP = GetIP();
				$sDTNow = date ('Y-m-d H:i:s');

				$query_add = "INSERT INTO `fst_report` SET
					report_type='" . $iReportType . "',
					issue_id='" . $iIssueID . "',
					report_occursattime='" . mysqli_real_escape_string
						($GLOBALS['link'], $sOccursAtTime) . "',
					video_id='" . $iVideoID . "',
					comment_id='" . $iCommentID . "',
					mbpost_id='" . $iPostID . "',
					user_id='" . $iUserID . "',
					message='" . mysqli_real_escape_string
						($GLOBALS['link'], $sMessage) . "',
					report_email='" . mysqli_real_escape_string
						($GLOBALS['link'], $sEmail) . "',
					report_ip='" . $sIP . "',
					report_dt='" . $sDTNow . "',
					report_action='0',
					report_action_dt='2010-10-10 10:10:10',
					report_action_user_id='0'";
				$result_add = Query ($query_add);
				if (mysqli_affected_rows ($GLOBALS['link']) == 1)
				{
					$arResult['result'] = 1;
					$arResult['error'] = '';
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Could not process.';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = $sError;
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Captcha incorrect.';
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Data is missing.';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
}
print (json_encode ($arResult));
?>
