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

include_once (dirname (__FILE__) . '/../fst_base.php');

/*****************************************************************************/
function Trending ($sDate)
/*****************************************************************************/
{
	$query_trending = "SELECT
			trending_rank,
			video_id,
			trending_title
		FROM `fst_trending`
		WHERE (trending_date='" . $sDate . "')";
	$result_trending = Query ($query_trending);
	if (mysqli_num_rows ($result_trending) != 0)
	{
		$sHTML = '';

		/*** $arYesterday. ***/
		$arYesterday = array();
		$sDateYesterday = date ('Y-m-d', strtotime ('-1 day', strtotime ($sDate)));
		$query_yesterday = "SELECT
				trending_rank,
				video_id
			FROM `fst_trending`
			WHERE (trending_date='" . $sDateYesterday . "')";
		$result_yesterday = Query ($query_yesterday);
		while ($row_yesterday = mysqli_fetch_assoc ($result_yesterday))
		{
			$iRank = intval ($row_yesterday['trending_rank']);
			$iVideoID = intval ($row_yesterday['video_id']);
			/***/
			$arYesterday[$iVideoID] = $iRank;
		}

		if ($sDate == date ('Y-m-d'))
		{
			$sDateTime = date ('Y-m-d H:i:s');
			$sDateOld = strtotime ($sDate);
			$sDateNew = strtotime ($sDateTime);
			$sHoursPassed = round ((($sDateNew - $sDateOld) / 60 / 60), 1);
			$sHTML .= '<p style="color:#f00;">Only ' . $sHoursPassed .
				' hours of this (UTC) day have passed, therefore this' .
				' overview is subject to change.</p>';
		} else {
			$sHTML .= '<p>This overview is definitive.</p>';
		}

$sHTML .= '
<table id="trending-table">
<thead>
<tr id="trending-header">
<th>#</th>
<th>Ch.</th>
<th>Content</th>
</tr>
</thead>
<tbody>
';

		while ($row_trending = mysqli_fetch_assoc ($result_trending))
		{
			$iRank = intval ($row_trending['trending_rank']);
			$iVideoID = intval ($row_trending['video_id']);
			$sCode = IDToCode ($iVideoID);
			$sTitle = $row_trending['trending_title'];

			/*** $sChange ***/
			if (isset ($arYesterday[$iVideoID]))
			{
				if ($iRank == $arYesterday[$iVideoID])
				{
					$sChange = 'same';
				} else if ($iRank < $arYesterday[$iVideoID]) {
					$sChange = 'up';
				} else if ($iRank > $arYesterday[$iVideoID]) {
					$sChange = 'down';
				}
			} else {
				$sChange = 'new';
			}

			$sHTML .= '<tr>';
			$sHTML .= '<td>' . $iRank . '</td>';
			$sHTML .= '<td><img src="/trending/rank_' . $sChange .
				'.png" alt="Ch."></td>';
			$sHTML .= '<td><a href="/v/' . $sCode . '">' .
				Sanitize ($sTitle) . '</a></td>';
			$sHTML .= '</tr>';
		}

$sHTML .= '
</tbody>
</table>
';
	} else {
		$sHTML = 'No data.';
	}

	return ($sHTML);
}
/*****************************************************************************/

if (isset ($_POST['date']))
{
	$sDate = $_POST['date'];
	if (date ('Y-m-d', strtotime ($sDate)) == $sDate)
	{
		$arResult['result'] = 1;
		$arResult['error'] = '';
		$arResult['html'] = Trending ($sDate);
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Invalid date.';
		$arResult['html'] = '';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Date is missing.';
	$arResult['html'] = '';
}
print (json_encode ($arResult));
?>
