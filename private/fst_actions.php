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
}
?>
