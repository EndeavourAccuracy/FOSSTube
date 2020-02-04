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

include_once (dirname (__FILE__) . '/../fst_base.php');

/*****************************************************************************/
function Messages ($iUserID)
/*****************************************************************************/
{
	$arNewMessages = NewMessages ($iUserID);
	if (count ($arNewMessages) > 0)
	{
		print ('<div style="margin-top:10px;">');
		foreach ($arNewMessages as $iMessage)
		{
			print ('<input type="hidden" name="message[' .
				$iMessage . ']" value="' . $iMessage . '">');

			$query_message = "SELECT
					fm.user_id_sender,
					fu.user_username AS sender,
					fm.message_text,
					fm.video_id,
					fv.video_title,
					fm.message_adddate
				FROM `fst_message` fm
				LEFT JOIN `fst_user` fu
					ON fm.user_id_sender = fu.user_id
				LEFT JOIN `fst_video` fv
					ON fm.video_id = fv.video_id
				WHERE (message_id='" . $iMessage . "')";
			$result_message = Query ($query_message);
			while ($row_message = mysqli_fetch_assoc ($result_message))
			{
				$iSender = $row_message['user_id_sender'];
				$sSender = $row_message['sender'];
				$sMessage = $row_message['message_text'];
				$iVideoID = $row_message['video_id'];
				$sCode = IDToCode ($iVideoID);
				$sVideoTitle = $row_message['video_title'];
				$sMessageDT = $row_message['message_adddate'];
				$sMessageDate = date ('j F Y', strtotime ($sMessageDT));

				if ($sMessage == '') /*** new video ***/
				{
					print ($sMessageDate . ' - User "' . $sSender .
						'" has published <a href="/v/' . $sCode . '">' .
						Sanitize ($sVideoTitle) . '</a>.');
					print ('<br>');
				} else { /*** message ***/
					print ('<span class="message">');
					switch ($iSender)
					{
						case -1: /*** system ***/
							print ('<p style="font-style:italic;">' . $sMessageDate .
								'</p>');
							break;
						case 0: /*** "an administrator" ***/
							print ('<p style="font-style:italic;">' . $sMessageDate .
								' - Message from an administrator.</p>');
							break;
						default: /*** user ID ***/
							print ('<p style="font-style:italic;">' . $sMessageDate .
								' - Message from "' . Sanitize ($sSender) . '".</p>');
							break;
					}
					print (nl2br (Sanitize ($sMessage)));
					print ('</span>');
				}
			}
		}
		print ('</div>');
	}
}
/*****************************************************************************/
function CommentsReplies ($sColumn, $iUserID, $sWhat)
/*****************************************************************************/
{
	$arNewComments = NewComments ($sColumn, $iUserID);
	if (count ($arNewComments) > 0)
	{
		foreach ($arNewComments as $iComment)
		{
			print ('<input type="hidden" name="' . $sWhat . '[' .
				$iComment . ']" value="' . $iComment . '">');
		}

		$arNewCommentsGrouped = NewCommentsGrouped ($sColumn, $iUserID);
		print ('<div style="margin-top:10px;">');
		foreach ($arNewCommentsGrouped as $arNewComments)
		{
			if ($sWhat == 'comment')
			{
				if ($arNewComments[2] == 1)
					{ $sThing = 'comment'; }
						else { $sThing = 'comments'; }
			} else {
				if ($arNewComments[2] == 1)
					{ $sThing = 'reply to you'; }
						else { $sThing = 'replies to you'; }
			}

			print ('<a href="/v/' . $arNewComments[0] . '">' .
				Sanitize ($arNewComments[1]) .
				'</a> has ' . $arNewComments[2] . ' new ' . $sThing);
			print ('.<br>');
		}
		print ('</div>');
	}
}
/*****************************************************************************/

if (!isset ($_SESSION['fst']['user_id']))
{
	HTMLStart ('Notifications', 'Notifications', 'Notifications', 0, FALSE);
	print ('<h1>Notifications</h1>');
	print ('You are not logged in.' . '<br>');
	print ('To check notifications, first <a href="/signin/">sign in</a>.');
} else {
	$iUserID = intval ($_SESSION['fst']['user_id']);

	if (strtoupper ($_SERVER['REQUEST_METHOD']) === 'POST')
	{
		if (isset ($_POST['comment']))
		{
			foreach ($_POST['comment'] as $sComment)
			{
				$iCommentID = intval ($sComment);
				$query_update = "UPDATE `fst_comment` SET
						comment_notify_publisher='0'
					WHERE (comment_id='" . $iCommentID . "')
					AND (comment_notify_publisher='" . $iUserID . "')";
				Query ($query_update);
			}
		}
		if (isset ($_POST['reply']))
		{
			foreach ($_POST['reply'] as $sComment)
			{
				$iCommentID = intval ($sComment);
				$query_update = "UPDATE `fst_comment` SET
						comment_notify_parent='0'
					WHERE (comment_id='" . $iCommentID . "')
					AND (comment_notify_parent='" . $iUserID . "')";
				Query ($query_update);
			}
		}
		if (isset ($_POST['message']))
		{
			foreach ($_POST['message'] as $sMessage)
			{
				$iMessageID = intval ($sMessage);
				$query_delete = "UPDATE `fst_message` SET
					message_cleared='1'
					WHERE (message_id='" . $iMessageID . "')
					AND (user_id_recipient='" . $iUserID . "')";
				Query ($query_delete);
			}
		}
		header ('Location: /notifications/');
	} else {
		HTMLStart ('Notifications', 'Notifications', 'Notifications', 0, FALSE);
		print ('<h1>Notifications</h1>');
		$iNrNotifications = NrNotifications();
		if ($iNrNotifications == 0)
		{
			print ('No notifications.');
		} else {
			print ('<form action="/notifications/" method="POST">');
			print ('<input type="submit" value="Clear notifications">');

			/*** MESSAGES ***/
			Messages ($iUserID);

			/*** COMMENTS ***/
			CommentsReplies ('comment_notify_publisher', $iUserID, 'comment');

			/*** REPLIES ***/
			CommentsReplies ('comment_notify_parent', $iUserID, 'reply');

			print ('</form>');
		}
	}
}
HTMLEnd();
?>
