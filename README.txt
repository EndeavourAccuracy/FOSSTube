===[CONTENTS]==================================================================

1 - ABOUT
2 - LICENSE/DISCLAIMER
3 - NON-EXHAUSTIVE FEATURE LIST
4 - INSTALLATION AND SET UP

===[1 - ABOUT]=================================================================

FSTube v1.4 (December 2021)
Copyright (C) 2020-2021 Norbert de Jonge <mail@norbertdejonge.nl>

A free and open-source video sharing content management system.

The FSTube website can be found at [ https://www.fstube.org/ ].
Its GitHub repository at [ https://github.com/FSTube/FSTube ].

===[2 - LICENSE/DISCLAIMER]====================================================

This software is provided 'as-is', without any express or implied
warranty.  In no event will the authors be held liable for any damages
arising from the use of this software.

Permission is granted to anyone to use this software for any purpose,
including commercial applications, and to alter it and redistribute it
freely, subject to the following restrictions:

1. The origin of this software must not be misrepresented; you must not
   claim that you wrote the original software. If you use this software
   in a product, an acknowledgment in the product documentation would be
   appreciated but is not required.
2. Altered source versions must be plainly marked as such, and must not be
   misrepresented as being the original software.
3. This notice may not be removed or altered from any source distribution.

--------------------
The above (FSF and OSI approved) zlib license is only for FSTube itself.
Distro, website and platform icons are property of their respective owners.
Bootstrap 3.3.7: MIT
HTML5 Shiv 3.7.3: MIT/GPL2
IE10 viewport hack: MIT
jQuery 3.3.1: MIT
noUiSlider 14.0.3: MIT
Respond.js 1.4.2: MIT
Swift Mailer 4.3.0: LGPL3
wNumb 1.2.0: MIT
PHP QR Code 1.1.4: LGPL3
[Used only for spherical videos:] Video.js (VR) 7.13.3 (1.8.0): Apache 2.0

===[3 - NON-EXHAUSTIVE FEATURE LIST]===========================================

* Users can publish videos (accepts all video formats, HTML5 video playback).
* Users can publish text, such as blog posts, with (previewable) BBCode markup.
* Forum, with boards and topics.
* Responsive (mobile/tablet friendly) design.
* Comments, including conversation threading.
* Social media integration (share thumbnails; Open Graph and Twitter Cards).
* User profile pages, with avatars and custom text.
* Public and private folders for bookmarking and creating collections.
* Both automatically extracted and custom thumbnails.
* Channel subscriptions.
* Notifications, for new content, comments/replies, admin messages.
* Content titles, descriptions, tags, categories, restrictions, languages.
* Per-content comment settings (on, off, only approved).
* Content and comments can be liked (thumbs up).
* Real-time switching between sizes (360p, 720p, 1080p).
* User roles (owners, admins, mods) and optional per-user privileges.
* Per-user monetization options (processors, cryptocurrencies), including a modal dialog.
* Sitewide 4-byte UTF-8 Unicode support (inc. emoji).
* Search, with autocomplete and hits preview.
* Advanced search page, with optional user filter, custom ordering, etc.
* Extensive content reporting options, a moderation queue, and banning options.
* Content pagination.
* Server-side transcoding queue.
* Subtitle support (via WebVTT).
* Sitewide and per-user RSS feeds.
* Multiple file upload support, with progress bars.
* Extensive admin panel, with user messaging, video purging, Tor blocking, etc.
* On-hover video previews.
* Sitewide and per-user referrer saving.
* Users can put up videos for adoption.
* Optional iframe embedding of videos.
* Hotlink protection.
* HTML5 valid pages (Nu Html Checker).
* Loop functionality, including customized links.
* Home page filters: views threshold, (not) safe for work.
* Extracts and displays video durations and average FPSs; counts views.
* Publishers can love/pin comments, (un)mute users.
* Lots of security measures, such as hashed passwords created on-site, sanitization, tokens, CAPTCHAs, logging of failed login attempts, and verification codes, to protect against CSRF, SQL injection, XSS, and brute-force attacks.
* User content is sortable by date, views, likes, comments, and durations.
* Auto-generation of related content.
* Live, development and maintenance modes.
* Feedback forms (general, when deleting accounts).
* IPv6 support.
* Clickable time stamps.
* Optional night mode.
* Moderators can move forum topics and can change (N)SFW statuses.
* Sticky (hideable) floatable videos when scrolling down long pages.
* Forgot username/password functionality.
* Users can change their email addresses, passwords, and usernames.
* Video playback speed control.
* Users can browse their comment history and subscriptions.
* User preferences: show NSFW content, home container width, thumbnail size, fewer notifications about certain users.
* Users can send and receive information requests (email address).
* Admins can change forum topic titles, and lock forum topics.
* Customizable 404 Not Found page.
* Disallows users from adding duplicate videos to the same account (MD5), and the video overview lists duplicate videos on other accounts (again, MD5).
* Polls can be attached to forum topics.
* Support for spherical videos (with Video.js).
* Highlighting of unread topics and comments/replies.
* Twitter-like microblogging on user pages, including likes, reblogs, following and timeline, hashtags and hashflags, status pages, and explore (search).
* Featured content.
* Trending page, based on daily top 10 logs.

===[4 - INSTALLATION AND SET UP]===============================================

--------------------
Before you get started
--------------------
FSTube is fairly easy to install and set up, but it requires at least some knowledge of GNU/Linux. If you have no experience with GNU/Linux, I urge you to ask a system administrator to get FSTube up and running for you.

FSTube is an asynchronous web application that heavily uses Ajax, which requires visitors to have JavaScript enabled. FSTube is NOT a decentralized, federated or peer-to-peer application. FSTube transcodes to MP4 H.264. Account creation requires a valid email address.

An example website that uses FSTube:
https://www.freespeechtube.org/

--------------------
Requirements
--------------------
* Apache HTTP Server 2
* MySQL/MariaDB
* PHP 8 (or 7)
* FFmpeg (ffmpeg + ffprobe)
* The ability to set up and run cronjobs.
* An SMTP account, preferably with an accurate SPF-record.
* A domain name.
* A proper server. This is subjective, but I suggest using 4+ CPUs, 8G+ RAM, 100G+ SSD, and being allowed to have 2TB+ monthly traffic.

--------------------
Settings and customization
--------------------
The assumption is that FSTube will be installed in the root of your domain. Running FSTube from a subdirectory is completely untested. You may attempt doing so, e.g. by including a <base> in HTMLStart(), but if you do, you are on your own.

You must have a non-public directory (such as private/) on the same level as your website directory (such as public_html/ or www/). Virtually all accounts have this nowadays. If your account does not, (ask your webmaster to) create one.

First, check if any addendums have been issued for this text:
https://www.fstube.org/addendums/1.4/

Add
character-set-server=utf8mb4
to your MySQL configuration.
$ sudo vim /etc/mysql/mysql.conf.d/mysqld.cnf
At the end of the [mysqld] section, add:
character-set-server=utf8mb4
$ sudo service mysql restart

Edit FSTube.sql to change all instances of "YOURPASS", and optionally also change the name of forum board "FSTube".
Then use FSTube.sql to create all MySQL tables.

Enable various Apache and PHP extensions:
$ sudo a2enmod rewrite
$ sudo a2enmod headers
$ sudo apt install php-mysql
$ sudo apt install php-mbstring
$ sudo apt install php-gd
$ sudo apt install php-cli
$ sudo service apache2 restart

Modify various PHP settings:
$ sudo vim /etc/php/8.x/apache2/php.ini
file_uploads = On
max_execution_time = 1500
max_input_time = 60
max_input_vars = 1000
memory_limit = 500M
post_max_size = 500M
session.gc_maxlifetime = 1440
upload_max_filesize = 500M
zlib.output_compression = Off
$ sudo service apache2 restart

Modify all "CHANGE" in private/fst_db.php.

Modify all settings in private/fst_settings.php.
If, for security purposes, you renamed the directories "swift_random" and "phpqrcode_random", also change it in the above file.

Modify public_html/.htaccess.
At the very least the two instances of "yourdomain.com".
Also, if you put $GLOBALS['live'] on FALSE in fst_settings.php, then add "#" in front of all lines under "# DO NOT USE ON LOCALHOST".

Edit public_html/templates/default.htm and replace "YOURIMAGE" with a Base64 image. You can do this by using a search engine to find a 'base64 image encoder' and then create something like this:
<img src="data:image/png;base64,..." alt="yourdomain">

Modify HTML text:
* public_html/about/about.html
* public_html/terms/terms.html
* public_html/privacy/privacy.html
* public_html/mod/guidelines.html
* public_html/404.html
* public_html/patronage/patronage.html

Modify images:
* All images in the directory public_html/images/favicons/
* public_html/images/yourdomain_208x30.png
* public_html/images/avatar_small.png
* public_html/images/avatar_large.png
* public_html/images/back_01.jpg
* public_html/images/back_02.jpg

Create two cronjobs that run every minute (all *):
php (path)/private/fst_encode.php >/dev/null 2>&1
php (path)/private/fst_actions.php >/dev/null 2>&1
Obviously, replace (path) with the full path.

Upload all private/ and public_html/ files.

Create an administrator account (you specified their usernames in fst_settings.php), and login. Then click "Admin" to make sure no red error messages show up.

Questions? Contact info@fstube.org.
