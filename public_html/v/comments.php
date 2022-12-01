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
function Muted ($iCommentID, $sMuted)
/*****************************************************************************/
{
	/*** Only called if $bOwner is TRUE. ***/

	$sHTML = '';

$sHTML .= '
<span id="muted-' . $iCommentID . '">
<a name="muted" href="javascript:;" style="display:inline-block;">
<img src="/images/muted_' . $sMuted . '.png" title="mute" alt="muted ' . $sMuted . '">
</a>
</span>
';

	return ($sHTML);
}
/*****************************************************************************/
function Loved ($iCommentID, $bOwner, $sLoved)
/*****************************************************************************/
{
	$sHTML = '';

	if ($bOwner === TRUE)
	{
$sHTML .= '
<span id="loved-' . $iCommentID . '">
<a name="loved" href="javascript:;" style="display:inline-block;">
<img src="/images/loved_' . $sLoved . '.png" title="love" alt="loved ' . $sLoved . '">
</a>
</span>
';
	} else if ($sLoved == 'on') {
		$sHTML .= '<img src="/images/loved_on.png" title="love" alt="loved on"> ';
	}

	return ($sHTML);
}
/*****************************************************************************/
function Pinned ($iCommentID, $bOwner, $sPinned)
/*****************************************************************************/
{
	$sHTML = '';

	if ($bOwner === TRUE)
	{
$sHTML .= '
<span id="pinned-' . $iCommentID . '">
<a name="pinned" href="javascript:;" style="display:inline-block;">
<img src="/images/pinned_' . $sPinned . '.png" title="pin" alt="pinned ' . $sPinned . '">
</a>
</span>
';
	} else if ($sPinned == 'on') {
		$sHTML .= '<img src="/images/pinned_on.png" title="pin" alt="pinned on">';
	}

	return ($sHTML);
}
/*****************************************************************************/
function Hidden ($iCommentID, $bOwner, $bAuthor)
/*****************************************************************************/
{
	$sHTML = '';

	if (($bOwner === TRUE) || ($bAuthor === TRUE))
	{
$sHTML .= '
<span id="hidden-' . $iCommentID . '">
<a name="hidden" href="javascript:;" style="display:inline-block;">
<img src="/images/hidden_off.png" title="remove" alt="hidden off">
</a>
</span>
';
	}

	return ($sHTML);
}
/*****************************************************************************/
function Approved ($iCommentID, $bOwner, $bAuthor, $sApproved)
/*****************************************************************************/
{
	$sHTML = '';

	if ($bOwner === TRUE)
	{
$sHTML .= '
<span id="approved-' . $iCommentID . '">
<a name="approved" href="javascript:;" style="display:inline-block;">
<img src="/images/approved_' . $sApproved . '.png" title="approve" alt="approved ' . $sApproved . '">
</a>
</span>
';
	} else if ($bAuthor === TRUE) {
		$sHTML .= '<img src="/images/approved_' .
			$sApproved . '.png" title="approve" alt="approved ' . $sApproved . '">';
	}

	return ($sHTML);
}
/*****************************************************************************/
function Liked ($iCommentID, $bAuthor, $bLiked)
/*****************************************************************************/
{
	$sHTML = '';

	if ($bAuthor === FALSE)
	{
		if ($bLiked === TRUE)
		{
			$sHTML .= ' <img src="/images/liked_on.png" title="like" alt="liked on">';
		} else {
$sHTML .= '
<span id="liked-' . $iCommentID . '">
<a name="liked" href="javascript:;" style="display:inline-block;">
<img src="/images/liked_off.png" title="like" alt="liked off">
</a>
</span>
';
		}
	} else {
		$sHTML .= ' <img src="/images/liked_off.png" title="like" alt="liked off">';
	}
	$sHTML .= '<span id="likes-' . $iCommentID .
		'" class="likes">';
	$iLikes = LikesComment ($iCommentID);
	if ($iLikes > 0) { $sHTML .= $iLikes; }
	$sHTML .= '</span>';

	return ($sHTML);
}
/*****************************************************************************/
function ShowComment ($iVideoID, $iCommentsShow, $bOwner, $bMuted,
	$iUserID, $sCode, $iHighlightID, $iParentID, $row_comment, $bAuthor)
