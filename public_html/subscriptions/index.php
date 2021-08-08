<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.2 (August 2021)
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
function Subscriptions ()
/*****************************************************************************/
{
	$query_sub = "SELECT
			fs.subscribe_id,
			fu.user_username,
			fs.subscribe_adddate
		FROM `fst_subscribe` fs
		LEFT JOIN `fst_user` fu
			ON fs.user_id_channel=fu.user_id
		WHERE (user_id_subscriber='" . $_SESSION['fst']['user_id'] . "')
			AND (user_deleted='0')
		ORDER BY fu.user_username";
	$result_sub = Query ($query_sub);
	if (mysqli_num_rows ($result_sub) != 0)
	{
		while ($row_sub = mysqli_fetch_assoc ($result_sub))
		{
			$sChannel = $row_sub['user_username'];
			$sAddDT = $row_sub['subscribe_adddate'];
			$sAddDate = date ('j F Y', strtotime ($sAddDT));

			$sURL = $GLOBALS['protocol'] . '://www.' .
				$GLOBALS['domain'] . '/user/' . $sChannel;

			print ('<a href="' . $sURL . '">' . $sChannel . '</a> - ' . $sAddDate);
			print ('<br>' . "\n");
		}
	} else {
		print ('No subscriptions.');
	}
}
/*****************************************************************************/

HTMLStart ('Subscriptions', 'Account', 'Subscriptions', 0, FALSE);
print ('<h1>Subscriptions</h1>');
if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To see your subscriptions, first <a href="/signin/">sign in</a>.');
} else {
	LinkBack ('/account/', 'Account');
	Subscriptions();
}
HTMLEnd();
?>
