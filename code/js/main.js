$(function(){	
	var colors = ["#FF2E99", "#FF8A2D", "#FFE12A", "#CAFF2A", "#1FB5FF", "#5931FF"]
	
	function colorize(text) {
		var colorized = ""
		var bracket_color = ""
		for (i = 0; i < text.length; i++) {
			var index = Math.floor(Math.random()*5)
			if (text[i] == "(")
				bracket_color = colors[index]
		
			color = (bracket_color.length && (text[i] == "(" || text[i] == ")")) ? bracket_color : colors[index]
			colorized = colorized + '<span style="color: '+color+' !important">' + text.charAt(i) + '</span>'
		}
		return colorized
	}
	
	var download_text = $(".big-download").text()
	$(".big-download").bind("mouseenter", function(){
		$(".big-download").html(colorize(download_text)).css("borderColor", "#1d1d1d")
	}).bind("mouseleave", function(){
		$(".big-download").html(download_text).css("borderColor", "#161616")
	})
	
	$(".tron").html(colorize($(".tron").text()))
})
