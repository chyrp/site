$(function(){
	$(".Summary .SBody .STable tbody tr").each(function(){
		$(this).children().css("borderLeft", "0")
		$(this).children().next().css("borderRight", "0")
	})
	$(".Summary .SBody .STable tbody tr:last td").css("borderBottom", "0")

	$(".MGroupContent").corner("#1a1a1a 7px bl br")
	$(".MGroupContent").find("a:even").css("background", "#181818")
	$(".MGroupContent").hide()
	$("#MSelected").parent().parent().show()
	$(".MGroup").find("a:first").click(function(){
		if ($(this).next().css("display") == "none")
			$(this).next().animate({ height: "show", opacity: "show" })
		else
			$(this).next().animate({ height: "hide", opacity: "hide" })
	})
	
	if ($.browser.safari)
		$(".SProperty .SEntry, .SFunction .SEntry, .SVariable .SEntry, .SConstant .SEntry, code.inline").css("fontSize", 9)
})