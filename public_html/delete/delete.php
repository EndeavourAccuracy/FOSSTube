<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.0 (February 2020)
 * Copyright (C) 2020 Norbert de Jonge <mail@norbertdejonge.nl>
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
	if ((isset ($_POST['semi'])) &&
		(isset ($_POST['codes'])))
	{
		if (isset ($_SESSION['fst']['user_id']))
		{
			$iUserID = intval ($_SESSION['fst']['user_id']);
			$iSemi = intval ($_POST['semi']);
			if ($iSemi == 0) { $iVideoDeleted = 1; } else { $iVideoDeleted = 5; }
			$arIDs = array();
			foreach ($_POST['codes'] as $sCode)
			{
				$iVideoID = CodeToID ($sCode);
				array_push ($arIDs, $iVideoID);
			}

			$query_chk = "SELECT
					video_id
				FROM `fst_video`
				WHERE (user_id='" . $iUserID . "')
				AND (video_deleted='0')
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
				$sDTNow = date ('Y-m-d H:i:s');
				$query_del = "UPDATE `fst_video` SET
						video_deleted=IF(user_id_old<>'0' AND user_id_old<>'" . $iUserID . "','5','" . $iVideoDeleted . "'),
						video_deletedate='" . $sDTNow . "'
					WHERE (video_id IN (" . implode (',', $arIDsVerified) . "))";
				Query ($query_del);
				if (mysqli_affected_rows ($GLOBALS['link']) == $iNrRows)
				{
					$_SESSION['fst']['deleted'] = $iVideoDeleted;
					$arResult['result'] = 1;
					$arResult['error'] = '';
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Go back to the <a href="/videos/">videos</a>.';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'One or more codes are incorrect.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Sign in to delete.';
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
