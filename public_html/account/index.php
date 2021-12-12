<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.4 (December 2021)
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
function FormAccount ()
/*****************************************************************************/
{
	$query_user = "SELECT
			user_email,
			user_username,
			user_avatarset,
			user_information,
			user_regdt
		FROM `fst_user`
		WHERE (user_id='" . $_SESSION['fst']['user_id'] . "')";
	$result_user = Query ($query_user);
	if (mysqli_num_rows ($result_user) == 1)
	{
		$row_user = mysqli_fetch_assoc ($result_user);
		$sEmail = $row_user['user_email'];
		$sUser = $row_user['user_username'];
		$sInfo = $row_user['user_information'];
print ('
<h2>Avatar</h2>
Uploaded avatars are resized to 200x200 and 50x50 PNGs.
<br>
<label for="avatar" class="lbl">Your avatar:</label>
<span style="display:inline-block; border:1px solid #aaa;">
' . GetUserAvatar ($_SESSION['fst']['user_username'], 'large', 0) . '
</span>
<span style="display:inline-block; border:1px solid #aaa;">
' . GetUserAvatar ($_SESSION['fst']['user_username'], 'small', 0) . '
</span>
<br>
<input type="file" id="avatar" name="avatar" accept="image/png, image/jpeg, image/gif">
<div id="avatar-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="avatar-save" value="Save">

<h2 style="margin-top:10px;">Profile</h2>
');

/*** Saved note. ***/
if ((isset ($_SESSION['fst']['profile-saved'])) &&
	($_SESSION['fst']['profile-saved'] == 1))
{
	print ('<div class="note saved">Saved.</div>');
	unset ($_SESSION['fst']['profile-saved']);
}

print ('
This is publicly visible on your <a target="_blank" href="/user/' . $sUser . '">user page</a>.
<label for="information" class="lbl">Information:</label>
<textarea id="information" style="width:600px; max-width:100%; height:70px;">' . Sanitize ($sInfo) . '</textarea>
<div id="profile-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="profile-save" value="Save">

<h2 style="margin-top:10px;">Credentials</h2>
Allowed characters for the password are letters (a-z, A-Z), numbers (0-9), minus (-) and underscore (_).
<br>
<label for="email" class="lbl">New email address:</label>
<input type="text" id="email" value="' . Sanitize ($sEmail) . '">
<br>
<label for="password" class="lbl">New password (10-20 chars):</label>
<input type="password" id="password">
<img id="visibility" src="/images/visibility_off.png" alt="visibility" style="cursor:pointer; vertical-align:middle;">
<div id="account-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="next" value="Next">
<h2 style="margin-top:10px;">Miscellaneous</h2>
<span style="display:block; margin-bottom:10px;">
<a href="/preferences/">Preferences</a>
</span>
<span style="display:block; margin-bottom:10px;">
<a href="/monetization/">Monetization</a>
</span>
<span style="display:block; margin-bottom:10px;">
<a href="/history/">History</a> / <a href="/subscriptions/">Subscriptions</a> / <a href="/following/">Following</a> / <a href="/unmute/">Unmute</a>
</span>
<span style="display:block; margin-bottom:10px;">
<a href="/rename/">Rename username</a>
</span>
<form id="frm-delete" action="/leave/" method="POST">
<input name="confirm" type="hidden" value="yes">
<a id="delete" href="javascript:;">Delete account</a>
</form>
');

print ('
<script>
$("#avatar-save").click(function (event) {
	var avatar = new FormData();
	avatar.append("file", $("#avatar").prop("files")[0]);
	avatar.append("csrf_token", "' . $_SESSION['fst']['csrf_token'] . '");
	SaveAvatar (avatar);
});

function SaveAvatar (avatar)
{
	$.ajax({
		type: "POST",
		url: "/account/save_avatar.php",
		data: avatar,
		dataType: "json",
		cache: false,
		contentType: false,
		processData: false,
		/*** mimeType: "multipart/form-data", ***/
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				location.reload();
			} else {
				$("#avatar-error").html(error);
			}
		},
		error: function() {
			$("#avatar-error").html("Error calling save_avatar.php.");
		}
	});
}

$("#profile-save").click(function(){
	var information = $("#information").val();
	$.ajax({
		type: "POST",
		url: "/account/save_profile.php",
		data: ({
			information : information,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				window.location.replace("/account/");
			} else {
				$("#profile-error").html(error);
			}
		},
		error: function() {
			$("#profile-error").html("Error calling save_profile.php.");
		}
	});
});

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

$("#next").click(function (event) {
	var email = $("#email").val();
	var password = $("#password").val();
	EmailCode (email, password);
});

function EmailCode (email, password) {
	$.ajax({
		type: "POST",
		url: "/account/email_code.php",
		data: ({
			email : email,
			password : password,
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
				$("#account-error").html(error);
			}
		},
		error: function() {
			$("#account-error").html("Error calling email_code.php.");
		}
	});
}

$("#delete").click(function(){
	$("#frm-delete").submit();
});
</script>
');
	} else {
		print ('Account not found.');
		exit();
	}
}
/*****************************************************************************/
function FormValidate ()
/*****************************************************************************/
{
print ('
Enter (copy-paste) the verification code we sent to <span style="color:#00f;">' . $_SESSION['fst']['email'] . '</span>.
<br>
If you don\'t see it: a) check your spam folder, and b) confirm the email address you entered (see above) is valid.
<br>
If you requested a code multiple times, use only the last, because it differs each time.
<br>
<input type="text" id="code" autofocus>
<div id="account-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="back" value="Back">
<input type="button" id="next" value="Next">
');

print ('
<script>
$("#back").click(function (event) {
	BackAccount();
});

$("#next").click(function (event) {
	var code = $("#code").val();
	ValidateCode (code);
});

function ValidateCode (code) {
	$.ajax({
		type: "POST",
		url: "/account/validate_code.php",
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
				$("#account-error").html(error);
			}
		},
		error: function() {
			$("#account-error").html("Error calling validate_code.php.");
		}
	});
}
</script>
');
}
/*****************************************************************************/
function Finished ()
/*****************************************************************************/
{
	$_SESSION['fst']['step_account'] = 1;
	print ('Done.');
}
/*****************************************************************************/

HTMLStart ('Account', 'Account', 'Account', 0, FALSE);
print ('<h1>Account</h1>');
if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To modify your account, first <a href="/signin/">sign in</a>.');
} else {
	if (!isset ($_SESSION['fst']['step_account']))
		{ $_SESSION['fst']['step_account'] = 1; }
	switch ($_SESSION['fst']['step_account'])
	{
		case 1: FormAccount(); break;
		case 2: FormValidate(); break;
		case 3: Finished(); break;
		default: exit ('Unknown step.'); break;
	}
}
HTMLEnd();
?>
