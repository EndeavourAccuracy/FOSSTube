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

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if (isset ($_POST['post_id']))
	{
		$iPostID = intval ($_POST['post_id']);

		$query_author = "SELECT
				user_id
			FROM `fst_microblog_post`
			WHERE (mbpost_id='" . $iPostID . "')";
		$result_author = Query ($query_author);
		if (mysqli_num_rows ($result_author) == 1)
		{
			$row_author = mysqli_fetch_assoc ($result_author);
			$iAuthorID = $row_author['user_id'];

			if (isset ($_SESSION['fst']['user_id']))
			{
				$iUserID = intval ($_SESSION['fst']['user_id']);

				if ($iUserID != $iAuthorID)
				{
					$query_old = "SELECT
							likembpost_id
						FROM `fst_likembpost`
						WHERE (mbpost_id='" . $iPostID . "')
						AND (user_id='" . $iUserID . "')";
					$result_old = Query ($query_old);
					if (mysqli_num_rows ($result_old) != 1)
					{
						$sDTNow = date ('Y-m-d H:i:s');

						$query_add = "INSERT INTO `fst_likembpost` SET
							mbpost_id='" . $iPostID . "',
							user_id='" . $iUserID . "',
							likembpost_adddate='" . $sDTNow . "'";
						$result_add = Query ($query_add);
						if (mysqli_affected_rows ($GLOBALS['link']) == 1)
						{
							UpdateCountLikesMBPost ($iPostID);

							$arResult['result'] = 1;
							$arResult['error'] = '';
							$arResult['likes'] = LikesMBPost ($iPostID);
						} else {
							$arResult['result'] = 0;
							$arResult['error'] = 'Could not save like.';
							$arResult['likes'] = 0;
						}
					} else {
						$arResult['result'] = 0;
						$arResult['error'] = 'Already liked by you.';
						$arResult['likes'] = 0;
					}
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Cannot like own post.';
					$arResult['likes'] = 0;
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'To like, sign in.';
				$arResult['likes'] = 0;
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Post unknown.';
			$arResult['likes'] = 0;
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Post ID missing.';
		$arResult['likes'] = 0;
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
	$arResult['likes'] = 0;
}
print (json_encode ($arResult));
?>
