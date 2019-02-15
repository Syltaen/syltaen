###
  * Controller for all Ninja Forms
  * @use Plugin : Ninja Forms ^3.0.0
###

import $ from "jquery"
import SelectField from "./../tools/SelectField.coffee"
import UploadField from "./../tools/UploadField.coffee"
import PasswordBox from "./../tools/PasswordBox.coffee"


if typeof Marionette isnt "undefined" then new (Marionette.Object.extend(

    initialize: ->
        # nfRadio.DEBUG = true
        # console.log nfRadio._channels

        @listenTo nfRadio.channel("submit"),               "validate:field",        @validateRequired
        @listenTo nfRadio.channel("fields"),               "change:modelValue",     @validateRequired

        @listenTo nfRadio.channel("listselect"),           "render:view",           @listselectRender
        @listenTo nfRadio.channel("listmultiselect"),      "render:view",           @listselectRender
        @listenTo nfRadio.channel("listcountry"),          "render:view",           @listselectRender
        @listenTo nfRadio.channel("liststate"),            "render:view",           @listselectRender
        @listenTo nfRadio.channel("fieldroles"),           "render:view",           @listselectRender
        @listenTo nfRadio.channel("fieldfileupload"),      "render:view",           @dropzoneRender
        @listenTo nfRadio.channel("fieldpassword"),        "render:view",           @passwordRender
        @listenTo nfRadio.channel("fieldscenario"),        "render:view",           @scenarioRender

        @listenTo nfRadio.channel("textarea"),             "render:view",           @trimDefault

        @listenTo nfRadio.channel("form"),                 "render:view",           @bindConditionalCheck
        @listenTo nfRadio.channel("form"),                 "render:view",           @gridRender



    # ==================================================
    # > CONDITIONAL RENDERING
    # ==================================================
    shouldHide: (field) ->
        shouldHide = false

        if field.attributes.has_conditional_display
            for i, condition of field.attributes.conditional_display
                for i, f of field.collection.models
                    if f.attributes.key == condition.label
                        fieldValue = f.attributes.value || f.attributes.default
                        pass = false

                        switch condition.calc
                            when "!="   then pass = true if fieldValue != condition.value
                            when "==="  then pass = true if fieldValue is condition.value
                            when "!=="  then pass = true if fieldValue isnt condition.value
                            when "in"   then pass = true if fieldValue.indexOf(condition.value) > -1
                            else             pass = true if fieldValue + "" == condition.value + ""
                        unless pass then shouldHide = true

        # Disable requirement if the field is hidden
        if shouldHide
            nfRadio.channel("fields").request("remove:error", field.id, "required-error")
            field.attributes.required = 0
        else
            field.attributes.required = field.attributes.required_base

        return shouldHide

    checkConditional: (form) ->
        for i, field of form.model.attributes.fields.models
            $container = $("#nf-field-#{field.id}-container")

            if @shouldHide field
                $container.hide()
            else
                $container.show()
                if field.attributes.type == "bpostpointfield"
                    $(document).trigger("bpostpointfield_display")

    bindConditionalCheck: (form) ->
        for i, field of form.model.attributes.fields.models
            field.attributes.required_base = field.attributes.required

        form.$el.find("input, select").each (i, el) =>
            $(el).change =>
                setTimeout =>
                    @checkConditional form
                , 100

        setTimeout =>
            @checkConditional form
        , 250


    # ==================================================
    # > VALIDATION
    # ==================================================
    validateRequired: (field) ->

        value = field.get("value")
        id    = field.get("id")

        switch field.get("type")
            # ========== LOGIN FIELD ========== #
            when "login"
                if @validateEmail value
                    nfRadio.channel("fields").request("remove:error", id, "login-error")
                else
                    nfRadio.channel("fields").request("add:error", id, "login-error", "Please provide a valid email address.")

    # ==================================================
    # > RENDERERS
    # ==================================================
    # SELECT 2
    listselectRender: (view) ->
        $(view.el).find("select").each ->
            new SelectField $(@), ($el) ->
                $el.change ->
                    view.model.attributes.value = $(@).val()
                    if view.model.attributes.value then nfRadio.channel("fields").request("remove:error", view.model.id, "required-error")

            view.model.attributes.value = $(@).val()


    # DROPZONE
    dropzoneRender: (view) ->
        new UploadField $(view.el).find("input[type='file']").first(), (list, value) ->
            view.model.attributes.value = value
            if value then nfRadio.channel("fields").request("remove:error", view.model.id, "required-error")


    # PASSWORD
    passwordRender: (view) ->
        $field  = $(view.el).find(".ninja-forms-field")
        new PasswordBox $field



        # $box    = $field.closest(".passwordbox")
        # $toggle = $box.find(".passwordbox__toggle")

        # $toggle.click ->
        #     console.log $field.attr("type")
        #     switch $field.attr("type")
        #         when "password"
        #             $field.attr "type", "text"
        #             $box.addClass "is-shown"
        #         else
        #             $field.attr "type", "password"
        #             $box.removeClass "is-shown"



    # TRIM DEFAULT
    trimDefault: (view) -> $(view.el).find(".nf-element").val $(view.el).find(".nf-element").val().trim()


    # GRID
    gridRender: (form) ->
        while form.$el.find(".fieldopentag-wrap").length

            column = false
            deph   = 0

            form.$el.find("nf-field").each ->

                append = true

                # When finding an opentag field
                if $(@).find(".fieldopentag-wrap").length
                    deph++
                    unless column
                        classes = $(@).find("label").text().trim()
                        if classes
                            id = $(@).find(".fieldopentag-container").attr "id"
                            column = $("<div class='" + classes + "' id='#{id}'></div>")
                            $(@).before(column)
                            $(@).remove()
                            append = false

                # When finding an closingtag field
                else if $(@).find(".fieldclosetag-wrap").length
                    deph--
                    unless deph
                        column = false
                        $(@).remove()

                # When finding another field
                if column && append
                    column.append $(@)





    # ==================================================
    # > UTILITY
    # ==================================================
    validateEmail: (email) ->
        re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
        return re.test(email)

))