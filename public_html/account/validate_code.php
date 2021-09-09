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
	if (isset ($_POST['code']))
	{
		$sCode = FixString ($_POST['code']);

		$sError = '';
		if ($_SESSION['fst']['step_account'] != 2)
			{ $sError = 'Not yet.'; }
		if ($sError == '')
		{
			if (RandomCorrect ($sCode))
			{
				$sEmail = $_SESSION['fst']['email'];
				$sPassword = $_SESSION['fst']['password'];
				$sHash = password_hash ($sPassword, PASSWORD_DEFAULT);

				$query_update = "UPDATE `fst_user` SET
						user_email='" . mysqli_real_escape_string
							($GLOBALS['link'], $sEmail) . "',
						user_hash='" . $sHash . "'
					WHERE (user_id='" . $_SESSION['fst']['user_id'] . "')";
				$result_update = Query ($query_update);

				$arResult['result'] = 1;
				$arResult['error'] = '';
				$_SESSION['fst']['step_account'] = 3;
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'Invalid code.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = $sError;
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Code value is missing.';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
}
print (json_encode ($arResult));
?>
