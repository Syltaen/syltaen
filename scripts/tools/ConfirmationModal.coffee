import $ from "jquery"
import Shadowbox from "./../tools/Shadowbox.coffee"

export default class ConfirmationModal

    constructor: (@$el) ->
        @modal   = @createModal @$el.data("confirm")

        @$el.on "click.confirm", (e) =>
            e.preventDefault()
            @modal.show()


    ###
    # Create a shadowbox and a modal with a custom message
    ###
    createModal: (message) ->
        sb       = new Shadowbox
        $modal   = $("<div class='shadowbox__modal'><p>#{message}</p></div>")
        $actions = $("<div class='shadowbox__modal__actions'></div>")
        $confirm = $("<span class='button button--green shadowbox__modal__action shadowbox__modal__action--confirm' data-action='close'>Ok</span>")
        $cancel  = $("<span class='button shadowbox__modal__action shadowbox__modal__action--cancel' data-action='close'>Annuler</span>")

        $modal.append $actions
        $actions.append $confirm
        $actions.append $cancel
        sb.$content.append $modal
        $modal.append sb.$close

        $confirm.click => @confirm()

        return sb


    ###
    # Confirm one and for all
    ###
    confirm: ->
        @$el.off("click.confirm")
        @$el.click()

        if @$el.attr "href"
            window.location = @$el.attr "href"