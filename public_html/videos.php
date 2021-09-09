<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.3 (September 2021)
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
function EnterLeave ()
/*****************************************************************************/
{
$sReturn = '
<script>
$("[data-name=\"hover\"]").mouseenter(function(){
	var active = $(this).data("active");
	var preview = $(this).data("preview");
	if ((active == "thumb") && (preview != ""))
	{
		var title = $(this).data("title");
		title = title.replace(/\"/g, "&quot;");
		style = $(this).find("img").attr("style");
		if (style == "undefined")
			{ style = ""; }
				else { style = " style=\"" + style + "\""; }
		var video = "<video src=\"" + preview + "\" alt=\"" +
			title + "\" class=\"thumb-or-preview\"" + style + " autoplay loop>";
		$(this).html(video);
		$(this).data("active","video");
	}
});
$("[data-name=\"hover\"]").mouseleave(function(){
	var active = $(this).data("active");
	if (active == "video")
	{
		var thumb = $(this).data("thumb");
		var title = $(this).data("title");
		title = title.replace(/\"/g, "&quot;");
		style = $(this).find("video").attr("style");
		if (style == "undefined")
			{ style = ""; }
				else { style = " style=\"" + style + "\""; }
		var image = "<img src=\"" + thumb + "\" alt=\"" +
			title + "\" class=\"thumb-or-preview\"" + style + ">";
		$(this).html(image);
		$(this).data("active","thumb");
	}
});
</script>
';

	return ($sReturn);
}
/*****************************************************************************/

if ((isset ($_POST['section'])) &&
	(isset ($_POST['order'])) &&
	(isset ($_POST['offset'])) &&
	(isset ($_POST['user'])) &&
	(isset ($_POST['searcht'])) &&
	(isset ($_POST['searcha'])) &&
	(isset ($_POST['filters'])))
{
	/*** The section value is used in a switch(). ***/
	$sOrder = $_POST['order'];
	$arValidOrder = array (
		'datedesc',
		'dateasc',
		'viewsdesc',
		'viewsasc',
		'likesdesc',
		'likesasc',
		'commentsdesc',
		'commentsasc',
		'secdesc',
		'secasc',
		'itemdesc'
	);
	if (in_array ($sOrder, $arValidOrder) === FALSE)
		{ $sOrder = 'datedesc'; } /*** Fallback. ***/
	$iOffset = intval ($_POST['offset']);
	$iUserID = 0;
	if ($_POST['user'] != '')
	{
		$arUser = UserExists ($_POST['user']);
		if ($arUser !== FALSE)
		{
			$iUserID = $arUser['id'];
			$sUsername = $arUser['username'];
		}
	}
	$sSearchT = $_POST['searcht'];
	$sSearchA = $_POST['searcha'];

	/*** $_POST['filters'] -> $iThreshold, $iNSFW, $iFolderID ***/
	$iThreshold = $GLOBALS['default_threshold'];
	$iNSFW = $GLOBALS['default_nsfw'];
	$iFolderID = 0;
	if (is_array ($_POST['filters']))
	{
		foreach ($_POST['filters'] as $key => $value)
		{
			if ($key == 'threshold') { $iThreshold = intval ($value); }
			if ($key == 'nsfw') { $iNSFW = intval ($value); }
			if ($key == 'folder') { $iFolderID = intval ($value); }
		}
	}
	if (in_array ($iThreshold, $GLOBALS['allowed_thresholds']) === FALSE)
		{ $iThreshold = $GLOBALS['default_threshold']; }
	if (in_array ($iNSFW, $GLOBALS['allowed_nsfws']) === FALSE)
		{ $iNSFW = $GLOBALS['default_nsfw']; }
	$arFilters = array (
		'threshold' => $iThreshold,
		'nsfw' => $iNSFW,
		'folder' => $iFolderID
	);

	switch ($_POST['section'])
	{
		case 'index':
			$arData = Videos (
				'videos',
				'index',
				"",
				$sOrder,
				$GLOBALS['items_per_page'],
				$iOffset,
				'',
				'',
				'',
				0,
				TRUE,
				$arFilters
			);
			$sHTML = $arData['html'];
			$sHTML .= EnterLeave();
			break;
		case 'user':
			if ($iUserID != 0)
			{
				$arData = Videos (
					'videos',
					'user',
					"AND (fv.user_id='" . $iUserID . "')",
					$sOrder,
					$GLOBALS['items_per_page'],
					$iOffset,
					$sUsername,
					'',
					'',
					0,
					TRUE,
					$arFilters
				);
				$sHTML = $arData['html'];
				$sHTML .= EnterLeave();
			} else {
				$sHTML = 'Unknown user "' .
					Sanitize ($_POST['user']) . '".';
			}
			break;
		case 'search':
			if ($sSearchT != '')
			{
				$sDivID = 'result_title';
				$arQuery = explode (' ', $sSearchT);
				$sWhere = "";
				foreach ($arQuery as $sBit)
				{
					$sWhere .= " AND (video_title LIKE '%" . str_replace ('%', '\%',
						mysqli_real_escape_string ($GLOBALS['link'], $sBit)) . "%')";
				}
			} else {
				$sDivID = 'result_any';
				$arQuery = explode (' ', $sSearchA);
				$sWhere = "";
				foreach ($arQuery as $sBit)
				{
					$sWhere .= " AND ((video_title LIKE '%" . str_replace ('%', '\%',
						mysqli_real_escape_string ($GLOBALS['link'], $sBit)) . "%') OR
						(video_description LIKE '%" . str_replace ('%', '\%',
						mysqli_real_escape_string ($GLOBALS['link'], $sBit)) . "%') OR
						(video_tags LIKE '%" . str_replace ('%', '\%',
						mysqli_real_escape_string ($GLOBALS['link'], $sBit)) . "%'))";
				}

				/*** Exclude title-only hits. ***/
				$sWhere .= " AND ((1<>1)";
				foreach ($arQuery as $sBit)
				{
					$sWhere .= " OR (video_title NOT LIKE '%" . str_replace ('%', '\%',
						mysqli_real_escape_string ($GLOBALS['link'], $sBit)) . "%')";
				}
				$sWhere .= ")";
			}

			$arData = Videos (
				$sDivID,
				'search',
				$sWhere,
				$sOrder,
				$GLOBALS['items_per_page'],
				$iOffset,
				'',
				$sSearchT,
				$sSearchA,
				0,
				TRUE,
				$arFilters
			);
			$sHTML = $arData['html'];
			$sHTML .= EnterLeave();
			break;
		case 'folder':
			$arData = Videos (
				'items',
				'folder',
				"",
				$sOrder,
				$GLOBALS['items_per_page'],
				$iOffset,
				'',
				'',
				'',
				0,
				TRUE,
				$arFilters
			);
			$sHTML = $arData['html'];
			$sHTML .= EnterLeave();
			break;
		case 'editf':
			$arData = Videos (
				'items',
				'editf',
				"",
				$sOrder,
				$GLOBALS['items_per_page'],
				$iOffset,
				'',
				'',
				'',
				2,
				TRUE,
				$arFilters
			);
			$sHTML = $arData['html'];
			$sHTML .= EnterLeave();
			break;
		default:
			$sHTML = 'Unknown section "' .
				Sanitize ($_POST['section']) . '".';
			break;
	}

	$arResult['result'] = 1;
	$arResult['error'] = '';
	$arResult['html'] = $sHTML;
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Missing data.';
	$arResult['html'] = '';
}
print (json_encode ($arResult));
?>
