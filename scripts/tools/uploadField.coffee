import $ from "jquery"
import Dropzone from "dropzone"

export default class UplaodField

    constructor: (@$field, @onChange = false) ->
        @$loader  = $("body")
        @config = @defaultConfig()
        @value  = {}
        @setup()

        @prefill()


    ###
    # Default config to use for the upload
    ###
    defaultConfig: ->
        return
            name:        @$field.attr("name").replace "[]", ""
            limit:       parseInt(@$field.attr("limit")) || if @$field.attr("multiple") then 5 else 1
            accept:      @$field.attr("accept") || null
            maxupload:   parseInt(@$field.attr("maxupload"), 10) || 10 # in Mb
            message:     @$field.attr("data-label") || "Fichier(s)"
            return:      @$field.attr("data-return") || "all"

            attachement: if @$field.attr("data-attachment") then 1 else 0
            folder:      @$field.attr("data-folder") || 0

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
        if @onChange then @onChange filesList, fieldValue


    ###
    # Setup the HTML and Dropzone
    ###
    setup: ->
        # Wrap in .uploadfield
        @$field.wrap("<div class='uploadfield'></div>")
        @$wrap = @$field.closest ".uploadfield"

        # Add drop zone
        @$zone = $("<div class='uploadfield__zone'></div>")
        @$wrap.append @$zone

        # Add hidden field to store future data
        @$hidden = $("<input class='uploadfield__data' type='hidden' name='" + @config.name + "'>")
        @$wrap.append @$hidden

        # Add message
        @$message = $("<p class='uploadfield__message'>" + @config.message + "</p>")
        @$zone.append @$message

        # Remove file field
        @$field.remove()

        # Dropzone
        _UplaodField = @
        @dropzone = new Dropzone @$zone[0],
            url:            ajaxurl + "?action=syltaen_ajax_upload"
            params:
                folder:      @config.folder
                attachement: @config.attachement


            paramName:      @config.name
            maxFiles:       @config.limit
            acceptedFiles:  @config.accept
            maxFilesize:    @config.maxupload

            clickable:      true
            addRemoveLinks: true

            dictFileTooBig:       "Ce fichier est trop lourd ({{filesize}}Mb) - Max. autorisé : {{maxFilesize}}Mb"
            dictInvalidFileType:  "Ce type de fichier n'est pas autorisé."
            dictMaxFilesExceeded: "Vous ne pouvez ajouter que {{maxFiles}} fichiers"

            # ========== EVENTS ========== #
            init: ->

                # Upload is successful
                @on "success", (file, uploaded) ->
                    file.uuid = "file" + Date.now()
                    _UplaodField.value[file.uuid] = uploaded
                    _UplaodField.syncHidden()

                # Remove a file
                @on "removedfile", (file) ->
                    console.log file


                    delete _UplaodField.value[file.uuid]
                    _UplaodField.syncHidden()


                # Add a file
                @on "addedfile", ->
                    _UplaodField.$loader.addClass "is-loading"

                # Upload is done
                @on "complete", ->
                    _UplaodField.$loader.removeClass "is-loading"


    ###
    # Prefill with custom thumbnails
    ###
    prefill: ->

        unless @$field.data("value") then return false

        for file, i in @$field.data("value")

            file.uuid = "file" + Date.now()
            file.size = 0
            file.name = file.url.substring(file.url.lastIndexOf('/') + 1)

            @dropzone.options.addedfile.call @dropzone, file
            @dropzone.options.thumbnail.call @dropzone, file, file.url
            @dropzone.options.complete.call @dropzone, file
            @dropzone.files.push file
            @dropzone.options.maxFiles--

            # Add the file to the list
            @value[file.uuid] = file

            @syncHidden()