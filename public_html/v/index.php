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
function Liked ($iVideoID, $bLiked, $sContent)
/*****************************************************************************/
{
	$sHTML = '';

	if ($bLiked === TRUE)
	{
		$sHTML .= '<img src="/images/liked_on.png" title="like ' . $sContent . '" alt="liked on">';
	} else {
$sHTML .= '
<span id="liked-video-' . $iVideoID . '">
<a id="liked-video" href="javascript:;" style="display:inline-block;">
<img src="/images/liked_off.png" title="like ' . $sContent . '" alt="liked off">
</a>
</span>
';
	}
	$sHTML .= '<span id="likes-video" class="likes">';
	$iLikes = LikesVideo ($iVideoID);
	if ($iLikes > 0) { $sHTML .= $iLikes; }
	$sHTML .= '</span>';

	return ($sHTML);
}
/*****************************************************************************/
function RelatedContent ($bByUser, $iUserID, $iVideoID,
	$sTitle, $sTags, $iNSFW)
/*****************************************************************************/
{
	$arStrip = array ('[', ']', '(', ')', '<', '>', '{', '}',
		':', ';', '.', ',', '!', '?', '"', '\'');
	$sTitle = str_replace ($arStrip, ' ', $sTitle);

	if ($bByUser === TRUE)
	{
		$sByUser = " AND (user_id='" . $iUserID . "')";
	} else {
		$sByUser = " AND (user_id<>'" . $iUserID . "')";
	}

	switch ($iNSFW)
	{
		case 0: $sNSFW = "AND (video_nsfw='0')"; break;
		/*** Is it never 1 or 2, and 3 is any. ***/
		default: $sNSFW = ""; break;
	}

	/*** Prepositions and Others (pronouns, possessives, articles, modal verbs, adverbs, and conjunctions). ***/
	$arSkip = array ('to', 'of', 'in', 'for', 'on', 'with', 'at', 'by', 'from', 'up', 'about', 'into', 'over', 'after', 'the', 'and', 'a', 'that', 'i', 'it', 'not', 'he', 'as', 'you', 'this', 'but', 'his', 'they', 'her', 'she', 'or', 'an', 'will', 'my', 'one', 'all', 'would', 'there', 'their');

	print ('<div>');
	$arTitleP = explode (' ', $sTitle);
	$arTagsP = explode (',', $sTags);
	$arP = array();
	foreach ($arTitleP as $sTitleP)
	{
		$sTitleTrim = trim ($sTitleP);
		if ((strlen ($sTitleTrim) > 1) &&
			(!in_array (strtolower ($sTitleTrim), $arSkip)))
			{ array_push ($arP, $sTitleTrim); }
	}
	foreach ($arTagsP as $sTagsP)
	{
		$sTagsTrim = trim ($sTagsP);
		if ((strlen ($sTagsTrim) > 1) &&
			(!in_array (strtolower ($sTagsTrim), $arSkip)))
			{ array_push ($arP, $sTagsTrim); }
	}
	$arP = array_unique ($arP);
	$sSelect = '';
	$sTitleWhere = '';
	$sTagsWhere = '';
	foreach ($arP as $sP)
	{
		$sSelect .= " + ((video_title LIKE '%" . str_replace ('%', '\%',
			mysqli_real_escape_string ($GLOBALS['link'], $sP)) . "%') OR
			(video_tags LIKE '%" . str_replace ('%', '\%',
			mysqli_real_escape_string ($GLOBALS['link'], $sP)) . "%'))";
		$sTitleWhere .= " OR (video_title LIKE '%" . str_replace ('%', '\%',
			mysqli_real_escape_string ($GLOBALS['link'], $sP)) . "%')";
		$sTagsWhere .= " OR (video_tags LIKE '%" . str_replace ('%', '\%',
			mysqli_real_escape_string ($GLOBALS['link'], $sP)) . "%')";
	}
	$query_related = "SELECT
			video_id,
			0" . $sSelect . " as matches
		FROM `fst_video`
		WHERE ((1=0)" . $sTitleWhere . $sTagsWhere . ")
		AND (video_id<>'" . $iVideoID . "')
		" . $sByUser . "
		AND (video_deleted='0')
		AND ((video_360='1') OR (video_istext='1'))
		" . $sNSFW . "
		ORDER BY matches DESC, video_id DESC
		LIMIT 4";
	$result_related = Query ($query_related);
	$iCount = 0;
	while ($row_related = mysqli_fetch_assoc ($result_related))
	{
		$arFilters = array ('threshold' => '0', 'nsfw' => '3');
		$arData = Videos (
			'',
			'',
			" AND (fv.video_id='" . $row_related['video_id'] . "')",
			'datedesc',
			1,
			0,
			'',
			'',
			'',
			0,
			FALSE,
			$arFilters
		);
		print ($arData['html']);
		$iCount++;
	}
	print ('</div>');

	return ($iCount);
}
/*****************************************************************************/
function Monetization ($iUserID, $sUser)
/*****************************************************************************/
{
	$query_mon = "SELECT
			monetization_information,
			monetization_patreon_yn,
			monetization_patreon_url,
			monetization_paypalme_yn,
			monetization_paypalme_url,
			monetization_subscribestar_yn,
			monetization_subscribestar_url,
			monetization_bitbacker_yn,
			monetization_bitbacker_url,
			monetization_crypto1_yn,
			monetization_crypto1_name,
			monetization_crypto1_address,
			monetization_crypto1_qr,
			monetization_crypto2_yn,
			monetization_crypto2_name,
			monetization_crypto2_address,
			monetization_crypto2_qr,
			monetization_crypto3_yn,
			monetization_crypto3_name,
			monetization_crypto3_address,
			monetization_crypto3_qr,
			monetization_crypto4_yn,
			monetization_crypto4_name,
			monetization_crypto4_address,
			monetization_crypto4_qr
		FROM `fst_monetization`
		WHERE (user_id='" . $iUserID . "')";
	$result_mon = Query ($query_mon);
	if (mysqli_num_rows ($result_mon) == 1)
	{
		$row_mon = mysqli_fetch_assoc ($result_mon);
		$sInfo = $row_mon['monetization_information'];
		$iPatreon = intval ($row_mon['monetization_patreon_yn']);
		$sPatreon = $row_mon['monetization_patreon_url'];
		$iPayPalMe = intval ($row_mon['monetization_paypalme_yn']);
		$sPayPalMe = $row_mon['monetization_paypalme_url'];
		$iSubscribeStar = intval ($row_mon['monetization_subscribestar_yn']);
		$sSubscribeStar = $row_mon['monetization_subscribestar_url'];
		$iBitbacker = intval ($row_mon['monetization_bitbacker_yn']);
		$sBitbacker = $row_mon['monetization_bitbacker_url'];
		/***/
		$iCrypto1 = intval ($row_mon['monetization_crypto1_yn']);
		$sCrypto1N = $row_mon['monetization_crypto1_name'];
		$sCrypto1A = $row_mon['monetization_crypto1_address'];
		$iCrypto1QR = intval ($row_mon['monetization_crypto1_qr']);
		$iCrypto2 = intval ($row_mon['monetization_crypto2_yn']);
		$sCrypto2N = $row_mon['monetization_crypto2_name'];
		$sCrypto2A = $row_mon['monetization_crypto2_address'];
		$iCrypto2QR = intval ($row_mon['monetization_crypto2_qr']);
		$iCrypto3 = intval ($row_mon['monetization_crypto3_yn']);
		$sCrypto3N = $row_mon['monetization_crypto3_name'];
		$sCrypto3A = $row_mon['monetization_crypto3_address'];
		$iCrypto3QR = intval ($row_mon['monetization_crypto3_qr']);
		$iCrypto4 = intval ($row_mon['monetization_crypto4_yn']);
		$sCrypto4N = $row_mon['monetization_crypto4_name'];
		$sCrypto4A = $row_mon['monetization_crypto4_address'];
		$iCrypto4QR = intval ($row_mon['monetization_crypto4_qr']);

		if (($iPatreon == 1) || ($iPayPalMe == 1) ||
			($iSubscribeStar == 1) || ($iBitbacker == 1) ||
			($iCrypto1 == 1) || ($iCrypto2 == 1) ||
			($iCrypto3 == 1) || ($iCrypto4 == 1))
		{
print ('
<div class="modal fade" id="donate-modal" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
<h4 class="modal-title">Donate</h4>
</div>
<div class="modal-body" style="text-align:center;">
<span style="display:block; margin-bottom:10px;">Publisher "' . $sUser . '" accepts tips or pledges.<br>To donate, select your preferred processor or cryptocurrency below.</span>
');

			if ($iPatreon == 1)
			{
				print ('<a target="_blank" href="https://www.patreon.com/' . Sanitize ($sPatreon) . '" class="processor-img"><img src="/images/mon/Patreon.png" alt="Patreon"></a>' . "\n");
			}
			if ($iPayPalMe == 1)
			{
				print ('<a target="_blank" href="https://www.paypal.me/' . Sanitize ($sPayPalMe) . '" class="processor-img"><img src="/images/mon/PayPalMe.png" alt="PayPalMe"></a>' . "\n");
			}
			if ($iSubscribeStar == 1)
			{
				print ('<a target="_blank" href="https://www.subscribestar.com/' . Sanitize ($sSubscribeStar) . '" class="processor-img"><img src="/images/mon/SubscribeStar.png" alt="SubscribeStar"></a>' . "\n");
			}
			if ($iBitbacker == 1)
			{
				print ('<a target="_blank" href="https://bitbacker.io/user/' . Sanitize ($sBitbacker) . '" class="processor-img"><img src="/images/mon/Bitbacker.png" alt="Bitbacker"></a>' . "\n");
			}
			if ($iCrypto1 == 1)
			{
				print ('<span class="crypto-blk">');
				print ('<span style="font-style:italic;">' . Sanitize ($sCrypto1N) . '</span><br>' . Sanitize ($sCrypto1A));
				if ($iCrypto1QR == 1) { print ('<br><img src="/qr.php?text=' . Sanitize ($sCrypto1A) . '">'); }
				print ('</span>');
			}
			if ($iCrypto2 == 1)
			{
				print ('<span class="crypto-blk">');
				print ('<span style="font-style:italic;">' . Sanitize ($sCrypto2N) . '</span><br>' . Sanitize ($sCrypto2A));
				if ($iCrypto2QR == 1) { print ('<br><img src="/qr.php?text=' . Sanitize ($sCrypto2A) . '">'); }
				print ('</span>');
			}
			if ($iCrypto3 == 1)
			{
				print ('<span class="crypto-blk">');
				print ('<span style="font-style:italic;">' . Sanitize ($sCrypto3N) . '</span><br>' . Sanitize ($sCrypto3A));
				if ($iCrypto3QR == 1) { print ('<br><img src="/qr.php?text=' . Sanitize ($sCrypto3A) . '">'); }
				print ('</span>');
			}
			if ($iCrypto4 == 1)
			{
				print ('<span class="crypto-blk">');
				print ('<span style="font-style:italic;">' . Sanitize ($sCrypto4N) . '</span><br>' . Sanitize ($sCrypto4A));
				if ($iCrypto4QR == 1) { print ('<br><img src="/qr.php?text=' . Sanitize ($sCrypto4A) . '">'); }
				print ('</span>');
			}
			if ($sInfo != '')
			{
print ('
<span style="display:block;">
The publisher has this to add:
<span id="donate-text">' . nl2br (Sanitize ($sInfo)) . '</span>
</span>
');
			}

print ('
</div>
<div class="modal-footer">
<button type="button" data-dismiss="modal" class="button">Close</button>
</div>
</div>
</div>
</div>
');

			print ('<div id="donate" data-toggle="modal" data-target="#donate-modal">This channel accepts donations. &#x2764; Info &raquo;</div>');
		}
	}
}
/*****************************************************************************/
function AdminBlock ($iVideoID, $sCode, $iIsText, $sTitle, $iCommentsAllow)
/*****************************************************************************/
{
	print ('<div class="admin-div" style="text-align:center; margin-bottom:10px;">');
	print ('<h2>admin</h2>');

	/*** video ID ***/
	print ('<span style="display:block;">video_id ' .
		$iVideoID . '</span>');

	/*** likes ***/
	$query_likes = "SELECT
			fu.user_username,
			fl.likevideo_adddate
		FROM `fst_likevideo` fl
		LEFT JOIN `fst_user` fu
			ON fl.user_id = fu.user_id
		WHERE (video_id='" . $iVideoID . "')
		ORDER BY likevideo_adddate DESC";
	$result_likes = Query ($query_likes);
	if (mysqli_num_rows ($result_likes) != 0)
	{
		print ('<span style="font-style:italic;">Likes:</span>');
		while ($row_likes = mysqli_fetch_assoc ($result_likes))
		{
			$sUsername = $row_likes['user_username'];
			$sAddDT = $row_likes['likevideo_adddate'];
			$sAddDate = date ('j F Y', strtotime ($sAddDT));

			print ('<br>' . $sAddDate . ' - ' . $sUsername);
		}
	}

	/*** rename title, lock topic ***/
	if ($iIsText == 3) /*** Only for forum topics. ***/
	{
print ('
<span id="title-span">
<div id="title-error" style="color:#f00;"></div>
<input type="text" id="title" value="' . Sanitize ($sTitle) . '" maxlength="100" style="margin:0;">
<input type="button" id="title-rename" value="Rename" style="margin:0;">
</span>

<script>
$("#title-rename").click(function(){
	var title = $("#title").val();
	$.ajax({
		type: "POST",
		url: "/v/rename_title.php",
		data: ({
			code : "' . $sCode . '",
			title : title,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				window.location.replace("/v/' . $sCode . '");
			} else {
				$("#title-error").html(error);
			}
		},
		error: function() {
			$("#title-error").html("Error calling rename_title.php.");
		}
	});
});
</script>
');

		print ('<select id="locked" style="margin-top:10px;">');
		print ('<option value="1"');
		if ($iCommentsAllow == 1) { print (' selected'); }
		print ('>Open</option>');
		print ('<option value="0"');
		if ($iCommentsAllow == 0) { print (' selected'); }
		print ('>Locked</option>');
		print ('</select>');

print ('
<script>
$("#locked").change(function(){
	var locked = $("#locked option:selected").val();

	$.ajax({
		type: "POST",
		url: "/v/locked.php",
		data: ({
			code : "' . $sCode . '",
			locked : locked,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				window.location.replace("/v/' . $sCode . '");
			} else {
				alert(error);
			}
		},
		error: function() {
			alert("Error calling locked.php.");
		}
	});
});
</script>
');
	}

	print ('</div>');
}
/*****************************************************************************/
function ModBlock ($iVideoID, $sCode, $iIsText, $iActiveBoard, $iNSFW)
/*****************************************************************************/
{
	print ('<div class="mod-div" style="text-align:center; margin-bottom:10px;">');
	print ('<h2>mod</h2>');

	/*** move ***/
	if ($iIsText == 3)
	{
		print ('<select id="moveto">');
		print ('<option value="">Move topic to board...</option>');
		$query_board = "SELECT
				fb.board_id,
				fb.board_name
			FROM `fst_board` fb
			ORDER BY board_order";
		$result_board = Query ($query_board);
		while ($row_board = mysqli_fetch_assoc ($result_board))
		{
			$iBoardID = intval ($row_board['board_id']);
			$sBoardName = $row_board['board_name'];

			if ($iBoardID != $iActiveBoard)
			{
				print ('<option value="' . $iBoardID . '">' .
					$sBoardName . '</option>');
			}
		}
		print ('</select>');

print ('
<script>
$("#moveto").change(function(){
	var moveto = $("#moveto option:selected").val();

	$.ajax({
		type: "POST",
		url: "/v/moveto.php",
		data: ({
			code : "' . $sCode . '",
			moveto : moveto,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				window.location.replace("/v/' . $sCode . '");
			} else {
				alert(error);
			}
		},
		error: function() {
			alert("Error calling moveto.php.");
		}
	});
});
</script>
');
	}

	/*** (N)SFW ***/
	if ($iIsText != 3)
	{
		print ('<select id="nsfwto">');

		print ('<option value="2"');
		if ($iNSFW == 2) { print (' selected'); }
		print ('>(unknown SFW status)</option>');

		print ('<option value="0"');
		if ($iNSFW == 0) { print (' selected'); }
		print ('>safe for work</option>');

		print ('<option value="1"');
		if ($iNSFW == 1) { print (' selected'); }
		print ('>NOT safe for work</option>');

		print ('</select>');

print ('
<script>
$("#nsfwto").change(function(){
	var nsfwto = $("#nsfwto option:selected").val();

	$.ajax({
		type: "POST",
		url: "/v/nsfwto.php",
		data: ({
			code : "' . $sCode . '",
			nsfwto : nsfwto,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				window.location.replace("/v/' . $sCode . '");
			} else {
				alert(error);
			}
		},
		error: function() {
			alert("Error calling nsfwto.php.");
		}
	});
});
</script>
');
	}

	print ('</div>');
}
/*****************************************************************************/

if (isset ($_GET['code']))
{
	$sCode = $_GET['code'];
	$iVideoID = CodeToID ($sCode);

	/*** Specific comment. ***/
	if (isset ($_GET['comment']))
	{
		$iCommentID = intval ($_GET['comment']);
	} else { $iCommentID = 0; }

	$query_video = "SELECT
			fu.user_id,
			fu.user_username,
			fv.user_id_old,
			fv.video_title,
			fv.video_description,
			fv.video_thumbnail,
			fv.video_tags,
			fv.video_license,
			fc.category_name,
			fv.video_restricted,
			fv.video_comments_allow,
			fl.language_nameeng,
			fv.video_nsfw,
			fv.video_subtitles,
			fv.video_seconds,
			fv.video_fps,
			fv.video_360,
			fv.video_360_width,
			fv.video_360_height,
			fv.video_720,
			fv.video_1080,
			fv.video_views,
			fv.video_deleted,
			fv.video_adddate,
			fv.video_text,
			fv.video_textsavedt,
			fv.video_istext,
			fv.board_id,
			fb.board_name,
			fv.projection_id,
			fp.projection_videojs,
			fv.poll_id
		FROM `fst_video` fv
		LEFT JOIN `fst_user` fu
			ON fv.user_id = fu.user_id
		LEFT JOIN `fst_category` fc
			ON fv.category_id = fc.category_id
		LEFT JOIN `fst_language` fl
			ON fv.language_id = fl.language_id
		LEFT JOIN `fst_board` fb
			ON fv.board_id = fb.board_id
		LEFT JOIN `fst_projection` fp
			ON fv.projection_id = fp.projection_id
		WHERE (video_id='" . $iVideoID . "')
		AND ((video_360='1') OR (video_istext='1') OR (video_istext='3'))";
	$result_video = Query ($query_video);
	if (mysqli_num_rows ($result_video) != 1)
	{
		HTMLStart ('404 Not Found', '', '', 0, FALSE);
		Search ('');
		print ('<h1>404 Not Found</h1>');
		print ('Video "' . Sanitize ($sCode) . '" does not exist.');
	} else {
		$row_video = mysqli_fetch_assoc ($result_video);
		$iDeleted = $row_video['video_deleted'];
		if ($iDeleted != 0)
		{
			HTMLStart ('404 Not Found', '', '', 0, FALSE);
			Search ('');
			print ('<h1>404 Not Found</h1>');
			print ('Content "' . Sanitize ($sCode) . '" has been deleted by');
			switch ($iDeleted)
			{
				case 1: case 2: case 5: print (' its uploader.'); break;
				case 3: case 4: print (' a moderator.'); break;
			}
		} else {
			$iUserID = intval ($row_video['user_id']);
			if (isset ($_SESSION['fst']['user_id']))
				{ $iVisitorID = $_SESSION['fst']['user_id']; }
					else { $iVisitorID = 0; }
			if ($iUserID == $iVisitorID)
				{ $bOwner = TRUE; } else { $bOwner = FALSE; }
			/***/
			$sUser = $row_video['user_username'];
			$iUserIDOld = intval ($row_video['user_id_old']);
			if (($iUserIDOld != 0) && ($iUserIDOld != $iUserID))
				{ $sAdopted = ' <span style="font-style:italic;">(adopted)</span>'; }
					else { $sAdopted = ''; }
			$sTitle = $row_video['video_title'];
			$sDesc = $row_video['video_description'];
			$iThumb = $row_video['video_thumbnail'];
			$sTags = $row_video['video_tags'];
			$iLicense = $row_video['video_license'];
			switch ($iLicense)
			{
				case 1: $sLicense = 'Standard ' . $GLOBALS['name'] . ' license'; break;
				case 2: $sLicense = 'Creative Commons Attribution 3.0'; break;
			}
			$sCategory = $row_video['category_name'];
			if ($sCategory == '') { $sCategory = '<i>(not set)</i>'; }
			$iRestricted = $row_video['video_restricted'];
			$iCommentsAllow = $row_video['video_comments_allow'];
			$sLanguage = $row_video['language_nameeng'];
			$iNSFW = intval ($row_video['video_nsfw']);
			$sSubtitles = $row_video['video_subtitles'];
			$iSeconds = intval ($row_video['video_seconds']);
			$fFPS = floatval ($row_video['video_fps']);
			if ($fFPS == 0)
			{
				$sFPS = '';
			} else {
				$sFPS = '<abbr title="average frames per second">FPS</abbr>: ' .
					$fFPS . ' / ';
			}
			$iProjection = intval ($row_video['projection_id']);
			$sProjection = $row_video['projection_videojs'];
			if ($iProjection == 0)
				{ $sQ360 = '360p'; $sQ720 = '720p'; $sQ1080 = '1080p'; }
					else { $sQ360 = '360s'; $sQ720 = '720s'; $sQ1080 = '1080s'; }
			$iQ360 = $row_video['video_360'];
			$sQuality = 'Size: <a id="q360" href="javascript:;" class="activep">' .
				$sQ360 . '</a>';
			$iEmbedWidth = intval ($row_video['video_360_width']);
			if ($iEmbedWidth == 0) { $iEmbedWidth = 640; }
			$iEmbedHeight = intval ($row_video['video_360_height']);
			if ($iEmbedHeight == 0) { $iEmbedHeight = 360; }
			$iQ720 = $row_video['video_720'];
			if ($iQ720 == 1) { $sQuality .=
				' <a id="q720" href="javascript:;">' . $sQ720 . '</a>'; }
			$iQ1080 = $row_video['video_1080'];
			if ($iQ1080 == 1) { $sQuality .=
				' <a id="q1080" href="javascript:;">' . $sQ1080 . '</a>'; }
			if (($iQ720 == 2) || ($iQ1080 == 2)) { $sQuality .= ' ' . Processing(); }
			$iViews = $row_video['video_views'];
			$sDate = date ('j F Y', strtotime ($row_video['video_adddate']));
			$sText = $row_video['video_text'];
			$sDateSave = date ('j F Y', strtotime ($row_video['video_textsavedt']));
			$iIsText = intval ($row_video['video_istext']);
			$iActiveBoard = intval ($row_video['board_id']);
			$sBoardName = $row_video['board_name'];
			if ($iIsText == 0) { $sContent = 'video'; }
				else { $sContent = 'text'; }
			if ($iIsText == 3) { $sAction = 'Posted'; }
				else { $sAction = 'Published'; }
			if (strtotime ($row_video['video_textsavedt']) >
				strtotime ($row_video['video_adddate'])) { $bModified = TRUE; }
				else { $bModified = FALSE; }
			$iPollID = intval ($row_video['poll_id']);

			IncreaseViews ($iVideoID);

			if ($iIsText != 3)
			{
				HTMLStart ($sTitle, '', '', $iVideoID, FALSE);
			} else {
				HTMLStart ($sTitle, 'Forum', 'Forum', 0, FALSE);
			}
			Search ('');
			if ($iRestricted == 1)
			{
print ('
<div style="margin:10px 0; color:#f00; text-align:center;">
MARKED BY THE PUBLISHER AS ADULT-ONLY CONTENT
</div>
');
			}
			print ('<h1');
			if ($iIsText == '1') { print (' style="font-style:italic;"'); }
			print ('>' . Sanitize ($sTitle) . '</h1>');

			if ($iProjection != 0)
			{
print ('
<div style="margin:10px 0; font-style:italic; text-align:center;">
This is a spherical video. Click+drag to pan around.
</div>
');
			}

			if ($iIsText == 3)
			{
				LinkBack ('/forum/' . $iActiveBoard, 'Board "' . $sBoardName . '"');
			}

			if (IsAdmin())
				{ AdminBlock ($iVideoID, $sCode, $iIsText, $sTitle, $iCommentsAllow); }

			if (IsMod())
				{ ModBlock ($iVideoID, $sCode, $iIsText, $iActiveBoard, $iNSFW); }

			if (isset ($_GET['t']))
			{
				$sTime = $_GET['t'];
				$arTime = explode (',', $sTime);
				$arTime[0] = intval ($arTime[0]);
				if (isset ($arTime[1])) { $arTime[1] = intval ($arTime[1]); }
				$sSeconds = 't=' . $arTime[0];
				if ((isset ($arTime[1])) && ($arTime[1] > $arTime[0]))
				{
					$sSeconds .= ',' . $arTime[1];
					$bLoop = TRUE;
					$sLoopDisplay = 'block';
				} else {
					$bLoop = FALSE;
					$sLoopDisplay = 'none';
				}
				$sSecondsHref = '?' . $sSeconds;
				$sSecondsSrc = '#' . $sSeconds;
				$sAuto = ' autoplay';
				$iStartMin = $arTime[0];
				if ((isset ($arTime[1])) && ($arTime[1] > $arTime[0]))
				{
					$iStartMax = $arTime[1];
				} else {
					$iStartMax = $iSeconds;
				}
			} else {
				$bLoop = FALSE;
				$sLoopDisplay = 'none';
				$sSecondsHref = '';
				$sSecondsSrc = '';
				$sAuto = '';
				$iStartMin = 0;
				$iStartMax = $iSeconds;
			}

			if ($iIsText == 0)
			{
				if (($iNSFW == 0) || ($iNSFW == 2) ||
					(($iNSFW == 1) && (Pref ('user_pref_nsfw') == 1)))
				{
print ('
<div id="video-div" data-hover="no">
<div id="video-cont-div">
<span id="video-cont-span" style="display:none;">
<a id="video-cont-a" href="javascript:;">hide</a>
<script>
$("#video-cont-a").click(function(){
	var hidden = $("#video").data("hidden");
	if (hidden == "no")
	{
		$("#video").css("display","none");
		$("#video-cont-a").text("show");
		$("#video").data("hidden","yes");
	} else {
		$("#video").css("display","block");
		$("#video-cont-a").text("hide");
		$("#video").data("hidden","no");
	}
});
</script>
</span>
');

print ('
<video id="video" poster="' . ThumbURL ($sCode, '720', $iThumb, TRUE) . '" preload="metadata" onloadstart="this.volume=0.5" style="max-width:100%;" data-hidden="no" controls' . $sAuto . ' class="video-js">
<source src="' . VideoURL ($sCode, '360') . $sSecondsSrc . '" type="video/mp4">
');

					if ($sSubtitles != '')
					{ print ('<track default src="/subtitles.php?video=' .
						$sCode . '">' . "\n"); }

print ('Your browser or OS does not support HTML5 MP4 video with H.264.
</video>
');

					if ($iProjection != 0)
					{
print ('
<script src="/videojs/video.min.js"></script>
<script src="/videojs/videojs-vr.min.js"></script>
<link rel="stylesheet" type="text/css" href="/videojs/video-js.min.css">
<link rel="stylesheet" type="text/css" href="/videojs/videojs-vr.css">
<script>
var fvideo = videojs("video");
fvideo.vr({
	projection: "' . $sProjection . '",
	responsive: "true"
});
</script>
');
					}

print ('
</div>
</div>
');
				} else {
					print ('<div id="nsfw-div">This video is NSFW.<br>To view it, change your <a href="/preferences/">preferences</a>.</div>');
				}
			} else if ($iIsText == 1) {
				if (($iNSFW == 0) || ($iNSFW == 2) ||
					(($iNSFW == 1) && (Pref ('user_pref_nsfw') == 1)))
				{
					if ($iThumb == 6)
					{
print ('
<span style="display:block; margin:0 auto; width:640px; max-width:100%;">
<img src="' . ThumbURL ($sCode, '720', $iThumb, TRUE) . '" alt="' . Sanitize ($sTitle) . '" style="width:100%;">
</span>
');
					}

print ('
<span class="text">
' . BBCodeToHTML ($row_video['video_text']) . '
</span>
');
				} else {
					print ('<div id="nsfw-div">This text is NSFW.<br>To view it, change your <a href="/preferences/">preferences</a>.</div>');
				}
			} else if ($iIsText == 3) {
				print ('<span class="text">' . Sanitize ($row_video['video_text']) .
					'</span>');
			}

			print ('<div style="text-align:center;">');

			if (($bOwner === TRUE) && (($iIsText == 0) || ($iIsText == 1)))
			{
				if ($iIsText == 0)
					{ print ('<a href="/edit/' . $sCode . '">Edit</a> | '); }
				if ($iIsText == 1)
				{
					print ('<a href="/text/' . $sCode . '">Compose</a> | ');
					print ('<a href="/edit/' . $sCode . '">Edit</a> | ');
				}
			}

			if ($iIsText == 0)
			{
				/*** Speed ***/
print ('
<span id="speed"><a href="javascript:;">Speed</a></span> | 
<script>
$("#speed").click(function(){
	if ($("#speed-div").css("display") == "none")
		{ $("#speed-div").css("display", "block"); }
			else { $("#speed-div").css("display", "none"); }
});
</script>
');

				/*** Embed and Loop ***/
print ('
<span id="embed"><a href="javascript:;">Embed</a></span> | <span id="loop"><a href="javascript:;">Loop</a></span>
<script>
$("#embed").click(function(){
	if ($("#embed-textarea").css("display") == "none")
		{ $("#embed-textarea").css("display", "block"); }
			else { $("#embed-textarea").css("display", "none"); }
});
$("#loop").click(function(){
	if ($("#loop-div").css("display") == "none")
		{ $("#loop-div").css("display", "block"); }
			else { $("#loop-div").css("display", "none"); }
});
</script>
');
				print (' | ');
				print ('<span id="quality">' . $sQuality . '</span>');
				print (' | ');

print ('
<script>
$("#q360").click(function(){
	Size("360", "' . $iProjection . '", "' . VideoURL ($sCode, '360') . '");
});
</script>
');

if ($iQ720 == 1)
{
print ('
<script>
$("#q720").click(function(){
	Size("720", "' . $iProjection . '", "' . VideoURL ($sCode, '720') . '");
});
</script>
');
}

if ($iQ1080 == 1)
{
print ('
<script>
$("#q1080").click(function(){
	Size("1080", "' . $iProjection . '", "' . VideoURL ($sCode, '1080') . '");
});
</script>
');
}
			}

			/*** Store. ***/
			if ((($iIsText == 0) || ($iIsText == 1)) && ($iVisitorID != 0) &&
				(IsMod() === FALSE))
			{
				$bInFolder = InFolder ($iVideoID, $iVisitorID, 0);
				if ($bInFolder === TRUE)
					{ $sInFolder = 'on'; } else { $sInFolder = 'off'; }
print ('
<a href="/store/' . $sCode . '">
<img src="/images/folder_' . $sInFolder . '.png" title="store ' . $sContent . '" alt="folder ' . $sInFolder . '">
</a>
');
			}

			/*** Delete. ***/
			if (($iIsText == 3) && ($iUserID == $iVisitorID))
			{
print ('
<a id="remove" href="javascript:;"><img src="/images/hidden_off.png" title="remove" alt="hidden off"></a>
<script>
$("#remove").click(function(){
	if (confirm ("Delete topic?")) {
		$.ajax({
			type: "POST",
			url: "/text/delete.php",
			data: ({
				code : "' . $sCode . '",
				csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
			}),
			dataType: "json",
			success: function(data) {
				var result = data["result"];
				var error = data["error"];
				if (result == 1)
				{
					window.location.replace("/forum/");
				} else {
					alert(error);
				}
			},
			error: function() {
				alert("Error calling delete.php.");
			}
		});
	}
});
</script>
');
			}

			/*** Report. ***/
print ('
<a target="_blank" href="/contact.php?video=' . $sCode . '">
<img src="/images/reported_off.png" title="report ' . $sContent . '" alt="reported off">
</a>
');

			/*** Like(d). ***/
			$query_liked = "SELECT
					likevideo_id
				FROM `fst_likevideo`
				WHERE (video_id='" . $iVideoID . "')
				AND (user_id='" . $iVisitorID . "')";
			$result_liked = Query ($query_liked);
			if (mysqli_num_rows ($result_liked) == 1)
				{ $bLiked = TRUE; } else { $bLiked = FALSE; }
			print (Liked ($iVideoID, $bLiked, $sContent));

			print ('</div>');

			if ($iIsText != 3) { Monetization ($iUserID, $sUser); }

			$sBaseURL = $GLOBALS['protocol'] . '://www.' .
				$GLOBALS['domain'] . '/v/' . $sCode;

/*** Speed ***/
print ('
<div id="speed-div" style="display:none; text-align:center;">
<a id="speed-025" href="javascript:;">0.25</a> · 
<a id="speed-050" href="javascript:;">0.5</a> · 
<a id="speed-075" href="javascript:;">0.75</a> · 
<a id="speed-100" href="javascript:;">Normal</a> · 
<a id="speed-125" href="javascript:;">1.25</a> · 
<a id="speed-150" href="javascript:;">1.5</a> · 
<a id="speed-175" href="javascript:;">1.75</a> · 
<a id="speed-200" href="javascript:;">2</a>
<script>
function VideoSpeed (speed) {
	var video = document.getElementById("video");
	video.playbackRate=speed;
}
$("#speed-025").click(function(){ VideoSpeed (0.25); });
$("#speed-050").click(function(){ VideoSpeed (0.50); });
$("#speed-075").click(function(){ VideoSpeed (0.75); });
$("#speed-100").click(function(){ VideoSpeed (1.00); });
$("#speed-125").click(function(){ VideoSpeed (1.25); });
$("#speed-150").click(function(){ VideoSpeed (1.50); });
$("#speed-175").click(function(){ VideoSpeed (1.75); });
$("#speed-200").click(function(){ VideoSpeed (2.00); });
</script>
</div>
');

print ('
<div id="embed-textarea" style="display:none;">
<textarea onclick="javascript:this.setSelectionRange(0, this.value.length);" style="width:100%; margin-bottom:10px;" readonly>' . Sanitize ('<iframe src="' . $GLOBALS['protocol'] . '://www.' . $GLOBALS['domain'] . '/embed/' . $sCode . '" style="display:block; margin:0 auto; width:' . $iEmbedWidth . 'px; max-width:100%; height:' . ($iEmbedHeight + 20) . 'px; border:1px solid #999;" allowfullscreen></iframe>') . '</textarea>
</div>
<div id="loop-div" style="display:' . $sLoopDisplay . ';">
<div id="loop-range" style="margin:50px 30px 10px 30px;"></div>
<div id="loop-url" style="text-align:center;" data-code="' . $sCode . '" data-baseurl="' . $sBaseURL . '"><a href="/v/' . $sCode . $sSecondsHref . '">' . $sBaseURL . $sSecondsHref . '</a></div>
<script>
var range = document.getElementById("loop-range");
function LoopSection(){
	var values = range.noUiSlider.get();
	if ($("#video").prop("currentTime") < values[0]) {
		$("#video").prop("currentTime", values[0]);
		$("#video").get(0).play();
	}
	if ($("#video").prop("currentTime") > values[1]) {
		$("#video").prop("currentTime", values[0]);
		$("#video").get(0).play();
	}
}
');

			if ($bLoop === TRUE)
			{
print ('
$("#video").on("loadedmetadata", function(){
	if (typeof iFirstLoop != "undefined") return; iFirstLoop = 1;
	this.addEventListener("timeupdate", LoopSection);
});
');
			}

print ('
$(document).ready(function(){
	noUiSlider.create(range, {
		start: [' . $iStartMin . ', ' . $iStartMax . '],
		connect: true,
		tooltips: [true, true],
		range: {
			"min": 0,
			"max": ' . $iSeconds . '
		}
	});
	range.noUiSlider.on("update", function (values, handle){
		var tooltips = range.querySelectorAll(".noUi-tooltip");
		for (var i = 0; i < tooltips.length; i++)
		{
			tooltips[i].textContent = SecToTime(values[i]);
		}
	});
	range.noUiSlider.on("slide", function (values, handle){
		var video = document.getElementById("video");
		video.currentTime = values[0];
		video.addEventListener("timeupdate", LoopSection);
		var code = $("#loop-url").data("code");
		var baseurl = $("#loop-url").data("baseurl");
		var time = "?t=" + Math.floor(values[0]) + "," + Math.floor(values[1]);
		$("#loop-url").html("<a href=\"" + code + time + "\">" + baseurl + time + "</a>");
	});
});
</script>
</div>

<div>
<span style="display:block; float:left; width:60px;">
' . GetUserAvatar ($sUser, 'small', 1) . '
</span>
<span style="display:block; float:left;">
<a target="_blank" href="/user/' . $sUser . '">' . $sUser . '</a>
');

			/*** (site owner) ***/
			if (($iIsText == 3) && (in_array ($sUser, $GLOBALS['owners']) === TRUE))
				{ print (' <span class="owner">(site owner)</span>'); }

print ('
<br>
' . $sAction . ' on ' . $sDate . '.' . $sAdopted . '
<br>');

			if ($bModified === TRUE)
			{
print ('
Last modified on ' . $sDateSave . '.
<br>');
			}

print ('
' . $sFPS . 'Views: ' . number_format ($iViews) . '
</span>
<span style="display:block; clear:both;"></span>
</div>
');

			if ($iIsText != 3)
			{
print ('
<div class="limit-height" style="overflow-wrap:break-word; overflow:hidden;">' .
	nl2br (Times (Sanitize ($sDesc))) . '</div>
<div style="color:rgba(65,77,197,0.5); font-style:italic;">' .
	Sanitize ($sTags) . '</div>
<div>' . $sLicense . '</div>
<div>Category: ' . $sCategory . '</div>
');
			} else {
print ('
<div style="margin-top:10px; overflow-wrap:break-word;">' .
	nl2br (Sanitize ($sDesc)) . '</div>
');
			}

			if (($sLanguage != NULL) && ($sLanguage != 'English'))
			{
				print ('<div style="color:#414dc5;">Language: ' .
					$sLanguage . '</div>');
			}

			/*** social ***/
			if ($iIsText != 3)
			{
				$sEncURL = rawurlencode ($GLOBALS['protocol'] . '://www.' .
					$GLOBALS['domain'] . '/v/' . $sCode);
				$sEncTitle = rawurlencode ($sTitle);
				print ('<a target="_blank" href="https://www.facebook.com/sharer.php?u=' . $sEncURL . '"><img src="/images/social/facebook.png" alt="Facebook" title="Facebook"></a> ');
				print ('<a target="_blank" href="https://www.reddit.com/submit?url=' . $sEncURL . '&title=' . $sEncTitle . '"><img src="/images/social/reddit.png" alt="Reddit" title="Reddit"></a> ');
				print ('<a target="_blank" href="https://www.twitter.com/intent/tweet?url=' . $sEncURL . '&text=' . $sEncTitle . '"><img src="/images/social/twitter.png" alt="Twitter" title="Twitter"></a> ');
				print ('<a target="_blank" href="https://www.linkedin.com/shareArticle?mini=true&url=' . $sEncURL . '&title=' . $sEncTitle . '"><img src="/images/social/linkedin.png" alt="LinkedIn" title="LinkedIn"></a> ');
				print ('<a target="_blank" href="https://www.tumblr.com/widgets/share/tool?canonicalUrl=' . $sEncURL . '&title=' . $sEncTitle . '"><img src="/images/social/tumblr.png" alt="Tumblr" title="Tumblr"></a> ');
				print ('<a target="_blank" href="https://www.digg.com/submit?url=' . $sEncURL . '"><img src="/images/social/digg.png" alt="Digg" title="Digg"></a> ');
				print ('<a target="_blank" href="https://www.pinterest.com/pin/create/link/?url=' . $sEncURL . '"><img src="/images/social/pinterest.png" alt="Pinterest" title="Pinterest"></a> ');
			}

			if (($iIsText == 3) && ($iPollID != 0))
			{
print ('
<div id="poll-div" data-poll="' . $iPollID . '">
<img src="/images/loading.gif" alt="loading">
</div>
');
			}

print ('
<div id="comments" data-code="' . $sCode . '">
<img src="/images/loading.gif" alt="loading">
</div>
');

			if ($iIsText != 3)
			{
print ('
<hr style="margin:30px 0 0 0; border:none; height:1px; background-color:#999;">
<span style="display:block; margin:10px 0;">
Possibly related content, auto-generated:
</span>
');

				/*** $iNSFWRel ***/
				if (($iNSFW == 0) || ($iNSFW == 2))
				{
					$iNSFWRel = 0;
				} else {
					if (Pref ('user_pref_nsfw') == 1)
					{
						$iNSFWRel = 3;
					} else {
						$iNSFWRel = 0;
					}
				}

				$iCount1 = RelatedContent (TRUE, $iUserID, $iVideoID,
					$sTitle, $sTags, $iNSFWRel);
				$iCount2 = RelatedContent (FALSE, $iUserID, $iVideoID,
					$sTitle, $sTags, $iNSFWRel);
				if (($iCount1 == 0) && ($iCount2 == 0))
					{ print ('<span style="font-style:italic;">Nothing...</span>'); }
			}

print ('
<script>
$("body").on("click", "#liked-video", function(){
	var this_id = $(this).closest("span").attr("id");
	video_id = this_id.replace("liked-video-","");
	LikeVideo (video_id);
});

$("body").on("click", "[name=\"liked\"]", function(){
	var this_id = $(this).closest("span").attr("id");
	comment_id = this_id.replace("liked-","");
	LikeComment (comment_id);
});

function LikeVideo (video_id) {
	$.ajax({
		type: "POST",
		url: "/v/like_video.php",
		data: ({
			video_id : video_id,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			var likes = data["likes"];
			if (result == 1)
			{
				$("#liked-video-" + video_id).html("<img src=\"/images/liked_on.png\" title=\"like ' . $sContent . '\" alt=\"liked on\">");
				$("#likes-video").html(likes);
			} else {
				alert (error);
			}
		},
		error: function() {
			alert ("Error calling like_video.php.");
		}
	});
}

function LikeComment (comment_id) {
	$.ajax({
		type: "POST",
		url: "/v/like_comment.php",
		data: ({
			comment_id : comment_id,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			var likes = data["likes"];
			if (result == 1)
			{
				$("#liked-" + comment_id).html("<img src=\"/images/liked_on.png\" alt=\"liked on\">");
				$("#likes-" + comment_id).html(likes);
			} else {
				alert (error);
			}
		},
		error: function() {
			alert ("Error calling like_comment.php.");
		}
	});
}

$(document).ready(function() {
');

			if (($iIsText == 3) && ($iPollID != 0))
			{
print ('
	Poll();
');
			}
print ('
	Comments (' . $iCommentID . ');
});

$("body").on("click", "[name=\"muted\"]", function(){
	if (confirm ("(Un)mute user?")){
		var this_id = $(this).closest("span").attr("id");
		comment_id = this_id.replace("muted-","");
		var csrf_token = "' . $_SESSION['fst']['csrf_token'] . '";
		CommentToggle ("muted", comment_id, csrf_token);
	}
});

$("body").on("click", "[name=\"loved\"]", function(){
	var this_id = $(this).closest("span").attr("id");
	comment_id = this_id.replace("loved-","");
	var csrf_token = "' . $_SESSION['fst']['csrf_token'] . '";
	CommentToggle ("loved", comment_id, csrf_token);
});

$("body").on("click", "[name=\"pinned\"]", function(){
	var this_id = $(this).closest("span").attr("id");
	comment_id = this_id.replace("pinned-","");
	var csrf_token = "' . $_SESSION['fst']['csrf_token'] . '";
	CommentToggle ("pinned", comment_id, csrf_token);
});

$("body").on("click", "[name=\"hidden\"]", function(){
	if (confirm ("Remove comment?")){
		var this_id = $(this).closest("span").attr("id");
		comment_id = this_id.replace("hidden-","");
		var csrf_token = "' . $_SESSION['fst']['csrf_token'] . '";
		CommentToggle ("hidden", comment_id, csrf_token);
	}
});

$("body").on("click", "[name=\"approved\"]", function(){
	var this_id = $(this).closest("span").attr("id");
	comment_id = this_id.replace("approved-","");
	var csrf_token = "' . $_SESSION['fst']['csrf_token'] . '";
	CommentToggle ("approved", comment_id, csrf_token);
});

$("body").on("click", "[name=\"reply\"]", function(){
	var old_id = $("#comment").parent("div").attr("id");
	var new_id = $(this).closest("div").attr("id");
	$("#" + new_id).html($("#comment"));
	if (old_id == "reply-0")
	{
		$("#reply-0").html("<a href=\"javascript:;\" name=\"reply\" style=\"display:block; margin:20px 0;\">add comment</a>");
	} else {
		$("#" + old_id).html("<a href=\"javascript:;\" name=\"reply\">add reply</a>");
	}
});
</script>
');
		}
	}
} else {
	header ('Location: /');
}

HTMLEnd();
?>
