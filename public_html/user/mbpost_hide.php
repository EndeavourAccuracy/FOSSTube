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
	if (isset ($_POST['post_id']))
	{
		$iPostID = intval ($_POST['post_id']);

		if (isset ($_SESSION['fst']['user_id']))
		{
			$iUserID = intval ($_SESSION['fst']['user_id']);

			$query_post = "SELECT
					mbpost_id
				FROM `fst_microblog_post`
				WHERE (mbpost_id='" . $iPostID . "')
				AND (user_id='" . $iUserID . "')
				AND (mbpost_hidden='0')";
			$result_post = Query ($query_post);
			if (mysqli_num_rows ($result_post) == 1)
			{
				$query_hide = "UPDATE `fst_microblog_post` SET
						mbpost_hidden='1'
					WHERE (mbpost_id='" . $iPostID . "')
					AND (user_id='" . $iUserID . "')";
				$result_hide = Query ($query_hide);
				if (mysqli_affected_rows ($GLOBALS['link']) == 1)
				{
					$iReblogID = GetReblogID ($iPostID);
					if ($iReblogID != 0)
						{ UpdateCountReblogsMBPost ($iReblogID); }

					$arResult['result'] = 1;
					$arResult['error'] = '';
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Could not remove.';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'Unknown post or not your post.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'To remove posts, sign in.';
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Post is missing.';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
}
print (json_encode ($arResult));
?>
