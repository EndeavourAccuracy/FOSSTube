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
function Thumbnails ($iThumb, $sCode, $iIsText)
/*****************************************************************************/
{
	$sSixth = dirname (__FILE__) . '/..' . ThumbURL ($sCode, '180', 6, FALSE);
	if (file_exists ($sSixth) === TRUE)
		{ $iNrThumbs = 6; } else { $iNrThumbs = 5; }
	if ($iIsText == 0)
		{ $iFirstThumb = 1; } else { $iFirstThumb = 5; }

	print ('<div id="thumbnail">');
	for ($iLoopJPG = $iFirstThumb; $iLoopJPG <= $iNrThumbs; $iLoopJPG++)
	{
		if ($iLoopJPG == $iThumb)
		{
			$iActive = 1;
			$sBorder = '#414dc5';
		} else {
			$iActive = 0;
			$sBorder = '#fff';
		}

		print ('<a href="javascript:;">');
		print ('<span style="display:inline-block; float:left; border:1px solid #000; margin:0 10px 10px 0;">');
		print ('<span name="thumb_' . $iLoopJPG . '" data-id="' . $iLoopJPG .
			'" data-active="' . $iActive .
			'" style="display:block; border:5px solid ' . $sBorder . ';">');
		print ('<img src="' . ThumbURL ($sCode, '180', $iLoopJPG, TRUE) . '" alt="preview" style="display:block; width:150px; border:1px solid #000;">');
		print ('</span>');
		print ('</span>');
		print ('</a>');
	}
	print ('<span style="display:block; clear:both;"></span>');
print ('
<script>
$("[name^=\'thumb\']").click(function(){
	var id = $(this).data("id");
	$("[name^=\'thumb\']").each(function(){
		$(this).data("active","0");
		$(this).css("border-color","#fff");
	});
	$(this).data("active","1");
	$(this).css("border-color","#414dc5");
});
</script>
');
	print ('</div>');
}
/*****************************************************************************/
function EditForm ($row_video, $sCode, $iIsText)
/*****************************************************************************/
{
	$sTitle = $row_video['video_title'];
	$sDesc = $row_video['video_description'];
	$iThumb = $row_video['video_thumbnail'];
	$sTags = $row_video['video_tags'];
	$iLicense = $row_video['video_license'];
	$iCat = $row_video['category_id'];
	$iRestricted = $row_video['video_restricted'];
	$iCommentsAllow = $row_video['video_comments_allow'];
	$iCommentsShow = $row_video['video_comments_show'];
	$iLanguageID = $row_video['language_id'];
	$iNSFW = $row_video['video_nsfw'];
	$sSubtitles = $row_video['video_subtitles'];
	$iLength = intval ($row_video['length']);
	$sSphMpProjection = $row_video['video_sph_mpprojection'];
	$sSphStereo3DType = $row_video['video_sph_stereo3dtype'];
	$iProjection = intval ($row_video['projection_id']);
	if ($iIsText == 2)
		{ $sSaveValue = 'Publish'; }
			else { $sSaveValue = 'Save changes'; }

	print ('<input type="hidden" id="code" value="' . $sCode . '">');

	if (($iIsText == 2) && ($iLength < 1000))
	{
print ('
<span style="display:block; margin:10px 0; color:#00f;">
Tip: your text is quite short (' . $iLength . ' characters); perhaps it is more suitable as a <a href="/forum/" style="color:#00f;">Forum</a> topic?
</span>
');
	}

	/*** title ***/
	print ('<label for="title" class="lbl">Title:</label>');
	print ('<input type="text" id="title" value="' .
		Sanitize ($sTitle) .
		'" maxlength="100" style="width:600px; max-width:100%;">');

	/*** description ***/
	print ('<label for="description" class="lbl">Description:</label>');
	print ('<textarea id="description" style="width:600px; max-width:100%; height:70px;">' .
		Sanitize ($sDesc) . '</textarea>');

	/*** thumbnail ***/
	print ('<label for="thumbnail" class="lbl">Thumbnail');
	if (intval (GetUserInfo ($_SESSION['fst']['user_id'],
		'user_priv_customthumbnails')) == 1)
		{ print (' (<a href="/thumbnail/' . $sCode . '">custom</a>)'); }
	print (':</label>');
	Thumbnails ($iThumb, $sCode, $iIsText);

	/*** tags ***/
	print ('<label for="tags" class="lbl">Tags (comma-separated):</label>');
	print ('<input type="text" id="tags" value="' .
		Sanitize ($sTags) . '" maxlength="100" style="width:600px; max-width:100%; margin-bottom:0;">');
print ('
<span style="display:block; font-size:12px; font-style:italic;">
Among other things, tags impact related content and search results.
</span>
');

	/*** license ***/
	print ('<span style="display:block; margin-top:10px;">');
	print ('<label for="license" class="lbl">License: <a target="_blank" href="/license/"><img src="/images/icon_info.png" alt="info" style="vertical-align:middle;"></a></label>');
	print ('<select id="license">');
	print ('<option value="1"');
	if ($iLicense == 1) { print (' selected'); }
	print ('>Standard ' . $GLOBALS['name'] . ' license</option>');
	print ('<option value="2"');
	if ($iLicense == 2) { print (' selected'); }
	print ('>Creative Commons Attribution 3.0</option>');
	print ('</select>');
	print ('</span>');

	/*** category ***/
	print ('<span style="display:block; margin-top:10px;">');
	print ('<label for="category" class="lbl">Category:</label>');
	print ('<select id="category">');
	print ('<option value="">Select...</option>');
	$query_cats = "SELECT
			category_id,
			category_name
		FROM `fst_category`";
	$result_cats = Query ($query_cats);
	while ($row_cats = mysqli_fetch_assoc ($result_cats))
	{
		print ('<option value="' . $row_cats['category_id'] . '"');
		if ($iCat == $row_cats['category_id']) { print (' selected'); }
		print ('>' . $row_cats['category_name'] . '</option>');
	}	
	print ('</select>');
	print ('</span>');

	/*** restricted ***/
	print ('<span style="display:block; margin-top:10px;">');
	print ('<label for="restricted" class="lbl">Age restricted:</label>');
	print ('<input type="checkbox" id="restricted"');
	if ($iRestricted == 1) { print (' checked'); }
	print ('> Yes');
	print ('</span>');

	/*** allow ***/
	print ('<span style="display:block; margin-top:10px;">');
	print ('<label for="allow" class="lbl">Allow comments:</label>');
	print ('<input type="checkbox" id="allow"');
	if ($iCommentsAllow == 1) { print (' checked'); }
	print ('> Yes');
	print ('</span>');

	/*** show ***/
	print ('<span style="display:block; margin-top:10px;">');
	print ('<label for="show" class="lbl">If allowed, show comments:</label>');
	print ('<select id="show">');
	print ('<option value="1"');
	if ($iCommentsShow == 1) { print (' selected'); }
	print ('>All</option>');
	print ('<option value="2"');
	if ($iCommentsShow == 2) { print (' selected'); }
	print ('>Approved by me</option>');
	print ('</select>');
	print ('</span>');

	/*** language ***/
	print ('<span style="display:block; margin-top:10px;">');
	print ('<label for="language" class="lbl">Language:</label>');
	print ('<select id="language">' . "\n");
	print ('<option value="">Select...</option>' . "\n");
	$query_lang = "SELECT
			language_id,
			language_nameeng,
			language_namelocal
		FROM `fst_language`
		ORDER BY language_nameeng";
	$result_lang = Query ($query_lang);
	while ($row_lang = mysqli_fetch_assoc ($result_lang))
	{
		print ('<option value="' . $row_lang['language_id'] . '"');
		if ($iLanguageID == $row_lang['language_id']) { print (' selected'); }
		print ('>' . $row_lang['language_nameeng'] . ' (' .
			$row_lang['language_namelocal'] . ')</option>' . "\n");
	}
	print ('</select>');
	print ('</span>');

	/*** NSFW ***/
	if ($iIsText == 0)
		{ $sContent = 'Video and thumbnail are'; }
			else { $sContent = 'Thumbnail is'; }
	print ('<span style="display:block; margin-top:10px;">');
	print ('<label for="nsfw" class="lbl">' .
		$sContent . ' appropriate to look at in a public/formal environment:</label>');
	print ('<select id="nsfw">');
	print ('<option value="2"');
	if ($iNSFW == 2) { print (' selected'); }
	print ('>Select...</option>');
	print ('<option value="0"');
	if ($iNSFW == 0) { print (' selected'); }
	print ('>safe for work</option>');
	print ('<option value="1"');
	if ($iNSFW == 1) { print (' selected'); }
	print ('>NOT safe for work</option>');
	print ('</select>');
	print ('</span>');

	/*** subtitles ***/
	if ($iIsText == 0)
	{
		print ('<span style="display:block; margin-top:10px;">');
		print ('<label for="subtitles" class="lbl">Optional subtitles (<a target="_blank" href="https://en.wikipedia.org/wiki/WebVTT">WebVTT</a> format):</label>');
		print ('<textarea id="subtitles" style="width:600px; max-width:100%; height:70px;">' .
			Sanitize ($sSubtitles) . '</textarea>');
		print ('</span>');
	} else {
		print ('<input type="hidden" id="subtitles" value="">');
	}

	/*** projection ***/
	if (($sSphMpProjection != '') || ($sSphStereo3DType != ''))
	{
		print ('<span style="display:block; margin-top:10px;">');
		print ('<label for="projection" class="lbl">Spherical projection:</label>');
		print ('<select id="projection">');
		print ('<option value="">Select...</option>');
		$query_pr = "SELECT
				projection_id,
				projection_name
			FROM `fst_projection`
			ORDER BY projection_order";
		$result_pr = Query ($query_pr);
		while ($row_pr = mysqli_fetch_assoc ($result_pr))
		{
			print ('<option value="' . $row_pr['projection_id'] . '"');
			if ($iProjection == $row_pr['projection_id']) { print (' selected'); }
			print ('>' . $row_pr['projection_name'] . '</option>');
		}
		print ('</select>');
		print ('</span>');
	} else {
		print ('<input type="hidden" id="projection" value="0">');
	}

	print ('<div id="save-error" style="color:#f00; margin-top:10px;"></div>');
	print ('<input type="button" id="save" value="' . $sSaveValue . '" style="margin-top:10px;">');

	if ($iIsText == 0)
		{ $sReplace = 'videos'; }
			else { $sReplace = 'text'; }

print ('
<script>
$("#save").click(function(){
	var code = $("#code").val();
	var title = $("#title").val();
	var description = $("#description").val();
	var thumb = 3;
	$("[name^=\'thumb\']").each(function(){
		if ($(this).data("active") == "1")
		{
			thumb = $(this).data("id");
		}
	});
	var tags = $("#tags").val();
	var license = $("#license").val();
	var category = $("#category").val();
	var restricted_bool = $("#restricted").is(":checked");
	if (restricted_bool == false)
		{ var restricted = 0; } else { var restricted = 1; }
	var allow_bool = $("#allow").is(":checked");
	if (allow_bool == false)
		{ var allow = 0; } else { var allow = 1; }
	var show = $("#show").val();
	var language = $("#language").val();
	var nsfw = $("#nsfw").val();
	var subtitles = $("#subtitles").val();
	var projection = $("#projection").val();

	$.ajax({
		type: "POST",
		url: "/edit/save.php",
		data: ({
			code : code,
			title : title,
			description : description,
			thumb : thumb,
			tags : tags,
			license : license,
			category : category,
			restricted : restricted,
			allow : allow,
			show : show,
			language : language,
			nsfw : nsfw,
			subtitles : subtitles,
			projection : projection,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				window.location.replace("/' . $sReplace . '/");
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

/*** $iIsText ***/
if (!isset ($_GET['code']))
	{ $iIsText = -1; }
		else { $iIsText = IsText ($_GET['code']); }
if ($iIsText === FALSE) { $iIsText = -1; }

switch ($iIsText)
{
	case -1: /*** unknown code ***/
		HTMLStart ('Edit', 'Account', 'Edit', 0, FALSE);
		print ('<h1>Edit</h1>');
		LinkBack ('/videos/', 'Videos');
		LinkBack ('/text/', 'Text');
		/*** No HTMLEnd() required. ***/
		break;
	case 0: /*** (published) video ***/
		HTMLStart ('Edit', 'Account', 'Edit', 0, FALSE);
		print ('<h1>Edit</h1>');
		LinkBack ('/videos/', 'Videos');
		break;
	case 1: /*** published text ***/
		HTMLStart ('Edit', 'Account', 'Edit', 0, FALSE);
		print ('<h1>Edit</h1>');
		LinkBack ('/text/', 'Text');
		break;
	case 2: /*** non-published text ***/
		HTMLStart ('Publish', 'Account', 'Edit', 0, FALSE);
		print ('<h1>Publish</h1>');
		LinkBack ('/text/', 'Text');
		break;
	case 3: /*** (published) forum text ***/
		HTMLStart ('Edit', 'Account', 'Edit', 0, FALSE);
		print ('<h1>Edit</h1>');
		LinkBack ('/videos/', 'Videos');
		LinkBack ('/text/', 'Text');
		print ('Forum text can not be edited.');
		HTMLEnd();
		break;
}

if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To edit videos, first <a href="/signin/">sign in</a>.');
} else {
	if (isset ($_GET['code']))
	{
		$sCode = $_GET['code'];
		$iVideoID = CodeToID ($sCode);
		$query_video = "SELECT
				video_title,
				video_description,
				video_thumbnail,
				video_tags,
				video_license,
				category_id,
				video_restricted,
				video_comments_allow,
				video_comments_show,
				language_id,
				video_nsfw,
				video_subtitles,
				CHAR_LENGTH(video_text) AS length,
				video_sph_mpprojection,
				video_sph_stereo3dtype,
				projection_id
			FROM `fst_video`
			WHERE (user_id='" . $_SESSION['fst']['user_id'] . "')
			AND (video_id='" . $iVideoID . "')";
		$result_video = Query ($query_video);
		if (mysqli_num_rows ($result_video) != 1)
		{
			print ('Content "' . Sanitize ($sCode) . '" either does not exist' .
				' or is owned by someone else.');
		} else {
			$row_video = mysqli_fetch_assoc ($result_video);
			EditForm ($row_video, $sCode, $iIsText);
		}
	} else {
		print ('To edit, select a <a href="/videos/">video</a>' .
			' or <a href="/text/">text</a>.');
	}
}
HTMLEnd();
?>
