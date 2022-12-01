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
function ShowTimeline ()
/*****************************************************************************/
{
print ('
<input type="hidden" id="mboffset" value="0">
<div id="microblogposts-error" style="color:#f00; margin-top:10px;"></div>
<div id="microblogposts"><img src="/images/loading.gif" alt="loading"></div>
<div id="microblogposts-more" style="margin-top:10px; text-align:center; display:none;"><span class="more-span">load more</span></div>

<script>
$(document).ready(function(){
	$("#mboffset").val(0);
	MicroBlogPosts (0, 0);
});

$("#microblogposts-more").click(function(){
	var mboffset = $("#mboffset").val();
	mboffset = parseInt(mboffset) + 10;
	$("#mboffset").val(mboffset);
	MicroBlogPosts (0, mboffset);
});
</script>
');
}
/*****************************************************************************/

HTMLStart ('Timeline', 'Account', 'Timeline', 0, FALSE);
print ('<h1>Timeline</h1>');
if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To browse your timeline, first <a href="/signin/">sign in</a>.');
} else {
	print (MicroBlogIcons (1));
	ShowTimeline();
}
HTMLEnd();
?>
