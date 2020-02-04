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

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if ((isset ($_POST['board'])) &&
		(isset ($_POST['title'])) &&
		(isset ($_POST['description'])))
	{
		$iBoard = intval ($_POST['board']);
		$sTitle = $_POST['title'];
		$sDesc = $_POST['description'];

		if (isset ($_SESSION['fst']['user_id']))
		{
			$iUserID = intval ($_SESSION['fst']['user_id']);

			if ((strlen ($sTitle) >= 1) && (strlen ($sTitle) <= 100))
			{
				if ((strlen ($sDesc) >= 1) && (strlen ($sDesc) <= 10000))
				{
					$query_board = "SELECT
							board_id
						FROM `fst_board`
						WHERE (board_id='" . $iBoard . "')";
					$result_board = Query ($query_board);
					if (mysqli_num_rows ($result_board) == 1)
					{
						if (MayAdd ('topics') === TRUE)
						{
							$sDTNow = date ('Y-m-d H:i:s');
							$sIP = GetIP();

							$query_insert = "INSERT INTO `fst_video` SET
								user_id='" . $iUserID . "',
								user_id_old='0',
								video_visibility='1',
								video_title='" . mysqli_real_escape_string
									($GLOBALS['link'], $sTitle) . "',
								video_description='" . mysqli_real_escape_string
									($GLOBALS['link'], $sDesc) . "',
								video_thumbnail='5',
								video_tags='',
								video_license='1',
								category_id='0',
								video_restricted='0',
								video_comments_allow='1',
								video_comments_show='1',
								language_id='0',
								video_nsfw='2',
								video_subtitles='',
								video_seconds='0',
								video_fps='0.00',
								video_preview='0',
								video_preview_bytes='0',
								video_360='0',
								video_360_bytes='0',
								video_360_width='0',
								video_360_height='0',
								video_720='0',
								video_720_bytes='0',
								video_720_width='0',
								video_720_height='0',
								video_1080='0',
								video_1080_bytes='0',
								video_1080_width='0',
								video_1080_height='0',
								video_ip='" . $sIP . "',
								video_views='0',
								video_deleted='0',
								video_deletedate='1970-01-01 00:00:00',
								video_adddate='" . $sDTNow . "',
								video_uploadedmd5='',
								video_text='',
								video_textsavedt='1970-01-01 00:00:00',
								video_istext='3',
								board_id='" . $iBoard . "'";
							$result_insert = Query ($query_insert);
							if (mysqli_affected_rows ($GLOBALS['link']) == 1)
							{
								$iVideoID = mysqli_insert_id ($GLOBALS['link']);
								$sCode = IDToCode ($iVideoID);
								/***/
								$arResult['result'] = 1;
								$arResult['error'] = '';
								$arResult['code'] = $sCode;
							} else {
								$arResult['result'] = 0;
								$arResult['error'] = 'For some reason, that did not work...';
								$arResult['code'] = '';
							}
						} else {
							$arResult['result'] = 0;
							$arResult['error'] = 'You may not add topics.';
							$arResult['code'] = '';
						}
					} else {
						$arResult['result'] = 0;
						$arResult['error'] = 'Unknown board "' . $iBoard . '".';
						$arResult['code'] = '';
					}
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'The text must be 1-10k characters' .
						' (UTF-16 code units). Currently: ' . strlen ($sDesc);
					$arResult['code'] = '';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'The title must be 1-100 characters' .
					' (UTF-16 code units). Currently: ' . strlen ($sTitle);
				$arResult['code'] = '';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'To create topics, <a href="/signin/">sign in</a>.';
			$arResult['code'] = '';
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Data is missing.';
		$arResult['code'] = '';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
	$arResult['code'] = '';
}
print (json_encode ($arResult));
?>
