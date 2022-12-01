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
function Where ($iFields, $sWord, $sLike)
/*****************************************************************************/
{
	$sWhere = "";

	/*** $sOrAnd ***/
	if ($sLike == 'NOT LIKE')
	{
		$sOrAnd = " AND ";
	} else {
		$sOrAnd = " OR ";
	}

	$sWhere .= "(";

	/*** titles ***/
	if (($iFields == 1) || ($iFields == 2) || ($iFields == 3))
	{
		$sWhere .= "(video_title " . $sLike . " '%" . str_replace ('%', '\%',
			mysqli_real_escape_string ($GLOBALS['link'], $sWord)) . "%')";
	}

	/*** tags ***/
	if (($iFields == 2) || ($iFields == 3))
	{
		$sWhere .= $sOrAnd;
		$sWhere .= "(video_tags " . $sLike . " '%" . str_replace ('%', '\%',
			mysqli_real_escape_string ($GLOBALS['link'], $sWord)) . "%')";
	}

	/*** descriptions ***/
	if ($iFields == 3)
	{
		$sWhere .= $sOrAnd;
		$sWhere .= "(video_description " . $sLike . " '%" . str_replace ('%', '\%',
			mysqli_real_escape_string ($GLOBALS['link'], $sWord)) . "%')";
	}

	$sWhere .= ")";

	return ($sWhere);
}
/*****************************************************************************/

