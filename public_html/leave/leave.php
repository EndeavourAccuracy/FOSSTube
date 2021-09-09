<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.3 (September 2021)
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
	if ((isset ($_POST['reason'])) &&
		(isset ($_POST['confirm'])))
	{
		$sReason = $_POST['reason'];
		$iConfirm = intval ($_POST['confirm']);

		if (strlen ($sReason) <= 3000)
		{
			if ($iConfirm == 1)
			{
				if (isset ($_SESSION['fst']['user_id']))
				{
					$iUserID = intval ($_SESSION['fst']['user_id']);
					$sDTNow = date ('Y-m-d H:i:s');

					/*** Comments. ***/
					$query_del = "UPDATE `fst_comment` SET
							comment_hidden='1'
						WHERE (user_id='" . $iUserID . "')";
					Query ($query_del);
					/***/
					$query_update = "SELECT
							DISTINCT(video_id)
						FROM `fst_comment`
						WHERE (user_id='" . $iUserID . "')";
					$result_update = Query ($query_update);
					while ($row_update = mysqli_fetch_assoc ($result_update))
						{ UpdateCountComments ($row_update['video_id']); }

					/*** Videos. ***/
					$query_del = "UPDATE `fst_video` SET
							video_deleted='1',
							video_deletedate='" . $sDTNow . "'
						WHERE (user_id='" . $iUserID . "')
						AND (video_deleted='0')";
					Query ($query_del);

					/*** Account. ***/
					$query_del = "UPDATE `fst_user` SET
							user_deleted='1',
							user_deleted_reason='" . mysqli_real_escape_string
								($GLOBALS['link'], $sReason) . "'
						WHERE (user_id='" . $iUserID . "')";
					Query ($query_del);

					$arResult['result'] = 1;
					$arResult['error'] = '';
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Sign in to delete your account.';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'You must check the checkbox.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'The reason must be max. 3000 characters' .
				' (UTF-16 code units). Currently: ' . strlen ($sReason);
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
