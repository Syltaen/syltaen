var el                = wp.element.createElement,
    // Fragment       = wp.element.Fragment,
    // BlockControls  = wp.editor.BlockControls,
    InnerBlocks       = wp.editor.InnerBlocks,
    InspectorControls = wp.editor.InspectorControls,
    PanelBody         = wp.components.PanelBody,
    SelectControl     = wp.components.SelectControl;

    console.log("yes?");

    wp.blocks.registerBlockType("syltaen/section", {

    title:    "Section",
    icon:     {
        src:  "align-center",
        foreground: "red"
    },
    category: "common",

    // ==================================================
    // > ATTRIBUTES
    // ==================================================
    attributes: {
        id: {
            type: "string"
        },
        // background: {

        // },
        // color: {

        // },
        spacing: {
            type: "string"
        }
    },


    // ==================================================
    // > EDITOR PREVIEW
    // ==================================================
    edit: function(props) {

        return [
            el(
                InspectorControls, null,
                el(
                    PanelBody, {
                        title: "Apparence"
                    },
                    el(SelectControl, {
                        label: "Espacements",
                        value: props.attributes.spacing,
                        options: [
                            {label: "Moyen", value: "md"},
                            {label: "Grand", value: "lg"},
                            {label: "Aucun", value: "no"},
                        ],
                        onChange: function (value) { props.setAttributes({spacing: value}) }
                    })
                )
            ),
            el(
                "section", {
                    className: props.className,
                },
                el(
                    "div", {
                        className: "container"
                    },
                    el(InnerBlocks, {
                        unallowedBlocks: ["syltaen/section"]
                    })
                )
            )
        ];


        // return el(Fragment, null,

        //     el(BlockControls, null, el(AlignmentToolbar, {
        //         value: props.attributes.alignment,
        //         onChange: function (newAlignment) {
        //             props.setAttributes({alignment: newAlignment})
        //         }
        //     })),


        //     el(RichText, {
        //         // key: "editable",
        //         tagName:   "p",
        //         className: props.className,
        //         value:     props.attributes.content,
        //         style: {
        //             textAlign: props.attributes.alignment
        //         },
        //         onChange: function (newContent) {
        //             props.setAttributes({content: newContent});
        //         }
        //     })
        // );
    },








    // ==================================================
    // > HTML GENERATION
    // ==================================================
    save: function(props) {
        return el("section", {
            className: props.className,
            style: {
                padding: "60px 0",
            },
        }, el("div", {
            className: "container"
        }, el(InnerBlocks.Content)));
    }
} );