/*****************************************************************************/
{
	$sHTML = '';

	if ($iParentID == 0) { $sClass = 'comment'; } else { $sClass = 'reply'; }

	if ($iUserID != 0)
	{
		$query_lastviewed = "SELECT
				commentslastviewed_dt
			FROM `fst_commentslastviewed`
			WHERE (video_id='" . $iVideoID . "')
			AND (user_id='" . $iUserID . "')";
		$result_lastviewed = Query ($query_lastviewed);
		if (mysqli_num_rows ($result_lastviewed) == 1)
		{
			$row_lastviewed = mysqli_fetch_assoc ($result_lastviewed);

			if (strtotime ($row_lastviewed['commentslastviewed_dt']) <
				strtotime ($row_comment['comment_adddate']))
				{ $sClass .= ' new border'; }
		}
	}

	$sUser = $row_comment['user_username'];
	$sDate = date ('j F Y', strtotime ($row_comment['comment_adddate']));
	if ($row_comment['comment_loved'] == 0)
		{ $sLoved = 'off'; } else { $sLoved = 'on'; }
	if ($row_comment['comment_pinned'] == 0)
		{ $sPinned = 'off'; } else { $sPinned = 'on'; }
	if ($row_comment['comment_approved'] == 0)
		{ $sApproved = 'off'; } else { $sApproved = 'on'; }
	$query_liked = "SELECT
			likecomment_id
		FROM `fst_likecomment`
		WHERE (comment_id='" . $row_comment['comment_id'] . "')
		AND (user_id='" . $iUserID . "')";
	$result_liked = Query ($query_liked);
	if (mysqli_num_rows ($result_liked) == 1)
		{ $bLiked = TRUE; } else { $bLiked = FALSE; }
	$iCommentID = $row_comment['comment_id'];
	$iCommentHidden = intval ($row_comment['comment_hidden']);

$sHTML .= '
<div id="comment-' . $iCommentID . '" data-id="' . $iCommentID . '" class="' . $sClass . '"';
if ($iHighlightID == $iCommentID)
	{ $sHTML .= ' style="background-color:#ff0!important;"'; }
$sHTML .= '>
';

	if ($iCommentHidden == '0')
	{
		$iPatron = intval ($row_comment['user_patron']);
		if ($iPatron == 1)
			{ $sPatron = PatronStar(); } else { $sPatron = ''; }

$sHTML .= '
<div>
<span style="display:block; float:left; width:60px;">
' . GetUserAvatar ($sUser, 'small', 1) . '
</span>
<span style="display:block; float:left;">
<a target="_blank" href="/user/' . $sUser . '">' . $sUser . '</a>' . $sPatron;

		/*** (site owner) ***/
		if ((IsText ($sCode) == 3) &&
			(in_array ($sUser, $GLOBALS['owners']) === TRUE))
			{ $sHTML .= ' <span class="owner">(site owner)</span>'; }

		/*** muted ***/
		if (($bOwner === TRUE) && ($bAuthor === FALSE))
		{
			if (IsMuted ($iUserID, $row_comment['user_id']) === FALSE)
				{ $sMuted = 'off'; } else { $sMuted = 'on'; }
			if (((in_array ($sUser, $GLOBALS['owners']) === FALSE) &&
				(in_array ($sUser, $GLOBALS['admins']) === FALSE) &&
				(in_array ($sUser, $GLOBALS['mods']) === FALSE)) ||
				(IsOwner() === TRUE) ||
				(IsAdmin() === TRUE) ||
				(IsMod() === TRUE))
				{ $sHTML .= Muted ($iCommentID, $sMuted); }
		}

$sHTML .= '
<a target="_blank" href="/v/' . $sCode . '/' . $iCommentID . '#comment-' . $iCommentID . '"><img src="/images/icon_anchor.png" alt="anchor"></a> ' . $sDate . '
<br>
';

		/*** loved ***/
		$sHTML .= Loved ($iCommentID, $bOwner, $sLoved);

		/*** pinned ***/
		$sHTML .= Pinned ($iCommentID, $bOwner, $sPinned);

		/*** hidden ***/
		if (((in_array ($sUser, $GLOBALS['owners']) === FALSE) &&
			(in_array ($sUser, $GLOBALS['admins']) === FALSE) &&
			(in_array ($sUser, $GLOBALS['mods']) === FALSE)) ||
			(IsOwner() === TRUE) ||
			(IsAdmin() === TRUE) ||
			(IsMod() === TRUE))
			{ $sHTML .= Hidden ($iCommentID, $bOwner, $bAuthor); }

		/*** approved ***/
		if (IsText ($sCode) != 3)
		{
			if ($iCommentsShow == 2)
				{ $sHTML .= Approved ($iCommentID, $bOwner, $bAuthor, $sApproved); }
		}

		/*** reported ***/
		$sHTML .= '<a target="_blank" href="/contact.php?comment=' .
			$iCommentID . '">';
		$sHTML .= '<img src="/images/reported_off.png" title="report" alt="reported off">';
		$sHTML .= '</a>';

		/*** liked ***/
		$sHTML .= Liked ($iCommentID, $bAuthor, $bLiked);

$sHTML .= '
</span>
<span style="display:block; clear:both;"></span>
</div>
<div style="overflow-wrap:break-word;">' . nl2br (Times (Sanitize ($row_comment['comment_text']))) . '</div>
';

		if (($iCommentsShow == 2) && ($bOwner === TRUE))
		{
			if ($sApproved == 'on') { $sContent = ''; } else { $sContent = 'As the content publisher, you can press the gray check mark to approve this comment.'; }
			$sHTML .= '<span id="approved-hint-' . $iCommentID .
				'" style="display:block; margin-top:10px; color:#414dc5;">' .
				$sContent . '</span>';
		}

		if (($iUserID != 0) && ($bMuted === FALSE) && (IsMod() === FALSE))
		{
$sHTML .= '
<div id="reply-' . $iCommentID . '">
<a href="javascript:;" name="reply">add reply</a>
</div>
';
		}
	} else {
		$sHTML .= '<span style="font-style:italic;">(removed)</span>';
	}

	$sHTML .= ShowComments ($iVideoID, $iCommentsShow, $bOwner, $bMuted,
		$iUserID, $sCode, $iHighlightID, $iCommentID);

	$sHTML .= '</div>';

	return ($sHTML);
}
/*****************************************************************************/
function ShowComments ($iVideoID, $iCommentsShow, $bOwner, $bMuted,
	$iUserID, $sCode, $iHighlightID, $iParentID)
