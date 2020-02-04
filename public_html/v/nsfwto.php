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

include_once (dirname (__FILE__) . '/../fst_base.php');

/*****************************************************************************/

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if ((isset ($_POST['code'])) &&
		(isset ($_POST['nsfwto'])))
	{
		$sCode = $_POST['code'];
		$iVideoID = CodeToID ($sCode);
		$iNSFWTo = intval ($_POST['nsfwto']);

		if (IsMod())
		{
			$query_nsfw = "UPDATE `fst_video` SET
				video_nsfw='" . $iNSFWTo . "'
				WHERE (video_id='" . $iVideoID . "')";
			Query ($query_nsfw);

			/*** Notify the publisher. ***/
			$query_info = "SELECT
					user_id,
					video_title
				FROM `fst_video`
				WHERE (video_id='" . $iVideoID . "')";
			$result_info = Query ($query_info);
			$row_info = mysqli_fetch_assoc ($result_info);
			CreateMessage (-1, $row_info['user_id'], 'A moderator has' .
				' changed the NSFW-status of "' . $row_info['video_title'] . '".');

			$arResult['result'] = 1;
			$arResult['error'] = '';
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'You are not a mod.';
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Data is missing.';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
}
print (json_encode ($arResult));
?>
