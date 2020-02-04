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
	if (isset ($_POST['information']))
	{
		$sInfo = $_POST['information'];

		if (strlen ($sInfo) <= 2000)
		{
			$query_profile = "UPDATE `fst_user` SET
				user_information='" . mysqli_real_escape_string
					($GLOBALS['link'], $sInfo) . "'
				WHERE (user_id='" . $_SESSION['fst']['user_id'] . "')";
			$result_profile = Query ($query_profile);
			if (mysqli_affected_rows ($GLOBALS['link']) == 1)
			{
				$_SESSION['fst']['profile-saved'] = 1;
				$arResult['result'] = 1;
				$arResult['error'] = '';
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'Nothing changed.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'The information must be max. 2000 characters' .
				' (UTF-16 code units). Currently: ' . strlen ($sInfo);
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