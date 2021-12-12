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

include_once (dirname (__FILE__) . '/fst_base.php');

/*****************************************************************************/

if (isset ($_POST['search_query']))
{
	$sSearch = $_POST['search_query'];

	/*** $sWhereStart ***/
	$sWhereStart = "WHERE (video_deleted='0') AND ((video_360='1') OR (video_istext='1'))";

	/*** $sWhereTitle ***/
	$sWhereTitle = '';
	$arSearch = explode (' ', $sSearch);
	foreach ($arSearch as $sBit)
	{
		$sWhereTitle .= " AND (video_title LIKE '%" . str_replace ('%', '\%',
			mysqli_real_escape_string ($GLOBALS['link'], $sBit)) . "%')";
	}

	/*** $sHTML ***/
	$sHTML = '';
	$query_titles = "SELECT
			video_title,
			video_likes
		FROM `fst_video`
		" . $sWhereStart . "
		" . $sWhereTitle . "
		ORDER BY video_likes DESC, video_title
		LIMIT 10";
	$result_titles = Query ($query_titles);
	while ($row_titles = mysqli_fetch_assoc ($result_titles))
	{
		$sHTML .= '<option value="' .
			Sanitize ($row_titles['video_title']) . '" />';
	}

	$arResult['result'] = 1;
	$arResult['error'] = '';
	$arResult['html'] = $sHTML;
	$arResult['matches_title'] = MatchesTitle ($sSearch, 3);
	$arResult['matches_any'] = MatchesAny ($sSearch, 3);
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Missing data.';
	$arResult['html'] = '';
	$arResult['matches_title'] = 0;
	$arResult['matches_any'] = 0;
}
print (json_encode ($arResult));
?>
