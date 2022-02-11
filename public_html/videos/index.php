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
function RecentReferrers ($iUserID)
/*****************************************************************************/
{
	$query_referrers = "SELECT
			fr.referrer_url,
			fr.video_id,
			fv.video_title,
			SUM(fr.referrer_count) AS referrer_count
		FROM `fst_referrer` fr
		LEFT JOIN `fst_video` fv
			ON fr.video_id=fv.video_id
		WHERE (referrer_url NOT IN ('" . $GLOBALS['name'] . "', ''))
		AND (referrer_date BETWEEN (CURRENT_DATE() - INTERVAL 29 DAY) AND CURRENT_DATE())
		AND (fv.video_deleted='0')
		AND (fv.user_id='" . $iUserID . "')
		GROUP BY referrer_url, video_id
		ORDER BY referrer_count DESC
		LIMIT 100";
	$result_referrers = Query ($query_referrers);
	$iRows = mysqli_num_rows ($result_referrers);
	if ($iRows != 0)
	{
		$iRow = 0;
		print ('<div id="referrers-div">');
		print ('<span style="display:block; font-style:italic;">Last 30d <a target="_blank" href="https://en.wikipedia.org/wiki/HTTP_referer">referrers</a>:</span>');
		print ('<span style="display:block; color:#414dc5;">Always be careful when visiting URLs (http links) that seem tailored specifically for you, as these may be (ab)used to obtain the IP address (' . GetIP() . ') of your Internet device.</span>');
		while ($row_referrers = mysqli_fetch_assoc ($result_referrers))
		{
			$sRefURL = $row_referrers['referrer_url'];
			$iVideoID = intval ($row_referrers['video_id']);
			$sCode = IDToCode ($iVideoID);
			$sVideoTitle = $row_referrers['video_title'];
			$iRefCount = intval ($row_referrers['referrer_count']);

			print ($sRefURL . ' (' . $iRefCount . 'x) to <a href="/v/' . $sCode . '">' .
				Sanitize ($sVideoTitle) . '</a>');
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
		print ('</div>');
	}
}
/*****************************************************************************/
function DuplicatesOtherAccounts ($iUserID)
/*****************************************************************************/
{
	$query_dupl = "SELECT
			fv.video_id,
			fu.user_username,
			fv.video_title,
			fv.video_uploadedmd5
		FROM `fst_video` fv
		LEFT JOIN `fst_user` fu
			ON fv.user_id = fu.user_id
		WHERE (fv.video_uploadedmd5 IN
			(SELECT
					DISTINCT(video_uploadedmd5)
				FROM `fst_video`
				WHERE (user_id='" . $iUserID . "')
				AND (video_uploadedmd5 <> '')
				AND (video_deleted='0')
			))
		AND (fv.user_id<>'" . $iUserID . "')
		AND (fv.video_deleted='0')
		ORDER BY user_username, video_title";
	$result_dupl = Query ($query_dupl);
	$iRows = mysqli_num_rows ($result_dupl);
	if ($iRows != 0)
	{
		$iRow = 0;
		print ('<div id="duplicates-div">');
		print ('<span style="display:block; font-style:italic;">Video duplicates (other accounts):</span>');
		while ($row_dupl = mysqli_fetch_assoc ($result_dupl))
		{
			$iDuplVideoID = intval ($row_dupl['video_id']);
			$sDuplCode = IDToCode ($iDuplVideoID);
			$sDuplUsername = $row_dupl['user_username'];
			$sDuplTitle = $row_dupl['video_title'];
			$sDuplMD5 = $row_dupl['video_uploadedmd5'];

			$query_your = "SELECT
					video_id,
					video_title
				FROM `fst_video`
				WHERE (user_id='" . $iUserID . "')
				AND (video_uploadedmd5='" . $sDuplMD5 . "')
				AND (video_deleted='0')";
			$result_your = Query ($query_your);
			$row_your = mysqli_fetch_assoc ($result_your);
			$iYourVideoID = intval ($row_your['video_id']);
			$sYourCode = IDToCode ($iYourVideoID);
			$sYourTitle = $row_your['video_title'];

			print ('User "' . $sDuplUsername . '" published <a href="/v/' . $sDuplCode . '">' . Sanitize ($sDuplTitle) . '</a>, which is the same as your <a href="/v/' . $sYourCode . '">' . Sanitize ($sYourTitle) . '</a>.');
			$iRow++;
			if ($iRow != $iRows) { print ('<br>'); }
		}
		print ('</div>');
	}
}
/*****************************************************************************/

HTMLStart ('Videos', 'Account', 'Videos', 0, FALSE);
print ('<h1>Videos</h1>');
if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To view/edit videos, first <a href="/signin/">sign in</a>.');
} else {
	$query_video = "SELECT
			video_id
		FROM `fst_video`
		WHERE (user_id='" . $_SESSION['fst']['user_id'] . "')
		AND (video_deleted='0')
		AND (video_istext='0')";
	$result_videos = Query ($query_video);
	if (mysqli_num_rows ($result_videos) == 0)
	{
		print ('<span style="display:block;">You have no videos. Maybe <a href="/upload/">upload</a>?</span>');
	} else {
		/*** Saved note. ***/
		if ((isset ($_SESSION['fst']['saved'])) &&
			($_SESSION['fst']['saved'] == 1))
		{
			print ('<div class="note saved">Saved.</div>');
			unset ($_SESSION['fst']['saved']);
		}

		/*** Deleted note. ***/
		if ((isset ($_SESSION['fst']['deleted'])) &&
			(($_SESSION['fst']['deleted'] == 1) ||
			($_SESSION['fst']['deleted'] == 5)))
		{
			if ($_SESSION['fst']['deleted'] == 1)
				{ print ('<div class="note deleted">Deleted.</div>'); }
					else { print ('<div class="note deleted">Semi-deleted.</div>'); }
			unset ($_SESSION['fst']['deleted']);
		}

		/*** Processing note. ***/
		$query_todo = "SELECT
				video_id
			FROM `fst_video`
			WHERE (user_id='" . $_SESSION['fst']['user_id'] . "')
			AND ((video_preview='2') OR
			(video_360='2') OR
			(video_720='2') OR
			(video_1080='2'))
			AND (video_deleted='0')";
		$result_todo = Query ($query_todo);
		if (mysqli_num_rows ($result_todo) > 0)
		{
			print ('<div class="note warning">One or more videos are still being processed. <a href="javascript:window.location.reload();" style="color:#fff;">Reload</a> this page to see the most recent status. If processing seems to never end (24h+), the video(s) you uploaded may have had an unsupported format.</div>');
		}

		RecentReferrers ($_SESSION['fst']['user_id']);

		DuplicatesOtherAccounts ($_SESSION['fst']['user_id']);

		print ('<form action="/delete/" method="POST">');
		$arFilters = array ('threshold' => '0', 'nsfw' => '3');
		$arData = Videos (
			'',
			'',
			" AND (fv.user_id='" . $_SESSION['fst']['user_id'] . "') AND (fv.video_istext='0')",
			'datedesc',
			0,
			0,
			$_SESSION['fst']['user_username'],
			'',
			'',
			1,
			TRUE,
			$arFilters
		);
		print ($arData['html']);
		print ('<input type="submit" value="Delete checked">');
		print ('</form>');
	}
}
HTMLEnd();
?>
