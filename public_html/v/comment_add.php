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

include_once (dirname (__FILE__) . '/../fst_base.php');

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if ((isset ($_POST['code'])) &&
		(isset ($_POST['comment_text'])) &&
		(isset ($_POST['parent_id'])))
	{
		$sCode = $_POST['code'];
		$iVideoID = CodeToID ($sCode);
		$sText = $_POST['comment_text'];
		$iParentID = intval ($_POST['parent_id']);

		$iNotifyParent = 0;
		if ($iParentID != 0)
		{
			$arComment = CommentExists ($iParentID, $iVideoID);
			if ($arComment === FALSE)
			{
				$iParentID = 0;
			} else {
				$iNotifyParent = $arComment['user_id'];
			}
		}

		$query_video = "SELECT
				user_id
			FROM `fst_video`
			WHERE (video_id='" . $iVideoID . "')
			AND (video_comments_allow='1')";
		$result_video = Query ($query_video);
		if (mysqli_num_rows ($result_video) == 1)
		{
			$row_video = mysqli_fetch_assoc ($result_video);
			$iNotifyPublisher = intval ($row_video['user_id']);

			if (isset ($_SESSION['fst']['user_id']))
			{
				$iUserID = intval ($_SESSION['fst']['user_id']);

				/*** Do not notify the commenter. ***/
				if ($iNotifyPublisher == $iUserID)
					{ $iNotifyPublisher = 0; }
				if ($iNotifyParent == $iUserID)
					{ $iNotifyParent = 0; }

				if (FewerNotif ($iUserID, $iNotifyParent) === TRUE)
					{ $iNotifyParent = 0; }

				if (IsMuted ($iNotifyPublisher, $iUserID) === FALSE)
				{
					if ((strlen ($sText) >= 1) && (strlen ($sText) <= 4000))
					{
						$sDTNow = date ('Y-m-d H:i:s');
						$sIP = GetIP();

						$query_add = "INSERT INTO `fst_comment` SET
							video_id='" . $iVideoID . "',
							user_id='" . $iUserID . "',
							comment_text='" . mysqli_real_escape_string
								($GLOBALS['link'], $sText) . "',
							comment_pinned='0',
							comment_hidden='0',
							comment_approved='0',
							comment_ip='" . $sIP . "',
							comment_adddate='" . $sDTNow . "',
							comment_notify_publisher='" . $iNotifyPublisher . "',
							comment_parent_id='" . $iParentID . "',
							comment_notify_parent='" . $iNotifyParent . "'";
						$result_add = Query ($query_add);
						if (mysqli_affected_rows ($GLOBALS['link']) == 1)
						{
							UpdateCountCommentsVideo ($iVideoID);

							$arResult['result'] = 1;
							$arResult['error'] = '';
						} else {
							$arResult['result'] = 0;
							$arResult['error'] = 'Could not save comment.';
						}
					} else {
						$arResult['result'] = 0;
						$arResult['error'] = 'A comment must be 1-4000 characters' .
							' (UTF-16 code units). Currently: ' . strlen ($sText);
					}
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'This publisher has muted you.';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'To comment, <a href="/signin/">sign in</a>.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Video unknown or comments disabled.';
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
