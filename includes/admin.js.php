<?php
	define('JAVASCRIPT', true);
	require_once "common.php";
	error_reporting(0);
	header("Content-Type: application/x-javascript");
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Mon, 03 Jun 1991 05:30:00 GMT");
	$action = $_GET['action'];
	$page = fallback($_GET['page'], 1, true);
	$more_options_string = (empty($_COOKIE['show_more_options'])) ? __("More Options &raquo;") : __("&laquo; Fewer Options") ;
?>
//<script>
$(function(){
	// Scan AJAX responses for errors.
	$(document).ajaxComplete(function(imconfused, request){
		var response = request.responseText
		if (isError(response))
			alert(response.replace(/HEY_JAVASCRIPT_THIS_IS_AN_ERROR_JUST_SO_YOU_KNOW/m, ""))
	})

<?php if (match(array("/edit_/", "/write_/"), $action)): ?>
	// Fancify the "More Options" links.
	$(document.createElement("a")).attr({
		id: "more_options_link",
		class: "more_options_link",
		href: "javascript:void(0)"
	}).html("<?php echo $more_options_string; ?>").insertBefore(".buttons")
	$("#more_options").clone().insertAfter("#more_options_link").removeClass("js_disabled")<?php if (empty($_COOKIE['show_more_options'])): ?>.css("display", "none")<?php endif; ?>

	$("#more_options_link").click(function(){
		if ($("#more_options").css("display") == "none") {
			$(this).html("<?php echo __("&laquo; Fewer Options"); ?>")
			Cookie.set("show_more_options", "true", 30)
		} else {
			$(this).html("<?php echo __("More Options &raquo;"); ?>")
			Cookie.destroy("show_more_options")
		}
		$("#more_options").slideToggle()
	})
<?php endif; ?>

	// Remove things that only exist for JS-disabled users.
	$(".js_disabled").remove()
	$(".js_enabled").css("display", "block")

	// Automated PNG fixing.
	$.ifixpng("<?php echo $config->url; ?>/admin/images/icons/pixel.gif")
	$("img[@src$=.png]").ifixpng()

	// Add the "Bookmarklet" with JS to the write nav since only JS-enabled users can use it.
	$(document.createElement("li")).addClass("bookmarklet right").html("<?php echo sprintf(__("Bookmarklet: %s"), '<a href=\"javascript:var%20d=document,w=window,e=w.getSelection,k=d.getSelection,x=d.selection,s=(e?e():(k)?k():(x?x.createRange().text:0)),f=\''.$config->url.'/includes/bookmarklet.php\',l=d.location,e=encodeURIComponent,p=\'?url=\'+e(l.href)+\'&title=\'+e(d.title)+\'&selection=\'+e(s),u=f+p;a=function(){if(!w.open(u,\'t\',\'toolbar=0,resizable=0,status=1,width=450,height=430\'))l.href=u;};if(/Firefox/.test(navigator.userAgent))setTimeout(a,0);else%20a();void(0)\">Chyrp!</a>'); ?>").prependTo(".write_post_nav")

<?php if (match("/(edit|write)_/", $_GET['action'])): ?>
	// Auto-expand text fields & auto-grow textareas.
	$("input.text").each(function(){
		$(this).css("min-width", $(this).width()).Autoexpand()
	})
	$("textarea").each(function(){
		$(this).css("min-height", $(this).height()).autogrow()
	})

<?php endif; ?>
	// "Help" links should open in popup windows.
	$(".help").click(function(){
		window.open($(this).attr("href"), "help", "status=0, height=350, width=300")
		return false;
	})

	// AJAX post deletion.
	$(".post_delete_link").click(function(){
		if (!confirm("<?php echo __("Are you sure you want to delete this post?\\n\\nIt cannot be restored if you do this. If you wish to hide it, save it as a draft."); ?>")) return false
		var id = $(this).attr("id").replace(/post_delete_/, "")
		Post.destroy(id)
		return false
	})

	// Content previewing.
	if ($(".preview_me").length > 0) {
		var feather = ($("#write_feather").size()) ? $("#write_feather").val() : ""
		var feather = ($("#edit_feather").size()) ? $("#edit_feather").val() : feather
		$(document.createElement("div")).css("display", "none").attr("id", "preview").insertBefore("#write_form, #edit_form")
		$(document.createElement("button")).html("<?php echo __("Preview &rarr;"); ?>").attr({ "type": "submit", "accesskey": "p" }).click(function(){
			$("#preview").load("<?php echo $config->url; ?>/includes/ajax.php", { action: "preview", content: $(".preview_me").val(), feather: feather }, function(){
				$(this).fadeIn("fast")
			})
			return false
		}).appendTo(".buttons")
	}

	// Checkbox toggling.
	var all_checked = true
	$("#toggler").html('<label for="toggle">Toggle All</label><input class="checkbox" type="checkbox" name="toggle" id="toggle" />')
	$("#toggle").click(function(){
		$("form#new_group, form#group_edit").find(":checkbox").not("#toggle").each(function(){
			this.checked = document.getElementById("toggle").checked
		})
	})
	$("form#new_group, form#group_edit").find(":checkbox").not("#toggle").each(function(){
		if (!all_checked) return
		all_checked = this.checked
	})
	if ($("#toggler").size())
		document.getElementById("toggle").checked = all_checked

	// Extension enabling/disabling (drag'n'drop)
	$(".enable h2, .disable h2").append(" <span class=\"sub\"><?php echo __("(drag)"); ?></span>")
	$(".disable ul li, .enable ul li").draggable({ zIndex: 100 })
	$(".enable ul, .disable ul").droppable({
		accept: ".enable ul.extend li",
		activeClass: "active",
		hoverClass: "hover",
		drop: function(ev, ui) {
			var classes = $(this).parent().attr("class").split(" ")
			var box = $(this)
			var confirmed = false
			var action = classes[0]
			var type = classes[1]
			var extension = $(ui.draggable).attr("class").split(" ")[0]

			$.post("<?php echo $config->url; ?>/includes/ajax.php", { action: "check_confirm", check: extension, type: type }, function(data){
				if (data != "" && action == "disable")
					var confirmed = (confirm(data)) ? 1 : 0

				$.ajax({ type: "post", dataType: "json", url: "<?php echo $config->url; ?>/includes/ajax.php", data: { action: action + "_" + type, extension: extension, confirm: confirmed }, beforeSend: function(){
					box.loader()
				}, success: function(json){
					box.loader(true)
					$(json.notifications).each(function(){
						if (this == "") return
						alert(this)
					})
				} })
			})

			$(ui.draggable).css({ left: 0, right: 0, top: 0, bottom: 0 }).appendTo(this)

			$("ul.extend").height("auto")
			$("ul.extend").each(function(){
				if ($(".enable ul.extend").height() > $(this).height())
					$(this).height($(".enable ul.extend").height())
				if ($(".disable ul.extend").height() > $(this).height())
					$(this).height($(".disable ul.extend").height())
				draw()
			})
		}
	})
	$("ul.extend li").css("cursor", "move")
	$("ul.extend li .description").css("display", "none")
	$(".info_link").click(function(){
		$(this).parent().find(".description").effect("blind", { mode: "toggle" })
		return false
	})
	$("ul.extend").each(function(){
		if ($(".enable ul.extend").height() > $(this).height())
			$(this).height($(".enable ul.extend").height())
		if ($(".disable ul.extend").height() > $(this).height())
			$(this).height($(".disable ul.extend").height())
	})
<?php if ($_GET['action'] == "extend_modules"): ?>

	function remove_from_array(value, array) {
		for (i = 0; i < array.length; i++)
			if (array[i] == value)
				array.splice(i, 1)
		return array
	}
	function draw() {
		if (!$(".extend li.conflict").size() && !($.browser.safari || $.browser.opera || ($.browser.mozilla && $.browser.version >= 1.9)))
			return

		$("#canvas").remove()

		$("#header, #welcome, #sub-nav, #content a.button, .extend li, #footer, h1, h2").css({
			position: "relative",
			zIndex: 2
		})
		$("#header ul li a").css({
			position: "relative",
			zIndex: 3
		})

		$(document.createElement("canvas")).attr("id", "canvas").prependTo("body")
		$("#canvas").css({
			position: "absolute",
			top: 0,
			bottom: 0,
			zIndex: 1,
			margin: "0 auto"
		}).attr({ width: ($("#content.column").width() + 150), height: $(document).height() })

		var canvas = document.getElementById("canvas").getContext("2d")
		var displayed = []

		$(".extend li.conflict").each(function(){
			var classes = $(this).attr("class").split(" ")
			classes.shift() // Remove the module's safename class

			// Remove any classes we don't want
			$(["conflict", "depends"]).each(function(){
				remove_from_array(this, classes);
			})

			for (i = 0; i < classes.length; i++) {
				var conflict = classes[i].replace("conflict_", "module_")

				if (displayed[$(this).attr("id")+" :: "+conflict])
					continue;

				canvas.strokeStyle = "#d12f19"
				canvas.fillStyle = "#fbe3e4"
				canvas.lineWidth = 3

				var this_status = $(this).parent().parent().attr("class").split(" ")[0] + "d"
				var conflict_status = $("#"+conflict).parent().parent().attr("class").split(" ")[0] + "d"

				if (conflict_status != this_status) {
					var line_from_x = $("#"+conflict).offset().left
					var line_from_y = $("#"+conflict).offset().top + 12
					var line_to_x   = $(this).offset().left + $(this).outerWidth()
					var line_to_y   = $(this).offset().top + 12

					// Line
					canvas.moveTo(line_from_x, line_from_y)
					canvas.lineTo(line_to_x, line_to_y)
					canvas.stroke()

					// Beginning circle
					canvas.beginPath()
					canvas.arc(line_from_x, line_from_y, 5, 1.35, -1.35, false)
					canvas.fill()
					canvas.stroke()

					// Ending circle
					canvas.beginPath()
					canvas.arc(line_to_x, line_to_y, 5, -1.75, 1.75, false)
					canvas.fill()
					canvas.stroke()
				} else if (conflict_status == "disabled") {
					var line_from_x = $("#"+conflict).offset().left
					var line_from_y = $("#"+conflict).offset().top + 12
					var line_to_x   = $(this).offset().left
					var line_to_y   = $(this).offset().top + 12
					var median = line_from_y + ((line_to_y - line_from_y) / 2)
					var curve = line_from_x - 25

					// Line
					canvas.beginPath();
					canvas.moveTo(line_from_x, line_from_y)
					canvas.quadraticCurveTo(curve, median, line_to_x, line_to_y);
					canvas.stroke();

					// Beginning circle
					canvas.beginPath()
					canvas.arc(line_from_x, line_from_y, 5, 1.35, -1.35, false)
					canvas.fill()
					canvas.stroke()

					// Ending circle
					canvas.beginPath()
					canvas.arc(line_to_x, line_to_y, 5, 1.35, -1.35, false)
					canvas.fill()
					canvas.stroke()
				} else if (conflict_status == "enabled") {
					var line_from_x = $("#"+conflict).offset().left + $("#"+conflict).outerWidth()
					var line_from_y = $("#"+conflict).offset().top + 12
					var line_to_x   = $(this).offset().left + $(this).outerWidth()
					var line_to_y   = $(this).offset().top + 12
					var median = line_from_y + ((line_to_y - line_from_y) / 2)
					var curve = line_from_x + 25

					// Line
					canvas.beginPath();
					canvas.moveTo(line_from_x, line_from_y)
					canvas.quadraticCurveTo(curve, median, line_to_x, line_to_y);
					canvas.stroke();

					// Beginning circle
					canvas.beginPath()
					canvas.arc(line_from_x, line_from_y, 5, -1.75, 1.75, false)
					canvas.fill()
					canvas.stroke()

					// Ending circle
					canvas.beginPath()
					canvas.arc(line_to_x, line_to_y, 5, -1.75, 1.75, false)
					canvas.fill()
					canvas.stroke()
				}

				displayed[conflict+" :: "+$(this).attr("id")] = true
			}
		})
	}

	draw()

	$(window).resize(function(){
		draw()
	})
<?php endif; ?>
})

