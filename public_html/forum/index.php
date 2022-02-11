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

/*****************************************************************************/
function ShowBoards ()
/*****************************************************************************/
{
	if (isset ($_SESSION['fst']['user_id']))
	{
print ('
<input type="button" id="read" value="Mark everything as read" style="margin-bottom:10px;">

<script>
$("#read").click(function(){
	if (confirm ("Mark everything as read?")){
		$("#read").prop("value","Wait...");
		$("#read").prop("disabled",true).css("opacity","0.5");

		$.ajax({
			type: "POST",
			url: "/forum/read.php",
			data: ({
				csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
			}),
			dataType: "json",
			success: function(data) {
				var result = data["result"];
				var error = data["error"];
				if (result == 1)
				{
					window.location.replace("/forum/");
				} else {
					alert(error);
				}
			},
			error: function() {
				alert("Error calling read.php.");
			}
		});
	}
});
</script>
');
	}

	print ('<p>Pick a board:</p>');

print ('
<div id="boards-error" style="color:#f00;"></div>
<div id="boards">
<img src="/images/loading.gif" alt="loading">
</div>

<p style="margin-top:10px;">UTC is currently: ' .
	ForumDate (date ('Y-m-d H:i:s')) . '</p>

<script>
$(document).ready(function() {
	Boards();
});
</script>
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
<div id="poll" style="margin-top:10px; margin-bottom:-5px;"><a href="javascript:;">Add poll</a></div>
<div id="poll-div" style="display:none; margin-top:10px;">
<label for="question" class="lbl">Poll question:</label>
<input type="text" id="question" maxlength="100" style="width:600px; max-width:100%;">
<label for="options" class="lbl">Place each poll answer on a new line:</label>
<textarea id="options" style="display:block; width:600px; max-width:100%; height:200px; margin-bottom:10px;"></textarea>
<label for="maxvotesperuser" class="lbl">The number of answers each user may select when voting:</label>
<input type="text" id="maxvotesperuser" maxlength="10" style="width:600px; max-width:100%;" value="1">
<label for="nrdays" class="lbl">The number of days to run the poll (0 = forever):</label>
<input type="text" id="nrdays" maxlength="10" style="width:600px; max-width:100%; margin-bottom:0;" value="7">
</div>
<div id="create-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="create-button" value="Create topic">
</div>

<script>
$("#create").click(function(){
	if ($("#create-div").css("display") == "none")
		{ $("#create-div").css("display", "block"); }
			else { $("#create-div").css("display", "none"); }
});
$("#poll").click(function(){
	if ($("#poll-div").css("display") == "none")
		{ $("#poll-div").css("display", "block"); }
			else { $("#poll-div").css("display", "none"); }
});
$("#create-button").click(function(){
	var board = $("#board").val();
	var title = $("#title").val();
	var description = $("#description").val();
	var question = $("#question").val();
	var options = $("#options").val();
	var maxvotesperuser = $("#maxvotesperuser").val();
	var nrdays = $("#nrdays").val();

	$.ajax({
		type: "POST",
		url: "/forum/create.php",
		data: ({
			board : board,
			title : title,
			description : description,
			question : question,
			options : options,
			maxvotesperuser : maxvotesperuser,
			nrdays : nrdays,
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

	ShowBoards();
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

print ('
<div id="topics-error" style="color:#f00;"></div>
<div id="topics" data-board="' . $iBoard . '">
<img src="/images/loading.gif" alt="loading">
</div>

<script>
$(document).ready(function() {
	Topics (0);
});
</script>
');
}

HTMLEnd();
?>
