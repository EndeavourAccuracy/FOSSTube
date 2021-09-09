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

/*** This script moves (and then symbolicly links) .mp4 files of video content that both a) is more than two weeks old, and b) has less than 200 views, to the directory $GLOBALS['big_storage_mp4']. The CPU load for this script is quite high. When running this script via ssh, make sure to ssh with '-o "ServerAliveInterval 60" -o "ServerAliveCountMax 120"' to keep the connection alive. ***/

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

$GLOBALS['lock_file'] = dirname (__FILE__) . '/lock_move';
$GLOBALS['big_storage_mp4'] = '/mnt/CHANGE/mp4/'; /*** WITH slashes. ***/

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
function IDToCode ($iID)
/*****************************************************************************/
{
	$sCode = base_convert (46655 + $iID, 10, 36);
	$arSearch = array ('a', 'e', 'i', 'o', 'u', 'y');
	$arReplace = array ('B', 'F', 'J', 'P', 'V', 'Z');
	$sCode = str_replace ($arSearch, $arReplace, $sCode);
	return ($sCode);
}
/*****************************************************************************/

if (posix_getuid() == 0)
{
	print ('Do not run this as root!' . "\n");
	print ('Use: sudo -u <USER> php fst_move.php' . "\n");
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
	if (is_writable ($GLOBALS['big_storage_mp4']) !== TRUE)
	{
		print ('Cannot write to "' . $GLOBALS['big_storage_mp4'] . '"!' . "\n");
		exit();
	}

	$query_moveid = "SELECT
			video_id,
			video_preview,
			video_360,
			video_720,
			video_1080
		FROM `fst_video`
		WHERE (DATE(video_adddate) < (NOW() - INTERVAL 15 DAY))
		AND (video_views < 200)
		AND (video_deleted='0')
		AND (video_istext='0')
		AND (video_preview<>'2')
		AND (video_360<>'2')
		AND (video_720<>'2')
		AND (video_1080<>'2')
		ORDER BY video_id";
	$result_moveid = Query ($query_moveid);
	$iRows = mysqli_num_rows ($result_moveid);
	if ($iRows != 0)
	{
		$iAtRow = 0;
		while ($row_moveid = mysqli_fetch_assoc ($result_moveid))
		{
			$iVideoID = $row_moveid['video_id'];
			$sCode = IDToCode ($iVideoID);
			$iPreview = intval ($row_moveid['video_preview']);
			$i360 = intval ($row_moveid['video_360']);
			$i720 = intval ($row_moveid['video_720']);
			$i1080 = intval ($row_moveid['video_1080']);

			$iAtRow++;
			print ('[' . $iAtRow . '/' . $iRows . '] Moving ' . $sCode . '...');

			/*** Create (sub)directories. ***/
			$sOutputM = $GLOBALS['big_storage_mp4'] . $sCode[-1] . '/';
			MakeDir ($sOutputM);
			$sOutputM .= $sCode[-2] . '/';
			MakeDir ($sOutputM);

			$arHeight = array ('preview', '360', '720', '1080');
			foreach ($arHeight as $sHeight)
			{
				if ((($sHeight == 'preview') && ($iPreview == 1)) ||
					(($sHeight == '360') && ($i360 == 1)) ||
					(($sHeight == '720') && ($i720 == 1)) ||
					(($sHeight == '1080') && ($i1080 == 1)))
				{
					$sFrom = dirname (__FILE__) . '/../' . $GLOBALS['public_html'] .
						VideoURL ($sCode, $sHeight);
					$sTo = $sOutputM . $sCode . '_' . $sHeight . '.mp4';
					if ((file_exists ($sFrom) === TRUE) &&
						(file_exists ($sTo) === FALSE) &&
						(is_link ($sFrom) === FALSE))
					{
						print (' ' . $sHeight); /*** Doing. ***/

						if (rename ($sFrom, $sTo) === FALSE)
						{
							print ('Cannot move "' . $sFrom . '" to "' .
								$sTo . '"!' . "\n");
							exit();
						} else {
							if (symlink ($sTo, $sFrom) === FALSE)
							{
								print ('Cannot symlink "' . $sFrom . '" to "' .
									$sTo . '"!' . "\n");
								exit();
							}
						}
					} else {
						print (' .'); /*** Already done. ***/
					}
				}
			}

			print (' done.' . "\n");
		}
	}
}
?>
