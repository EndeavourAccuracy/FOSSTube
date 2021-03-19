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
function Error ($sText)
/*****************************************************************************/
{
	HTMLStart ('Text', 'Account', 'Text', 0, FALSE);
	print ('<h1>Text</h1>');
	print ($sText); HTMLEnd(); exit();
}
/*****************************************************************************/
function Overview ($iUserID)
/*****************************************************************************/
{
	$query_text = "SELECT
			video_id,
			video_title,
			video_adddate,
			video_istext
		FROM `fst_video`
		WHERE (user_id='" . $iUserID . "')
		AND ((video_istext='1') OR (video_istext='2'))
		AND (video_deleted='0')
		ORDER BY video_istext,video_adddate DESC,video_id DESC";
	$result_text = Query ($query_text);
	if (mysqli_num_rows ($result_text) != 0)
	{
		print ('<hr class="fst-hr" style="margin:10px 0;">');
		print ('<div>');
		while ($row_text = mysqli_fetch_assoc ($result_text))
		{
			$iVideoID = intval ($row_text['video_id']);
			$sCode = IDToCode ($iVideoID);
			$sTitle = $row_text['video_title'];
			$sDate = date ('j M Y', strtotime ($row_text['video_adddate']));
			$iIsText = intval ($row_text['video_istext']);

			print ('<span style="display:block; margin-bottom:10px;">');
			print ('<h2 id="h2-' . $sCode . '" style="display:inline-block;' .
				' margin-bottom:0; font-style:italic;' .
				'">' . Sanitize ($sTitle) . '</h2> - ');
			if ($iIsText == 1)
				{ print ('publ ' . $sDate); }
					else { print ('unpublished'); }
			print ('<br>');
			print (' <a href="/text/' . $sCode . '">compose</a>');
			switch ($iIsText)
			{
				case 1:
					print (' <a href="/v/' . $sCode . '">view</a>');
					print (' <a href="/edit/' . $sCode . '">edit</a>');
					break;
				case 2:
					print (' view');
					print (' <a href="/edit/' . $sCode . '">publish</a>');
					break;
			}
			print (' <a id="delete-' . $sCode . '" href="javascript:;">delete</a>');
			print ('</span>');
		}
		print ('</div>');

print ('
<script>
$("[id^=delete]").click(function(){
	var code = $(this).attr("id").replace("delete-","");
	var title = $("#h2-" + code).text();
	if (confirm ("Delete text \"" + title + "\"?")) {
		$.ajax({
			type: "POST",
			url: "/text/delete.php",
			data: ({
				code : code,
				csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
			}),
			dataType: "json",
			success: function(data) {
				var result = data["result"];
				var error = data["error"];
				if (result == 1)
				{
					window.location.replace("/text/");
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
/*****************************************************************************/
function Compose ($row_video, $sCode)
/*****************************************************************************/
{
	if ($row_video !== FALSE)
	{
		$sTitle = $row_video['video_title'];
		$sText = $row_video['video_text'];
	} else {
		$sTitle = '';
		$sText = '';
	}

print ('
<span style="display:block; margin-bottom:10px;">
Save early, and save often. After saving, you can always come back later to continue editing. To publish a saved text, return to the <a href="/text/">overview</a>.
</span>
');

print ('
<div>
<a id="show-tab-write" href="javascript:;" class="show-tab active">write</a>
<a id="show-tab-preview" href="javascript:;" class="show-tab">preview</a>
<a id="show-tab-help" href="javascript:;" class="show-tab">help</a>
</div>
');

	print ('<div id="tab-write" class="tab" style="display:block;">');
	TabWrite ($sTitle, $sText);
	print ('</div>');

	print ('<div id="tab-preview" class="tab" style="display:none;">');
	TabPreview();
	print ('</div>');

	print ('<div id="tab-help" class="tab" style="display:none;">');
	TabHelp();
	print ('</div>');

print ('
<script>
function Tab (active) {
	var tabs = ["write", "preview", "help"];
	for (var i = 0; i < tabs.length; i++)
	{
		if (tabs[i] == active)
		{
			$("#show-tab-" + tabs[i]).addClass("active");
			$("#tab-" + tabs[i]).css("display","block");
		} else {
			$("#show-tab-" + tabs[i]).removeClass("active");
			$("#tab-" + tabs[i]).css("display","none");
		}
	}
	if (active == "preview")
	{
		$("#preview-error").html(""); /*** Remove old error. ***/
		$("#preview").html("<img src=\"/images/loading.gif\" alt=\"loading\">");
		var title = $("#title").val();
		var text = $("#write-textarea").val();
		$.ajax({
			type: "POST",
			url: "/text/preview.php",
			data: ({
				title : title,
				text : text,
				csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
			}),
			dataType: "json",
			success: function(data) {
				var result = data["result"];
				var error = data["error"];
				var html = data["html"];
				if (result == 1)
				{
					$("#preview").html(html);
				} else {
					$("#preview-error").html(error);
				}
			},
			error: function() {
				$("#preview-error").html("Error calling preview.php.");
			}
		});
	}
}
$("#show-tab-write").click(function(){ Tab ("write"); });
$("#show-tab-preview").click(function(){ Tab ("preview"); });
$("#show-tab-help").click(function(){ Tab ("help"); });
</script>
');

print ('
<input id="code" type="hidden" value="' . Sanitize ($sCode) . '">
<div id="save-error" style="color:#f00; margin-top:10px;"></div>
<input id="save" type="button" value="Save">
<script>
$("#save").click(function(){
	$("#save-error").html(""); /*** Remove old error. ***/
	var title = $("#title").val();
	var text = $("#write-textarea").val();
	var code = $("#code").val();
	$.ajax({
		type: "POST",
		url: "/text/save.php",
		data: ({
			title : title,
			text : text,
			code : code,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			var code = data["code"];
			if (result == 1)
			{
				window.location.replace("/text/" + code);
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
}
/*****************************************************************************/
function TabWrite ($sTitle, $sText)
/*****************************************************************************/
{
	print ('<div class="tab-content">');

	/*** title ***/
	print ('<label for="title" class="lbl">Title:</label>');
	print ('<input type="text" id="title" value="' .
		Sanitize ($sTitle) .
		'" maxlength="100" style="width:600px; max-width:100%;" autofocus>');

	print ('<textarea id="write-textarea">' . Sanitize ($sText) . '</textarea>');

	print ('</div>');
}
/*****************************************************************************/
function TabPreview ()
/*****************************************************************************/
{
print ('
<div class="tab-content">
<div id="preview-error" style="color:#f00; margin-top:10px;"></div>
<div id="preview" class="text"></div>
</div>
');
}
/*****************************************************************************/
function TabHelp ()
/*****************************************************************************/
{
print ('
<div class="tab-content">
<h2>Limitations</h2>
<p>
When publishing a saved text, you can add one thumbnail/header image.
<br>
Other images and external links and <span style="font-style:italic;">not</span> supported.
<br>
Alternatives are respectively showing a link to an external image, and showing the link to the reader.
<br>
On ' . $GLOBALS['name'] . ', <a target="_blank" href="/">bold</a> text are links. An alternative you can use is [size], see below.
</p>
<h2>BBCode</h2>
');
	print ('<p>Formatting and styling is available through <a target="_blank" href="https://en.wikipedia.org/wiki/BBCode">BBCode</a> markup.</p>');
	print ('<span style="display:block; margin:20px 0;">');
	print ('This [u]is[/u] an [i]example[/i] of [color=green]BBCode[/color]. More [font=Courier New]information[/font] below.' . '<br>' . BBCodeToHTML ('This [u]is[/u] an [i]example[/i] of [color=green]BBCode[/color]. More [font=Courier New]information[/font] below.'));
	print ('</span>');
	print ('<hr class="fst-hr">');
	print ('[i]italic[/i]' . '<br>' .
		BBCodeToHTML ('[i]italic[/i]'));
	print ('<hr class="fst-hr">');
	print ('[u]underline[/u]' . '<br>' .
		BBCodeToHTML ('[u]underline[/u]'));
	print ('<hr class="fst-hr">');
	print ('[size=20]size[/size]' . '<br>' .
		BBCodeToHTML ('[size=20]size[/size]'));
	print ('<hr class="fst-hr">');
	print ('[ul]<br>[li]unordered[/li]<br>[li]list[/li]<br>[/ul]' . '<br>' .
		BBCodeToHTML ('[ul][li]unordered[/li][li]list[/li][/ul]'));
	print ('<hr class="fst-hr">');
	print ('[ol type="1"]<br>[li]ordered[/li]<br>[li]list[/li]<br>[/ol]' .
		'<br>' .
		BBCodeToHTML ('[ol type="1"][li]ordered[/li][li]list[/li][/ol]'));
	print ('Supported types are "1", "a", "A", "i" and "I".');
	print ('<hr class="fst-hr">');
	print ('[blockquote]blockquote[/blockquote]' . '<br>' .
		BBCodeToHTML ('[blockquote]blockquote[/blockquote]'));
	print ('<hr class="fst-hr">');
	print ('[center]center[/center]' . '<br>' .
		BBCodeToHTML ('[center]center[/center]'));
	print ('<hr class="fst-hr">');
	print ('[h2]h2[/h2]' . '<br>' .
		BBCodeToHTML ('[h2]h2[/h2]'));
	print ('[h3]h3[/h3]' . '<br>' .
		BBCodeToHTML ('[h3]h3[/h3]'));
	print ('[h2 id="anchor1"]h2 with anchor[/h2]' . '<br>' .
		BBCodeToHTML ('[h2 id="anchor1"]h2 with anchor[/h2]'));
	print ('[h3 id="anchor2"]h3 with anchor[/h3]' . '<br>' .
		BBCodeToHTML ('[h3 id="anchor2"]h3 with anchor[/h3]'));
	print ('[anchor id="anchor3"]anchor[/anchor]' . '<br>' .
		BBCodeToHTML ('[anchor id="anchor3"]anchor[/anchor]'));
	print ('<br>');
	print ('[linktoid=anchor3]link to id[/linktoid]' . '<br>' .
		BBCodeToHTML ('[linktoid=anchor3]link to id[/linktoid]'));
	print ('<hr class="fst-hr">');
	print ('[indent=20]indent[/indent]' . '<br>' .
		BBCodeToHTML ('[indent=20]indent[/indent]'));
	print ('<hr class="fst-hr">');
	print ('[color=red]color[/color]' . '<br>' .
		BBCodeToHTML ('[color=red]color[/color]'));
	print ('<br>');
	print ('[color=#f00]hex color[/color]' . '<br>' .
		BBCodeToHTML ('[color=#f00]hex color[/color]'));
	print ('<hr class="fst-hr">');
	print ('[font=Courier New]font[/font]' . '<br>' .
		BBCodeToHTML ('[font=Courier New]font[/font]'));
	print ('<br>');
	print ('Widely supported fonts are "Arial", "Arial Narrow", "Courier New", "Times New Roman" and "Verdana". This website also explicitly supports "CenturySchL-Roma".');
	print ('<hr class="fst-hr">');
	print ('[justify]justify stretches the lines so that each line has equal width, like in newspapers and magazines[/justify]' . '<br>' .
		'<span style="display:block; width:200px;">' . BBCodeToHTML ('[justify]justify stretches the lines so that each line has equal width, like in newspapers and magazines[/justify]') . '</span>');
	print ('<hr class="fst-hr">');
	print ('[scrollable height="50"]scrollable[/scrollable]' . '<br>' .
		BBCodeToHTML ('[scrollable height="50"]scrollable[/scrollable]'));
	print ('<hr class="fst-hr">');
	print ('[sup]sup[/sup]' . '<br>' . BBCodeToHTML ('[sup]sup[/sup]'));
	print ('<hr class="fst-hr">');
	print ('[pre]pre[/pre]' . '<br>' . BBCodeToHTML ('[pre]pre[/pre]'));
	print ('<hr class="fst-hr">');
	print ('[s]strikethrough[/s]' . '<br>' .
		BBCodeToHTML ('[s]strikethrough[/s]'));
print ('
</div>
');
}
/*****************************************************************************/

/*** $sCode ***/
if (isset ($_GET['code']))
{
	$sCode = $_GET['code'];
} else { $sCode = ''; }

/*** Errors, $iUserID and $row_video ***/
if (IsMod()) { Error ('Not as a mod.'); }
if (MayAdd ('texts') === FALSE) { Error ('You may not add texts.'); }
if (!isset ($_SESSION['fst']['user_id']))
{
	Error ('You are not logged in.' . '<br>' .
		'To compose, first <a href="/signin/">sign in</a>.');
} else { $iUserID = $_SESSION['fst']['user_id']; }
if (($sCode != '') && ($sCode != 'new'))
{
	$iVideoID = CodeToID ($sCode);
	$query_video = "SELECT
			video_title,
			video_text
		FROM `fst_video`
		WHERE (user_id='" . $_SESSION['fst']['user_id'] . "')
		AND (video_id='" . $iVideoID . "')
		AND ((video_istext='1') OR (video_istext='2'))";
	$result_video = Query ($query_video);
	if (mysqli_num_rows ($result_video) != 1)
	{
		Error ('Text "' . Sanitize ($sCode) . '" either does not exist' .
			' or is owned by someone else.');
	} else {
		$row_video = mysqli_fetch_assoc ($result_video);
	}
} else { $row_video = FALSE; }

if ($sCode == '')
{
	HTMLStart ('Text', 'Account', 'Text', 0, FALSE);
	print ('<h1>Text</h1>');

	/*** Deleted note. ***/
	if ((isset ($_SESSION['fst']['deleted'])) &&
		($_SESSION['fst']['deleted'] == 2))
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

print ('
<span style="display:block; margin-bottom:10px;">
<span style="color:#f00;">
Sexually explicit content is not allowed.
</span>
<br>
See the <a href="/terms/">Terms of service</a> for more information.
</span>
');

	print ('<p>In addition to videos, ' . $GLOBALS['name'] . ' facilitates the publication of text, such as articles and blog posts.<br>Formatting and styling is available through <a target="_blank" href="https://en.wikipedia.org/wiki/BBCode">BBCode</a> markup.</p>');
print ('
<span style="display:block; margin-top:10px; color:#00f;">
Tip: see also the <a href="/forum/" style="color:#00f;">Forum</a>.
</span>
');
	print ('<hr class="fst-hr" style="margin:10px 0;">');
	print ('<a href="/text/new">Compose new</a>');
	Overview ($iUserID);
} else {
	HTMLStart ('Compose', 'Account', 'Text', 0, FALSE);
	print ('<h1>Compose</h1>');
	LinkBack ('/text/', 'Text');

	/*** Saved note. (from compose) ***/
	if ((isset ($_SESSION['fst']['saved'])) &&
		($_SESSION['fst']['saved'] == 1))
	{
		$sTNow = date ('H:i');
		print ('<div class="note saved">Last saved at ' . $sTNow . ' UTC.</div>');
		unset ($_SESSION['fst']['saved']);
	}

	Compose ($row_video, $sCode);
}
HTMLEnd();
?>
