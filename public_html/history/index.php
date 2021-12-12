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
function History ()
/*****************************************************************************/
{
	$query_history = "SELECT
			fv.video_title,
			COUNT(fc.video_id) AS amount,
			fc.video_id,
			MAX(comment_adddate) AS usedate
		FROM `fst_comment` fc
		LEFT JOIN `fst_video` fv
			ON fc.video_id=fv.video_id
		WHERE (fc.user_id='" . $_SESSION['fst']['user_id'] . "')
			AND (comment_hidden='0')
			AND (fv.video_deleted='0')
			AND (fv.video_comments_allow='1')
		GROUP BY video_id
		ORDER BY usedate DESC";
	$result_history = Query ($query_history);
	if (mysqli_num_rows ($result_history) != 0)
	{
		while ($row_history = mysqli_fetch_assoc ($result_history))
		{
			$sTitle = $row_history['video_title'];
			$iAmount = intval ($row_history['amount']);
			$iVideoID = intval ($row_history['video_id']);
			$sCode = IDToCode ($iVideoID);

			$sURL = $GLOBALS['protocol'] . '://www.' .
				$GLOBALS['domain'] . '/v/' . $sCode;

			print ('<a href="' . $sURL . '">' . Sanitize ($sTitle) . '</a>');
			if ($iAmount != 1) { print (' (' . $iAmount . 'x)'); }
			print ('<br>' . "\n");
		}
	} else {
		print ('No history.');
	}
}
/*****************************************************************************/

HTMLStart ('History', 'Account', 'History', 0, FALSE);
print ('<h1>History</h1>');
if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To see your history, first <a href="/signin/">sign in</a>.');
} else {
	LinkBack ('/account/', 'Account');
	History();
}
HTMLEnd();
?>
