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

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if (isset ($_POST['user_id_channel']))
	{
		$iUserIDChannel = intval ($_POST['user_id_channel']);

		if (isset ($_SESSION['fst']['user_id']))
		{
			$iUserIDSubscriber = intval ($_SESSION['fst']['user_id']);

			$query_subscribed = "SELECT
					subscribe_id
				FROM `fst_subscribe`
				WHERE (user_id_channel='" . $iUserIDChannel . "')
				AND (user_id_subscriber='" . $iUserIDSubscriber . "')";
			$result_subscribed = Query ($query_subscribed);
			if (mysqli_num_rows ($result_subscribed) == 1)
			{
				/*** UNSUBSCRIBE ***/
				$row_subscribed = mysqli_fetch_assoc ($result_subscribed);
				$iSubscribeID = intval ($row_subscribed['subscribe_id']);
				$query_unsubscribe = "DELETE FROM `fst_subscribe`
					WHERE (subscribe_id='" . $iSubscribeID . "')
					AND (user_id_subscriber='" . $iUserIDSubscriber . "')";
				$result_unsubscribe = Query ($query_unsubscribe);
				if (mysqli_affected_rows ($GLOBALS['link']) == 1)
				{
					$arResult['result'] = 1;
					$arResult['error'] = '';
					$arResult['html'] = '/images/subscribe.png';
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Nothing changed.';
					$arResult['html'] = '';
				}
			} else {
				/*** SUBSCRIBE ***/
				$sDTNow = date ('Y-m-d H:i:s');

				$query_subscribe = "INSERT INTO `fst_subscribe` SET
						user_id_channel='" . $iUserIDChannel . "',
						user_id_subscriber='" . $iUserIDSubscriber . "',
						subscribe_adddate='" . $sDTNow . "'";
				$result_subscribe = Query ($query_subscribe);
				if (mysqli_affected_rows ($GLOBALS['link']) == 1)
				{
					$arResult['result'] = 1;
					$arResult['error'] = '';
					$arResult['html'] = '/images/subscribed.png';
				} else {
					$arResult['result'] = 0;
					$arResult['error'] = 'Nothing changed.';
					$arResult['html'] = '';
				}
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Sign in to (un)subscribe.';
			$arResult['html'] = '';
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Data is missing.';
		$arResult['html'] = '';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Incorrect token. Restart your browser, and retry.';
	$arResult['html'] = '';
}
print (json_encode ($arResult));
?>
