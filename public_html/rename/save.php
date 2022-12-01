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
	if (isset ($_POST['username']))
	{
		/*** Do NOT use FixString() here. ***/
		$sUsername = $_POST['username'];

		$sError = '';
		if (strlen ($sUsername) < 4)
			{ $sError = 'Username is too short.'; }
		if (strlen ($sUsername) > 15)
			{ $sError = 'Username is too long.'; }
		$cInvalid = ValidateChars ($sUsername);
		if ($cInvalid !== FALSE)
			{ $sError = 'Username character "' . $cInvalid . '" is invalid.'; }
		if (UsernameInUse ($sUsername) === TRUE)
			{ $sError = 'Username is already taken.'; }
		if (in_array ($sUsername, $GLOBALS['disallowed_usernames']) === TRUE)
			{ $sError = 'Disallowed username.'; }

		if ($sError == '')
		{
			$iUserID = $_SESSION['fst']['user_id'];
			$sUsernameOld = $_SESSION['fst']['user_username'];
			$arOld = OldUsernames ($iUserID);

			if ($arOld['count'] != 2)
			{
				/*** Copy large and small avatars. ***/
				$sAvFrom = dirname (__FILE__) . '/../avatars/' .
					$sUsernameOld . '_large.png';
				$sAvTo = dirname (__FILE__) . '/../avatars/' .
					$sUsername . '_large.png';
				copy ($sAvFrom, $sAvTo);
				$sAvFrom = dirname (__FILE__) . '/../avatars/' .
					$sUsernameOld . '_small.png';
				$sAvTo = dirname (__FILE__) . '/../avatars/' .
					$sUsername . '_small.png';
				copy ($sAvFrom, $sAvTo);

				if ($arOld['old1'] == '')
				{
					$query_rename = "UPDATE `fst_user` SET
							user_username='" . $sUsername . "',
							user_username_old1='" . $sUsernameOld . "'
						WHERE (user_id='" . $iUserID . "')";
				} else {
					$query_rename = "UPDATE `fst_user` SET
							user_username='" . $sUsername . "',
							user_username_old2='" . $sUsernameOld . "'
						WHERE (user_id='" . $iUserID . "')";
				}
				$result_rename = Query ($query_rename);
				if (mysqli_affected_rows ($GLOBALS['link']) == 1)
				{
					$_SESSION['fst']['user_username'] = $sUsername;
					$_SESSION['fst']['rename-saved'] = 1;
					/***/
					$arResult['result'] = 1;
					$arResult['error'] = '';
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Could not save, for some reason.';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'You have already renamed twice.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = $sError;
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Username is missing.';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
}
print (json_encode ($arResult));
?>
