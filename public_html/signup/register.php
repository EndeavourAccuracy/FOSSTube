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

if ((isset ($_POST['username'])) &&
	(isset ($_POST['password'])) &&
	(isset ($_POST['agree'])))
{
	/*** Do NOT use FixString() here. ***/
	$sUsername = $_POST['username'];
	$sPassword = $_POST['password'];
	$iAgree = intval ($_POST['agree']);

	$sError = '';
	if (strlen ($sUsername) < 4)
		{ $sError = 'Username is too short.'; }
	if (strlen ($sUsername) > 15)
		{ $sError = 'Username is too long.'; }
	$cInvalid = ValidateChars ($sUsername);
	if ($cInvalid !== FALSE)
		{ $sError = 'Username character "' . $cInvalid . '" is invalid.'; }
	if (strlen ($sPassword) < 10)
		{ $sError = 'Password is too short.'; }
	if (strlen ($sPassword) > 20)
		{ $sError = 'Password is too long.'; }
	$cInvalid = ValidateChars ($sPassword);
	if ($cInvalid !== FALSE)
		{ $sError = 'Password character "' . $cInvalid . '" is invalid.'; }
	if ($iAgree != 1)
		{ $sError = 'You must check the checkbox.'; }
	if ((!isset ($_SESSION['fst']['step_signup'])) ||
		($_SESSION['fst']['step_signup'] != 3))
		{ $sError = 'Not yet.'; }
	if (UsernameInUse ($sUsername) === TRUE)
		{ $sError = 'Username is already taken.'; }
	if (in_array ($sUsername, $GLOBALS['disallowed_usernames']) === TRUE)
		{ $sError = 'Disallowed username.'; }

	if ($sError == '')
	{
		$sDTNow = date ('Y-m-d H:i:s');
		$sEmail = $_SESSION['fst']['email'];
		$sHash = password_hash ($sPassword, PASSWORD_DEFAULT);
		$sIP = GetIP();

		$query_insert = "INSERT INTO `fst_user` SET
			user_email='" . mysqli_real_escape_string
				($GLOBALS['link'], $sEmail) . "',
			user_username='" . $sUsername . "',
			user_hash='" . $sHash . "',
			user_priv_customthumbnails='1',
			user_information='',
			user_warnings_video='0',
			user_warnings_comment='0',
			user_warnings_avatar='0',
			user_deleted='0',
			user_deleted_reason='',
			user_regip='" . mysqli_real_escape_string
				($GLOBALS['link'], $sIP) . "',
			user_regdt='" . $sDTNow . "',
			user_lastlogindt='" . $sDTNow . "',
			user_pref_nsfw='" . $GLOBALS['default_pref']['user_pref_nsfw'] . "'";
		$result_insert = Query ($query_insert);

		/*** Session. ***/
		$iUserID = mysqli_insert_id ($GLOBALS['link']);
		$_SESSION['fst']['user_id'] = $iUserID;
		$_SESSION['fst']['user_username'] = $sUsername;
		$_SESSION['fst']['user_pref_nsfw'] =
			$GLOBALS['default_pref']['user_pref_nsfw'];
		$_SESSION['fst']['step_signup'] = 1;

		$arResult['result'] = 1;
		$arResult['error'] = '';
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = $sError;
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Values are missing.';
}
print (json_encode ($arResult));
?>
