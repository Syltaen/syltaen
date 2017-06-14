jQuery(function($) {

    if (typeof Marionette !== 'undefined') {

        var NF_SyltaenController = Marionette.Object.extend({

            initialize: function() {
                this.listenTo(Backbone.Radio.channel("submit"), "validate:field", this.validateRequired);
                this.listenTo(Backbone.Radio.channel("fields"), "change:modelValue", this.validateRequired);

                this.listenTo(nfRadio.channel("listselect"), 'render:view', this.listselectRender);
                this.listenTo(nfRadio.channel("fileuploadfield"), 'render:view', this.dropzoneRender);
            },


            // ==================================================
            // > FRONT END VALIDATION
            // ==================================================
            validateRequired: function( model ) {
                var value = model.get("value"),
                    id    = model.get("id");

                switch (model.get("type")) {

                    // ========== LOGIN FIELD ========== //
                    case "login":
                        if( validateEmail(value) )
                            Backbone.Radio.channel("fields").request("remove:error", id, "login-error");
                        else
                            Backbone.Radio.channel("fields").request("add:error", id, "login-error", "Please provide a valid email address.");
                        break;

                    default: return;
                }
            },


            // ==================================================
            // > USE SELECT 2 FOR SELECT INPUT
            // ==================================================
            listselectRender: function (view) {
                $(view.el).find("select").select2({
                    minimumResultsForSearch: 8,
                    placeholder: "Cliquez pour choisir"
                }).change(function () {
                    view.model.attributes.value = $(this).val();
                    if (view.model.attributes.value) {
                        Backbone.Radio.channel( 'fields' ).request( 'remove:error', view.model.id, 'required-error' );
                    }
                });
            },

            // ==================================================
            // > USE DROPZONE FOR FILE UPLOADS
            // ==================================================
            dropzoneRender: function (view) {
                var $hidden = $(view.el).find(".ninja-forms-field"),
                    $input  = $(view.el).find("label"),
                    now     = Date.now();

                $input.dropzone({
                    url: ajaxurl+"?action=syltaen_ajax_upload&time="+now,
                    paramName: view.model.attributes.key,
                    acceptedFiles: view.model.attributes.filetypes,
                    uploadMultiple: false,
                    maxFilesize: view.model.attributes.maxupload,
                    clickable: true,
                    dictDefaultMessage: view.model.attributes.label,
                    dictFileTooBig: "This file is too heavy ({{filesize}}Mb) - Max. authorised : {{maxFilesize}}Mb",
                    dictInvalidFileType: "Ce type de fichier n'est pas autorisé.",
                    uploadprogress: function (file, progress, bytesSent) {
                        if (progress >= 100) {
                            $hidden.val(now+"_"+file.name);
                        }
                    },
                    accept: function (file, done) {
                        done();
                    },
                    init: function () {
                        this.on("addedfile", function (file) {
                            if ($input.find(".dz-preview").size() > 1) {
                                $input.find(".dz-preview").first().remove();
                            }
                        });
                    }
                });

                $input.on("click", "div", function (e) {
                    e.stopPropagation();
                    $input.click();
                })
            }

        });

        // ==================================================
        // > INIT
        // ==================================================
        new NF_SyltaenController();

    }
    // ==================================================
    // > UTILITY
    // ==================================================
    function validateEmail(email) {
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }


});