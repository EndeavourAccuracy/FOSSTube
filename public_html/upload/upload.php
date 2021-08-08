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

$iAllowed = AllowedBytes();
$sAllowed = GetSizeHuman ($iAllowed);

/*** Do NOT move to fst_settings.php, because of shell_exec(). ***/
$GLOBALS['ffprobe'] = substr (shell_exec ('which ffprobe'), 0, -1);

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if (isset ($_SESSION['fst']['user_id']))
	{
		$iUserID = $_SESSION['fst']['user_id'];
		if (count ($_FILES) == 1) /*** Yes, 1. Processing one by one. ***/
		{
			if ($_FILES['file']['error'] == 0)
			{
				if ($_FILES['file']['size'] <= $iAllowed)
				{
					if (substr ($_FILES['file']['type'], 0, 6) === 'video/')
					{
						$sUploadToDir = dirname (__FILE__) . '/../uploads/';
						if (MakeDir ($sUploadToDir) === TRUE)
						{
							if (MayAdd ('videos') === TRUE)
							{
								$sVideoName = str_replace ('_', ' ', pathinfo
									($_FILES['file']['name'], PATHINFO_FILENAME));
								$sDTNow = date ('Y-m-d H:i:s');
								$sIP = GetIP();
								$sMD5 = md5_file ($_FILES['file']['tmp_name']);
								if ($sMD5 === FALSE) { $sMD5 = ''; }

								/*** $sSphMpProjection and $sSphStereo3DType ***/
								$sSphMpProjection = '';
								$sSphStereo3DType = '';
								$sExec = $GLOBALS['ffprobe'] . ' -loglevel 0 -print_format json -show_format -show_streams "' . $_FILES['file']['tmp_name'] . '" 2>&1';
								$xFormatStreams = shell_exec ($sExec);
								$arFormatStreams = json_decode ($xFormatStreams, TRUE);
								foreach ($arFormatStreams['streams'] as $arStream)
								{
									if ($arStream['codec_type'] == 'video')
									{
										if (isset ($arStream['side_data_list']))
										{
											foreach ($arStream['side_data_list'] as $arSideData)
											{
												switch ($arSideData['side_data_type'])
												{
													case 'Spherical Mapping':
														$sSphMpProjection = $arSideData['projection'];
														break;
													case 'Stereo 3D':
														$sSphStereo3DType = $arSideData['type'];
														break;
												}
											}
										}
									}
								}

								$query_insert = "INSERT INTO `fst_video` SET
									user_id='" . $iUserID . "',
									user_id_old='0',
									video_visibility='1',
									video_title='" . mysqli_real_escape_string
										($GLOBALS['link'], $sVideoName) . "',
									video_description='',
									video_thumbnail='3',
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
									video_preview='2',
									video_preview_bytes='0',
									video_360='2',
									video_360_bytes='0',
									video_360_width='0',
									video_360_height='0',
									video_720='2',
									video_720_bytes='0',
									video_720_width='0',
									video_720_height='0',
									video_1080='2',
									video_1080_bytes='0',
									video_1080_width='0',
									video_1080_height='0',
									video_ip='" . $sIP . "',
									video_views='0',
									video_deleted='0',
									video_deletedate='1970-01-01 00:00:00',
									video_adddate='" . $sDTNow . "',
									video_uploadedmd5='" . $sMD5 . "',
									video_text='',
									video_textsavedt='1970-01-01 00:00:00',
									video_istext='0',
									board_id='0',
									video_sph_mpprojection='" . $sSphMpProjection . "',
									video_sph_stereo3dtype='" . $sSphStereo3DType . "',
									projection_id='0',
									poll_id='0'";
								$result_insert = Query ($query_insert);
								$iVideoID = mysqli_insert_id ($GLOBALS['link']);
								$sCode = IDToCode ($iVideoID);

								$sUploadFrom = $_FILES['file']['tmp_name'];
								$sUploadTo = $sUploadToDir . $iVideoID;
								if (move_uploaded_file ($sUploadFrom, $sUploadTo) === TRUE)
								{
									$arResult['result'] = 1;
									$arResult['error'] = '';
								} else {
									$arResult['result'] = 0;
									$arResult['error'] = 'Could not move the uploaded file.';
								}
							} else {
								$arResult['result'] = 0;
								$arResult['error'] = 'You may not add videos.';
							}
						} else {
							$arResult['result'] = 0;
							$arResult['error'] = 'Could not create uploads/ directory.';
						}
					} else {
						$arResult['result'] = 0;
						$arResult['error'] = 'This is not a video (' .
							$_FILES['file']['type'] . ').';
					}
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'File is larger than ' . $sAllowed . '.';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'An error occurred.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'No file found; probably larger than ' .
				$sAllowed . '.';
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Sign in to upload.';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
}
print (json_encode ($arResult));
?>
