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
function Reblogs ($iPostID, $iVisitorID)
/*****************************************************************************/
{
	$query_reblogs = "SELECT
			mbpost_id
		FROM `fst_microblog_post`
		WHERE (mbpost_id_reblog='" . $iPostID . "')
		AND (mbpost_hidden='0')
		ORDER BY mbpost_dt DESC";
	$result_reblogs = Query ($query_reblogs);
	$iReblogs = mysqli_num_rows ($result_reblogs);
	if ($iReblogs != 0)
	{
		while ($row_reblogs = mysqli_fetch_assoc ($result_reblogs))
		{
			$iReblogID = intval ($row_reblogs['mbpost_id']);
			print (GetMBPost ($iReblogID, $iVisitorID, FALSE, 2));
		}
	} else {
		print ('<p style="font-style:italic;">None.</p>');
	}
}
/*****************************************************************************/

if ((isset ($_GET['user'])) &&
	(isset ($_GET['post'])))
{
	HTMLStart ('Status', 'Status', 'Status', 0, FALSE);
	print ('<h1>Status</h1>');

	$sUsername = $_GET['user'];
	$iPostID = intval ($_GET['post']);
	$arPost = MBPostExists ($sUsername, $iPostID);
	if ($arPost !== FALSE)
	{
		/*** $iVisitorID ***/
		if (isset ($_SESSION['fst']['user_id']))
			{ $iVisitorID = $_SESSION['fst']['user_id']; }
				else { $iVisitorID = 0; }

		print (GetMBPost ($iPostID, $iVisitorID, FALSE, 2));

		print ('<h3>Reblogs</h3>');
		Reblogs ($iPostID, $iVisitorID);

		print (PostJavaScript());
	} else {
		print ('<p>Unknown status.</p>');
	}

	HTMLEnd();
} else {
	header ('Location: /');
}
?>
