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

/*****************************************************************************/
function ValidValue ($sTable, $sColumn, $iValue)
/*****************************************************************************/
{
	if ($iValue == 0) { return (TRUE); }

	$query_value = "SELECT
			" . $sColumn . "
		FROM `" . $sTable . "`
		WHERE (" . $sColumn . "='" . $iValue . "')";
	$result_value = Query ($query_value);
	if (mysqli_num_rows ($result_value) == 1)
	{
		return (TRUE);
	} else {
		return (FALSE);
	}
}
/*****************************************************************************/

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if ((isset ($_POST['code'])) &&
		(isset ($_POST['title'])) &&
		(isset ($_POST['description'])) &&
		(isset ($_POST['thumb'])) &&
		(isset ($_POST['tags'])) &&
		(isset ($_POST['license'])) &&
		(isset ($_POST['category'])) &&
		(isset ($_POST['restricted'])) &&
		(isset ($_POST['allow'])) &&
		(isset ($_POST['show'])) &&
		(isset ($_POST['language'])) &&
		(isset ($_POST['nsfw'])) &&
		(isset ($_POST['subtitles'])) &&
		(isset ($_POST['projection'])))
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
			$sTitle = $_POST['title'];
			$sDesc = $_POST['description'];
			$iThumb = intval ($_POST['thumb']);
			$sTags = $_POST['tags'];
			$iLicense = intval ($_POST['license']);
			$iCat = intval ($_POST['category']);
			$iRestricted = intval ($_POST['restricted']);
			$iCommentsAllow = intval ($_POST['allow']);
			$iCommentsShow = intval ($_POST['show']);
			$iLanguageID = intval ($_POST['language']);
			$iNSFW = intval ($_POST['nsfw']);
			$sSubtitles = $_POST['subtitles'];
			$iProjection = intval ($_POST['projection']);

			if ((strlen ($sTitle) >= 1) && (strlen ($sTitle) <= 100))
			{
				if (strlen ($sDesc) <= 3000)
				{
					if ((($iThumb >= 1) && ($iThumb <= 6)) &&
						(strlen ($sTags) <= 100) &&
						(($iLicense >= 1) && ($iLicense <= 2)) &&
						(ValidValue ('fst_category', 'category_id', $iCat) === TRUE) &&
						(($iRestricted >= 0) && ($iRestricted <= 1)) &&
						(($iCommentsAllow >= 0) && ($iCommentsAllow <= 1)) &&
						(($iCommentsShow >= 1) && ($iCommentsShow <= 2)) &&
						(($iLanguageID == 0) ||
						(LanguageName ('eng', $iLanguageID) !== FALSE)) &&
						(($iNSFW >= 0) && ($iNSFW <= 2)) &&
						(strlen ($sSubtitles) <= 1000000) &&
						(ValidValue ('fst_projection', 'projection_id',
							$iProjection) === TRUE))
					{
						$sDTNow = date ('Y-m-d H:i:s');

						$query_save = "UPDATE `fst_video` SET
							video_title='" . mysqli_real_escape_string
								($GLOBALS['link'], $sTitle) . "',
							video_description='" . mysqli_real_escape_string
								($GLOBALS['link'], $sDesc) . "',
							video_thumbnail='" . $iThumb . "',
							video_tags='" . mysqli_real_escape_string
								($GLOBALS['link'], $sTags) . "',
							video_license='" . $iLicense . "',
							category_id='" . $iCat . "',
							video_restricted='" . $iRestricted . "',
							video_comments_allow='" . $iCommentsAllow . "',
							video_comments_show='" . $iCommentsShow . "',
							language_id='" . $iLanguageID . "',
							video_nsfw='" . $iNSFW . "',
							video_subtitles='" . mysqli_real_escape_string
								($GLOBALS['link'], $sSubtitles) . "',
							projection_id='" . $iProjection . "',
							video_adddate=IF(video_istext='2','" . $sDTNow . "',video_adddate),
							video_istext=IF(video_istext='2','1',video_istext)
							WHERE (video_id='" . $iVideoID . "')";
						$result_save = Query ($query_save);
						if (mysqli_affected_rows ($GLOBALS['link']) == 1)
						{
							$_SESSION['fst']['saved'] = 1;
							$arResult['result'] = 1;
							$arResult['error'] = '';
						} else {
							$arResult['result'] = 0;
							$arResult['error'] = 'Nothing changed.';
						}
					} else {
						$arResult['result'] = 0;
						$arResult['error'] = 'Unexpected values.';
					}
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'The description must be max. 3000 characters' .
						' (UTF-16 code units). Currently: ' . strlen ($sDesc);
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'The title must be 1-100 characters' .
					' (UTF-16 code units). Currently: ' . strlen ($sTitle);
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
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
}
print (json_encode ($arResult));
?>
