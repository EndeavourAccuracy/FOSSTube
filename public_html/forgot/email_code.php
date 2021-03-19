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
function GetUser ($sData)
/*****************************************************************************/
{
	$query_found = "SELECT
			user_email,
			user_username
		FROM `fst_user`
		WHERE ((user_email='" . mysqli_real_escape_string
			($GLOBALS['link'], $sData) . "')
		OR (user_username='" . mysqli_real_escape_string
			($GLOBALS['link'], $sData) . "'))
		AND (user_deleted='0')";
	$result_found = Query ($query_found);
	if (mysqli_num_rows ($result_found) == 1)
	{
		$row_found = mysqli_fetch_assoc ($result_found);
		$arReturn['user_email'] = Sanitize ($row_found['user_email']);
		$arReturn['user_username'] = $row_found['user_username'];
		return ($arReturn);
	} else {
		return (FALSE);
	}
}
/*****************************************************************************/

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if ((isset ($_POST['captcha'])) &&
		(isset ($_POST['data'])))
	{
		$sCaptcha = str_replace ('−', '-', $_POST['captcha']);
		$_SESSION['fst']['captcha'] = intval ($sCaptcha);
		$sData = $_POST['data'];
		$_SESSION['fst']['random'] = bin2hex (random_bytes (16));

		if (($GLOBALS['live'] === FALSE) ||
			($_SESSION['fst']['captcha'] == VerifyAnswer()))
		{
			$arUser = GetUser ($sData);
			if ($arUser !== FALSE)
			{
				$sEmail = $arUser['user_email'];
				$sUsername = $arUser['user_username'];
				$iSent = SendEmail ($sEmail, array(),
					'[ ' . $GLOBALS['name'] . ' ] Code',
					$_SESSION['fst']['random']);
				if ($iSent == 1)
				{
					$arResult['result'] = 1;
					$arResult['error'] = '';

					/*** Session. ***/
					$_SESSION['fst']['user_emailtmp'] = $sEmail;
					$_SESSION['fst']['user_usernametmp'] = $sUsername;
					$_SESSION['fst']['step_forgot'] = 2;
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Could not send email. Maybe try again later.';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'Username or email address not in use.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Captcha incorrect.';
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Some data is missing.';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
}
print (json_encode ($arResult));
?>
