<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.4 (December 2021)
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

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if (isset ($_POST['user_id_microblog']))
	{
		$iUserIDMicroBlog = intval ($_POST['user_id_microblog']);

		if (isset ($_SESSION['fst']['user_id']))
		{
			$iUserIDFollower = intval ($_SESSION['fst']['user_id']);

			$query_following = "SELECT
					follow_id
				FROM `fst_follow`
				WHERE (user_id_microblog='" . $iUserIDMicroBlog . "')
				AND (user_id_follower='" . $iUserIDFollower . "')";
			$result_following = Query ($query_following);
			if (mysqli_num_rows ($result_following) == 1)
			{
				/*** UNFOLLOW ***/
				$row_following = mysqli_fetch_assoc ($result_following);
				$iFollowID = intval ($row_following['follow_id']);
				$query_unfollow = "DELETE FROM `fst_follow`
					WHERE (follow_id='" . $iFollowID . "')
					AND (user_id_follower='" . $iUserIDFollower . "')";
				$result_unfollow = Query ($query_unfollow);
				if (mysqli_affected_rows ($GLOBALS['link']) == 1)
				{
					$arResult['result'] = 1;
					$arResult['error'] = '';
					$arResult['html'] = '/images/follow.png';
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Nothing changed.';
					$arResult['html'] = '';
				}
			} else {
				/*** FOLLOW ***/
				$sDTNow = date ('Y-m-d H:i:s');

				$query_follow = "INSERT INTO `fst_follow` SET
						user_id_microblog='" . $iUserIDMicroBlog . "',
						user_id_follower='" . $iUserIDFollower . "',
						follow_adddate='" . $sDTNow . "'";
				$result_follow = Query ($query_follow);
				if (mysqli_affected_rows ($GLOBALS['link']) == 1)
				{
					$arResult['result'] = 1;
					$arResult['error'] = '';
					$arResult['html'] = '/images/following.png';
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Nothing changed.';
					$arResult['html'] = '';
				}
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Sign in to (un)follow.';
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
