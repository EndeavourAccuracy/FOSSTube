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
function FormDelete ()
/*****************************************************************************/
{
	$iUserID = intval ($_SESSION['fst']['user_id']);
	$sUsername = $_SESSION['fst']['user_username'];

print ('
<span style="display:block;">
Optionally, to help improve the website, you may explain why you are leaving:
<br>
<textarea id="reason" style="width:600px; max-width:100%; height:70px;"></textarea>
<br>
Do you really want to delete your account "' . $sUsername . '", <span style="color:#f00;">including all its videos, texts, comments and posts</span>?
</span>
<input id="confirm" type="checkbox"> I confirm.
<div id="delete-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="cancel" value="Cancel" onclick="javascript:window.location.href=\'/account/\';">
<input type="button" id="delete" value="Yes, delete">
<span style="display:block; margin-top:10px; color:#00f;">
Tip: if you want to allow other users to take over (some of) your videos, then first select the <a href="/videos/" style="color:#00f;">videos</a>, then use "Delete checked", and finally check the option to "allow other users to adopt the videos by semi-deleting".
</span>
');

print ('
<script>
$("#delete").click(function(){
	var reason = $("#reason").val();
	if ($("#confirm").is(":checked"))
		{ var confirm = 1; } else { var confirm = 0; }
	$.ajax({
		type: "POST",
		url: "/leave/leave.php",
		data: ({
			reason : reason,
			confirm : confirm,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				window.location.replace("/signout/");
			} else {
				$("#delete-error").html(error);
			}
		},
		error: function() {
			$("#delete-error").html("Error calling leave.php.");
		}
	});
});
</script>
');
}
/*****************************************************************************/

HTMLStart ('Leave', 'Account', 'Leave', 0, FALSE);
print ('<h1>Leave</h1>');

if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To delete your account, first <a href="/signin/">sign in</a>.');
} else {
	if ((strtoupper ($_SERVER['REQUEST_METHOD']) === 'POST') &&
		($_POST['confirm'] == 'yes'))
	{
		LinkBack ('/account/', 'Account');
		FormDelete();
	} else {
		print ('On the <a href="/account/">Account</a> page,' .
			' select "Delete account".');
	}
}

HTMLEnd();
?>