/*****************************************************************************/
{
	$sReturn = '';

	$query_comment = "SELECT
			fc.comment_id,
			fc.user_id,
			fu.user_username,
			fc.comment_text,
			fc.comment_loved,
			fc.comment_pinned,
			fc.comment_hidden,
			fc.comment_approved,
			fc.comment_adddate,
			fu.user_patron
		FROM `fst_comment` fc
		LEFT JOIN `fst_user` fu
			ON fc.user_id = fu.user_id
		WHERE (video_id='" . $iVideoID . "')
		AND (comment_parent_id='" . $iParentID . "')
		ORDER BY comment_pinned DESC, comment_adddate DESC";
	$result_comment = Query ($query_comment);
	while ($row_comment = mysqli_fetch_assoc ($result_comment))
	{
		if ((isset ($_SESSION['fst']['user_id'])) &&
			($_SESSION['fst']['user_id'] == $row_comment['user_id']))
			{ $bAuthor = TRUE; } else { $bAuthor = FALSE; }

		if (($iCommentsShow == 1) ||
			(($iCommentsShow == 2) && ($row_comment['comment_approved'] == 1)) ||
			($bOwner === TRUE) ||
			($bAuthor === TRUE))
		{
			if (($row_comment['comment_hidden'] == '0') ||
				(HasNonhiddenReplies ($row_comment['comment_id']) === TRUE))
			{
				$sReturn .= ShowComment ($iVideoID, $iCommentsShow, $bOwner, $bMuted,
					$iUserID, $sCode, $iHighlightID, $iParentID, $row_comment, $bAuthor);
			}
		}
	}

	return ($sReturn);
}
/*****************************************************************************/
function UpdateLastViewed ($iVideoID, $iUserID)
/*****************************************************************************/
{
	if ($iUserID != 0)
	{
		$sDTNow = date ('Y-m-d H:i:s');
		$query_lastviewed = "INSERT INTO `fst_commentslastviewed` SET
				video_id='" . $iVideoID . "',
				user_id='" . $iUserID . "',
				commentslastviewed_dt='" . $sDTNow . "'
			ON DUPLICATE KEY UPDATE
				commentslastviewed_dt='" . $sDTNow . "'";
		Query ($query_lastviewed);
	}
}
/*****************************************************************************/

