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

HTMLStart ('Trending', 'Trending', 'Trending', 0, FALSE);
print ('<h1>Trending</h1>');

$iCurYear = intval (date ('Y'));
$sCurDate = date ('Y-m-d');
$iActYear = $iCurYear;
$sActDate = $sCurDate;

print ('
<p>
Trending content has notably increased views, likes, commenters, or referrers.
<br>
The number of entries varies by day, but never exceeds 40.
<br>
The "Ch." column shows the position changes since yesterday.
</p>
');

print ('<select id="trending-year" style="margin:0 10px 10px 0;">' . "\n");
print ('<option value="' . $iActYear . '" selected>' .
	$iActYear . '</option>' . "\n");

$query_years = "SELECT
		DISTINCT(YEAR(trending_date)) AS year
	FROM `fst_trending`
	WHERE (YEAR(trending_date)<>'" . $iActYear . "')
	ORDER BY year DESC";
$result_years = Query ($query_years);
while ($row_years = mysqli_fetch_assoc ($result_years))
{
	$iYearOption = intval ($row_years['year']);
	print ('<option value="' . $iYearOption . '">' .
		$iYearOption . '</option>' . "\n");
}

print ('</select>');

print ('<select id="trending-date" style="margin-bottom:10px;">' . "\n");
print (GetTrendingDates ($sCurDate, $sActDate));
print ('</select>');

print ('
<div id="trending-error" style="color:#f00;"></div>
<div id="trending">
<img src="/images/loading.gif" alt="loading">
</div>

<script>
$(document).ready(function() {
	Trending ("' . $sActDate . '");
});

$("#trending-year").change(function(){
	var actyear = $("#trending-year option:selected").val();
	TrendingDates ("' . $sCurDate . '", actyear);
});

$("#trending-date").change(function(){
	$("#trending").html("<img src=\"/images/loading.gif\" alt=\"loading\">");
	var actdate = $("#trending-date option:selected").val();
	Trending (actdate);
});
</script>
');

HTMLEnd();
?>
