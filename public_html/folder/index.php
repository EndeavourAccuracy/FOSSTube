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

if (isset ($_GET['folder']))
{
	$iFolderID = intval ($_GET['folder']);

	$query_folder = "SELECT
			ff.user_id,
			fu.user_username,
			ff.folder_title,
			ff.folder_description,
			ff.folder_public
		FROM `fst_folder` ff
		LEFT JOIN `fst_user` fu
			ON ff.user_id = fu.user_id
		WHERE (folder_id='" . $iFolderID . "')";
	$result_folder = Query ($query_folder);
	if (mysqli_num_rows ($result_folder) == 1)
	{
		$row_folder = mysqli_fetch_assoc ($result_folder);
		$iUserID = intval ($row_folder['user_id']);
		$sUsername = $row_folder['user_username'];
		$sTitle = $row_folder['folder_title'];
		$sDesc = $row_folder['folder_description'];
		$iPublic = intval ($row_folder['folder_public']);

		$bShow = FALSE;
		if ($iPublic == 0)
		{
			if (isset ($_SESSION['fst']['user_id']))
				{ $iVisitorID = $_SESSION['fst']['user_id']; } else { $iVisitorID = 0; }
			if (($iVisitorID == $iUserID) || (IsAdmin())) { $bShow = TRUE; }
		} else { $bShow = TRUE; }

		if ($bShow === TRUE)
		{
			HTMLStart ('Folder "' . $sTitle . '"', '', '', 0, FALSE);
			print ('<h1>Folder "' . Sanitize ($sTitle) . '"</h1>');
			if ($iPublic == 0)
			{
				print ('<span style="display:block; margin-bottom:20px;"><span style="font-size:20px;">This is currently a private folder that only you can access.</span><br><span style="font-style:italic;">(Content can NOT be hidden sitewide by putting it in private folders.)</span></span>');
			}
			print ('<p>User <a href="/user/' . Sanitize ($sUsername) . '">' .
				Sanitize ($sUsername) . '</a> created this folder.</p>');
			if ($sDesc != '')
			{
				print ('<div id="folder-desc">' .
					nl2br (Sanitize ($sDesc)) . '</div>');
			}

/*** $iNSFW (and nsfw-div) ***/
$query_nsfw = "SELECT
		COUNT(*) AS nsfw
	FROM `fst_folderitem` ffi
	LEFT JOIN `fst_video` fv
		ON ffi.video_id = fv.video_id
	WHERE (folder_id='" . $iFolderID . "')
	AND (video_deleted='0')
	AND ((video_360='1') OR (video_istext='1'))
	AND (video_nsfw<>'0')";
$result_nsfw = Query ($query_nsfw);
$row_nsfw = mysqli_fetch_assoc ($result_nsfw);
if ($row_nsfw['nsfw'] != 0)
{
	/*** Folder has non-SFW video(s). ***/
	if (Pref ('user_pref_nsfw') == 1)
	{
		$iNSFW = 3;
	} else {
		$iNSFW = 0;
		print ('<div id="nsfw-div">Showing only SFW content.<br><a href="/preferences/">Preferences</a></div>');
	}
} else {
	/*** Folder has NO non-SFW video(s). ***/
	$iNSFW = 3;
}

			print ('<div id="items" style="margin-top:10px;">');
			print ('<img src="/images/loading.gif" alt="loading"></div>');

print ('
<script>
var filters = {};
filters["threshold"] = 0;
filters["nsfw"] = ' . $iNSFW . ';
filters["folder"] = ' . $iFolderID . ';
$(document).ready(function(){ VideosJS ("items", "folder", "itemdesc", 0, "", "", "", filters); });
</script>
');
			HTMLEnd();
		} else {
			HTMLStart ('Private folder', '', '', 0, FALSE);
			print ('<h1>Private folder</h1>');
			print ('This is not a public folder.');
			HTMLEnd();
		}
	} else {
		HTMLStart ('404 Not Found', '', '', 0, FALSE);
		Search ('');
		print ('<h1>404 Not Found</h1>');
		print ('Folder "' . $iFolderID . '" does not exist.');
		HTMLEnd();
	}
} else {
	header ('Location: /');
}
?>
