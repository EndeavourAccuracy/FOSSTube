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

if ((isset ($_POST['csrf_token'])) &&
	(TokenCorrect ($_POST['csrf_token'])))
{
	if ((isset ($_POST['poll'])) &&
		(isset ($_POST['options'])))
	{
		$iPollID = intval ($_POST['poll']);

		/*** $arOptions ***/
		$arOptions = array();
		foreach ($_POST['options'] as $iOption)
		{
			array_push ($arOptions, $iOption);
		}
		$arOptions = array_unique ($arOptions);

		$sOpen = PollOpen ($iPollID);
		if (($sOpen !== FALSE) && ($sOpen != -1))
		{
			if (isset ($_SESSION['fst']['user_id']))
			{
				$iUserID = intval ($_SESSION['fst']['user_id']);
				$sIP = GetIP();
				$iVoted = PollVoted ($iPollID, $iUserID, $sIP);

				if ($iVoted == 0)
				{
					$query_max = "SELECT
							poll_nroptions,
							poll_maxvotesperuser
						FROM `fst_poll`
						WHERE (poll_id='" . $iPollID . "')";
					$result_max = Query ($query_max);
					$row_max = mysqli_fetch_assoc ($result_max);
					$iNrOptions = intval ($row_max['poll_nroptions']);
					$iMaxVotesPerUser = intval ($row_max['poll_maxvotesperuser']);

					$iNrVotes = count ($arOptions);
					if (($iNrVotes >= 1) && ($iNrVotes <= $iMaxVotesPerUser))
					{
						$sError = '';
						foreach ($arOptions as $iOption)
						{
							if (($iOption < 1) || ($iOption > $iNrOptions))
								{ $sError = 'Invalid option value.'; }
						}
						if ($sError == '')
						{
							foreach ($arOptions as $iOption)
							{
								$query_vote = "INSERT INTO `fst_vote` SET
									user_id='" . $iUserID . "',
									poll_id='" . $iPollID . "',
									vote_option='" . $iOption . "',
									vote_ip='" . $sIP . "'";
								Query ($query_vote);
							}

							$arResult['result'] = 1;
							$arResult['error'] = '';
						} else {
							$arResult['result'] = 0;
							$arResult['error'] = $sError;
						}
					} else {
						$arResult['result'] = 0;
						$arResult['error'] = 'You picked too many options.';
					}
				} else {
					$arResult['result'] = 0;
					switch ($iVoted)
					{
						case 1: $arResult['error'] = 'You have already voted.'; break;
						case 2: $arResult['error'] = 'Your IP has already voted.'; break;
					}
				}
			} else {
				$arResult['result'] = 0;
				$arResult['error'] = 'To vote, <a href="/signin/">sign in</a>.';
			}
		} else {
			$arResult['result'] = 0;
			$arResult['error'] = 'Poll is closed or nonexistent.';
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
