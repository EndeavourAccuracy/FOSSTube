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

HTMLStart ('Explore', 'Explore', 'Explore', 0, FALSE);
print ('<h1>Explore</h1>');

print ('<p>To find videos/texts, use <a href="/search/">advanced search</a>.</p>');

print (MicroBlogIcons (3));

print ('<hr class="fst-hr">');

print ('<h2 style="margin-top:10px;">Words</h2>');

/*** $sPhrase ***/
if (isset ($_GET['phrase']))
{
	$sPhrase = $_GET['phrase'];
} else { $sPhrase = ''; }

print ('
<span style="display:block; margin-top:10px;">
<label for="phrase" class="lbl">This exact phrase:</label>
<input type="text" id="phrase" maxlength="100" style="width:600px; max-width:100%;" value="' . Sanitize ($sPhrase) . '">
</span>
');

print ('<hr class="fst-hr">');

print ('<h2 style="margin-top:10px;">Users</h2>');

print ('
<span style="display:block; margin-top:10px;">
<label for="username" class="lbl">Only by user:</label>
<input type="text" id="username" maxlength="100" style="width:600px; max-width:100%;">
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
<option value="reblogsdesc">most reblogs</option>
<option value="reblogsasc">least reblogs</option>
<option value="likesdesc">most likes</option>
<option value="likesasc">least likes</option>
</select>
</span>
');

print ('<hr class="fst-hr">');

print ('
<input type="hidden" id="offset" value="0">
<div id="explore-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="explore" value="Explore" style="margin-top:10px;">
<div id="explore-result" style="margin-top:20px;"></div>
<div id="explore-more" style="margin-top:10px; text-align:center; display:none;"><span class="more-span">load more</span></div>
');

print ('
<script>
function Explore () {
	var phrase = $("#phrase").val();
	var username = $("#username").val();
	var order = $("#order").val();
	var offset = $("#offset").val();

	$.ajax({
		type: "POST",
		url: "/explore/explore.php",
		data: ({
			phrase : phrase,
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
				$("#explore-result").append(html);
				if (rows == 0)
				{
					$("#explore-more").hide();
				} else {
					$("#explore-more").show();
				}
			} else {
				$("#explore-error").html(error);
			}
		},
		error: function() {
			$("#explore-error").html("Error calling explore.php.");
		}
	});
}

$("#explore").click(function(){
	$("#offset").val(0);
	$("#explore-result").html("");
	$("#explore-more").hide();
	Explore();
});

$("#explore-more").click(function(){
	var offset = $("#offset").val();
	offset = parseInt(offset) + 10;
	$("#offset").val(offset);
	Explore();
});
</script>
');

print (PostJavaScript());

HTMLEnd();
?>
