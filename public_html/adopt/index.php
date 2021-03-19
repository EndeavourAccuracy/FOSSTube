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
function FormAdopt ()
/*****************************************************************************/
{
	$query_deleted = "SELECT
			fv.video_id,
			fu.user_username,
			fv.video_title,
			fv.video_views,
			fv.video_deletedate,
			(SELECT COUNT(*) FROM `fst_likevideo` WHERE (video_id = fv.video_id)) AS likes,
			(SELECT COUNT(*) FROM `fst_comment` WHERE (video_id = fv.video_id) AND comment_hidden='0') AS comments
		FROM `fst_video` fv
		LEFT JOIN `fst_user` fu
			ON fv.user_id = fu.user_id
		WHERE (video_deleted='5')
		AND (video_deletedate BETWEEN (NOW() - INTERVAL 2 DAY) AND NOW())
		AND (video_360='1')
		ORDER BY video_deletedate";
	$result_deleted = Query ($query_deleted);
	$iRows = mysqli_num_rows ($result_deleted);
	if ($iRows == 0)
	{
		print ('Currently, there are no videos to adopt.');
	} else {
print ('
<span style="display:block; margin-bottom:10px;">
You may adopt the following semi-deleted video(s). <a target="_blank" href="/faq/#Q3"><img src="/images/icon_info.png" alt="info" style="vertical-align:middle;"></a>
</span>
');

		while ($row_deleted = mysqli_fetch_assoc ($result_deleted))
		{
			$iVideoID = intval ($row_deleted['video_id']);
			$sCode = IDToCode ($iVideoID);
			$sUsername = $row_deleted['user_username'];
			$sVideoTitle = $row_deleted['video_title'];
			$iVideoViews = $row_deleted['video_views'];
			$sVideoDelDT = $row_deleted['video_deletedate'];
			$sVideoDelDate = date ('j F Y (H:i)', strtotime ($sVideoDelDT));
			$iLikes = intval ($row_deleted['likes']);
			$iComments = intval ($row_deleted['comments']);

			$sLink = ' (<a target="_blank" href="' .
				VideoURL ($sCode, '360') . '">watch</a>, ' . $iLikes . ' likes, ' .
				$iComments . ' comments, ' . $iVideoViews . ' views)';

print ('
<span style="display:block;">
<input type="checkbox" name="adopt-' . $sCode . '"> ' . $sVideoDelDate .
	' - "' . Sanitize ($sVideoTitle) . '" by ' . $sUsername . $sLink . '
</span>
');
		}

print ('
<div id="adopt-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="adopt" value="Adopt selected" style="margin-top:10px;">

<script>
$("#adopt").click(function (event){
	var codes = [];
	$("input[name^=\"adopt-\"]").each(function(){
		if ($(this).is(":checked"))
		{
			var code = $(this).attr("name").replace("adopt-","");
			codes.push(code);
		}
	});

	$.ajax({
		type: "POST",
		url: "/adopt/adopt.php",
		data: ({
			codes : codes,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				window.location.replace("/adopt/");
			} else {
				$("#adopt-error").html(error);
			}
		},
		error: function() {
			$("#adopt-error").html("Error calling adopt.php.");
		}
	});
});
</script>
');
	}
}
/*****************************************************************************/

HTMLStart ('Adopt', 'About', 'Adopt', 0, FALSE);
print ('<h1>Adopt</h1>');
if (IsMod()) { print ('Not as a mod.'); HTMLEnd(); exit(); }
if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To adopt, first <a href="/signin/">sign in</a>.');
} else {
	LinkBack ('/faq/', 'FAQ');

	/*** Adopted note. ***/
	if ((isset ($_SESSION['fst']['adopted'])) &&
		($_SESSION['fst']['adopted'] == 1))
	{
		print ('<div class="note report">Adopted.</div>');
		unset ($_SESSION['fst']['adopted']);
	}

	FormAdopt();
}
HTMLEnd();
?>
