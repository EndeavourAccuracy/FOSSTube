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
function FormThumbnail ($row_video, $sCode)
/*****************************************************************************/
{
print ('
<h2>' . Sanitize ($row_video['video_title']) . '</h2>
Uploaded thumbnails are resized to <a target="_blank" href="https://en.wikipedia.org/wiki/16:9_aspect_ratio">16:9 aspect ratio</a>.
<br>
<span style="color:#f00;">
Your privilege to use this functionality may be revoked if abused.
</span>
<br>
<label for="thumbnail" class="lbl">Custom thumbnail:</label>
<img src="' . ThumbURL ($sCode, '180', 6, TRUE) . '" alt="preview" style="display:block; width:150px; border:1px solid #aaa;">
<input type="file" id="thumbnail" name="thumbnail" accept="image/png, image/jpeg, image/gif">
<div id="thumbnail-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="thumbnail-save" value="Save">
');

print ('
<script>
$("#thumbnail-save").click(function (event) {
	var thumbnail = new FormData();
	thumbnail.append("file", $("#thumbnail").prop("files")[0]);
	thumbnail.append("csrf_token", "' . $_SESSION['fst']['csrf_token'] . '");
	thumbnail.append("code", "' . $sCode . '");
	SaveThumbnail (thumbnail);
});

function SaveThumbnail (thumbnail)
{
	$.ajax({
		type: "POST",
		url: "/thumbnail/save_thumbnail.php",
		data: thumbnail,
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
				$("#thumbnail-error").html(error);
			}
		},
		error: function() {
			$("#thumbnail-error").html("Error calling save_thumbnail.php.");
		}
	});
}
</script>
');
}
/*****************************************************************************/

HTMLStart ('Thumbnail', 'Account', 'Thumbnail', 0, FALSE);
print ('<h1>Thumbnail</h1>');
if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To add a thumbnail, first <a href="/signin/">sign in</a>.');
} else {
	if (isset ($_GET['code']))
	{
		$sCode = $_GET['code'];
		$iVideoID = CodeToID ($sCode);
		$query_video = "SELECT
				video_title
			FROM `fst_video`
			WHERE (user_id='" . $_SESSION['fst']['user_id'] . "')
			AND (video_id='" . $iVideoID . "')";
		$result_video = Query ($query_video);
		if (mysqli_num_rows ($result_video) == 1)
		{
			if (intval (GetUserInfo ($_SESSION['fst']['user_id'],
				'user_priv_customthumbnails')) == 1)
			{
				LinkBack ('/edit/' . $sCode, 'Edit');
				$row_video = mysqli_fetch_assoc ($result_video);
				FormThumbnail ($row_video, $sCode);
			} else {
				print ('Privilege revoked.');
			}
		} else {
			print ('Content "' . Sanitize ($sCode) . '" either does not exist' .
				' or is owned by someone else.');
		}
	} else {
		print ('To add a thumbnail, edit a <a href="/videos/">video</a>' .
			' or <a href="/text/">text</a>.');
	}
}
HTMLEnd();
?>
