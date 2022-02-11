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
function FormForgot ()
/*****************************************************************************/
{
VerifyCreate();
print ('
<img src="/captcha.php" alt="x">
<br>
<label for="captcha" class="lbl">Calculate answer:</label>
<input type="text" id="captcha" autofocus>
<br>
<label for="data" class="lbl">Your username or email address:</label>
<input type="text" id="data">
<div id="forgot-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="next" value="Next">
');

print ('
<script>
$("#next").click(function (event) {
	var captcha = $("#captcha").val();
	var data = $("#data").val();
	EmailCode (captcha, data);
});

function EmailCode (captcha, data) {
	$.ajax({
		type: "POST",
		url: "/forgot/email_code.php",
		data: ({
			captcha : captcha,
			data : data,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				location.reload();
			} else {
				$("#forgot-error").html(error);
			}
		},
		error: function() {
			$("#forgot-error").html("Error calling email_code.php.");
		}
	});
}
</script>
');
}
/*****************************************************************************/
function FormValidate ()
/*****************************************************************************/
{
print ('
Enter (copy-paste) the verification code we sent to you. If you don\'t see it, check your spam folder.
<br>
If you requested a code multiple times, use only the last, because it differs each time.
<br>
<input type="text" id="code" autofocus>
<div id="forgot-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="back" value="Back">
<input type="button" id="next" value="Next">
');

print ('
<script>
$("#back").click(function (event) {
	BackForgot();
});

$("#next").click(function (event) {
	var code = $("#code").val();
	ValidateCode (code);
});

function ValidateCode (code) {
	$.ajax({
		type: "POST",
		url: "/forgot/validate_code.php",
		data: ({
			code : code,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				location.reload();
			} else {
				$("#forgot-error").html(error);
			}
		},
		error: function() {
			$("#forgot-error").html("Error calling validate_code.php.");
		}
	});
}
</script>
');
}
/*****************************************************************************/
function FormModify ()
/*****************************************************************************/
{
print ('
Enter the new password for ' . $_SESSION['fst']['user_usernametmp'] . ' (' .
	$_SESSION['fst']['user_emailtmp'] . ').
<br>
Allowed characters are letters (a-z, A-Z), digits (0-9), minus (-) and underscore (_).
<br>
<label for="password" class="lbl">Password (10-20 chars):</label>
<input type="password" id="password" autofocus>
<img id="visibility" src="/images/visibility_off.png" alt="visibility" style="cursor:pointer; vertical-align:middle;">
<div id="forgot-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="back" value="Back">
<input type="button" id="next" value="Save">
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

$("#back").click(function (event) {
	BackForgot();
});

$("#next").click(function (event) {
	var password = $("#password").val();
	$.ajax({
		type: "POST",
		url: "/forgot/save.php",
		data: ({
			password : password,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				window.location.replace("/");
			} else {
				$("#forgot-error").html(error);
			}
		},
		error: function() {
			$("#forgot-error").html("Error calling save.php.");
		}
	});
});
</script>
');
}
/*****************************************************************************/

CheckIfBanned();
HTMLStart ('Forgot', 'Account', 'Forgot', 0, FALSE);
print ('<h1>Forgot</h1>');
LinkBack ('/signin/', 'Sign in');
if (isset ($_SESSION['fst']['user_id']))
{
	$sUsername = $_SESSION['fst']['user_username'];

	print ('You are logged in as "' . $sUsername . '".' . '<br>');
	print ('To use this functionality, first <a href="/signout/">sign out</a>.');
} else {
	if (!isset ($_SESSION['fst']['step_forgot']))
		{ $_SESSION['fst']['step_forgot'] = 1; }
	switch ($_SESSION['fst']['step_forgot'])
	{
		case 1: FormForgot(); break;
		case 2: FormValidate(); break;
		case 3: FormModify(); break;
		default: exit ('Unknown step.'); break;
	}
}
HTMLEnd();
?>
