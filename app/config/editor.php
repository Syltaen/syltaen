<?php


// ==================================================
// > MCE CUSTOM STYLES
// ==================================================
add_filter("tiny_mce_before_init", function ($init_array) {

    $style_formats = [
        /* ========= TITLES ========= */
        [
            "title"=> "Titre",
            "items" => [
                [
                    "title"   => "Titre 1 - H1",
                    "block"   => "h1",
                    "wrapper" => false
                ],
                [
                    "title"   => "Titre 2 - H2",
                    "block"   => "h2",
                    "wrapper" => false
                ],
                [
                    "title"   => "Titre 3 - H3",
                    "block"   => "h3",
                    "wrapper" => false
                ],
                [
                    "title"   => "Titre 4 - H4",
                    "block"   => "h4",
                    "wrapper" => false
                ],
                [
                    "title"   => "Titre 5 - H5",
                    "block"   => "h5",
                    "wrapper" => false
                ],
                [
                    "title"   => "Titre 6 - H6",
                    "block"   => "h6",
                    "wrapper" => false
                ],
                [
                    "title"   => "Sous-titre",
                    "inline"  => "span",
                    "classes" => "subtitle",
                    "wrapper" => false
                ]
            ]
        ],

        /* ========= CALLS ========= */
        [
            "title"=> "Liens",
            "items" => [
                [
                    "title"    => "Bouton",
                    "selector" => "a",
                    "classes"  => "button",
                    "wrapper"  => false
                ],
                [
                    "title"    => "Bouton pleine largueur",
                    "selector" => "a",
                    "classes"  => "button fullwidth",
                    "wrapper"  => false
                ],
                [
                    "title"    => "Lien à crochets",
                    "selector" => "a",
                    "classes"  => "croched-link",
                    "wrapper"  => false
                ],
                // [
                //     "title"    => "Lien souligné",
                //     "selector" => "a",
                //     "classes"  => "underlined",
                //     "wrapper"  => false
                // ]
            ]
        ],

        /* ========= TEXTES ========= */
        [
            "title"=> "Textes",
            "items" => [
                [
                    "title"   => "Couleur : Principale",
                    "inline"  => "span",
                    "classes" => "main-color"
                ],
                [
                    "title"   => "Couleur : Secondaire",
                    "inline"  => "span",
                    "classes" => "secondary-color"
                ],

                [
                    "title"   => "Fonte : Light",
                    "inline"  => "span",
                    "classes" => "font-light"
                ],

                [
                    "title"   => "Texte plus petit",
                    "inline"  => "small"
                ],

                [
                    "title"   => "Nombre incrémenté",
                    "inline"  => "span",
                    "classes" => "incrementor"
                ],

                [
                    "title"   => "Citation",
                    "block"   => "blockquote",
                    "wrapper" => true
                ],
                [
                    "title"   => "Citation - Source",
                    "block"   => "cite",
                    "wrapper" => true
                ]
            ]
        ],

        /* ========= LISTES ========= */
        [
            "title"=> "Listes",
            "items" => [
                [
                    "title"    => "Liste sans style",
                    "selector" => "ul",
                    "classes"  => "unstyled"
                ],
                [
                    "title"    => "Liste de blocs alignés",
                    "selector" => "ul",
                    "classes"  => "blocks-aligned"
                ],
                [
                    "title"    => "Liste ordonnée en colonnes",
                    "selector" => "ol",
                    "classes"  => "point-list"
                ],
                [
                    "title"    => "Liste de documents",
                    "selector" => "ul",
                    "classes"  => "doc-list"
                ]
            ]
        ],

        /* ========= IMAGES ========= */
        [
            "title"=> "Images",
            "items" => [
                [
                    "title"    => "Bord blanc",
                    "selector" => "img",
                    "classes"  => "white-border",
                    "wrapper"  => false
                ],
                [
                    "title"    => "Rond",
                    "selector" => "img",
                    "classes"  => "round",
                    "wrapper"  => false
                ],
                [
                    "title"    => "Taille - Toute la largueur",
                    "selector" => "img",
                    "classes"  => "full-width",
                    "wrapper"  => false
                ],
                [
                    "title"    => "Taille - Toute la hauteur",
                    "selector" => "img",
                    "classes"  => "full-height",
                    "wrapper"  => false
                ]
            ]
        ]
    ];

    $init_array["style_formats_merge"] = false;
    $init_array["style_formats"] = json_encode($style_formats);
    return $init_array;

} );


// ==================================================
// > EDITOR STYLESHEET
// ==================================================
add_editor_style("build/css/bundle.css");


// ==================================================
// > CONFIG
// ==================================================
/**
 * TinyMCE Editor settings.
 * Copy-past it on this page :
 * @see /wp-admin/options-general.php?page=tinymce-advanced

{"settings":{"toolbar_1":"styleselect,fontsizeselect,bold,italic,underline,forecolor,bullist,numlist,aligncenter,alignleft,alignright,image,link,unlink,wp_adv","toolbar_2":"undo,redo,removeformat,pastetext,anchor,visualchars,nonbreaking,charmap,code,visualblocks,fullscreen,wp_help","toolbar_3":"","toolbar_4":"","options":"contextmenu,advlink,menubar,fontsize_formats","plugins":"anchor,code,insertdatetime,nonbreaking,print,searchreplace,table,visualblocks,visualchars,link,contextmenu"},"admin_settings":{"options":"no_autop","disabled_editors":""}}

*/