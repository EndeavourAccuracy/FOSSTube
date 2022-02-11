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
function PercBar ($fFilled)
/*****************************************************************************/
{
	$sBar = '';

	$iFilled = intval (round ($fFilled, 0));
	if ($iFilled != 0)
	{
		$sBar .= '<span class="pollbar filled" style="width:' .
			($iFilled * 2) . 'px;">&nbsp;</span>';
	}

	$iEmpty = 100 - $iFilled;
	if ($iEmpty != 0)
	{
		$sBar .= '<span class="pollbar empty" style="width:' .
			($iEmpty * 2) . 'px;">&nbsp;</span>';
	}

	$sBar .= ' ' . number_format ($fFilled, 1, '.', ',') . '%';

	return ($sBar);
}
/*****************************************************************************/
function OpenUntil ($sOpen)
/*****************************************************************************/
{
	$sUntil = '<span style="display:block; text-align:center;">';
	if ($sOpen === FALSE)
	{
		$sUntil .= 'This poll is closed.';
	} else if ($sOpen === TRUE) {
		$sUntil .= 'This poll never closes.';
	} else {
		$sDate = date ('j F Y', strtotime ($sOpen));
		$sTime = date ('H:i', strtotime ($sOpen));
		$sUntil .= 'This poll closes ' . $sDate .
			' <span style="font-size:12px;">(' . $sTime . ' UTC)</span>.';
	}
	$sUntil .= '</span>';

	return ($sUntil);
}
/*****************************************************************************/
function PollVote ($iPollID, $row_poll, $sOpen)
/*****************************************************************************/
{
	$sQuestion = $row_poll['poll_question'];
	$sOptions = $row_poll['poll_options'];
	$iMaxVotesPerUser = intval ($row_poll['poll_maxvotesperuser']);
	if ($iMaxVotesPerUser == 1)
		{ $sType = 'radio'; } else { $sType = 'checkbox'; }

	$sHTML = '';
	$sHTML .= '<div class="vote">';
	$sHTML .= OpenUntil ($sOpen);
	$sHTML .= '<span id="vote-question">' . Sanitize ($sQuestion) . '</span>';
	if ($iMaxVotesPerUser > 1)
	{
$sHTML .= '
<span style="display:block; font-style:italic;">
(You may choose max. ' . $iMaxVotesPerUser . ' options.)
</span>
';
	}
	$arOptions = preg_split ('/[\n\r]+/', $sOptions);
	$iNrOptions = 0;
	foreach ($arOptions as $sOption)
	{
		$iNrOptions++;
		$sHTML .= '<span style="display:block;">';
		$sHTML .= '<input id="option-' . $iNrOptions . '" type="' .
			$sType . '" name="options"> ' . Sanitize ($sOption);
		$sHTML .= '</span>';
	}
	$sHTML .= '<div id="vote-error" style="color:#f00; margin-top:10px;"></div>';
	if (isset ($_SESSION['fst']['user_id']))
	{
		$sHTML .= '<input type="button" id="vote" value="Vote">';
	} else {
		$sHTML .= '<span style="font-style:italic;">Sign in to vote.</span>';
	}
	$sHTML .= '</div>';

$sHTML .= '
<script>
$("#vote").click(function(){
	$("#vote").prop("value","Wait...");
	$("#vote").prop("disabled",true).css("opacity","0.5");

	var options = [];
	$("input[id^=\"option-\"]").each(function(){
		if ($(this).is(":checked"))
		{
			var option = $(this).attr("id").replace("option-","");
			options.push(option);
		}
	});

	$.ajax({
		type: "POST",
		url: "/v/vote.php",
		data: ({
			poll : "' . $iPollID . '",
			options : options,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				Poll();
			} else {
				$("#vote").prop("value","Vote");
				$("#vote").removeAttr("disabled").css("opacity","1");
				$("#vote-error").html(error);
			}
		},
		error: function() {
			$("#vote").prop("value","Vote");
			$("#vote").removeAttr("disabled").css("opacity","1");
			$("#vote-error").html("Error calling vote.php.");
		}
	});
});
</script>
';

	$arResult['result'] = 1;
	$arResult['error'] = '';
	$arResult['html'] = $sHTML;

	return ($arResult);
}
/*****************************************************************************/
function PollResult ($iPollID, $row_poll, $sOpen)
/*****************************************************************************/
{
	$sQuestion = $row_poll['poll_question'];
	$sOptions = $row_poll['poll_options'];

	/*** $arVotes and $iVotes ***/
	$arVotes = array();
	$iVotes = 0;
	$query_votes = "SELECT
			vote_option
		FROM `fst_vote`
		WHERE (poll_id='" . $iPollID . "')";
	$result_votes = Query ($query_votes);
	while ($row_votes = mysqli_fetch_assoc ($result_votes))
	{
		$iOption = intval ($row_votes['vote_option']);
		if (!isset ($arVotes[$iOption]))
		{
			$arVotes[$iOption] = 1;
		} else {
			$arVotes[$iOption]++;
		}
		$iVotes++;
	}

	$sHTML = '';
	$sHTML .= '<div class="poll-result">';
	$sHTML .= OpenUntil ($sOpen);
	$sHTML .= '<span id="vote-question">' . Sanitize ($sQuestion) . '</span>';
	$arOptions = preg_split ('/[\n\r]+/', $sOptions);
	$iOption = 0;
	foreach ($arOptions as $sOption)
	{
		$iOption++;
		$sHTML .= '<span style="display:block;">';
		if (isset ($arVotes[$iOption]))
		{
			$fPerc = ($arVotes[$iOption] / $iVotes) * 100;
			$sHTML .= PercBar ($fPerc) . ' (' . $arVotes[$iOption] . ')';
		} else {
			$sHTML .= PercBar (0);
		}
		$sHTML .= ' ' . Sanitize ($sOption);
		$sHTML .= '</span>';
	}
	$sHTML .= '</div>';

	$arResult['result'] = 1;
	$arResult['error'] = '';
	$arResult['html'] = $sHTML;

	return ($arResult);
}
/*****************************************************************************/

if (isset ($_POST['poll']))
{
	$iPollID = intval ($_POST['poll']);

	$query_poll = "SELECT
			poll_question,
			poll_options,
			poll_maxvotesperuser
		FROM `fst_poll`
		WHERE (poll_id='" . $iPollID . "')";
	$result_poll = Query ($query_poll);
	if (mysqli_num_rows ($result_poll) == 1)
	{
		$row_poll = mysqli_fetch_assoc ($result_poll);
		/***/
		$sOpen = PollOpen ($iPollID);

		if ($sOpen !== FALSE) /*** NO need to check for -1. ***/
		{
			if (isset ($_SESSION['fst']['user_id']))
			{
				$iUserID = intval ($_SESSION['fst']['user_id']);
				$sIP = GetIP();
				$iVoted = PollVoted ($iPollID, $iUserID, $sIP);
			} else { $iVoted = 0; }

			if ($iVoted == 0)
			{
				$arResult = PollVote ($iPollID, $row_poll, $sOpen);
			} else {
				$arResult = PollResult ($iPollID, $row_poll, $sOpen);
			}
		} else {
			$arResult = PollResult ($iPollID, $row_poll, $sOpen);
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Unknown poll.';
		$arResult['html'] = '';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Poll is missing.';
	$arResult['html'] = '';
}
print (json_encode ($arResult));
?>
