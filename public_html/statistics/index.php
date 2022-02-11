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
function Statistics ()
/*****************************************************************************/
{
	$iUserID = intval ($_SESSION['fst']['user_id']);
	$sUsername = $_SESSION['fst']['user_username'];

	print ('<h2>Introduction</h2>');
	print ('<p>Statistics about user/publisher "' .
		Sanitize ($sUsername) . '".</p>');

	print ('<h2>Publisher data</h2>');
	print ('<p>');

	/*** subscribers ***/
	$query_sub = "SELECT
			COUNT(user_id_subscriber) AS subscribers
		FROM `fst_subscribe`
		WHERE (user_id_channel='" . $iUserID . "')";
	$result_sub = Query ($query_sub);
	$row_sub = mysqli_fetch_assoc ($result_sub);
	$iSubscribers = intval ($row_sub['subscribers']);
	print ('Content subscribers: ' . $iSubscribers);

	print ('<br>');

	/*** followers ***/
	$query_fol = "SELECT
			COUNT(user_id_follower) AS followers
		FROM `fst_follow`
		WHERE (user_id_microblog='" . $iUserID . "')";
	$result_fol = Query ($query_fol);
	$row_fol = mysqli_fetch_assoc ($result_fol);
	$iFollowers = intval ($row_fol['followers']);
	print ('Microblog followers: ' . $iFollowers);

	print ('</p>');
}
/*****************************************************************************/

HTMLStart ('Statistics', 'Account', 'Statistics', 0, FALSE);
print ('<h1>Statistics</h1>');
if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To see statistics, first <a href="/signin/">sign in</a>.');
} else {
	LinkBack ('/account/', 'Account');
	Statistics();
}
HTMLEnd();
?>
