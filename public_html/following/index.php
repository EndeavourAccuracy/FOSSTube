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

include_once (dirname (__FILE__) . '/../fst_base.php');

/*****************************************************************************/
function Following ()
/*****************************************************************************/
{
	$query_fol = "SELECT
			fu.user_username,
			ff.follow_adddate,
			fs.subscribe_id
		FROM `fst_follow` ff
		LEFT JOIN `fst_user` fu
			ON ff.user_id_microblog = fu.user_id
		LEFT JOIN `fst_subscribe` fs
			ON ff.user_id_microblog = fs.user_id_channel
			AND ff.user_id_follower = fs.user_id_subscriber
		WHERE (user_id_follower='" . $_SESSION['fst']['user_id'] . "')
		AND (user_deleted='0')
		ORDER BY fu.user_username";
	$result_fol = Query ($query_fol);
	if (mysqli_num_rows ($result_fol) != 0)
	{
		$bTip = FALSE;
		while ($row_fol = mysqli_fetch_assoc ($result_fol))
		{
			$sChannel = $row_fol['user_username'];
			$sAddDT = $row_fol['follow_adddate'];
			$sAddDate = date ('j F Y', strtotime ($sAddDT));
			$iSubscribeID = intval ($row_fol['subscribe_id']);

			$sURL = $GLOBALS['protocol'] . '://www.' .
				$GLOBALS['domain'] . '/user/' . $sChannel;

			print ('<a href="' . $sURL . '">' . $sChannel . '</a>');
			if ($iSubscribeID == 0)
			{
				print (' <span style="color:#f00;">*</span>');
				$bTip = TRUE;
			}
			print (' - ' . $sAddDate);
			print ('<br>' . "\n");
		}
		if ($bTip === TRUE)
		{
print ('
<span style="display:block; margin-top:10px;">
<span style="color:#f00;">*</span> Following microblog, but not <a href="/subscriptions/">subscribed</a> to content.
</span>
');
		}
	} else {
		print ('Following nobody.');
	}
}
/*****************************************************************************/

HTMLStart ('Following', 'Account', 'Following', 0, FALSE);
print ('<h1>Following</h1>');
if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To see who you are following, first <a href="/signin/">sign in</a>.');
} else {
	LinkBack ('/account/', 'Account');
	Following();
}
HTMLEnd();
?>
