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

include_once (dirname (__FILE__) . '/../fst_base.php');

/*****************************************************************************/
function ShowInfo ()
/*****************************************************************************/
{
	print ('<div class="div-report" style="text-align:center;">');
	print ('<h2>Guidelines</h2>' . "\n");
	include_once (dirname (__FILE__) . '/guidelines.html');
	print ('</div>');
}
/*****************************************************************************/
function ContentName ($iIsText)
/*****************************************************************************/
{
	switch ($iIsText)
	{
		case 0: $sContent = 'video'; break;
		case 1: $sContent = 'text'; break;
		case 2: $sContent = 'text'; break;
		case 3: $sContent = 'topic'; break;
	}

	return ($sContent);
}
/*****************************************************************************/
function ShowDate ($sDT)
/*****************************************************************************/
{
	$sDate = date ('j F Y (H:i)', strtotime ($sDT));

print ('
<span style="display:block;">
Reported: ' . $sDate . '
</span>
');
}
/*****************************************************************************/
function ShowReporter ($sReportIP, $sEmail)
/*****************************************************************************/
{
	$sText = 'Reporter (MD5 IP): ...' . substr (md5 ($sReportIP), -4);
	if (IsAdmin()) { $sText .= ' / ' . Sanitize ($sEmail); }

print ('
<span style="display:block;">
' . $sText . '
</span>
');
}
/*****************************************************************************/
function ShowVideo ($sCode)
/*****************************************************************************/
{
	$arVideo = VideoExists ($sCode);

	if ($arVideo !== FALSE)
	{
		$sTitle = $arVideo['title'];
		$sThumbURL = ThumbURL ($sCode, '180', $arVideo['thumbnail'], TRUE);
	} else {
		$sTitle = '(deleted)';
		$sThumbURL = '/images/thumbnail_180.jpg';
	}

print ('
<span style="display:block; margin-bottom:10px; padding:5px; background-color:#ddd;">
<h2>' . Sanitize ($sTitle) . '</h2>

<span style="display:inline-block; border:1px solid #000; max-width:100%;">
<video style="display:block; max-width:100%;" autoplay loop>
<source src="' . VideoURL ($sCode, 'preview') . '" type="video/mp4">
Your browser or OS does not support HTML5 MP4 video with H.264.
</video>
</span>

<span style="display:inline-block; vertical-align:top; border:1px solid #000; max-width:100%;">
<img src="' . $sThumbURL . '" alt="preview" style="max-width:100%;">
</span>

</span>
<a target="_blank" href="/v/' . $sCode . '">open video in tab</a>
');
}
/*****************************************************************************/
function ShowText ($sCode, $iVideoID, $iIsText)
/*****************************************************************************/
{
	if (($iIsText == 1) || ($iIsText == 2)) /*** text ***/
	{
		$query_text = "SELECT
				video_text
			FROM `fst_video`
			WHERE (video_id='" . $iVideoID . "')";
	} else { /*** 3 (forum text) ***/
		$query_text = "SELECT
				video_description
			FROM `fst_video`
			WHERE (video_id='" . $iVideoID . "')";
	}
	$result_text = Query ($query_text);
	$row_text = mysqli_fetch_assoc ($result_text);
	if (($iIsText == 1) || ($iIsText == 2)) /*** text ***/
	{
		$sText = BBCodeToHTML ($row_text['video_text']);
	} else { /*** 3 (forum text) ***/
		$sText = nl2br (Sanitize ($row_text['video_description']));
	}

print ('
<span style="display:block; margin-bottom:10px; padding:5px; background-color:#ddd; max-height:200px; overflow-y:auto;">
' . $sText . '
</span>
<a target="_blank" href="/v/' . $sCode . '">open content in tab</a>
');
}
/*****************************************************************************/
function ShowComment ($iCommentID)
/*****************************************************************************/
{
	$query_comment = "SELECT
			video_id,
			comment_text
		FROM `fst_comment`
		WHERE (comment_id='" . $iCommentID . "')";
	$result_comment = Query ($query_comment);
	$row_comment = mysqli_fetch_assoc ($result_comment);
	$iVideoID = $row_comment['video_id'];
	$sCode = IDToCode ($iVideoID);
	$sComment = $row_comment['comment_text'];

print ('
<span style="display:block; margin-bottom:10px; padding:5px; background-color:#ddd; max-height:200px; overflow-y:auto;">
' . nl2br (Sanitize ($sComment)) . '
</span>
<a target="_blank" href="/v/' . $sCode . '/' . $iCommentID . '#comment-' . $iCommentID . '">open comment in tab</a>
');
}
/*****************************************************************************/
function ShowMBPost ($iPostID)
/*****************************************************************************/
{
	$query_post = "SELECT
			mbpost_text
		FROM `fst_microblog_post`
		WHERE (mbpost_id='" . $iPostID . "')";
	$result_post = Query ($query_post);
	$row_post = mysqli_fetch_assoc ($result_post);
	$sPost = $row_post['mbpost_text'];

print ('
<span style="display:block; margin-bottom:10px; padding:5px; background-color:#ddd;">
' . nl2br (Sanitize ($sPost)) . '
</span>
');
}
/*****************************************************************************/
function ShowUser ($iUserID)
/*****************************************************************************/
{
	$query_user = "SELECT
			user_username
		FROM `fst_user`
		WHERE (user_id='" . $iUserID . "')";
	$result_user = Query ($query_user);
	$row_user = mysqli_fetch_assoc ($result_user);
	$sUsername = $row_user['user_username'];

print ('
<span style="display:block; margin-bottom:10px;">
User "' . $sUsername . '" may have a problematic avatar, profile text or video(s)/text(s).
<br>
If necessary, you may manually report a video or text yourself.
</span>
<a target="_blank" href="/user/' . $sUsername . '">open user in tab</a>
');
}
/*****************************************************************************/
function ShowFeedback ($sMessage)
/*****************************************************************************/
{
print ('
<span class="span-report">
' . nl2br (Sanitize ($sMessage)) . '
</span>
');
}
/*****************************************************************************/
function ShowIssue ($iIssue)
/*****************************************************************************/
{
	$query_issue = "SELECT
			issue_name_long,
			issue_name_short
		FROM `fst_issue`
		WHERE (issue_id='" . $iIssue . "')";
	$result_issue = Query ($query_issue);
	if (mysqli_num_rows ($result_issue) == 1)
	{
		$row_issue = mysqli_fetch_assoc ($result_issue);

print ('
<span style="display:block; margin-bottom:10px;">
Complaint: <span style="color:#f00;">' . $row_issue['issue_name_long'] . '</span>
</span>
');

		switch ($row_issue['issue_name_short'])
		{
			case 'rights':
print ('
<span style="display:block; margin-bottom:10px; color:#00f; font-size:16px;">
Are you unsure whether some or all of this constitutes copyright infringement?
<br>
Then feel free to select the action that hides the content and requests admin input.
</span>
');
				break;
			case 'hate':
print ('
<span style="display:block; margin-bottom:10px; color:#00f; font-size:16px;">
Generally, content that is framed as commentary (or is comedy) is acceptable.
</span>
');
				break;
		}
	}
}
/*****************************************************************************/
function ShowWarnings ($iUserID)
/*****************************************************************************/
{
	$query_warn = "SELECT
			user_username,
			user_warnings_video,
			user_warnings_comment,
			user_warnings_avatar,
			user_warnings_mbpost
		FROM `fst_user`
		WHERE (user_id='" . $iUserID . "')";
	$result_warn = Query ($query_warn);
	$row_warn = mysqli_fetch_assoc ($result_warn);
	$sUsername = $row_warn['user_username'];
	$iWarnVideo = $row_warn['user_warnings_video'];
	$iWarnComment = $row_warn['user_warnings_comment'];
	$iWarnAvatar = $row_warn['user_warnings_avatar'];
	$iWarnMBPost = $row_warn['user_warnings_mbpost'];

print ('
<span style="display:block; margin-bottom:10px;">
Past warnings user "' . $sUsername . '": ' . WarningCount ($iWarnVideo) . ' video(s)/text(s), ' . WarningCount ($iWarnComment) . ' comment(s), ' . WarningCount ($iWarnAvatar) . ' avatar(s), ' . WarningCount ($iWarnMBPost) . ' post(s)
</span>
');
}
/*****************************************************************************/
function ActionText ($iReportType, $iAction, $iIsText)
/*****************************************************************************/
{
	$sText = 'Unknown action';

	if ($iReportType == 1) /*** video, text, topic ***/
	{
		$sContent = ContentName ($iIsText);
		switch ($iAction)
		{
			case 1: $sText =
				'No action is necessary'; break;
			case 2: $sText =
				'No action is necessary + email REPORTER a warning'; break;
			case 3: $sText =
				'No action is necessary + ban this REPORTER'; break;
			case 4: $sText =
				'Hide ' . $sContent . ' + request admin input'; break;
			case 5: $sText =
				'Delete ' . $sContent; break;
			case 6: $sText =
				'Delete ' . $sContent . ' + email USER a warning'; break;
			case 9: $sText =
				'Ban this USER + delete all their content/comments/posts'; break;
			case 11: $sText =
				'Remove custom thumbnail'; break;
			case 12: $sText =
				'Remove all custom thumbnails + revoke privilege'; break;
		}
	}

	if ($iReportType == 2) /*** comment ***/
	{
		switch ($iAction)
		{
			case 1: $sText =
				'No action is necessary'; break;
			case 2: $sText =
				'No action is necessary + email REPORTER a warning'; break;
			case 3: $sText =
				'No action is necessary + ban this REPORTER'; break;
			case 4: $sText =
				'Hide comment + request admin input'; break;
			case 5: $sText =
				'Delete comment'; break;
			case 6: $sText =
				'Delete comment + email USER a warning'; break;
			case 9: $sText =
				'Ban this USER + delete all their content/comments/posts'; break;
		}
	}

	if ($iReportType == 3) /*** user ***/
	{
		switch ($iAction)
		{
			case 1: $sText =
				'No action is necessary'; break;
			case 2: $sText =
				'No action is necessary + email REPORTER a warning'; break;
			case 3: $sText =
				'No action is necessary + ban this REPORTER'; break;
			case 7: $sText =
				'Delete USER avatar and profile text'; break;
			case 8: $sText =
				'Delete USER avatar and profile text + email USER a warning'; break;
			case 9: $sText =
				'Ban this USER + delete all their content/comments/posts'; break;
		}
	}

	if ($iReportType == 4) /*** feedback ***/
	{
		switch ($iAction)
		{
			case 1: $sText =
				'No action is necessary'; break;
			case 2: $sText =
				'No action is necessary + email SENDER a warning'; break;
			case 3: $sText =
				'No action is necessary + ban this SENDER'; break;
			case 10: $sText =
				'Email this feedback to the admin(s).'; break;
		}
	}

	if ($iReportType == 5) /*** mbpost ***/
	{
		switch ($iAction)
		{
			case 1: $sText =
				'No action is necessary'; break;
			case 2: $sText =
				'No action is necessary + email REPORTER a warning'; break;
			case 3: $sText =
				'No action is necessary + ban this REPORTER'; break;
			case 4: $sText =
				'Hide post + request admin input'; break;
			case 5: $sText =
				'Delete post'; break;
			case 6: $sText =
				'Delete post + email USER a warning'; break;
			case 9: $sText =
				'Ban this USER + delete all their content/comments/posts'; break;
		}
	}

	return ($sText);
}
/*****************************************************************************/
function ShowActions ($iReportID, $iReportType, $iIsText, $iVideoID)
/*****************************************************************************/
{
	print ('<select name="action-' . $iReportID . '" style="display:block; width:100%; border:1px solid #000; padding:5px;">');
	print ('<option value="">Select action...</option>');

if ($iReportType == 1) /*** video, text, topic ***/
{
print ('
<option value="1">' . ActionText ($iReportType, 1, $iIsText) . '</option>
<option value="2">' . ActionText ($iReportType, 2, $iIsText) . '</option>
<option value="3">' . ActionText ($iReportType, 3, $iIsText) . '</option>
<option value="4">' . ActionText ($iReportType, 4, $iIsText) . '</option>
<option value="5">' . ActionText ($iReportType, 5, $iIsText) . '</option>
<option value="6">' . ActionText ($iReportType, 6, $iIsText) . '</option>
<option value="9">' . ActionText ($iReportType, 9, $iIsText) . '</option>
');

	$iThumb = VideoThumb ($iVideoID);
	if ($iThumb == 6)
	{
print ('
<option value="11">' . ActionText ($iReportType, 11, $iIsText) . '</option>
<option value="12">' . ActionText ($iReportType, 12, $iIsText) . '</option>
');
	}
}

if ($iReportType == 2) /*** comment ***/
{
print ('
<option value="1">' . ActionText ($iReportType, 1, $iIsText) . '</option>
<option value="2">' . ActionText ($iReportType, 2, $iIsText) . '</option>
<option value="3">' . ActionText ($iReportType, 3, $iIsText) . '</option>
<option value="4">' . ActionText ($iReportType, 4, $iIsText) . '</option>
<option value="5">' . ActionText ($iReportType, 5, $iIsText) . '</option>
<option value="6">' . ActionText ($iReportType, 6, $iIsText) . '</option>
<option value="9">' . ActionText ($iReportType, 9, $iIsText) . '</option>
');
}

if ($iReportType == 3) /*** user ***/
{
print ('
<option value="1">' . ActionText ($iReportType, 1, $iIsText) . '</option>
<option value="2">' . ActionText ($iReportType, 2, $iIsText) . '</option>
<option value="3">' . ActionText ($iReportType, 3, $iIsText) . '</option>
<option value="7">' . ActionText ($iReportType, 7, $iIsText) . '</option>
<option value="8">' . ActionText ($iReportType, 8, $iIsText) . '</option>
<option value="9">' . ActionText ($iReportType, 9, $iIsText) . '</option>
');
}

if ($iReportType == 4) /*** feedback ***/
{
print ('
<option value="1">' . ActionText ($iReportType, 1, $iIsText) . '</option>
<option value="2">' . ActionText ($iReportType, 2, $iIsText) . '</option>
<option value="3">' . ActionText ($iReportType, 3, $iIsText) . '</option>
<option value="10">' . ActionText ($iReportType, 10, $iIsText) . '</option>
');
}

if ($iReportType == 5) /*** mbpost ***/
{
print ('
<option value="1">' . ActionText ($iReportType, 1, $iIsText) . '</option>
<option value="2">' . ActionText ($iReportType, 2, $iIsText) . '</option>
<option value="3">' . ActionText ($iReportType, 3, $iIsText) . '</option>
<option value="4">' . ActionText ($iReportType, 4, $iIsText) . '</option>
<option value="5">' . ActionText ($iReportType, 5, $iIsText) . '</option>
<option value="6">' . ActionText ($iReportType, 6, $iIsText) . '</option>
<option value="9">' . ActionText ($iReportType, 9, $iIsText) . '</option>
');
}

	print ('</select>');
	print ('<input name="button-' . $iReportID . '" type="button" value="Submit" style="border:1px solid #000; padding:5px;">');
}
/*****************************************************************************/
function ShowMod ($row_report, $iIsText)
/*****************************************************************************/
{
	$sDate = date ('j F Y (H:i)', strtotime ($row_report['report_action_dt']));
	$sUsername = GetUserInfo ($row_report['report_action_user_id'],
		'user_username');
	$sText = ActionText ($row_report['report_type'],
		$row_report['report_action'], $iIsText);

print ('
<span style="font-size:20px;">
On ' . $sDate . ', mod "' . $sUsername . '" picked: ' . $sText . '
</span>
');
}
/*****************************************************************************/
function ShowPreviouslyPickedActions ($row_report, $iIsText)
/*****************************************************************************/
{
	$query_previous = "SELECT
			report_action,
			GROUP_CONCAT(DATE_FORMAT(report_action_dt,'%e %M %Y') SEPARATOR ', ') AS report_action_dt,
			COUNT(*) AS count
		FROM `fst_report`
		WHERE (report_type='" . $row_report['report_type'] . "')
		AND (issue_id='" . $row_report['issue_id'] . "')
		AND (video_id='" . $row_report['video_id'] . "')
		AND (comment_id='" . $row_report['comment_id'] . "')
		AND (mbpost_id='" . $row_report['mbpost_id'] . "')
		AND (user_id='" . $row_report['user_id'] . "')
		AND (report_action<>'0')
		GROUP BY report_action";
	$result_previous = Query ($query_previous);
	if (mysqli_num_rows ($result_previous) != 0)
	{
print ('
<span style="display:block; margin-bottom:10px; color:#00f; font-size:16px;">
This received the same complaint in the past, and back then a moderator picked:
<br>
');
		while ($row_previous = mysqli_fetch_assoc ($result_previous))
		{
			$sAction = ActionText ($row_report['report_type'],
				$row_previous['report_action'], $iIsText);
			$sDate = $row_previous['report_action_dt'];
			$iCount = intval ($row_previous['count']);

			print ('- ' . $sAction . ' (' . $iCount . 'x: ' . $sDate . ')<br>');
		}
		print ('</span>');
	}
}
/*****************************************************************************/

HTMLStart ('Mod', 'Mod', 'Mod', 0, FALSE);
print ('<h1>Mod</h1>');
if (!IsMod())
{
	if (!isset ($_SESSION['fst']['user_id']))
	{
		print ('First, <a href="/signin/">sign in</a> as a mod.');
	} else {
		print ('First, <a href="/signout/">sign out</a>, then sign in as a mod.');
	}
} else {
print ('
<span style="display:block; margin-bottom:10px;">
<a href="javascript:window.location.reload();">Reload page</a>
</span>
');
	ShowInfo();
	if (IsAdmin() === FALSE)
		{ $sWhere = "WHERE (report_action='0')"; }
			else { $sWhere = ""; }
	$query_report = "SELECT
			report_id,
			report_type,
			issue_id,
			report_occursattime,
			video_id,
			comment_id,
			mbpost_id,
			user_id,
			message,
			report_email,
			report_ip,
			report_dt,
			report_action,
			report_action_dt,
			report_action_user_id
		FROM `fst_report`
		" . $sWhere . "
		ORDER BY report_action<>'0', report_dt DESC LIMIT 10";
	$result_report = Query ($query_report);
	if (mysqli_num_rows ($result_report) == 0)
	{
		print ('No pending reports.');
	} else {
		while ($row_report = mysqli_fetch_assoc ($result_report))
		{
			$iReportID = $row_report['report_id'];
			$iReportType = $row_report['report_type'];
			$sOccursAtTime = $row_report['report_occursattime'];
			$sReportEmail = $row_report['report_email'];
			$sReportIP = $row_report['report_ip'];
			$iReportAction = $row_report['report_action'];

			print ('<div id="report-' . $iReportID . '" class="div-report"');
			if ($iReportAction != 0)
				{ print (' style="background-color:#bfffbf;"'); }
			print ('>');
			$iIsText = -1;
			switch ($iReportType)
			{
				case 1: /*** video, text, topic ***/
					$sCode = IDToCode ($row_report['video_id']);
					$iIsText = IsText ($sCode);
					print ('<h2 title="#' . $iReportID . '">Report: ' .
						ContentName ($iIsText) . '</h2>');
					ShowDate ($row_report['report_dt']);
					ShowReporter ($sReportIP, $sReportEmail);
					ShowIssue ($row_report['issue_id']);
					/***/
					if ($iIsText == 0)
					{
						if ($sOccursAtTime != '')
						{
print ('
<span style="display:block; margin-bottom:10px; color:#00f; font-size:16px;">
Occurs at time: ' . Sanitize ($sOccursAtTime) . '
</span>
');
						}
					}
					/***/
					if ($iIsText == 0)
					{
						ShowVideo ($sCode);
					} else {
						ShowText ($sCode, $row_report['video_id'], $iIsText);
					}
					ShowWarnings ($row_report['user_id']);
					/***/
					$query_del = "SELECT
							video_deleted
						FROM `fst_video`
						WHERE (video_id='" . $row_report['video_id'] . "')";
					$result_del = Query ($query_del);
					$row_del = mysqli_fetch_assoc ($result_del);
					if ($row_del['video_deleted'] != 0)
					{
print ('
<span style="display:block; margin-bottom:10px; color:#00f; font-size:16px;">
This content has already been deleted.
<br>
If necessary, you may still "Delete content + email USER a warning".
</span>
');
					}
					/***/
					break;
				case 2: /*** comment ***/
					print ('<h2 title="#' . $iReportID . '">Report: comment</h2>');
					ShowDate ($row_report['report_dt']);
					ShowReporter ($sReportIP, $sReportEmail);
					ShowIssue ($row_report['issue_id']);
					ShowComment ($row_report['comment_id']);
					ShowWarnings ($row_report['user_id']);
					/***/
					$query_del = "SELECT
							fc.comment_hidden,
							fv.video_deleted
						FROM `fst_comment` fc
						LEFT JOIN `fst_video` fv
							ON fc.video_id = fv.video_id
						WHERE (comment_id='" . $row_report['comment_id'] . "')";
					$result_del = Query ($query_del);
					$row_del = mysqli_fetch_assoc ($result_del);
					if (($row_del['comment_hidden'] != 0) ||
						($row_del['video_deleted'] != 0))
					{
print ('
<span style="display:block; margin-bottom:10px; color:#00f; font-size:16px;">
This comment has already been deleted.
<br>
If necessary, you may still "Delete video + email USER a warning".
</span>
');
					}
					/***/
					break;
				case 3: /*** user ***/
					print ('<h2 title="#' . $iReportID . '">Report: user</h2>');
					ShowDate ($row_report['report_dt']);
					ShowReporter ($sReportIP, $sReportEmail);
					ShowIssue ($row_report['issue_id']);
					ShowUser ($row_report['user_id']);
					ShowWarnings ($row_report['user_id']);
					/***/
					$query_del = "SELECT
							user_deleted
						FROM `fst_user`
						WHERE (user_id='" . $row_report['user_id'] . "')";
					$result_del = Query ($query_del);
					$row_del = mysqli_fetch_assoc ($result_del);
					if ($row_del['user_deleted'] != 0)
					{
print ('
<span style="display:block; margin-bottom:10px; color:#00f; font-size:16px;">
This user has already been deleted.
</span>
');
					}
					/***/
					break;
				case 4: /*** feedback ***/
					print ('<h2 title="#' . $iReportID . '">General feedback</h2>');
					ShowDate ($row_report['report_dt']);
					ShowReporter ($sReportIP, $sReportEmail);
					ShowIssue ($row_report['issue_id']);
					ShowFeedback ($row_report['message']);
					break;
				case 5: /*** mbpost ***/
					print ('<h2 title="#' . $iReportID . '">Report: post</h2>');
					ShowDate ($row_report['report_dt']);
					ShowReporter ($sReportIP, $sReportEmail);
					ShowIssue ($row_report['issue_id']);
					ShowMBPost ($row_report['mbpost_id']);
					ShowWarnings ($row_report['user_id']);
					/***/
					$query_del = "SELECT
							mbpost_hidden
						FROM `fst_microblog_post`
						WHERE (mbpost_id='" . $row_report['mbpost_id'] . "')";
					$result_del = Query ($query_del);
					$row_del = mysqli_fetch_assoc ($result_del);
					if ($row_del['mbpost_hidden'] != 0)
					{
print ('
<span style="display:block; margin-bottom:10px; color:#00f; font-size:16px;">
This post has already been deleted.
<br>
If necessary, you may still "Delete post + email USER a warning".
</span>
');
					}
					/***/
					break;
			}
			if ($iReportAction == 0)
			{
				$arIssue = IssueExists ('rights');
				$iIssueID = $arIssue['id'];
				/***/
				if (($iReportType != 4) && ($row_report['issue_id'] != $iIssueID))
					{ ShowPreviouslyPickedActions ($row_report, $iIsText); }
				print ('<div id="report-error-' . $iReportID .
					'" style="color:#f00;"></div>');
				ShowActions ($iReportID, $iReportType,
					$iIsText, $row_report['video_id']);
			} else {
				ShowMod ($row_report, $iIsText);
			}
			print ('</div>');
		}

print ('
<script>
$("[name^=\"button-\"]").click(function(){
	number = $(this).attr("name");
	number = number.replace("button-","");
	action = $("[name=\"action-" + number + "\"]").val();
	if (action == "")
	{
		alert ("Select an action.");
		return false;
	}
	Action (number, action);
});

function Action (number, action)
{
	$.ajax({
		type: "POST",
		url: "/mod/process.php",
		data: ({
			number : number,
			action : action,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			var html = data["html"];
			if (result == 1)
			{
				$("#report-" + number).html(html);
				$("#report-" + number).css("background-color","#bfffbf");
				location.reload();
			} else {
				$("#report-error-" + number).html(error);
			}
		},
		error: function() {
			$("#report-error-" + number).html("Error calling process.php.");
		}
	});
}
</script>
');
	}
}
HTMLEnd();
?>
