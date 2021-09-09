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
	if (isset ($_POST['muteids']))
	{
		if (isset ($_SESSION['fst']['user_id']))
		{
			$iUserID = intval ($_SESSION['fst']['user_id']);
			$arIDs = array();
			foreach ($_POST['muteids'] as $iMuteID)
			{
				array_push ($arIDs, $iMuteID);
			}

			$query_chk = "SELECT
					mute_id
				FROM `fst_mute`
				WHERE (mute_user_publisher='" . $iUserID . "')
				AND (mute_id IN (" . implode (',', $arIDs) . "))";
			$result_chk = Query ($query_chk);
			$iNrRows = mysqli_num_rows ($result_chk);
			if ($iNrRows == count ($arIDs))
			{
				$query_unmute = "DELETE FROM `fst_mute`
					WHERE (mute_id IN (" . implode (',', $arIDs) . "))";
				Query ($query_unmute);
				if (mysqli_affected_rows ($GLOBALS['link']) == $iNrRows)
				{
					$_SESSION['fst']['unmuted'] = 1;
					/***/
					$arResult['result'] = 1;
					$arResult['error'] = '';
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Try <a href="/unmute/">reloading</a>.';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'One or more muteids are incorrect.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Sign in to unmute.';
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
