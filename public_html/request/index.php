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

/*****************************************************************************/
function Request ($sRecipient, $iType)
/*****************************************************************************/
{
print ('
<h2>Introduction</h2>
<p>
<i>Once per day</i>, you may privately send an information request to another user.
<br>
The other user may approve or discard your request.
<br>
If a request is <i>approved</i>, you will receive the information via an on-site notification.
</p>

<h2>Confirm</h2>
<p>Ask user "' . Sanitize ($sRecipient) . '" for their ' .
	$GLOBALS['request_types'][$iType] . '?</p>
<input type="hidden" id="recipient" value="' . Sanitize ($sRecipient) . '">
<input type="hidden" id="type" value="' . $iType . '">
<div id="request-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="request-save" value="Yes, send request">

<script>
$("#request-save").click(function(){
	var recipient = $("#recipient").val();
	var type = $("#type").val();
	$.ajax({
		type: "POST",
		url: "/request/send.php",
		data: ({
			recipient : recipient,
			type : type,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				window.location.replace("/request/' . Sanitize ($sRecipient) .
					'/' . $iType . '");
			} else {
				$("#request-error").html(error);
			}
		},
		error: function() {
			$("#request-error").html("Error calling send.php.");
		}
	});
});
</script>
');
}
/*****************************************************************************/

if ((isset ($_GET['recipient'])) &&
	(isset ($_GET['type'])))
{
	HTMLStart ('Request', 'Request', 'Request', 0, FALSE);
	print ('<h1>Request</h1>');
	if (!isset ($_SESSION['fst']['user_id']))
	{
		print ('You are not logged in.' . '<br>');
		print ('To send a request, first <a href="/signin/">sign in</a>.');
	} else {
		/*** Sent note. ***/
		if ((isset ($_SESSION['fst']['sent'])) &&
			($_SESSION['fst']['sent'] == 1))
		{
			print ('<div class="note saved">Sent successfully.</div>');
			unset ($_SESSION['fst']['sent']);
		}

		$sRecipient = $_GET['recipient'];
		$iType = intval ($_GET['type']);

		if ((UserExists ($sRecipient) === FALSE) ||
			(!isset ($GLOBALS['request_types'][$iType])))
		{
			print ('Invalid request.');
		} else {
			Request ($sRecipient, $iType);
		}
	}
	HTMLEnd();
} else {
	header ('Location: /');
	exit();
}
?>
