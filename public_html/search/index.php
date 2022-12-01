<?php
/* SPDX-License-Identifier: Zlib */
/* FOSSTube v1.6 (December 2022)
 * Copyright (C) 2020-2022 Norbert de Jonge <nlmdejonge@gmail.com>
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

HTMLStart ('Advanced search', 'Advanced search', 'Advanced search', 0, FALSE);
print ('<h1>Advanced search</h1>');

print ('<p>To find microblog posts, <a href="/explore/">explore</a>.</p>');

/*** $iUserID ***/
if (isset ($_SESSION['fst']['user_id']))
{
	$iUserID = intval ($_SESSION['fst']['user_id']);
} else {
	$iUserID = 0;
	print ('<p><a href="/signin/">Sign in</a> for personalized search options.</p>');
}

print ('<hr class="fst-hr">');

print ('<h2 style="margin-top:10px;">Words</h2>');

print('
<span style="display:block; margin-top:10px;">
<label for="fields" class="lbl">Search in:</label>
<select id="fields">
<option value="1">Titles only</option>
<option value="2" selected>Titles and tags</option>
<option value="3">Titles, tags, descriptions</option>
</select>
</span>
');

print ('
<span style="display:block; margin-top:10px;">
<label for="all" class="lbl">All of these words:</label>
<input type="text" id="all" maxlength="100" style="width:600px; max-width:100%;">
</span>

<span style="display:block; margin-top:10px;">
<label for="phrase" class="lbl">This exact phrase:</label>
<input type="text" id="phrase" maxlength="100" style="width:600px; max-width:100%;">
</span>

<span style="display:block; margin-top:10px;">
<label for="any" class="lbl">Any of these words:</label>
<input type="text" id="any" maxlength="100" style="width:600px; max-width:100%;">
</span>

<span style="display:block; margin-top:10px;">
<label for="none" class="lbl">None of these words:</label>
<input type="text" id="none" maxlength="100" style="width:600px; max-width:100%;">
</span>
');

print ('<hr class="fst-hr">');

print ('<h2 style="margin-top:10px;">Users</h2>');

if ($iUserID != 0)
{
print ('
<span style="display:block; margin-top:10px;">
<label for="subscribed" class="lbl">Only users I am subscribed to:</label>
<input type="checkbox" id="subscribed"> Yes
</span>
');
}

/*** $sUser ***/
if (isset ($_GET['user']))
{
	$sUser = $_GET['user'];
} else { $sUser = ''; }

print ('
<span style="display:block; margin-top:10px;">
<label for="username" class="lbl">Only by user:</label>
<input type="text" id="username" maxlength="100" style="width:600px; max-width:100%;" value="' . Sanitize ($sUser) . '">
</span>
');

print ('<hr class="fst-hr">');

print ('<h2 style="margin-top:10px;">Order</h2>');

print ('
<span style="display:block; margin:10px 0;">
<label for="order" class="lbl">Order by:</label>
<select id="order">
<option value="datedesc">newest</option>
<option value="dateasc">oldest</option>
<option value="viewsdesc">most views</option>
<option value="viewsasc">least views</option>
<option value="likesdesc">most likes</option>
<option value="likesasc">least likes</option>
<option value="commentsdesc">most comments</option>
<option value="commentsasc">least comments</option>
<option value="secdesc">longest</option>
<option value="secasc">shortest</option>
</select>
</span>
');

print ('<hr class="fst-hr">');

print ('
<input type="hidden" id="offset" value="0">
<div id="asearch-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="asearch" value="Search" style="margin-top:10px;">
<div id="asearch-result" style="margin-top:20px;"></div>
<div id="asearch-more" style="margin-top:10px; text-align:center; display:none;"><span class="more-span">load more</span></div>
');

print ('
<script>
function ASearch () {
	var fields = $("#fields").val();
	var all = $("#all").val();
	var phrase = $("#phrase").val();
	var any = $("#any").val();
	var none = $("#none").val();
	var subscribed_bool = $("#subscribed").is(":checked");
	if (subscribed_bool == false)
		{ var subscribed = 0; } else { var subscribed = 1; }
	var username = $("#username").val();
	var order = $("#order").val();
	var offset = $("#offset").val();

	$.ajax({
		type: "POST",
		url: "/search/asearch.php",
		data: ({
			fields : fields,
			all : all,
			phrase : phrase,
			any : any,
			none : none,
			subscribed : subscribed,
			username : username,
			order : order,
			offset : offset
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			var html = data["html"];
			var rows = data["rows"];
			if (result == 1)
			{
				$("#asearch-result").append(html);
				if (rows == 0)
				{
					$("#asearch-more").hide();
				} else {
					$("#asearch-more").show();
				}
			} else {
				$("#asearch-error").html(error);
			}
		},
		error: function() {
			$("#asearch-error").html("Error calling asearch.php.");
		}
	});
}

$("#asearch").click(function(){
	$("#offset").val(0);
	$("#asearch-result").html("");
	$("#asearch-more").hide();
	ASearch();
});

$("#asearch-more").click(function(){
	var offset = $("#offset").val();
	offset = parseInt(offset) + 10;
	$("#offset").val(offset);
	ASearch();
});
</script>
');

HTMLEnd();
?>
