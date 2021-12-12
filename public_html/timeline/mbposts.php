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

if ((isset ($_POST['user'])) &&
	(isset ($_POST['mboffset'])))
{
	$iUserID = intval ($_POST['user']);
	$iOffset = intval ($_POST['mboffset']);

	/*** $iVisitorID ***/
	if (isset ($_SESSION['fst']['user_id']))
		{ $iVisitorID = $_SESSION['fst']['user_id']; }
			else { $iVisitorID = 0; }

	/*** $arUsers, $bTimeline, $iLimit ***/
	$arUsers = array();
	if ($iUserID != 0)
	{
		$bTimeline = FALSE;
		$iLimit = 3;
		/***/
		array_push ($arUsers, $iUserID);
	} else if ($iVisitorID != 0) {
		$bTimeline = TRUE;
		$iLimit = 10;
		/***/
		$query_users = "SELECT
				user_id_microblog
			FROM `fst_follow`
			WHERE (user_id_follower='" . $iVisitorID . "')";
		$result_users = Query ($query_users);
		while ($row_users = mysqli_fetch_assoc ($result_users))
		{
			/*** Do NOT set $iUserID here. ***/
			array_push ($arUsers, $row_users['user_id_microblog']);
		}
	}

	$sHTML = '';

	if (count ($arUsers) != 0)
	{
		$query_posts = "SELECT
				fm.mbpost_id
			FROM `fst_microblog_post` fm
			LEFT JOIN `fst_user` fu
				ON fm.user_id = fu.user_id
			WHERE (fm.user_id IN (" . implode (', ', $arUsers) . "))
			AND (fm.mbpost_hidden='0')
			ORDER BY fm.mbpost_dt DESC
			LIMIT " . $iLimit . "
			OFFSET " . $iOffset;
		$result_posts = Query ($query_posts);
		$iNrPosts = mysqli_num_rows ($result_posts);
		if ($iNrPosts != 0)
		{
			while ($row_posts = mysqli_fetch_assoc ($result_posts))
			{
				$iPostID = intval ($row_posts['mbpost_id']);
				$sHTML .= GetMBPost ($iPostID, $iVisitorID, FALSE, 2);
			}
			$sHTML .= PostJavaScript();
		} else {
			/*** $iNrPosts is set above. ***/
			if ($iOffset == 0)
			{
				if ($bTimeline === FALSE)
				{
					$sHTML .= '<p>This user has no microblog posts.</p>';
				} else {
					$sHTML .= '<p>Nobody you are following has microblog posts.</p>';
				}
			} else {
				$sHTML .= '<p>That was all.</p>';
			}
		}
	} else {
		$iNrPosts = 0;
		if ($bTimeline === FALSE)
		{
			$sHTML .= '<p>Invalid data.</p>';
		} else {
			$sHTML .= '<p>You are not following anyone.</p>';
		}
	}

	$arResult['result'] = 1;
	$arResult['error'] = '';
	$arResult['html'] = $sHTML;
	$arResult['posts'] = $iNrPosts;
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Data is missing.';
	$arResult['html'] = '';
	$arResult['posts'] = 0;
}
print (json_encode ($arResult));
?>
