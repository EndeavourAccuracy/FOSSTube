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
function FormRename ()
/*****************************************************************************/
{
	$sUsername = $_SESSION['fst']['user_username'];
	$arOld = OldUsernames ($_SESSION['fst']['user_id']);
	switch ($arOld['count'])
	{
		case 0:
			$sCount = 'You have never renamed your username ("' .
				$sUsername . '").'; break;
		case 1:
			$sCount = 'You renamed your username <span style="color:#f00;">once' .
				'</span> ("' . $arOld['old1'] . '" &gt; "' .
				$sUsername . '").'; break;
		case 2:
			$sCount = 'You renamed your username <span style="color:#f00;">twice' .
				'</span> ("' . $arOld['old1'] . '" &gt; "' .
				$arOld['old2'] . '" &gt; "' . $sUsername . '").'; break;
	}
	$sCurrentURL = $GLOBALS['protocol'] . '://www.' .
		$GLOBALS['domain'] . '/user/' . $sUsername;

print ('
<h2>Introduction</h2>
<p>
<span style="color:#f00;">Each account may rename their username twice.</span>
<br>
' . $sCount . '
<br>
Usernames no longer in use cannot be reclaimed by anyone.
</p>
<p>
Renaming also changes the URL <a href="' . $sCurrentURL . '">' . $sCurrentURL . '</a>
<br>
The old URL will auto-redirect visitors to the new URL.
</p>
<h2 style="margin-top:10px;">Rename</h2>
');

	if ($arOld['count'] == 2)
	{
		print ('To use another username, <a href="/signup/">' .
			'create a new account</a>.');
	} else {
print ('
Allowed characters are letters (a-z, A-Z), numbers (0-9), minus (-) and underscore (_).
<br>
<label for="username" class="lbl">New username (4-15 chars):</label>
<input type="text" id="username" value="' . $sUsername . '" autofocus>
<div id="rename-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="rename-save" value="Save">
');

print ('
<script>
$("#rename-save").click(function(){
	var username = $("#username").val();
	$.ajax({
		type: "POST",
		url: "/rename/save.php",
		data: ({
			username : username,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				window.location.replace("/rename/");
			} else {
				$("#rename-error").html(error);
			}
		},
		error: function() {
			$("#rename-error").html("Error calling save.php.");
		}
	});
});
</script>
');
	}
}
/*****************************************************************************/

HTMLStart ('Rename', 'Account', 'Rename', 0, FALSE);
print ('<h1>Rename</h1>');
if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To rename your username, first <a href="/signin/">sign in</a>.');
} else {
	LinkBack ('/account/', 'Account');

	/*** Saved note. ***/
	if ((isset ($_SESSION['fst']['rename-saved'])) &&
		($_SESSION['fst']['rename-saved'] == 1))
	{
		print ('<div class="note saved">Saved.</div>');
		unset ($_SESSION['fst']['rename-saved']);
	}

	FormRename();
}
HTMLEnd();
?>
