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
function OwnerCheck ()
/*****************************************************************************/
{
	foreach ($GLOBALS['owners'] as $sOwner)
	{
		if ((in_array ($sOwner, $GLOBALS['admins']) === TRUE) ||
			(in_array ($sOwner, $GLOBALS['mods']) === TRUE))
		{
			print ('<div class="admin-div" style="text-align:center; color:#f00; font-weight:bold;">Owner "' . $sOwner . '" has admin or mod privileges.<br>This creates a potential security risk, because owners are publicly marked as such on the Forum.</div>');
		}
	}
}
/*****************************************************************************/
function UserPick ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
print ('
<form action="/admin/" method="POST">
<input type="submit" name="action" value="Sign in" style="margin-top:0;">
as user
<input type="text" name="user_username" style="margin-bottom:0;" required>
</form>
');
	print ('</div>');
}
/*****************************************************************************/
function SearchIP ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
print ('
<form action="/admin/" method="POST">
<input type="submit" name="action" value="Search IP" style="margin-top:0;">
<input type="text" name="search_ip" style="margin-bottom:0;" required>
<span style="display:block; font-style:italic; font-size:12px;">
Enter either a full address or only part of an address.
</span>
</form>
');
	print ('</div>');
}
/*****************************************************************************/
function SearchEmail ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
print ('
<form action="/admin/" method="POST">
<input type="submit" name="action" value="Search email" style="margin-top:0;">
<input type="text" name="search_email" style="margin-bottom:0;" required>
</form>
');
	print ('</div>');
}
/*****************************************************************************/
function ConvertIDCode ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
print ('
<form action="/admin/" method="POST">
<input type="submit" name="action" value="To video ID" style="margin-top:0;">
<input type="text" name="convert_code" style="margin-bottom:0;" placeholder="code" required>
</form>
<form action="/admin/" method="POST">
<input type="submit" name="action" value="To code">
<input type="text" name="convert_id" style="margin-bottom:0;" placeholder="video ID" required>
</form>
');
	print ('</div>');
}
/*****************************************************************************/
function CreateNotification ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
print ('
<form id="create_notification" action="/admin/" method="POST">
<span style="display:block;">For: <input type="radio" name="to" value="user" checked> user <input type="text" id="user_username" name="user_username"> <input type="radio" name="to" value="all"> all (non-deleted) users</span>
<textarea name="text" style="width:600px; max-width:100%; height:150px;"></textarea>
<input type="submit" id="action-create" name="action" value="Create notification" style="display:block;">
</form>

<script>
$("#action-create").click(function(e) {
	var to = $("#create_notification input[name=to]:checked").val();
	if (to == "user")
	{
		var user = $("#user_username").val();
		var question = "Create for user \"" + user + "\"?";
	} else {
		var question = "Create for ALL users?";
	}
	if (confirm (question)) {
		$("#create_notification").submit();
	} else {
		e.preventDefault();
	}
});
</script>
');
	print ('</div>');
}
/*****************************************************************************/
function UserSwitch ()
/*****************************************************************************/
{
	/*** Modifies session data or returns non-existing username. ***/

	if (isset ($_POST['user_username']))
		{ $sUsername = $_POST['user_username']; }
			else { $sUsername = ''; }
	$arUser = UserExists ($_POST['user_username']);
	if ($arUser === FALSE)
	{
		return ($sUsername);
	} else {
		/*** Session. ***/
		$_SESSION['fst']['user_id'] = $arUser['id'];
		$_SESSION['fst']['user_username'] = $arUser['username'];
		$_SESSION['fst']['user_pref_nsfw'] = $arUser['pref_nsfw'];
		$_SESSION['fst']['user_pref_cwidth'] = $arUser['pref_cwidth'];
		$_SESSION['fst']['user_pref_tsize'] = $arUser['pref_tsize'];
		/***/
		header ('Location: /');
	}
}
/*****************************************************************************/
function DisallowTorLogin ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
print ('
<form action="/admin/" method="POST">
<input type="submit" name="action" value="Disallow Tor login" style="margin-top:0;">
<input type="text" name="url" style="margin-bottom:0;" value="https://check.torproject.org/torbulkexitlist?ip=1.1.1.1" required>
<span style="display:block; font-style:italic; font-size:12px;">
This will ban all IPs listed <a target="_blank" href="https://check.torproject.org/torbulkexitlist?ip=1.1.1.1">here</a>. Another list is available <a target="_blank" href="https://openinternet.io/tor/tor-exit-list.txt">here</a>. Tor users can then still view content but not login to add their own.
</span>
</form>
');
	print ('</div>');
}
/*****************************************************************************/
function BanExitNodes ()
/*****************************************************************************/
{
	if ((!isset ($_POST['url'])) || ($_POST['url'] == ''))
	{
		HTMLStart ('Admin', 'Admin', 'Admin', 0, FALSE);
		print ('<h1>Admin</h1>');
		print ('You did not enter a URL.');
	} else {
		$sURL = $_POST['url'];
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		curl_setopt ($ch, CURLOPT_VERBOSE, 0);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt ($ch, CURLOPT_URL, $sURL);
		$sResult = curl_exec ($ch);
		if ($sResult === FALSE)
		{
			HTMLStart ('Admin', 'Admin', 'Admin', 0, FALSE);
			print ('<h1>Admin</h1>');
			print ('Could not obtain "' . $sURL . '".');
		} else {
			$iAdded = 0; $iInvalid = 0;
			$tLine = strtok ($sResult, "\r\n");
			while ($tLine !== FALSE)
			{
				if ($tLine[0] != '#') /*** comment ***/
				{
					$sIP = strval ($tLine);
					if (filter_var ($sIP, FILTER_VALIDATE_IP))
					{
						$iBanned = BanIP ($sIP, TRUE);
						if ($iBanned == 1) { $iAdded++; }
					} else {
						$iInvalid++;
					}
				}
				$tLine = strtok ("\r\n");
			}
			if ($iInvalid != 0)
				{ $sInvalid = '<span style="color:#f00;">' .
				strval ($iInvalid) . '</span>'; }
					else { $sInvalid = intval ($iInvalid); }
			HTMLStart ('Admin', 'Admin', 'Admin', 0, FALSE);
			print ('<h1>Admin</h1>');
			print ('Banned ' . $iAdded . ' (more) IPs.<br>Invalid: ' . $sInvalid);
		}
	}
}
/*****************************************************************************/
function IPv6 ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	if (defined ('AF_INET6'))
	{
		print ('IPv6 is <span style="color:#008000;">enabled</span>');
	} else {
		print ('IPv6 is <span style="color:#f00;">disabled</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function StatsSettings ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');

	$iHomeSize = disk_free_space ($GLOBALS['home']);
	$sHomeSize = '<span style="display:block;">' . $GLOBALS['home'] . ' = ' .
		GetSizeHuman ($iHomeSize) . ' free space</span>';
	print ($sHomeSize);

	/*** active users ***/
	$query_users = "SELECT
			COUNT(*) AS amount
		FROM `fst_user`
		WHERE (user_deleted='0')";
	$result_users = Query ($query_users);
	$row_users = mysqli_fetch_assoc ($result_users);
	$iAmount = $row_users['amount'];
	$sUsers = '<span style="display:block;">Active users: ' .
		number_format ($iAmount) . '</span>';
	print ($sUsers);

	/*** active videos ***/
	$query_bytes = "SELECT
			COUNT(*) AS amount,
			SUM(video_preview_bytes) +
			SUM(video_360_bytes) +
			SUM(video_720_bytes) +
			SUM(video_1080_bytes) AS bytes,
			SUM(video_seconds) AS seconds,
			SUM(video_views) AS views
		FROM `fst_video`
		WHERE (video_deleted='0')
		AND (video_360='1')";
	$result_bytes = Query ($query_bytes);
	$row_bytes = mysqli_fetch_assoc ($result_bytes);
	$iAmount = $row_bytes['amount'];
	$iBytes = $row_bytes['bytes'];
	$iSeconds = $row_bytes['seconds'];
	$iH = floor ($iSeconds / 3600);
	$iM = floor (($iSeconds / 60) % 60);
	$iS = $iSeconds % 60;
	$iViews = $row_bytes['views'];
	$sVideosSize = '<span style="display:block;">Active videos: ' .
		number_format ($iAmount) .
		' (' . $iH . ' hrs, ' . $iM . ' min, ' . $iS . ' sec; ' .
		number_format ($iViews) . ' views; ' . GetSizeHuman ($iBytes) .
		' used)</span>';
	print ($sVideosSize);

	/*** active texts ***/
	$query_texts = "SELECT
			COUNT(*) AS amount
		FROM `fst_video`
		WHERE (video_deleted='0')
		AND (video_istext='1')";
	$result_texts = Query ($query_texts);
	$row_texts = mysqli_fetch_assoc ($result_texts);
	$iAmountNonForum = $row_texts['amount'];
	/***/
	$query_texts = "SELECT
			COUNT(*) AS amount
		FROM `fst_video`
		WHERE (video_deleted='0')
		AND (video_istext='3')";
	$result_texts = Query ($query_texts);
	$row_texts = mysqli_fetch_assoc ($result_texts);
	$iAmountForum = $row_texts['amount'];
	/***/
	$sTexts = '<span style="display:block;">Active texts: ' . $iAmountNonForum .
		' (non-forum), ' . $iAmountForum . ' (forum)</span>';
	print ($sTexts);

	/*** active comments ***/
	$query_comments = "SELECT
			COUNT(*) AS amount
		FROM `fst_comment`
		WHERE (comment_hidden='0')";
	$result_comments = Query ($query_comments);
	$row_comments = mysqli_fetch_assoc ($result_comments);
	$iAmount = $row_comments['amount'];
	$sComments = '<span style="display:block;">Active comments: ' .
		number_format ($iAmount) . '</span>';
	print ($sComments);

	$iFileSize = GetSizeBytes ($GLOBALS['max_file_size']);
	$sFileSize = '<span style="display:block;">max_file_size = ' .
		GetSizeHuman ($iFileSize) . '</span>';
	print ($sFileSize);

	$iUploadSize = GetSizeBytes (ini_get ('upload_max_filesize'));
	$sUploadSize = '<span style="display:block;">upload_max_filesize = ' .
		GetSizeHuman ($iUploadSize) . '</span>';
	if ($iUploadSize < $iFileSize)
		{ $sUploadSize = '<span style="color:#f00;">' . $sUploadSize . '</span>'; }
	print ($sUploadSize);

	$iPostSize = GetSizeBytes (ini_get ('post_max_size'));
	$sPostSize = '<span style="display:block;">post_max_size = ' .
		GetSizeHuman ($iPostSize) . '</span>';
	if ($iPostSize < $iFileSize)
		{ $sPostSize = '<span style="color:#f00;">' . $sPostSize . '</span>'; }
	print ($sPostSize);

	$arLoad = sys_getloadavg();
	$fPercent1 = round ((($arLoad[0] / $GLOBALS['total_cpu_cores']) * 100), 2);
	$fPercent5 = round ((($arLoad[1] / $GLOBALS['total_cpu_cores']) * 100), 2);
	$fPercent15 = round ((($arLoad[2] / $GLOBALS['total_cpu_cores']) * 100), 2);
	$sLoad = '<span style="display:block;">CPU load = ' .
		$fPercent1 . '% (last minute), ' .
		$fPercent5 . '% (5 min), ' .
		$fPercent15 . '% (15 min)</span>';
	print ($sLoad);

	/*** videos to purge ***/
	$query_topurge = "SELECT
			COUNT(*) AS topurge
		FROM `fst_video`
		WHERE (video_deleted='1')
		OR (video_deleted='3')
		OR (video_deleted='5')";
	$result_topurge = Query ($query_topurge);
	$row_topurge = mysqli_fetch_assoc ($result_topurge);
	$iToPurge = $row_topurge['topurge'];
	$sToPurge = '<span style="display:block;">Videos to purge: ' .
		$iToPurge . '</span>';
	if ($iToPurge > 0)
		{ $sToPurge = '<span style="color:#f00;">' . $sToPurge . '</span>'; }
	print ($sToPurge);

	print ('</div>');
}
/*****************************************************************************/
function UTF8MB4 ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	$query_char = "SHOW VARIABLES LIKE 'char%'";
	$result_char = Query ($query_char);
	while ($row_char = mysqli_fetch_row ($result_char))
	{
		print ($row_char[0] . ': ' . $row_char[1] . '<br>');
		if (($row_char[0] == 'character_set_server') &&
			($row_char[1] != 'utf8mb4'))
		{
			print ('<span style="display:block; color:#f00;">');
			print ('Add "character-set-server=utf8mb4" under "[mysqld]".');
			print ('</span>');
		}
	}
	print ('</div>');

	print ('<div class="admin-div">');
	$query_char = "SHOW VARIABLES LIKE 'coll%'";
	$result_char = Query ($query_char);
	while ($row_char = mysqli_fetch_row ($result_char))
		{ print ($row_char[0] . ': ' . $row_char[1] . '<br>'); }
	print ('</div>');
}
/*****************************************************************************/
function Banned ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Banned IP ranges (limit 50):</span>');
	$query_banned = "SELECT
			banned_ip_from,
			banned_ip_to,
			banned_ip,
			banned_istor,
			banned_dt
		FROM `fst_banned`
		ORDER BY banned_dt DESC
		LIMIT 50";
	$result_banned = Query ($query_banned);
	$iRows = mysqli_num_rows ($result_banned);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_banned = mysqli_fetch_assoc ($result_banned))
		{
			$sIPFrom = inet_ntop ($row_banned['banned_ip_from']);
			$sIPTo = inet_ntop ($row_banned['banned_ip_to']);
			$sIP = inet_ntop ($row_banned['banned_ip']);
			$iIsTor = intval ($row_banned['banned_istor']);
			$sBanDT = $row_banned['banned_dt'];
			$sBanDate = date ('j M Y', strtotime ($sBanDT));

			print ($sIPFrom . ' - ' . $sIPTo);
			print ('<span style="display:inline-block; margin-left:20px;">');
			print ('(' . $sIP);
			if ($iIsTor == 1) { print ('; tor'); }
			print ('; ' . $sBanDate . ')');
			print ('</span>');
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function Accounts ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Recently registered users (limit 10):</span>');
	$query_accounts = "SELECT
			user_email,
			user_username,
			user_warnings_video,
			user_warnings_comment,
			user_warnings_avatar,
			user_deleted,
			user_regdt
		FROM `fst_user`
		ORDER BY user_regdt DESC
		LIMIT 10";
	$result_accounts = Query ($query_accounts);
	$iRows = mysqli_num_rows ($result_accounts);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_accounts = mysqli_fetch_assoc ($result_accounts))
		{
			$sEmail = $row_accounts['user_email'];
			$sUsername = $row_accounts['user_username'];
			$iWarnVideo = $row_accounts['user_warnings_video'];
			$iWarnComment = $row_accounts['user_warnings_comment'];
			$iWarnAvatar = $row_accounts['user_warnings_avatar'];
			$iUserDeleted = $row_accounts['user_deleted'];
			$sRegDT = $row_accounts['user_regdt'];
			$sRegDate = date ('j F Y (H:i)', strtotime ($sRegDT));

			print ('<a href="/user/' . $sUsername . '">' . $sUsername .
				'</a> (' . $sEmail . ') - ' . $sRegDate . ' - Warnings: ' .
				WarningCount ($iWarnVideo) . ' video(s)/text(s), ' .
				WarningCount ($iWarnComment) . ' comment(s), ' .
				WarningCount ($iWarnAvatar) . ' avatar(s)');
			if ($iUserDeleted != 0)
				{ print (' <span style="color:#00f;">deleted</span>'); }
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function Subscriptions ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Recent subscriptions (limit 10):</span>');
	$query_sub = "SELECT
			fu1.user_username AS channel,
			fu2.user_username AS subscriber,
			fs.subscribe_adddate
		FROM `fst_subscribe` fs
		LEFT JOIN `fst_user` fu1
			ON fs.user_id_channel = fu1.user_id
		LEFT JOIN `fst_user` fu2
			ON fs.user_id_subscriber = fu2.user_id
		ORDER BY subscribe_adddate DESC
		LIMIT 10";
	$result_sub = Query ($query_sub);
	$iRows = mysqli_num_rows ($result_sub);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_sub = mysqli_fetch_assoc ($result_sub))
		{
			$sChannel = $row_sub['channel'];
			$sSubscriber = $row_sub['subscriber'];
			$sAddedDT = $row_sub['subscribe_adddate'];
			$sAddedDate = date ('j F Y (H:i)', strtotime ($sAddedDT));

			print ($sSubscriber . ' subscribed to ' .
				$sChannel . ' - ' . $sAddedDate);
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function Searches ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Last 24h searches:</span>');
	$query_search = "SELECT
			search_text,
			search_matchest,
			search_matcheso,
			search_adddate
		FROM `fst_search`
		WHERE (search_adddate > DATE_SUB(NOW(), INTERVAL 1 DAY))
		ORDER BY search_adddate DESC";
	$result_search = Query ($query_search);
	$iRows = mysqli_num_rows ($result_search);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_search = mysqli_fetch_assoc ($result_search))
		{
			$sText = $row_search['search_text'];
			$iMatchesTitle = intval ($row_search['search_matchest']);
			$iMatchesOther = intval ($row_search['search_matcheso']);
			$sAddedDT = $row_search['search_adddate'];
			$sAddedDate = date ('j F Y (H:i)', strtotime ($sAddedDT));

			print ($sAddedDate . ' - ' . Sanitize ($sText) . ': ' .
				$iMatchesTitle . ' (+' . $iMatchesOther . ')');
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function SearchesTop ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Last 7d searches (limit 20):</span>');
	$query_search = "SELECT
			COUNT(*) AS hits,
			search_text
		FROM `fst_search`
		WHERE (DATE(search_adddate) BETWEEN DATE_SUB(NOW(),INTERVAL 1 WEEK) AND NOW())
		GROUP BY search_text
		ORDER BY hits DESC
		LIMIT 20";
	$result_search = Query ($query_search);
	$iRows = mysqli_num_rows ($result_search);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_search = mysqli_fetch_assoc ($result_search))
		{
			$iHits = intval ($row_search['hits']);
			$sText = $row_search['search_text'];

			print ($iHits . ' - ' . strtolower ($sText));
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function Comments ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Last 24h comments:</span>');
	$query_comments = "SELECT
			fc.comment_adddate,
			fu.user_username,
			fc.comment_text,
			fv.video_id,
			fv.video_title,
			fv.video_comments_show,
			fc.comment_hidden,
			fc.comment_approved
		FROM `fst_comment` fc
		LEFT JOIN `fst_video` fv
			ON fc.video_id=fv.video_id
		LEFT JOIN `fst_user` fu
			ON fc.user_id=fu.user_id
		WHERE (comment_adddate > DATE_SUB(NOW(), INTERVAL 1 DAY))
		ORDER BY comment_adddate DESC";
	$result_comments = Query ($query_comments);
	$iRows = mysqli_num_rows ($result_comments);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_comments = mysqli_fetch_assoc ($result_comments))
		{
			$sCommentDT = $row_comments['comment_adddate'];
			$sCommentDate = date ('j F Y (H:i)', strtotime ($sCommentDT));
			$sUsername = $row_comments['user_username'];
			$sText = $row_comments['comment_text'];
			$iVideoID = intval ($row_comments['video_id']);
			$sCode = IDToCode ($iVideoID);
			$sVideoTitle = $row_comments['video_title'];
			$iCommentsShow = intval ($row_comments['video_comments_show']);
			$iCommentHidden = intval ($row_comments['comment_hidden']);
			$iCommentApproved = intval ($row_comments['comment_approved']);

			print ($sUsername . ' - ' . $sCommentDate .
				' - <a href="/v/' . $sCode . '">' .
				Sanitize ($sVideoTitle) . '</a>');
			if ($iCommentHidden != 0)
				{ print (' <span style="color:#00f;">deleted</span>'); }
			if (($iCommentsShow == 2) && ($iCommentApproved == 0))
				{ print (' <span style="color:#00f;">not approved</span>'); }
			print ('<span style="display:block; overflow-wrap:break-word;">' .
				nl2br (Times (Sanitize ($sText))) . '</span>');
			$iRow++;
			if ($iRow != $iRows) { print ('<hr class="fst-hr">'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function Referrers ($iDays)
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Last ' . $iDays . 'd top (10+) referrers:</span>');
	$query_referrers = "SELECT
			fr.referrer_url,
			fr.video_id,
			fv.video_title,
			SUM(fr.referrer_count) AS referrer_count
		FROM `fst_referrer` fr
		LEFT JOIN `fst_video` fv
			ON fr.video_id=fv.video_id
		WHERE (referrer_url NOT IN ('" . $GLOBALS['name'] . "', ''))
		AND (referrer_date BETWEEN (CURRENT_DATE() - INTERVAL " . ($iDays - 1) . " DAY) AND CURRENT_DATE())
		GROUP BY referrer_url, video_id
		HAVING SUM(fr.referrer_count) >= 10
		ORDER BY referrer_count DESC";
	$result_referrers = Query ($query_referrers);
	$iRows = mysqli_num_rows ($result_referrers);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_referrers = mysqli_fetch_assoc ($result_referrers))
		{
			$sRefURL = $row_referrers['referrer_url'];
			$iVideoID = intval ($row_referrers['video_id']);
			if ($iVideoID != 0)
				{ $sSiteURL = '/v/' . IDToCode ($iVideoID); }
					else { $sSiteURL = '/'; }
			$sVideoTitle = $row_referrers['video_title'];
			if ($sVideoTitle == '') { $sVideoTitle = 'Home'; }
			$iRefCount = intval ($row_referrers['referrer_count']);

			print ($sRefURL . ' - <a href="' . $sSiteURL . '">' .
				Sanitize ($sVideoTitle) . '</a> - ' . $iRefCount);
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function CustomThumbnails ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Custom thumbnails (limit 10):</span>');
	$query_thumb = "SELECT
			video_id,
			video_title
		FROM `fst_video`
		WHERE (video_deleted='0')
		AND (video_thumbnail='6')
		ORDER BY video_adddate DESC
		LIMIT 10";
	$result_thumb = Query ($query_thumb);
	$iRows = mysqli_num_rows ($result_thumb);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_thumb = mysqli_fetch_assoc ($result_thumb))
		{
			$iVideoID = intval ($row_thumb['video_id']);
			$sCode = IDToCode ($iVideoID);
			$sVideoTitle = $row_thumb['video_title'];

			print ('<a href="/v/' . $sCode . '">' .
				Sanitize ($sVideoTitle) . '</a>');
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function Monetization ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Monetization:</span>');
	$query_mon = "SELECT
			fu.user_username,
			fm.monetization_patreon_yn,
			fm.monetization_paypalme_yn,
			fm.monetization_subscribestar_yn,
			fm.monetization_bitbacker_yn,
			fm.monetization_crypto1_yn,
			fm.monetization_crypto1_name,
			fm.monetization_crypto2_yn,
			fm.monetization_crypto2_name,
			fm.monetization_crypto3_yn,
			fm.monetization_crypto3_name,
			fm.monetization_crypto4_yn,
			fm.monetization_crypto4_name
		FROM `fst_monetization` fm
		LEFT JOIN `fst_user` fu
			ON fm.user_id = fu.user_id
		WHERE (monetization_patreon_yn='1')
		OR (monetization_paypalme_yn='1')
		OR (monetization_subscribestar_yn='1')
		OR (monetization_bitbacker_yn='1')
		OR (monetization_crypto1_yn='1')
		OR (monetization_crypto2_yn='1')
		OR (monetization_crypto3_yn='1')
		OR (monetization_crypto4_yn='1')
		ORDER BY user_username";
	$result_mon = Query ($query_mon);
	$iRows = mysqli_num_rows ($result_mon);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_mon = mysqli_fetch_assoc ($result_mon))
		{
			$sUsername = $row_mon['user_username'];
			$iPatreon = intval ($row_mon['monetization_patreon_yn']);
			$iPayPalMe = intval ($row_mon['monetization_paypalme_yn']);
			$iSubscribeStar = intval ($row_mon['monetization_subscribestar_yn']);
			$iBitbacker = intval ($row_mon['monetization_bitbacker_yn']);
			$iCrypto1 = intval ($row_mon['monetization_crypto1_yn']);
			$iCrypto2 = intval ($row_mon['monetization_crypto2_yn']);
			$iCrypto3 = intval ($row_mon['monetization_crypto3_yn']);
			$iCrypto4 = intval ($row_mon['monetization_crypto4_yn']);
			$arProc = array();
			if ($iPatreon == 1) { array_push ($arProc, 'Patreon'); }
			if ($iPayPalMe == 1) { array_push ($arProc, 'PayPalMe'); }
			if ($iSubscribeStar == 1) { array_push ($arProc, 'SubscribeStar'); }
			if ($iBitbacker == 1) { array_push ($arProc, 'Bitbacker'); }
			if ($iCrypto1 == 1) { array_push ($arProc,
				$row_mon['monetization_crypto1_name']); }
			if ($iCrypto2 == 1) { array_push ($arProc,
				$row_mon['monetization_crypto2_name']); }
			if ($iCrypto3 == 1) { array_push ($arProc,
				$row_mon['monetization_crypto3_name']); }
			if ($iCrypto4 == 1) { array_push ($arProc,
				$row_mon['monetization_crypto4_name']); }

			print ('<a href="/user/' . $sUsername . '">' .
				$sUsername . '</a>: ' . Sanitize (implode (', ', $arProc)));
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function Renamed ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Renamed usernames:</span>');
	$query_renamed = "SELECT
			user_username,
			user_username_old1,
			user_username_old2
		FROM `fst_user`
		WHERE (user_username_old1<>'')
		ORDER BY user_username";
	$result_renamed = Query ($query_renamed);
	$iRows = mysqli_num_rows ($result_renamed);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_renamed = mysqli_fetch_assoc ($result_renamed))
		{
			$sUsername = $row_renamed['user_username'];
			$sOld1 = $row_renamed['user_username_old1'];
			$sOld2 = $row_renamed['user_username_old2'];

			if ($sOld1 != '') { print ($sOld1 . ' &gt; '); }
			if ($sOld2 != '') { print ($sOld2 . ' &gt; '); }
			print ('<a href="/user/' . $sUsername . '">' . $sUsername . '</a>');
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function Muted ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Muted:</span>');
	$query_muted = "SELECT
			fu1.user_username AS publisher,
			fu2.user_username AS commenter,
			fm.mute_dt
		FROM `fst_mute` fm
		LEFT JOIN `fst_user` fu1
			ON fm.mute_user_publisher = fu1.user_id
		LEFT JOIN `fst_user` fu2
			ON fm.mute_user_commenter = fu2.user_id
		ORDER BY mute_dt DESC";
	$result_muted = Query ($query_muted);
	$iRows = mysqli_num_rows ($result_muted);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_muted = mysqli_fetch_assoc ($result_muted))
		{
			$sPublisher = $row_muted['publisher'];
			$sCommenter = $row_muted['commenter'];
			$sMuteDT = $row_muted['mute_dt'];
			$sMuteDate = date ('j F Y (H:i)', strtotime ($sMuteDT));

			print ('<a href="/user/' . $sCommenter . '">' . $sCommenter .
				'</a> muted by <a href="/user/' . $sPublisher . '">' . $sPublisher .
				'</a> - ' . $sMuteDate);
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function Logins ()
/*****************************************************************************/
{
	$sNotIn = implode ('\', \'',
		array_merge ($GLOBALS['admins'], $GLOBALS['mods']));

	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Recent non-admin/mod logins (limit 10):</span>');
	$query_logins = "SELECT
			user_username,
			user_lastlogindt
		FROM `fst_user`
		WHERE (user_username NOT IN ('" . $sNotIn . "'))
		ORDER BY user_lastlogindt DESC
		LIMIT 10";
	$result_logins = Query ($query_logins);
	$iRows = mysqli_num_rows ($result_logins);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_logins = mysqli_fetch_assoc ($result_logins))
		{
			$sUsername = $row_logins['user_username'];
			$sLastLoginDT = $row_logins['user_lastlogindt'];
			$sLastLoginDate = date ('j F Y (H:i)', strtotime ($sLastLoginDT));

			print ('<a href="/user/' . $sUsername . '">' . $sUsername .
				'</a> - ' . $sLastLoginDate);
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function LeaveReasons ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Leave reasons:</span>');
	$query_reasons = "SELECT
			user_username,
			user_deleted_reason
		FROM `fst_user`
		WHERE (user_deleted='1')
		AND (user_deleted_reason<>'')
		ORDER BY user_lastlogindt DESC";
	$result_reasons = Query ($query_reasons);
	$iRows = mysqli_num_rows ($result_reasons);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_reasons = mysqli_fetch_assoc ($result_reasons))
		{
			$sUsername = $row_reasons['user_username'];
			$sReason = $row_reasons['user_deleted_reason'];

			print ($sUsername . ':<br>' . nl2br (Sanitize ($sReason)));
			$iRow++;
			if ($iRow != $iRows) { print ('<hr class="fst-hr">'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function ShowIPHits ($sIPPart, $sTable, $sColumn, $sShow)
/*****************************************************************************/
{
	print ('<h2>' . $sColumn . ' in `' . $sTable . '`</h2>');
	print ('<div style="margin-bottom:10px;">');
	$query_hits = "SELECT
			" . $sColumn . " AS ip,
			COUNT(" . $sColumn . ") AS hits
		FROM `" . $sTable . "`
		WHERE (" . $sColumn . " LIKE '%" . $sIPPart . "%')
		GROUP BY ip";
	$result_hits = Query ($query_hits);
	while ($row_hits = mysqli_fetch_assoc ($result_hits))
		{ print ($row_hits['ip'] . ' - ' . $row_hits['hits'] . '<br>'); }
	if ($sShow != '')
	{
		print ('<hr class="fst-hr">');
		$query_hits = "SELECT
				" . $sColumn . " AS ip,
				" . $sShow . " AS hit
			FROM `" . $sTable . "`
			WHERE (" . $sColumn . " LIKE '%" . $sIPPart . "%')
			ORDER BY ip";
		$result_hits = Query ($query_hits);
		while ($row_hits = mysqli_fetch_assoc ($result_hits))
		{
			print ($row_hits['ip'] . ' - ' .
				Sanitize ($row_hits['hit']) . '<br>');
		}
	}
	print ('</div>');
}
/*****************************************************************************/
function Adopted ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Adopted videos:</span>');
	$query_adopted = "SELECT
			fv.video_id,
			fu1.user_username AS userto,
			fu2.user_username AS userfrom,
			fv.video_title
		FROM `fst_video` fv
		LEFT JOIN `fst_user` fu1
			ON fu1.user_id = fv.user_id
		LEFT JOIN `fst_user` fu2
			ON fu2.user_id = fv.user_id_old
		WHERE (video_deleted='0')
		AND (user_id_old<>'0')
		ORDER BY video_adddate";
	$result_adopted = Query ($query_adopted);
	$iRows = mysqli_num_rows ($result_adopted);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_adopted = mysqli_fetch_assoc ($result_adopted))
		{
			$iVideoID = $row_adopted['video_id'];
			$sCode = IDToCode ($iVideoID);
			$sUsernameTo = $row_adopted['userto'];
			$sUsernameFrom = $row_adopted['userfrom'];
			$sVideoTitle = $row_adopted['video_title'];

			print ($sUsernameFrom . ' &gt; ' . $sUsernameTo . ' - <a href="/v/' .
				$sCode . '">' . Sanitize ($sVideoTitle) . '</a>');
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function TextDrafts ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Text drafts:</span>');
	$query_drafts = "SELECT
			fu.user_username,
			COUNT(*) AS amount,
			SUM(CHAR_LENGTH(fv.video_text)) AS chars,
			MAX(fv.video_textsavedt) AS modified
		FROM `fst_video` fv
		LEFT JOIN `fst_user` fu
			ON fv.user_id = fu.user_id
		WHERE (fv.video_istext='2')
		AND (fv.video_deleted='0')
		GROUP BY (fv.user_id)
		ORDER BY modified DESC";
	$result_drafts = Query ($query_drafts);
	$iRows = mysqli_num_rows ($result_drafts);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_drafts = mysqli_fetch_assoc ($result_drafts))
		{
			$sUsername = $row_drafts['user_username'];
			$iAmount = intval ($row_drafts['amount']);
			$iChars = intval ($row_drafts['chars']);
			$sDateSave = date ('j F Y', strtotime ($row_drafts['modified']));

			print ('<a href="/user/' . $sUsername . '">' . $sUsername . '</a>' .
				' (' . $iAmount . 'x; ' . number_format ($iChars) . ' chars;' .
				' last update: ' . $sDateSave . ')</a>');
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function Folders ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Recent folders (limit 10):</span>');
	$query_folders = "SELECT
			ff.folder_id,
			fu.user_username,
			ff.folder_title
		FROM `fst_folder` ff
		LEFT JOIN `fst_user` fu
			ON ff.user_id = fu.user_id
		ORDER BY folder_id DESC
		LIMIT 10";
	$result_folders = Query ($query_folders);
	$iRows = mysqli_num_rows ($result_folders);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_folders = mysqli_fetch_assoc ($result_folders))
		{
			$iFolderID = intval ($row_folders['folder_id']);
			$sUsername = $row_folders['user_username'];
			$sTitle = $row_folders['folder_title'];

			print ('<a href="/folder/' . $iFolderID . '">' . Sanitize ($sTitle) .
				'</a> (by ' . $sUsername . ')');
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function IPMultipleAccounts ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">IPs with multiple accounts:</span>');
	$query_multiple = "SELECT
			user_regip,
			GROUP_CONCAT(user_username SEPARATOR ', ') AS usernames
		FROM `fst_user`
		GROUP BY user_regip
			HAVING COUNT(user_regip) > 1
		ORDER BY COUNT(user_regip) DESC";
	$result_multiple = Query ($query_multiple);
	$iRows = mysqli_num_rows ($result_multiple);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_multiple = mysqli_fetch_assoc ($result_multiple))
		{
			$sIP = $row_multiple['user_regip'];
			$sUsernames = $row_multiple['usernames'];

			print ($sIP . ': ' . $sUsernames);
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function SystemMessages ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">System messages (limit 10):</span>');
	$query_system = "SELECT
			fu.user_username,
			fm.message_text,
			fm.message_cleared
		FROM `fst_message` fm
		LEFT JOIN `fst_user` fu
			ON fm.user_id_recipient = fu.user_id
		WHERE (user_id_sender='-1')
		ORDER BY message_adddate DESC
		LIMIT 10";
	$result_system = Query ($query_system);
	$iRows = mysqli_num_rows ($result_system);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_system = mysqli_fetch_assoc ($result_system))
		{
			$sUsername = $row_system['user_username'];
			$sText = $row_system['message_text'];
			$iCleared = intval ($row_system['message_cleared']);

			print ('Sent to ' . $sUsername . '.');
			if ($iCleared == 1)
				{ print (' <span style="color:#00f;">cleared</span>'); }
			print ('<br>');
			print (nl2br (Sanitize ($sText)));
			$iRow++;
			if ($iRow != $iRows) { print ('<hr class="fst-hr">'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function CustomizedSizes ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Customized sizes:</span>');
	$query_custom = "SELECT
			user_username,
			user_pref_cwidth,
			user_pref_tsize
		FROM `fst_user`
		WHERE (user_pref_cwidth <> 0) || (user_pref_tsize <> 80)
		ORDER BY user_username";
	$result_custom = Query ($query_custom);
	$iRows = mysqli_num_rows ($result_custom);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_custom = mysqli_fetch_assoc ($result_custom))
		{
			$sUsername = $row_custom['user_username'];
			$sCWidth = CWidth ($row_custom['user_pref_cwidth']);
			$sTSize = TSize ($row_custom['user_pref_tsize']);

			print ('<a href="/user/' . $sUsername . '">' .
				$sUsername . '</a>: ' . $sCWidth . ' / ' . $sTSize);
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function MostLiked ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Most liked (limit 10):</span>');
	$query_likes = "SELECT
			fl.video_id,
			fv.video_title,
			COUNT(fl.likevideo_id) as likes
		FROM `fst_likevideo` fl
		LEFT JOIN `fst_video` fv
			ON fl.video_id = fv.video_id
		WHERE (fv.video_deleted='0')
		GROUP BY fl.video_id
		HAVING (likes > 0)
		ORDER BY likes DESC
		LIMIT 10";
	$result_likes = Query ($query_likes);
	$iRows = mysqli_num_rows ($result_likes);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_likes = mysqli_fetch_assoc ($result_likes))
		{
			$iVideoID = intval ($row_likes['video_id']);
			$sCode = IDToCode ($iVideoID);
			$sVideoTitle = $row_likes['video_title'];
			$iLikes = intval ($row_likes['likes']);

			print ($iLikes . ' - <a href="/v/' . $sCode . '">' .
				Sanitize ($sVideoTitle) . '</a>');
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function MostViews ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Most views (limit 10):</span>');
	$query_views = "SELECT
			video_views,
			video_id,
			video_title
		FROM `fst_video`
		WHERE (video_deleted='0')
		ORDER BY video_views DESC
		LIMIT 10";
	$result_views = Query ($query_views);
	$iRows = mysqli_num_rows ($result_views);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_views = mysqli_fetch_assoc ($result_views))
		{
			$iViews = intval ($row_views['video_views']);
			$iVideoID = intval ($row_views['video_id']);
			$sCode = IDToCode ($iVideoID);
			$sVideoTitle = $row_views['video_title'];

			print (number_format ($iViews) . ' - <a href="/v/' . $sCode . '">' .
				Sanitize ($sVideoTitle) . '</a>');
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function Requests ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Requests:</span>');
	$query_requests = "SELECT
			fu1.user_username AS requestor,
			fu2.user_username AS recipient,
			fr.request_type,
			fr.request_adddate,
			fr.request_status
		FROM `fst_request` fr
		LEFT JOIN `fst_user` fu1
			ON fr.user_id_requestor = fu1.user_id
		LEFT JOIN `fst_user` fu2
			ON fr.user_id_recipient = fu2.user_id
		ORDER BY request_adddate DESC";
	$result_requests = Query ($query_requests);
	$iRows = mysqli_num_rows ($result_requests);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_requests = mysqli_fetch_assoc ($result_requests))
		{
			$sRequestor = $row_requests['requestor'];
			$sRecipient = $row_requests['recipient'];
			$iType = intval ($row_requests['request_type']);
			$sType = $GLOBALS['request_types'][$iType];
			$sRequestDT = $row_requests['request_adddate'];
			$sRequestDate = date ('j F Y (H:i)', strtotime ($sRequestDT));
			$iStatus = intval ($row_requests['request_status']);
			switch ($iStatus)
			{
				case 0: $sColor = '#800000'; $sStatus = 'discarded'; break;
				case 1: $sColor = '#008000'; $sStatus = 'approved'; break;
				case 2: $sColor = ''; $sStatus = 'pending'; break;
			}

			print ($sRequestDate . ' - <a href="/user/' . $sRequestor . '">' .
				$sRequestor . '</a> asked <a href="/user/' . $sRecipient . '">' .
				$sRecipient . '</a> for their ' . $sType . ': ');
			if ($sColor != '') { print ('<span style="color:' . $sColor . ';">'); }
			print ($sStatus);
			if ($sColor != '') { print ('</span>'); }
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function EmailDomains ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Email domains (limit 10):</span>');
	$query_domains = "SELECT
			substring_index(user_email, '@', -1) AS domain,
			COUNT(*) AS count
		FROM `fst_user`
		GROUP BY domain
		ORDER BY count DESC
		LIMIT 10";
	$result_domains = Query ($query_domains);
	$iRows = mysqli_num_rows ($result_domains);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_domains = mysqli_fetch_assoc ($result_domains))
		{
			$sDomain = $row_domains['domain'];
			$iCount = intval ($row_domains['count']);

			print (number_format ($iCount) . ' - ' . Sanitize ($sDomain));
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/
function DiskUsage ()
/*****************************************************************************/
{
	print ('<div class="admin-div">');
	print ('<span style="display:block; font-style:italic;">Disk usage (limit 10):</span>');
	$query_disk = "SELECT
			SUM(video_preview_bytes + video_360_bytes + video_720_bytes + video_1080_bytes) AS total,
			fu.user_username
		FROM `fst_video` fv
		LEFT JOIN `fst_user` fu
			ON fv.user_id = fu.user_id
		WHERE (video_deleted = '0')
		AND (user_deleted = '0')
		GROUP BY fv.user_id
		ORDER BY total DESC
		LIMIT 10";
	$result_disk = Query ($query_disk);
	$iRows = mysqli_num_rows ($result_disk);
	if ($iRows != 0)
	{
		$iRow = 0;
		while ($row_disk = mysqli_fetch_assoc ($result_disk))
		{
			$iBytes = intval ($row_disk['total']);
			$sUsername = $row_disk['user_username'];

			print ('<a href="/user/' . $sUsername . '">' . $sUsername .
				'</a> - ' . GetSizeHuman ($iBytes));
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
	} else {
		print ('<span style="font-style:italic;">(none)</span>');
	}
	print ('</div>');
}
/*****************************************************************************/

if (!IsAdmin())
{
	if (!isset ($_SESSION['fst']['user_id']))
	{
		HTMLStart ('Admin', 'Admin', 'Admin', 0, FALSE);
		print ('<h1>Admin</h1>');
		print ('First, <a href="/signin/">sign in</a> as an admin.');
	} else {
		HTMLStart ('Admin', 'Admin', 'Admin', 0, FALSE);
		print ('<h1>Admin</h1>');
		print ('First, <a href="/signout/">sign out</a>, then sign in as an admin.');
	}
} else {
	if (strtoupper ($_SERVER['REQUEST_METHOD']) === 'POST')
	{
		switch ($_POST['action'])
		{
			case 'Sign in':
				$sUsername = UserSwitch();
				HTMLStart ('Admin', 'Admin', 'Admin', 0, FALSE);
				print ('<h1>Admin</h1>');
				print ('User "' . Sanitize ($sUsername) . '" not found.');
				break;
			case 'Search IP':
				if (isset ($_POST['search_ip']))
					{ $sIPPart = $_POST['search_ip']; }
						else { $sIPPart = ''; }
				HTMLStart ('Admin', 'Admin', 'Admin', 0, FALSE);
				print ('<h1>Search IP</h1>');
				print ('<span style="display:block; margin-bottom:10px;">Showing' .
					' hits for "' . Sanitize ($sIPPart) . '".</span>');
				ShowIPHits ($sIPPart, 'fst_user',
					'user_avatarip', 'user_username');
				ShowIPHits ($sIPPart, 'fst_user',
					'user_regip', 'user_username');
				ShowIPHits ($sIPPart, 'fst_faillogin', 'faillogin_ip', '');
				ShowIPHits ($sIPPart, 'fst_video', 'video_ip', '');
				ShowIPHits ($sIPPart, 'fst_comment', 'comment_ip', '');
				ShowIPHits ($sIPPart, 'fst_recentviews', 'recentviews_ip', '');
				ShowIPHits ($sIPPart, 'fst_report', 'report_ip', '');
				break;
			case 'Search email':
				if (isset ($_POST['search_email']))
					{ $sEmail = $_POST['search_email']; }
						else { $sEmail = ''; }
				HTMLStart ('Admin', 'Admin', 'Admin', 0, FALSE);
				print ('<h1>Search email</h1>');
				$query_user = "SELECT
						user_username,
						user_deleted
					FROM `fst_user`
					WHERE (user_email='" . $sEmail . "')";
				$result_user = Query ($query_user);
				if (mysqli_num_rows ($result_user) == 1)
				{
					$row_user = mysqli_fetch_assoc ($result_user);
					$sUsername = $row_user['user_username'];
					$iDeleted = intval ($row_user['user_deleted']);
					print ('Email address "' . Sanitize ($sEmail) . '"');
					if ($iDeleted == 0)
					{
						print (' is in use by <a href="/user/' . $sUsername . '">' .
							$sUsername . '</a>.');
					} else {
						print (' was used by user "' . $sUsername . '".');
					}
				} else {
					print ('Email address "' . Sanitize ($sEmail) . '" not found.');
				}
				break;
			case 'To video ID':
				HTMLStart ('Admin', 'Admin', 'Admin', 0, FALSE);
				print ('<h1>To video ID</h1>');
				if (isset ($_POST['convert_code']))
					{ $sCode = $_POST['convert_code']; }
						else { $sCode = '1000'; } /*** Fallback. ***/
				print ('Code ' . Sanitize ($sCode) . ' is video ID ' .
					CodeToID ($sCode) . '.');
				break;
			case 'To code':
				HTMLStart ('Admin', 'Admin', 'Admin', 0, FALSE);
				print ('<h1>To code</h1>');
				if (isset ($_POST['convert_id']))
					{ $iVideoID = intval ($_POST['convert_id']); }
						else { $iVideoID = 1; } /*** Fallback. ***/
				print ('Video ID ' . $iVideoID . ' is code ' .
					IDToCode ($iVideoID) . '.');
				break;
			case 'Create notification':
				$sDTNow = date ('Y-m-d H:i:s');
				/***/
				HTMLStart ('Admin', 'Admin', 'Admin', 0, FALSE);
				print ('<h1>Admin</h1>');

				if ($_POST['to'] == 'user')
				{
					$arUser = UserExists ($_POST['user_username']);
					if ($arUser === FALSE)
					{
						print ('User "' . Sanitize ($_POST['user_username']) .
							'" not found.');
					} else {
						CreateMessage (0, $arUser['id'], $_POST['text']);
						print ('The notification has been created for "' .
							$arUser['username'] . '".');
					}
				} else { /*** 'all' ***/
					$query_all = "SELECT
							user_id
						FROM `fst_user`
						WHERE (user_deleted='0')
						ORDER BY user_username";
					$result_all = Query ($query_all);
					while ($row_all = mysqli_fetch_assoc ($result_all))
					{
						CreateMessage (0, $row_all['user_id'], $_POST['text']);
					}
					print ('The notification has been created for all active users.');
				}
				break;
			case 'Disallow Tor login':
				BanExitNodes();
				break;
		}
	} else {
		HTMLStart ('Admin', 'Admin', 'Admin', 0, FALSE);
		print ('<h1>Admin</h1>');
		OwnerCheck();
		StatsSettings();
		UserPick();
		SearchIP();
		SearchEmail();
		ConvertIDCode();
		CreateNotification();
		DisallowTorLogin();
		IPv6();
		UTF8MB4();
		Banned();
		Accounts();
		Subscriptions();
		Searches();
		SearchesTop();
		Comments();
		Referrers (30);
		Referrers (2);
		CustomThumbnails();
		Monetization();
		Renamed();
		Muted();
		Logins();
		LeaveReasons();
		Adopted();
		TextDrafts();
		Folders();
		IPMultipleAccounts();
		SystemMessages();
		CustomizedSizes();
		MostLiked();
		MostViews();
		Requests();
		EmailDomains();
		DiskUsage();
	}
}
HTMLEnd();
?>
