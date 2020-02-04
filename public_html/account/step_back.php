<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.0 (February 2020)
 * Copyright (C) 2020 Norbert de Jonge <mail@norbertdejonge.nl>
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

if (isset ($_SESSION['fst']['step_account']))
{
	$iCurrentStep = intval ($_SESSION['fst']['step_account']);

	switch ($iCurrentStep)
	{
		case 2: $_SESSION['fst']['step_account'] = 1; $bBack = TRUE; break;
		case 3: $_SESSION['fst']['step_account'] = 2; $bBack = TRUE; break;
		default: $bBack = FALSE; break;
	}

	if ($bBack === TRUE)
	{
		$arResult['result'] = 1;
		$arResult['error'] = '';
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Could not go back.';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Unknown step.';
}
print (json_encode ($arResult));
?>
