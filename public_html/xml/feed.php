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
function SanitizeXML ($sUserInput, $iMaxLength)
/*****************************************************************************/
{
	$sReturn = substr ($sUserInput, 0, $iMaxLength);
	$sReturn = htmlentities ($sReturn, ENT_XML1);
	$sReturn = str_ireplace ('javascript', 'JS', $sReturn);

	return ($sReturn);
}
/*****************************************************************************/
function RLastItem ($iUserID)
/*****************************************************************************/
{
	if ($iUserID == 0) { $sWhere = ""; }
		else { $sWhere = "AND (user_id='" . $iUserID . "')"; }
	$query_date = "SELECT
			video_adddate
		FROM `fst_video`
		WHERE (video_deleted='0')
		AND ((video_360='1') OR (video_istext='1'))
		" . $sWhere . "
		ORDER BY video_adddate DESC
		LIMIT 1";
	$result_date = Query ($query_date);
	if (mysqli_num_rows ($result_date) == 1)
	{
		$row_date = mysqli_fetch_assoc ($result_date);
		return (date ('r', strtotime ($row_date['video_adddate'])));
	} else {
		return (date ('r')); /*** Fallback. ***/
	}
}
/*****************************************************************************/
function Items ($iUserID)
/*****************************************************************************/
{
	if ($iUserID == 0) { $sWhere = ""; }
		else { $sWhere = "AND (user_id='" . $iUserID . "')"; }
	$query_videos = "SELECT
			fv.video_id,
			fv.video_title,
			fv.video_description,
			fc.category_name,
			fv.video_360_bytes,
			fv.video_adddate,
			fv.video_istext
		FROM `fst_video` fv
		LEFT JOIN `fst_category` fc
			ON fv.category_id = fc.category_id
		WHERE (video_deleted='0')
		AND ((video_360='1') OR (video_istext='1'))
		" . $sWhere . "
		ORDER BY video_adddate DESC
		LIMIT 15";
	$result_videos = Query ($query_videos);
	while ($row_videos = mysqli_fetch_assoc ($result_videos))
	{
		$iVideoID = intval ($row_videos['video_id']);
		$sTitle = $row_videos['video_title'];
		$sDesc = $row_videos['video_description'];
		$sCategory = $row_videos['category_name'];
		if ($sCategory == '') { $sCategory = '(not set)'; }
		$iBytes = intval ($row_videos['video_360_bytes']);
		$sPubDate = date ('r', strtotime ($row_videos['video_adddate']));
		$iIsText = intval ($row_videos['video_istext']);
		/***/
		$sCode = IDToCode ($iVideoID);
		$sLink = $GLOBALS['protocol'] . '://www.' . $GLOBALS['domain'] .
			'/v/' . $sCode;
		$sVideoURL = $GLOBALS['protocol'] . '://www.' . $GLOBALS['domain'] .
			VideoURL ($sCode, '360');

		print ('<item>' . "\n");
		print ("\t" . '<title>' . SanitizeXML ($sTitle, 100) . '</title>' . "\n");
		print ("\t" . '<link>' . SanitizeXML ($sLink, 500) . '</link>' . "\n");
		print ("\t" . '<description>' . SanitizeXML ($sDesc, 500) .
			'</description>' . "\n");
		print ("\t" . '<category>' . SanitizeXML ($sCategory, 100) .
			'</category>' . "\n");
		if ($iIsText == 0)
		{
			print ("\t" . '<enclosure url="' . $sVideoURL . '" length="' .
				$iBytes . '" type="video/mp4" />' . "\n");
		}
		print ("\t" . '<guid isPermaLink="true">' . SanitizeXML ($sLink, 500) .
			'</guid>' . "\n");
		print ("\t" . '<pubDate>' . $sPubDate . '</pubDate>' . "\n");
		print ('</item>' . "\n");
	}
}
/*****************************************************************************/

/*** $sUsername, $iUserID, $sLink ***/
$sUsername = '';
$iUserID = 0;
$sLink = $GLOBALS['protocol'] . '://www.' . $GLOBALS['domain'] . '/';
if (isset ($_GET['user']))
{
	$arUser = UserExists ($_GET['user']);
	if ($arUser !== FALSE)
	{
		$sUsername = $arUser['username'];
		$iUserID = GetUserID ($sUsername);
		$sLink .= 'user/' . $sUsername;
	} else {
		print ('Unknown user "' . Sanitize ($_GET['user']) . '".');
		exit();
	}
}

/*** $sTitle, $sDesc, $arImage ***/
$arImage = array();
if ($iUserID == 0)
{
	$sTitle = $GLOBALS['name'] . ' · ' . $GLOBALS['name_seo_alternative'];
	$sDesc = $GLOBALS['short_description'];
	$arImage['url'] = $GLOBALS['protocol'] . '://www.' .
		$GLOBALS['domain'] . '/images/' . $GLOBALS['header_image_name'];
	$arImage['width'] = $GLOBALS['header_image_width'];
	$arImage['height'] = $GLOBALS['header_image_height'];
} else {
	$sTitle = $sUsername . ' · ' . $GLOBALS['name'];
	$sDesc = GetUserInfo ($iUserID, 'user_information');

	/*** $sURL ***/
	$sCustom = '/avatars/' . $sUsername . '_large.png';
	if (file_exists (dirname (__FILE__) . '/..' . $sCustom))
	{
		$sURL = $sCustom;
	} else {
		$sURL = '/images/avatar_large.png';
	}

	$arImage['url'] = $GLOBALS['protocol'] . '://www.' .
		$GLOBALS['domain'] . $sURL;
	$arImage['width'] = 200;
	$arImage['height'] = 200;
}

header ('content-type: text/xml');

print ('<?xml version="1.0" encoding="UTF-8" ?>' . "\n");
print ('<rss version="2.0">' . "\n");
print ('<channel>' . "\n");
print ('<title>' . SanitizeXML ($sTitle, 100) . '</title>' . "\n");
print ('<link>' . SanitizeXML ($sLink, 500) . '</link>' . "\n");
print ('<description>' . SanitizeXML ($sDesc, 500) . '</description>' . "\n");
print ('<image>' . "\n");
print ("\t" . '<url>' . SanitizeXML ($arImage['url'], 500) . '</url>' . "\n");
print ("\t" . '<title>' . SanitizeXML ($sTitle, 100) . '</title>' . "\n");
print ("\t" . '<link>' . SanitizeXML ($sLink, 500) . '</link>' . "\n");
print ("\t" . '<width>' . $arImage['width'] . '</width>' . "\n");
print ("\t" . '<height>' . $arImage['height'] . '</height>' . "\n");
print ('</image>' . "\n");
print ('<lastBuildDate>' . date ('r') . '</lastBuildDate>' . "\n");
print ('<pubDate>' . RLastItem ($iUserID) . '</pubDate>' . "\n");

Items ($iUserID);

print ('</channel>' . "\n");
print ('</rss>' . "\n");
?>
