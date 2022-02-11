<?php
/* SPDX-License-Identifier: Zlib */
/* FOSSTube v1.5 (February 2022)
 * Copyright (C) 2020-2022 Norbert de Jonge <mail@norbertdejonge.nl>
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
function InUseByOther ($sEmail)
/*****************************************************************************/
{
	$query_found = "SELECT
			COUNT(*) AS found
		FROM `fst_user`
		WHERE (user_email='" . $sEmail . "')
		AND (user_id<>'" . $_SESSION['fst']['user_id'] . "')";
	$result_found = Query ($query_found);
	$row_found = mysqli_fetch_assoc ($result_found);
	if ($row_found['found'] == 0)
		{ return (FALSE); } else { return (TRUE); }
}
/*****************************************************************************/

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if ((isset ($_POST['email'])) &&
		(isset ($_POST['password'])))
	{
		$sEmail = FixString ($_POST['email']);
		$_SESSION['fst']['email'] = $sEmail;
		/*** Do NOT use FixString() here. ***/
		$sPassword = $_POST['password'];
		$_SESSION['fst']['password'] = $sPassword;
		$_SESSION['fst']['random'] = bin2hex (random_bytes (16));

		$sError = '';
		if (IsEmail ($sEmail) === FALSE)
			{ $sError = 'Not a valid email address.'; }
		if (strlen ($sEmail) > 100)
			{ $sError = 'Email address is too long.'; }
		if (InUseByOther ($sEmail) === TRUE)
			{ $sError = 'Email address already has (or had) an account.'; }
		foreach ($GLOBALS['disallowed_email_ends'] AS $sEnd)
		{
			if (substr (strtolower ($sEmail), 0 - strlen ($sEnd)) == $sEnd)
			{
				$sError = 'Addresses that end with "' . $sEnd . '" are not allowed.';
			}
		}

		if (strlen ($sPassword) < 10)
			{ $sError = 'Password is too short.'; }
		if (strlen ($sPassword) > 20)
			{ $sError = 'Password is too long.'; }
		$cInvalid = ValidateChars ($sPassword);
		if ($cInvalid !== FALSE)
			{ $sError = 'Password character "' . $cInvalid . '" is invalid.'; }

		if ($sError == '')
		{
			$iSent = SendEmail ($sEmail, array(),
				'[ ' . $GLOBALS['name'] . ' ] Code',
				$_SESSION['fst']['random']);
			if ($iSent == 1)
			{
				$arResult['result'] = 1;
				$arResult['error'] = '';
				$_SESSION['fst']['step_account'] = 2;
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'Could not send email. Maybe try again later.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = $sError;
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Email or password are missing.';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
}
print (json_encode ($arResult));
?>
