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

error_reporting (-1);
ini_set ('display_errors', 'On');

date_default_timezone_set ('UTC');

ini_set ('php.internal_encoding', 'UTF-8');
mb_internal_encoding ('UTF-8');

ini_set ('output_buffering', 'Off');
while (@ob_end_flush());

set_time_limit (0);
ini_set ('max_execution_time', 0);

include_once (dirname (__FILE__) . '/fst_settings.php');
include_once (dirname (__FILE__) . '/../' .
	$GLOBALS['public_html'] . '/fst_both.php');

$GLOBALS['lock_file'] = dirname (__FILE__) . '/lock_actions';

/*****************************************************************************/
function MySQLConnect ()
/*****************************************************************************/
{
	include_once (dirname (__FILE__) . '/fst_db.php');

	if ($GLOBALS['link'] === FALSE)
	{
		$GLOBALS['link'] = mysqli_connect ($GLOBALS['db_host'],
			$GLOBALS['db_user'], $GLOBALS['db_pass'], $GLOBALS['db_dtbs']);
		if ($GLOBALS['link'] === FALSE)
		{
			print ('The database appears to be down.');
			exit();
		}
		
		if (mysqli_set_charset ($GLOBALS['link'], 'utf8mb4') === FALSE)
		{
			print ('Cannot set the database charset.');
			exit();
		}
	}
}
/*****************************************************************************/
function Query ($query)
/*****************************************************************************/
{
	$result = mysqli_query ($GLOBALS['link'], $query);
	if ($result === FALSE)
	{
		print ('Query failed.' . "\n");
		print ('Query: ' . $query . "\n");
		print ('Error: ' . mysqli_error ($GLOBALS['link']) . "\n");
		exit();
	}

	return ($result);
}
/*****************************************************************************/
function QueueStatus ()
/*****************************************************************************/
{
	$query_queue = "SELECT
			queue_empty
		FROM `fst_queue`";
	$result_queue = Query ($query_queue);
	$row_queue = mysqli_fetch_assoc ($result_queue);
	$iQueueStatus = $row_queue['queue_empty'];

	return ($iQueueStatus);
}
/*****************************************************************************/
function InQueue ()
/*****************************************************************************/
{
	$query_queue = "SELECT
			COUNT(*) AS queue
		FROM `fst_report`
		WHERE (report_action='0')";
	$result_queue = Query ($query_queue);
	$row_queue = mysqli_fetch_assoc ($result_queue);
	$iInQueue = $row_queue['queue'];

	return ($iInQueue);
}
/*****************************************************************************/
function EmailSomethingInQueue ()
/*****************************************************************************/
{
	$query_queue = "UPDATE `fst_queue` SET
			queue_empty='0'";
	Query ($query_queue);

	/*** $arTo, $arBcc ***/
	$arTo = array();
	$arBcc = array();
	$sMods = '\'' . implode ('\',\'', $GLOBALS['mods']) . '\'';
	$query_email = "SELECT
			user_email
		FROM `fst_user`
		WHERE (user_username IN (" . $sMods . "))";
	$result_email = Query ($query_email);
	while ($row_email = mysqli_fetch_assoc ($result_email))
	{
		array_push ($arBcc, $row_email['user_email']);
	}

	SendEmail ($arTo, $arBcc,
		'[ ' . $GLOBALS['name'] . ' ] Something in queue',
		'The <a href="' . $GLOBALS['protocol'] . '://www.' . $GLOBALS['domain'] .
		'/mod/">moderator queue</a> has one or more reports.');
}
/*****************************************************************************/
function EmailNothingInQueue ()
/*****************************************************************************/
{
	$query_queue = "UPDATE `fst_queue` SET
			queue_empty='1'";
	Query ($query_queue);

	/*** $arTo, $arBcc ***/
	$arTo = array();
	$arBcc = array();
	$sMods = '\'' . implode ('\',\'', $GLOBALS['mods']) . '\'';
	$query_email = "SELECT
			user_email
		FROM `fst_user`
		WHERE (user_username IN (" . $sMods . "))";
	$result_email = Query ($query_email);
	while ($row_email = mysqli_fetch_assoc ($result_email))
	{
		array_push ($arBcc, $row_email['user_email']);
	}

	SendEmail ($arTo, $arBcc,
		'[ ' . $GLOBALS['name'] . ' ] Queue now empty',
		'The moderator queue is now empty.');
}
/*****************************************************************************/
function Top10 ($sType, $sDate)
/*****************************************************************************/
{
	switch ($sType)
	{
		case 'views':
			$sTable = 'fst_top10_views';
			$sColDate = 'top10v_date';
			$sColRank = 'top10v_rank';
			$sColCount = 'top10v_count';
			$sColTitle = 'top10v_title';
			$sQuery = "SELECT
					fv.video_id,
					fv.video_title,
					COUNT(*) AS count
				FROM `fst_recentviews` fr
				LEFT JOIN `fst_video` fv
					ON fr.video_id = fv.video_id
				WHERE (fr.video_id<>0)
				AND (recentviews_date='" . $sDate . "')
				GROUP BY fv.video_id
				ORDER BY count DESC, fv.video_title
				LIMIT 10";
			break;
		case 'likes':
			$sTable = 'fst_top10_likes';
			$sColDate = 'top10l_date';
			$sColRank = 'top10l_rank';
			$sColCount = 'top10l_count';
			$sColTitle = 'top10l_title';
			$sQuery = "SELECT
					fv.video_id,
					fv.video_title,
					COUNT(*) AS count
				FROM `fst_likevideo` fl
				LEFT JOIN `fst_video` fv
					ON fl.video_id = fv.video_id
				WHERE (fl.video_id<>0)
				AND (likevideo_adddate LIKE CONCAT('" . $sDate . "','%'))
				GROUP BY fv.video_id
				ORDER BY count DESC, fv.video_title
				LIMIT 10";
			break;
		case 'commenters':
			$sTable = 'fst_top10_commenters';
			$sColDate = 'top10c_date';
			$sColRank = 'top10c_rank';
			$sColCount = 'top10c_count';
			$sColTitle = 'top10c_title';
			$sQuery = "SELECT
					fv.video_id,
					fv.video_title,
					COUNT(DISTINCT(fc.user_id)) AS count
				FROM `fst_comment` fc
				LEFT JOIN `fst_video` fv
					ON fc.video_id = fv.video_id
				WHERE (fc.video_id<>0)
				AND (comment_adddate LIKE CONCAT('" . $sDate . "','%'))
				AND (fc.comment_hidden='0')
				AND (fv.video_comments_allow='1')
				AND (
					(fv.video_comments_show='1')
					OR (
						(fv.video_comments_show='2')
						AND (fc.comment_approved='1')
					)
				)
				GROUP BY fv.video_id
				ORDER BY count DESC, fv.video_title
				LIMIT 10";
			break;
		case 'referrers':
			$sTable = 'fst_top10_referrers';
			$sColDate = 'top10r_date';
			$sColRank = 'top10r_rank';
			$sColCount = 'top10r_count';
			$sColTitle = 'top10r_title';
			$sQuery = "SELECT
					fv.video_id,
					fv.video_title,
					fr.referrer_count AS count
				FROM `fst_referrer` fr
				LEFT JOIN `fst_video` fv
					ON fr.video_id = fv.video_id
				WHERE (fr.video_id<>0)
				AND (referrer_date='" . $sDate . "')
				AND (fr.referrer_url NOT IN ('" . $GLOBALS['name'] . "', ''))
				ORDER BY count DESC, fv.video_title
				LIMIT 10";
			break;
	}

	$iRank = 0;
	/***/
	$query_top = $sQuery;
	$result_top = Query ($query_top);
	while ($row_top = mysqli_fetch_assoc ($result_top))
	{
		$iRank++;
		/***/
		$iVideoID = intval ($row_top['video_id']);
		$sVideoTitle = $row_top['video_title'];
		$iCount = intval ($row_top['count']);
		/***/
		$query_rank = "INSERT INTO `" . $sTable . "` SET
			" . $sColDate . "='" . $sDate . "',
			" . $sColRank . "='" . $iRank . "',
			video_id='" . $iVideoID . "',
			" . $sColCount . "='" . $iCount . "',
			" . $sColTitle . "='" . mysqli_real_escape_string
				($GLOBALS['link'], $sVideoTitle) . "'";
		Query ($query_rank);
	}

	/*** If necessary, fill remaining spots. ***/
	if ($iRank != 10)
	{
		for ($iLoopRank = ($iRank + 1); $iLoopRank <= 10; $iLoopRank++)
		{
			$query_rank = "INSERT INTO `" . $sTable . "` SET
				" . $sColDate . "='" . $sDate . "',
				" . $sColRank . "='" . $iLoopRank . "',
				video_id='0',
				" . $sColCount . "='0',
				" . $sColTitle . "=''";
			Query ($query_rank);
		}
	}
}
/*****************************************************************************/
function RankToPoints ($iRank)
/*****************************************************************************/
{
	switch ($iRank)
	{
		case 1: $iPoints = 10; break;
		case 2: $iPoints = 9; break;
		case 3: $iPoints = 8; break;
		case 4: $iPoints = 7; break;
		case 5: $iPoints = 6; break;
		case 6: $iPoints = 5; break;
		case 7: $iPoints = 4; break;
		case 8: $iPoints = 3; break;
		case 9: $iPoints = 2; break;
		case 10: $iPoints = 1; break;
		default: print ('Invalid rank.'); exit(); break;
	}

	return ($iPoints);
}
/*****************************************************************************/
function AddPoints ($iVideoID, $iPoints, $sTitle)
/*****************************************************************************/
{
	if ($iVideoID != 0)
	{
		if (array_key_exists ($iVideoID, $GLOBALS['trending']) === FALSE)
		{
			$GLOBALS['trending'][$iVideoID]['points'] = $iPoints;
			$GLOBALS['trending'][$iVideoID]['title'] = $sTitle;
		} else {
			$GLOBALS['trending'][$iVideoID]['points'] += $iPoints;
		}
	}
}
/*****************************************************************************/
function TrendingAdd ($sType, $sDate)
/*****************************************************************************/
{
	switch ($sType)
	{
		case 'views':
			$sTable = 'fst_top10_views';
			$sColDate = 'top10v_date';
			$sColRank = 'top10v_rank';
			$sColTitle = 'top10v_title';
			break;
		case 'likes':
			$sTable = 'fst_top10_likes';
			$sColDate = 'top10l_date';
			$sColRank = 'top10l_rank';
			$sColTitle = 'top10l_title';
			break;
		case 'commenters':
			$sTable = 'fst_top10_commenters';
			$sColDate = 'top10c_date';
			$sColRank = 'top10c_rank';
			$sColTitle = 'top10c_title';
			break;
		case 'referrers':
			$sTable = 'fst_top10_referrers';
			$sColDate = 'top10r_date';
			$sColRank = 'top10r_rank';
			$sColTitle = 'top10r_title';
			break;
	}

	$query_rank = "SELECT
			" . $sColRank . " AS arank,
			video_id,
			" . $sColTitle . " AS title
		FROM `" . $sTable . "`
		WHERE (" . $sColDate . "='" . $sDate . "')";
	$result_rank = Query ($query_rank);
	while ($row_rank = mysqli_fetch_assoc ($result_rank))
	{
		$iPoints = RankToPoints (intval ($row_rank['arank']));
		$iVideoID = intval ($row_rank['video_id']);
		$sTitle = $row_rank['title'];
		AddPoints ($iVideoID, $iPoints, $sTitle);
	}
}
/*****************************************************************************/
function MostPointsFirst ($arVideo1, $arVideo2)
/*****************************************************************************/
{
	return ($arVideo2['points'] - $arVideo1['points']);
}
/*****************************************************************************/
function Trending ($sDate)
/*****************************************************************************/
{
	$GLOBALS['trending'] = array();

	TrendingAdd ('views', $sDate);
	TrendingAdd ('likes', $sDate);
	TrendingAdd ('commenters', $sDate);
	TrendingAdd ('referrers', $sDate);

	uasort ($GLOBALS['trending'], 'MostPointsFirst');

	$iTrendingRank = 0;
	foreach ($GLOBALS['trending'] AS $iVideoID => $arData)
	{
		$iTrendingRank++;
		$query_trending = "INSERT INTO `fst_trending` SET
			trending_date='" . $sDate . "',
			trending_rank='" . $iTrendingRank . "',
			video_id='" . $iVideoID . "',
			trending_total='" . $arData['points'] . "',
			trending_title='" . mysqli_real_escape_string
				($GLOBALS['link'], $arData['title']) . "'";
		Query ($query_trending);
	}
}
/*****************************************************************************/
function TopAndTrending ($sDate)
/*****************************************************************************/
{
	Query ("DELETE FROM `fst_top10_views` WHERE (top10v_date='" .
		$sDate . "')");
	Top10 ('views', $sDate);
	Query ("DELETE FROM `fst_top10_likes` WHERE (top10l_date='" .
		$sDate . "')");
	Top10 ('likes', $sDate);
	Query ("DELETE FROM `fst_top10_commenters` WHERE (top10c_date='" .
		$sDate . "')");
	Top10 ('commenters', $sDate);
	Query ("DELETE FROM `fst_top10_referrers` WHERE (top10r_date='" .
		$sDate . "')");
	Top10 ('referrers', $sDate);
	Query ("DELETE FROM `fst_trending` WHERE (trending_date='" .
		$sDate . "')");
	Trending ($sDate);
}
/*****************************************************************************/
function UpdateCounts ()
/*****************************************************************************/
{
	$query_users = "SELECT
			user_id_deleted
		FROM `fst_updatecounts`";
	$result_users = Query ($query_users);
	while ($row_users = mysqli_fetch_assoc ($result_users))
	{
		$iUserID = $row_users['user_id_deleted'];

		$query_update = "SELECT
				mbpost_id_reblog
			FROM `fst_microblog_post`
			WHERE (user_id='" . $iUserID . "')";
		$result_update = Query ($query_update);
		while ($row_update = mysqli_fetch_assoc ($result_update))
		{
			$iReblogID = intval ($row_update['mbpost_id_reblog']);
			if ($iReblogID != 0)
				{ UpdateCountReblogsMBPost ($iReblogID); }
		}

		$query_update = "SELECT
				DISTINCT(video_id)
			FROM `fst_comment`
			WHERE (user_id='" . $iUserID . "')";
		$result_update = Query ($query_update);
		while ($row_update = mysqli_fetch_assoc ($result_update))
			{ UpdateCountCommentsVideo ($row_update['video_id']); }

		$query_del = "DELETE FROM `fst_updatecounts` WHERE (user_id_deleted='" .
			$iUserID . "')";
		Query ($query_del);
	}
}
/*****************************************************************************/

