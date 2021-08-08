<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.2 (August 2021)
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

include_once (dirname (__FILE__) . '/../fst_base.php');

/*****************************************************************************/
function HideVideoOrComment ($row_report, $bWarning)
/*****************************************************************************/
{
	$iReportType = $row_report['report_type'];
	$sDTNow = date ('Y-m-d H:i:s');

	if ($iReportType == 1) /*** video ***/
	{
		/*** $sTitle ***/
		$query_title = "SELECT
				video_title
			FROM `fst_video`
			WHERE (video_id='" . $row_report['video_id'] . "')";
		$result_title = Query ($query_title);
		$row_title = mysqli_fetch_assoc ($result_title);
		$sTitle = $row_title['video_title'];

		CreateMessage (-1, $row_report['user_id'], 'A moderator has removed "' .
			$sTitle . '".');

		$query_update = "UPDATE `fst_video` SET
				video_deleted='3',
				video_deletedate='" . $sDTNow . "'
			WHERE (video_id='" . $row_report['video_id'] . "')";
		Query ($query_update);

		if ($bWarning === TRUE)
		{
			$query_user = "UPDATE `fst_user` SET
					user_warnings_video=user_warnings_video+1
				WHERE (user_id='" . $row_report['user_id'] . "')";
			Query ($query_user);
		}

		return ('video');
	}

	if ($iReportType == 2) /*** comment ***/
	{
		$query_update = "UPDATE `fst_comment` SET
				comment_hidden='1'
			WHERE (comment_id='" . $row_report['comment_id'] . "')";
		Query ($query_update);

		if ($bWarning === TRUE)
		{
			$query_user = "UPDATE `fst_user` SET
					user_warnings_comment=user_warnings_comment+1
				WHERE (user_id='" . $row_report['user_id'] . "')";
			Query ($query_user);
		}

		return ('comment');
	}
}
/*****************************************************************************/
function DeleteAvatar ($iUserID, $bWarning)
/*****************************************************************************/
{
	/*** Also removes the profile text. ***/

	$sUsername = GetUserInfo ($iUserID, 'user_username');

	$sPathFile = dirname (__FILE__) . '/../avatars/' . $sUsername . '_small.png';
	if (file_exists ($sPathFile) === TRUE) { unlink ($sPathFile); }
	$sPathFile = dirname (__FILE__) . '/../avatars/' . $sUsername . '_large.png';
	if (file_exists ($sPathFile) === TRUE) { unlink ($sPathFile); }

	$query_av = "UPDATE `fst_user` SET
			user_avatarset='0',
			user_information=''
		WHERE (user_id='" . $iUserID . "')";
	Query ($query_av);

	if ($bWarning === TRUE)
	{
		$query_user = "UPDATE `fst_user` SET
				user_warnings_avatar=user_warnings_avatar+1
			WHERE (user_id='" . $iUserID . "')";
		Query ($query_user);
	}
}
/*****************************************************************************/
function BanUser ($iUserID)
/*****************************************************************************/
{
	$sDTNow = date ('Y-m-d H:i:s');

	/*** avatar ***/
	DeleteAvatar ($iUserID, FALSE);

	/*** videos ***/
	$query_videos = "UPDATE `fst_video` SET
			video_deleted='3',
			video_deletedate='" . $sDTNow . "'
		WHERE (user_id='" . $iUserID . "')";
	Query ($query_videos);

	/*** comments ***/
	$query_comments = "UPDATE `fst_comment` SET
			comment_hidden='1'
		WHERE (user_id='" . $iUserID . "')";
	Query ($query_comments);

	/*** IP ***/
	$sIP = GetUserInfo ($iUserID, 'user_regip');
	BanIP ($sIP, FALSE);
	$sIP = GetUserInfo ($iUserID, 'user_avatarip');
	BanIP ($sIP, FALSE);
	$query_vip = "SELECT
			video_ip
		FROM `fst_video`
		WHERE (user_id='" . $iUserID . "')
		AND (video_ip<>'')
		GROUP BY video_ip";
	$result_vip = Query ($query_vip);
	while ($row_vip = mysqli_fetch_assoc ($result_vip))
		{ BanIP ($row_vip['video_ip'], FALSE); }
	$query_cip = "SELECT
			comment_ip
		FROM `fst_comment`
		WHERE (user_id='" . $iUserID . "')
		AND (comment_ip<>'')
		GROUP BY comment_ip";
	$result_cip = Query ($query_cip);
	while ($row_cip = mysqli_fetch_assoc ($result_cip))
		{ BanIP ($row_cip['comment_ip'], FALSE); }

	/*** user ***/
	$query_user = "UPDATE `fst_user` SET
			user_deleted='1'
		WHERE (user_id='" . $iUserID . "')";
	Query ($query_user);
}
/*****************************************************************************/

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if ((isset ($_POST['number'])) &&
		(isset ($_POST['action'])))
	{
		$iNumber = intval ($_POST['number']);
		$iAction = intval ($_POST['action']);

		if (IsMod())
		{
			$query_report = "SELECT
					report_type,
					issue_id,
					video_id,
					comment_id,
					user_id,
					message,
					report_email,
					report_ip,
					report_action
				FROM `fst_report`
				WHERE (report_id='" . $iNumber . "')";
			$result_report = Query ($query_report);
			$row_report = mysqli_fetch_assoc ($result_report);
			if ($row_report['report_action'] == '0')
			{
				switch ($iAction)
				{
					case 1: /*** Nothing. ***/
						break;
					case 2: /*** Email report_email a warning. ***/
						SendEmail ($row_report['report_email'], array(),
							'[ ' . $GLOBALS['name'] . ' ] Warning',
							'According to a moderator, someone with IP "' . Sanitize ($row_report['report_ip']) . '" misused the report functionality of ' . $GLOBALS['name'] . ' (' . $GLOBALS['domain'] . ').' . '<br>' . 'They entered "' . Sanitize ($row_report['report_email']) . '" as their email address.');
						break;
					case 3: /*** Ban report_ip. ***/
						BanIP ($row_report['report_ip'], FALSE);
						break;
					case 4: /*** Hide content/comment + request admin input. ***/
						HideVideoOrComment ($row_report, FALSE);
						/***/
						SendEmail ($GLOBALS['mail_admins'], array(),
							'[ ' . $GLOBALS['name'] . ' ] Request',
							'Admin input requested.');
						break;
					case 5: /*** Hide content/comment. ***/
						HideVideoOrComment ($row_report, FALSE);
						break;
					case 6: /*** Hide content/comment + email user_id warning. ***/
						$sType = HideVideoOrComment ($row_report, TRUE);
						/***/
						$sUserEmail = GetUserInfo ($row_report['user_id'], 'user_email');
						SendEmail ($sUserEmail, array(),
							'[ ' . $GLOBALS['name'] . ' ] Warning',
							'A moderator has removed one of your ' . $sType . 's.' . '<br>' . 'Please reread the <a href="' . $GLOBALS['protocol'] . '://www.' . $GLOBALS['domain'] . '/terms/">Terms of service</a>.' . '<br>' . 'Users who add content that is disallowed may get banned.');
						break;
					case 7: /*** Delete user_id avatar. ***/
						DeleteAvatar ($row_report['user_id'], FALSE);
						break;
					case 8: /*** Delete user_id avatar + email user_id warning. ***/
						DeleteAvatar ($row_report['user_id'], TRUE);
						/***/
						$sUserEmail = GetUserInfo ($row_report['user_id'], 'user_email');
						SendEmail ($sUserEmail, array(),
							'[ ' . $GLOBALS['name'] . ' ] Warning',
							'A moderator has removed your avatar and profile text.' . '<br>' . 'Please reread the <a href="' . $GLOBALS['protocol'] . '://www.' . $GLOBALS['domain'] . '/terms/">Terms of service</a>.' . '<br>' . 'Users who add content that is disallowed may get banned.');
						break;
					case 9: /*** Ban user_id + delete all their content/comments. ***/
						BanUser ($row_report['user_id']);
						break;
					case 10: /*** Email this feedback to the admin(s). ***/
						SendEmail ($GLOBALS['mail_admins'], array(),
							'[ ' . $GLOBALS['name'] . ' ] Feedback',
							'Feedback from someone who entered email address "' .
							Sanitize ($row_report['report_email']) . '":' . '<br>' .
							nl2br (Sanitize ($row_report['message'])));
						break;
					case 11: /*** Remove custom thumbnail ***/
						RemoveCustomThumbnail ($row_report['video_id']);
						CreateMessage (-1, $row_report['user_id'], 'A moderator has' .
							' removed one of your custom thumbnails.');
						break;
					case 12: /*** Remove all custom thumbnails + revoke privilege ***/
						$query_videoids = "SELECT
								video_id
							FROM `fst_video`
							WHERE (user_id='" . $row_report['user_id'] . "')";
						$result_videoids = Query ($query_videoids);
						while ($row_videoids = mysqli_fetch_assoc ($result_videoids))
							{ RemoveCustomThumbnail ($row_videoids['video_id']); }
						/***/
						$query_priv = "UPDATE `fst_user` SET
							user_priv_customthumbnails='0'
							WHERE (user_id='" . $row_report['user_id'] . "')";
						Query ($query_priv);
						/***/
						CreateMessage (-1, $row_report['user_id'], 'A moderator has' .
							' revoked your privilege to use custom thumbnails.');
						break;
				}

				$sDTNow = date ('Y-m-d H:i:s');

				/*** update queue (current entry) ***/
				$query_update = "UPDATE `fst_report` SET
						report_action='" . $iAction . "',
						report_action_dt='" . $sDTNow . "',
						report_action_user_id='" . $_SESSION['fst']['user_id'] . "'
					WHERE (report_id='" . $iNumber . "')";
				Query ($query_update);

				/*** update queue (other entries) ***/
				switch ($iAction)
				{
					case 3:
						$query_update = "UPDATE `fst_report` SET
								report_action='3',
								report_action_dt='" . $sDTNow . "',
								report_action_user_id='" . $_SESSION['fst']['user_id'] . "'
							WHERE (report_ip='" . $row_report['report_ip'] . "')
							AND (report_action='0')";
						Query ($query_update);
						break;
					case 9:
						$query_update = "UPDATE `fst_report` SET
								report_action='9',
								report_action_dt='" . $sDTNow . "',
								report_action_user_id='" . $_SESSION['fst']['user_id'] . "'
							WHERE (user_id='" . $row_report['user_id'] . "')
							AND (report_action='0')
							AND (report_type<>'4')";
						Query ($query_update);
						break;
					default:
						/* Process similar entries, ignoring who is the reporter.
						 * But SKIP entries with report type "feedback" or
						 * issue "Infringes my rights".
						 */
						$arIssue = IssueExists ('rights');
						$iIssueID = $arIssue['id'];
						/***/
						$query_update = "UPDATE `fst_report` SET
								report_action='" . $iAction . "',
								report_action_dt='" . $sDTNow . "',
								report_action_user_id='" . $_SESSION['fst']['user_id'] . "'
							WHERE (report_type='" . $row_report['report_type'] . "')
							AND (issue_id='" . $row_report['issue_id'] . "')
							AND (video_id='" . $row_report['video_id'] . "')
							AND (comment_id='" . $row_report['comment_id'] . "')
							AND (user_id='" . $row_report['user_id'] . "')
							AND (report_action='0')
							AND (report_type<>'4')
							AND (issue_id<>'" . $iIssueID . "')";
						Query ($query_update);
						break;
				}

				$arResult['result'] = 1;
				$arResult['error'] = '';
				$arResult['html'] = 'Thanks. Reloading page...';
			} else {
				$arResult['result'] = 1;
				$arResult['error'] = '';
				$arResult['html'] = 'Another mod has already processed this. Reloading page...';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Sign in as a mod.';
			$arResult['html'] = '';
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Data is missing.';
		$arResult['html'] = '';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
	$arResult['html'] = '';
}
print (json_encode ($arResult));
?>
