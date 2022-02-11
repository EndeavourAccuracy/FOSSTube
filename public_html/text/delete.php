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
	if (isset ($_POST['code']))
	{
		$sCode = $_POST['code'];
		$iVideoID = CodeToID ($sCode);

		if (isset ($_SESSION['fst']['user_id']))
		{
			$iUserID = intval ($_SESSION['fst']['user_id']);

			$query_chk = "SELECT
					video_id
				FROM `fst_video`
				WHERE (user_id='" . $iUserID . "')
				AND (video_deleted='0')
				AND (video_id='" . $iVideoID . "')";
			$result_chk = Query ($query_chk);
			if (mysqli_num_rows ($result_chk) == 1)
			{
				$sDTNow = date ('Y-m-d H:i:s');
				$query_del = "UPDATE `fst_video` SET
						video_deleted='2',
						video_deletedate='" . $sDTNow . "'
					WHERE (video_id='" . $iVideoID . "')";
				Query ($query_del);
				if (mysqli_affected_rows ($GLOBALS['link']) == 1)
				{
					$_SESSION['fst']['deleted'] = 2;
					$arResult['result'] = 1;
					$arResult['error'] = '';
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Failed to delete, please contact us.';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'Not your text, or already deleted.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Sign in to delete.';
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Code is missing.';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
}
print (json_encode ($arResult));
?>
