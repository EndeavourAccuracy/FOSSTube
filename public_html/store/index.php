<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.1 (March 2021)
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
function StoreOptions ($iUserID)
/*****************************************************************************/
{
	$sReturn = '';

	$sReturn .= '<option value="">Select...</option>' . "\n";
	$sReturn .= '<option value="0">Create new folder</option>' . "\n";

	$query_folder = "SELECT
			folder_id,
			folder_title
		FROM `fst_folder`
		WHERE (user_id='" . $iUserID . "')
		ORDER BY folder_title ASC, folder_id DESC";
	$result_folder = Query ($query_folder);
	while ($row_folder = mysqli_fetch_assoc ($result_folder))
	{
		$iFolderID = intval ($row_folder['folder_id']);
		$sFolderTitle = $row_folder['folder_title'];

		$sReturn .= '<option value="' . $iFolderID . '">' .
			Sanitize ($sFolderTitle) . '</option>' . "\n";
	}

	return ($sReturn);
}
/*****************************************************************************/

HTMLStart ('Store', '', '', 0, FALSE);
print ('<h1>Store</h1>');
if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To store content, first <a href="/signin/">sign in</a>.');
} else {
	if (isset ($_GET['code']))
	{
		$sCode = $_GET['code'];
		if (VideoExists ($sCode) !== FALSE)
		{
			LinkBack ('/v/' . $sCode, 'Back');

print ('
<label for="folder" class="lbl">Store in folder:</label>
<select id="folder" required>
');

			print (StoreOptions ($_SESSION['fst']['user_id']));

print ('
</select>
<div id="store-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="store-store" value="Store">

<script>
$("#store-store").click(function (event) {
	var folder = $("#folder option:selected").val();

	$.ajax({
		type: "POST",
		url: "/store/store.php",
		data: ({
			folder : folder,
			code : "' . $sCode . '",
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			var folder = data["folder"];
			if (result == 1)
			{
				window.location.replace("/editf/" + folder);
			} else {
				$("#store-error").html(error);
			}
		},
		error: function() {
			$("#store-error").html("Error calling store.php.");
		}
	});
});
</script>
');
		} else {
			print ('Content "' . Sanitize ($sCode) . '" does not exist.');
		}
	} else {
		print ('To store content, use a <img src="/images/folder_off.png"' .
			' alt="folder off"> folder icon.');
	}
}
HTMLEnd();
?>
