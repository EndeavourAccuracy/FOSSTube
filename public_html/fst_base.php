<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.0 (February 2020)
 * Copyright (C) 2020 Norbert de Jonge <mail@norbertdejonge.nl>
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

$sPrivate = 'private';

error_reporting (-1);
ini_set ('display_errors', 'On');

date_default_timezone_set ('UTC');

if (function_exists ('mb_internal_encoding') === FALSE)
{
	print ('Install mbstring.'); exit();
} else {
	ini_set ('php.internal_encoding', 'UTF-8');
	mb_internal_encoding ('UTF-8');
}

ini_set ('output_buffering', 'Off');
while (@ob_end_flush());

set_time_limit (0);
ini_set ('max_execution_time', 0);

include_once (dirname (__FILE__) . '/../' . $sPrivate . '/fst_settings.php');

/*** CPU load too high? ***/
/***
$arLoad = sys_getloadavg();
if (($arLoad[0] / $GLOBALS['total_cpu_cores']) > $GLOBALS['max_cpu_load'])
{
	$sProtocol = 'HTTP/1.1';
	if (isset ($_SERVER['SERVER_PROTOCOL']))
		{ $sProtocol = $_SERVER['SERVER_PROTOCOL']; }
	header ($sProtocol . ' 503 Service Unavailable');
	die ('Temporarily down for maintenance. Try again later.');
}
***/

include_once (dirname (__FILE__) . '/fst_both.php');

