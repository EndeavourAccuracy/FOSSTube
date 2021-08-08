<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.2 (August 2021)
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
function Processor ($sID, $sWebsite, $sName, $sURLBase, $iChecked, $sValue)
/*****************************************************************************/
{
print ('
<div class="processor">
<h3 style="display:inline;">' . $sName . '</h3>
<a target="_blank" href="' . $sWebsite . '"><img src="/images/icon_info.png" alt="info" style="vertical-align:middle;"></a>
<br>
<input type="checkbox" id="' . $sID . '_yn"');
	if ($iChecked == 1) { print (' checked'); }
print ('> Check to enable
<br>
' . $sURLBase . '<input type="text" id="' . $sID . '_url" value="' . Sanitize ($sValue) . '" style="margin-bottom:0;">
</div>
');
}
/*****************************************************************************/
function Cryptocurrency ($sID, $iNr, $iChecked, $sName, $sAddress, $iQR)
/*****************************************************************************/
{
print ('
<div class="crypto">
<h3>Cryptocurrency #' . $iNr . '</h3>
<input type="checkbox" id="' . $sID . '_yn"');
	if ($iChecked == 1) { print (' checked'); }
print ('> Check to enable
<br>
<label class="lbl">Name (e.g. Bitcoin):</label>
<input type="text" id="' . $sID . '_name" value="' . Sanitize ($sName) . '" maxlength="30">
<br>
<label class="lbl">Address:</label>
<input type="text" id="' . $sID . '_address" value="' . Sanitize ($sAddress) . '" maxlength="250" style="width:100%;">
<br>
<label class="lbl">In addition to displaying the address, autogenerate and show a <a target="_blank" href="https://en.wikipedia.org/wiki/QR_code">QR code</a>:</label>
');

	print ('<select id="' . $sID . '_qr">');
	print ('<option value="2"');
	if ($iQR == 2) { print (' selected'); }
	print ('>Select...</option>');
	print ('<option value="1"');
	if ($iQR == 1) { print (' selected'); }
	print ('>Yes, also a QR code</option>');
	print ('<option value="0"');
	if ($iQR == 0) { print (' selected'); }
	print ('>No, just the address</option>');
	print ('</select>');

print ('
</div>
');
}
/*****************************************************************************/
function FormMon ()
/*****************************************************************************/
{
	$query_mon = "SELECT
			monetization_information,
			monetization_patreon_yn,
			monetization_patreon_url,
			monetization_paypalme_yn,
			monetization_paypalme_url,
			monetization_subscribestar_yn,
			monetization_subscribestar_url,
			monetization_bitbacker_yn,
			monetization_bitbacker_url,
			monetization_crypto1_yn,
			monetization_crypto1_name,
			monetization_crypto1_address,
			monetization_crypto1_qr,
			monetization_crypto2_yn,
			monetization_crypto2_name,
			monetization_crypto2_address,
			monetization_crypto2_qr,
			monetization_crypto3_yn,
			monetization_crypto3_name,
			monetization_crypto3_address,
			monetization_crypto3_qr,
			monetization_crypto4_yn,
			monetization_crypto4_name,
			monetization_crypto4_address,
			monetization_crypto4_qr
		FROM `fst_monetization`
		WHERE (user_id='" . $_SESSION['fst']['user_id'] . "')";
	$result_mon = Query ($query_mon);
	if (mysqli_num_rows ($result_mon) == 1)
	{
		$row_mon = mysqli_fetch_assoc ($result_mon);
		$sInfo = $row_mon['monetization_information'];
		$iPatreon = intval ($row_mon['monetization_patreon_yn']);
		$sPatreon = $row_mon['monetization_patreon_url'];
		$iPayPalMe = intval ($row_mon['monetization_paypalme_yn']);
		$sPayPalMe = $row_mon['monetization_paypalme_url'];
		$iSubscribeStar = intval ($row_mon['monetization_subscribestar_yn']);
		$sSubscribeStar = $row_mon['monetization_subscribestar_url'];
		$iBitbacker = intval ($row_mon['monetization_bitbacker_yn']);
		$sBitbacker = $row_mon['monetization_bitbacker_url'];
		/***/
		$iCrypto1 = intval ($row_mon['monetization_crypto1_yn']);
		$sCrypto1N = $row_mon['monetization_crypto1_name'];
		$sCrypto1A = $row_mon['monetization_crypto1_address'];
		$iCrypto1QR = intval ($row_mon['monetization_crypto1_qr']);
		$iCrypto2 = intval ($row_mon['monetization_crypto2_yn']);
		$sCrypto2N = $row_mon['monetization_crypto2_name'];
		$sCrypto2A = $row_mon['monetization_crypto2_address'];
		$iCrypto2QR = intval ($row_mon['monetization_crypto2_qr']);
		$iCrypto3 = intval ($row_mon['monetization_crypto3_yn']);
		$sCrypto3N = $row_mon['monetization_crypto3_name'];
		$sCrypto3A = $row_mon['monetization_crypto3_address'];
		$iCrypto3QR = intval ($row_mon['monetization_crypto3_qr']);
		$iCrypto4 = intval ($row_mon['monetization_crypto4_yn']);
		$sCrypto4N = $row_mon['monetization_crypto4_name'];
		$sCrypto4A = $row_mon['monetization_crypto4_address'];
		$iCrypto4QR = intval ($row_mon['monetization_crypto4_qr']);
	} else {
		$sInfo = '';
		$iPatreon = 0; $sPatreon = '';
		$iPayPalMe = 0; $sPayPalMe = '';
		$iSubscribeStar = 0; $sSubscribeStar = '';
		$iBitbacker = 0; $sBitbacker = '';
		$iCrypto1 = 0; $sCrypto1N = ''; $sCrypto1A = ''; $iCrypto1QR = 2;
		$iCrypto2 = 0; $sCrypto2N = ''; $sCrypto2A = ''; $iCrypto2QR = 2;
		$iCrypto3 = 0; $sCrypto3N = ''; $sCrypto3A = ''; $iCrypto3QR = 2;
		$iCrypto4 = 0; $sCrypto4N = ''; $sCrypto4A = ''; $iCrypto4QR = 2;
	}

print ('
<h2>Introduction</h2>
' . $GLOBALS['name'] . ' offers pass-through functionality for a number of payment processors, allowing you to collect one-off tips or recurring pledges from viewers. ' . $GLOBALS['name'] . ' receives zero fees from this feature.

<h2 style="margin-top:10px;">Custom message</h2>
Optional text to be displayed on the monetization pop-up (modal dialog).
<label for="information" class="lbl">Information:</label>
<textarea id="information" style="width:600px; max-width:100%; height:70px;">' . Sanitize ($sInfo) . '</textarea>

<h2 style="margin-top:10px;">Enabled processors</h2>
');

	Processor ('patreon', 'https://patreon.com/',
		'Patreon', 'https://www.patreon.com/',
		$iPatreon, $sPatreon);
	Processor ('paypalme', 'https://www.paypal.me/',
		'PayPal.Me', 'https://www.paypal.me/',
		$iPayPalMe, $sPayPalMe);
	Processor ('subscribestar', 'https://www.subscribestar.com/',
		'SubscribeStar', 'https://www.subscribestar.com/',
		$iSubscribeStar, $sSubscribeStar);
	Processor ('bitbacker', 'https://bitbacker.io/',
		'Bitbacker', 'https://bitbacker.io/user/',
		$iBitbacker, $sBitbacker);

print ('
<h2 style="margin-top:10px;">Enabled cryptocurrencies</h2>
');

	Cryptocurrency ('crypto1', 1, $iCrypto1,
		$sCrypto1N, $sCrypto1A, $iCrypto1QR);
	Cryptocurrency ('crypto2', 2, $iCrypto2,
		$sCrypto2N, $sCrypto2A, $iCrypto2QR);
	Cryptocurrency ('crypto3', 3, $iCrypto3,
		$sCrypto3N, $sCrypto3A, $iCrypto3QR);
	Cryptocurrency ('crypto4', 4, $iCrypto4,
		$sCrypto4N, $sCrypto4A, $iCrypto4QR);

print ('
<h2 style="margin-top:10px;">Save settings</h2>
If one or more processors or cryptocurrencies have been enabled, a link to a monetization pop-up (modal dialog) will be shown under all your published videos and texts.
<div id="monetization-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="monetization-save" value="Save">
');

print ('
<script>
$("#monetization-save").click(function(){
	var information = $("#information").val();
	var patreon_bool = $("#patreon_yn").is(":checked");
	if (patreon_bool == false)
		{ var patreon_yn = 0; } else { var patreon_yn = 1; }
	var patreon_url = $("#patreon_url").val();
	var paypalme_bool = $("#paypalme_yn").is(":checked");
	if (paypalme_bool == false)
		{ var paypalme_yn = 0; } else { var paypalme_yn = 1; }
	var paypalme_url = $("#paypalme_url").val();
	var subscribestar_bool = $("#subscribestar_yn").is(":checked");
	if (subscribestar_bool == false)
		{ var subscribestar_yn = 0; } else { var subscribestar_yn = 1; }
	var subscribestar_url = $("#subscribestar_url").val();
	var bitbacker_bool = $("#bitbacker_yn").is(":checked");
	if (bitbacker_bool == false)
		{ var bitbacker_yn = 0; } else { var bitbacker_yn = 1; }
	var bitbacker_url = $("#bitbacker_url").val();
	/***/
	var crypto1_bool = $("#crypto1_yn").is(":checked");
	if (crypto1_bool == false)
		{ var crypto1_yn = 0; } else { var crypto1_yn = 1; }
	var crypto1_name = $("#crypto1_name").val();
	var crypto1_address = $("#crypto1_address").val();
	var crypto1_qr = $("#crypto1_qr").val();
	var crypto2_bool = $("#crypto2_yn").is(":checked");
	if (crypto2_bool == false)
		{ var crypto2_yn = 0; } else { var crypto2_yn = 1; }
	var crypto2_name = $("#crypto2_name").val();
	var crypto2_address = $("#crypto2_address").val();
	var crypto2_qr = $("#crypto2_qr").val();
	var crypto3_bool = $("#crypto3_yn").is(":checked");
	if (crypto3_bool == false)
		{ var crypto3_yn = 0; } else { var crypto3_yn = 1; }
	var crypto3_name = $("#crypto3_name").val();
	var crypto3_address = $("#crypto3_address").val();
	var crypto3_qr = $("#crypto3_qr").val();
	var crypto4_bool = $("#crypto4_yn").is(":checked");
	if (crypto4_bool == false)
		{ var crypto4_yn = 0; } else { var crypto4_yn = 1; }
	var crypto4_name = $("#crypto4_name").val();
	var crypto4_address = $("#crypto4_address").val();
	var crypto4_qr = $("#crypto4_qr").val();
	$.ajax({
		type: "POST",
		url: "/monetization/save.php",
		data: ({
			information : information,
			patreon_yn : patreon_yn,
			patreon_url : patreon_url,
			paypalme_yn : paypalme_yn,
			paypalme_url : paypalme_url,
			subscribestar_yn : subscribestar_yn,
			subscribestar_url : subscribestar_url,
			bitbacker_yn : bitbacker_yn,
			bitbacker_url : bitbacker_url,
			crypto1_yn : crypto1_yn,
			crypto1_name : crypto1_name,
			crypto1_address : crypto1_address,
			crypto1_qr : crypto1_qr,
			crypto2_yn : crypto2_yn,
			crypto2_name : crypto2_name,
			crypto2_address : crypto2_address,
			crypto2_qr : crypto2_qr,
			crypto3_yn : crypto3_yn,
			crypto3_name : crypto3_name,
			crypto3_address : crypto3_address,
			crypto3_qr : crypto3_qr,
			crypto4_yn : crypto4_yn,
			crypto4_name : crypto4_name,
			crypto4_address : crypto4_address,
			crypto4_qr : crypto4_qr,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				window.location.replace("/monetization/");
			} else {
				$("#monetization-error").html(error);
			}
		},
		error: function() {
			$("#monetization-error").html("Error calling save.php.");
		}
	});
});
</script>
');
}
/*****************************************************************************/

HTMLStart ('Monetization', 'Account', 'Monetization', 0, FALSE);
print ('<h1>Monetization</h1>');
if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To tweak monetization, first <a href="/signin/">sign in</a>.');
} else {
	LinkBack ('/account/', 'Account');

	/*** Saved note. ***/
	if ((isset ($_SESSION['fst']['monetization-saved'])) &&
		($_SESSION['fst']['monetization-saved'] == 1))
	{
		print ('<div class="note saved">Saved.</div>');
		unset ($_SESSION['fst']['monetization-saved']);
	}

	FormMon();
}
HTMLEnd();
?>
