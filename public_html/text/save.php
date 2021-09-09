<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.3 (September 2021)
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
		$iUserID = $_SESSION['fst']['user_id'];

		if ((isset ($_POST['title'])) &&
			(isset ($_POST['text'])) &&
			(isset ($_POST['code'])))
		{
			$sTitle = $_POST['title'];
			$sText = $_POST['text'];
			$sCode = $_POST['code'];

			if ((strlen ($sTitle) >= 1) && (strlen ($sTitle) <= 100))
			{
				if ((strlen ($sText) >= 1) && (strlen ($sText) <= 1000000))
				{
					if (MayAdd ('texts') === TRUE)
					{
						$sDTNow = date ('Y-m-d H:i:s');

						if ($sCode == 'new')
						{
							$query_insert = "INSERT INTO `fst_video` SET
								user_id='" . $iUserID . "',
								user_id_old='0',
								video_visibility='1',
								video_title='" . mysqli_real_escape_string
									($GLOBALS['link'], $sTitle) . "',
								video_description='',
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
								video_ip='',
								video_views='0',
								video_likes='0',
								video_comments='0',
								video_deleted='0',
								video_deletedate='1970-01-01 00:00:00',
								video_adddate='1970-01-01 00:00:00',
								video_uploadedmd5='',
								video_text='" . mysqli_real_escape_string
									($GLOBALS['link'], $sText) . "',
								video_textsavedt='" . $sDTNow . "',
								video_istext='2',
								board_id='0',
								video_sph_mpprojection='',
								video_sph_stereo3dtype='',
								projection_id='0',
								poll_id='0'";
							$result_insert = Query ($query_insert);
							if (mysqli_affected_rows ($GLOBALS['link']) == 1)
							{
								$iVideoID = mysqli_insert_id ($GLOBALS['link']);
								$sCode = IDToCode ($iVideoID);
								/***/
								$sOutputJ = dirname (__FILE__) . '/../jpg/';
								MakeDir ($sOutputJ);
								$sOutputJ .= $sCode[strlen ($sCode) - 1] . '/';
								MakeDir ($sOutputJ);
								$sOutputJ .= $sCode[strlen ($sCode) - 2] . '/';
								MakeDir ($sOutputJ);
								/***/
								$_SESSION['fst']['saved'] = 1;
								/***/
								$arResult['result'] = 1;
								$arResult['error'] = '';
								$arResult['code'] = $sCode;
							} else {
								$arResult['result'] = 0;
								$arResult['error'] = 'Saving failed. Save the text on' .
									' your PC, and then please contact us.';
								$arResult['code'] = '';
							}
						} else {
							$iVideoID = CodeToID ($sCode);
							$query_video = "SELECT
									video_id
								FROM `fst_video`
								WHERE (user_id='" . $_SESSION['fst']['user_id'] . "')
								AND (video_id='" . $iVideoID . "')
								AND ((video_istext='1') OR (video_istext='2'))";
							$result_video = Query ($query_video);
							if (mysqli_num_rows ($result_video) == 1)
							{
								$query_save = "UPDATE `fst_video` SET
									video_title='" . mysqli_real_escape_string
										($GLOBALS['link'], $sTitle) . "',
									video_text='" . mysqli_real_escape_string
										($GLOBALS['link'], $sText) . "',
									video_textsavedt='" . $sDTNow . "'
									WHERE (video_id='" . $iVideoID . "')";
								$result_save = Query ($query_save);
								if (mysqli_affected_rows ($GLOBALS['link']) == 1)
								{
									$_SESSION['fst']['saved'] = 1;
									/***/
									$arResult['result'] = 1;
									$arResult['error'] = '';
									$arResult['code'] = $sCode;
								} else {
									$arResult['result'] = 0;
									$arResult['error'] = 'Nothing changed.';
									$arResult['code'] = '';
								}
							} else {
								$arResult['result'] = 0;
								$arResult['error'] = 'Text "' . Sanitize ($sCode) .
									'" either does not exist or is owned by someone else.';
								$arResult['code'] = '';
							}
						}
					} else {
						$arResult['result'] = 0;
						$arResult['error'] = 'You may not add texts.';
						$arResult['code'] = '';
					}
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'The text must be 1-1M characters' .
						' (UTF-16 code units). Currently: ' . strlen ($sText);
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
			$arResult['error'] = 'Data is missing.';
			$arResult['code'] = '';
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Sign in to save.';
		$arResult['code'] = '';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
	$arResult['code'] = '';
}
print (json_encode ($arResult));
?>
