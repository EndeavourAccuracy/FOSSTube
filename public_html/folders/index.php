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

HTMLStart ('Folders', 'Account', 'Folders', 0, FALSE);
print ('<h1>Folders</h1>');
if (IsMod()) { print ('Not as a mod.'); HTMLEnd(); exit(); }
if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To view/edit folders, first <a href="/signin/">sign in</a>.');
} else {
	/*** Deleted note. ***/
	if ((isset ($_SESSION['fst']['deleted'])) &&
		($_SESSION['fst']['deleted'] == 1))
	{
		print ('<div class="note deleted">Deleted.</div>');
		unset ($_SESSION['fst']['deleted']);
	}
	/*** Saved note. (from edit) ***/
	if ((isset ($_SESSION['fst']['saved'])) &&
		($_SESSION['fst']['saved'] == 1))
	{
		print ('<div class="note saved">Saved.</div>');
		unset ($_SESSION['fst']['saved']);
	}

	$query_folders = "SELECT
			folder_id,
			folder_title,
			folder_public,
			(SELECT COUNT(*) FROM `fst_folderitem` ffi LEFT JOIN `fst_video` fv ON ffi.video_id = fv.video_id WHERE (ffi.folder_id = ff.folder_id) AND (fv.video_deleted='0')) AS items
		FROM `fst_folder` ff
		WHERE (user_id='" . $_SESSION['fst']['user_id'] . "')
		ORDER BY folder_title ASC, folder_id DESC";
	$result_folders = Query ($query_folders);
	if (mysqli_num_rows ($result_folders) == 0)
	{
		print ('<span style="display:block;">You have no folders.<br>To store content, use a <img src="/images/folder_off.png" alt="folder off"> folder icon.</span>');
	} else {
		while ($row_folders = mysqli_fetch_assoc ($result_folders))
		{
			$iFolderID = intval ($row_folders['folder_id']);
			$sTitle = $row_folders['folder_title'];
			$iPublic = intval ($row_folders['folder_public']);
			$iItems = intval ($row_folders['items']);
			if ($iItems == 1) { $sItems = 'item'; } else { $sItems = 'items'; }

			print ('<span style="display:block; margin-bottom:10px;">');
			print ('<h2 id="h2-' . $iFolderID . '" style="display:inline-block;' .
				' margin-bottom:0;">' . Sanitize ($sTitle) . '</h2>' .
				' (' . $iItems . ' ' . $sItems . ')');
			if ($iPublic == 1) { print (' - public'); }
			print ('<br>');
			print (' <a href="/folder/' . $iFolderID . '">view</a>');
			print (' <a href="/editf/' . $iFolderID . '">edit</a>');
			print (' <a id="delete-' . $iFolderID .
				'" href="javascript:;">delete</a>');
			print ('</span>');
		}

print ('
<script>
$("[id^=delete]").click(function(){
	var folder = $(this).attr("id").replace("delete-","");
	var title = $("#h2-" + folder).text();
	if (confirm ("Delete folder \"" + title + "\"?")) {
		$.ajax({
			type: "POST",
			url: "/folders/delete.php",
			data: ({
				folder : folder,
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
					alert(error);
				}
			},
			error: function() {
				alert("Error calling delete.php.");
			}
		});
	}
});
</script>
');
	}
}
HTMLEnd();
?>
