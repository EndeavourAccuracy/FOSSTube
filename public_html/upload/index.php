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

include_once (dirname (__FILE__) . '/../fst_base.php');

/*****************************************************************************/

HTMLStart ('Upload', 'Account', 'Upload', 0, FALSE);
print ('<h1>Upload</h1>');
if (IsMod())
	{ print ('Not as a mod.'); HTMLEnd(); exit(); }
if (MayAdd ('videos') === FALSE)
	{ print ('You may not add videos.'); HTMLEnd(); exit(); }
if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To upload videos, first <a href="/signin/">sign in</a>.');
} else {
print ('
<span style="display:block; margin-bottom:10px;">
<span style="color:#f00;">
Unlawful acts, graphic violence, sexually explicit content, and sexy and sexualized minors are not allowed.
</span>
<br>
See the <a href="/terms/">Terms of service</a> for more information.
</span>
<span style="display:block; margin-bottom:10px;">
Each video may be max. ' . GetSizeHuman (AllowedBytes()) . '. (<a href="/faq/#Q1">Not enough?</a>)
</span>
<input type="file" id="multiupload" accept="video/*" multiple>
<div id="upload-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="upload" value="Upload">
<div id="status" style="margin-top:10px; border:1px dashed #414dc5; padding:10px; word-wrap:break-word;">Waiting...</div>
<div id="finished" style="display:none; background-color:#0f0; color:#000;">Uploading has completed. Feel free to leave this page. See your <a href="/videos/" style="color:#000;">videos</a> for their processing status.</div>
');

print ('
<script>
function upload_ajax (total, cl) {
	var file_list = $("#multiupload").prop("files");
	var form_data = new FormData();
	form_data.append ("file", file_list[cl]);
	form_data.append ("csrf_token", "' . $_SESSION['fst']['csrf_token'] . '");

	$.ajax({
		type: "POST",
		url: "/upload/upload.php",
		data: form_data,
		dataType: "json",
		cache: false,
		contentType: false,
		processData: false,
		xhr: function() {
			var xhr = $.ajaxSettings.xhr();
			if (xhr.upload) {
				xhr.upload.addEventListener("progress", function(event) {
					var percent = 0;
					if (event.lengthComputable) {
						percent = Math.ceil((event.loaded / event.total) * 100);
					}
					$("#prog" + cl).text(percent + "%")
				}, false);
			}
			return xhr;
		},
		success: function (data, status) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				$("#prog" + cl).addClass("upload-success");
				$("#prog" + cl).text("Successfully uploaded.");
			} else {
				$("#prog" + cl).addClass("upload-failed");
				$("#prog" + cl).text(error);
			}
			if (cl < total) {
					upload_ajax (total, cl + 1);
			} else {
				$("#multiupload").val("");
				$("#upload").removeAttr("disabled");
				$("#upload").css("opacity",1);
				$("#finished").css("display","block");
			}
		},
		error: function() {
			$("#upload-error").html("Error calling upload.php");
		}
	});
}

$("#upload").click(function() {
	var file_list = $("#multiupload").prop("files");
	if (file_list.length != 0)
	{
		if (file_list.length > 10)
		{
			$("#upload-error").html("Upload 10 or less files at once.");
			return false;
		}

		$("#upload").prop("disabled",true);
		$("#upload").css("opacity",0.5);
		$("#status").html("");
		var iloop;
		for (iloop = 0; iloop < file_list.length; iloop++) {
			$("#status").append("<div>" + file_list[iloop].name + "<span class=\"loading-prep\" id=\"prog" + iloop + "\" style=\"margin-left:10px;\"><img src=\"/images/loading.gif\" alt=\"loading\" style=\"vertical-align:middle;\"></span></div>");
			if (iloop == (file_list.length - 1)) {
				upload_ajax (file_list.length - 1, 0);
			}
		}
	}
});
</script>
');
}
HTMLEnd();
?>
