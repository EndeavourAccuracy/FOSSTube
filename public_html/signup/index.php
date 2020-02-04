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
function FormEmail ()
/*****************************************************************************/
{
VerifyCreate();
print ('
<div style="margin-bottom:10px;">
<a href="/signin/">Sign in</a>
</div>
<img src="/captcha.php" alt="x">
<br>
<label for="captcha" class="lbl">Calculate answer:</label>
<input type="text" id="captcha" autofocus>
<br>
<label for="email" class="lbl">Your email address:</label>
<p style="margin-bottom:5px; font-style:italic;">(You will receive a verification code that you will need to register.)</p>
<input type="text" id="email">
<div id="signup-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="next" value="Next">
');

print ('
<script>
$("#next").click(function (event) {
	var captcha = $("#captcha").val();
	var email = $("#email").val();
	EmailCode (captcha, email);
});

function EmailCode (captcha, email) {
	$.ajax({
		type: "POST",
		url: "/signup/email_code.php",
		data: ({
			captcha : captcha,
			email : email
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				location.reload();
			} else {
				$("#signup-error").html(error);
			}
		},
		error: function() {
			$("#signup-error").html("Error calling email_code.php.");
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
<p style="margin-bottom:5px;">
Enter (copy-paste) the verification code we sent to <span style="color:#00f;">' . $_SESSION['fst']['email'] . '</span>.
<br>
If you don\'t see it: a) check your spam folder, and b) confirm the email address you entered (see above) is valid.
<br>
If you requested a code multiple times, use only the last, because it differs each time.
</p>
<input type="text" id="code" autofocus>
<div id="signup-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="back" value="Back">
<input type="button" id="next" value="Next">
');

print ('
<script>
$("#back").click(function (event) {
	BackSignUp();
});

$("#next").click(function (event) {
	var code = $("#code").val();
	ValidateCode (code);
});

function ValidateCode (code) {
	$.ajax({
		type: "POST",
		url: "/signup/validate_code.php",
		data: ({
			code : code
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				location.reload();
			} else {
				$("#signup-error").html(error);
			}
		},
		error: function() {
			$("#signup-error").html("Error calling validate_code.php.");
		}
	});
}
</script>
');
}
/*****************************************************************************/
function FormRegister ()
/*****************************************************************************/
{
print ('
Enter the username and password for ' . $_SESSION['fst']['email'] . '.
<br>
Allowed characters for both are letters (a-z, A-Z), numbers (0-9), minus (-) and underscore (_).
<br>
<label for="username" class="lbl">Username (4-15 chars):</label>
<input type="text" id="username" autofocus>
<br>
<label for="password" class="lbl">Password (10-20 chars):</label>
<input type="password" id="password">
<img id="visibility" src="/images/visibility_off.png" alt="visibility" style="cursor:pointer; vertical-align:middle;">
<br>
<input type="checkbox" id="agree"> By using "Sign up", you agree to our <a target="_blank" href="/terms/">Terms of service</a> and <a target="_blank" href="/privacy/">Privacy policy</a>.
<div id="signup-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="back" value="Back">
<input type="button" id="next" value="Sign up">
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
	BackSignUp();
});

$("#next").click(function (event) {
	var username = $("#username").val();
	var password = $("#password").val();
	if ($("#agree").is(":checked"))
		{ var agree = 1; } else { var agree = 0; }
	$.ajax({
		type: "POST",
		url: "/signup/register.php",
		data: ({
			username : username,
			password : password,
			agree : agree
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
			$("#signup-error").html("Error calling register.php.");
		}
	});
});
</script>
');
}
/*****************************************************************************/

CheckIfBanned();
HTMLStart ('Create account', 'Account', 'Create account', 0, FALSE);
print ('<h1>Create account</h1>');
if (isset ($_SESSION['fst']['user_id']))
{
	$sUsername = $_SESSION['fst']['user_username'];

	print ('You are logged in as "' . $sUsername . '".' . '<br>');
	print ('To create another account, first <a href="/signout/">sign out</a>.');
} else {
	$sIP = GetIP();
	if (strpos ($sIP, '.') === FALSE)
	{
		/*** IPv6 /64 ***/
		if (strpos ($sIP, '::') !== FALSE)
		{
			/* Zeroes have been omitted.
			 * This would mess up the substr() below.
			 */
			$iColons = substr_count ($sIP, ':');
			switch ($iColons)
			{
				case 7: $sIP = str_replace ('::', ':0:', $sIP); break;
				case 6: $sIP = str_replace ('::', ':0:0:', $sIP); break;
				case 5: $sIP = str_replace ('::', ':0:0:0:', $sIP); break;
				case 4: $sIP = str_replace ('::', ':0:0:0:0:', $sIP); break;
				case 3: $sIP = str_replace ('::', ':0:0:0:0:0:', $sIP); break;
			}
			if (substr ($sIP, -1) == ':') { $sIP .= '0'; }
		}
		preg_match_all ('/:/', $sIP, $arColon, PREG_OFFSET_CAPTURE);
		$sIPLike = substr ($sIP, 0, $arColon[0][3][1] + 1);
		$iIPV = 6;
	} else {
		/*** IPv4 ***/
		$sIPLike = $sIP;
		$iIPV = 4;
	}
	$query_accounts = "SELECT
			COUNT(*) AS accounts
		FROM `fst_user`
		WHERE (user_regip LIKE '" . $sIPLike . "%')";
	$result_accounts = Query ($query_accounts);
	$row_accounts = mysqli_fetch_assoc ($result_accounts);
	$iAccounts = intval ($row_accounts['accounts']);
	if ($iIPV == 6) { $sIPLike .= '...'; }
	if ($iAccounts < $GLOBALS['max_accounts'])
	{
print ('
<span style="display:block; margin-bottom:10px;">
<span style="color:#f00;">
Unlawful acts, graphic violence, sexually explicit content, and sexy and sexualized minors are not allowed.
</span>
<br>
See the <a href="/terms/">Terms of service</a> for more information.
</span>
');
		if ($iAccounts >= $GLOBALS['warn_accounts'])
		{
			print ('<span style="display:block; margin-bottom:10px; color:#f00;">');
			print ('Creating account ' . ($iAccounts + 1) . ' of max. ' .
				$GLOBALS['max_accounts'] . ' from IP "' . $sIPLike . '".');
			print ('</span>');
		}

		if (!isset ($_SESSION['fst']['step_signup']))
			{ $_SESSION['fst']['step_signup'] = 1; }
		switch ($_SESSION['fst']['step_signup'])
		{
			case 1: FormEmail(); break;
			case 2: FormValidate(); break;
			case 3: FormRegister(); break;
			default: exit ('Unknown step.'); break;
		}
	} else {
		print ('Your IP "' . $sIPLike . '" already created ' . $iAccounts .
			' accounts. You may <a href="/signin/">sign in</a>.');
	}
}
HTMLEnd();
?>
