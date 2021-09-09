<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.3 (September 2021)
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
function FormPref ()
/*****************************************************************************/
{
	$query_pref = "SELECT
			user_pref_nsfw,
			user_pref_cwidth,
			user_pref_tsize,
			user_pref_musers
		FROM `fst_user`
		WHERE (user_id='" . $_SESSION['fst']['user_id'] . "')";
	$result_pref = Query ($query_pref);
	$row_pref = mysqli_fetch_assoc ($result_pref);
	$iNSFW = intval ($row_pref['user_pref_nsfw']);
	if ($iNSFW == 1)
		{ $iCheckedNSFW = ' checked'; } else { $iCheckedNSFW = ''; }
	$iCWidth = intval ($row_pref['user_pref_cwidth']);
	$iTSize = intval ($row_pref['user_pref_tsize']);
	$sMUsers = $row_pref['user_pref_musers'];

	print ('<hr class="fst-hr" style="margin:10px 0;">');

	print ('<h2>General settings</h2>');

print ('
<input type="checkbox" id="nsfw_yn"' . $iCheckedNSFW . '> Show <abbr title="not safe for work">NSFW</abbr> content.
');

	/*** cwidth ***/
	print ('<span style="display:block; margin-top:10px;">');
	print ('<label for="cwidth" class="lbl">Home container width:</label>');
	print ('<select id="cwidth">');
	for ($iLoopCWidth = 0; $iLoopCWidth <= 13; $iLoopCWidth++)
	{
		print ('<option value="' . $iLoopCWidth . '"');
		if ($iCWidth == $iLoopCWidth) { print (' selected'); }
		print ('>' . CWidth ($iLoopCWidth) . '</option>');
	}
	print ('</select>');
	print ('</span>');

	/*** tsize ***/
	print ('<span style="display:block; margin-top:10px;">');
	print ('<label for="tsize" class="lbl">Thumbnail size:</label>');
	print ('<select id="tsize">');
	for ($iLoopTSize = 100; $iLoopTSize >= 50; $iLoopTSize-=10)
	{
		print ('<option value="' . $iLoopTSize . '"');
		if ($iTSize == $iLoopTSize) { print (' selected'); }
		print ('>' . TSize ($iLoopTSize) . '</option>');
	}
	print ('</select>');
	print ('</span>');

	print ('<hr class="fst-hr" style="margin:10px 0;">');

print ('
<h2 style="margin-top:10px;">
Fewer notifications about certain users
</h2>
');

	/*** musers ***/
print ('
<p>To get fewer on-site notifications about certain users, add their usernames below. You will no longer be notified about their replies to your comments, and no longer receive their requests. Note that you will still be notified about comments they add under content you have published (unless you muted them), and about their new content if you subscribed to them.</p>
<label for="musers" class="lbl">
Place each username on a new line:
</label>
<textarea id="musers" style="width:600px; max-width:100%; height:70px;">' .
	Sanitize ($sMUsers) . '</textarea>
');

	print ('<hr class="fst-hr" style="margin:10px 0;">');

print ('
<div id="preferences-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="preferences-save" value="Save">
');

print ('
<script>
$("#preferences-save").click(function(){
	var nsfw_bool = $("#nsfw_yn").is(":checked");
	if (nsfw_bool == false)
		{ var nsfw_yn = 0; } else { var nsfw_yn = 1; }
	var cwidth = $("#cwidth").val();
	var tsize = $("#tsize").val();
	var musers = $("#musers").val();
	$.ajax({
		type: "POST",
		url: "/preferences/save.php",
		data: ({
			nsfw_yn : nsfw_yn,
			cwidth : cwidth,
			tsize : tsize,
			musers : musers,
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
