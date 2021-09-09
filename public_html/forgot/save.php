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
	if (isset ($_POST['password']))
	{
		/*** Do NOT use FixString() here. ***/
		$sPassword = $_POST['password'];

		$sError = '';
		if (strlen ($sPassword) < 10)
			{ $sError = 'Password is too short.'; }
		if (strlen ($sPassword) > 20)
			{ $sError = 'Password is too long.'; }
		$cInvalid = ValidateChars ($sPassword);
		if ($cInvalid !== FALSE)
			{ $sError = 'Password character "' . $cInvalid . '" is invalid.'; }
		if ($_SESSION['fst']['step_forgot'] != 3)
			{ $sError = 'Not yet.'; }

		if ($sError == '')
		{
			$sHash = password_hash ($sPassword, PASSWORD_DEFAULT);
			$iUserID = GetUserID ($_SESSION['fst']['user_usernametmp']);
			if ($iUserID === FALSE)
			{
				print ('Unknown username.');
				exit();
			}

			$query_update = "UPDATE `fst_user` SET
					user_hash='" . $sHash . "'
					WHERE user_id='" . $iUserID . "'";
			$result_update = Query ($query_update);

			/*** Session. ***/
			$_SESSION['fst']['user_id'] = $iUserID;
			$_SESSION['fst']['user_username'] = $_SESSION['fst']['user_usernametmp'];
			$_SESSION['fst']['user_pref_nsfw'] =
				intval (GetUserInfo ($iUserID, 'user_pref_nsfw'));
			$_SESSION['fst']['user_pref_cwidth'] =
				intval (GetUserInfo ($iUserID, 'user_pref_cwidth'));
			$_SESSION['fst']['user_pref_tsize'] =
				intval (GetUserInfo ($iUserID, 'user_pref_tsize'));
			$_SESSION['fst']['step_forgot'] = 1;

			$arResult['result'] = 1;
			$arResult['error'] = '';
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = $sError;
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Password is missing.';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
}
print (json_encode ($arResult));
?>
