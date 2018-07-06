import $ from "jquery"

# ==================================================
# > FORMS LIST
# ==================================================
class FormLoader
    constructor: ->
        @storedForms = @registerForms()
        @loadedForms = []

    ###
    # Get all pre-loaded forms and store them out of the DOM
    ###
    registerForms: ->
        storage = {}
        $(".nf-form-loaded").each (i, el) ->
            storage[$(el).data("id")] = $(el)
            # $(el).remove()
        return storage

    ###
    # When arriving : get needed forms from storage
    ###
    in: ->
        unless $(".nf-form-loader").length then return false

        $(".nf-form-loader").each (i, el) =>
            $(el).append @storedForms[$(el).data("id")]

    ###
    # When leaving : put back all forms into storage
    ###
    out: ->


# ==================================================
# > EXPORT
# ==================================================
export default new FormLoader