<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.3 (September 2021)
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
function NotFound ($sUser)
/*****************************************************************************/
{
	HTMLStart ('404 Not Found', '', '', 0, FALSE);
	print ('<h1>404 Not Found</h1>');
	print ('User "' . Sanitize ($sUser) . '" does not exist.');
}
/*****************************************************************************/
function HasBeenMoved ($sUser)
/*****************************************************************************/
{
	/*** 301 Moved Permanently ***/
	header ('Location: ' . $GLOBALS['protocol'] . '://www.' .
		$GLOBALS['domain'] . '/user/' . $sUser, TRUE, 301);
	exit();
}
/*****************************************************************************/
function HasBeenDeleted ($sUser)
/*****************************************************************************/
{
	HTMLStart ('404 Not Found', '', '', 0, FALSE);
	print ('<h1>404 Not Found</h1>');
	print ('User "' . $sUser . '" has been deleted.');
}
/*****************************************************************************/
function Found ($sUser, $row_user)
/*****************************************************************************/
{
	$iUserID = $row_user['user_id'];
	$sInfo = $row_user['user_information'];
	$sRegDate = date ('j F Y', strtotime ($row_user['user_regdt']));

	$arData['video_id'] = 0;
	$arData['user_id'] = $iUserID;
	HTMLStart ($sUser, '', '', $arData, FALSE);
	print ('<h1>' . $sUser . '</h1>');

	if (IsAdmin())
	{
		$sRegIP = $row_user['user_regip'];
		print ('<div style="text-align:center; margin-bottom:10px;">user_regip ' .
			Sanitize ($sRegIP) . '</div>');
	}

print ('
<div class="container-fluid" style="padding:0;">
<div class="row" style="margin:0;">

<div class="col-lg-3" style="padding:0;">
<div style="margin-bottom:10px;">
' . GetUserAvatar ($sUser, 'large', 1) . '
<div>Registered: ' . $sRegDate . '</div>
<div id="report">
<a target="_blank" href="/contact.php?user=' . $sUser . '">
<img src="/images/reported_off.png" title="report user" alt="reported off">
</a>
<a target="_blank" href="/xml/feed.php?user=' . $sUser . '">
<img src="/images/icon_rss.png" title="RSS" alt="RSS">
</a>
<a target="_blank" href="/request/' . $sUser . '/1">
<img src="/images/icon_email.png" title="request email address" alt="request email address">
</a>
<a target="_blank" href="/search/?user=' . Sanitize ($sUser) . '">
<img src="/images/icon_asearch.png" title="advanced search" alt="advanced search">
</a>
</div>
</div>
</div>

<div class="col-lg-9" style="padding:0;">
<div style="margin-bottom:10px;">
');
	if ($sInfo != '')
	{
		print ('<div id="user-info">' . nl2br (Sanitize ($sInfo)) . '</div>');
	}
	Subscribe ($iUserID);
print ('
</div>
</div>

</div>
</div>
');

/*** $iNSFW (and nsfw-div) ***/
$query_nsfw = "SELECT
		COUNT(*) AS nsfw
	FROM `fst_video`
	WHERE (user_id='" . $iUserID . "')
	AND (video_deleted='0')
	AND ((video_360='1') OR (video_istext='1'))
	AND (video_nsfw<>'0')";
$result_nsfw = Query ($query_nsfw);
$row_nsfw = mysqli_fetch_assoc ($result_nsfw);
if ($row_nsfw['nsfw'] != 0)
{
	/*** User has non-SFW video(s). ***/
	if (Pref ('user_pref_nsfw') == 1)
	{
		$iNSFW = 3;
	} else {
		$iNSFW = 0;
		print ('<div id="nsfw-div">Showing only SFW content.<br><a href="/preferences/">Preferences</a></div>');
	}
} else {
	/*** User has NO non-SFW video(s). ***/
	$iNSFW = 3;
}

$query_count = "SELECT
		COUNT(*) AS amount
	FROM `fst_video`
	WHERE (user_id='" . $iUserID . "')
	AND (video_deleted='0')
	AND ((video_360='1') OR (video_istext='1'))";
$result_count = Query ($query_count);
$row_count = mysqli_fetch_assoc ($result_count);
if ($row_count['amount'] > 0)
{
print ('
<div style="margin-bottom:10px;">
<select id="sort">
<option value="datedesc">newest</option>
<option value="dateasc">oldest</option>
<option value="viewsdesc">most views</option>
<option value="viewsasc">least views</option>
<option value="likesdesc">most likes</option>
<option value="likesasc">least likes</option>
<option value="commentsdesc">most comments</option>
<option value="commentsasc">least comments</option>
<option value="secdesc">longest</option>
<option value="secasc">shortest</option>
</select>
</div>
<script>
$("#sort").change(function(){
	$("#videos").html("<img src=\"/images/loading.gif\" alt=\"loading\">");
	var sort = $("#sort option:selected").val();
	var filters = {};
	filters["threshold"] = 0;
	filters["nsfw"] = ' . $iNSFW . ';
	VideosJS ("videos", "user", sort, 0, "' . Sanitize ($sUser) . '", "", "", filters);
});
</script>
');
}

print ('
<div id="videos"><img src="/images/loading.gif" alt="loading"></div>
<script>
var filters = {};
filters["threshold"] = 0;
filters["nsfw"] = ' . $iNSFW . ';
$(document).ready(function(){ VideosJS ("videos", "user", "datedesc", 0, "' . Sanitize ($sUser) . '", "", "", filters); });
</script>
');

	/*** Folders. ***/
	$query_folders = "SELECT
			folder_id,
			folder_title,
			(SELECT COUNT(*) FROM `fst_folderitem` ffi LEFT JOIN `fst_video` fv ON ffi.video_id = fv.video_id WHERE (ffi.folder_id = ff.folder_id) AND (fv.video_deleted='0')) AS items
		FROM `fst_folder` ff
		WHERE (user_id='" . $iUserID . "')
		AND (folder_public='1')
		ORDER BY folder_title ASC, folder_id DESC";
	$result_folders = Query ($query_folders);
	if (mysqli_num_rows ($result_folders) != 0)
	{
		print ('<h2 style="margin-top:10px;">Public folders</h2>');
		while ($row_folders = mysqli_fetch_assoc ($result_folders))
		{
			$iFolderID = intval ($row_folders['folder_id']);
			$sTitle = $row_folders['folder_title'];
			$iItems = intval ($row_folders['items']);
			if ($iItems == 1) { $sItems = 'item'; } else { $sItems = 'items'; }

			print ('<span style="display:block;">');
			print ('<a href="/folder/' . $iFolderID . '">' .
				Sanitize ($sTitle) . '</a> (' . $iItems . ' ' . $sItems . ')');
			print ('</span>');
		}
	}
}
/*****************************************************************************/
function Subscribe ($iUserIDChannel)
/*****************************************************************************/
{
	print ('<div style="margin-top:10px; text-align:right;">');
	if (!isset ($_SESSION['fst']['user_id']))
	{
		$bSubscribed = FALSE;
	} else {
		$query_subscribed = "SELECT
				subscribe_id
			FROM `fst_subscribe`
			WHERE (user_id_channel='" . $iUserIDChannel . "')
			AND (user_id_subscriber='" . $_SESSION['fst']['user_id'] . "')";
		$result_subscribed = Query ($query_subscribed);
		if (mysqli_num_rows ($result_subscribed) == 1)
		{
			$bSubscribed = TRUE;
		} else {
			$bSubscribed = FALSE;
		}
	}
	print ('<img id="subscribe" src="/images/subscribe');
	if ($bSubscribed === TRUE) { print ('d'); }
	print ('.png" alt="subscribe" style="cursor:pointer;">');
	print ('<div id="subscribe-error" style="color:#f00; margin-top:10px;"></div>');
	if (isset ($_SESSION['fst']['user_id']))
	{
		print ('<span style="display:block; font-size:12px;">(<a href="/faq/#Q2">What is this?</a>)</span>');
	}

print ('
<script>
$("#subscribe").click(function(){
	$.ajax({
		type: "POST",
		url: "/user/subscribe.php",
		data: ({
			user_id_channel : "' . $iUserIDChannel . '",
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			var html = data["html"];
			if (result == 1)
			{
				$("#subscribe").attr("src",html);
			} else {
				$("#subscribe-error").html(error);
			}
		},
		error: function() {
			$("#subscribe-error").html("Error calling subscribe.php.");
		}
	});
});
</script>
');

	print ('</div>');
}
/*****************************************************************************/

if (isset ($_GET['username']))
{
	$sUser = $_GET['username'];
	$query_user = "SELECT
			user_id,
			user_username,
			user_username_old1,
			user_username_old2,
			user_information,
			user_deleted,
			user_regip,
			user_regdt
		FROM `fst_user`
		WHERE (user_username='" . mysqli_real_escape_string
			($GLOBALS['link'], $sUser) . "')
		OR (user_username_old1='" . mysqli_real_escape_string
			($GLOBALS['link'], $sUser) . "')
		OR (user_username_old2='" . mysqli_real_escape_string
			($GLOBALS['link'], $sUser) . "')";
	$result_user = Query ($query_user);
	if (mysqli_num_rows ($result_user) != 1)
	{
		NotFound ($sUser);
	} else {
		$row_user = mysqli_fetch_assoc ($result_user);
		$sUserExact = $row_user['user_username'];
		$iDeleted = intval ($row_user['user_deleted']);

		if ($sUserExact != $sUser)
		{
			HasBeenMoved ($sUserExact);
		} else if ($iDeleted == 1) {
			HasBeenDeleted ($sUserExact);
		} else {
			Found ($sUserExact, $row_user);
		}
	}
} else {
	header ('Location: /');
}

HTMLEnd();
?>
