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

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if (isset ($_SESSION['fst']['user_id']))
	{
		$iUserID = intval ($_SESSION['fst']['user_id']);

		$query_topics = "SELECT
				video_id
			FROM `fst_video`
			WHERE (video_istext='3')
			AND (video_deleted='0')";
		$result_topics = Query ($query_topics);
		if (mysqli_num_rows ($result_topics) != 0)
		{
			$sDTNow = date ('Y-m-d H:i:s');

			while ($row_topics = mysqli_fetch_assoc ($result_topics))
			{
				$iVideoID = intval ($row_topics['video_id']);

				$query_lastviewed = "INSERT INTO `fst_commentslastviewed` SET
						video_id='" . $iVideoID . "',
						user_id='" . $iUserID . "',
						commentslastviewed_dt='" . $sDTNow . "'
					ON DUPLICATE KEY UPDATE
						commentslastviewed_dt='" . $sDTNow . "'";
				Query ($query_lastviewed);
			}
		}

		$arResult['result'] = 1;
		$arResult['error'] = '';
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'To mark read, sign in.';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
}
print (json_encode ($arResult));
?>
