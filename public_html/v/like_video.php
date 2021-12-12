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

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if (isset ($_POST['video_id']))
	{
		$iVideoID = intval ($_POST['video_id']);

		$query_video = "SELECT
				video_id
			FROM `fst_video`
			WHERE (video_id='" . $iVideoID . "')";
		$result_video = Query ($query_video);
		if (mysqli_num_rows ($result_video) == 1)
		{
			if (isset ($_SESSION['fst']['user_id']))
			{
				$iUserID = intval ($_SESSION['fst']['user_id']);

				$query_old = "SELECT
						likevideo_id
					FROM `fst_likevideo`
					WHERE (video_id='" . $iVideoID . "')
					AND (user_id='" . $iUserID . "')";
				$result_old = Query ($query_old);
				if (mysqli_num_rows ($result_old) != 1)
				{
					$sDTNow = date ('Y-m-d H:i:s');

					$query_add = "INSERT INTO `fst_likevideo` SET
						video_id='" . $iVideoID . "',
						user_id='" . $iUserID . "',
						likevideo_adddate='" . $sDTNow . "'";
					$result_add = Query ($query_add);
					if (mysqli_affected_rows ($GLOBALS['link']) == 1)
					{
						UpdateCountLikesVideo ($iVideoID);

						$arResult['result'] = 1;
						$arResult['error'] = '';
						$arResult['likes'] = LikesVideo ($iVideoID);
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
				$arResult['error'] = 'To like, sign in.';
				$arResult['likes'] = 0;
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Video unknown.';
			$arResult['likes'] = 0;
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Video ID missing.';
		$arResult['likes'] = 0;
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
	$arResult['likes'] = 0;
}
print (json_encode ($arResult));
?>
