<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.1 (March 2021)
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
	if (isset ($_POST['folder']))
	{
		$iFolderID = intval ($_POST['folder']);

		if (isset ($_SESSION['fst']['user_id']))
		{
			$iUserID = intval ($_SESSION['fst']['user_id']);

			if (FolderIsFromUser ($iFolderID, $iUserID) === TRUE)
			{
				$query_del = "DELETE FROM `fst_folder`
					WHERE (folder_id='" . $iFolderID . "')
					AND (user_id='" . $iUserID . "')";
				Query ($query_del);
				if (mysqli_affected_rows ($GLOBALS['link']) == 1)
				{
					$query_del = "DELETE FROM `fst_folderitem`
						WHERE (folder_id='" . $iFolderID . "')";
					Query ($query_del);
					/***/
					$_SESSION['fst']['deleted'] = 1;
					$arResult['result'] = 1;
					$arResult['error'] = '';
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Failed to delete, please contact us.';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'Folder is not yours.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Sign in to delete.';
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Folder is missing.';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
}
print (json_encode ($arResult));
?>