var Post = {
	destroy: function(id) {
		$("#post_"+id+" .target, #post_"+id+".target").loader()
		$.post("<?php echo $config->url; ?>/includes/ajax.php", { action: "delete_post", id: id }, function(response){
			$("#post_"+id+" .target, #post_"+id+".target").loader(true)
			if (isError(response)) return
			$("#post_"+id).animate({ height: "hide", opacity: "hide" }).remove()
		})
	}
}

// "Loading..." overlay.
$.fn.loader = function(remove) {
	if (remove) {
		$(this).next().remove()
		return this
	}

	var offset = $(this).offset()
	var width = $(this).outerWidth()
	var loading_top = ($(this).outerHeight() / 2) - 11
	var loading_left = ($(this).outerWidth() / 2) - 63

	$(this).after("<div class=\"load_overlay\"><img src=\"<?php echo $config->url; ?>/includes/close.png\" style=\"display: none\" class=\"close\" /><img src=\"<?php echo $config->url; ?>/includes/loading.gif\" style=\"display: none\" class=\"loading\" /></div>")

	$(".load_overlay .loading").css({
		position: "absolute",
		top: loading_top+"px",
		left: loading_left+"px",
		display: "inline"
	})

	$(".load_overlay .close").css({
		position: "absolute",
		top: "3px",
		right: "3px",
		color: "#fff",
		cursor: "pointer",
		display: "inline"
	}).click(function(){ $(this).parent().remove() })

	$(".load_overlay").css({
		position: "absolute",
		top: offset.top,
		left: offset.left,
		zIndex: 100,
		width: $(this).outerWidth(),
		height: $(this).outerHeight(),
		background: ($.browser.msie) ? "transparent" : "transparent url('<?php echo $config->url; ?>/includes/trans.png')",
		textAlign: "center",
		filter: ($.browser.msie) ? "progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src='<?php echo $config->url; ?>/includes/trans.png');" : ""
	})

	return this
}

var Cookie = {
	set: function(name, value, expires) {
		var today = new Date()
		today.setTime( today.getTime() )

		if (expires)
			expires = expires * 1000 * 60 * 60 * 24

		var expires_date = new Date(today.getTime() + (expires))

		document.cookie = name+"="+escape(value)+
		                  ((expires) ? ";expires="+expires_date.toGMTString() : "" )+";path=/"
	},
	destroy: function(name) {
		document.cookie = name+"=;path=/;expires=Thu, 01-Jan-1970 00:00:01 GMT"
	}
}

// Used to check if AJAX responses are errors.
function isError(text) {
	return /HEY_JAVASCRIPT_THIS_IS_AN_ERROR_JUST_SO_YOU_KNOW/m.test(text);
}

<?php $trigger->call("admin_javascript"); ?>
//</script>