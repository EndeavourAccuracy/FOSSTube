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
								/*** 200x200 ***/
								$rImage200 = imagecreatetruecolor (200, 200);
								imagecopyresampled ($rImage200, $rImage, 0, 0, 0, 0,
									200, 200, $iWidth, $iHeight);
								$sOut = dirname (__FILE__) . '/../avatars/' .
									$_SESSION['fst']['user_username'] . '_large.png';
								$bCreated1 = imagepng ($rImage200, $sOut, 9);
								imagedestroy ($rImage200);

								/*** 50x50 ***/
								$rImage50 = imagecreatetruecolor (50, 50);
								imagecopyresampled ($rImage50, $rImage, 0, 0, 0, 0,
									50, 50, $iWidth, $iHeight);
								$sOut = dirname (__FILE__) . '/../avatars/' .
									$_SESSION['fst']['user_username'] . '_small.png';
								$bCreated2 = imagepng ($rImage50, $sOut, 9);
								imagedestroy ($rImage50);

								$sIP = GetIP();
								$query_avatar = "UPDATE `fst_user` SET
										user_avatarset='1',
										user_avatarip='" . $sIP . "'
									WHERE (user_id='" . $_SESSION['fst']['user_id'] . "')";
								$result_avatar = Query ($query_avatar);

								if (($bCreated1 === TRUE) && ($bCreated2 === TRUE))
								{
									$arResult['result'] = 1;
									$arResult['error'] = '';
								} else {
									$arResult['result'] = 0;
									$arResult['error'] = 'Avatar creation may have failed.';
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
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
}
print (json_encode ($arResult));
?>
