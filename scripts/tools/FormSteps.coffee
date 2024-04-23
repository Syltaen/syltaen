import $ from "jquery"


class Step

    constructor: (@$step, @i, @steps) ->
        @name = @$step.data("name")

        # Progress
        @$progress = $("<li class='form-steps__progress__item'><span>#{@name}</span></li>")
        @steps.$progress.append @$progress
        @$progress.click => @show()

        # Nav
        @addNav()

    ###
    # Show this step
    ###
    show: ->

        if @steps.current == @i then return false

        for s in @steps.steps then s.hide()
        @$progress.addClass "is-active"
        @$step.addClass "is-active"

        @steps.current = @i

    ###
    # Hide this step
    ###
    hide: ->
        @$progress.removeClass "is-active"
        @$step.removeClass "is-active"


    ###
    # Add an item to the progress bar
    ###
    addNav: ->
        @$step.append "<nav class='form-steps__nav'><span class='form-steps__nav__button form-steps__nav__button--prev button button--medium button--arrow-left'>Étape précédente</span><span class='form-steps__nav__button form-steps__nav__button--next button button--medium button--green button--arrow'>Étape suivante</span></nav>"

        @$step.find(".form-steps__nav__button--prev").click => @steps.prev()
        @$step.find(".form-steps__nav__button--next").click => @steps.next()


    ###
    # Highlight errors in this step
    ###
    showErrors: ->
        if @$step.find(".nf-error-msg, .form__error, .is-error, .color-error").length
            @$progress.addClass "has-errors"
            return true
        else
            @$progress.removeClass "has-errors"
            return false


export default class FormSteps

    constructor: (@$el) ->
        @$roots = $("html, body")
        @current = -1

        # Add the progress bar
        @$progress = $("<ul class='form-steps__progress'></ul>")
        @$el.prepend @$progress

        # Create each step and display the first one
        @steps = @$el.find(".form-steps__step").map (i, el) => new Step $(el), i, @
        @steps[0].show()

        # On Error
        if nfRadio
            nfRadio.channel("forms").on "submit:failed", => @showAllErrors()
            nfRadio.channel("fields").on "change:modelValue", => @showAllErrors()

        @$el.find("form").submit => @showAllErrors()


    ###
    # Display the previous step
    ###
    prev: ->
        if @current <= 0 then return false
        @steps[@current - 1].show()
        @scrollTop()


    ###
    # Display the next step
    ###
    next: ->
        if @current >= (@steps.length - 1) then return false
        @steps[@current + 1].show()
        @scrollTop()

    ###
    # Scroll at the top of the form
    ###
    scrollTop: ->
        @$roots.animate
            scrollTop: @$el.offset().top - 160
        , 350


    ###
    # Show error on each step and display the first error
    ###
    showAllErrors: ->
        errorsSteps = []
        for step in @steps
            if step.showErrors()
                errorsSteps.push step

        # Display the first step with error
        if errorsSteps.length
            @scrollTop()
            errorsSteps[0].show()