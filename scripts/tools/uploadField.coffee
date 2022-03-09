import $ from "jquery"
import Dropzone from "dropzone"
import ProgressCircle from "./ProgressCircle.coffee"

export default class UplaodField

    ###
    # Create a new instance
    ###
    constructor: (@$field, @autoUpload, @config = {}) ->
        @$form  = @$field.closest("form")
        @config  = Object.assign @getDefaultConfig(), @config

        @value  = {}
        @setupHTML()
        @setupDropzone()
        @prefill()


    ###
    # Default Dropzone config to use
    ###
    getDefaultConfig: (config) ->
        folder     = @$field.attr("data-folder") || 0
        attachment = if @$field.attr("data-attachment") then 1 else 0

        return
            # Ajax parameters
            url: ajaxurl + "?action=syltaen_ajax_upload&folder=#{folder}&attachment=#{attachment}"

            # Field processing
            paramName:      @$field.attr("name").replace "[]", ""
            maxFiles:       parseInt(@$field.attr("limit")) || if @$field.attr("multiple") then 5 else 1
            acceptedFiles:  @$field.attr("accept") || null
            maxFilesize:    parseInt(@$field.attr("maxupload"), 10) || 10 # in Mb

            # Interactions
            clickable:      true
            addRemoveLinks: true

            # Error messages
            dictFileTooBig:       "Ce fichier est trop lourd ({{filesize}}Mb) - Max. autorisé : {{maxFilesize}}Mb"
            dictInvalidFileType:  "Ce type de fichier n'est pas autorisé."
            dictMaxFilesExceeded: "Vous ne pouvez ajouter que {{maxFiles}} fichier(s)"

            # Process all files at the end
            autoProcessQueue: @autoUpload
            uploadMultiple:   true

            # Custom parameters
            returnType: @$field.attr("data-return") || "all"
            autoUpload: @isAuto

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
        unless @autoUpload then @$zone.addClass "dz-no-auto"

        # Add hidden field to store future data
        @$hidden = $("<input class='uploadfield__data' type='hidden' name='" + @config.paramName + "'>")
        @$wrap.append @$hidden

        # Add message
        @$message = $("<p class='uploadfield__message'>" + (@$field.attr("data-label") || "Fichier(s)") + "</p>")
        @$zone.append @$message

        # Remove file field
        @$field.remove()

    ###
    # Setup Dropzone
    ###
    setupDropzone: ->
        @dropzone = new Dropzone @$zone[0], @config

        # Bind events
        if @autoUpload
            @autoUploadEvents()
        else
            @nautoUploadEvents()

        # Only one file allowed : replace existing value with new one
        if @config.maxFiles == 1
            @dropzone.on "maxfilesexceeded", (file) =>
                @dropzone.removeAllFiles()
                @dropzone.addFile(file)
            @dropzone.on "addedfile", (file) =>
                @removeAllPrefill()

        # Remove a file
        @dropzone.on "removedfile", (file) =>
            delete @value[file.uuid]
            @syncHidden()
            if @config.onRemoveFile then @config.onRemoveFile file, uploaded, @

    ###
    # Events triggered when a file is auto-uploaded
    ###
    autoUploadEvents: ->
        # Upload is successful
        @dropzone.on "success", (file, uploaded) =>
            file.uuid = "file" + Date.now()
            @value[file.uuid] = uploaded
            if uploaded[0].error then @displayFileError file, uploaded[0].error
            @syncHidden()
            if @config.onSuccess then @config.onSuccess file, uploaded, @

        # Add a file
        @dropzone.on "addedfile", =>
            @$form.addClass "is-loading"
            if @config.onAddedFile then @config.onAddedFile file, uploaded, @

        # Upload is done
        @dropzone.on "complete", =>
            @$form.removeClass "is-loading"
            if @config.onComplete then @config.onComplete file, uploaded, @

    ###
    # Events triggered when files are subimtted alongside the form data
    ###
    nautoUploadEvents: ->
        @$progress = new ProgressCircle
        @$progress.addTo @$form

        @$form.on "submit.upload", (e) =>
            if @dropzone.files.length
                e.preventDefault()
                e.stopPropagation()
                @dropzone.processQueue()

        # Upload is starting
        @dropzone.on "sendingmultiple", () =>
            @$form.addClass "is-loading"

        # Upload is progressing
        @dropzone.on "totaluploadprogress", (totalProgress, totalBytes, totalBytesSent) =>
            @$progress.setProgress totalProgress

        # Upload is successful
        @dropzone.on "successmultiple", (files, response) =>
            hasErrors = false
            for res, i in response
                if res.error
                    @displayFileError @dropzone.files[i], res.error
                    hasErrors = true
            if hasErrors
                @$form.removeClass "is-loading"
            else
                @value = response
                @syncHidden()
                if @config.onSuccess then @config.onSuccess files, uploaded, @
                @$form.off "submit.upload"
                @$form.removeClass "is-sending"
                @$form.submit()

        # Upload is not successful
        @dropzone.on "errormultiple", (files, response) =>
            @$form.removeClass "is-loading"
            for file in files
                @displayFileError file, response

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
                for f in filesList
                    urls.push f.url
                fieldValue = urls.join ", "
            else
                fieldValue = JSON.stringify filesList

        # Set the value of the hidden field
        @$hidden.val fieldValue

        # Custom callback
        if @config.onChange then @config.onChange filesList, fieldValue, @


    ###
    # Prefill with custom thumbnails
    ###
    prefill: ->
        unless @$field.data("value") then return false
        value = @$field.data("value")

        # Tranform in array of files if it's a string
        for file, i in value
            file.uuid = file["ID"]
            @dropzone.options.addedfile.call @dropzone, file
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