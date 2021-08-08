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

if (isset ($_SESSION['fst']['user_id']))
{
	unset ($_SESSION['fst']['user_id']);
	unset ($_SESSION['fst']['user_username']);
	unset ($_SESSION['fst']['user_pref_nsfw']);
	unset ($_SESSION['fst']['user_pref_cwidth']);
	unset ($_SESSION['fst']['user_pref_tsize']);
	/***/
	unset ($_SESSION['fst']['step_signup']);
	unset ($_SESSION['fst']['step_forgot']);
	unset ($_SESSION['fst']['step_account']);
}

header ('Location: /');
?>
