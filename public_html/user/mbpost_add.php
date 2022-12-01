<?php
/* SPDX-License-Identifier: Zlib */
/* FOSSTube v1.6 (December 2022)
 * Copyright (C) 2020-2022 Norbert de Jonge <nlmdejonge@gmail.com>
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
	if ((isset ($_POST['rbuser'])) &&
		(isset ($_POST['rbpost'])) &&
		(isset ($_POST['mbpost_text'])))
	{
		$sReblogUser = $_POST['rbuser'];
		$iReblogID = intval ($_POST['rbpost']);
		$sText = $_POST['mbpost_text'];

		if (($iReblogID == 0) || (($iReblogID != 0) &&
			(MBPostExists ($sReblogUser, $iReblogID) !== FALSE)))
		{
			if (isset ($_SESSION['fst']['user_id']))
			{
				$iUserID = intval ($_SESSION['fst']['user_id']);

				if (($iReblogID == 0) || (($iReblogID != 0) &&
					(HasReblogged ($iUserID, $iReblogID) === FALSE)))
				{
					if ((
							(($iReblogID == 0) && (strlen ($sText) >= 1)) ||
							(($iReblogID != 0) && (strlen ($sText) >= 0))
						) && (strlen ($sText) <= 280))
					{
						$sDTNow = date ('Y-m-d H:i:s');
						$sIP = GetIP();

						$query_add = "INSERT INTO `fst_microblog_post` SET
							user_id='" . $iUserID . "',
							mbpost_ip='" . $sIP . "',
							mbpost_dt='" . $sDTNow . "',
							mbpost_text='" . mysqli_real_escape_string
								($GLOBALS['link'], $sText) . "',
							mbpost_likes='0',
							mbpost_reblogs='0',
							mbpost_id_reblog='" . $iReblogID . "',
							mbpost_hidden='0'";
						$result_add = Query ($query_add);
						if (mysqli_affected_rows ($GLOBALS['link']) == 1)
						{
							if ($iReblogID != 0)
								{ UpdateCountReblogsMBPost ($iReblogID); }

							$arResult['result'] = 1;
							$arResult['error'] = '';
						} else {
							$arResult['result'] = 0;
							$arResult['error'] = 'Could not save microblog post.';
						}
					} else {
						$arResult['result'] = 0;
						if ($iReblogID == 0)
						{
							$arResult['error'] = 'A microblog post must be 1-280 characters' .
								' (UTF-16 code units). Currently: ' . strlen ($sText);
						} else {
							$arResult['error'] = 'A reblog comment must be 0-280 characters' .
								' (UTF-16 code units). Currently: ' . strlen ($sText);
						}
					}
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Already reblogged by you.';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'To microblog, <a href="/signin/">sign in</a>.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Cannot reblog unknown microblog post.';
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
