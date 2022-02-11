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
function Unmute ($iUserID)
/*****************************************************************************/
{
	$query_muted = "SELECT
			fm.mute_id,
			fu.user_username
		FROM `fst_mute` fm
		LEFT JOIN `fst_user` fu
			ON fm.mute_user_commenter = fu.user_id
		WHERE (mute_user_publisher='" . $iUserID . "')
		ORDER BY fu.user_username";
	$result_muted = Query ($query_muted);
	if (mysqli_num_rows ($result_muted) != 0)
	{
		while ($row_muted = mysqli_fetch_assoc ($result_muted))
		{
			$iMuteID = intval ($row_muted['mute_id']);
			$sUsername = $row_muted['user_username'];

			print ('<span style="display:block; margin-bottom:5px;">');
			print ('<input type="checkbox" id="muteid-' . $iMuteID . '" value="' .
				$iMuteID . '"> <a href="/user/' . $sUsername . '">' .
				Sanitize ($sUsername) . '</a>');
			print ('</span>');
		}
		print ('<div id="unmute-error" style="color:#f00;"></div>');
		print ('<input type="button" id="unmute" value="Unmute checked"' .
			' style="margin-top:10px;">');

print ('
<script>
$("#unmute").click(function(){
	$("#unmute").prop("value","Wait...");
	$("#unmute").prop("disabled",true).css("opacity","0.5");

	var muteids = [];
	$("input[id^=\"muteid-\"]").each(function(){
		if ($(this).is(":checked"))
		{
			var muteid = $(this).attr("id").replace("muteid-","");
			muteids.push(muteid);
		}
	});

	$.ajax({
		type: "POST",
		url: "/unmute/unmute.php",
		data: ({
			muteids : muteids,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				window.location.replace("/unmute/");
			} else {
				$("#unmute").prop("value","Unmute checked");
				$("#unmute").removeAttr("disabled").css("opacity","1");
				$("#unmute-error").html(error);
			}
		},
		error: function() {
			$("#unmute").prop("value","Unmute checked");
			$("#unmute").removeAttr("disabled").css("opacity","1");
			$("#unmute-error").html("Error calling unmute.php.");
		}
	});
});
</script>
');
	} else {
		print ('Currently, you have nobody muted.');
	}
}
/*****************************************************************************/

HTMLStart ('Unmute', 'Account', 'Unmute', 0, FALSE);
print ('<h1>Unmute</h1>');
if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To unmute, first <a href="/signin/">sign in</a>.');
} else {
	$iUserID = intval ($_SESSION['fst']['user_id']);

	/*** Unmuted note. ***/
	if ((isset ($_SESSION['fst']['unmuted'])) &&
		($_SESSION['fst']['unmuted'] == 1))
	{
		print ('<div class="note unmuted">Unmuted.</div>');
		unset ($_SESSION['fst']['unmuted']);
	}

	LinkBack ('/account/', 'Account');
	Unmute ($iUserID);
}
HTMLEnd();
?>
