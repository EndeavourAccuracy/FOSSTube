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
function FormLogin ()
/*****************************************************************************/
{
print ('
<div style="margin-bottom:10px;">
<a href="/signup/">Create account</a>
<br>
<a href="/forgot/">Don\'t remember?</a>
</div>
<label for="username" class="lbl">Username:</label>
<input type="text" id="username" autofocus>
<br>
<label for="password" class="lbl">Password:</label>
<input type="password" id="password">
<img id="visibility" src="/images/visibility_off.png" alt="visibility" style="cursor:pointer; vertical-align:middle;">
<div id="signup-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="next" value="Sign in">
');

print ('
<script>
$("#visibility").click(function (event) {
	var visibility = $("#password").prop("type");
	if (visibility == "password")
	{
		$("#password").prop("type","text");
		$("#visibility").attr("src","/images/visibility_on.png");
	} else {
		$("#password").prop("type","password");
		$("#visibility").attr("src","/images/visibility_off.png");
	}
});

$("#password").keypress(function(e){
	if (e.which == 13) { $("#next").trigger("click"); }
});

$("#next").click(function (event) {
	var username = $("#username").val();
	var password = $("#password").val();
	SignIn (username, password);
});

function SignIn (username, password) {
	$.ajax({
		type: "POST",
		url: "/signin/signin.php",
		data: ({
			username : username,
			password : password
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				window.location.replace("/");
			} else {
				$("#signup-error").html(error);
			}
		},
		error: function() {
			$("#signup-error").html("Error calling signin.php.");
		}
	});
}
</script>
');
}
/*****************************************************************************/

CheckIfBanned();
HTMLStart ('Sign in', 'Account', 'Sign in', 0, FALSE);
print ('<h1>Sign in</h1>');
if (isset ($_SESSION['fst']['user_id']))
{
	$sUsername = $_SESSION['fst']['user_username'];

	print ('You are logged in as "' . $sUsername . '".' . '<br>');
	print ('To sign in with another account, first <a href="/signout/">sign out</a>.');
} else {
	FormLogin();
}
HTMLEnd();
?>