/*****************************************************************************/
function MenuStart ($sMenuName)
/*****************************************************************************/
{
	if (($sMenuName == 'Account') &&
		(isset ($_SESSION['fst']['user_id'])))
	{
		$sUsername = $_SESSION['fst']['user_username'];
		$sMenuNameShow = GetUserAvatar ($sUsername, 'small', 2) . ' ' . $sUsername;
	} else { $sMenuNameShow = $sMenuName; }

	print ('<li class="dropdown');
	if ($sMenuName == $GLOBALS['menu_name'])
	{
		print (' active');
	}
	print ('"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">' . $sMenuNameShow . ' <span class="caret"></span></a><ul class="dropdown-menu scrollable-menu">');
}
/*****************************************************************************/
function ShowLi ($sLink, $sText)
/*****************************************************************************/
{
	print ('<li');
	if ($sText == $GLOBALS['menu_item'])
	{
		print (' class="active"');
	}
	print ('><a href="' . $sLink . '">' . $sText . '</a></li>' . "\n");
}
/*****************************************************************************/
function Nav ()
/*****************************************************************************/
{
	if ($GLOBALS['menu_item'] == 'Home')
	{
		$sHomeH1S = '<h1 class="h1h">';
		$sHomeH1E = '</h1>';
		$sSiteName = $GLOBALS['name'] . ' · ' . $GLOBALS['name_seo_alternative'];
	} else { $sHomeH1S = ''; $sHomeH1E = ''; $sSiteName = $GLOBALS['name']; }

print ('
<nav class="navbar navbar-default">
<div class="container-fluid div-navbar">
<div class="navbar-header">
<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
<span class="sr-only">Toggle navigation</span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
</button>
' . $sHomeH1S . '<a href="/"><img src="/images/' . $GLOBALS['header_image_name'] . '" alt="' . $sSiteName . '" title="' . $sSiteName . '" style="max-width:100%;"></a>' . $sHomeH1E . '
</div>
<div id="navbar" class="navbar-collapse collapse">
<ul class="nav navbar-nav">
');

ShowLi ('/', 'Home');

/*** About ***/
MenuStart ('About');
ShowLi ('/about/', 'About');
if (isset ($_SESSION['fst']['user_id'])) { ShowLi ('/faq/', 'FAQ'); }
print ('<li role="separator" class="divider"></li>');
ShowLi ('/terms/', 'Terms of service');
ShowLi ('/privacy/', 'Privacy policy');
print ('<li role="separator" class="divider"></li>');
ShowLi ('/contact.php', 'Contact');
print ('</ul></li>' . "\n");

ShowLi ('/forum/', 'Forum');

/*** Account ***/
if (isset ($_SESSION['fst']['user_id']))
{
	MenuStart ('Account');
	if (MayAdd ('videos') === TRUE)
	{
		ShowLi ('/videos/', 'Videos');
		ShowLi ('/upload/', 'Upload');
	}
	if (MayAdd ('texts') === TRUE)
	{
		ShowLi ('/text/', 'Text');
	}
	ShowLi ('/folders/', 'Folders');
	ShowLi ('/account/', 'Account');
	print ('<li role="separator" class="divider"></li>');
	ShowLi ('/signout/', 'Sign out');
	print ('</ul></li>' . "\n");
} else {
	MenuStart ('Account');
	ShowLi ('/signup/', 'Create account');
	ShowLi ('/signin/', 'Sign in');
	print ('</ul></li>' . "\n");
}

$iNrNotifications = NrNotifications();
if ($iNrNotifications != 0)
{
	print ('<li><a href="/notifications/" style="background-color:#008000; color:#fff!important;">' . strval ($iNrNotifications) . '</a></li>' . "\n");
}

if (IsAdmin()) { ShowLi ('/admin/', 'Admin'); }
if (IsAdmin()) { ShowLi ('/purge/', 'Purge'); }
if (IsMod()) { ShowLi ('/mod/', 'Mod'); }

if ($_SESSION['fst']['theme'] == 'day')
	{ $sOther = 'night'; } else { $sOther = 'day'; }

print ('
</ul>
<ul class="nav navbar-nav navbar-right">
<li><a href="javascript:;" id="switch" style="padding:10px;"><img src="/images/theme/' . $sOther . '.png" id="switch-img" alt="' . $sOther . '"></a></li>
</ul>

</div>
</div>
</nav>
');
}
/*****************************************************************************/
function HTMLStart ($sTitle, $sMenuName, $sMenuItem, $xData, $bEmbed)
/*****************************************************************************/
{
	/*** $sFeed, $iVideoID ***/
	$sFeed = '';
	if (is_array ($xData) === FALSE)
	{
		$iVideoID = $xData;
		if ($sTitle == 'Home') { $sFeed = '/xml/feed.php'; }
	} else {
		$iVideoID = $xData['video_id'];
		$iUserID = $xData['user_id'];
		$sUsername = GetUserInfo ($iUserID, 'user_username');
		$sFeed = '/xml/feed.php?user=' . $sUsername;
	}

	if ($bEmbed === TRUE) { $sEmbedHTML = ' style="overflow-y:auto;"'; }
		else { $sEmbedHTML = ''; }
	if ($sMenuItem != 'Home')
	{
		$sHTMLTitle = Sanitize ($sTitle) . ' · ' . $GLOBALS['name'];
	} else {
		$sHTMLTitle = $GLOBALS['name'] . ' · ' . $GLOBALS['name_seo_alternative'];
	}

	/*** $sLang ***/
	$sLang = 'en';
	if ($iVideoID != 0)
	{
		$query_lang = "SELECT
				fl.`language_iso639-1`
			FROM `fst_video` fv
			LEFT JOIN `fst_language` fl
				ON fv.language_id = fl.language_id
			WHERE (video_id='" . $iVideoID . "')";
		$result_lang = Query ($query_lang);
		if (mysqli_num_rows ($result_lang) != 0)
		{
			$row_lang = mysqli_fetch_assoc ($result_lang);
			if ($row_lang['language_iso639-1'] != NULL)
				{ $sLang = $row_lang['language_iso639-1']; }
		}
	}

print ('
<!DOCTYPE html>
<html lang="' . $sLang . '"' . $sEmbedHTML . '>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Always start with the above 3 meta tags. -->
');

	if ($sMenuItem == 'Home')
	{
		print ('<meta name="description" content="' .
			$GLOBALS['short_description'] . '">' . "\n");
	}

	/*** Open Graph and Twitter Cards ***/
	if ($iVideoID != 0)
	{
		$query_meta = "SELECT
				DATE_FORMAT(video_adddate,'%Y-%m-%d') AS date,
				video_seconds AS secs,
				SUBSTRING(REPLACE(video_title,'\n',' '),1,70) AS title,
				IF(video_description<>'',SUBSTRING(REPLACE(video_description,'\n',' '),1,200),SUBSTRING(REPLACE(video_text,'\n',' '),1,200)) AS descr,
				video_thumbnail AS thumb,
				(video_360_width * 2) AS width,
				(video_360_height * 2) AS height,
				video_istext
			FROM `fst_video`
			WHERE (video_id='" . $iVideoID . "')";
		$result_meta = Query ($query_meta);
		$row_meta = mysqli_fetch_assoc ($result_meta);

		$sOGDate = $row_meta['date'];
		$iOGSecs = intval ($row_meta['secs']);
		$sOGTitle = Sanitize ($row_meta['title']);
		$sOGDescr = Sanitize ($row_meta['descr']);
		$sOGThumb = ThumbURL (IDToCode ($iVideoID), '720',
			$row_meta['thumb'], TRUE);
		$iOGWidth = intval ($row_meta['width']);
		$iOGHeight = intval ($row_meta['height']);
		$iIsText = intval ($row_meta['video_istext']);
		if ($iIsText == 0)
		{
			$sOGType = 'video.other';
			$sOGPropDate = 'video:release_date';
		} else {
			$sOGType = 'article';
			$sOGPropDate = 'article:published_time';
		}

print ('
<!-- Open Graph and Twitter Cards -->
<meta property="og:type" content="' . $sOGType . '">
<meta name="twitter:card" content="summary_large_image">
<meta property="' . $sOGPropDate . '" content="' . $sOGDate . '">
');
		if ($iOGSecs != 0)
		{
print ('<meta property="video:duration" content="' . $iOGSecs . '">
');
		}
print ('<meta property="og:site_name" content="' . $GLOBALS['name'] . '">
<meta name="twitter:site" content="@fstube">
<meta property="og:title" content="' . $sOGTitle . '">
<meta name="twitter:title" content="' . $sOGTitle . '">
<meta property="og:description" content="' . $sOGDescr . '">
<meta name="twitter:description" content="' . $sOGDescr . '">
<meta property="og:image" content="' . $GLOBALS['protocol'] .
	'://www.' . $GLOBALS['domain'] . $sOGThumb . '">
<meta name="twitter:image" content="' . $GLOBALS['protocol'] .
	'://www.' . $GLOBALS['domain'] . $sOGThumb . '">
<meta property="og:image:type" content="image/jpeg">
');
		if (($iOGWidth != 0) && ($iOGHeight != 0))
		{
print ('<meta property="og:image:width" content="' . $iOGWidth . '">
<meta property="og:image:height" content="' . $iOGHeight . '">
');
		}
print ('<!-- Open Graph and Twitter Cards -->
');
	}

print ('
<link rel="icon" href="/images/favicons/favicon-016.png" sizes="16x16" type="image/png">
<link rel="icon" href="/images/favicons/favicon-032.png" sizes="32x32" type="image/png">
<link rel="icon" href="/images/favicons/favicon-057.png" sizes="57x57" type="image/png">
<link rel="icon" href="/images/favicons/favicon-076.png" sizes="76x76" type="image/png">
<link rel="icon" href="/images/favicons/favicon-096.png" sizes="96x96" type="image/png">
<link rel="icon" href="/images/favicons/favicon-120.png" sizes="120x120" type="image/png">
<link rel="icon" href="/images/favicons/favicon-128.png" sizes="128x128" type="image/png">
<link rel="icon" href="/images/favicons/favicon-144.png" sizes="144x144" type="image/png">
<link rel="icon" href="/images/favicons/favicon-152.png" sizes="152x152" type="image/png">
<link rel="icon" href="/images/favicons/favicon-167.png" sizes="167x167" type="image/png">
<link rel="icon" href="/images/favicons/favicon-180.png" sizes="180x180" type="image/png">
<link rel="icon" href="/images/favicons/favicon-195.png" sizes="195x195" type="image/png">
<link rel="icon" href="/images/favicons/favicon-196.png" sizes="196x196" type="image/png">
<link rel="icon" href="/images/favicons/favicon-228.png" sizes="228x228" type="image/png">
<link rel="apple-touch-icon" href="/images/favicons/apple-touch-icon.png">
');

if ($sFeed != '')
{
print ('
<link rel="alternate" type="application/rss+xml" title="New content" href="' . $sFeed . '">
');
}

print ('
<title>' . $sHTMLTitle . '</title>

<!-- JS -->
<script src="/js/jquery-3.3.1.min.js"></script>
<script src="/bootstrap/js/bootstrap.min.js"></script>
<script src="/js/ie10-viewport-bug-workaround.js"></script>
<!--[if lt IE 9]>
<script src="/js/html5shiv.min.js"></script>
<script src="/js/respond.min.js"></script>
<![endif]-->
<script src="/js/wNumb.min.js"></script>
<script src="/js/nouislider.min.js"></script>
<script src="/js/fst.js?v=28"></script>

<!-- CSS -->
<link rel="stylesheet" type="text/css" href="/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="/css/nouislider.min.css">
<link rel="stylesheet" type="text/css" href="/css/fst.css?v=35">
');

if (!isset ($_SESSION['fst']['theme']))
	{ $_SESSION['fst']['theme'] = $GLOBALS['default_theme']; }
if ($_SESSION['fst']['theme'] == 'day')
{
	print ('<link rel="stylesheet" type="text/css" href="/css/fst_day.css?v=12" id="theme" data-theme="day">' . "\n");
} else {
	print ('<link rel="stylesheet" type="text/css" href="/css/fst_night.css?v=12" id="theme" data-theme="night">' . "\n");
}

print ('
</head>
');

	if ($bEmbed === FALSE)
	{
print ('
<body>
<div class="container" style="height:calc(100% - 71px);">
');
		$GLOBALS['menu_name'] = $sMenuName;
		$GLOBALS['menu_item'] = $sMenuItem;
		Nav();
print ('
<div class="container-fluid div-main">
<div class="row" id="content" style="margin:5px;">
');
	} else {
print ('
<body style="background-image:none;">
<div class="container" style="height:100%;">
');
print ('
<div class="container-fluid" style="padding:0;">
<div class="row" id="content" style="margin:0!important;">
');
	}
}
/*****************************************************************************/
function HTMLEnd ()
/*****************************************************************************/
{
print ('
</div>
</div>

<div id="footer">
&copy; ' . date ('Y') . ' ' . $GLOBALS['name_copyright'] . '
&nbsp;|&nbsp;
<a target="_blank" href="https://validator.w3.org/check?uri=referer"><img src="/images/W3C_HTML5.png" alt="W3C HTML5"></a>
</div>

</div>

</body>
</html>
');
}
/*****************************************************************************/
function IsEmail ($sEmail)
/*****************************************************************************/
{
	if (filter_var ($sEmail, FILTER_VALIDATE_EMAIL) !== FALSE)
	{
/***
		$sDomain = substr ($sEmail, strpos ($sEmail, '@'));
		if (checkdnsrr ($sDomain) !== FALSE)
		{
			return (TRUE);
		} else {
			return (FALSE);
		}
***/
return (TRUE);
	} else {
		return (FALSE);
	}
}
/*****************************************************************************/
function FixString ($sString)
/*****************************************************************************/
{
	$sReturn = htmlspecialchars ($sString, ENT_QUOTES);
	return ($sReturn);
}
/*****************************************************************************/
function StartSession ()
/*****************************************************************************/
{
	/*** Start the session. ***/
	if (session_status() !== PHP_SESSION_ACTIVE)
	{
		ini_set ('session.use_trans_sid', 0);
		session_name ('fst');
		if (session_start() === FALSE) { exit ('Session error.'); }
	}

	/*** Generate a CSRF token, if necessary. ***/
	if (!isset ($_SESSION['fst']['csrf_token']))
	{
		$_SESSION['fst']['csrf_token'] = bin2hex (random_bytes (32));
	}
}
/*****************************************************************************/
function HasRequired ()
/*****************************************************************************/
{
	$arHas = array();

	if (get_extension_funcs ('gd') === FALSE)
	{
		array_push ($arHas, 'GD is missing');
	} else {
		$arGDInfo = gd_info();
		/*** Some GD releases use "bundled (2.1.0 compatible)". ***/
		$arGDVersion = preg_match ('/(\d+(?:\.\d+)*)/',
			$arGDInfo['GD Version'], $arMatch);
		$sGDVersion = $arMatch[0];
		if (version_compare ($sGDVersion, '2.0', '>=') !== TRUE)
			{ array_push ($arHas, 'GD is too old'); }
	}

	if (!empty ($arHas))
	{
		print ('Problem(s):' . '<br>');
		foreach ($arHas AS $sHas)
		{
			print ($sHas . '<br>');
		}
		exit();
	}
}
/*****************************************************************************/
function VerifyCreate ()
/*****************************************************************************/
{
	$_SESSION['fst']['value1'] = rand (10, 99);
	$_SESSION['fst']['operator1'] = rand (1, 2);
	$_SESSION['fst']['value2'] = rand (10, 99);
	$_SESSION['fst']['operator2'] = rand (1, 2);
	$_SESSION['fst']['value3'] = rand (10, 99);
}
/*****************************************************************************/
function VerifyShow ()
/*****************************************************************************/
{
	if (!isset ($_SESSION['fst']['value1'])) { VerifyCreate(); }

	$sVerify = '';

	$sVerify .= $_SESSION['fst']['value1'];
	switch ($_SESSION['fst']['operator1'])
	{
		case 1: $sVerify .= ' + '; break;
		case 2: $sVerify .= ' - '; break;
	}
	$sVerify .= $_SESSION['fst']['value2'];
	switch ($_SESSION['fst']['operator2'])
	{
		case 1: $sVerify .= ' + '; break;
		case 2: $sVerify .= ' - '; break;
	}
	$sVerify .= $_SESSION['fst']['value3'];

	return ($sVerify);
}
/*****************************************************************************/
function VerifyAnswer ()
/*****************************************************************************/
{
	if (!isset ($_SESSION['fst']['value1'])) { VerifyCreate(); }

	$value1 = $_SESSION['fst']['value1'];
	$value2 = $_SESSION['fst']['value2'];
	$value3 = $_SESSION['fst']['value3'];

	$iAnswer = $value1;
	switch ($_SESSION['fst']['operator1'])
	{
		case 1: $iAnswer = $iAnswer + $value2; break;
		case 2: $iAnswer = $iAnswer - $value2; break;
	}
	switch ($_SESSION['fst']['operator2'])
	{
		case 1: $iAnswer = $iAnswer + $value3; break;
		case 2: $iAnswer = $iAnswer - $value3; break;
	}

	return ($iAnswer);
}
/*****************************************************************************/
function MySQLConnect ()
/*****************************************************************************/
{
	include_once (dirname (__FILE__) . '/../' .
		$GLOBALS['private'] . '/fst_db.php');

	if ($GLOBALS['link'] === FALSE)
	{
		$GLOBALS['link'] = mysqli_connect ($GLOBALS['db_host'],
			$GLOBALS['db_user'], $GLOBALS['db_pass'], $GLOBALS['db_dtbs']);
		if ($GLOBALS['link'] === FALSE)
		{
			print ('The database appears to be down.');
			exit();
		}

		if (mysqli_set_charset ($GLOBALS['link'], 'utf8mb4') === FALSE)
		{
			print ('Cannot set the database charset.');
			exit();
		}
	}
}
/*****************************************************************************/
function Query ($query)
/*****************************************************************************/
{
	$result = mysqli_query ($GLOBALS['link'], $query);
	if ($result === FALSE)
	{
		print ('Query failed.' . '<br>');
		print ('Query: ' . $query . '<br>');
		print ('Error: ' . mysqli_error ($GLOBALS['link']) . '<br>');
		exit();
	}

	return ($result);
}
/*****************************************************************************/
function GetIP ()
/*****************************************************************************/
{
	$arServer = array (
		'HTTP_CLIENT_IP',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_FORWARDED',
		'HTTP_X_CLUSTER_CLIENT_IP',
		'HTTP_FORWARDED_FOR',
		'HTTP_FORWARDED',
		'REMOTE_ADDR'
	);
	foreach ($arServer as $sServer)
	{
		if (array_key_exists ($sServer, $_SERVER) === TRUE)
		{
			foreach (explode (',', $_SERVER[$sServer]) as $sIP)
			{
				if (filter_var ($sIP, FILTER_VALIDATE_IP) !== FALSE)
					{ return ($sIP); }
			}
		} 
	} 
	return ('unknown');
}
/*****************************************************************************/
function ValidateChars ($sString)
/*****************************************************************************/
{
	/*** Used for the username and password. ***/

	for ($iPos = 0; $iPos < strlen ($sString); $iPos++)
	{
		$cChar = $sString[$iPos];
		if ((($cChar >= 'a') && ($cChar <= 'z')) ||
			(($cChar >= 'A') && ($cChar <= 'Z')) ||
			(($cChar >= '0') && ($cChar <= '9')) ||
			($cChar == '-') ||
			($cChar == '_'))
			{ } else { return ($cChar); }
	}

	return (FALSE);
}
/*****************************************************************************/
function GetUserID ($sUsername)
/*****************************************************************************/
{
	$query_id = "SELECT
			user_id
		FROM `fst_user`
		WHERE (user_username='" . $sUsername . "')";
	$result_id = Query ($query_id);
	if (mysqli_num_rows ($result_id) == 1)
	{
		$row_id = mysqli_fetch_assoc ($result_id);
		return ($row_id['user_id']);
	} else {
		print ('Unknown username.');
		exit();
	}
}
/*****************************************************************************/
function GetUserAvatar ($sUsername, $sSize, $iStyle)
/*****************************************************************************/
{
	if (($sSize != 'small') && ($sSize != 'large')) { $sSize = 'small'; }
	$sCustom = '/avatars/' . $sUsername . '_' . $sSize . '.png';
	if (file_exists (dirname (__FILE__) . $sCustom))
	{
		$sURL = $sCustom;
	} else {
		$sURL = '/images/avatar_' . $sSize . '.png';
	}
	$sReturn = '<img src="' . $sURL . '" alt="' . $sUsername . '"';
	if ($iStyle == 1) { $sReturn .= ' style="border:1px solid #414dc5;"'; }
	if ($iStyle == 2) { $sReturn .= ' style="border:1px solid #414dc5; height:18px; vertical-align:top;"'; }
	$sReturn .= '>';

	return ($sReturn);
}
/*****************************************************************************/
function GetSizeBytes ($sString)
/*****************************************************************************/
{
	switch (substr ($sString, -1))
	{
		case 'G': case 'g':
			$iBytes = (intval ($sString)) * 1024 * 1024 * 1024;
			break;
		case 'M': case 'm':
			$iBytes = (intval ($sString)) * 1024 * 1024;
			break;
		case 'K': case 'k':
			$iBytes = (intval ($sString)) * 1024;
			break;
	}

	return ($iBytes);
}
/*****************************************************************************/
function GetSizeHuman ($iBytes)
/*****************************************************************************/
{
	if ($iBytes === NULL) { return ('nothing'); }

	$iG = 1024 * 1024 * 1024;
	$iM = 1024 * 1024;
	$iK = 1024;
	if ($iBytes >= $iG)
	{
		$sHuman = str_replace ('.00', '', number_format ($iBytes / $iG, 2)) . 'G';
	} else if ($iBytes >= $iM) {
		$sHuman = str_replace ('.00', '', number_format ($iBytes / $iM, 2)) . 'M';
	} else if ($iBytes >= $iK) {
		$sHuman = str_replace ('.00', '', number_format ($iBytes / $iK, 2)) . 'K';
	} else {
		$sHuman = $iBytes . ' B';
	}

	return ($sHuman);
}
/*****************************************************************************/
function AllowedBytes ()
/*****************************************************************************/
{
	$iLimit = 1024 * 1024 * 1024 * 1024; /*** T ***/
	$iFileSize = GetSizeBytes ($GLOBALS['max_file_size']);
	if ($iFileSize < $iLimit) { $iLimit = $iFileSize; }
	$iUploadSize = GetSizeBytes (ini_get ('upload_max_filesize'));
	if ($iUploadSize < $iLimit) { $iLimit = $iUploadSize; }
	$iPostSize = GetSizeBytes (ini_get ('post_max_size'));
	if ($iPostSize < $iLimit) { $iLimit = $iPostSize; }

	return ($iLimit);
}
/*****************************************************************************/
function IDToCode ($iID)
/*****************************************************************************/
{
	$sCode = base_convert (46655 + $iID, 10, 36);
	$arSearch = array ('a', 'e', 'i', 'o', 'u', 'y');
	$arReplace = array ('B', 'F', 'J', 'P', 'V', 'Z');
	$sCode = str_replace ($arSearch, $arReplace, $sCode);
	return ($sCode);
}
/*****************************************************************************/
function CodeToID ($sCode)
/*****************************************************************************/
{
	$arValid = array ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'B', 'b', 'c', 'd', 'F', 'f', 'g', 'h', 'J', 'j', 'k', 'l', 'm', 'n', 'P', 'p', 'q', 'r', 's', 't', 'V', 'v', 'w', 'x', 'Z', 'z');
	for ($iLoopCode = 0; $iLoopCode < strlen ($sCode); $iLoopCode++)
	{
		if (in_array ($sCode[$iLoopCode], $arValid) === FALSE)
			{ return (0); }
	}

	$arSearch = array ('B', 'F', 'J', 'P', 'V', 'Z');
	$arReplace = array ('a', 'e', 'i', 'o', 'u', 'y');
	$sCode = str_replace ($arSearch, $arReplace, $sCode);
	$iID = intval ($sCode, 36) - 46655;
	return ($iID);
}
/*****************************************************************************/
function LinkBack ($sPath, $sText)
/*****************************************************************************/
{
	print ('<span style="display:block; margin-bottom:10px;">');
	print ('<a href="' . $sPath . '">&laquo; ' . $sText . '</a>');
	print ('</span>');
}
/*****************************************************************************/
function IsAdmin ()
/*****************************************************************************/
{
	if ((isset ($_SESSION['fst']['user_username'])) &&
		(in_array ($_SESSION['fst']['user_username'],
			$GLOBALS['admins']) === TRUE))
	{
		return (TRUE);
	} else {
		return (FALSE);
	}
}
/*****************************************************************************/
function IsMod ()
/*****************************************************************************/
{
	/*** All admins are automatically also mods. ***/
	if (IsAdmin()) { return (TRUE); }

	if ((isset ($_SESSION['fst']['user_username'])) &&
		(in_array ($_SESSION['fst']['user_username'],
			$GLOBALS['mods']) === TRUE))
	{
		return (TRUE);
	} else {
		return (FALSE);
	}
}
/*****************************************************************************/
function ThumbURL ($sCode, $sHeight, $iThumb, $bCheckExists)
/*****************************************************************************/
{
	/* $iThumb is 1, 2, 3, 4, 5 (all regular) or 6 (custom).
	 * All videos, regardless their dimensions, have at least thumbnails 1-5 at
	 * both 180p and 720p.
	 */

	$sURL = '/jpg/';
	$sURL .= $sCode[-1];
	$sURL .= '/';
	$sURL .= $sCode[-2];
	$sURL .= '/';
	$sURL .= $sCode;
	$sURL .= '_';
	$sURL .= $sHeight;
	$sURL .= '_';
	$sURL .= $iThumb;
	$sURL .= '.jpg';

	if (($bCheckExists === FALSE) ||
		(file_exists (dirname (__FILE__) . $sURL) === TRUE))
	{
		return ($sURL);
	} else {
		return ('/images/thumbnail_' . $sHeight . '.jpg');
	}
}
/*****************************************************************************/
function Processing ()
/*****************************************************************************/
{
	$sReturn = '';

	$sReturn .= '<span class="processing">';
	$sReturn .= 'Still being processed.';
	$sReturn .= '</span>';

	return ($sReturn);
}
/*****************************************************************************/
function VideoTime ($iSeconds)
/*****************************************************************************/
{
	if ($iSeconds >= 3600)
	{
		$sTime = gmdate ('H:i:s', $iSeconds);
	} else {
		$sTime = gmdate ('i:s', $iSeconds);
	}
	$sTime = ltrim ($sTime, '0');
	if ($sTime[0] == ':') { $sTime = '0' . $sTime; }

	return ($sTime);
}
/*****************************************************************************/
function LikesVideo ($iVideoID)
/*****************************************************************************/
{
	$query_likes = "SELECT
			COUNT(*) AS likes
		FROM `fst_likevideo`
		WHERE (video_id='" . $iVideoID . "')";
	$result_likes = Query ($query_likes);
	$row_likes = mysqli_fetch_assoc ($result_likes);
	$iLikes = $row_likes['likes'];

	return ($iLikes);
}
/*****************************************************************************/
function LikesComment ($iCommentID)
/*****************************************************************************/
{
	$query_likes = "SELECT
			COUNT(*) AS likes
		FROM `fst_likecomment`
		WHERE (comment_id='" . $iCommentID . "')";
	$result_likes = Query ($query_likes);
	$row_likes = mysqli_fetch_assoc ($result_likes);
	$iLikes = $row_likes['likes'];

	return ($iLikes);
}
/*****************************************************************************/
function VideoExists ($sCode)
/*****************************************************************************/
{
	/*** Returns an array or FALSE. ***/

	$iID = CodeToID ($sCode);
	$query_video = "SELECT
			video_id,
			user_id,
			video_title,
			video_thumbnail
		FROM `fst_video`
		WHERE (video_id='" . $iID . "')
		AND (video_deleted='0')";
	$result_video = Query ($query_video);
	if (mysqli_num_rows ($result_video) == 1)
	{
		$row_video = mysqli_fetch_assoc ($result_video);
		$arVideo['id'] = $row_video['video_id'];
		$arVideo['user_id'] = $row_video['user_id'];
		$arVideo['title'] = $row_video['video_title'];
		$arVideo['thumbnail'] = $row_video['video_thumbnail'];
	} else {
		$arVideo = FALSE;
	}

	return ($arVideo);
}
/*****************************************************************************/
function CommentExists ($iCommentID, $iVideoID)
/*****************************************************************************/
{
	/* Returns an array or FALSE.
	 * Hidden comments also return FALSE.
	 */

	$iID = intval ($iCommentID);
	if ($iVideoID != 0)
		{ $sVideoID = "AND (video_id='" . $iVideoID . "')"; }
			else { $sVideoID = ""; }

	$query_comment = "SELECT
			comment_id,
			user_id,
			comment_text
		FROM `fst_comment`
		WHERE (comment_id='" . $iID . "')
		" . $sVideoID . "
		AND (comment_hidden='0')";
	$result_comment = Query ($query_comment);
	if (mysqli_num_rows ($result_comment) == 1)
	{
		$row_comment = mysqli_fetch_assoc ($result_comment);
		$arComment['id'] = $row_comment['comment_id'];
		$arComment['user_id'] = $row_comment['user_id'];
		$arComment['text'] = $row_comment['comment_text'];
	} else {
		$arComment = FALSE;
	}

	return ($arComment);
}
/*****************************************************************************/
function UserExists ($sUsername)
/*****************************************************************************/
{
	/*** Returns an array or FALSE. ***/

	$query_user = "SELECT
			user_id,
			user_username,
			user_pref_nsfw
		FROM `fst_user`
		WHERE (user_username='" . mysqli_real_escape_string
			($GLOBALS['link'], $sUsername) . "')
		AND (user_deleted='0')";
	$result_user = Query ($query_user);
	if (mysqli_num_rows ($result_user) == 1)
	{
		$row_user = mysqli_fetch_assoc ($result_user);
		$arUser['id'] = intval ($row_user['user_id']);
		$arUser['username'] = $row_user['user_username'];
		$arUser['pref_nsfw'] = intval ($row_user['user_pref_nsfw']);
	} else {
		$arUser = FALSE;
	}

	return ($arUser);
}
/*****************************************************************************/
function IssueExists ($sNameShort)
/*****************************************************************************/
{
	/*** Returns an array or FALSE. ***/

	$query_issue = "SELECT
			issue_id,
			issue_name_long
		FROM `fst_issue`
		WHERE (issue_name_short='" . mysqli_real_escape_string
			($GLOBALS['link'], $sNameShort) . "')";
	$result_issue = Query ($query_issue);
	if (mysqli_num_rows ($result_issue) == 1)
	{
		$row_issue = mysqli_fetch_assoc ($result_issue);
		$arIssue['id'] = $row_issue['issue_id'];
		$arIssue['name_long'] = $row_issue['issue_name_long'];
	} else {
		$arIssue = FALSE;
	}

	return ($arIssue);
}
/*****************************************************************************/
function Videos ($sDivID, $sSection, $sWhere, $sOrder, $iLimit, $iOffset,
	$sUsername, $sSearchT, $sSearchA, $iViewEdit, $bClearBoth, $arFilters)
/*****************************************************************************/
{
	$arVideoIDs = array();
	$sHTML = '';

	/*** $arFilters -> $iThreshold, $iNSFW ***/
	$iThreshold = $GLOBALS['default_threshold'];
	$iNSFW = $GLOBALS['default_nsfw'];
	$iFolderID = 0;
	if (is_array ($arFilters))
	{
		foreach ($arFilters as $key => $value)
		{
			if ($key == 'threshold') { $iThreshold = intval ($value); }
			if ($key == 'nsfw') { $iNSFW = intval ($value); }
			if ($key == 'folder') { $iFolderID = intval ($value); }
		}
	}
	if (in_array ($iThreshold, $GLOBALS['allowed_thresholds']) === FALSE)
		{ $iThreshold = $GLOBALS['default_threshold']; }
	if (in_array ($iNSFW, $GLOBALS['allowed_nsfws']) === FALSE)
		{ $iNSFW = $GLOBALS['default_nsfw']; }

	/*** $sOrderBy ***/
	switch ($sOrder)
	{
		case 'datedesc': $sOrderBy = "video_adddate DESC, video_id DESC"; break;
		case 'dateasc': $sOrderBy = "video_adddate ASC, video_id ASC"; break;
		case 'viewsdesc': $sOrderBy = "video_views DESC, video_id DESC"; break;
		case 'viewsasc': $sOrderBy = "video_views ASC, video_id ASC"; break;
		case 'likesdesc': $sOrderBy = "likes DESC, video_id DESC"; break;
		case 'likesasc': $sOrderBy = "likes ASC, video_id ASC"; break;
		case 'commentsdesc': $sOrderBy = "comments DESC, video_id DESC"; break;
		case 'commentsasc': $sOrderBy = "comments ASC, video_id ASC"; break;
		case 'secdesc': $sOrderBy = "video_seconds DESC, video_id DESC"; break;
		case 'secasc': $sOrderBy = "video_seconds ASC, video_id ASC"; break;
		/*** folder ***/
		case 'itemdesc': $sOrderBy = "folderitem_order DESC, video_id DESC"; break;
	}

	$sWhereStart = "WHERE (video_deleted='0') AND (video_views >= '" .
		$iThreshold . "')";
	switch ($iNSFW)
	{
		case 0: $sWhereStart .= " AND (video_nsfw='0')"; break;
		case 1: $sWhereStart .= " AND (video_nsfw='1')"; break;
		case 2: $sWhereStart .= " AND (video_nsfw='2')"; break;
		/*** 3 = any ***/
	}
	if ($iFolderID != 0)
	{
		$sSelectStart = "ffi.folderitem_order,";
		$sFromStart = "FROM `fst_folderitem` ffi LEFT JOIN `fst_video` fv ON ffi.video_id = fv.video_id";
		$sWhereStart .= " AND (folder_id='" . $iFolderID . "')";
	} else {
		$sSelectStart = "";
		$sFromStart = "FROM `fst_video` fv";
	}
	if ($iViewEdit != 1)
	{
		$sWhereStart .= " AND ((video_360='1') OR (video_istext='1'))";
	}

	/*** $iRowsTotal ***/
	$query_videos = "SELECT COUNT(*) AS total FROM (SELECT
		fv.video_id " . $sFromStart . "
		" . $sWhereStart . "
		" . $sWhere . "
		LIMIT 10000000000000000000
		OFFSET " . $iOffset . ") AS a";
	$result_videos = Query ($query_videos);
	$row_videos = mysqli_fetch_assoc ($result_videos);
	$iRowsTotal = $row_videos['total'];

	$query_videos = "SELECT
			" . $sSelectStart . "
			fv.video_id,
			video_title,
			video_thumbnail,
			language_id,
			video_seconds,
			video_preview,
			video_360,
			video_720,
			video_1080,
			video_views,
			video_adddate,
			video_istext,
			(SELECT COUNT(*) FROM `fst_likevideo` WHERE (video_id = fv.video_id)) AS likes,
			(SELECT COUNT(*) FROM `fst_comment` WHERE (video_id = fv.video_id) AND (comment_hidden='0') AND (fv.video_comments_allow='1') AND ((fv.video_comments_show='1') OR ((fv.video_comments_show='2') AND (comment_approved='1')))) AS comments,
			fu.user_username
		" . $sFromStart . "
		LEFT JOIN `fst_user` fu
			ON fv.user_id = fu.user_id
		" . $sWhereStart . "
		" . $sWhere . "
		ORDER BY " . $sOrderBy;
	if ($iLimit != 0) { $query_videos .= " LIMIT " . $iLimit; }
		else { $query_videos .= " LIMIT 10000000000000000000"; }
	$query_videos .= " OFFSET " . $iOffset;
	$result_videos = Query ($query_videos);
	$iRows = mysqli_num_rows ($result_videos);
	if ($iRows != 0)
	{
		while ($row_videos = mysqli_fetch_assoc ($result_videos))
		{
			if ($iFolderID != 0)
				{ $iOrder = intval ($row_videos['folderitem_order']); }
			$iVideoID = $row_videos['video_id'];
			array_push ($arVideoIDs, $iVideoID);
			$sCode = IDToCode ($iVideoID);
			$sTitle = $row_videos['video_title'];
			$iThumb = $row_videos['video_thumbnail'];
			$sThumb = ThumbURL ($sCode, '180', $iThumb, TRUE);
			if ($row_videos['video_preview'] == 1)
				{ $sPreview = VideoURL ($sCode, 'preview'); }
					else { $sPreview = ''; }
			if (($row_videos['video_720'] == 1) || ($row_videos['video_1080'] == 1))
				{ $bHD = TRUE; } else { $bHD = FALSE; }
			$iViews = $row_videos['video_views'];

			$sHTML .= '<div class="video';
			if ($sSection == 'index') { $sHTML .= ' zoomin'; }
			$sHTML .= '">';
			$sHTML .= '<div style="position:relative;">';
			$sHTML .= '<a href="/v/' . $sCode . '">';
			$sHTML .= '<span data-name="hover" data-active="thumb" data-thumb="' . $sThumb . '" data-preview="' . $sPreview . '" data-title="' . Sanitize ($sTitle) . '" class="hover">';
			$sHTML .= '<img src="' . $sThumb . '" alt="' .
				Sanitize ($sTitle) . '" class="thumb-or-preview">';
			$sHTML .= '</span>';
			$sHTML .= '</a>';
			if (($row_videos['video_preview'] == 2) ||
				($row_videos['video_360'] == 2) ||
				($row_videos['video_720'] == 2) ||
				($row_videos['video_1080'] == 2))
			{
				$sHTML .= '<span class="processing-span">';
				$sHTML .= Processing();
				$sHTML .= '</span>';
			}
			if ($bHD === TRUE)
			{
				$sHTML .= '<span class="hd-span">';
				$sHTML .= '<img src="/images/HD.png" alt="HD" style="height:15px;">';
				$sHTML .= '</span>';
			}
			if ($row_videos['video_seconds'] != 0)
			{
				$sHTML .= '<span class="time-span">';
				$sHTML .= VideoTime ($row_videos['video_seconds']);
				$sHTML .= '</span>';
			} else if ($row_videos['video_istext'] == '1') {
				$sHTML .= '<span class="time-span">text</span>';
			}
			if ($row_videos['language_id'] != 0)
			{
				$sLanguage = LanguageName ('eng', $row_videos['language_id']);
				if ($sLanguage != 'English')
				{
					$sHTML .= '<span class="lang-span">';
					$sHTML .= $sLanguage;
					$sHTML .= '</span>';
				}
			}
			if ($iViewEdit == 2)
			{
				$sHTML .= '<span class="edit-span">';
				if ($iOrder < 1000)
				{
					$sHTML .= '<a id="action-up-' . $sCode .
						'" href="javascript:;" title="up">&nwarr;</a>';
				} else { $sHTML .= '&nwarr;'; }
				$sHTML .= ' ' . $iOrder . ' ';
				if ($iOrder > 0)
				{
					$sHTML .= '<a id="action-down-' . $sCode .
						'" href="javascript:;" title="down">&searr;</a>';
				} else { $sHTML .= '&searr;'; }
				$sHTML .= ' ';
				$sHTML .= '<a id="action-del-' . $sCode .
					'" href="javascript:;" title="delete">x</a>';
				$sHTML .= '</span>';
			}
			$sHTML .= '</div>';
			$sHTML .= '<a href="/v/' . $sCode . '" title="' .
				Sanitize ($sTitle) . '" style="text-decoration:none;">';
			$sHTML .= '<h2 class="title"';
			if ($row_videos['video_istext'] == '1')
				{ $sHTML .= ' style="font-style:italic;"'; }
			$sHTML .= '>' . Sanitize ($sTitle) . '</h2>';
			$sHTML .= '</a>';
			if ($iViewEdit == 1)
			{
				$sHTML .= '<span style="display:block; text-align:center;">';
				if ($row_videos['video_360'] == 1)
				{
					$sHTML .= '<a href="/v/' . $sCode . '">view</a>';
				} else { $sHTML .= 'view'; }
				$sHTML .= ' ';
				if ($row_videos['video_preview'] == 1)
				{
					$sHTML .= '<a href="/edit/' . $sCode . '">edit</a>';
				} else { $sHTML .= 'edit'; }
				$sHTML .= '</span>';
				$sHTML .= '<input type="checkbox" name="delete-' . $sCode . '" class="chk-delete">';
			} else {
				$sHTML .= '<div style="position:relative;">';
				$sHTML .= '<a href="/user/' . $row_videos['user_username'] . '">';
				$sHTML .= '<div class="user">' .
					$row_videos['user_username'] . '</div>';
				$sHTML .= '</a>';
				$sHTML .= '<div class="views">' . $iViews . ' view';
				if ($iViews != 1) { $sHTML .= 's'; }
				$sHTML .= '</div>';
				$sHTML .= '</div>';
			}
			$sHTML .= '</div>';
			$sHTML .= "\n";
		}
		if ($bClearBoth === TRUE) { $sHTML .= '<div style="clear:both;"></div>'; }

		if ($iViewEdit == 2)
		{
$sHTML .= '
<script>
function ItemAction (code, action){
	$.ajax({
		type: "POST",
		url: "/editf/item.php",
		data: ({
			folder : "' . $iFolderID . '",
			code : code,
			action : action,
			csrf_token : "' . $_SESSION['fst']['csrf_token'] . '"
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				var filters = {};
				filters["threshold"] = 0;
				filters["nsfw"] = 3;
				filters["folder"] = ' . $iFolderID . ';
				VideosJS ("items", "editf", "itemdesc", 0, "", "", "", filters);
			} else {
				alert(error);
			}
		},
		error: function() {
			alert("Error calling item.php.");
		}
	});
}
$("[id^=action-up]").click(function(){
	var code = $(this).attr("id").replace("action-up-","");
	ItemAction (code, "up");
});
$("[id^=action-down]").click(function(){
	var code = $(this).attr("id").replace("action-down-","");
	ItemAction (code, "down");
});
$("[id^=action-del]").click(function(){
	var code = $(this).attr("id").replace("action-del-","");
	if (confirm ("Remove from folder?")) {
		ItemAction (code, "del");
	}
});
</script>
';
		}

		/*** prev, next ***/
		if (($sDivID != '') && (($iOffset != 0) || ($iRowsTotal > $iRows)))
		{
			$sHTML .= '<div style="width:100%; text-align:center; font-size:16px;">';
			if ($iOffset != 0)
			{
$sHTML .= '
<a href="javascript:;" id="prev-' . $sDivID . '" title="prev">&laquo;</a>
<script>
$("#prev-' . $sDivID . '").click(function(){
	$("#' . $sDivID . '").html(\'<img src="/images/loading.gif" alt="loading">\');
	var filters = {};
	filters["threshold"] = ' . $iThreshold . ';
	filters["nsfw"] = ' . $iNSFW . ';
	filters["folder"] = ' . $iFolderID . ';
	VideosJS ("' . $sDivID . '", "' . $sSection . '", "' . $sOrder . '", ' . ($iOffset - $iLimit) . ', "' . Sanitize ($sUsername) . '", "' . Sanitize ($sSearchT) . '", "' . Sanitize ($sSearchA) . '", filters);
});
</script>
';
			} else { $sHTML .= '&laquo;'; }
			$sHTML .= ' · ';
			if ($iRowsTotal > $iRows)
			{
$sHTML .= '
<a href="javascript:;" id="next-' . $sDivID . '" title="next">&raquo;</a>
<script>
$("#next-' . $sDivID . '").click(function(){
	$("#' . $sDivID . '").html(\'<img src="/images/loading.gif" alt="loading">\');
	var filters = {};
	filters["threshold"] = ' . $iThreshold . ';
	filters["nsfw"] = ' . $iNSFW . ';
	filters["folder"] = ' . $iFolderID . ';
	VideosJS ("' . $sDivID . '", "' . $sSection . '", "' . $sOrder . '", ' . ($iOffset + $iLimit) . ', "' . Sanitize ($sUsername) . '", "' . Sanitize ($sSearchT) . '", "' . Sanitize ($sSearchA) . '", filters);
});
</script>
';
			} else { $sHTML .= '&raquo;'; }
			$sHTML .= '</div>';
		}
	} else {
		if (($sUsername == '') && ($iFolderID == 0))
		{
			if (isset ($_SESSION['fst']['user_id']))
			{
				$sSuggest = '<br>Maybe <a href="/upload/">upload</a> a video?';
			} else {
				$sSuggest = '<br>Maybe <a href="/signin/">sign in</a> and upload?';
			}
		} else { $sSuggest = ''; }
		$sHTML .= '<span style="font-size:16px;">No videos.' .
			$sSuggest . '</span>';
	}

	$arReturn['video_ids'] = $arVideoIDs;
	$arReturn['html'] = $sHTML;
	return ($arReturn);
}
/*****************************************************************************/
function RandomCorrect ($sRandom)
/*****************************************************************************/
{
	if ($GLOBALS['live'] === FALSE) { return (TRUE); }

	if ((isset ($sRandom)) &&
		(isset ($_SESSION['fst']['random'])) &&
		(hash_equals ($_SESSION['fst']['random'], $sRandom)))
	{
		return (TRUE);
	} else {
		return (FALSE);
	}
}
/*****************************************************************************/
function TokenCorrect ($sToken)
/*****************************************************************************/
{
	if ((isset ($sToken)) &&
		(isset ($_SESSION['fst']['csrf_token'])) &&
		(hash_equals ($_SESSION['fst']['csrf_token'], $sToken)))
	{
		return (TRUE);
	} else {
		return (FALSE);
	}
}
/*****************************************************************************/
function ListIssues ($sInd, $iVideoID)
/*****************************************************************************/
{
	$iThumb = VideoThumb ($iVideoID);

	print ('<div style="margin:10px 0;">');
	print ('What is the issue?');
	$query_issues = "SELECT
			issue_name_long,
			issue_name_short
		FROM `fst_issue`
		WHERE (" . $sInd . "='1')";
	$result_issues = Query ($query_issues);
	while ($row_issues = mysqli_fetch_assoc ($result_issues))
	{
		$sNameShort = $row_issues['issue_name_short'];

		if (($sNameShort != 'thumbnail') || ($iThumb == 6))
		{
			print ('<span style="display:block;">');
			print ('<input type="radio" name="problem" value="' .
				$sNameShort . '"> ');
			print ($row_issues['issue_name_long']);
			print ('</span>');

			if ($sNameShort == 'explicit')
			{
print ('
<span id="explicit-hint" style="font-style:italic; display:none;">
"Sexually explicit content" is a euphemism for PORNOGRAPHY.
<br>
Select if the content describes/displays sexual organs or activity with the intention to stimulate sexual excitement.
</span>
');
			}
		}
	}
	print ('</div>');
}
/*****************************************************************************/
function AutoCaptcha ()
/*****************************************************************************/
{
	if ((IsAdmin()) || (IsMod()))
	{
		return (VerifyAnswer());
	} else {
		return ('');
	}
}
/*****************************************************************************/
function BanIP ($sIP, $bIsTor)
/*****************************************************************************/
{
	/* Returns...
	 * 0: Not added.
	 * 1: Added.
	 * 2: Already banned.
	 *
	 * Generally, to ban a single IPv6 user, start banning /128, go up to /64 if
	 * it doesn't help, go up to /56 if it still doesn't help, etc.
	 * BanIP() uses /64.
	 * https://www.mediawiki.org/wiki/Help:Range_blocks/IPv6
	 *
	 * Manually add:
	 * INSERT INTO `fst_banned` VALUES (NULL, INET6_ATON('...'),
	 * INET6_ATON('...'), INET6_ATON('...'), 0, NOW());
	 *
	 * Manually update from and to:
	 * UPDATE `fst_banned` SET banned_ip_from=INET6_ATON('...'),
	 * banned_ip_to=INET6_ATON('...') WHERE (banned_id='...');
	 */

	if ($sIP == '') { return (0); }
	if (IsBanned ($sIP) != 0) { return (2); }

	/*** $sIPFrom and $sIPTo ***/
	if (strpos ($sIP, '.') === FALSE)
	{
		/*** IPv6 /64 ***/
		if (strpos ($sIP, '::') !== FALSE)
		{
			/* Zeroes have been omitted.
			 * This would mess up the substr() below.
			 */
			$iColons = substr_count ($sIP, ':');
			switch ($iColons)
			{
				case 7: $sIP = str_replace ('::', ':0:', $sIP); break;
				case 6: $sIP = str_replace ('::', ':0:0:', $sIP); break;
				case 5: $sIP = str_replace ('::', ':0:0:0:', $sIP); break;
				case 4: $sIP = str_replace ('::', ':0:0:0:0:', $sIP); break;
				case 3: $sIP = str_replace ('::', ':0:0:0:0:0:', $sIP); break;
			}
			if (substr ($sIP, -1) == ':') { $sIP .= '0'; }
		}
		preg_match_all ('/:/', $sIP, $arColon, PREG_OFFSET_CAPTURE);
		$sIPBase = substr ($sIP, 0, $arColon[0][3][1] + 1);
		$sIPFrom = $sIPBase . '0000:0000:0000:0000';
		$sIPTo = $sIPBase . 'ffff:ffff:ffff:ffff';
	} else {
		/*** IPv4 ***/
		$sIPFrom = $sIP;
		$sIPTo = $sIP;
	}

	/*** $iIsTor ***/
	if ($bIsTor === FALSE) { $iIsTor = 0; } else { $iIsTor = 1; }

	/*** $sDTNow ***/
	$sDTNow = date ('Y-m-d H:i:s');

	$query_ban = "INSERT INTO `fst_banned` SET
		banned_ip_from='" . mysqli_real_escape_string
			($GLOBALS['link'], inet_pton ($sIPFrom)) . "',
		banned_ip_to='" . mysqli_real_escape_string
			($GLOBALS['link'], inet_pton ($sIPTo)) . "',
		banned_ip='" . mysqli_real_escape_string
			($GLOBALS['link'], inet_pton ($sIP)) . "',
		banned_istor='" . $iIsTor . "',
		banned_dt='" . $sDTNow . "'";
	Query ($query_ban);
	if (mysqli_affected_rows ($GLOBALS['link']) == 1)
		{ return (1); } else { return (0); }
}
/*****************************************************************************/
function IsBanned ($sIP)
/*****************************************************************************/
{
	/* Returns...
	 * 0: Not banned.
	 * 1: Banned.
	 * 2: Banned, Tor exit node.
	 */

	$sVisitor = inet_ntop (inet_pton ($sIP));
	$query_banned = "SELECT
			banned_istor
		FROM `fst_banned`
		WHERE (INET6_ATON('" . $sVisitor . "')
			BETWEEN banned_ip_from AND banned_ip_to)";
	$result_banned = Query ($query_banned);
	if (mysqli_num_rows ($result_banned) != 0) /*** Do NOT use "== 1". ***/
	{
		$row_banned = mysqli_fetch_assoc ($result_banned); /*** First hit. ***/
		if ($row_banned['banned_istor'] == 0)
			{ return (1); } else { return (2); }
	} else {
		return (0);
	}
}
/*****************************************************************************/
function CheckIfBanned ()
/*****************************************************************************/
{
	if ($_SERVER['REQUEST_URI'] != '/banned/')
	{
		$sIP = GetIP();
		if (IsBanned ($sIP) != 0)
		{
			header ('Location: /banned/');
			exit();
		}
	} 
}
/*****************************************************************************/
function CheckIfMaintenance ()
/*****************************************************************************/
{
	if (($GLOBALS['maintenance'] === TRUE) && (!IsAdmin()) && (!IsMod()))
	{
		unset ($_SESSION['fst']['user_id']);
		unset ($_SESSION['fst']['user_username']);
		unset ($_SESSION['fst']['user_pref_nsfw']);
		/***/
		unset ($_SESSION['fst']['step_signup']);
		unset ($_SESSION['fst']['step_forgot']);
		unset ($_SESSION['fst']['step_account']);
		/***/
		header ('Location: /maintenance/');
		exit();
	}
}
/*****************************************************************************/
function GetUserInfo ($iUserID, $sField)
/*****************************************************************************/
{
	$query_user = "SELECT
			*
		FROM `fst_user`
		WHERE (user_id='" . $iUserID . "')";
	$result_user = Query ($query_user);
	if (mysqli_num_rows ($result_user) == 1)
	{
		$row_user = mysqli_fetch_assoc ($result_user);
		if (isset ($row_user[$sField]))
		{
			$sInfo = $row_user[$sField];
		} else {
			print ('Unknown field.');
			exit();
		}
	} else {
		print ('Unknown user ID.');
		exit();
	}

	return ($sInfo);
}
/*****************************************************************************/
function WarningCount ($iCount)
/*****************************************************************************/
{
	if ($iCount == 0)
	{
		$sCount = strval ($iCount);
	} else {
		$sCount = '<span style="color:#00f; font-weight:bold;">';
		$sCount .= strval ($iCount);
		$sCount .= '</span>';
	}

	return ($sCount);
}
/*****************************************************************************/
function CompareByLength ($sString1, $sString2)
/*****************************************************************************/
{
	return (strlen ($sString2) - strlen ($sString1));
}
/*****************************************************************************/
function Times ($sString)
/*****************************************************************************/
{
	preg_match_all ('/(([01]?[0-9]|2[0-3]):)?([0-5]?[0-9]):([0-5][0-9])/',
		$sString, $arMatches);
	$arMatchesU = array_unique ($arMatches[0]);
	/* Using usort() to prevent smaller matches (e.g. 10:00) from
	 * modifying larger matches (e.g. 1:10:00).
	 */
	usort ($arMatchesU, 'CompareByLength');
	foreach ($arMatchesU as $sMatchU)
	{
		switch (strlen ($sMatchU))
		{
			case 4: $sMatchS = '00:0' . $sMatchU; break;
			case 5: $sMatchS = '00:' . $sMatchU; break;
			case 7: $sMatchS = '0' . $sMatchU; break;
		}
		$iSeconds = strtotime ('1970-01-01 ' . $sMatchS . ' UTC');
		$sString = str_replace ($sMatchU, '<a href="?t=' . $iSeconds . '">' .
			/* Using str_replace() to prevent smaller matches (e.g. 10:00) from
			 * modifying larger matches (e.g. 1:10:00).
			 */
			str_replace (':', '&colon;', $sMatchU) . '</a>', $sString);
	}

	return ($sString);
}
/*****************************************************************************/
function NewCommentsGrouped ($sColumn, $iUserID)
/*****************************************************************************/
{
	$arReturn = array();

	$query_new = "SELECT
			COUNT(*) AS amount,
			fc.video_id,
			fv.video_title
		FROM `fst_comment` fc
		LEFT JOIN `fst_video` fv
			ON fc.video_id=fv.video_id
		WHERE (fc.comment_hidden='0')
		AND (fv.video_deleted='0')
		AND (" . $sColumn . "='" . $iUserID . "')
		GROUP BY fc.video_id";
	$result_new = Query ($query_new);
	while ($row_new = mysqli_fetch_assoc ($result_new))
	{
		$iAmount = $row_new['amount'];
		$iVideoID = $row_new['video_id'];
		$sVideoTitle = $row_new['video_title'];
		$sCode = IDToCode ($iVideoID);

		$arLine = array ($sCode, $sVideoTitle, $iAmount);
		array_push ($arReturn, $arLine);
	}

	return ($arReturn);
}
/*****************************************************************************/
function NewComments ($sColumn, $iUserID)
/*****************************************************************************/
{
	$arReturn = array();

	$query_new = "SELECT
			fc.comment_id
		FROM `fst_comment` fc
		LEFT JOIN `fst_video` fv
			ON fc.video_id=fv.video_id
		WHERE (fc.comment_hidden='0')
		AND (fv.video_deleted='0')
		AND (" . $sColumn . "='" . $iUserID . "')";
	$result_new = Query ($query_new);
	while ($row_new = mysqli_fetch_assoc ($result_new))
	{
		array_push ($arReturn, $row_new['comment_id']);
	}

	return ($arReturn);
}
/*****************************************************************************/
function NewMessages ($iUserID)
/*****************************************************************************/
{
	$arReturn = array();

	$query_new = "SELECT
			message_id
		FROM `fst_message`
		WHERE (user_id_recipient='" . $iUserID . "')
		AND (message_cleared='0')
		ORDER BY message_adddate DESC";
	$result_new = Query ($query_new);
	while ($row_new = mysqli_fetch_assoc ($result_new))
	{
		array_push ($arReturn, $row_new['message_id']);
	}

	return ($arReturn);
}
/*****************************************************************************/
function NrNotifications ()
/*****************************************************************************/
{
	$iNotifications = 0;

	if (isset ($_SESSION['fst']['user_id']))
	{
		$iUserID = intval ($_SESSION['fst']['user_id']);

		$arNewCommentsGrouped = NewCommentsGrouped
			('comment_notify_publisher', $iUserID);
		$iNotifications += count ($arNewCommentsGrouped);

		$arNewCommentsGrouped = NewCommentsGrouped
			('comment_notify_parent', $iUserID);
		$iNotifications += count ($arNewCommentsGrouped);

		$arNewMessages = NewMessages ($iUserID);
		$iNotifications += count ($arNewMessages);
	}

	return ($iNotifications);
}
/*****************************************************************************/
function Search ($sSearch)
/*****************************************************************************/
{
print ('
<div id="search">
<form action="/" method="GET">
<input type="text" id="search_query" name="search_query" list="search_list" value="' . Sanitize ($sSearch) . '" autocomplete="off" placeholder="Search" minlength="2" maxlength="100" required><button id="search_button"><img src="/images/icon_search.png" alt="Search" style="padding:8px;"></button> <span id="matches"><span id="matches_title"></span><span id="matches_any"></span></span>
<datalist id="search_list">
</datalist>
</form>
<script>
$("#search_query").on("keyup",function(e){
	var keycode = (e.keyCode || e.which);
	var search_query = $("#search_query").val();
	UpdateSearchList (keycode, search_query);
});
</script>
</div>
');
}
/*****************************************************************************/
function IncreaseViews ($iVideoID)
/*****************************************************************************/
{
	/*** $iVideoID is 0 for the Home page. ***/

	$sIP = GetIP();
	$sDateDB = date ('Y-m-d');

	$query_recent = "SELECT
			recentviews_id
		FROM `fst_recentviews`
		WHERE (video_id='" . $iVideoID . "')
		AND (recentviews_ip='" . $sIP . "')
		AND (recentviews_date='" . $sDateDB . "')";
	$result_recent = Query ($query_recent);
	if (mysqli_num_rows ($result_recent) == 0)
	{
		/*** $sReferrer ***/
		if (isset ($_SERVER['HTTP_REFERER']))
		{
			$sReferrer = $_SERVER['HTTP_REFERER'];
			if ((strpos ($sReferrer, $GLOBALS['protocol'] . '://www.' .
				$GLOBALS['domain']) === 0) ||
				(strpos ($sReferrer, $GLOBALS['protocol'] . '://' .
				$GLOBALS['domain']) === 0)) { $sReferrer = $GLOBALS['name']; }
		} else { $sReferrer = ''; }

		$query_add = "INSERT INTO `fst_recentviews` SET
				video_id='" . $iVideoID . "',
				recentviews_ip='" . $sIP . "',
				recentviews_date='" . $sDateDB . "'";
		Query ($query_add);

		$query_ref = "INSERT INTO `fst_referrer` SET
				video_id='" . $iVideoID . "',
				referrer_url='" . mysqli_real_escape_string
					($GLOBALS['link'], $sReferrer) . "',
				referrer_date='" . $sDateDB . "'
			ON DUPLICATE KEY UPDATE referrer_count=referrer_count+1";
		Query ($query_ref);

		if ($iVideoID != 0)
		{
			$query_inc = "UPDATE `fst_video` SET
					video_views=video_views+1
				WHERE (video_id='" . $iVideoID . "')";
			Query ($query_inc);
		}
	}
}
/*****************************************************************************/
function MatchesTitle ($sSearch, $iNSFW)
/*****************************************************************************/
{
	$arSearch = explode (' ', $sSearch);
	$sWhereStart = "WHERE (video_deleted='0') AND ((video_360='1') OR (video_istext='1'))";
	switch ($iNSFW)
	{
		case 0: $sWhereStart .= " AND (video_nsfw='0')"; break;
		case 1: $sWhereStart .= " AND (video_nsfw='1')"; break;
		case 2: $sWhereStart .= " AND (video_nsfw='2')"; break;
		/*** 3 = any ***/
	}
	$sWhereTitle = '';
	foreach ($arSearch as $sBit)
	{
		$sWhereTitle .= " AND (video_title LIKE '%" . str_replace ('%', '\%',
			mysqli_real_escape_string ($GLOBALS['link'], $sBit)) . "%')";
	}
	$query_matches = "SELECT COUNT(*) AS matches FROM (SELECT
		video_id FROM `fst_video`
		" . $sWhereStart . "
		" . $sWhereTitle . ") AS a";
	$result_matches = Query ($query_matches);
	$row_matches = mysqli_fetch_assoc ($result_matches);
	$iMatches = intval ($row_matches['matches']);

	return ($iMatches);
}
/*****************************************************************************/
function MatchesAny ($sSearch, $iNSFW)
/*****************************************************************************/
{
	$arSearch = explode (' ', $sSearch);
	$sWhereStart = "WHERE (video_deleted='0') AND ((video_360='1') OR (video_istext='1'))";
	switch ($iNSFW)
	{
		case 0: $sWhereStart .= " AND (video_nsfw='0')"; break;
		case 1: $sWhereStart .= " AND (video_nsfw='1')"; break;
		case 2: $sWhereStart .= " AND (video_nsfw='2')"; break;
		/*** 3 = any ***/
	}
	$sWhereAny = '';
	foreach ($arSearch as $sBit)
	{
		$sWhereAny .= " AND ((video_title LIKE '%" . str_replace ('%', '\%',
			mysqli_real_escape_string ($GLOBALS['link'], $sBit)) . "%') OR
			(video_description LIKE '%" . str_replace ('%', '\%',
			mysqli_real_escape_string ($GLOBALS['link'], $sBit)) . "%') OR
			(video_tags LIKE '%" . str_replace ('%', '\%',
			mysqli_real_escape_string ($GLOBALS['link'], $sBit)) . "%'))";
	}
	$query_matches = "SELECT COUNT(*) AS matches FROM (SELECT
		video_id FROM `fst_video`
		" . $sWhereStart . "
		" . $sWhereAny . ") AS a";
	$result_matches = Query ($query_matches);
	$row_matches = mysqli_fetch_assoc ($result_matches);
	$iMatches = intval ($row_matches['matches']);

	return ($iMatches);
}
/*****************************************************************************/
function LanguageName ($sType, $iLanguageID)
/*****************************************************************************/
{
	/*** Returns a name or FALSE. ***/

	$query_lang = "SELECT
			language_name" . $sType . " AS name
		FROM `fst_language`
		WHERE (language_id='" . $iLanguageID . "')";
	$result_lang = Query ($query_lang);
	if (mysqli_num_rows ($result_lang) == 0)
	{
		$sReturn = FALSE;
	} else {
		$row_lang = mysqli_fetch_assoc ($result_lang);
		$sReturn = $row_lang['name'];
	}

	return ($sReturn);
}
/*****************************************************************************/
function IsMuted ($iUserPublisher, $iUserCommenter)
/*****************************************************************************/
{
	/*** Returns TRUE or FALSE. ***/

	$query_muted = "SELECT
			mute_id
		FROM `fst_mute`
		WHERE (mute_user_publisher='" . $iUserPublisher . "')
		AND (mute_user_commenter='" . $iUserCommenter . "')";
	$result_muted = Query ($query_muted);
	if (mysqli_num_rows ($result_muted) == 1)
		{ return (TRUE); } else { return (FALSE); }
}
/*****************************************************************************/
function UsernameInUse ($sUsername)
/*****************************************************************************/
{
	/*** Returns TRUE or FALSE. ***/

	if ($sUsername == '') { return (FALSE); }

	$query_found = "SELECT
			COUNT(*) AS found
		FROM `fst_user`
		WHERE (user_username='" . mysqli_real_escape_string
			($GLOBALS['link'], $sUsername) . "')
		OR (user_username_old1='" . mysqli_real_escape_string
			($GLOBALS['link'], $sUsername) . "')
		OR (user_username_old2='" . mysqli_real_escape_string
			($GLOBALS['link'], $sUsername) . "')";
	$result_found = Query ($query_found);
	$row_found = mysqli_fetch_assoc ($result_found);
	if ($row_found['found'] == 0)
		{ return (FALSE); } else { return (TRUE); }
}
/*****************************************************************************/
function OldUsernames ($iUserID)
/*****************************************************************************/
{
	/*** Returns an array with 'count', 'old1' and 'old2'. ***/

	$arOld['count'] = 0;
	$query_old = "SELECT
			user_username_old1,
			user_username_old2
		FROM `fst_user`
		WHERE (user_id='" . $iUserID . "')";
	$result_old = Query ($query_old);
	$row_old = mysqli_fetch_assoc ($result_old);
	$arOld['old1'] = $row_old['user_username_old1'];
	$arOld['old2'] = $row_old['user_username_old2'];
	if ($arOld['old1'] != '') { $arOld['count']++; }
	if ($arOld['old2'] != '') { $arOld['count']++; }

	return ($arOld);
}
/*****************************************************************************/
function HasNonhiddenReplies ($iCommentID)
/*****************************************************************************/
{
	/*** Returns TRUE or FALSE. ***/

	$query_replies = "SELECT
			comment_id,
			comment_hidden
		FROM `fst_comment`
		WHERE (comment_parent_id='" . $iCommentID . "')";
	$result_replies = Query ($query_replies);
	while ($row_replies = mysqli_fetch_assoc ($result_replies))
	{
		if (($row_replies['comment_hidden'] == '0') ||
			(HasNonhiddenReplies ($row_replies['comment_id'])))
		{ return (TRUE); }
	}

	return (FALSE);
}
/*****************************************************************************/
function BBCodeToHTML ($sText)
/*****************************************************************************/
{
	$sReturn = nl2br (Sanitize ($sText));

	/*** italic ***/
	$sReturn = str_replace ('[i]', '<i>', $sReturn);
	$sReturn = str_replace ('[/i]', '</i>', $sReturn);

	/*** underline ***/
	$sReturn = str_replace ('[u]', '<u>', $sReturn);
	$sReturn = str_replace ('[/u]', '</u>', $sReturn);

	/*** size ***/
	$sReturn = preg_replace ('~\[size=([0-9]+)\]~',
		'<span style="font-size:$1px;">', $sReturn);
	$sReturn = str_replace ('[/size]', '</span>', $sReturn);

	/*** (un)ordered list ***/
	$sReturn = str_replace ('[ul]', '<ul>', $sReturn);
	$sReturn = str_replace ('[/ul]', '</ul>', $sReturn);
	$sReturn = preg_replace ('~\[ol type=&quot;([1aAiI])&quot;\]~',
		'<ol type="$1">', $sReturn);
	$sReturn = str_replace ('[/ol]', '</ol>', $sReturn);
	$sReturn = str_replace ('[li]', '<li>', $sReturn);
	$sReturn = str_replace ('[/li]', '</li>', $sReturn);

	/*** blockquote ***/
	$sReturn = str_replace ('[blockquote]', '<blockquote>', $sReturn);
	$sReturn = str_replace ('[/blockquote]', '</blockquote>', $sReturn);

	/*** center ***/
	$sReturn = str_replace ('[center]',
		'<span style="display:block; text-align:center;">', $sReturn);
	$sReturn = str_replace ('[/center]', '</span>', $sReturn);

	/*** h2, h3, anchors, link to id ***/
	$sReturn = str_replace ('[h2]', '<h2>', $sReturn);
	$sReturn = preg_replace ('~\[h2 id=&quot;([0-9a-zA-Z\.]+)&quot;\]~',
		'<h2 id="$1">', $sReturn);
	$sReturn = str_replace ('[/h2]', '</h2>', $sReturn);
	$sReturn = str_replace ('[h3]', '<h3>', $sReturn);
	$sReturn = preg_replace ('~\[h3 id=&quot;([0-9a-zA-Z\.]+)&quot;\]~',
		'<h3 id="$1">', $sReturn);
	$sReturn = str_replace ('[/h3]', '</h3>', $sReturn);
	$sReturn = preg_replace ('~\[anchor id=&quot;([0-9a-zA-Z\.]+)&quot;\]~',
		'<span id="$1">', $sReturn);
	$sReturn = str_replace ('[/anchor]', '</span>', $sReturn);
	$sReturn = preg_replace ('~\[linktoid=([0-9a-zA-Z\.]+)\]~',
		'<a href="#$1">', $sReturn);
	$sReturn = str_replace ('[/linktoid]', '</a>', $sReturn);

	/*** indent ***/
	$sReturn = preg_replace ('~\[indent=([0-9]+)\]~',
		'<span style="display:block; padding-left:$1px;">', $sReturn);
	$sReturn = str_replace ('[/indent]', '</span>', $sReturn);

	/*** color ***/
	$sReturn = preg_replace ('~\[color=([#0-9a-z]+)\]~',
		'<span style="color:$1">', $sReturn);
	$sReturn = str_replace ('[/color]', '</span>', $sReturn);

	/*** font ***/
	$sReturn = preg_replace ('~\[font=([a-zA-Z- ]+)\]~',
		'<span style="font-family:\'$1\',Arial;">', $sReturn);
	$sReturn = str_replace ('[/font]', '</span>', $sReturn);

	/*** justify ***/
	$sReturn = str_replace ('[justify]',
		'<span style="display:block; text-align:justify;">', $sReturn);
	$sReturn = str_replace ('[/justify]', '</span>', $sReturn);

	/*** scrollable ***/
	$sReturn = preg_replace ('~\[scrollable height=&quot;([0-9]+)&quot;\]~',
		'<div style="height:$1px; overflow-y:scroll; border:1px solid #888;"><span style="display:block; padding:10px;">', $sReturn);
	$sReturn = str_replace ('[/scrollable]', '</span></div>', $sReturn);

	/*** sup ***/
	$sReturn = str_replace ('[sup]', '<sup>', $sReturn);
	$sReturn = str_replace ('[/sup]', '</sup>', $sReturn);

	/*** pre ***/
	$sReturn = str_replace ('[pre]', '<pre>', $sReturn);
	$sReturn = str_replace ('[/pre]', '</pre>', $sReturn);

	/*** strikethrough ***/
	$sReturn = str_replace ('[s]', '<s>', $sReturn);
	$sReturn = str_replace ('[/s]', '</s>', $sReturn);

	return ($sReturn);
}
/*****************************************************************************/
function IsText ($sCode)
/*****************************************************************************/
{
	/* Returns...
	 * 0: a (published) video
	 * 1: a published text
	 * 2: a non-published text
	 * 3: a (published) forum text
	 * FALSE: unknown code
	 */

	$iVideoID = CodeToID ($sCode);
	$query_istext = "SELECT
			video_istext
		FROM `fst_video`
		WHERE (video_id='" . $iVideoID . "')";
	$result_istext = Query ($query_istext);
	if (mysqli_num_rows ($result_istext) == 1)
	{
		$row_istext = mysqli_fetch_assoc ($result_istext);
		return (intval ($row_istext['video_istext']));
	} else {
		return (FALSE);
	}
}
/*****************************************************************************/
function Sanitize ($sUserInput)
/*****************************************************************************/
{
	$sReturn = htmlentities ($sUserInput, ENT_QUOTES);
	$sReturn = str_ireplace ('javascript', 'JS', $sReturn);

	return ($sReturn);
}
/*****************************************************************************/
function Pref ($sPref)
/*****************************************************************************/
{
	/*** Returns 0 or 1. ***/

	if (isset ($_SESSION['fst'][$sPref]))
	{
		return ($_SESSION['fst'][$sPref]);
	} else {
		return ($GLOBALS['default_pref'][$sPref]);
	}
}
/*****************************************************************************/
function InFolder ($iVideoID, $iUserID, $iFolderID)
/*****************************************************************************/
{
	/* Returns TRUE or FALSE.
	 * Either $iUserID or $iFolderID must be 0.
	 */

	$query_folder = "SELECT
			folderitem_id
		FROM `fst_folderitem` ffi
		LEFT JOIN `fst_folder` ff
			ON ffi.folder_id = ff.folder_id
		WHERE (ffi.video_id='" . $iVideoID . "')";
	if ($iUserID != 0)
		{ $query_folder .= " AND (ff.user_id='" . $iUserID . "')"; }
	if ($iFolderID != 0)
		{ $query_folder .= " AND (ffi.folder_id='" . $iFolderID . "')"; }
	$result_folder = Query ($query_folder);
	if (mysqli_num_rows ($result_folder) != 0)
		{ return (TRUE); } else { return (FALSE); }
}
/*****************************************************************************/
function FolderIsFromUser ($iFolderID, $iUserID)
/*****************************************************************************/
{
	/*** Returns TRUE or FALSE. ***/

	$query_folder = "SELECT
			folder_id
		FROM `fst_folder`
		WHERE (folder_id='" . $iFolderID . "')
		AND (user_id='" . $iUserID . "')";
	$result_folder = Query ($query_folder);
	if (mysqli_num_rows ($result_folder) == 1)
		{ return (TRUE); } else { return (FALSE); }
}
/*****************************************************************************/
function CreateMessage ($iSenderID, $iRecipientID, $sText)
/*****************************************************************************/
{
	/* $iSenderID:
	 * -1 = system
	 * 0 = "an administrator"
	 * positive number = user ID
	 */

	$sDTNow = date ('Y-m-d H:i:s');

	$query_message = "INSERT INTO `fst_message` SET
		user_id_sender='" . $iSenderID . "',
		user_id_recipient='" . $iRecipientID . "',
		message_text='" . mysqli_real_escape_string
			($GLOBALS['link'], $sText) . "',
		video_id='0',
		message_adddate='" . $sDTNow . "',
		message_cleared='0'";
	Query ($query_message);
}
/*****************************************************************************/
function VideoThumb ($iVideoID)
/*****************************************************************************/
{
	/*** Returns 1-6 or FALSE. ***/

	if ($iVideoID != 0)
	{
		$query_c = "SELECT
				video_thumbnail
			FROM `fst_video`
			WHERE (video_id='" . $iVideoID . "')";
		$result_c = Query ($query_c);
		$row_c = mysqli_fetch_assoc ($result_c);
		return (intval ($row_c['video_thumbnail']));
	} else { return (FALSE); }
}
/*****************************************************************************/
function RemoveCustomThumbnail ($iVideoID)
/*****************************************************************************/
{
	if (VideoThumb ($iVideoID) == 6)
	{
		$sCode = IDToCode ($iVideoID);
		/***/
		DeleteFile (ThumbURL ($sCode, '180', 6, TRUE), FALSE);
		DeleteFile (ThumbURL ($sCode, '720', 6, TRUE), FALSE);
		/***/
		/*** Do NOT use '3' here. ***/
		$query_rem = "UPDATE `fst_video` SET
			video_thumbnail='5'
			WHERE (video_id='" . $iVideoID . "')";
		Query ($query_rem);
	}
}
/*****************************************************************************/
function DeleteFile ($sPathFile, $bPrint)
/*****************************************************************************/
{
	if (strpos ($sPathFile, 'thumbnail') === FALSE) /*** Do not delete the fallback thumbnails. ***/
	{
		$sDelete = dirname (__FILE__) . $sPathFile;
		if (file_exists ($sDelete) === TRUE)
		{
			$bDeleted = unlink ($sDelete);
			if ($bPrint === TRUE)
			{
				if ($bDeleted === TRUE)
				{
					print ('<span style="display:block; color:#008000;">' .
						$sDelete . '</span>');
				} else {
					print ('<span style="display:block; color:#f00;">' .
						$sDelete . '</span>');
				}
			}
		} else {
			if ($bPrint === TRUE)
				{ print ('<span style="display:block;">' . $sDelete . '</span>'); }
		}
	}
}
/*****************************************************************************/
function MayAdd ($sType)
/*****************************************************************************/
{
	if (!isset ($_SESSION['fst']['user_username'])) { return (FALSE); }

	switch ($sType)
	{
		case 'videos':
			if ((count ($GLOBALS['may_add_videos']) != 0) &&
				(in_array ($_SESSION['fst']['user_username'],
				$GLOBALS['may_add_videos']) === FALSE)) { return (FALSE); }
			break;
		case 'texts':
			if ((count ($GLOBALS['may_add_texts']) != 0) &&
				(in_array ($_SESSION['fst']['user_username'],
				$GLOBALS['may_add_texts']) === FALSE)) { return (FALSE); }
			break;
		case 'topics':
			if ((count ($GLOBALS['may_add_topics']) != 0) &&
				(in_array ($_SESSION['fst']['user_username'],
				$GLOBALS['may_add_topics']) === FALSE)) { return (FALSE); }
			break;
	}

	return (TRUE);
}
/*****************************************************************************/

HasRequired();
StartSession();
MySQLConnect();
if (isset ($_SESSION['fst']['user_id'])) { CheckIfBanned(); }
if (isset ($_SESSION['fst']['user_id'])) { CheckIfMaintenance(); }
?>