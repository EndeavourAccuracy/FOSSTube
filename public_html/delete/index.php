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
function AskConfirm ($iUserID)
/*****************************************************************************/
{
	$arCodes = array();
	$arCodesVerified = array();
	foreach ($_POST as $key => $value)
	{
		if ((strpos ($key, 'delete-') === 0) &&
			(strtoupper ($value) === 'ON'))
		{
			$sCode = substr ($key, 7);
			$iVideoID = CodeToID ($sCode);
			array_push ($arCodes, $iVideoID);
		}
	}
	if (count ($arCodes) != 0)
	{
		$query_video = "SELECT
				user_id_old,
				video_id,
				video_title
			FROM `fst_video`
			WHERE (user_id='" . $iUserID . "')
			AND (video_id IN (" . implode (',', $arCodes) . "))";
		$result_video = Query ($query_video);
		$iNrRows = mysqli_num_rows ($result_video);
	} else { $iNrRows = 0; }
	if ($iNrRows != 0)
	{
print ('
<span style="display:block; margin-bottom:10px;">
<input type="checkbox" id="semi"> Allow other users to adopt the video');
		if ($iNrRows > 1) { print ('s'); }
print (' by semi-deleting. <a target="_blank" href="/faq/#Q3"><img src="/images/icon_info.png" alt="info" style="vertical-align:middle;"></a>
</span>
');
		print ('<span style="display:block; margin-bottom:10px;">');
		if ($iNrRows == 1)
			{ print ('Really delete this video?'); }
				else { print ('Really delete these ' . $iNrRows . ' videos?'); }
		print ('</span>');
		print ('<span style="display:inline-block; margin-bottom:5px; border:1px solid #000; padding:10px; background-color:#fff;">');
		while ($row_video = mysqli_fetch_assoc ($result_video))
		{
			$iUserIDOld = $row_video['user_id_old'];
			$iVideoID = $row_video['video_id'];

			print (Sanitize ($row_video['video_title']));
			if (($iUserIDOld != 0) && ($iUserIDOld != $iUserID))
				{ print (' <span style="color:#00f;">(will automatically be up for re-adoption)</span>'); }
			print ('<br>');
			array_push ($arCodesVerified, IDToCode ($iVideoID));
		}
print ('
</span>
<div id="delete-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="cancel" value="Cancel" onclick="javascript:window.location.href=\'/videos/\';">
<input type="button" id="delete" value="Yes, delete">
');

print ('
<script>
$("#delete").click(function (event) {
	if ($("#semi").is(":checked"))
		{ var semi = 1; } else { var semi = 0; }
	var codes = [];
');
foreach ($arCodesVerified as $sCode)
{
	print ("\t" . 'codes.push("' . $sCode . '");' . "\n");
}
print ('
	$.ajax({
		type: "POST",
		url: "/delete/delete.php",
		data: ({
			semi : semi,
			codes : codes,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				window.location.replace("/videos/");
			} else {
				$("#delete-error").html(error);
			}
		},
		error: function() {
			$("#delete-error").html("Error calling delete.php.");
		}
	});
});
</script>
');
	} else {
		print ('No <a href="/videos/">videos</a> match your selection.');
	}
}
/*****************************************************************************/

HTMLStart ('Delete', 'Account', 'Delete', 0, FALSE);
print ('<h1>Delete</h1>');

if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To delete videos, first <a href="/signin/">sign in</a>.');
} else {
	if (strtoupper ($_SERVER['REQUEST_METHOD']) === 'POST')
	{
		$iUserID = intval ($_SESSION['fst']['user_id']);
		AskConfirm ($iUserID);
	} else {
		print ('Check all <a href="/videos/">videos</a> you want to delete,' .
			' and press "Delete checked".');
	}
}

HTMLEnd();
?>
