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
function ToggleMute ($iOwnerID, $iAuthorID)
/*****************************************************************************/
{
	if (IsMuted ($iOwnerID, $iAuthorID) === FALSE)
	{
		$sDTNow = date ('Y-m-d H:i:s');

		$query_mute = "INSERT INTO `fst_mute` SET
			mute_user_publisher='" . $iOwnerID . "',
			mute_user_commenter='" . $iAuthorID . "',
			mute_dt='" . $sDTNow . "'";
		$result_mute = Query ($query_mute);
		if (mysqli_affected_rows ($GLOBALS['link']) == 1)
		{
			$arResult['result'] = 1;
			$arResult['error'] = '';
			$arResult['state'] = 'on';
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Could not toggle.';
			$arResult['state'] = '';
		}
	} else {
		$query_unmute = "DELETE FROM `fst_mute`
			WHERE (mute_user_publisher='" . $iOwnerID . "')
			AND (mute_user_commenter='" . $iAuthorID . "')";
		$result_unmute = Query ($query_unmute);
		if (mysqli_affected_rows ($GLOBALS['link']) == 1)
		{
			$arResult['result'] = 1;
			$arResult['error'] = '';
			$arResult['state'] = 'off';
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Could not toggle.';
			$arResult['state'] = '';
		}
	}

	return ($arResult);
}
/*****************************************************************************/

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if ((isset ($_POST['toggle'])) &&
		(isset ($_POST['comment_id'])))
	{
		$sToggle = $_POST['toggle'];
		$iCommentID = intval ($_POST['comment_id']);

		if (($sToggle == 'loved') || ($sToggle == 'pinned') ||
			($sToggle == 'hidden') || ($sToggle == 'approved') ||
			($sToggle == 'muted'))
		{
			$query_video = "SELECT
					fc.video_id,
					fc.user_id AS author,
					fv.user_id AS owner
				FROM `fst_comment` fc
				LEFT JOIN `fst_video` fv
					ON fc.video_id = fv.video_id
				WHERE (comment_id='" . $iCommentID . "')";
			$result_video = Query ($query_video);
			if (mysqli_num_rows ($result_video) == 1)
			{
				$row_video = mysqli_fetch_assoc ($result_video);
				$iAuthorID = $row_video['author'];
				$iOwnerID = $row_video['owner'];
				$iVideoID = $row_video['video_id'];

				if (isset ($_SESSION['fst']['user_id']))
				{
					$iUserID = intval ($_SESSION['fst']['user_id']);

					if (($iUserID == $iOwnerID) ||
						(($sToggle == 'hidden') && ($iUserID == $iAuthorID)))
					{
						if ($sToggle != 'muted')
						{
							$query_old = "SELECT
									comment_" . $sToggle . " AS old
								FROM `fst_comment`
								WHERE (comment_id='" . $iCommentID . "')";
							$result_old = Query ($query_old);
							$row_old = mysqli_fetch_assoc ($result_old);
							$iOld = $row_old['old'];
							if ($iOld == 0)
							{
								$iNew = 1; $sNew = 'on';
							} else {
								$iNew = 0; $sNew = 'off';
							}

							$query_add = "UPDATE `fst_comment` SET
								comment_" . $sToggle . "='" . $iNew . "'
								WHERE (comment_id='" . $iCommentID . "')";
							$result_add = Query ($query_add);
							if (mysqli_affected_rows ($GLOBALS['link']) == 1)
							{
								$arResult['result'] = 1;
								$arResult['error'] = '';
								$arResult['state'] = $sNew;
							} else {
								$arResult['result'] = 0;
								$arResult['error'] = 'Could not toggle.';
								$arResult['state'] = '';
							}
						} else {
							$arResult = ToggleMute ($iOwnerID, $iAuthorID);
						}
					} else {
						$arResult['result'] = 0;
						$arResult['error'] = 'Not your content.';
						$arResult['state'] = '';
					}
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'To tweak comments, sign in.';
					$arResult['state'] = '';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'Comment unknown.';
				$arResult['state'] = '';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Unknown action.';
			$arResult['state'] = '';
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Data is missing.';
		$arResult['state'] = '';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
	$arResult['state'] = '';
}
print (json_encode ($arResult));
?>
