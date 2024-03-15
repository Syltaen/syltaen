import $ from "jquery"
import Dropzone from "dropzone"


# =============================================================================
# > COMMON ABSTRACT CLASS
# =============================================================================
class AbstractUploadField

    ###
    # Create a new instance
    ###
    constructor: (@$field, @config = {}) ->
        @uuid    = Dropzone.uuidv4()
        @$form   = @$field.closest("form")
        @config  = Object.assign @getDefaultConfig(), @config

        @value  = {}
        @setupHTML()
        @setupDropzone()
        @prefill()


    ###
    # Default Dropzone config to use
    ###
    getDefaultConfig: ->
        folder     = @$field.attr("folder") || 0
        attachment = if @$field.attr("attachment") then 1 else 0
        sleep = @$field.attr("sleep") || 0 # used for testing

        return
            # Ajax parameters
            url: ajaxurl + "?action=syltaen_ajax_upload&folder=#{folder}&attachment=#{attachment}&sleep=#{sleep}"

            # Field processing
            paramName:      @$field.attr("name").replace "[]", ""
            maxFiles:       parseInt(@$field.attr("limit")) || if @$field.attr("multiple") then 10 else 1
            acceptedFiles:  @$field.attr("accept") || null
            maxFilesize:    parseInt(@$field.attr("maxupload"), 10) || 10 # in Mb
            parallelUploads: 10

            # Interactions
            clickable:      true
            addRemoveLinks: true

            # Error messages
            dictFileTooBig:       "This file is too heavy ({{filesize}}Mb) - Max allowed is {{maxFilesize}}Mb"
            dictInvalidFileType:  "This type of file is not autorized."
            dictMaxFilesExceeded: "You can't upload more than {{maxFiles}} files."

            # Process all files at the end
            autoProcessQueue: false
            uploadMultiple:   true

            # Return type : all, url
            returnType: @$field.attr("return") || "all"


    ###
    # Setup Dropzone
    ###
    setupHTML: ->
        # Wrap in .uploadfield
        @$field.wrap("<div class='uploadfield'></div>")
        @$wrap = @$field.closest ".uploadfield"

        # Add drop zone
        @$zone = $("<div class='uploadfield__zone'></div>")
        @$wrap.append @$zone

        # Add hidden field to store future data
        @$hidden = $("<input class='uploadfield__data' type='hidden' name='" + @config.paramName + "'>")
        @$wrap.append @$hidden

        # Add message
        @$message = $("<p class='uploadfield__message'>" + (@$field.attr("label") || "Click here or drop your file") + "</p>")
        @$zone.append @$message

        # Remove file field
        @$field.remove()

    ###
    # Setup Dropzone
    ###
    setupDropzone: ->
        @dropzone = new Dropzone @$zone[0], @config

        # Bind events
        @bindEvents()

        # Remove a file
        @dropzone.on "removedfile", (file, a, b) =>
            uuid = file.uuid || file.upload.uuid
            delete @value[uuid]
            @syncHidden()
            @$form.removeClass "is-loading is-sending"

        # Only one file allowed : replace existing value with new one
        if @config.maxFiles == 1
            @dropzone.on "maxfilesexceeded", (file) =>
                @dropzone.removeAllFiles()
                @dropzone.addFile(file)

            @dropzone.on "addedfile", (file) =>
                @removeAllPrefill()

    ###
    # Update hidden field value with @value
    ###
    syncHidden: ->
        filesList = []

        # Merge all uploads into one list
        for key, upload of @value then filesList = filesList.concat upload

        switch @config.return
            when "url"
                urls = []
                for f in filesList then urls.push f.url
                fieldValue = urls.join ", "
            else
                fieldValue = JSON.stringify filesList

        # Set the value of the hidden field
        @$hidden.val fieldValue


    ###
    # Prefill with custom thumbnails
    ###
    prefill: ->
        unless @$field.data("value") then return false
        value = @$field.data("value")

        # Tranform in array of files if it's a string
        for file, i in value
            file.uuid = file.ID || file.path
            @dropzone.options.addedfile.call @dropzone, file
            if file.mime && file.mime.match /image/
                @dropzone.options.thumbnail.call @dropzone, file, file.url

            # Add the file to the list
            @value[file.uuid] = file

        @syncHidden()

    ###
    # Remove all the prefilled files
    ###
    removeAllPrefill: ->
        @$wrap.find(".dz-image img[src^='http']").closest(".dz-preview").remove()

    ###
    # Clear the previews and keep only a defined amount
    ###
    clearPreviews: (keep = 0) ->
        while @$wrap.find(".dz-preview").length > keep
            @$wrap.find(".dz-preview").first().remove()

    ###
    # Display a backend upload error
    ###
    displayFileError: (file, error) ->
        $preview = $(file.previewElement)
        $preview.removeClass("dz-success dz-processing").addClass("dz-error")
        $preview.find(".dz-error-message span").text error

# =============================================================================
# > DIRECT UPLOAD : Upload file when it is dropped
# =============================================================================
export class AutoUploadField extends AbstractUploadField
    getDefaultConfig: ->
        config = super()
        config.autoProcessQueue = true
        return config

    ###
    # Bind the different upload events
    ###
    bindEvents: ->
        # Upload is successful
        @dropzone.on "success", (file, uploaded) =>
            # Find original file
            for u in uploaded then if u.origin.name == file.name then break

            if u.error
                @displayFileError file, u.error
            else
                @value[file.upload.uuid] = u
            @syncHidden()


# =============================================================================
# > INDIRECT UPLOAD : Upload file when it the form is submitted
# =============================================================================
export class UploadField extends AbstractUploadField

    ###
    # Bind the different upload events
    ###
    bindEvents: ->
        # On upload : check if there are new files to process
        @$form.on "submit.upload-#{@uuid}", (e) =>
            @$form.removeClass "has-errors"

            # Submit form normally if there are no pending uploads
            nok = @dropzone.files.filter (file) -> file.status != "success"
            unless nok.length then return true

            e.preventDefault()
            e.stopPropagation()
            @dropzone.processQueue()

        # removedfile

        # Upload is successful
        @dropzone.on "successmultiple", (files, responses) =>
            for file, i in files
                res = responses[i]

                if res.error
                    @displayFileError file, res.error
                    @$form.addClass "has-errors"
                else
                    @value[file.upload.uuid] = res

            @syncHidden()
            @$form.removeClass "is-loading is-sending"

            unless @$form.hasClass "has-errors"
                @$form.submit()