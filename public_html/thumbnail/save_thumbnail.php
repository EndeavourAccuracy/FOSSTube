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

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if (intval (GetUserInfo ($_SESSION['fst']['user_id'],
		'user_priv_customthumbnails')) == 1)
	{
		if (isset ($_POST['code']))
		{
			$sCode = $_POST['code'];
			$iVideoID = CodeToID ($sCode);
			$query_video = "SELECT
					video_id
				FROM `fst_video`
				WHERE (user_id='" . $_SESSION['fst']['user_id'] . "')
				AND (video_id='" . $iVideoID . "')";
			$result_video = Query ($query_video);
			if (mysqli_num_rows ($result_video) == 1)
			{
				if (isset ($_FILES['file']))
				{
					$sType = $_FILES['file']['type'];
					$sTmpName = $_FILES['file']['tmp_name'];
					$sError = $_FILES['file']['error'];
					$iSize = $_FILES['file']['size'];

					if ($sError == "0")
					{
						if (($sType == 'image/png') ||
							($sType == 'image/jpeg') ||
							($sType == 'image/gif'))
						{
							if ($iSize < (1024 * 1024 * 5)) /*** 5MB ***/
							{
								if (file_exists ($sTmpName))
								{
									$arSize = getimagesize ($sTmpName);
									if ($arSize !== FALSE)
									{
										$iWidth = $arSize[0];
										$iHeight = $arSize[1];

										switch ($sType)
										{
											case 'image/png':
												$rImage = @imagecreatefrompng ($sTmpName);
												break;
											case 'image/jpeg':
												$rImage = @imagecreatefromjpeg ($sTmpName);
												break;
											case 'image/gif':
												$rImage = @imagecreatefromgif ($sTmpName);
												break;
										}
										if ($rImage !== FALSE)
										{
											/*** 320x180 ***/
											$rImage180 = imagecreatetruecolor (320, 180);
											imagecopyresampled ($rImage180, $rImage, 0, 0, 0, 0,
												320, 180, $iWidth, $iHeight);
											$sOut = dirname (__FILE__) . '/..' .
												ThumbURL ($sCode, '180', 6, FALSE);
											$bCreated1 = @imagejpeg ($rImage180, $sOut, 50);
											imagedestroy ($rImage180);

											/*** 1280x720 ***/
											$rImage720 = imagecreatetruecolor (1280, 720);
											imagecopyresampled ($rImage720, $rImage, 0, 0, 0, 0,
												1280, 720, $iWidth, $iHeight);
											$sOut = dirname (__FILE__) . '/..' .
												ThumbURL ($sCode, '720', 6, FALSE);
											$bCreated2 = @imagejpeg ($rImage720, $sOut, 50);
											imagedestroy ($rImage720);

											$query_thumbnail = "UPDATE `fst_video` SET
													video_thumbnail='6'
												WHERE (video_id='" . $iVideoID . "')";
											$result_thumbnail = Query ($query_thumbnail);

											if (($bCreated1 === TRUE) && ($bCreated2 === TRUE))
											{
												$arResult['result'] = 1;
												$arResult['error'] = '';
											} else {
												$arResult['result'] = 0;
												$arResult['error'] = 'Thumbnail creation may' .
													' have failed.';
											}
										} else {
											$arResult['result'] = 0;
											$arResult['error'] = 'Could not create image.' .
												' Is the filename extension correct?';
										}
									} else {
										$arResult['result'] = 0;
										$arResult['error'] = 'Could not get dimensions.';
									}
								} else {
									$arResult['result'] = 0;
									$arResult['error'] = 'Uploaded file not found.';
								}
							} else {
								$arResult['result'] = 0;
								$arResult['error'] = 'File is too large (5MB).';
							}
						} else {
							$arResult['result'] = 0;
							$arResult['error'] = 'Incorrect file type.';
						}
					} else {
						$arResult['result'] = 0;
						$arResult['error'] = $sError;
					}
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'No file provided.';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'Unexpected video code.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Data is missing.';
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Privilege revoked.';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
}
print (json_encode ($arResult));
?>
