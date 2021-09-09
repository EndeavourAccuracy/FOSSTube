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

/*****************************************************************************/

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if (isset ($_POST['codes']))
	{
		if (isset ($_SESSION['fst']['user_id']))
		{
			$iUserID = intval ($_SESSION['fst']['user_id']);
			$arIDs = array();
			foreach ($_POST['codes'] as $sCode)
			{
				$iVideoID = CodeToID ($sCode);
				array_push ($arIDs, $iVideoID);
			}

			$query_chk = "SELECT
					video_id
				FROM `fst_video`
				WHERE (video_deleted='5')
				AND (video_deletedate BETWEEN (NOW() - INTERVAL 2 DAY) AND NOW())
				AND (video_id IN (" . implode (',', $arIDs) . "))";
			$result_chk = Query ($query_chk);
			$iNrRows = mysqli_num_rows ($result_chk);
			if ($iNrRows == count ($arIDs))
			{
				$arIDsVerified = array();
				while ($row_chk = mysqli_fetch_assoc ($result_chk))
				{
					array_push ($arIDsVerified, $row_chk['video_id']);
				}
				/*** Do NOT move user_id_old=... down. ***/
				$query_adopt = "UPDATE `fst_video` SET
						user_id_old=IF(user_id_old='0',user_id,user_id_old),
						user_id='" . $iUserID . "',
						video_deleted='0',
						video_deletedate='1970-01-01 00:00:00'
					WHERE (video_id IN (" . implode (',', $arIDsVerified) . "))";
				Query ($query_adopt);
				if (mysqli_affected_rows ($GLOBALS['link']) == $iNrRows)
				{
					$_SESSION['fst']['adopted'] = 1;
					$arResult['result'] = 1;
					$arResult['error'] = '';
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Check your <a href="/videos/" style="color:#f00;">videos</a>.';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'Perhaps someone beat you to it? Try <a href="/adopt/" style="color:#f00;">reloading</a> the page.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Sign in to adopt.';
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
