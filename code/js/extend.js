$(function(){	
	var colors = ["#FF2E99", "#FF8A2D", "#FFE12A", "#CAFF2A", "#1FB5FF", "#5931FF"]
	$(".tags a").each(function(){
		var index = Math.floor(Math.random()*5)
		$(this).css("color", colors[index])
	})
	
	var text;
	$(".add-buttons a").bind("mouseenter", function(){
		text = $(this).text()
		
		var colorized = ""
		var bracket_color = ""
		for (i = 0; i < text.length; i++) {
			var index = Math.floor(Math.random()*5)
			if (text[i] == "(")
				bracket_color = colors[index]
		
			color = (bracket_color.length && (text[i] == "(" || text[i] == ")")) ? bracket_color : colors[index]
			colorized = colorized + '<span style="color: '+color+' !important">' + text[i] + '</span>'
		}
		
		$(this).html(colorized)
	}).bind("mouseleave", function(){
		$(this).html(text)
	})
})