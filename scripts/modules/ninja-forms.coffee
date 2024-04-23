###
  * Controller for all Ninja Forms
  * @use Plugin : Ninja Forms ^3.0.0
###

import $ from "jquery"
import SelectField from "./../tools/SelectField.coffee"
import { UploadField, AutoUploadField } from "./../tools/UploadField.coffee"
import RangeField from "./../tools/RangeField.coffee"
import FormSteps from "./../tools/FormSteps.coffee"
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
        @listenTo nfRadio.channel("fieldrange"),           "render:view",           @rangeRender

        @listenTo nfRadio.channel("fieldfileupload"),      "render:view",           @dropzoneRender
        @listenTo nfRadio.channel("fieldpassword"),        "render:view",           @passwordRender

        @listenTo nfRadio.channel("textarea"),             "render:view",           @trimDefault

        @listenTo nfRadio.channel("form"),                 "render:view",           @bindConditionalCheck


        @listenTo nfRadio.channel("form"),                 "render:view",           @stepRender
        @listenTo nfRadio.channel("fieldopentag"),         "render:view",           @wrapAttributes
        @listenTo nfRadio.channel("form"),                 "render:view",           @wrapRender



    # ==================================================
    # > CONDITIONAL RENDERING
    # ==================================================
    shouldHide: (field) ->
        unless field.attributes.has_conditional_display then return false

        # Parse conditions
        conditions = { allOf: [], oneOf: [] }
        for i, condition of field.attributes.conditional_display
            for i, f of field.collection.models
                unless f.attributes.key == condition.label then continue
                c =
                    value:   f.attributes.value || f.attributes.default
                    require: condition.value
                    compare: condition.calc

                if condition.selected
                    conditions.oneOf.push c
                else
                    conditions.allOf.push c

        # Parse results
        results = { allOf: conditions.allOf.length > 0, oneOf: false }
        for c in conditions.allOf
            if !@valuesMatch c.value, c.require, c.compare
                results.allOf = false
                break

        for c in conditions.oneOf
            if @valuesMatch c.value, c.require, c.compare
                results.oneOf = true
                break

        shouldHide = !(results.allOf || results.oneOf)

        # Disable requirement if the field is hidden
        if shouldHide
            nfRadio.channel("fields").request("remove:error", field.id, "required-error")
            nfRadio.channel("fields").request("remove:error", field.id, "email-error")
            field.attributes.required = 0
            field.attributes.cached_value = field.attributes.value
            field.attributes.value = if field.attributes.type == "email" then "" else "N/A"
        else
            field.attributes.required = field.attributes.required_base
            if field.attributes.value == "N/A"
                field.attributes.value = field.attributes.cached_value || null

        return shouldHide

    valuesMatch: (a, b, compare) ->
        switch compare
            when "!="
                if a != b then return true
            when "==="
                if a is b then return true
            when "!=="
                if a isnt b then return true
            when "in"
                unless a then return false
                if a == "N/A" then return false
                if a.indexOf(b) > -1 then return true
            else
                if a + "" == b + "" then return true

        return false

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


    # RANGE
    rangeRender: (view) ->
        $field = $(view.el).find("input")
        view.model.attributes.value = $field.val()

        setTimeout ->
            $field.each -> new RangeField $(@), (value) ->
                view.model.attributes.value = value
                nfRadio.channel("fields").request("remove:error", view.model.id, "required-error")
        , 0

    # PASSWORD
    passwordRender: (view) ->
        $field  = $(view.el).find(".ninja-forms-field")
        new PasswordBox $field

    # GOOGLE ADDRESS AUTOFILL
    addressAutocomplete: (view) ->
        return false #TODO
        $binds =
            field_num:
                component: "street_number"
            field_cp:
                component: "postal_code"
            field_town:
                component: "locality"

        for field, b of $binds
            b.key = view.model.attributes[field].replace("{field:", "").replace("}", "")

        for m, model of view.model.collection.models
            for field, b of $binds
                if model.attributes.key == b.key
                    b.selector = "#nf-field-" + model.id
                    b.model    = model

        # STREET BIND
        $binds.field_street =
            selector: view.$el.find("input")[0]
            component: "route"

        # EVENT
        $(view.el).find("input").geoloc (data) ->
            for b, field of $binds
                if data[field.component]
                    $(field.selector).val(data[field.component])
                    if field.model
                        field.model.attributes.value = data[field.component]

            view.model.attributes.value = $(view.el).find("input").val()

    # TRIM DEFAULT
    trimDefault: (view) -> $(view.el).find(".nf-element").val $(view.el).find(".nf-element").val().trim()


    # WRAPPERS
    wrapAttributes: (view) ->
        $(view.$el).data "attrs", view.model.attributes.attrs

    wrapRender: (form) ->
        while form.$el.find(".fieldopentag-wrap").length

            $column = false
            deph   = 0

            form.$el.find("nf-field").each ->

                append = true

                # When finding an opentag field
                if $(@).find(".fieldopentag-wrap").length
                    deph++
                    unless $column
                        attrs       = $(@).find(".fieldopentag-wrap").data("attrs") || []
                        attrs.push { label: "class", value: $(@).find("label").text().trim() }
                        attrs.push { label: "id", value: $(@).find(".fieldopentag-container").attr("id") }

                        $column = $("<div></div>")
                        for attr in attrs then $column.attr attr.label, attr.value

                        $(@).before($column)
                        $(@).remove()
                        append = false

                # When finding a closingtag field
                else if $(@).find(".fieldclosetag-wrap").length
                    deph--
                    unless deph
                        $column = false
                        $(@).remove()

                # When finding another field
                if $column && append
                    $column.append $(@)


    # STEPS
    stepRender: (form) ->
        if form.$el.find(".fieldstep-wrap").length
            $step = false

            # Create steps
            form.$el.find("nf-field").each ->
                if $(@).find(".fieldstep-wrap").length
                    $step = $("<li data-name='" + $(@).find("label").text().trim() + "' class='form-steps__step'></li>")
                    $(@).before($step).remove()
                else if $step
                    $step.append $(@)

            # Wrap all steps
            $list = $("<div class='form-steps'><ul class='form-steps__slide'></ul></div>")
            $(".form-steps__step").first().before $list
            $(".form-steps__step").each -> $list.find(".form-steps__slide").append $(@)

            new FormSteps $list





    # ==================================================
    # > UTILITY
    # ==================================================
    validateEmail: (email) ->
        re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
        return re.test(email)

))