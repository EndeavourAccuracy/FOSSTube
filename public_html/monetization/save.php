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
	if ((isset ($_POST['information'])) &&
		(isset ($_POST['patreon_yn'])) &&
		(isset ($_POST['patreon_url'])) &&
		(isset ($_POST['paypalme_yn'])) &&
		(isset ($_POST['paypalme_url'])) &&
		(isset ($_POST['subscribestar_yn'])) &&
		(isset ($_POST['subscribestar_url'])) &&
		(isset ($_POST['bitbacker_yn'])) &&
		(isset ($_POST['bitbacker_url'])) &&
		(isset ($_POST['crypto1_yn'])) &&
		(isset ($_POST['crypto1_name'])) &&
		(isset ($_POST['crypto1_address'])) &&
		(isset ($_POST['crypto1_qr'])) &&
		(isset ($_POST['crypto2_yn'])) &&
		(isset ($_POST['crypto2_name'])) &&
		(isset ($_POST['crypto2_address'])) &&
		(isset ($_POST['crypto2_qr'])) &&
		(isset ($_POST['crypto3_yn'])) &&
		(isset ($_POST['crypto3_name'])) &&
		(isset ($_POST['crypto3_address'])) &&
		(isset ($_POST['crypto3_qr'])) &&
		(isset ($_POST['crypto4_yn'])) &&
		(isset ($_POST['crypto4_name'])) &&
		(isset ($_POST['crypto4_address'])) &&
		(isset ($_POST['crypto4_qr'])))
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
		/***/
		$iCrypto1 = intval ($_POST['crypto1_yn']);
		$sCrypto1N = $_POST['crypto1_name'];
		$sCrypto1A = $_POST['crypto1_address'];
		$iCrypto1QR = intval ($_POST['crypto1_qr']);
		$iCrypto2 = intval ($_POST['crypto2_yn']);
		$sCrypto2N = $_POST['crypto2_name'];
		$sCrypto2A = $_POST['crypto2_address'];
		$iCrypto2QR = intval ($_POST['crypto2_qr']);
		$iCrypto3 = intval ($_POST['crypto3_yn']);
		$sCrypto3N = $_POST['crypto3_name'];
		$sCrypto3A = $_POST['crypto3_address'];
		$iCrypto3QR = intval ($_POST['crypto3_qr']);
		$iCrypto4 = intval ($_POST['crypto4_yn']);
		$sCrypto4N = $_POST['crypto4_name'];
		$sCrypto4A = $_POST['crypto4_address'];
		$iCrypto4QR = intval ($_POST['crypto4_qr']);

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
				$iCrypto1NLen = strlen ($sCrypto1N);
				$iCrypto1ALen = strlen ($sCrypto1A);
				$iCrypto2NLen = strlen ($sCrypto2N);
				$iCrypto2ALen = strlen ($sCrypto2A);
				$iCrypto3NLen = strlen ($sCrypto3N);
				$iCrypto3ALen = strlen ($sCrypto3A);
				$iCrypto4NLen = strlen ($sCrypto4N);
				$iCrypto4ALen = strlen ($sCrypto4A);
				$bCryptoOK = TRUE;
				if ($iCrypto1NLen > 30) { $bCryptoOK = FALSE; }
				if ($iCrypto1ALen > 250) { $bCryptoOK = FALSE; }
				if (($iCrypto1 < 0) || ($iCrypto1 > 1)) { $bCryptoOK = FALSE; }
				if (($iCrypto1 == 1) && ($iCrypto1NLen == 0)) { $bCryptoOK = FALSE; }
				if (($iCrypto1 == 1) && ($iCrypto1ALen == 0)) { $bCryptoOK = FALSE; }
				if (($iCrypto1 == 1) && ($iCrypto1QR < 0)) { $bCryptoOK = FALSE; }
				if (($iCrypto1 == 1) && ($iCrypto1QR > 1)) { $bCryptoOK = FALSE; }
				if ($iCrypto2NLen > 30) { $bCryptoOK = FALSE; }
				if ($iCrypto2ALen > 250) { $bCryptoOK = FALSE; }
				if (($iCrypto2 < 0) || ($iCrypto2 > 1)) { $bCryptoOK = FALSE; }
				if (($iCrypto2 == 1) && ($iCrypto2NLen == 0)) { $bCryptoOK = FALSE; }
				if (($iCrypto2 == 1) && ($iCrypto2ALen == 0)) { $bCryptoOK = FALSE; }
				if (($iCrypto2 == 1) && ($iCrypto2QR < 0)) { $bCryptoOK = FALSE; }
				if (($iCrypto2 == 1) && ($iCrypto2QR > 1)) { $bCryptoOK = FALSE; }
				if ($iCrypto3NLen > 30) { $bCryptoOK = FALSE; }
				if ($iCrypto3ALen > 250) { $bCryptoOK = FALSE; }
				if (($iCrypto3 < 0) || ($iCrypto3 > 1)) { $bCryptoOK = FALSE; }
				if (($iCrypto3 == 1) && ($iCrypto3NLen == 0)) { $bCryptoOK = FALSE; }
				if (($iCrypto3 == 1) && ($iCrypto3ALen == 0)) { $bCryptoOK = FALSE; }
				if (($iCrypto3 == 1) && ($iCrypto3QR < 0)) { $bCryptoOK = FALSE; }
				if (($iCrypto3 == 1) && ($iCrypto3QR > 1)) { $bCryptoOK = FALSE; }
				if ($iCrypto4NLen > 30) { $bCryptoOK = FALSE; }
				if ($iCrypto4ALen > 250) { $bCryptoOK = FALSE; }
				if (($iCrypto4 < 0) || ($iCrypto4 > 1)) { $bCryptoOK = FALSE; }
				if (($iCrypto4 == 1) && ($iCrypto4NLen == 0)) { $bCryptoOK = FALSE; }
				if (($iCrypto4 == 1) && ($iCrypto4ALen == 0)) { $bCryptoOK = FALSE; }
				if (($iCrypto4 == 1) && ($iCrypto4QR < 0)) { $bCryptoOK = FALSE; }
				if (($iCrypto4 == 1) && ($iCrypto4QR > 1)) { $bCryptoOK = FALSE; }
				if ($bCryptoOK === TRUE)
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
								($GLOBALS['link'], $sBitbacker) . "',
							monetization_crypto1_yn='" . $iCrypto1 . "',
							monetization_crypto1_name='" . mysqli_real_escape_string
								($GLOBALS['link'], $sCrypto1N) . "',
							monetization_crypto1_address='" . mysqli_real_escape_string
								($GLOBALS['link'], $sCrypto1A) . "',
							monetization_crypto1_qr='" . $iCrypto1QR . "',
							monetization_crypto2_yn='" . $iCrypto2 . "',
							monetization_crypto2_name='" . mysqli_real_escape_string
								($GLOBALS['link'], $sCrypto2N) . "',
							monetization_crypto2_address='" . mysqli_real_escape_string
								($GLOBALS['link'], $sCrypto2A) . "',
							monetization_crypto2_qr='" . $iCrypto2QR . "',
							monetization_crypto3_yn='" . $iCrypto3 . "',
							monetization_crypto3_name='" . mysqli_real_escape_string
								($GLOBALS['link'], $sCrypto3N) . "',
							monetization_crypto3_address='" . mysqli_real_escape_string
								($GLOBALS['link'], $sCrypto3A) . "',
							monetization_crypto3_qr='" . $iCrypto3QR . "',
							monetization_crypto4_yn='" . $iCrypto4 . "',
							monetization_crypto4_name='" . mysqli_real_escape_string
								($GLOBALS['link'], $sCrypto4N) . "',
							monetization_crypto4_address='" . mysqli_real_escape_string
								($GLOBALS['link'], $sCrypto4A) . "',
							monetization_crypto4_qr='" . $iCrypto4QR . "'";
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
					$arResult['error'] = 'Cryptocurrency data incorrect.';
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
