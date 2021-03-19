<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.1 (March 2021)
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

include_once (dirname (__FILE__) . '/fst_base.php');

/*****************************************************************************/
function UserEmail ()
/*****************************************************************************/
{
	$sEmail = '';

	if (isset ($_SESSION['fst']['user_id']))
	{
		$iUserID = intval ($_SESSION['fst']['user_id']);
		$query_email = "SELECT
				user_email
			FROM `fst_user`
			WHERE (user_id='" . $iUserID . "')";
		$result_email = Query ($query_email);
		if (mysqli_num_rows ($result_email) == 1)
		{
			$row_email = mysqli_fetch_assoc ($result_email);
			$sEmail = Sanitize ($row_email['user_email']);
		}
	}

	return ($sEmail);
}
/*****************************************************************************/
function ContactForm ()
/*****************************************************************************/
{
	$bReport = FALSE;

	if (isset ($_GET['user']))
	{
		$arUser = UserExists ($_GET['user']);
		if ($arUser !== FALSE)
		{
			print ('<h2>Report user</h2>');
			print ('<div class="note report">');
			print ('Complete the form below to report this user.<br>');
			print ($arUser['username'] . '</div>');
			print ('<input type="hidden" id="user" value="' .
				$arUser['username'] . '">');
print ('
<div class="note warning">
If possible, report a specific video, text or comment instead.<br>That will allow us to more quickly identify the problem and take action.
</div>
');
			ListIssues ('issue_ind_user', 0);
			$bReport = TRUE;
		} else {
			print ('<div class="note error">Unknown user "' .
				Sanitize ($_GET['user']) . '".</div>');
		}
	}

	if (isset ($_GET['video']))
	{
		$sCode = $_GET['video'];
		$arVideo = VideoExists ($sCode);
		if ($arVideo !== FALSE)
		{
			$iIsText = IsText ($sCode);
			switch ($iIsText)
			{
				case 0:
					print ('<h2>Report video</h2>');
					print ('<div class="note report">');
					print ('Complete the form below to report this video.<br>');
					print (Sanitize ($arVideo['title']) . '<br>');
print ('
<span style="display:inline-block; border:1px solid #000; max-width:100%;">
<video style="display:block; max-width:100%;" autoplay loop>
<source src="' . VideoURL ($sCode, 'preview') . '" type="video/mp4">
Your browser or OS does not support HTML5 MP4 video with H.264.
</video>
</span>
');
					print ('</div>');
					print ('<input type="hidden" id="video" value="' .
						Sanitize ($sCode) . '">');
					ListIssues ('issue_ind_video', $arVideo['id']);
print ('
<label for="occursattime" class="lbl"><span style="font-style:italic;">(optional)</span> Occurs at time:</label>
<input type="text" id="occursattime" placeholder="00:00">
<br>
');
					$bReport = TRUE;
					break;
				default: /*** 1, 2, 3 ***/
					print ('<h2>Report content</h2>');
					print ('<div class="note report">');
					print ('Complete the form below to report this content.');
					print ('<span style="display:block; font-size:20px;">');
					print (Sanitize ($arVideo['title']));
					print ('</span>');
					print ('</div>');
					print ('<input type="hidden" id="video" value="' .
						Sanitize ($sCode) . '">');
					if ($iIsText == 3)
					{
						ListIssues ('issue_ind_text_forum', $arVideo['id']);
					} else {
						ListIssues ('issue_ind_text', $arVideo['id']);
					}
					$bReport = TRUE;
					break;
			}
		} else {
			print ('<div class="note error">Unknown video "' .
				Sanitize ($sCode) . '".</div>');
		}
	}

	if (isset ($_GET['comment']))
	{
		$iCommentID = intval ($_GET['comment']);
		$arComment = CommentExists ($iCommentID, 0);
		if ($arComment !== FALSE)
		{
			print ('<h2>Report comment</h2>');
			print ('<div class="note report">');
			print ('Complete the form below to report this comment.');
			print ('<span style="display:block; max-height:200px; overflow:auto;">');
			print (nl2br (Sanitize ($arComment['text'])));
			print ('</span>');
			print ('</div>');
			print ('<input type="hidden" id="comment" value="' .
				$iCommentID . '">');
			ListIssues ('issue_ind_comment', 0);
			$bReport = TRUE;
		} else {
			print ('<div class="note error">Unknown comment "' .
				$iCommentID . '".</div>');
		}
	}

	if ($bReport === TRUE)
	{
		$sButtonValue = 'Report';

print ('
<script>
$("[name=\"problem\"]").click(function(){
	if ($(this).val() == "explicit")
		{ $("#explicit-hint").css("display","block"); }
});
</script>
');
	} else {
		$sButtonValue = 'Send';
	}

	if ($bReport === FALSE)
	{
print ('
<div class="note warning">
This form is <i>not</i> for reporting videos, texts, comments or users.
<br>
If you want to report a problem, such as alleged copyright infringement, go to the video, text, comment or user page, and press a report icon <img src="/images/reported_off.png" alt="reported off"> instead.
</div>
');
	}

VerifyCreate();
print ('
<img src="/captcha.php" alt="x">
<br>
<label for="captcha" class="lbl">Calculate answer:</label>
<input type="text" id="captcha" value="' . AutoCaptcha() . '">
<br>
<label for="email" class="lbl">Your email address:</label>
<input type="text" id="email" value="' . UserEmail() . '">
');

	if ($bReport === FALSE)
	{
print ('
<br>
<label for="message" class="lbl">Message:</label>
<textarea id="message" style="width:600px; max-width:100%; height:150px;"></textarea>
');
	}

print ('
<br>
<input type="checkbox" id="agree"');
	if ((IsAdmin()) || (IsMod())) { print (' checked'); }
print ('> By using "' . $sButtonValue . '", you agree to our <a target="_blank" href="/privacy/">Privacy policy</a>.
<div id="contact-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="cancel" value="Cancel" onclick="javascript:window.location.href=\'/\';">
<input type="button" id="send" value="' . $sButtonValue . '">
');

	if ($bReport === FALSE)
	{
print ('
<span style="display:block; margin-top:10px; color:#00f;">
Tip: see also the <a href="/forum/" style="color:#00f;">Forum</a>.
</span>
');
	}

print ('
<script>
$("#send").click(function (event) {
	var video = $("#video").val();
	if (typeof (video) === "undefined") { video = ""; }
	var comment = $("#comment").val();
	if (typeof (comment) === "undefined") { comment = ""; }
	var user = $("#user").val();
	if (typeof (user) === "undefined") { user = ""; }
	var problem = $("input[name=\"problem\"]:checked").val();
	if (typeof (problem) === "undefined") { problem = ""; }
	var occursattime = $("#occursattime").val();
	if (typeof (occursattime) === "undefined") { occursattime = ""; }
	var captcha = $("#captcha").val();
	var email = $("#email").val();
	var message = $("#message").val();
	if (typeof (message) === "undefined") { message = ""; }
	if ($("#agree").is(":checked"))
		{ var agree = 1; } else { var agree = 0; }
	ProcessReport (video, comment, user, problem, occursattime,
		captcha, email, message, agree);
});

function ProcessReport (video, comment, user, problem, occursattime,
	captcha, email, message, agree) {
	$.ajax({
		type: "POST",
		url: "/process_report.php",
		data: ({
			video : video,
			comment : comment,
			user : user,
			problem : problem,
			occursattime : occursattime,
			captcha : captcha,
			email : email,
			message : message,
			agree : agree,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				window.location.replace("/contact.php?result=success");
			} else {
				$("#contact-error").html(error);
			}
		},
		error: function() {
			$("#contact-error").html("Error calling process_report.php.");
		}
	});
}
</script>
');
}
/*****************************************************************************/
function ContactSent ()
/*****************************************************************************/
{
print ('
Thank you for contacting us.
<br>
Appropriate action(s) will be taken.
<br>
If necessary, we will contact you.
');
}
/*****************************************************************************/

CheckIfBanned();
HTMLStart ('Contact', 'About', 'Contact', 0, FALSE);
print ('<h1>Contact</h1>');

if ((isset ($_GET['result'])) &&
	($_GET['result'] == 'success'))
{
	ContactSent();
} else {
	ContactForm();
}

HTMLEnd();
?>
