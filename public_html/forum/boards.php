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
function NewContent ($iBoardID, $iUserID)
/*****************************************************************************/
{
	$arTopics = Topics ($iBoardID, $iUserID, 1);
	if ($arTopics === FALSE) /*** No topics. ***/
	{
		return (FALSE);
	} else {
		foreach ($arTopics as $iKey => $arTopic)
		{
			if ($arTopic['new_content'] === TRUE) { return (TRUE); }
		}
		return (FALSE);
	}
}
/*****************************************************************************/
function Boards ()
/*****************************************************************************/
{
	$query_board = "SELECT
			fb.board_id,
			fb.board_name,
			(SELECT COUNT(*) FROM `fst_video` WHERE (video_istext='3') AND (video_deleted='0') AND (board_id=fb.board_id)) AS nr_topics,
			(SELECT COUNT(*) FROM `fst_comment` fc WHERE (fc.video_id IN (SELECT video_id FROM `fst_video` WHERE (board_id=fb.board_id) AND (video_deleted='0'))) AND (fc.comment_hidden='0')) AS nr_replies,
			(SELECT video_adddate FROM `fst_video` fv WHERE (fv.video_id IN (SELECT video_id FROM `fst_video` WHERE (board_id=fb.board_id) AND (video_deleted='0'))) ORDER BY video_adddate DESC LIMIT 1) AS lasttopic_date,
			(SELECT comment_adddate FROM `fst_comment` fc WHERE (fc.video_id IN (SELECT video_id FROM `fst_video` WHERE (board_id=fb.board_id) AND (video_deleted='0'))) AND (fc.comment_hidden='0') ORDER BY comment_adddate DESC LIMIT 1) AS lastreply_date
		FROM `fst_board` fb
		ORDER BY board_order";
	$result_board = Query ($query_board);

$sHTML = '
<table id="boards-table">
<thead>
<tr id="boards-header">
<th>Board</th>
<th>#T</th>
<th>#P</th>
<th>Last post (UTC)</th>
</tr>
</thead>
<tbody>
';

	while ($row_board = mysqli_fetch_assoc ($result_board))
	{
		$iBoardID = intval ($row_board['board_id']);
		if (isset ($_SESSION['fst']['user_id']))
		{
			$iUserID = intval ($_SESSION['fst']['user_id']);
			$bNew = NewContent ($iBoardID, $iUserID);
		} else { $bNew = FALSE; }
		$sBoardName = $row_board['board_name'];
		$xNrTopics = intval ($row_board['nr_topics']);
		$xNrReplies = intval ($row_board['nr_replies']);
		$xNrPosts = $xNrTopics + $xNrReplies;
		if (strtotime ($row_board['lastreply_date']) >
			strtotime ($row_board['lasttopic_date']))
		{
			$sLastPostDate = ForumDate ($row_board['lastreply_date']);
		} else {
			$sLastPostDate = ForumDate ($row_board['lasttopic_date']);
		}
		/***/
		if ($xNrTopics == 0) { $xNrTopics = '-'; }
		if ($xNrPosts == 0) { $xNrPosts = '-'; }

$sHTML .= '
<tr';

		if ($bNew === TRUE) { $sHTML .= ' class="new"'; }

$sHTML .= '>
<td><a href="/forum/' . $iBoardID . '">' . $sBoardName . '</a></td>
<td>' . $xNrTopics . '</td>
<td>' . $xNrPosts . '</td>
<td>' . $sLastPostDate . '</td>
</tr>
';
	}

$sHTML .= '
</tbody>
</table>
';

	return ($sHTML);
}
/*****************************************************************************/

$arResult['result'] = 1;
$arResult['error'] = '';
$arResult['html'] = Boards();

print (json_encode ($arResult));
?>
