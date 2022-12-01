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
function Where ($sWord)
/*****************************************************************************/
{
	$sWhere = "";

	$sWhere .= "(";

	$sWhere .= "(mbpost_text LIKE '%" . str_replace ('%', '\%',
		mysqli_real_escape_string ($GLOBALS['link'], $sWord)) . "%')";

	$sWhere .= ")";

	return ($sWhere);
}
/*****************************************************************************/

if ((isset ($_POST['phrase'])) &&
	(isset ($_POST['username'])) &&
	(isset ($_POST['order'])) &&
	(isset ($_POST['offset'])))
{
	$sPhrase = trim ($_POST['phrase']);
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
	if ($iPubID === FALSE)
		{ $sError = 'Unknown user "' . Sanitize ($sUsername) . '".'; }

	if ($sError == '')
	{
		/*** $sWhereStart ***/
		$sWhereStart = "WHERE (mbpost_hidden='0')";

		/*** $sWhereText ***/
		$sWhereText = "";
		if ($sPhrase != '')
		{
			$sWhereText .= " AND (";
			$sWhereText .= Where ($sPhrase);
			$sWhereText .= ")";
		}

		/*** $sWherePub ***/
		$sWherePub = "";
		if ($iPubID != 0)
		{
			$sWherePub = " AND (user_id='" . $iPubID . "')";
		}

		/*** $sOrderBy ***/
		switch ($sOrder)
		{
			case 'datedesc': $sOrderBy = "mbpost_dt DESC, mbpost_id DESC"; break;
			case 'dateasc': $sOrderBy = "mbpost_dt ASC, mbpost_id ASC"; break;
			case 'reblogsdesc': $sOrderBy = "mbpost_reblogs DESC, mbpost_id DESC"; break;
			case 'reblogsasc': $sOrderBy = "mbpost_reblogs ASC, mbpost_id ASC"; break;
			case 'likesdesc': $sOrderBy = "mbpost_likes DESC, mbpost_id DESC"; break;
			case 'likesasc': $sOrderBy = "mbpost_likes ASC, mbpost_id ASC"; break;
			default: $sOrderBy = "mbpost_id DESC"; break; /*** Fallback. ***/
		}

		/*** $sHTML ***/
		$sHTML = '';
		$query_explore = "SELECT
				mbpost_id
			FROM `fst_microblog_post`
			" . $sWhereStart . "
			" . $sWhereText . "
			" . $sWherePub . "
			ORDER BY " . $sOrderBy . "
			LIMIT 10
			OFFSET " . $iOffset;
		$result_explore = Query ($query_explore);
		$iRows = mysqli_num_rows ($result_explore);
		if ($iRows != 0)
		{
			$iRow = 0;
			while ($row_explore = mysqli_fetch_assoc ($result_explore))
			{
				$iPostID = intval ($row_explore['mbpost_id']);
				$sHTML .= GetMBPost ($iPostID, $iUserID, FALSE, 2);

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
