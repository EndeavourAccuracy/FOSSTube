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
	if (isset ($_SESSION['fst']['user_id']))
	{
		$iUserID = $_SESSION['fst']['user_id'];

		if ((isset ($_POST['nsfw_yn'])) &&
			(isset ($_POST['cwidth'])) &&
			(isset ($_POST['tsize'])) &&
			(isset ($_POST['musers'])))
		{
			$iNSFW = intval ($_POST['nsfw_yn']);
			$iCWidth = intval ($_POST['cwidth']);
			$iTSize = intval ($_POST['tsize']);
			$sMUsers = $_POST['musers'];

			if ((($iNSFW == 0) || ($iNSFW == 1)) &&
				(($iCWidth >= 0) && ($iCWidth <= 13)) &&
				(($iTSize == 100) || ($iTSize == 90) || ($iTSize == 80) ||
					($iTSize == 70) || ($iTSize == 60) || ($iTSize == 50)))
			{
				$sError = '';

				if ($sMUsers != '') /*** The textarea may be empty. ***/
				{
					$arMUsers = preg_split ('/(\r\n|\r|\n)/', $sMUsers);
					foreach ($arMUsers as $sMUser)
					{
						if (strlen ($sMUser) == 0)
						{
							$sError = 'The username list contains an empty line.';
						} else if (UserExists ($sMUser) === FALSE) {
							$sError = 'Unknown username "' . Sanitize ($sMUser) .
								'". Either they deleted their account, or you made a typo.';
						}
					}
				}

				if ($sError == '')
				{
					$query_upd = "UPDATE `fst_user` SET
							user_pref_nsfw='" . $iNSFW . "',
							user_pref_cwidth='" . $iCWidth . "',
							user_pref_tsize='" . $iTSize . "',
							user_pref_musers='" . mysqli_real_escape_string
								($GLOBALS['link'], $sMUsers) . "'
						WHERE (user_id='" . $iUserID . "')";
					Query ($query_upd);
					if (mysqli_affected_rows ($GLOBALS['link']) == 1)
					{
						$_SESSION['fst']['user_pref_nsfw'] = $iNSFW;
						$_SESSION['fst']['user_pref_cwidth'] = $iCWidth;
						$_SESSION['fst']['user_pref_tsize'] = $iTSize;
						/***/
						$_SESSION['fst']['preferences-saved'] = 1;
						$arResult['result'] = 1;
						$arResult['error'] = '';
					} else {
						$arResult['result'] = 0;
						$arResult['error'] = 'Nothing changed.';
					}
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = $sError;
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'Invalid value.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Data is missing.';
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Sign in to save.';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
}
print (json_encode ($arResult));
?>
