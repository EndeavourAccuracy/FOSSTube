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
function Processor ($sID, $sWebsite, $sName, $sURLBase, $iChecked, $sValue)
/*****************************************************************************/
{
print ('
<div class="processor">
<label class="lbl"><a target="_blank" href="' . $sWebsite . '">' . $sName . '</a></label>
<input type="checkbox" id="' . $sID . '_yn"');
	if ($iChecked == 1) { print (' checked'); }
print ('> Check to enable
<br>
' . $sURLBase . '<input type="text" id="' . $sID . '_url" value="' . Sanitize ($sValue) . '" style="margin-bottom:0;">
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
			monetization_bitbacker_url
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
	} else {
		$sInfo = '';
		$iPatreon = 0; $sPatreon = '';
		$iPayPalMe = 0; $sPayPalMe = '';
		$iSubscribeStar = 0; $sSubscribeStar = '';
		$iBitbacker = 0; $sBitbacker = '';
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
<h2 style="margin-top:10px;">Save settings</h2>
If one or more processors have been enabled, a link to a monetization pop-up (modal dialog) will be shown under all your published videos and texts.
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
