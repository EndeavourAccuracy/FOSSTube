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
	if ((isset ($_POST['recipient'])) &&
		(isset ($_POST['type'])))
	{
		$sRecipient = $_POST['recipient'];
		$arUser = UserExists ($sRecipient);
		$iType = intval ($_POST['type']);

		if (($arUser !== FALSE) &&
			(isset ($GLOBALS['request_types'][$iType])))
		{
			$iUserRequestor = $_SESSION['fst']['user_id'];
			$iUserRecipient = $arUser['id']; /*** Do NOT move up. ***/

			$query_requests = "SELECT
					COUNT(*) AS requests
				FROM `fst_request`
				WHERE (user_id_requestor='" . $iUserRequestor . "')
				AND (DATE(request_adddate)=CURDATE())";
			$result_requests = Query ($query_requests);
			$row_requests = mysqli_fetch_assoc ($result_requests);
			if ($row_requests['requests'] < 1)
			{
				$query_requests = "SELECT
						COUNT(*) AS requests
					FROM `fst_request`
					WHERE (user_id_requestor='" . $iUserRequestor . "')
					AND (user_id_recipient='" . $iUserRecipient . "')
					AND (request_type='" . $iType . "')
					AND (request_status='2')";
				$result_requests = Query ($query_requests);
				$row_requests = mysqli_fetch_assoc ($result_requests);
				if ($row_requests['requests'] == 0)
				{
					if (FewerNotif (GetUserInfo ($iUserRequestor, 'user_username'),
						GetUserInfo ($iUserRecipient, 'user_username')) === FALSE)
					{
						$sDTNow = date ('Y-m-d H:i:s');

						$query_insert = "INSERT INTO `fst_request` SET
							user_id_requestor='" . $iUserRequestor . "',
							user_id_recipient='" . $iUserRecipient . "',
							request_type='" . $iType . "',
							request_adddate='" . $sDTNow . "',
							request_status='2'";
						Query ($query_insert);

						$_SESSION['fst']['sent'] = 1;
						$arResult['result'] = 1;
						$arResult['error'] = '';
					} else {
						$arResult['result'] = 0;
						$arResult['error'] = 'This user does not accept requests.';
					}
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'This request is already pending.';
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'Allowed once per day.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Invalid request.';
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
