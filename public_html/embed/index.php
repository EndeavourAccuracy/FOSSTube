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

/*** Prevent $GLOBALS['no_embed_domains'] from embedding. ***/
if (isset ($_SERVER['HTTP_REFERER']))
{
	$sRef = $_SERVER['HTTP_REFERER'];
	foreach ($GLOBALS['no_embed_domains'] AS $sDomain)
	{
		if (strpos ($sRef, $sDomain) !== FALSE)
		{
			print ('Domain "' . $sDomain . '" may not embed.');
			exit();
		}
	}
}

if (isset ($_GET['code']))
{
	$sCode = $_GET['code'];
	$iVideoID = CodeToID ($sCode);

	$query_video = "SELECT
			fv.video_title,
			fv.video_thumbnail,
			fv.video_360,
			fv.video_720,
			fv.video_1080,
			fv.video_deleted,
			fv.projection_id,
			fp.projection_videojs
		FROM `fst_video` fv
		LEFT JOIN `fst_projection` fp
			ON fv.projection_id = fp.projection_id
		WHERE (video_id='" . $iVideoID . "')
		AND (video_360='1')";
	$result_video = Query ($query_video);
	if (mysqli_num_rows ($result_video) != 1)
	{
		HTMLStart ('404 Not Found', '', '', 0, TRUE);
		print ('<h1>404 Not Found</h1>');
		print ('Video "' . Sanitize ($sCode) . '" does not exist.');
	} else {
		$row_video = mysqli_fetch_assoc ($result_video);
		$iDeleted = $row_video['video_deleted'];
		if ($iDeleted != 0)
		{
			HTMLStart ('404 Not Found', '', '', 0, TRUE);
			print ('<h1>404 Not Found</h1>');
			print ('Content "' . Sanitize ($sCode) . '" has been deleted by');
			switch ($iDeleted)
			{
				case 1: case 2: case 5: print (' its publisher.'); break;
				case 3: case 4: print (' a moderator.'); break;
			}
		} else {
			$sTitle = $row_video['video_title'];
			$iThumb = $row_video['video_thumbnail'];
			$iProjection = intval ($row_video['projection_id']);
			$sProjection = $row_video['projection_videojs'];
			if ($iProjection == 0)
				{ $sQ360 = '360p'; $sQ720 = '720p'; $sQ1080 = '1080p'; }
					else { $sQ360 = '360s'; $sQ720 = '720s'; $sQ1080 = '1080s'; }
			$iQ360 = $row_video['video_360'];
			$sQuality = 'Size: <a id="q360" href="javascript:;" class="activep">' .
				$sQ360 . '</a>';
			$iQ720 = $row_video['video_720'];
			if ($iQ720 == 1) { $sQuality .=
				' <a id="q720" href="javascript:;">' . $sQ720 . '</a>'; }
			$iQ1080 = $row_video['video_1080'];
			if ($iQ1080 == 1) { $sQuality .=
				' <a id="q1080" href="javascript:;">' . $sQ1080 . '</a>'; }
			if (($iQ720 == 2) || ($iQ1080 == 2)) { $sQuality .= ' ' . Processing(); }

			IncreaseViews ($iVideoID);

			HTMLStart ($sTitle, '', '', 0, TRUE);

			if (isset ($_GET['t']))
			{
				$iSeconds = intval ($_GET['t']);
				$sSeconds = '#t=' . $iSeconds;
			} else { $iSeconds = 0; $sSeconds = ''; }

print ('
<div>
<video id="video" poster="' . ThumbURL ($sCode, '720', $iThumb, TRUE) . '" preload="metadata" onloadstart="this.volume=0.5" style="max-width:100%;" title="' . Sanitize ($sTitle) . '" controls class="video-js">
<source src="' . VideoURL ($sCode, '360') . $sSeconds . '" type="video/mp4">
Your browser or OS does not support HTML5 MP4 video with H.264.
</video>
');

			if ($iProjection != 0)
			{
print ('
<script src="/videojs/video.min.js"></script>
<script src="/videojs/videojs-vr.min.js"></script>
<link rel="stylesheet" type="text/css" href="/videojs/video-js.min.css">
<link rel="stylesheet" type="text/css" href="/videojs/videojs-vr.css">
<script>
var fvideo = videojs("video");
fvideo.vr({
	projection: "' . $sProjection . '",
	responsive: "true"
});
</script>
');
			}

print ('
</div>
<div style="text-align:center;">
');

			print ('<span id="quality">' . $sQuality . '</span>');
			print (' | ');

print ('
<script>
$("#q360").click(function(){
	Size("360", "' . $iProjection . '", "' . VideoURL ($sCode, '360') . '");
});
</script>
');

if ($iQ720 == 1)
{
print ('
<script>
$("#q720").click(function(){
	Size("720", "' . $iProjection . '", "' . VideoURL ($sCode, '720') . '");
});
</script>
');
}

if ($iQ1080 == 1)
{
print ('
<script>
$("#q1080").click(function(){
	Size("1080", "' . $iProjection . '", "' . VideoURL ($sCode, '1080') . '");
});
</script>
');
}

			/*** Report. ***/
print ('
<a target="_blank" href="/contact.php?video=' . $sCode . '">
<img src="/images/reported_off.png" title="report video" alt="reported off">
</a>
');

			/*** "on (website)" ***/
print ('
<a target="_blank" href="' . $GLOBALS['protocol'] . '://www.' . $GLOBALS['domain'] . '/v/' . $sCode);
if ($iSeconds != 0) { print ('?t=' . $iSeconds); }
print ('" style="margin-left:10px;">
on ' . $GLOBALS['name'] . '
</a>
');

print ('
</div>
');
		}
	}
} else {
	HTMLStart ('404 Not Found', '', '', 0, TRUE);
	print ('<h1>404 Not Found</h1>');
	print ('Incorrectly embedded.');
}

print ('
</div>
</div>

</div>

</body>
</html>
');
?>
