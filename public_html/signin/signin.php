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

if ((isset ($_POST['username'])) &&
	(isset ($_POST['password'])))
{
	$sUsername = FixString ($_POST['username']);
	$sPassword = FixString ($_POST['password']);
	$sIP = GetIP();
	$sDTHourAgo = date ('Y-m-d H:i:s', time() - 3600);

	$query_tries = "SELECT
			COUNT(*) AS tries
		FROM `fst_faillogin`
		WHERE (((faillogin_ip='" . $sIP . "')
		OR (faillogin_username='" . $sUsername . "'))
		AND (faillogin_dt >= '" . $sDTHourAgo . "'))";
	$result_tries = Query ($query_tries);
	$row_tries = mysqli_fetch_assoc ($result_tries);
	if ($row_tries['tries'] < $GLOBALS['max_faillogin_hour'])
	{
		$query_hash = "SELECT
				user_id,
				user_username,
				user_hash,
				user_pref_nsfw
			FROM `fst_user`
			WHERE (user_username='" . $sUsername . "')
			AND (user_deleted='0')";
		$result_hash = Query ($query_hash);
		if (mysqli_num_rows ($result_hash) == 1)
		{
			$row_hash = mysqli_fetch_assoc ($result_hash);
			$iUserID = $row_hash['user_id'];
			$sUsername = $row_hash['user_username'];
			$sHash = $row_hash['user_hash'];
			$iPrefNSFW = intval ($row_hash['user_pref_nsfw']);
			$sDTNow = date ('Y-m-d H:i:s');

			if (password_verify ($sPassword, $sHash))
			{
				/*** Session. ***/
				$_SESSION['fst']['user_id'] = $iUserID;
				$_SESSION['fst']['user_username'] = $sUsername;
				$_SESSION['fst']['user_pref_nsfw'] = $iPrefNSFW;
				/***/
				unset ($_SESSION['fst']['step_signup']);
				unset ($_SESSION['fst']['step_forgot']);
				unset ($_SESSION['fst']['step_account']);
				/***/
				$query_lastlogin = "UPDATE `fst_user` SET
						user_lastlogindt='" . $sDTNow . "'
					WHERE (user_id='" . $iUserID . "')";
				Query ($query_lastlogin);

				$arResult['result'] = 1;
				$arResult['error'] = '';
			} else {
				$query_fail = "INSERT INTO `fst_faillogin` SET
					faillogin_username='" . $sUsername . "',
					faillogin_ip='" . $sIP . "',
					faillogin_dt='" . $sDTNow . "'";
				$result_fail = Query ($query_fail);

				$arResult['result'] = 0;
				$arResult['error'] = 'Incorrect password.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Username is not registered or has been renamed, or account was deleted.';
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Too many tries last hour.';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Values are missing.';
}
print (json_encode ($arResult));
?>
