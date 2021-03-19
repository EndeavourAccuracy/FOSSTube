<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.1 (March 2021)
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
function ForumDate ($sDateTime)
/*****************************************************************************/
{
	if (($sDateTime == '1970-01-01 00:00:00') || ($sDateTime == NULL))
		{ return ('-'); }

	$sDate = date ('d M y', strtotime ($sDateTime));
	$sTime = date ('H:i', strtotime ($sDateTime));

	return ($sDate . ' <span style="font-size:12px;">(' . $sTime . ')</span>');
}
/*****************************************************************************/
function OverviewBoards ()
/*****************************************************************************/
{
	print ('<p>Pick a board:</p>');

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
print ('
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
');
	while ($row_board = mysqli_fetch_assoc ($result_board))
	{
		$iBoardID = intval ($row_board['board_id']);
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

print ('
<tr>
<td><a href="/forum/' . $iBoardID . '">' . $sBoardName . '</a></td>
<td>' . $xNrTopics . '</td>
<td>' . $xNrPosts . '</td>
<td>' . $sLastPostDate . '</td>
</tr>
');
	}
print ('
</tbody>
</table>
');
}
/*****************************************************************************/
function Create ($iBoard)
/*****************************************************************************/
{
print ('
<a id="create" href="javascript:;">Create a new topic</a> on this board.
<div id="create-div" style="display:none;">

<span style="display:block; margin:10px 0;">
<span style="color:#f00;">
Sexually explicit content is not allowed.
</span>
<br>
See the <a href="/terms/">Terms of service</a> for more information.
</span>

<input type="hidden" id="board" value="' . $iBoard . '">
<label for="title" class="lbl">Title:</label>
<p style="margin-bottom:5px; font-style:italic;">(Try to make your title descriptive. That is, summarize the substance of your topic. Avoid nondescript titles such as "check this out" or "we need this".)</p>
<input type="text" id="title" maxlength="100" style="width:600px; max-width:100%;">
<label for="description" class="lbl">Text:</label>
<textarea id="description" style="display:block; width:600px; max-width:100%; height:200px;"></textarea>
<div id="create-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="create-button" value="Create topic" style="margin-top:10px;">
</div>

<script>
$("#create").click(function(){
	if ($("#create-div").css("display") == "none")
		{ $("#create-div").css("display", "block"); }
			else { $("#create-div").css("display", "none"); }
});
$("#create-button").click(function(){
	var board = $("#board").val();
	var title = $("#title").val();
	var description = $("#description").val();

	$.ajax({
		type: "POST",
		url: "/forum/create.php",
		data: ({
			board : board,
			title : title,
			description : description,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			var code = data["code"];
			if (result == 1)
			{
				window.location.replace("/forum/" + board);
			} else {
				$("#create-error").html(error);
			}
		},
		error: function() {
			$("#create-error").html("Error calling create.php.");
		}
	});
});
</script>
');
}
/*****************************************************************************/
function ShowTopics ($iBoard)
/*****************************************************************************/
{
	$query_topics = "SELECT
			fv.video_id,
			fv.video_title,
			fu.user_username AS created_username,
			fv.video_adddate AS created_date,
			(SELECT COUNT(*) FROM `fst_comment` fc WHERE (fc.video_id IN (SELECT video_id FROM `fst_video` WHERE (video_id=fv.video_id) AND (video_deleted='0'))) AND (fc.comment_hidden='0')) AS nr_replies,
			fv.video_views,
			(SELECT fu.user_username FROM `fst_comment` fc LEFT JOIN `fst_user` fu ON fc.user_id=fu.user_id WHERE (fc.video_id IN (SELECT video_id FROM `fst_video` WHERE (video_id=fv.video_id) AND (video_deleted='0'))) AND (fc.comment_hidden='0') ORDER BY comment_adddate DESC LIMIT 1) AS lastreply_username,
			(SELECT comment_adddate FROM `fst_comment` fc WHERE (fc.video_id IN (SELECT video_id FROM `fst_video` WHERE (video_id=fv.video_id) AND (video_deleted='0'))) AND (fc.comment_hidden='0') ORDER BY comment_adddate DESC LIMIT 1) AS lastreply_date
		FROM `fst_video` fv
		LEFT JOIN `fst_user` fu
			ON fv.user_id = fu.user_id
		WHERE (video_istext='3')
		AND (board_id='" . $iBoard . "')
		AND (video_deleted='0')
		ORDER BY CASE WHEN lastreply_date > created_date THEN lastreply_date ELSE created_date END DESC";
	$result_topics = Query ($query_topics);
	if (mysqli_num_rows ($result_topics) != 0)
	{
print ('
<div id="topics" class="container-fluid">

<div id="topics-header" class="row">
<div class="col-sm-3">Topic</div>
<div class="col-sm-3">Created (UTC)</div>
<div class="col-sm-3">#R / #V</div>
<div class="col-sm-3">Last reply (UTC)</div>
</div>
');
		while ($row_topics = mysqli_fetch_assoc ($result_topics))
		{
			$iVideoID = $row_topics['video_id'];
			$sCode = IDToCode ($iVideoID);
			$sVideoTitle = $row_topics['video_title'];
			/***/
			$sCreatedUser = $row_topics['created_username'];
			$sCreatedDate = ForumDate ($row_topics['created_date']);
			/***/
			$iNrReplies = intval ($row_topics['nr_replies']);
			$iVideoViews = intval ($row_topics['video_views']);
			/***/
			$sLastReplyUser = $row_topics['lastreply_username'];
			$sLastReplyDate = ForumDate ($row_topics['lastreply_date']);
			if ($sLastReplyUser == NULL)
			{
				$sLastReply = '-';
			} else {
				$sLastReply = $sLastReplyDate . ' by <a href="/user/' .
					$sLastReplyUser . '">' . $sLastReplyUser . '</a>';
			}

print ('
<div class="row">

<div class="col-sm-3">
<a href="/v/' . $sCode . '">' . Sanitize ($row_topics['video_title']) . '</a>
</div>

<div class="col-sm-3">
' . $sCreatedDate . ' by <a href="/user/' . $sCreatedUser . '">' . $sCreatedUser . '</a>
</div>

<div class="col-sm-3">
' . $iNrReplies . ' / ' . $iVideoViews . '
</div>

<div class="col-sm-3">
' . $sLastReply . '
</div>

</div>
');
		}
print ('
</div>
');
	} else {
		print ('<span style="display:block; margin-top:10px;' .
			' font-style:italic;">No topics.</span>');
	}
}
/*****************************************************************************/

if (!isset ($_GET['board']))
{
	HTMLStart ('Forum boards', 'Forum', 'Forum', 0, FALSE);
	print ('<h1>Forum boards</h1>');

	/*** Deleted note. ***/
	if ((isset ($_SESSION['fst']['deleted'])) &&
		($_SESSION['fst']['deleted'] == 2))
	{
		print ('<div class="note deleted">Deleted.</div>');
		unset ($_SESSION['fst']['deleted']);
	}

	OverviewBoards();
} else {
	$iBoard = intval ($_GET['board']);

	/*** $sBoardName ***/
	$query_board = "SELECT
			board_name,
			board_description
		FROM `fst_board`
		WHERE (board_id='" . $iBoard . "')";
	$result_board = Query ($query_board);
	$row_board = mysqli_fetch_assoc ($result_board);
	$sBoardName = $row_board['board_name'];
	$sBoardDesc = $row_board['board_description'];

	HTMLStart ('Board "' . $sBoardName . '"', 'Forum', 'Forum', 0, FALSE);
	print ('<h1>Board "' . $sBoardName . '"</h1>');
	if ($row_board['board_description'] != '') { print ('<p style="text-align:center;">' . Sanitize ($row_board['board_description']) . '</p>'); }

	LinkBack ('/forum/', 'Forum boards');

	if (isset ($_SESSION['fst']['user_id']))
	{
		if (IsMod() === TRUE)
		{
print ('
<span style="display:block; font-style:italic;">
Moderator accounts can not create topics.
</span>
');
		} else if (MayAdd ('topics') === FALSE) {
print ('
<span style="display:block; font-style:italic;">
You may not add topics.
</span>
');
		} else {
			Create ($iBoard);
		}
	} else {
print ('
<span style="font-style:italic;">
<a href="/signin/">Sign in</a> to create topics.
</span>
');
	}

	ShowTopics ($iBoard);
}

HTMLEnd();
?>
