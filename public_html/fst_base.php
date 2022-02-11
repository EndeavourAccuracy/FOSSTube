<?php
/* SPDX-License-Identifier: Zlib */
/* FOSSTube v1.5 (February 2022)
 * Copyright (C) 2020-2022 Norbert de Jonge <mail@norbertdejonge.nl>
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
<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar"');

	$iNrNotifications = NrNotifications();
	if ($iNrNotifications != 0)
	{
		print (' style="background-color:#008000;"');
	}

print ('>
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
ShowLi ('/patronage/', 'Patronage');
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
	ShowLi ('/timeline/', 'Timeline');
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
<meta name="twitter:site" content="' . $GLOBALS['twitter_account'] . '">
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
<script src="/js/fst.js?v=44"></script>

<!-- CSS -->
<link rel="stylesheet" type="text/css" href="/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="/css/nouislider.min.css">
<link rel="stylesheet" type="text/css" href="/css/fst.css?v=47">
');

if (!isset ($_SESSION['fst']['theme']))
	{ $_SESSION['fst']['theme'] = $GLOBALS['default_theme']; }
if ($_SESSION['fst']['theme'] == 'day')
{
	print ('<link rel="stylesheet" type="text/css" href="/css/fst_day.css?v=24" id="theme" data-theme="day">' . "\n");
} else {
	print ('<link rel="stylesheet" type="text/css" href="/css/fst_night.css?v=24" id="theme" data-theme="night">' . "\n");
}

print ('
</head>
');

	if ($bEmbed === FALSE)
	{
		$sCWidth = '';
		if ($sTitle == 'Home')
		{
			switch (Pref ('user_pref_cwidth'))
			{
				case 1: $sCWidth = ' width:100%!important;'; break;
				case 2: $sCWidth = ' width:calc(100% - 50px)!important;'; break;
				case 3: $sCWidth = ' width:calc(100% - 100px)!important;'; break;
				case 4: $sCWidth = ' width:calc(100% - 150px)!important;'; break;
				case 5: $sCWidth = ' width:2290px!important;'; break;
				case 6: $sCWidth = ' width:1730px!important;'; break;
				case 7: $sCWidth = ' width:1170px!important;'; break;
				case 8: $sCWidth = ' width:970px!important;'; break;
				case 9: $sCWidth = ' width:750px!important;'; break;
				case 10: $sCWidth = ' width:650px!important;'; break;
				case 11: $sCWidth = ' width:550px!important;'; break;
				case 12: $sCWidth = ' width:450px!important;'; break;
				case 13: $sCWidth = ' width:350px!important;'; break;
			}
		}

print ('
<body>
<div class="container" style="height:calc(100% - 71px);' . $sCWidth . '">
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
function GetActiveURL ()
/*****************************************************************************/
{
	/*** This function does not urlencode(). ***/

	$sURL = '';
	$sURL .= $GLOBALS['protocol'];
	$sURL .= '://';
	$sURL .= $_SERVER['HTTP_HOST'];
	$sURL .= $_SERVER['REQUEST_URI'];

	return ($sURL);
}
/*****************************************************************************/
function HTMLEnd ()
/*****************************************************************************/
{
	/* For the validator, do NOT use "/check?uri=referer", because proxies
	 * may strip out the referrer header.
	 */

print ('
</div>
</div>

<div id="footer">
&copy; ' . date ('Y') . ' ' . $GLOBALS['name_copyright'] . '
&nbsp;|&nbsp;
<a target="_blank" href="https://validator.w3.org/nu/?doc=' . urlencode (GetActiveURL()) . '"><img src="/images/W3C_HTML5.png" alt="W3C HTML5"></a>
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
	/*** Returns an ID or FALSE. ***/

	$query_id = "SELECT
			user_id
		FROM `fst_user`
		WHERE (user_username='" . mysqli_real_escape_string
			($GLOBALS['link'], $sUsername) . "')";
	$result_id = Query ($query_id);
	if (mysqli_num_rows ($result_id) == 1)
	{
		$row_id = mysqli_fetch_assoc ($result_id);
		return ($row_id['user_id']);
	} else {
		return (FALSE);
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
function IsOwner ()
/*****************************************************************************/
{
	if ((isset ($_SESSION['fst']['user_username'])) &&
		(in_array ($_SESSION['fst']['user_username'],
			$GLOBALS['owners']) === TRUE))
	{
		return (TRUE);
	} else {
		return (FALSE);
	}
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
	/* This function works, but is deprecated.
	 * Wherever possible, use video_likes instead.
	 */

	$query_likes = "SELECT
			COUNT(*) AS likes
		FROM `fst_likevideo`
		WHERE (video_id='" . $iVideoID . "')";
	$result_likes = Query ($query_likes);
	$row_likes = mysqli_fetch_assoc ($result_likes);
	$iLikes = intval ($row_likes['likes']);

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
	$iLikes = intval ($row_likes['likes']);

	return ($iLikes);
}
/*****************************************************************************/
function LikesMBPost ($iPostID)
/*****************************************************************************/
{
	/* This function works, but is deprecated.
	 * Wherever possible, use mbpost_likes instead.
	 */

	$query_likes = "SELECT
			COUNT(*) AS likes
		FROM `fst_likembpost`
		WHERE (mbpost_id='" . $iPostID . "')";
	$result_likes = Query ($query_likes);
	$row_likes = mysqli_fetch_assoc ($result_likes);
	$iLikes = intval ($row_likes['likes']);

	return ($iLikes);
}
/*****************************************************************************/
function ReblogsMBPost ($iPostID)
/*****************************************************************************/
{
	/* This function works, but is deprecated.
	 * Wherever possible, use mbpost_reblogs instead.
	 */

	$query_reblogs = "SELECT
			COUNT(*) AS reblogs
		FROM `fst_microblog_post`
		WHERE (mbpost_id_reblog='" . $iPostID . "')
		AND (mbpost_hidden='0')";
	$result_reblogs = Query ($query_reblogs);
	$row_reblogs = mysqli_fetch_assoc ($result_reblogs);
	$iReblogs = intval ($row_reblogs['reblogs']);

	return ($iReblogs);
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
function MBPostExists ($sUsername, $iPostID)
/*****************************************************************************/
{
	/* Returns an array or FALSE.
	 * Hidden posts also return FALSE.
	 */

	$iID = intval ($iPostID);
	$query_post = "SELECT
			fm.mbpost_id,
			fm.user_id,
			fm.mbpost_text
		FROM `fst_microblog_post` fm
		LEFT JOIN `fst_user` fu
			ON fm.user_id = fu.user_id
		WHERE (fm.mbpost_id='" . $iID . "')
		AND (fu.user_username='" . mysqli_real_escape_string
			($GLOBALS['link'], $sUsername) . "')
		AND (fm.mbpost_hidden='0')";
	$result_post = Query ($query_post);
	if (mysqli_num_rows ($result_post) == 1)
	{
		$row_post = mysqli_fetch_assoc ($result_post);
		$arPost['id'] = intval ($row_post['mbpost_id']);
		$arPost['user_id'] = intval ($row_post['user_id']);
		$arPost['text'] = $row_post['mbpost_text'];
	} else {
		$arPost = FALSE;
	}

	return ($arPost);
}
/*****************************************************************************/
function UserExists ($sUsername)
/*****************************************************************************/
{
	/*** Returns an array or FALSE. ***/

	$query_user = "SELECT
			user_id,
			user_username,
			user_pref_nsfw,
			user_pref_cwidth,
			user_pref_tsize
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
		$arUser['pref_cwidth'] = intval ($row_user['user_pref_cwidth']);
		$arUser['pref_tsize'] = intval ($row_user['user_pref_tsize']);
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
		case 'likesdesc': $sOrderBy = "video_likes DESC, video_id DESC"; break;
		case 'likesasc': $sOrderBy = "video_likes ASC, video_id ASC"; break;
		case 'commentsdesc':
			$sOrderBy = "video_comments DESC, video_id DESC"; break;
		case 'commentsasc':
			$sOrderBy = "video_comments ASC, video_id ASC"; break;
		case 'secdesc': $sOrderBy = "(video_seconds = 0), video_seconds DESC," .
			" video_id DESC"; break;
		case 'secasc': $sOrderBy = "(video_seconds = 0), video_seconds ASC," .
			" video_id ASC"; break;
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
			video_likes,
			video_comments,
			video_adddate,
			video_istext,
			projection_id,
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
			/*** $iTSizeW, $iTSizeH ***/
			$iTSizeW = 0; $iTSizeH = 0;
			switch (Pref ('user_pref_tsize'))
			{
				case 100: $iTSizeW = 320; $iTSizeH = 180; break;
				case 90: $iTSizeW = 288; $iTSizeH = 162; break;
				/*** 80 = default ***/
				case 70: $iTSizeW = 224; $iTSizeH = 126; break;
				case 60: $iTSizeW = 192; $iTSizeH = 108; break;
				case 50: $iTSizeW = 160; $iTSizeH = 90; break;
			}

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
			if ($row_videos['projection_id'] != 0)
				{ $bSph = TRUE; } else { $bSph = FALSE; }
			$iViews = $row_videos['video_views'];

			$sHTML .= '<div class="video';
			if ($sSection == 'index') { $sHTML .= ' zoomin'; }
			$sHTML .= '"';
			if (($iTSizeW != 0) && ($iTSizeH != 0))
				{ $sHTML .= ' style="width:calc(' . $iTSizeW . 'px + 24px)!important; height:calc(' . $iTSizeH . 'px + 94px)!important;"'; }
			$sHTML .= '>';
			$sHTML .= '<div style="position:relative;">';
			$sHTML .= '<a href="/v/' . $sCode . '">';
			$sHTML .= '<span data-name="hover" data-active="thumb" data-thumb="' . $sThumb . '" data-preview="' . $sPreview . '" data-title="' . Sanitize ($sTitle) . '" class="hover"';
			if (($iTSizeW != 0) && ($iTSizeH != 0))
				{ $sHTML .= ' style="width:calc(' . $iTSizeW . 'px + 2px)!important; height:calc(' . $iTSizeH . 'px + 2px)!important;"'; }
			$sHTML .= '>';
			$sHTML .= '<img src="' . $sThumb . '" alt="' .
				Sanitize ($sTitle) . '" class="thumb-or-preview"';
			if (($iTSizeW != 0) && ($iTSizeH != 0))
				{ $sHTML .= ' style="max-width:' . $iTSizeW . 'px!important; max-height:' . $iTSizeH . 'px!important;"'; }
			$sHTML .= '>';
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
			if (($bHD === TRUE) || ($bSph === TRUE))
			{
				$sHTML .= '<span class="hd-span">';
				if ($bSph === FALSE)
				{
					$sHTML .= '<img src="/images/HD.png" alt="HD"' .
						' style="height:15px;">';
				} else {
					$sHTML .= '<img src="/images/Sph.png" alt="Sph"' .
						' style="height:15px;">';
				}
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
			if (($row_videos['video_istext'] == '1') ||
				(Pref ('user_pref_tsize') !=
				$GLOBALS['default_pref']['user_pref_tsize']))
			{
				$sHTML .= ' style="';
				if ($row_videos['video_istext'] == '1')
					{ $sHTML .= 'font-style:italic;'; }
				if (($iTSizeW != 0) && ($iTSizeH != 0))
					{ $sHTML .= 'width:' . $iTSizeW . 'px!important;'; }
				$sHTML .= '"';
			}
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
				$sHTML .= '<div class="user"';
				if (($iTSizeW != 0) && ($iTSizeH != 0))
					{ $sHTML .= ' style="max-width:' . $iTSizeW . 'px!important;"'; }
				$sHTML .= '>';
				$sHTML .= $row_videos['user_username'];
				$sHTML .= '</div>';
				$sHTML .= '</a>';
				$sHTML .= '<div class="views">' . number_format ($iViews) . ' view';
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
		(hash_equals ($_SESSION['fst']['random'], trim ($sRandom))))
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
		unset ($_SESSION['fst']['user_pref_cwidth']);
		unset ($_SESSION['fst']['user_pref_tsize']);
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
			case 8: $sMatchS = $sMatchU; break;
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
function HashTags ($sString)
/*****************************************************************************/
{
	preg_match_all ('/(?<!&)(#\w+)/', $sString, $arMatches);
	$arMatchesU = array_unique ($arMatches[0]);
	foreach ($arMatchesU as $sMatchU)
	{
		$sReplace = '<a href="/explore/?phrase=' .
			urlencode (HashFlag ($sMatchU, FALSE)) .
			'" style="white-space:nowrap;">';
		$sReplace .= HashFlag ($sMatchU, TRUE);
		$sReplace .= '</a>';

		$sString = str_replace ($sMatchU, $sReplace, $sString);
	}

	return ($sString);
}
/*****************************************************************************/
function HashFlag ($sHashTag, $bImage)
/*****************************************************************************/
{
	$sSearch = substr (strtolower ($sHashTag), 1);

	if (isset ($GLOBALS['hashflags'][$sSearch]))
	{
		$sText = $GLOBALS['hashflags'][$sSearch];
	} else {
		return ($sHashTag);
	}

	$sHashFlag = '#' . $sText;
	if ($bImage === TRUE)
	{
		$sHashFlag .= ' <img src="/hashflags/' . $sText . '.png" alt="' .
			$sText . '" class="hashflag">';
	}

	return ($sHashFlag);
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
function NewRequests ($iUserID)
/*****************************************************************************/
{
	$arReturn = array();

	$query_new = "SELECT
			request_id
		FROM `fst_request`
		WHERE (user_id_recipient='" . $iUserID . "')
		AND (request_status='2')
		ORDER BY request_adddate DESC";
	$result_new = Query ($query_new);
	while ($row_new = mysqli_fetch_assoc ($result_new))
	{
		array_push ($arReturn, $row_new['request_id']);
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

		$arNewRequests = NewRequests ($iUserID);
		$iNotifications += count ($arNewRequests);
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
		'<span style="display:inline-block; padding-left:$1px;">', $sReturn);
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
function Pref ($sPref)
/*****************************************************************************/
{
	/*** Returns 0 or 1 for 'user_pref_nsfw'. ***/

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
function CWidth ($iCWidth)
/*****************************************************************************/
{
	switch ($iCWidth)
	{
		case 0: $sCWidth = 'auto (default)'; break;
		case 1: $sCWidth = '100%'; break;
		case 2: $sCWidth = '100% - 50px'; break;
		case 3: $sCWidth = '100% - 100px'; break;
		case 4: $sCWidth = '100% - 150px'; break;
		case 5: $sCWidth = '2290px'; break;
		case 6: $sCWidth = '1730px'; break;
		case 7: $sCWidth = '1170px'; break;
		case 8: $sCWidth = '970px'; break;
		case 9: $sCWidth = '750px'; break;
		case 10: $sCWidth = '650px'; break;
		case 11: $sCWidth = '550px'; break;
		case 12: $sCWidth = '450px'; break;
		case 13: $sCWidth = '350px'; break;
		default: $sCWidth = FALSE; break;
	}

	return ($sCWidth);
}
/*****************************************************************************/
function TSize ($iTSize)
/*****************************************************************************/
{
	switch ($iTSize)
	{
		case 100: $sTSize = '100%, 320x180'; break;
		case 90: $sTSize = '90%, 288x162'; break;
		case 80: $sTSize = '80%, 256x144 (default)'; break;
		case 70: $sTSize = '70%, 224x126'; break;
		case 60: $sTSize = '60%, 192x108'; break;
		case 50: $sTSize = '50%, 160x90'; break;
		default: $sTSize = FALSE; break;
	}

	return ($sTSize);
}
/*****************************************************************************/
function GetRequestedInfo ($iUserID, $iType)
/*****************************************************************************/
{
	switch ($iType)
	{
		case 1: $sInformation = GetUserInfo ($iUserID, 'user_email'); break;
		default: $sInformation = FALSE; break;
	}

	return ($sInformation);
}
/*****************************************************************************/
function ForumDate ($sDateTime)
/*****************************************************************************/
{
	if (($sDateTime == '1970-01-01 00:00:00') || ($sDateTime == NULL))
		{ return ('-'); }

	$sDate = date ('d M y', strtotime ($sDateTime));
	$sTime = date ('H:i', strtotime ($sDateTime));

	return ($sDate . ' <span style="font-size:12px;">(' . $sTime . ')</span>');
}
/*****************************************************************************/
function PollOpen ($iPollID)
/*****************************************************************************/
{
	/* Returns one of:
	 * -1 (nonexistent)
	 * TRUE (forever open)
	 * FALSE (closed)
	 * The datetime when it will close.
	 */

	$query_poll = "SELECT
			poll_nrdays,
			poll_dt
		FROM `fst_poll`
		WHERE (poll_id='" . $iPollID . "')";
	$result_poll = Query ($query_poll);
	if (mysqli_num_rows ($result_poll) == 1)
	{
		$row_poll = mysqli_fetch_assoc ($result_poll);
		$iNrDays = intval ($row_poll['poll_nrdays']);
		$sStartDT = $row_poll['poll_dt'];
		$sEndDT = date ('Y-m-d H:i:s', strtotime ($sStartDT .
			' +' . $iNrDays . ' day'));
		$sNowDT = date ('Y-m-d H:i:s');

		if ($iNrDays == 0)
		{
			$sOpen = TRUE;
		} else {
			if ($sNowDT < $sEndDT)
			{
				$sOpen = $sEndDT;
			} else {
				$sOpen = FALSE;
			}
		}
	} else { $sOpen = -1; }

	return ($sOpen);
}
/*****************************************************************************/
function PollVoted ($iPollID, $iUserID, $sIP)
/*****************************************************************************/
{
	/*** Returns 0 (not voted), 1 (user voted), or 2 (IP voted). ***/

	/*** user voted ***/
	$query_voted = "SELECT
			vote_id
		FROM `fst_vote`
		WHERE (poll_id='" . $iPollID . "')
		AND (user_id='" . $iUserID . "')";
	$result_voted = Query ($query_voted);
	if (mysqli_num_rows ($result_voted) != 0) { return (1); }

	/*** IP voted ***/
	$query_voted = "SELECT
			vote_id
		FROM `fst_vote`
		WHERE (poll_id='" . $iPollID . "')
		AND (vote_ip='" . $sIP . "')";
	$result_voted = Query ($query_voted);
	if (mysqli_num_rows ($result_voted) != 0) { return (2); }

	/*** not voted ***/
	return (0);
}
/*****************************************************************************/
function NewestFirst ($arTopic1, $arTopic2)
/*****************************************************************************/
{
	return (strtotime ($arTopic2['lastupdate_date']) -
		strtotime ($arTopic1['lastupdate_date']));
}
/*****************************************************************************/
function Topics ($iBoard, $iUserID, $iJustNewContent)
/*****************************************************************************/
{
	/*** Returns FALSE if the board has no topics. ***/

	$query_topics = "SELECT
			fv.video_id,
			fv.video_title,
			fu.user_username AS created_username,
			fv.video_adddate AS created_date,
			fv.video_views,
			fv.video_comments_allow
		FROM `fst_video` fv
		LEFT JOIN `fst_user` fu
			ON fv.user_id = fu.user_id
		WHERE (video_istext='3')
		AND (board_id='" . $iBoard . "')
		AND (video_deleted='0')";
	$result_topics = Query ($query_topics);
	if (mysqli_num_rows ($result_topics) != 0)
	{
		$iTopic = 0;
		while ($row_topics = mysqli_fetch_assoc ($result_topics))
		{
			$arTopics[$iTopic] = array();

			$arTopics[$iTopic]['video_id'] = intval ($row_topics['video_id']);
			$arTopics[$iTopic]['code'] = IDToCode ($arTopics[$iTopic]['video_id']);
			$arTopics[$iTopic]['video_title'] = $row_topics['video_title'];
			/***/
			$arTopics[$iTopic]['created_username'] = $row_topics['created_username'];
			$arTopics[$iTopic]['created_date'] = $row_topics['created_date'];
			/***/
			$arTopics[$iTopic]['video_views'] = intval ($row_topics['video_views']);
			$arTopics[$iTopic]['video_comments_allow'] =
				intval ($row_topics['video_comments_allow']);

			$iTopic++;
		}

		/*** $arReplies ***/
		if ($iJustNewContent == 0)
		{
			$arReplies = array();
			$query_replies = "SELECT
					fc.video_id,
					COUNT(*) AS nr_replies
				FROM `fst_comment` fc
				LEFT JOIN `fst_video` fv
					ON fc.video_id=fv.video_id
				WHERE (fv.board_id='" . $iBoard . "')
				AND (fv.video_deleted='0')
				AND (fc.comment_hidden='0')
				GROUP BY video_id";
			$result_replies = Query ($query_replies);
			if (mysqli_num_rows ($result_replies) != 0)
			{
				while ($row_replies = mysqli_fetch_assoc ($result_replies))
				{
					$iVideoID = intval ($row_replies['video_id']);
					$iNrReplies = intval ($row_replies['nr_replies']);
					$arReplies[$iVideoID] = $iNrReplies;
				}
			}
		}

		/*** $arLastReplyUsername and $arLastReplyDate ***/
		$arLastReplyUsername = array();
		$arLastReplyDate = array();
		$query_last = "SELECT
				fc.video_id,
				fu.user_username AS lastreply_username,
				fc.comment_adddate AS lastreply_date
			FROM `fst_comment` fc
			LEFT JOIN `fst_video` fv
				ON fc.video_id=fv.video_id
			LEFT JOIN `fst_user` fu
				ON fc.user_id=fu.user_id
			WHERE (fc.comment_id IN (
				SELECT
					MAX(fc.comment_id)
				FROM `fst_comment` fc
				LEFT JOIN `fst_video` fv
					ON fc.video_id=fv.video_id
				LEFT JOIN `fst_user` fu
					ON fc.user_id=fu.user_id
				WHERE (fv.board_id='" . $iBoard . "')
				AND (fv.video_deleted='0')
				AND (fc.comment_hidden='0')
				GROUP BY fv.video_id))";
		$result_last = Query ($query_last);
		if (mysqli_num_rows ($result_last) != 0)
		{
			while ($row_last = mysqli_fetch_assoc ($result_last))
			{
				$iVideoID = intval ($row_last['video_id']);
				/***/
				$sLastReplyUsername = $row_last['lastreply_username'];
				$arLastReplyUsername[$iVideoID] = $sLastReplyUsername;
				/***/
				$sLastReplyDate = $row_last['lastreply_date'];
				$arLastReplyDate[$iVideoID] = $sLastReplyDate;
			}
		}

		/*** $arLastViewed ***/
		if ($iUserID != 0)
		{
			$arLastViewed = array();
			$query_lastviewed = "SELECT
					video_id,
					commentslastviewed_dt
				FROM `fst_commentslastviewed`
				WHERE (user_id='" . $iUserID . "')";
			$result_lastviewed = Query ($query_lastviewed);
			if (mysqli_num_rows ($result_lastviewed) != 0)
			{
				while ($row_lastviewed = mysqli_fetch_assoc ($result_lastviewed))
				{
					$iVideoID = intval ($row_lastviewed['video_id']);
					$sLastViewedDT = $row_lastviewed['commentslastviewed_dt'];
					/***/
					$arLastViewed[$iVideoID] = $sLastViewedDT;
				}
			}
		} else { $arLastViewed = FALSE; }

		foreach ($arTopics as $iKey => $arTopic)
		{
			/*** nr_replies ***/
			if (isset ($arReplies[$arTopic['video_id']]))
			{
				$arTopics[$iKey]['nr_replies'] = $arReplies[$arTopic['video_id']];
			} else {
				$arTopics[$iKey]['nr_replies'] = 0;
			}

			/*** last_reply and lastupdate_date ***/
			if ((isset ($arLastReplyUsername[$arTopic['video_id']])) &&
				(isset ($arLastReplyDate[$arTopic['video_id']])))
			{
				$arTopics[$iKey]['lastreply_username'] =
					$arLastReplyUsername[$arTopic['video_id']];
				$arTopics[$iKey]['lastreply_date'] =
					$arLastReplyDate[$arTopic['video_id']];
				$arTopics[$iKey]['last_reply'] =
					ForumDate ($arTopics[$iKey]['lastreply_date']) .
					' by <a href="/user/' .
					$arTopics[$iKey]['lastreply_username'] . '">' .
					$arTopics[$iKey]['lastreply_username'] . '</a>';
				/***/
				$arTopics[$iKey]['lastupdate_date'] =
					$arTopics[$iKey]['lastreply_date'];
			} else {
				$arTopics[$iKey]['last_reply'] = '-';
				/***/
				$arTopics[$iKey]['lastupdate_date'] =
					$arTopics[$iKey]['created_date'];
			}

			/*** new_content ***/
			if (($arLastViewed !== FALSE) &&
				((!isset ($arLastViewed[$arTopic['video_id']])) ||
				(strtotime ($arLastViewed[$arTopic['video_id']]) <
				strtotime ($arTopics[$iKey]['lastupdate_date']))))
			{
				$arTopics[$iKey]['new_content'] = TRUE;
			} else {
				$arTopics[$iKey]['new_content'] = FALSE;
			}
		}

		usort ($arTopics, 'NewestFirst');
	} else {
		$arTopics = FALSE;
	}

	return ($arTopics);
}
/*****************************************************************************/
function UpdateCountLikesVideo ($iVideoID)
/*****************************************************************************/
{
	$query_update = "UPDATE `fst_video` fv SET
			video_likes=(
				SELECT
					COUNT(*)
				FROM `fst_likevideo` fl
				WHERE (fl.video_id='" . $iVideoID . "')
			)
		WHERE (fv.video_id='" . $iVideoID . "')";
	Query ($query_update);
}
/*****************************************************************************/
function UpdateCountLikesMBPost ($iPostID)
/*****************************************************************************/
{
	$query_update = "UPDATE `fst_microblog_post` fm SET
			mbpost_likes=(
				SELECT
					COUNT(*)
				FROM `fst_likembpost` fl
				WHERE (fl.mbpost_id='" . $iPostID . "')
			)
		WHERE (fm.mbpost_id='" . $iPostID . "')";
	Query ($query_update);
}
/*****************************************************************************/
function FewerNotif ($sUserFrom, $sUserTo)
/*****************************************************************************/
{
	$sReturn = FALSE;

	$query_fewer = "SELECT
			user_pref_musers
		FROM `fst_user`
		WHERE (user_username='" . mysqli_real_escape_string
			($GLOBALS['link'], $sUserTo) . "')";
	$result_fewer = Query ($query_fewer);
	if (mysqli_num_rows ($result_fewer) == 1)
	{
		$row_fewer = mysqli_fetch_assoc ($result_fewer);
		$sMUsers = $row_fewer['user_pref_musers'];
		if ($sMUsers != '')
		{
			$arMUsers = preg_split ('/[\n\r]+/', $sMUsers);
			if (in_array ($sUserFrom, $arMUsers) === TRUE) { $sReturn = TRUE; }
		}
	}

	return ($sReturn);
}
/*****************************************************************************/
function HasReblogged ($iUserID, $iPostID)
/*****************************************************************************/
{
	$query_reblogged = "SELECT
			mbpost_id
		FROM `fst_microblog_post`
		WHERE (user_id='" . $iUserID . "')
		AND (mbpost_id_reblog='" . $iPostID . "')
		AND (mbpost_hidden='0')";
	$result_reblogged = Query ($query_reblogged);
	if (mysqli_num_rows ($result_reblogged) == 1)
		{ $bReblogged = TRUE; } else { $bReblogged = FALSE; }

	return ($bReblogged);
}
/*****************************************************************************/
function GetMBPostHidden ($iPostID, $bIcon)
/*****************************************************************************/
{
$sHTML = '
<span id="hidden-' . $iPostID . '">
<a name="hidden" href="javascript:;" style="display:inline-block;">
';

	if ($bIcon === TRUE)
	{
		$sHTML .=
			'<img src="/images/hidden_off.png" title="remove" alt="hidden off">';
	} else {
		$sHTML .= 'remove reblog';
	}

$sHTML .= '
</a>
</span>
';

	return ($sHTML);
}
/*****************************************************************************/
function GetMBPostReported ($iPostID, $sUsername)
/*****************************************************************************/
{
$sHTML = '
<a target="_blank" href="/contact.php?mbuser=' .
	Sanitize ($sUsername) . '&mbpost=' . $iPostID . '">
<img src="/images/reported_off.png" title="report" alt="reported off">
</a>
';

	return ($sHTML);
}
/*****************************************************************************/
function GetMBPostReblogged ($iPostID, $sUsername, $bReblogged, $iReblogs)
/*****************************************************************************/
{
	if (isset ($_SESSION['fst']['user_username']))
	{
		$sURL = '/user/' . $_SESSION['fst']['user_username'] . '?rbuser=' .
			Sanitize ($sUsername) . '&rbpost=' . $iPostID . '#microblog';
	} else {
		$sURL = '/signin/';
	}

	if ($bReblogged === FALSE)
	{
$sHTML = '
<a href="' . $sURL . '">
<img src="/images/reblogged_off.png" title="reblog" alt="reblogged off">
</a>
';
	} else {
		$sHTML = '<img src="/images/reblogged_on.png"' .
			' title="reblog" alt="reblogged on">';
	}
	if ($iReblogs > 0)
	{
		$sHTML .= '<span id="reblogs-' . $iPostID . '" class="reblogs">';
		$sHTML .= $iReblogs;
		$sHTML .= '</span>';
	}

	return ($sHTML);
}
/*****************************************************************************/
function GetMBPostLiked ($iPostID, $bAuthor, $bLiked, $iLikes)
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
<span id="liked-' . $iPostID . '">
<a name="liked" href="javascript:;" style="display:inline-block;">
<img src="/images/liked_off.png" title="like" alt="liked off">
</a>
</span>
';
		}
	} else {
		$sHTML .= ' <img src="/images/liked_off.png" title="like" alt="liked off">';
	}
	$sHTML .= '<span id="likes-' . $iPostID .
		'" class="likes">';
	if ($iLikes > 0) { $sHTML .= $iLikes; }
	$sHTML .= '</span>';

	return ($sHTML);
}
/*****************************************************************************/
function GetMBPost ($iPostID, $iVisitorID, $bReblog, $iActions)
/*****************************************************************************/
{
	/*** Returns a microblog post or FALSE (if result not 1 row). ***/

	$query_post = "SELECT
			fm.user_id,
			fm.mbpost_dt,
			fm.mbpost_text,
			fm.mbpost_likes,
			fm.mbpost_reblogs,
			fm.mbpost_id_reblog,
			fu.user_username,
			fu.user_patron
		FROM `fst_microblog_post` fm
		LEFT JOIN `fst_user` fu
			ON fm.user_id = fu.user_id
		WHERE (fm.mbpost_id='" . $iPostID . "')
		AND (fm.mbpost_hidden='0')";
	$result_post = Query ($query_post);
	if (mysqli_num_rows ($result_post) == 1)
	{
		$row_post = mysqli_fetch_assoc ($result_post);
		/***/
		$iBloggerID = intval ($row_post['user_id']);
		$sDT = $row_post['mbpost_dt'];
		$sDate = date ('j F Y (H:i)', strtotime ($sDT));
		$sText = $row_post['mbpost_text'];
		$iLikes = intval ($row_post['mbpost_likes']);
		$iReblogs = intval ($row_post['mbpost_reblogs']);
		$iReblogID = intval ($row_post['mbpost_id_reblog']);
		$sUsername = $row_post['user_username'];
		$iPatron = intval ($row_post['user_patron']);
		/***/
		if ($bReblog === TRUE)
			{ $sClass = 'reblog'; } else { $sClass = 'mbpost'; }
		/***/
		$bReblogged = HasReblogged ($iVisitorID, $iPostID);
		/***/
		$query_liked = "SELECT
				likembpost_id
			FROM `fst_likembpost`
			WHERE (mbpost_id='" . $iPostID . "')
			AND (user_id='" . $iVisitorID . "')";
		$result_liked = Query ($query_liked);
		if (mysqli_num_rows ($result_liked) == 1)
			{ $bLiked = TRUE; } else { $bLiked = FALSE; }
		/***/
		if ($iBloggerID == $iVisitorID)
			{ $bAuthor = TRUE; } else { $bAuthor = FALSE; }
		/***/
		if ($iPatron == 1)
			{ $sPatron = PatronStar(); } else { $sPatron = ''; }
		/***/
		$sStatusURL = '/status/' . $sUsername . '/' . $iPostID;

$sHTML = '
<div id="post-' . $iPostID . '" class="' . $sClass . '">
<span style="display:block; float:left; width:60px;">
' . GetUserAvatar ($sUsername, 'small', 1) . '
</span>
<div style="float:left; width:calc(100% - 60px);">
<a href="/user/' . $sUsername . '">' . $sUsername . '</a>' . $sPatron . ' · <a href="' . $sStatusURL . '" style="font-size:12px; color:#666; font-weight:normal;">' . $sDate . '</a>
<br>
';

		if (($iReblogID == 0) || (($iReblogID != 0) && (strlen ($sText) != 0)))
		{
$sHTML .= '
<span style="display:block; word-break:break-word; padding:5px 0;">
' . nl2br (HashTags (Sanitize ($sText))) . '
</span>
';
		}

		if (($iReblogID != 0) && ($bReblog === FALSE))
		{
			if (strlen ($sText) == 0)
			{
				$sMBPost = GetMBPost ($iReblogID, $iVisitorID, TRUE, 2);
				if ($sMBPost !== FALSE)
				{
					$sHTML .= $sMBPost;
				} else {
$sHTML .= '
<div class="reblog" style="font-style:italic;">
This microblog post has been deleted.
</div>
';
				}
				$iActions = 1;
			} else {
				$sMBPost = GetMBPost ($iReblogID, $iVisitorID, TRUE, 1);
				if ($sMBPost !== FALSE)
				{
					$sHTML .= $sMBPost;
				} else {
$sHTML .= '
<div class="reblog" style="font-style:italic;">
This microblog post has been deleted.
</div>
';
				}
			}
		}

		switch ($iActions)
		{
			case 1:
				if ($bAuthor === TRUE)
					{ $sHTML .= GetMBPostHidden ($iPostID, FALSE); }
				break;
			case 2:
				if ($bAuthor === TRUE)
					{ $sHTML .= GetMBPostHidden ($iPostID, TRUE); }
				$sHTML .= GetMBPostReported ($iPostID, $sUsername);
				$sHTML .= GetMBPostReblogged ($iPostID, $sUsername,
					$bReblogged, $iReblogs);
				$sHTML .= GetMBPostLiked ($iPostID, $bAuthor, $bLiked, $iLikes);
				break;
		}

$sHTML .= '
</div>
<span style="display:block; clear:both;"></span>
</div>
';

		return ($sHTML);
	} else {
		return (FALSE);
	}
}
/*****************************************************************************/
function GetReblogID ($iPostID)
/*****************************************************************************/
{
	/* Returns an ID (which may be 0) or FALSE for unknown posts.
	 * Ignores the mbpost_hidden status.
	 */

	$query_id = "SELECT
			mbpost_id_reblog
		FROM `fst_microblog_post`
		WHERE (mbpost_id='" . $iPostID . "')";
	$result_id = Query ($query_id);
	if (mysqli_num_rows ($result_id) == 0)
	{
		$iID = FALSE;
	} else {
		$row_id = mysqli_fetch_assoc ($result_id);
		$iID = intval ($row_id['mbpost_id_reblog']);
	}

	return ($iID);
}
/*****************************************************************************/
function PostJavaScript ()
/*****************************************************************************/
{
	/* Functions MicroBlogPosts(), HidePost() and LikePost() reside in
	 * js/fst.js.
	 */

$sHTML = '
<script>
$("body").on("click", "[name=\"hidden\"]", function(event) {
	event.stopImmediatePropagation(); /*** TODO ***/
	if (confirm ("Remove post?")){
		var this_id = $(this).closest("span").attr("id");
		post_id = this_id.replace("hidden-","");
		var csrf_token = "' . $_SESSION['fst']['csrf_token'] . '";
		HidePost (post_id, csrf_token);
	}
});

$("body").on("click", "[name=\"liked\"]", function(event){
	event.stopImmediatePropagation(); /*** TODO ***/
	var this_id = $(this).closest("span").attr("id");
	post_id = this_id.replace("liked-","");
	var csrf_token = "' . $_SESSION['fst']['csrf_token'] . '";
	LikePost (post_id, csrf_token);
});
</script>
';

	return ($sHTML);
}
/*****************************************************************************/
function PatronStar ()
/*****************************************************************************/
{
$sHTML = '
<a href="/patronage/" style="text-decoration:none;">
<img src="/images/patronage.png" title="patron" alt="patronage" style="vertical-align:text-bottom;">
</a>
';

	return ($sHTML);
}
/*****************************************************************************/
function PatronBlock ($iYear)
/*****************************************************************************/
{
$sHTML = '
<div class="patronage-div">
<a href="/patronage/" style="text-decoration:none;">
<span class="patronage-span">
<img src="/images/patronage.png" alt="patronage" style="margin-right:5px;">
' . $iYear . ' patron
</span>
</a>
</div>
';

	return ($sHTML);
}
/*****************************************************************************/
function MicroBlogIcons ($iActive)
/*****************************************************************************/
{
	$sHTML = '';

	if (isset ($_SESSION['fst']['user_id']))
	{
		$sUsername = $_SESSION['fst']['user_username'];

		$sHTML .= '<div id="mbicons">';

		/*** 1 ***/
$sHTML .= '
<a href="/timeline/" style="text-decoration:none; margin-right:5px;">
<img src="/images/icon32_timeline.png" title="timeline" alt="timeline">
</a>
';

		/*** 2 ***/
$sHTML .= '
<a href="/user/' . $sUsername . '#microblog" style="text-decoration:none; margin-right:5px;">
<img src="/images/icon32_post.png" title="post" alt="post">
</a>
';

		/*** 3 ***/
$sHTML .= '
<a href="/explore/" style="text-decoration:none;">
<img src="/images/icon32_explore.png" title="explore" alt="explore">
</a>
';

		$sHTML .= '</div>';
	}

	return ($sHTML);
}
/*****************************************************************************/
function GetTrendingDates ($sCurDate, $sActDate)
/*****************************************************************************/
{
	$sHTML = '';

	$iCurYear = date ('Y', strtotime ($sCurDate));
	$iActYear = date ('Y', strtotime ($sActDate));

	$query_dates = "SELECT
			DISTINCT(trending_date)
		FROM `fst_trending`
		WHERE (YEAR(trending_date) = '" . $iActYear . "')
		ORDER BY trending_date DESC";
	$result_dates = Query ($query_dates);
	while ($row_dates = mysqli_fetch_assoc ($result_dates))
	{
		$sDateOption = $row_dates['trending_date'];

		$sHTML .= '<option value="' . $sDateOption . '"';
		if ($sDateOption == $sActDate)
			{ $sHTML .= ' selected'; }
		$sHTML .= '>';
		if ($sDateOption == $sCurDate)
			{ $sHTML .= 'today'; } else { $sHTML .= $sDateOption; }
		$sHTML .= '</option>' . "\n";
	}

	return ($sHTML);
}
/*****************************************************************************/
function IfMaintenanceShow ()
/*****************************************************************************/
{
	if ($GLOBALS['maintenance'] === TRUE)
	{
		/*** Do NOT center the div. ***/
$sHTML = '
<div style="color:#00f; margin:10px 0; font-size:16px;">
The website is currently in maintenance mode.
<br>
Users can not login. Please check back later.
</div>
';

		print ($sHTML);
	}
}
/*****************************************************************************/

HasRequired();
StartSession();
MySQLConnect();
if (isset ($_SESSION['fst']['user_id'])) { CheckIfBanned(); }
if (isset ($_SESSION['fst']['user_id'])) { CheckIfMaintenance(); }
?>
