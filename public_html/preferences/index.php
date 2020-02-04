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
function FormPref ()
/*****************************************************************************/
{
	$query_pref = "SELECT
			user_pref_nsfw
		FROM `fst_user`
		WHERE (user_id='" . $_SESSION['fst']['user_id'] . "')";
	$result_pref = Query ($query_pref);
	$row_pref = mysqli_fetch_assoc ($result_pref);
	$iNSFW = intval ($row_pref['user_pref_nsfw']);
	if ($iNSFW == 1)
		{ $iCheckedNSFW = ' checked'; } else { $iCheckedNSFW = ''; }

print ('
<input type="checkbox" id="nsfw_yn"' . $iCheckedNSFW . '> Show NSFW content.
<div id="preferences-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="preferences-save" value="Save">
');

print ('
<script>
$("#preferences-save").click(function(){
	var nsfw_bool = $("#nsfw_yn").is(":checked");
	if (nsfw_bool == false)
		{ var nsfw_yn = 0; } else { var nsfw_yn = 1; }
	$.ajax({
		type: "POST",
		url: "/preferences/save.php",
		data: ({
			nsfw_yn : nsfw_yn,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				window.location.replace("/preferences/");
			} else {
				$("#preferences-error").html(error);
			}
		},
		error: function() {
			$("#preferences-error").html("Error calling save.php.");
		}
	});
});
</script>
');
}
/*****************************************************************************/

HTMLStart ('Preferences', 'Account', 'Preferences', 0, FALSE);
print ('<h1>Preferences</h1>');
if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To tweak preferences, first <a href="/signin/">sign in</a>.');
} else {
	LinkBack ('/account/', 'Account');

	/*** Saved note. ***/
	if ((isset ($_SESSION['fst']['preferences-saved'])) &&
		($_SESSION['fst']['preferences-saved'] == 1))
	{
		print ('<div class="note saved">Saved.</div>');
		unset ($_SESSION['fst']['preferences-saved']);
	}

	FormPref();
}
HTMLEnd();
?>
