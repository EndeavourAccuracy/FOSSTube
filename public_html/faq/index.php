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
function Q1 ()
/*****************************************************************************/
{
print ('
<h2 id="Q1" class="question">
Is your video larger than ' . GetSizeHuman (AllowedBytes()) . '?
</h2>
<span>
Either use video editing software (such as <a target="_blank" href="https://kdenlive.org/">Kdenlive</a>) to split your video into multiple parts, or use <a target="_blank" href="https://ffmpeg.org/">FFmpeg</a> to reduce the size of your single video.
<br>
If your video is less than or equal to 1920x1080 (= 1080p; Full HD), run:
<br>
<span style="font-family:monospace;">ffmpeg -i <i>inputfile</i> -vcodec libx264 -crf 20 output.avi</span>
<br>
If your video is even greater than 1920x1080, run:
<br>
<span style="font-family:monospace;">ffmpeg -i <i>inputfile</i> -vcodec libx264 -crf 20 -vf scale=1920:1080 output.avi</span>
<br>
If the resulting <span style="font-family:monospace;">output.avi</span> is still too large, scale your video down to 720p:
<br>
<span style="font-family:monospace;">ffmpeg -i <i>inputfile</i> -vcodec libx264 -crf 20 -vf scale=1280:720 output.avi</span>
</span>
');
}
/*****************************************************************************/
function Q2 ()
/*****************************************************************************/
{
print ('
<h2 id="Q2" class="question" style="margin-top:30px;">
What do subscribe and follow do?
</h2>
<span>
Once you subscribe to a user\'s content, any new videos/texts they publish will show up on your <a href="/notifications/">Notifications</a> page.
<br>
If one or more notifications exist, you will automatically see a green notice about this in the menu bar when logged in.
<br>
You will <span style="font-style:italic;">not</span> receive emails.
<br>
Once you follow a user\'s microblog, any new posts they publish will show up on your <a href="/timeline/">Timeline</a>.
<br>
You will <span style="font-style:italic;">not</span> receive emails.
</span>
');
}
/*****************************************************************************/
function Q3 ()
/*****************************************************************************/
{
print ('
<h2 id="Q3" class="question" style="margin-top:30px;">
What is video adoption?
</h2>
<span>
By explicitly choosing semi-deletion of your videos, you are allowing other users to take control of these videos during a two-day period. The videos, including their comments, likes and view counts, will be transferred and become part of whichever channel decides to adopt them. You can not exclude certain channels, so keep in mind that <span style="font-style:italic;">anyone</span> could take over, rename, etc. your videos. If adoption does not take place during the two-day period, your videos will be queued for permanent deletion.
</span>
');

	$query_adopt = "SELECT
			COUNT(*) AS amount
		FROM `fst_video`
		WHERE (video_deleted='5')
		AND (video_deletedate BETWEEN (NOW() - INTERVAL 2 DAY) AND NOW())";
	$result_adopt = Query ($query_adopt);
	$row_adopt = mysqli_fetch_assoc ($result_adopt);
	$iAmount = $row_adopt['amount'];
print ('
<span style="display:block; margin-top:10px;">
Videos currently up for <a href="/adopt/">adoption</a>: ' . $iAmount . '
</span>
');
}
/*****************************************************************************/
function Q4 ()
/*****************************************************************************/
{
print ('
<h2 id="Q4" class="question" style="margin-top:30px;">
How to add spherical (e.g. 360-degree) videos?
</h2>
<span>
First, add spherical metadata to your video, for instance with Google\'s <a target="_blank" href="https://github.com/google/spatial-media/tree/master/spatialmedia">Spatial Media Metadata Injector</a>. Then, upload the video. Finally, edit your video to select the correct "spherical projection" from the drop-down list.
</span>
');
}
/*****************************************************************************/

HTMLStart ('FAQ', 'About', 'FAQ', 0, FALSE);
print ('<h1>FAQ</h1>');
if (!isset ($_SESSION['fst']['user_id']))
{
	print ('You are not logged in.' . '<br>');
	print ('To read the FAQ, first <a href="/signin/">sign in</a>.');
} else {
	Q1();
	Q2();
	Q3();
	Q4();
}
HTMLEnd();
?>
