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
	if ((isset ($_POST['information'])) &&
		(isset ($_POST['patreon_yn'])) &&
		(isset ($_POST['patreon_url'])) &&
		(isset ($_POST['paypalme_yn'])) &&
		(isset ($_POST['paypalme_url'])) &&
		(isset ($_POST['subscribestar_yn'])) &&
		(isset ($_POST['subscribestar_url'])) &&
		(isset ($_POST['bitbacker_yn'])) &&
		(isset ($_POST['bitbacker_url'])))
	{
		$sInfo = $_POST['information'];
		$iPatreon = intval ($_POST['patreon_yn']);
		$sPatreon = $_POST['patreon_url'];
		$iPayPalMe = intval ($_POST['paypalme_yn']);
		$sPayPalMe = $_POST['paypalme_url'];
		$iSubscribeStar = intval ($_POST['subscribestar_yn']);
		$sSubscribeStar = $_POST['subscribestar_url'];
		$iBitbacker = intval ($_POST['bitbacker_yn']);
		$sBitbacker = $_POST['bitbacker_url'];

		if (strlen ($sInfo) <= 2000)
		{
			$iPatreonLen = strlen ($sPatreon);
			$iPayPalMeLen = strlen ($sPayPalMe);
			$iSubscribeStarLen = strlen ($sSubscribeStar);
			$iBitbackerLen = strlen ($sBitbacker);
			if (((($iPatreon == 0) && ($iPatreonLen <= 250)) ||
				(($iPatreon == 1) && ($iPatreonLen > 0) &&
				($iPatreonLen <= 250))) &&
				((($iPayPalMe == 0) && ($iPayPalMeLen <= 250)) ||
				(($iPayPalMe == 1) && ($iPayPalMeLen > 0) &&
				($iPayPalMeLen <= 250))) &&
				((($iSubscribeStar == 0) && ($iSubscribeStarLen <= 250)) ||
				(($iSubscribeStar == 1) && ($iSubscribeStarLen > 0) &&
				($iSubscribeStarLen <= 250))) &&
				((($iBitbacker == 0) && ($iBitbackerLen <= 250)) ||
				(($iBitbacker == 1) && ($iBitbackerLen > 0) &&
				($iBitbackerLen <= 250))))
			{
				$query_del = "DELETE FROM `fst_monetization`
					WHERE (user_id='" . $_SESSION['fst']['user_id'] . "')";
				Query ($query_del);

				$query_ins = "INSERT INTO `fst_monetization` SET
						user_id='" . $_SESSION['fst']['user_id'] . "',
						monetization_information='" . mysqli_real_escape_string
							($GLOBALS['link'], $sInfo) . "',
						monetization_patreon_yn='" . $iPatreon . "',
						monetization_patreon_url='" . mysqli_real_escape_string
							($GLOBALS['link'], $sPatreon) . "',
						monetization_paypalme_yn='" . $iPayPalMe . "',
						monetization_paypalme_url='" . mysqli_real_escape_string
							($GLOBALS['link'], $sPayPalMe) . "',
						monetization_subscribestar_yn='" . $iSubscribeStar . "',
						monetization_subscribestar_url='" . mysqli_real_escape_string
							($GLOBALS['link'], $sSubscribeStar) . "',
						monetization_bitbacker_yn='" . $iBitbacker . "',
						monetization_bitbacker_url='" . mysqli_real_escape_string
							($GLOBALS['link'], $sBitbacker) . "'";
				$result_ins = Query ($query_ins);
				if (mysqli_affected_rows ($GLOBALS['link']) == 1)
				{
					$_SESSION['fst']['monetization-saved'] = 1;
					$arResult['result'] = 1;
					$arResult['error'] = '';
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Could not save, for some reason.';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'Processor data incorrect.';
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
