<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.2 (August 2021)
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
	if ((isset ($_POST['folder'])) &&
		(isset ($_POST['code'])))
	{
		$iFolderID = intval ($_POST['folder']);
		$sCode = $_POST['code'];

		if (isset ($_SESSION['fst']['user_id']))
		{
			$iUserID = $_SESSION['fst']['user_id'];
			$arVideo = VideoExists ($sCode);

			if ((($iFolderID == 0) ||
				(FolderIsFromUser ($iFolderID, $iUserID) === TRUE))
				&& ($arVideo !== FALSE))
			{
				if (($iFolderID == 0) ||
					(InFolder ($arVideo['id'], 0, $iFolderID) === FALSE))
				{
					/*** Create folder. ***/
					if ($iFolderID == 0)
					{
						$query_folder = "INSERT INTO `fst_folder` SET
							user_id='" . $iUserID . "',
							folder_title='New folder',
							folder_description='',
							folder_public='0'";
						Query ($query_folder);
						$iFolderID = mysqli_insert_id ($GLOBALS['link']);
					}

					/*** Store content. ***/
					$query_item = "INSERT INTO `fst_folderitem` SET
						folder_id='" . $iFolderID . "',
						video_id='" . $arVideo['id'] . "',
						folderitem_order='0'";
					Query ($query_item);
					if (mysqli_affected_rows ($GLOBALS['link']) == 1)
					{
						$_SESSION['fst']['stored'] = 1;
						/***/
						$arResult['result'] = 1;
						$arResult['error'] = '';
						$arResult['folder'] = $iFolderID;
					} else {
						$arResult['result'] = 0;
						$arResult['error'] = 'For some reason, that did not work...';
						$arResult['folder'] = 0;
					}
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Content is already in that folder.';
					$arResult['folder'] = 0;
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'Content unknown or folder is not yours.';
				$arResult['folder'] = 0;
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Sign in to store.';
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Data is missing.';
		$arResult['folder'] = 0;
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
	$arResult['folder'] = 0;
}
print (json_encode ($arResult));
?>