if ((isset ($_POST['fields'])) &&
	(isset ($_POST['all'])) &&
	(isset ($_POST['phrase'])) &&
	(isset ($_POST['any'])) &&
	(isset ($_POST['none'])) &&
	(isset ($_POST['subscribed'])) &&
	(isset ($_POST['username'])) &&
	(isset ($_POST['order'])) &&
	(isset ($_POST['offset'])))
{
	$iFields = intval ($_POST['fields']);
	/***/
	$sAll = trim ($_POST['all']);
	$sAll = str_replace (',', ' ', $sAll);
	$sAll = preg_replace ('/ {2,}/', ' ', $sAll);
	if ($sAll == '')
		{ $arAll = FALSE; }
			else { $arAll = explode (' ', $sAll); }
	/***/
	$sPhrase = trim ($_POST['phrase']);
	/***/
	$sAny = trim ($_POST['any']);
	$sAny = str_replace (',', ' ', $sAny);
	$sAny = preg_replace ('/ {2,}/', ' ', $sAny);
	if ($sAny == '')
		{ $arAny = FALSE; }
			else { $arAny = explode (' ', $sAny); }
	/***/
	$sNone = trim ($_POST['none']);
	$sNone = str_replace (',', ' ', $sNone);
	$sNone = preg_replace ('/ {2,}/', ' ', $sNone);
	if ($sNone == '')
		{ $arNone = FALSE; }
			else { $arNone = explode (' ', $sNone); }
	/***/
	$iSubscribed = intval ($_POST['subscribed']);
	/***/
	$sUsername = $_POST['username'];
	if ($sUsername != '')
	{
		$iPubID = GetUserID ($sUsername);
	} else { $iPubID = 0; }
	/***/
	$sOrder = $_POST['order'];
	/***/
	$iOffset = intval ($_POST['offset']);
	/***/
	if (isset ($_SESSION['fst']['user_id']))
	{
		$iUserID = intval ($_SESSION['fst']['user_id']);
	} else { $iUserID = 0; }

	/*** $sError ***/
	$sError = '';
	if (($iFields < 1) || ($iFields > 3))
		{ $sError = 'Invalid fields.'; }
	if (($iUserID == 0) && ($iSubscribed == 1))
	{
		$sError = 'You are trying to use personalized options,' .
			' but are signed out.';
	}
	if ($iPubID === FALSE)
		{ $sError = 'Unknown user "' . Sanitize ($sUsername) . '".'; }

	if ($sError == '')
	{
		/*** $sWhereStart ***/
		$sWhereStart = "WHERE (video_deleted='0') AND ((video_360='1') OR (video_istext='1'))";

		/*** $sWhereText ***/
		$sWhereText = "";
		if ($arAll !== FALSE)
		{
			$sWhereText .= " AND (";
			$key_last = array_key_last ($arAll);
			foreach ($arAll as $key => $sWord)
			{
				$sWhereText .= Where ($iFields, $sWord, 'LIKE');
				if ($key != $key_last) { $sWhereText .= " AND "; }
			}
			$sWhereText .= ")";
		}
		if ($sPhrase != '')
		{
			$sWhereText .= " AND (";
			$sWhereText .= Where ($iFields, $sPhrase, 'LIKE');
			$sWhereText .= ")";
		}
		if ($arAny !== FALSE)
		{
			$sWhereText .= " AND (";
			$key_last = array_key_last ($arAny);
			foreach ($arAny as $key => $sWord)
			{
				$sWhereText .= Where ($iFields, $sWord, 'LIKE');
				if ($key != $key_last) { $sWhereText .= " OR "; }
			}
			$sWhereText .= ")";
		}
		if ($arNone !== FALSE)
		{
			$sWhereText .= " AND (";
			$key_last = array_key_last ($arNone);
			foreach ($arNone as $key => $sWord)
			{
				$sWhereText .= Where ($iFields, $sWord, 'NOT LIKE');
				if ($key != $key_last) { $sWhereText .= " AND "; }
			}
			$sWhereText .= ")";
		}

		/*** $sWhereSub ***/
		$sWhereSub = "";
		if ($iSubscribed == 1)
		{
			$sWhereSub = " AND (fv.user_id IN (";
			$arSub = array();
			array_push ($arSub, 0); /*** Prevent an empty array. ***/
			$query_sub = "SELECT
					user_id_channel
				FROM `fst_subscribe`
				WHERE (user_id_subscriber='" . $iUserID . "')";
			$result_sub = Query ($query_sub);
			while ($row_sub = mysqli_fetch_assoc ($result_sub))
			{
				array_push ($arSub, $row_sub['user_id_channel']);
			}
			$sWhereSub .= implode (', ', $arSub);
			$sWhereSub .= "))";
		}

		/*** $sWherePub ***/
		$sWherePub = "";
		if ($iPubID != 0)
		{
			$sWherePub = " AND (fv.user_id='" . $iPubID . "')";
		}

		/*** $sOrderBy ***/
		switch ($sOrder)
		{
			case 'datedesc': $sOrderBy = "video_adddate DESC, video_id DESC"; break;
			case 'dateasc': $sOrderBy = "video_adddate ASC, video_id ASC"; break;
			case 'viewsdesc': $sOrderBy = "video_views DESC, video_id DESC"; break;
			case 'viewsasc': $sOrderBy = "video_views ASC, video_id ASC"; break;
			case 'likesdesc': $sOrderBy = "video_likes DESC, video_id DESC"; break;
			case 'likesasc': $sOrderBy = "video_likes ASC, video_id ASC"; break;
			case 'commentsdesc':
				$sOrderBy = "video_comments DESC, video_id DESC"; break;
			case 'commentsasc':
				$sOrderBy = "video_comments ASC, video_id ASC"; break;
			case 'secdesc': $sOrderBy = "(video_seconds = 0), video_seconds DESC," .
				" video_id DESC"; break;
			case 'secasc': $sOrderBy = "(video_seconds = 0), video_seconds ASC," .
				" video_id ASC"; break;
			default: $sOrderBy = "video_id DESC"; break; /*** Fallback. ***/
		}

		/*** $sHTML ***/
		$sHTML = '';
		$query_asearch = "SELECT
				fv.video_id,
				fu.user_username,
				fv.video_title,
				fv.video_seconds,
				fv.video_views,
				fv.video_likes,
				fv.video_comments,
				fv.video_adddate,
				fv.video_istext
			FROM `fst_video` fv
			LEFT JOIN `fst_user` fu
				ON fv.user_id = fu.user_id
			" . $sWhereStart . "
			" . $sWhereText . "
			" . $sWhereSub . "
			" . $sWherePub . "
			ORDER BY " . $sOrderBy . "
			LIMIT 10
			OFFSET " . $iOffset;
		$result_asearch = Query ($query_asearch);
		$iRows = mysqli_num_rows ($result_asearch);
		if ($iRows != 0)
		{
			$iRow = 0;
			while ($row_asearch = mysqli_fetch_assoc ($result_asearch))
			{
				$iVideoID = intval ($row_asearch['video_id']);
				$sCode = IDToCode ($iVideoID);
				$sByUser = $row_asearch['user_username'];
				$sTitle = $row_asearch['video_title'];
				$iSecs = intval ($row_asearch['video_seconds']);
				if ($iSecs != 0)
				{
					$sSecs = VideoTime ($iSecs);
				} else { $sSecs = '-'; }
				$iViews = intval ($row_asearch['video_views']);
				$iLikes = intval ($row_asearch['video_likes']);
				$iComments = intval ($row_asearch['video_comments']);
				$sDT = $row_asearch['video_adddate'];
				$sDate = date ('Y-m-d', strtotime ($sDT));
				$iIsText = intval ($row_asearch['video_istext']);
				switch ($iIsText)
				{
					case 0: $sIsText = 'video'; break;
					case 1: $sIsText = 'text'; break;
				}

				if (($iOffset == 0) && ($iRow == 0))
				{
					$sClass = 'asearch-row-first';
				} else {
					$sClass = 'asearch-row-rest';
				}

$sHTML .= '
<span class="' . $sClass . '">
<span class="asearch-span" style="width:100px;">
' . $sIsText . '
</span>
<span class="asearch-span asearch-title" style="width:200px;">
<a target="_blank" href="/v/' . $sCode . '" title="' . Sanitize ($sTitle) . '">' . Sanitize ($sTitle) . '</a>
</span>
<span class="asearch-span" style="width:200px;">
by "' . Sanitize ($sByUser) . '"
</span>
<span class="asearch-span" style="width:100px;">
' . $sDate . '
</span>
<span class="asearch-span" style="width:100px;">
' . number_format ($iViews) . ' views
</span>
<span class="asearch-span" style="width:100px;">
' . $iLikes . ' likes
</span>
<span class="asearch-span" style="width:100px;">
' . $iComments . ' comments
</span>
<span class="asearch-span" style="width:100px;">
' . $sSecs . '
</span>
</span>
';
				$iRow++;
			}
		} else {
$sHTML .= '
<span style="display:block; margin-top:10px; font-size:18px; font-style:italic;">
No results.
</span>
';
		}

		$arResult['result'] = 1;
		$arResult['error'] = '';
		$arResult['html'] = $sHTML;
		$arResult['rows'] = $iRows;
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = $sError;
		$arResult['html'] = '';
		$arResult['rows'] = 0;
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Data is missing.';
	$arResult['html'] = '';
	$arResult['rows'] = 0;
}
print (json_encode ($arResult));
?>
