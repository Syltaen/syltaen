jQuery ($) ->


    # ==================================================
    # > APP
    # ==================================================
    class App
        constructor: ->
            @$body  = $("body")
            @$roots = $("html, body")

            @post = (/((page-id)|(postid))-(\d*)/g).exec( @$body.attr("class") )
            @post = if @post then @post[4] else false

        clicks: ->
            $("[data-click]").click (e) ->
            	e.preventDefault()
            	switch $(@).data "click"
            		when "print"
            			window.print()
            		when "windowed"
            			window.open $(@).attr("href"), "_blank", "location=yes,height=500,width=600,scrollbars=yes,status=yes"

    # ==================================================
    # > FORMS
    # ==================================================
    class Forms
        @transformSelect: ($el) ->
            if $el.data "value" then $el.val $el.data "value"
            $el.select2
                minimumResultsForSearch: 5
                placeholder: "Cliquez pour choisir"



    # ==================================================
    # > SLIDERS
    # ==================================================
    class Slider
        constructor: (@$el) ->
            @slick =  @el.slick
                speed: 750
                dots: true
                arrows: true
                appendArrows: $("")
                appendDots: $("")
                prevArrow: null
                nextArrow: null
                appendDots: null
            .on "beforeChange", @beforeChange

        beforeChange: (e, s, curr, next) ->


    # ==================================================
    # > INIT
    # ==================================================
    new App()
    $("select").each -> Forms.transformSelect $(@)
    $(".incrementor").each -> $(@).incrementor()
    $(".archive-locations").gmap(".locations-types li")