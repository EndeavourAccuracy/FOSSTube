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
function ShowForm ()
/*****************************************************************************/
{
	$query_deleted = "SELECT
			fv.video_id,
			fu.user_username,
			fv.video_title,
			fv.video_360,
			fv.video_views,
			fv.video_deleted,
			fv.video_deletedate,
			(SELECT COUNT(*) FROM `fst_likevideo` WHERE (video_id = fv.video_id)) AS likes,
			(SELECT COUNT(*) FROM `fst_comment` WHERE (video_id = fv.video_id) AND comment_hidden='0') AS comments
		FROM `fst_video` fv
		LEFT JOIN `fst_user` fu
			ON fv.user_id = fu.user_id
		WHERE (video_deleted='1')
		OR (video_deleted='3')
		OR (video_deleted='5')
		ORDER BY video_deletedate";
	$result_deleted = Query ($query_deleted);
	$iRows = mysqli_num_rows ($result_deleted);
	if ($iRows == 0)
	{
		print ('Nothing to purge.');
	} else {
		$bSemi = FALSE;
		print ('<form action="/purge/" method="POST">');
		while ($row_deleted = mysqli_fetch_assoc ($result_deleted))
		{
			$iVideoID = intval ($row_deleted['video_id']);
			$sUsername = $row_deleted['user_username'];
			$sVideoTitle = $row_deleted['video_title'];
			$iVideo360 = $row_deleted['video_360'];
			$iVideoViews = $row_deleted['video_views'];
			$iVideoDeleted = $row_deleted['video_deleted'];
			$sVideoDelDT = $row_deleted['video_deletedate'];
			$sVideoDelDate = date ('j F Y (H:i)', strtotime ($sVideoDelDT));
			$iLikes = intval ($row_deleted['likes']);
			$iComments = intval ($row_deleted['comments']);

			if ($iVideo360 == 1)
			{
				$sCode = IDToCode ($iVideoID);
				$sLink = ' (video_id ' . $iVideoID . ', <a target="_blank" href="' .
					VideoURL ($sCode, '360') . '">360</a>, ' . $iLikes . ' likes, ' .
					$iComments . ' comments, ' . $iVideoViews . ' views)';
			} else { $sLink = ''; }

			if ($iVideoDeleted == 5)
			{
				$bSemi = TRUE;
				$iSemi = 1;
				$sSemi = '<span style="color:#00f;">(semi)</span> ';
			} else { $iSemi = 0; $sSemi = ''; }

print ('
<span style="display:block;">
<input type="checkbox" name="id[' . $iVideoID . ']" data-semi="' . $iSemi . '"> ' . $sSemi . $sVideoDelDate . ' - "' . Sanitize ($sVideoTitle) . '" by ' . $sUsername . $sLink . '
</span>
');
		}

print ('
<div style="margin-top:10px;">
<input type="button" id="all" value="Check all">
<input type="button" id="non-semi" value="Check non-semi">
<input type="submit" value="Purge selected">
');

		if ($bSemi === TRUE)
		{
print ('
<span style="display:block; margin-top:10px; color:#00f;">
Tip: unless there is an urgent reason, wait at least 48 hours before purging entries marked with "(semi)".
</span>
');
		}

print ('
<script>
function Check (semi)
{
	$(\'input[name^="id"]\').each(function(){
		if ((semi == 0) || ($(this).data("semi") == 0))
			{ $(this).prop("checked",true); }
				else { $(this).prop("checked",false); }
	});
}
$("#all").click(function(){ Check(0); });
$("#non-semi").click(function(){ Check(1); });
</script>
</div>
</form>
');
	}
}
/*****************************************************************************/

HTMLStart ('Purge', 'Purge', 'Purge', 0, FALSE);
print ('<h1>Purge</h1>');
if (!IsAdmin())
{
	if (!isset ($_SESSION['fst']['user_id']))
	{
		print ('First, <a href="/signin/">sign in</a> as an admin.');
	} else {
		print ('First, <a href="/signout/">sign out</a>, then sign in as an admin.');
	}
} else {
	if (strtoupper ($_SERVER['REQUEST_METHOD']) === 'POST')
	{
		if ((!isset ($_POST['id'])) || (count ($_POST['id']) == 0))
		{
			print ('Select video(s) to <a href="/purge/">purge</a>.');
		} else {
			foreach ($_POST['id'] as $iVideoID => $value)
			{
				$sCode = IDToCode ($iVideoID);

				print ('Purging #' . $iVideoID . ' (' . $sCode . ')...<br>');
				DeleteFile (ThumbURL ($sCode, '180', 1, TRUE), TRUE);
				DeleteFile (ThumbURL ($sCode, '180', 2, TRUE), TRUE);
				DeleteFile (ThumbURL ($sCode, '180', 3, TRUE), TRUE);
				DeleteFile (ThumbURL ($sCode, '180', 4, TRUE), TRUE);
				DeleteFile (ThumbURL ($sCode, '180', 5, TRUE), TRUE);
				DeleteFile (ThumbURL ($sCode, '180', 6, TRUE), TRUE);
				DeleteFile (ThumbURL ($sCode, '720', 1, TRUE), TRUE);
				DeleteFile (ThumbURL ($sCode, '720', 2, TRUE), TRUE);
				DeleteFile (ThumbURL ($sCode, '720', 3, TRUE), TRUE);
				DeleteFile (ThumbURL ($sCode, '720', 4, TRUE), TRUE);
				DeleteFile (ThumbURL ($sCode, '720', 5, TRUE), TRUE);
				DeleteFile (ThumbURL ($sCode, '720', 6, TRUE), TRUE);
				DeleteFile (VideoURL ($sCode, 'preview'), TRUE);
				DeleteFile (VideoURL ($sCode, '360'), TRUE);
				DeleteFile (VideoURL ($sCode, '720'), TRUE);
				DeleteFile (VideoURL ($sCode, '1080'), TRUE);
				DeleteFile ('/uploads/' . $iVideoID, TRUE);

				$query_update = "UPDATE `fst_video` SET
						video_deleted='2'
					WHERE (video_id='" . $iVideoID . "')
					AND ((video_deleted='1') OR (video_deleted='5'))";
				Query ($query_update);

				$query_update = "UPDATE `fst_video` SET
						video_deleted='4'
					WHERE (video_id='" . $iVideoID . "')
					AND (video_deleted='3')";
				Query ($query_update);
			}
		}
	} else {
		ShowForm();
	}
}
HTMLEnd();
?>
