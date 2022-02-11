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

if ((isset ($_POST['curdate'])) &&
	(isset ($_POST['actyear'])))
{
	$sCurDate = $_POST['curdate'];
	$iActYear = intval ($_POST['actyear']);

	/*** $sActDate ***/
	$query_actdate = "SELECT
			trending_date
		FROM `fst_trending`
		WHERE (YEAR(trending_date)='" . $iActYear . "')
		ORDER BY trending_date DESC
		LIMIT 1";
	$result_actdate = Query ($query_actdate);
	$row_actdate = mysqli_fetch_assoc ($result_actdate);
	$sActDate = $row_actdate['trending_date'];

	$arResult['result'] = 1;
	$arResult['error'] = '';
	$arResult['html'] = GetTrendingDates ($sCurDate, $sActDate);
	$arResult['actdate'] = $sActDate;
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Data is missing.';
	$arResult['html'] = '';
	$arResult['actdate'] = '';
}
print (json_encode ($arResult));
?>
