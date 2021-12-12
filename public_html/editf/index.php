<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.4 (December 2021)
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
function EditForm ($iFolderID)
/*****************************************************************************/
{
	print ('<h2>Properties</h2>');

	$query_folder = "SELECT
			folder_title,
			folder_description,
			folder_public
		FROM `fst_folder`
		WHERE (folder_id='" . $iFolderID . "')";
	$result_folder = Query ($query_folder);
	$row_folder = mysqli_fetch_assoc ($result_folder);
	$sTitle = $row_folder['folder_title'];
	$sDesc = $row_folder['folder_description'];
	$iPublic = intval ($row_folder['folder_public']);

	print ('<input type="hidden" id="folder" value="' . $iFolderID . '">');

	/*** title ***/
	print ('<label for="title" class="lbl">Title:</label>');
	print ('<input type="text" id="title" value="' .
		Sanitize ($sTitle) .
		'" maxlength="100" style="width:600px; max-width:100%;">');

	/*** description ***/
	print ('<label for="description" class="lbl">Description:</label>');
	print ('<textarea id="description" style="width:600px; max-width:100%; height:70px;">' .
		Sanitize ($sDesc) . '</textarea>');

	/*** public ***/
	print ('<span style="display:block; margin-top:10px;">');
	print ('<label for="public" class="lbl">Public folder:</label>');
	print ('<input type="checkbox" id="public"');
	if ($iPublic == 1) { print (' checked'); }
	print ('> Yes');
	print ('</span>');
	print ('<span style="display:block; font-style:italic;">(Content can NOT be hidden sitewide by putting it in private folders.)</span>');

	print ('<div id="save-error" style="color:#f00; margin-top:10px;"></div>');
	print ('<input type="button" id="save" value="Save" style="margin-top:10px;">');

print ('
<script>
$("#save").click(function(){
	var folder = $("#folder").val();
	var title = $("#title").val();
	var description = $("#description").val();
	var public_bool = $("#public").is(":checked");
	if (public_bool == false)
		{ var public = 0; } else { var public = 1; }

	$.ajax({
		type: "POST",
		url: "/editf/save.php",
		data: ({
			folder : folder,
			title : title,
			description : description,
			public : public,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				window.location.replace("/folders/");
			} else {
				$("#save-error").html(error);
			}
		},
		error: function() {
			$("#save-error").html("Error calling save.php.");
		}
	});
});
</script>
');

	print ('<h2 style="margin-top:10px;">Items</h2>');

	print ('<div id="items" style="margin-top:10px;">');
	print ('<img src="/images/loading.gif" alt="loading"></div>');

print ('
<script>
var filters = {};
filters["threshold"] = 0;
filters["nsfw"] = 3;
filters["folder"] = ' . $iFolderID . ';
$(document).ready(function(){ VideosJS ("items", "editf", "itemdesc", 0, "", "", "", filters); });
</script>
');
}
/*****************************************************************************/

HTMLStart ('Edit folder', '', '', 0, FALSE);
print ('<h1>Edit folder</h1>');
if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To edit folders, first <a href="/signin/">sign in</a>.');
} else {
	if (isset ($_GET['folder']))
	{
		$iFolderID = intval ($_GET['folder']);
		$iUserID = $_SESSION['fst']['user_id'];

		if (FolderIsFromUser ($iFolderID, $iUserID) !== FALSE)
		{
			LinkBack ('/folders/', 'Folders');

			/*** Stored note. ***/
			if ((isset ($_SESSION['fst']['stored'])) &&
				($_SESSION['fst']['stored'] == 1))
			{
				print ('<div class="note stored">Stored.</div>');
				unset ($_SESSION['fst']['stored']);
			}

			EditForm ($iFolderID);
		} else {
			print ('Folder "' . $iFolderID . '" is not yours.');
		}
	} else {
		print ('To edit, select a <a href="/folders/">folder</a>.');
	}
}
HTMLEnd();
?>
