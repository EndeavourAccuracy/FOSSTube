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

include_once (dirname (__FILE__) . '/fst_base.php');

/*****************************************************************************/
function ThresholdOptions ()
/*****************************************************************************/
{
	$sReturn = '';

	foreach ($GLOBALS['allowed_thresholds'] as $iThreshold)
	{
		$sReturn .= '<option value="' . $iThreshold . '"';
		if ($iThreshold == $GLOBALS['default_threshold'])
			{ $sReturn .= ' selected'; }
		$sReturn .= '>' . $iThreshold . '+</option>' . "\n";
	}

	return ($sReturn);
}
/*****************************************************************************/
function NSFWOptions ()
/*****************************************************************************/
{
	$sReturn = '';

	$sReturn .= '<option value="3"';
	if (Pref ('user_pref_nsfw') == 1) { $sReturn .= ' selected'; }
	$sReturn .= '>any</option>' . "\n";

	$sReturn .= '<option value="0"';
	if (Pref ('user_pref_nsfw') == 0) { $sReturn .= ' selected'; }
	$sReturn .= '>SFW</option>' . "\n";

	$sReturn .= '<option value="1">NSFW</option>' . "\n";

	/*** $sReturn .= '<option value="2">unknown</option>' . "\n"; ***/

	return ($sReturn);
}
/*****************************************************************************/
function Main ()
/*****************************************************************************/
{
	if ($GLOBALS['maintenance'] === TRUE)
	{
print ('
<div style="text-align:center; margin:10px 0; font-size:16px;">
The website is currently in maintenance mode.
<br>
Users can not login. Please check back later.
</div>
');
	}

	Search ('');

	/*** $sNSFW, $iNSFW ***/
	if (isset ($_SESSION['fst']['user_id']))
	{
		$sNSFW = '<select id="nsfw">' . NSFWOptions() . '</select>';
		if (Pref ('user_pref_nsfw') == 1) { $iNSFW = 3; } else { $iNSFW = 0; }
	} else {
		$sNSFW = '<a href="/signin/">';
		switch ($GLOBALS['default_nsfw'])
		{
			case 0: $sNSFW .= 'safe for work'; break;
			case 1: $sNSFW .= 'NOT safe for work'; break;
			case 2: $sNSFW .= 'unknown SFW status'; break;
			case 3: $sNSFW .= 'any SFW status'; break;
		}
		$sNSFW .= '</a>';
		$iNSFW = $GLOBALS['default_nsfw'];
	}

print ('
<span style="display:block; margin-bottom:10px;">
New with
<select id="threshold">
');

	print (ThresholdOptions());

print ('</select>
views and ' . $sNSFW . ':
</span>
<div id="videos"><img src="/images/loading.gif" alt="loading"></div>
<script>
var filters = {};
filters["threshold"] = ' . $GLOBALS['default_threshold'] . ';
filters["nsfw"] = ' . $iNSFW . ';
$(document).ready(function(){ VideosJS ("videos", "index", "datedesc", 0, "", "", "", filters); });

function LoadVideos () {
	$("#videos").html("<img src=\"/images/loading.gif\" alt=\"loading\">");
	var filters = {};
	filters["threshold"] = $("#threshold option:selected").val();
	filters["nsfw"] = $("#nsfw option:selected").val();
	VideosJS ("videos", "index", "datedesc", 0, "", "", "", filters);
}
$("#threshold").change(function(){ LoadVideos(); });
$("#nsfw").change(function(){ LoadVideos(); });
</script>
');
}
/*****************************************************************************/
function Result ()
/*****************************************************************************/
{
	$sQuery = trim ($_GET['search_query']);

	Search (substr ($sQuery, 0, 100));

	/*** Query length validation. ***/
	if ((strlen ($sQuery) < 2) || (strlen ($sQuery) > 100))
	{
		print ('Query must be 2-100 characters.');
		return;
	}

	$iMatchesTitle = MatchesTitle ($sQuery, 3);
	$iMatchesTitleSFW = MatchesTitle ($sQuery, 0);
	$iMatchesAny = MatchesAny ($sQuery, 3);
	$iMatchesAnySFW = MatchesAny ($sQuery, 0);
	$iMatchesOther = $iMatchesAny - $iMatchesTitle;

	$sDTNow = date ('Y-m-d H:i:s');
	$query_search = "INSERT INTO `fst_search` SET
		search_text='" . mysqli_real_escape_string
			($GLOBALS['link'], $sQuery) . "',
		search_matchest='" . $iMatchesTitle . "',
		search_matcheso='" . $iMatchesOther . "',
		search_adddate='" . $sDTNow . "'";
	Query ($query_search);

	if (strtolower (substr ($sQuery, -1)) == 's')
	{
print ('
<span style="display:block; margin-bottom:10px; color:#00f;">
Tip: removing the -s suffix may produce more results.
<br>
Example: "cat" (singular) instead of "cats" (plural).
</span>
');
	}

	if (($iMatchesTitle != $iMatchesTitleSFW) ||
		($iMatchesAny != $iMatchesAnySFW))
	{
		/*** Search result has non-SFW video(s). ***/
		if (Pref ('user_pref_nsfw') == 1)
		{
			$iNSFW = 3;
		} else {
			$iNSFW = 0;
			print ('<div id="nsfw-div">Showing only SFW content.<br><a href="/preferences/">Preferences</a></div>');
		}
	} else {
		/*** Search result has NO non-SFW video(s). ***/
		$iNSFW = 3;
	}

print ('
<span style="display:block; margin-bottom:10px;">
Title matches (' . $iMatchesTitle . ') for "' . Sanitize ($sQuery) . '":
</span>
<div id="result_title"><img src="/images/loading.gif" alt="loading"></div>
<script>$(document).ready(function(){
	var filters = {};
	filters["threshold"] = 0;
	filters["nsfw"] = ' . $iNSFW . ';
	VideosJS ("result_title", "search", "viewsdesc", 0, "", "' . Sanitize ($sQuery) . '", "", filters);
});</script>
');

print ('
<span style="display:block; margin:10px 0;">
Other results (' . $iMatchesOther . ') for "' . Sanitize ($sQuery) . '":
</span>
<div id="result_any"><img src="/images/loading.gif" alt="loading"></div>
<script>$(document).ready(function(){
	var filters = {};
	filters["threshold"] = 0;
	filters["nsfw"] = ' . $iNSFW . ';
	VideosJS ("result_any", "search", "viewsdesc", 0, "", "", "' . Sanitize ($sQuery) . '", filters);
});</script>
');

	/*** users ***/
	$query_user = "SELECT
			fu.user_username,
			IF(SUM(fv.video_views) IS NULL,0,SUM(fv.video_views)) AS views
		FROM `fst_user` fu
		LEFT JOIN `fst_video` fv
			ON fu.user_id = fv.user_id
		WHERE (fu.user_username LIKE '%" . str_replace ('%', '\%',
			mysqli_real_escape_string ($GLOBALS['link'], $sQuery)) . "%')
		AND (fu.user_deleted='0')
		GROUP BY fu.user_username
		ORDER BY views DESC
		LIMIT 25";
	$result_user = Query ($query_user);
	if (mysqli_num_rows ($result_user) != 0)
	{
print ('
<span style="display:block; margin:10px 0;">
User matches for "' . Sanitize ($sQuery) . '":
</span>
');

		print ('<span style="display:block; margin-bottom:10px;">');
		$bUserFirst = TRUE;
		while ($row_user = mysqli_fetch_assoc ($result_user))
		{
			$sUsername = $row_user['user_username'];

			/*** Do not show admins and mods. ***/
			if ((in_array ($sUsername, $GLOBALS['admins']) === FALSE) &&
				(in_array ($sUsername, $GLOBALS['mods']) === FALSE))
			{
				if ($bUserFirst === FALSE) { print (' / '); }
				print ('<a href="/user/' . $sUsername . '">' . $sUsername . '</a>');
				$bUserFirst = FALSE;
			}
		}
		print ('</span>');
	}
}
/*****************************************************************************/

IncreaseViews (0);
HTMLStart ('Home', 'Home', 'Home', 0, FALSE);

if ((strtoupper ($_SERVER['REQUEST_METHOD']) === 'GET') &&
	(isset ($_GET['search_query'])))
{
	Result();
} else {
	Main();
}

HTMLEnd();
?>
