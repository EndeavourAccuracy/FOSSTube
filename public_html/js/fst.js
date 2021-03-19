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

function BackSignUp () {
	$.ajax({
		type: "POST",
		url: "/signup/step_back.php",
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				location.reload();
			} else {
				alert (error);
			}
		},
		error: function() {
			alert ("Error calling step_back.php.");
		}
	});
}

function BackForgot () {
	$.ajax({
		type: "POST",
		url: "/forgot/step_back.php",
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				location.reload();
			} else {
				alert (error);
			}
		},
		error: function() {
			alert ("Error calling step_back.php.");
		}
	});
}

function BackAccount () {
	$.ajax({
		type: "POST",
		url: "/account/step_back.php",
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			if (result == 1)
			{
				location.reload();
			} else {
				alert (error);
			}
		},
		error: function() {
			alert ("Error calling step_back.php.");
		}
	});
}

function Comments (comment) {
	var code = $("#comments").data("code");
	$.ajax({
		type: "POST",
		url: "/v/comments.php",
		data: ({
			code : code,
			comment : comment
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			var html = data["html"];
			if (result == 1)
			{
				$("#comments").html(html);
			} else {
				$("#comment-error").html(error);
			}
		},
		error: function() {
			$("#comment-error").html("Error calling comments.php.");
		}
	});
}

function CommentToggle (toggle, comment_id, csrf_token) {
	$.ajax({
		type: "POST",
		url: "/v/toggle.php",
		data: ({
			toggle : toggle,
			comment_id : comment_id,
			csrf_token : csrf_token
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			var state = data["state"];
			if (result == 1)
			{
				$("#" + toggle + "-" + comment_id).html("<a name=\"" + toggle + "\" href=\"javascript:;\" style=\"display:inline-block;\"><img src=\"/images/" + toggle + "_" + state + ".png\" alt=\"" + toggle + " " + state + "\"></a>");
				if ((toggle == "pinned") || (toggle == "hidden") || (toggle == "muted")) { Comments (0); }
				if (toggle == "approved")
				{
					if (state == "on") { content = ""; } else { content = "As the content publisher, you can press the gray check mark to approve this comment."; }
					$("#approved-hint-" + comment_id).html(content);
				}
			} else {
				alert (error);
			}
		},
		error: function() {
			alert ("Error calling toggle.php.");
		}
	});
}

function VideosJS (divid, section, order, offset,
	user, searcht, searcha, filters) {
/***
	var filtersa = [];
	$.each(filters, function(key,value) {
		var str = key + ":" + value;
		filtersa.push(str);
	});
***/
	$.ajax({
		type: "POST",
		url: "/videos.php",
		data: ({
			section : section,
			order : order,
			offset : offset,
			user : user,
			searcht : searcht,
			searcha : searcha,
			filters : filters
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			var html = data["html"];
			if (result == 1)
			{
				$("#" + divid).html(html);
			} else {
				alert (error);
			}
		},
		error: function(xhr, status, error) {
			console.log(xhr.responseText);
			console.log(status);
			console.log(error);
			alert ("Error calling videos.php.");
		}
	});
}

function UpdateSearchList (keycode, search_query) {
	if ((keycode == 37) || (keycode == 38) || (keycode == 39) || (keycode == 40))
		{ return false; }

	if (search_query.length < 3)
	{
		$("#matches_title").html("");
		$("#matches_any").html("");
		$("#search_list").html("");
		$("#search_query").css("background-color","#f8f8ff");
		return false;
	}

	$.ajax({
		type: "POST",
		url: "/searchlist.php",
		data: ({
			search_query : search_query
		}),
		dataType: "json",
		success: function(data) {
			var result = data["result"];
			var error = data["error"];
			var html = data["html"];
			var matches_title = data["matches_title"];
			var matches_any = data["matches_any"];
			if (result == 1)
			{
				$("#matches_title").html(matches_title);
				$("#matches_any").html("(+" + (matches_any - matches_title) + ")");
				$("#search_list").html(html);
				if ((matches_title == 0) && (matches_any == 0))
					{ var bgcolor = "#fcc"; } else { var bgcolor = "#f8f8ff"; }
				$("#search_query").css("background-color",bgcolor);
			} else {
				alert (error);
			}
		},
		error: function() {
			alert ("Error calling searchlist.php.");
		}
	});
}

function PadLeft (nr) {
	if (nr < 10) { nr = "0" + nr; }
	return (nr);
}
function SecToTime (sec) {
	var seconds = Math.floor(sec % 60);
	var minutes = Math.floor((sec / 60) % 60);
	var hours = Math.floor((sec / (60 * 60)) % 60);

	return PadLeft(hours) + ":" + PadLeft(minutes) + ":" + PadLeft(seconds);
}

$(document).ready(function(){
	$("[data-name=\"hover\"]").mouseenter(function(){
		var active = $(this).data("active");
		var preview = $(this).data("preview");
		if ((active == "thumb") && (preview != ""))
		{
			var title = $(this).data("title");
			title = title.replace(/\"/g, "&quot;");
			style = $(this).find("img").attr("style");
			if (style == "undefined")
				{ style = ""; }
					else { style = " style=\"" + style + "\""; }
			var video = "<video src=\"" + preview + "\" alt=\"" +
				title + "\" class=\"thumb-or-preview\"" + style + " autoplay loop>";
			$(this).html(video);
			$(this).data("active","video");
		}
	});
	$("[data-name=\"hover\"]").mouseleave(function(){
		var active = $(this).data("active");
		if (active == "video")
		{
			var thumb = $(this).data("thumb");
			var title = $(this).data("title");
			title = title.replace(/\"/g, "&quot;");
			style = $(this).find("video").attr("style");
			if (style == "undefined")
				{ style = ""; }
					else { style = " style=\"" + style + "\""; }
			var image = "<img src=\"" + thumb + "\" alt=\"" +
				title + "\" class=\"thumb-or-preview\"" + style + ">";
			$(this).html(image);
			$(this).data("active","thumb");
		}
	});

	$(window).scroll(function(){
		if ($("#video-div").length > 0) {
			var bottom = $("#video-div").offset().top + $("#video-div").height();
			var hover = $("#video-div").data("hover");
			var down = $(window).scrollTop();
			if (down > bottom) {
				if (hover == "no") {
					$("#video-div").css("height",$("#video").height());
					$("#video-cont-div").addClass("hover");
					$("#video-cont-span").css("display","block");
					$("#video-div").data("hover","yes");
				}
			} else {
				$("#video-div").css("height","auto");
				$("#video-cont-div").removeClass("hover");
				$("#video-cont-span").css("display","none");
				$("#video-div").data("hover","no");
				/***/
				$("#video").css("display","block");
				$("#video-cont-a").text("hide");
				$("#video").data("hidden","no");
			}
		}
	});

	$("#switch").click(function(){
		var theme = $("#theme").data("theme");

		$.ajax({
			type: "POST",
			url: "/switch.php",
			data: ({
				theme : theme
			}),
			dataType: "json",
			success: function(data) {
				var result = data["result"];
				var error = data["error"];
				var theme = data["theme"];
				if (result == 1)
				{
					if (theme == "day")
					{
						$("#theme").data("theme","day");
						$("#theme").attr("href","/css/fst_day.css?v=16");
						$("#switch-img").attr("alt","night");
						$("#switch-img").attr("src","/images/theme/night.png");
					} else {
						$("#theme").data("theme","night");
						$("#theme").attr("href","/css/fst_night.css?v=16");
						$("#switch-img").attr("alt","day");
						$("#switch-img").attr("src","/images/theme/day.png");
					}
				} else {
					alert (error);
				}
			},
			error: function() {
				alert ("Error calling switch.php.");
			}
		});
	});

	var MaxHeight = 60;
	$(".limit-height").each(function(){
		var scrollh = this.scrollHeight;
		var div = $(this);
		if (scrollh > MaxHeight) {
			div.css("height",MaxHeight + "px");
			div.after("<a href=\"javascript:;\">Show more</a><br>");
			var link = div.next();
			link.click(function(e){
				e.stopPropagation();
				if (link.text() != "Show less") {
					link.text("Show less");
					div.animate({"height": scrollh});
				} else {
					link.text("Show more");
					div.animate({"height": MaxHeight + "px"});
				}
			});
		}
	});
});
