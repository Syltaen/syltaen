jQuery ($) ->

	$body 	= $("body")
	$oots	= $("html, body")
	page 	= (/((page-id)|(postid))-(\d*)/g).exec( $body.attr("class") )
	page 	= if page then page[4] else false


	# ==================================================
	# > FORMS
	# ==================================================
	$("select").each ->
		if $(@).data "value" then $(@).val $(@).data "value"

		$(@).select2
			minimumResultsForSearch: 5
			placeholder: "Cliquez pour choisir"


	# ==================================================
	# > NAVIGATION
	# ==================================================
	$("#gotop").addClassAt(300, "shown").click ->
		$roots.animate
			scrollTop: 0
		, 500


	# ==================================================
	# > SLIDERS
	# ==================================================
	# $("#").slick
	# 	speed: 750
	# 	dots: true
	# 	arrows: true
	# 	appendArrows: $("")
	# 	appendDots: $("")
	# 	prevArrow: null
	# 	nextArrow: null
	# 	appendDots: null
	# .on "beforeChange", (e, s, curr, next) ->


	# ==================================================
	# > CLICK ACTIONS
	# ==================================================
	$("[data-click]").click (e) ->
		e.preventDefault()
		switch $(@).data "click"
			when "print"
				window.print()
			when "windowed"
				window.open $(@).attr("href"), "_blank", "location=yes,height=500,width=600,scrollbars=yes,status=yes"



