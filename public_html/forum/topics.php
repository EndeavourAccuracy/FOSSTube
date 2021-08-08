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
function ShowTopics ($iBoard)
/*****************************************************************************/
{
	/*** $iUserID ***/
	if (isset ($_SESSION['fst']['user_id']))
	{
		$iUserID = intval ($_SESSION['fst']['user_id']);
	} else { $iUserID = 0; }

	$arTopics = Topics ($iBoard, $iUserID, 0);

	if ($arTopics !== FALSE)
	{
$sHTML = '
<div id="topics-table" class="container-fluid">

<div id="topics-header" class="row">
<div class="col-sm-3">Topic</div>
<div class="col-sm-3">Created (UTC)</div>
<div class="col-sm-3">#R / #V</div>
<div class="col-sm-3">Last reply (UTC)</div>
</div>
';

		foreach ($arTopics as $iKey => $arTopic)
		{
$sHTML .= '
<div class="row';

			if ($arTopic['new_content'] === TRUE) { $sHTML .= ' new'; }

$sHTML .= '">

<div class="col-sm-3">
';

			if ($arTopic['video_comments_allow'] == 0)
				{ $sHTML .= '&#x1F512; '; }

$sHTML .= '
<a href="/v/' . $arTopic['code'] . '">' . Sanitize ($arTopic['video_title']) . '</a>
</div>

<div class="col-sm-3">
' . ForumDate ($arTopic['created_date']) . ' by <a href="/user/' . $arTopic['created_username'] . '">' . $arTopic['created_username'] . '</a>
</div>

<div class="col-sm-3">
' . $arTopic['nr_replies'] . ' / ' . $arTopic['video_views'] . '
</div>

<div class="col-sm-3">
' . $arTopic['last_reply'] . '
</div>

</div>
';
		}
		$sHTML .= '</div>';
	} else {
		$sHTML = '<span style="display:block; margin-top:10px;' .
			' font-style:italic;">No topics.</span>';
	}

	return ($sHTML);
}
/*****************************************************************************/

if ((isset ($_POST['board'])) &&
	(isset ($_POST['offset'])))
{
	$iBoard = intval ($_POST['board']);
	/*** The offset is unused, for now. ***/

	$arResult['result'] = 1;
	$arResult['error'] = '';
	$arResult['html'] = ShowTopics ($iBoard);
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Data is missing.';
	$arResult['html'] = '';
}
print (json_encode ($arResult));
?>
