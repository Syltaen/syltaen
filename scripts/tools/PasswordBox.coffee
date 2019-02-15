import $ from "jquery"

export default class PasswordBox

    constructor: (@$field) ->

        @addMarkup()

        # BINDS
        @$toggle.click => @toggle()


    ###
    # Wrap the field with passwordbox actions
    ###
    addMarkup: ->
        @$field.addClass "passwordbox__field"

        @$field.wrap "<div class='passwordbox'></div>"
        @$el = @$field.parent(".passwordbox")

        @$toggle = $("<div class='passwordbox__toggle'>Afficher/Cacher</div>")
        @$el.append @$toggle


    ###
    # Toggle the field type
    ###
    toggle: ->
        switch @$field.attr("type")
            when "password"
                @$field.attr "type", "text"
                @$el.addClass "is-shown"

                unless @$field.val().trim()
                    @$field.val("")

            else
                @$field.attr "type", "password"
                @$el.removeClass "is-shown"