if ((isset ($_POST['code'])) &&
	(isset ($_POST['comment'])))
{
	$sCode = $_POST['code'];
	$iVideoID = CodeToID ($sCode);
	$iHighlightID = intval ($_POST['comment']);

	$query_video = "SELECT
			user_id,
			video_comments_allow,
			video_comments_show,
			video_istext
		FROM `fst_video`
		WHERE (video_id='" . $iVideoID . "')";
	$result_video = Query ($query_video);
	if (mysqli_num_rows ($result_video) == 1)
	{
		$row_video = mysqli_fetch_assoc ($result_video);
		if (isset ($_SESSION['fst']['user_id']))
			{ $iUserID = $_SESSION['fst']['user_id']; } else { $iUserID = 0; }
		if ($iUserID == $row_video['user_id'])
			{ $bOwner = TRUE; } else { $bOwner = FALSE; }
		$iCommentsAllow = intval ($row_video['video_comments_allow']);
		$iCommentsShow = intval ($row_video['video_comments_show']);
		$iIsText = intval ($row_video['video_istext']);

		if ($iCommentsAllow == 0)
		{
			if ($iIsText != 3)
			{
$sHTML = '
<span style="display:block; margin:30px 0; font-style:italic;">
Publisher disabled comments for this content.
</span>
';
			} else {
$sHTML = '
<span style="display:block; margin:30px 0; font-style:italic;">
A moderator has locked this topic.
</span>
';
				$sHTML .= ShowComments ($iVideoID, $iCommentsShow, $bOwner, TRUE,
					$iUserID, $sCode, $iHighlightID, 0);
				UpdateLastViewed ($iVideoID, $iUserID);
			}
			$arResult['result'] = 1;
			$arResult['error'] = '';
			$arResult['html'] = $sHTML;
		} else {
			$sHTML = '';
			$bMuted = FALSE;
			if ($iCommentsShow == 2)
			{
				if (!isset ($_SESSION['fst']['user_id']))
				{
					$sHTML .= 'Publisher has to approve comments.';
				} else {
$sHTML .= '
<span style="display:block; margin-top:10px; color:#414dc5; font-size:16px;">
The publisher of this content has chosen to only display comments after approving them.
<br>
Once approved by the publisher (the check mark will be green), visitors will see your comment.
</span>
';
				}
			}
			if ((isset ($_SESSION['fst']['user_id'])) && (!IsMod()))
			{
				if (IsMuted ($row_video['user_id'],
					$_SESSION['fst']['user_id']) === TRUE)
				{
$sHTML .= '
<span style="display:block; margin:30px 0; font-style:italic;">
This publisher has muted you.
</span>
';
					$bMuted = TRUE;
				} else {
$sHTML .= '
<div id="reply-0" style="margin:10px 0;">
<div id="comment">
<textarea id="comment_text" style="display:block; width:600px; max-width:100%;"></textarea>
<div id="comment-error" style="color:#f00; margin-top:10px;"></div>
<input type="button" id="comment_add" value="Comment">
</div>
</div>
<script>
$("#comment_add").click(function(){
	$("#comment_add").prop("value","Wait...");
	$("#comment_add").prop("disabled",true).css("opacity","0.5");
	var comment_text = $("#comment_text").val();
	var parent_id = $(this).parent("div").parent("div").attr("id").replace("reply-","");

	$.ajax({
		type: "POST",
		url: "/v/comment_add.php",
		data: ({
			code : "' . $sCode . '",
			comment_text : comment_text,
			parent_id : parent_id,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			var html = data["html"];
			if (result == 1)
			{
				Comments (0);
			} else {
				$("#comment_add").prop("value","Comment");
				$("#comment_add").removeAttr("disabled").css("opacity","1");
				$("#comment-error").html(error);
			}
		},
		error: function() {
			$("#comment_add").prop("value","Comment");
			$("#comment_add").removeAttr("disabled").css("opacity","1");
			$("#comment-error").html("Error calling comment_add.php.");
		}
	});
});
</script>
';
				}
			}

			if (!isset ($_SESSION['fst']['user_id']))
			{
$sHTML .= '
<span style="display:block; margin:30px 0; font-style:italic;">
Sign in to comment.
</span>
';
			} else if (IsMod() === TRUE) {
$sHTML .= '
<span style="display:block; margin:30px 0; font-style:italic;">
Moderator accounts can not add comments.
</span>
';
			}

			$sHTML .= ShowComments ($iVideoID, $iCommentsShow, $bOwner, $bMuted,
				$iUserID, $sCode, $iHighlightID, 0);
			UpdateLastViewed ($iVideoID, $iUserID);

			$arResult['result'] = 1;
			$arResult['error'] = '';
			$arResult['html'] = $sHTML;
		}
	} else {
		$arResult['result'] = 0;
		$arResult['error'] = 'Unknown video.';
		$arResult['html'] = '';
	}
} else {
	$arResult['result'] = 0;
	$arResult['error'] = 'Code is missing.';
	$arResult['html'] = '';
}
print (json_encode ($arResult));
?>
