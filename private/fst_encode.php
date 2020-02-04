<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.0 (February 2020)
 * Copyright (C) 2020 Norbert de Jonge <mail@norbertdejonge.nl>
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

$GLOBALS['lock_file'] = dirname (__FILE__) . '/lock_encode';

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
function Thumbnails ($arOutPer, $fSeconds, $sVideoURL, $sOutputJ, $sCode)
/*****************************************************************************/
{
	/*** 180 ***/
	for ($iLoopJPG = 1; $iLoopJPG <= 5; $iLoopJPG++)
	{
		$iSec = intval (($fSeconds * $arOutPer[$iLoopJPG]) / 100);
		/*** Removed: ,pad=320:180:(ow-iw)/2:(oh-ih)/2:black ***/
		$sExec = $GLOBALS['ffmpeg'] . ' -ss ' . $iSec . ' -i ' . $sVideoURL . ' -y -vf "scale=320:180:flags=lanczos:force_original_aspect_ratio=decrease" -vframes 1 -q:v 5 ' . $sOutputJ . $sCode . '_180_' . $iLoopJPG . '.jpg 2> /dev/null';
		shell_exec ($sExec);
	}

	/*** 720 ***/
	for ($iLoopJPG = 1; $iLoopJPG <= 5; $iLoopJPG++)
	{
		$iSec = intval (($fSeconds * $arOutPer[$iLoopJPG]) / 100);
		/*** Removed: ,pad=1280:720:(ow-iw)/2:(oh-ih)/2:black ***/
		$sExec = $GLOBALS['ffmpeg'] . ' -ss ' . $iSec . ' -i ' . $sVideoURL . ' -y -vf "scale=1280:720:flags=lanczos:force_original_aspect_ratio=decrease" -vframes 1 -q:v 5 ' . $sOutputJ . $sCode . '_720_' . $iLoopJPG . '.jpg 2> /dev/null';
		shell_exec ($sExec);
	}
}
/*****************************************************************************/
function Encode ($sHeight, $sVideoURL, $iID, $fSeconds)
/*****************************************************************************/
{
	$sCode = IDToCode ($iID);

	/*** Create (sub)directories. ***/
	$sOutputM = dirname (__FILE__) . '/../' . $GLOBALS['public_html'] . '/mp4/';
	MakeDir ($sOutputM);
	$sOutputJ = dirname (__FILE__) . '/../' . $GLOBALS['public_html'] . '/jpg/';
	MakeDir ($sOutputJ);
	$sOutputM .= $sCode[strlen ($sCode) - 1] . '/';
	MakeDir ($sOutputM);
	$sOutputJ .= $sCode[strlen ($sCode) - 1] . '/';
	MakeDir ($sOutputJ);
	$sOutputM .= $sCode[strlen ($sCode) - 2] . '/';
	MakeDir ($sOutputM);
	$sOutputJ .= $sCode[strlen ($sCode) - 2] . '/';
	MakeDir ($sOutputJ);

	$sOutMP4 = $sOutputM . $sCode . '_' . $sHeight . '.mp4';
	$arOutPer[1] = 1;
	$arOutPer[2] = 25;
	$arOutPer[3] = 50;
	$arOutPer[4] = 75;
	$arOutPer[5] = 100;

	/*** Going to 16:9. ***/
	$sFromFPS = ''; $sToFPS = '';
	switch ($sHeight)
	{
		case 'preview':
			$sScale = '320:180';
			$sFromFPS = ' -r:v "480/1"';
			$sToFPS = ' -an -r:v "12/1"'; /*** Also disable audio. ***/
			Thumbnails ($arOutPer, $fSeconds, $sVideoURL, $sOutputJ, $sCode);
			break;
		case '360': $sScale = '640:360'; break;
		case '720': $sScale = '1280:720'; break;
		case '1080': $sScale = '1920:1080'; break;
	}

	print ('Encoding ' . $sOutMP4 . "\n");
	/* Do NOT add "zerolatency" or "pad=".
	 * The "-max_muxing_queue_size 9999" is a workaround for
	 * https://trac.ffmpeg.org/ticket/6375
	 */
	$sExec = $GLOBALS['ffmpeg'] . $sFromFPS . ' -i ' . $sVideoURL . ' -y -c:v libx264 -crf ' . $GLOBALS['ffmpeg_crf'] . ' -preset veryfast -tune fastdecode -c:a aac -strict -2 -ac 2 -ar 44100 -ab ' . $GLOBALS['ffmpeg_ab'] . ' -threads ' . $GLOBALS['ffmpeg_threads'] . ' -profile:v high -pix_fmt yuv420p -level 4.2 -movflags +faststart -max_muxing_queue_size 9999 -f mp4 -vf "scale=' . $sScale . ':flags=lanczos:force_original_aspect_ratio=decrease,pad=ceil(iw/2)*2:ceil(ih/2)*2"' . $sToFPS . ' ' . $sOutMP4 . ' 2> /dev/null';
	$arOut = array();
	$iReturn = 1; /*** Random, but do NOT use 0. ***/
	exec ($sExec, $arOut, $iReturn);

	if ($iReturn == 0)
	{
		return (1); /*** Success. ***/
	} else {
		return (0);
	}
}
/*****************************************************************************/
function Added ($iID, $sHeight, $iAdded)
/*****************************************************************************/
{
	$sCode = IDToCode ($iID);

	/*** $iBytes, $sWidthQ and $sHeightQ + Save FPS. ***/
	$iBytes = 0; $sWidthQ = ""; $sHeightQ = "";
	if ($iAdded == 1)
	{
		$sPathFile = dirname (__FILE__) . '/../' . $GLOBALS['public_html'] .
			VideoURL ($sCode, $sHeight);
		if (file_exists ($sPathFile) === TRUE)
		{
			$iBytes = filesize ($sPathFile);
			if ($iBytes === FALSE) { $iBytes = 0; }
			/***/
			if ($sHeight != 'preview')
			{
				$sExec = $GLOBALS['ffprobe'] . ' -v error -show_entries stream=width -of default=noprint_wrappers=1:nokey=1 "' . $sPathFile . '" 2>&1';
				$iWidthQ = intval (shell_exec ($sExec));
				$sWidthQ = ", video_" . $sHeight . "_width='" . $iWidthQ . "'";
				/***/
				$sExec = $GLOBALS['ffprobe'] . ' -v error -show_entries stream=height -of default=noprint_wrappers=1:nokey=1 "' . $sPathFile . '" 2>&1';
				$iHeightQ = intval (shell_exec ($sExec));
				$sHeightQ = ", video_" . $sHeight . "_height='" . $iHeightQ . "'";
			}
			/***/
			if ($sHeight == '360')
			{
				/*** Do NOT remove "-select_streams v:0". ***/
				$sExec = $GLOBALS['ffprobe'] . ' -v error -select_streams v:0 -show_entries stream=avg_frame_rate -of default=noprint_wrappers=1:nokey=1 "' . $sPathFile . '" 2>&1';
				$sAFR = substr (shell_exec ($sExec), 0, -1);
				$sDividend = substr ($sAFR, 0, strpos ($sAFR, '/'));
				$sDivisor = substr ($sAFR, strpos ($sAFR, '/') + 1);
				if (floatval ($sDivisor) != 0)
				{
					$fFPS = round (floatval ($sDividend) / floatval ($sDivisor), 2);
				} else { $fFPS = 0; }

				$query_fps = "UPDATE `fst_video` SET
					video_fps='" . $fFPS . "'
					WHERE (video_id='" . $iID . "')";
				Query ($query_fps);
			}
		}
	}

	$query_added = "UPDATE `fst_video` SET
		video_" . $sHeight . "='" . $iAdded . "',
		video_" . $sHeight . "_bytes='" . $iBytes . "'
		" . $sWidthQ . "
		" . $sHeightQ . "
		WHERE (video_id='" . $iID . "')";
	Query ($query_added);
}
/*****************************************************************************/
function MessageSubscribers ($iVideoID, $iUserIDChannel)
/*****************************************************************************/
{
	$query_subscribers = "SELECT
			user_id_subscriber
		FROM `fst_subscribe`
		WHERE (user_id_channel='" . $iUserIDChannel . "')
		ORDER BY subscribe_adddate";
	$result_subscribers = Query ($query_subscribers);
	if (mysqli_num_rows ($result_subscribers) != 0)
	{
		while ($row_subscribers = mysqli_fetch_assoc ($result_subscribers))
		{
			$iUserIDSubscriber = $row_subscribers['user_id_subscriber'];
			$sDTNow = date ('Y-m-d H:i:s');

			$query_message = "INSERT INTO `fst_message` SET
				user_id_sender='" . $iUserIDChannel . "',
				user_id_recipient='" . $iUserIDSubscriber . "',
				message_text='',
				video_id='" . $iVideoID . "',
				message_adddate='" . $sDTNow . "',
				message_cleared='0'";
			Query ($query_message);
		}
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

$GLOBALS['ffmpeg'] = substr (shell_exec ('which ffmpeg'), 0, -1);
$GLOBALS['ffprobe'] = substr (shell_exec ('which ffprobe'), 0, -1);

if (flock ($rLock, LOCK_EX|LOCK_NB))
{
	$bDone = FALSE;
	do {
		$query_todo = "SELECT
				video_id,
				user_id,
				video_preview,
				video_360,
				video_720,
				video_1080
			FROM `fst_video`
			WHERE (video_deleted='0')
			AND ((video_preview='2') OR
			(video_360='2') OR
			(video_720='2') OR
			(video_1080='2'))
			ORDER BY video_preview DESC, video_360 DESC,
				video_720 DESC, video_1080 DESC, video_adddate
			LIMIT 1";
		$result_todo = Query ($query_todo);
		if (mysqli_num_rows ($result_todo) == 1)
		{
			$row_todo = mysqli_fetch_assoc ($result_todo);
			$iID = $row_todo['video_id'];
			$iUserID = $row_todo['user_id'];
			$sVideoURL = dirname (__FILE__) .
				'/../' . $GLOBALS['public_html'] . '/uploads/' . $iID;

			/*** $fSeconds ***/
			$sExec = $GLOBALS['ffprobe'] . ' -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 "' . $sVideoURL . '" 2>&1';
			$fSeconds = floatval (shell_exec ($sExec));
			/***/
			$query_seconds = "UPDATE `fst_video` SET
					video_seconds='" . round ($fSeconds) . "'
				WHERE (video_id='" . $iID . "')";
			Query ($query_seconds);

			/*** $iWidth ***/
			$sExec = $GLOBALS['ffprobe'] . ' -v error -select_streams v:0 -show_entries stream=width -of csv=s=x:p=0 "' . $sVideoURL . '" 2>&1';
			$iWidth = intval (shell_exec ($sExec));

			/*** $iHeight ***/
			$sExec = $GLOBALS['ffprobe'] . ' -v error -select_streams v:0 -show_entries stream=height -of csv=s=x:p=0 "' . $sVideoURL . '" 2>&1';
			$iHeight = intval (shell_exec ($sExec));

			if ($row_todo['video_preview'] == 2)
			{
				$iAdded = Encode ('preview', $sVideoURL, $iID, $fSeconds);
				Added ($iID, 'preview', $iAdded);
			} else if ($row_todo['video_360'] == 2) {
				$iAdded = Encode ('360', $sVideoURL, $iID, 0);
				Added ($iID, '360', $iAdded);
			} else if ($row_todo['video_720'] == 2) {
				if (($iWidth >= 1280) || ($iHeight >= 720))
				{
					$iAdded = Encode ('720', $sVideoURL, $iID, 0);
				} else { $iAdded = 0; }
				Added ($iID, '720', $iAdded);
			} else { /*** $row_todo['video_1080'] == 2 ***/
				if (($iWidth >= 1920) || ($iHeight >= 1080))
				{
					$iAdded = Encode ('1080', $sVideoURL, $iID, 0);
				} else { $iAdded = 0; }
				Added ($iID, '1080', $iAdded);

				MessageSubscribers ($iID, $iUserID);

				unlink ($sVideoURL);
			}
		} else {
			$bDone = TRUE;
		}
	} while ($bDone === FALSE);
}
?>
