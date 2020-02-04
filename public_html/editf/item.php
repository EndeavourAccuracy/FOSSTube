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

/*****************************************************************************/

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if ((isset ($_POST['folder'])) &&
		(isset ($_POST['code'])) &&
		(isset ($_POST['action'])))
	{
		$iFolderID = intval ($_POST['folder']);
		$sCode = $_POST['code'];
		$iVideoID = CodeToID ($sCode);
		$sAction = $_POST['action'];

		if (isset ($_SESSION['fst']['user_id']))
		{
			$iUserID = intval ($_SESSION['fst']['user_id']);

			if (FolderIsFromUser ($iFolderID, $iUserID) === TRUE)
			{
				if (($sAction == 'up') || ($sAction == 'down') || ($sAction == 'del'))
				{
					switch ($sAction)
					{
						case 'up':
							$query_upd = "UPDATE `fst_folderitem` SET
								folderitem_order=folderitem_order+1
								WHERE (folder_id='" . $iFolderID . "')
								AND (video_id='" . $iVideoID . "')";
							break;
						case 'down':
							$query_upd = "UPDATE `fst_folderitem` SET
								folderitem_order=folderitem_order-1
								WHERE (folder_id='" . $iFolderID . "')
								AND (video_id='" . $iVideoID . "')";
							break;
						case 'del':
							$query_upd = "DELETE FROM `fst_folderitem`
								WHERE (folder_id='" . $iFolderID . "')
								AND (video_id='" . $iVideoID . "')";
							break;
					}
					$result_upd = Query ($query_upd);
					if (mysqli_affected_rows ($GLOBALS['link']) == 1)
					{
						$arResult['result'] = 1;
						$arResult['error'] = '';
					} else {
						$arResult['result'] = 0;
						$arResult['error'] = 'Nothing changed.';
					}
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Unknown action.';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'Folder is not yours.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Sign in to edit.';
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