if (posix_getuid() == 0)
{
	print ('Do not run this as root!' . "\n");
	exit();
}

$rLock = fopen ($GLOBALS['lock_file'], 'w');
if ($rLock === FALSE)
{
	print ('Could not create lock file!' . "\n");
	exit();
}

MySQLConnect();

if (flock ($rLock, LOCK_EX|LOCK_NB))
{
	$iQueueStatus = QueueStatus();
	$iInQueue = InQueue();
	if (($iQueueStatus == 1) && ($iInQueue != 0))
	{
		EmailSomethingInQueue();
	}
	if (($iQueueStatus == 0) && ($iInQueue == 0))
	{
		EmailNothingInQueue();
	}
	/***/
	$query_views = "DELETE from `fst_recentviews` WHERE (DATEDIFF(CURDATE(),recentviews_date) > 7)";
	Query ($query_views);
	/***/
	$sDateToday = date ('Y-m-d');
	TopAndTrending ($sDateToday);
	/***/
	$sDateYesterday = date ('Y-m-d', strtotime ('-1 days'));
	$query_tdone = "SELECT
			tdone_id
		FROM `fst_tdone`
		WHERE (tdone_date='" . $sDateYesterday . "')";
	$result_tdone = Query ($query_tdone);
	if (mysqli_num_rows ($result_tdone) == 0)
	{
		TopAndTrending ($sDateYesterday);
		/***/
		$query_tdone = "INSERT INTO `fst_tdone` SET
			tdone_date='" . $sDateYesterday . "'";
		Query ($query_tdone);
	}
	/***/
	UpdateCounts();
}
?>
