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

include_once (dirname (__FILE__) . '/../fst_base.php');

/*****************************************************************************/

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if ((isset ($_POST['action'])) &&
		(isset ($_POST['request_id'])))
	{
		$cAction = $_POST['action'];
		$iRequestID = intval ($_POST['request_id']);
		$iUserRecipient = $_SESSION['fst']['user_id'];

		if (($cAction == 'a') || ($cAction == 'd'))
		{
			if ($cAction == 'a')
			{
				$iNewStatus = 1;
			} else { /*** 'd' ***/
				$iNewStatus = 0;
			}

			$query_update = "UPDATE `fst_request` SET
					request_status='" . $iNewStatus . "'
				WHERE (request_id='" . $iRequestID . "')
				AND (request_status='2')
				AND (user_id_recipient='" . $iUserRecipient . "')";
			$result_update = Query ($query_update);
			if (mysqli_affected_rows ($GLOBALS['link']) == 1)
			{
				if ($cAction == 'a')
				{
					$query_send = "SELECT
							user_id_requestor,
							request_type
						FROM `fst_request`
						WHERE (request_id='" . $iRequestID . "')";
					$result_send = Query ($query_send);
					$row_send = mysqli_fetch_assoc ($result_send);
					$iUserRequestor = intval ($row_send['user_id_requestor']);
					$iType = intval ($row_send['request_type']);
					/***/
					$sType = $GLOBALS['request_types'][$iType];
					$sUserRecipient = GetUserInfo ($iUserRecipient, 'user_username');
					$sInfo = GetRequestedInfo ($iUserRecipient, $iType);

					$sText = 'User "' . $sUserRecipient . '" has approved your information request. Their ' . $sType . ' is "' . Sanitize ($sInfo) . '".';
					CreateMessage (-1, $iUserRequestor, $sText);
				}

				$arResult['result'] = 1;
				$arResult['error'] = '';
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'Nothing changed.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Invalid action.';
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Data is missing.';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
}
print (json_encode ($arResult));
?>
