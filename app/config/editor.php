<?php


// ==================================================
// > MCE CUSTOM STYLES
// ==================================================
add_filter("tiny_mce_before_init", function ($init_array) {

    $style_formats = [

        /* ========= TEXTES ========= */
        [
            "title"=> "Textes",
            "items" => [

                [
                    "title" => "Couleurs",
                    "items" => [
                        [
                            "title"   => "Defaut",
                            "inline"  => "span",
                            "classes" => "color-text"
                        ],
                        [
                            "title"   => "Blanc",
                            "inline"  => "span",
                            "classes" => "color-white"
                        ],
                        [
                            "title"   => "Gris clair",
                            "inline"  => "span",
                            "classes" => "color-light-grey"
                        ],
                        [
                            "title"   => "Gris foncé",
                            "inline"  => "span",
                            "classes" => "color-dark-grey"
                        ],
                        [
                            "title"   => "Principale",
                            "inline"  => "span",
                            "classes" => "color-primary"
                        ],
                        [
                            "title"   => "Secondaire",
                            "inline"  => "span",
                            "classes" => "color-secondary"
                        ]
                    ]
                ],

                [
                    "title" => "Alignements",
                    "items" => [
                        [
                            "title"   => "Centre",
                            "classes" => "text-align-center",
                            "wrapper" => true,
                            "selector" => "*"
                        ],
                        [
                            "title"   => "Gauche",
                            "classes" => "text-align-left",
                            "wrapper" => true,
                            "selector" => "*"
                        ],
                        [
                            "title"   => "Droite",
                            "classes" => "text-align-right",
                            "wrapper" => true,
                            "selector" => "*"
                        ],
                    ]
                ],

                [
                    "title" => "Fontes",
                    "items" => [
                        [
                            "title"   => "Light",
                            "inline"  => "span",
                            "classes" => "font-light"
                        ],
                        [
                            "title"   => "Regular",
                            "inline"  => "span",
                            "classes" => "font-regular"
                        ],
                        [
                            "title"   => "Bold",
                            "inline"  => "span",
                            "classes" => "font-bold"
                        ],
                    ]
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
                    "title"   => "Titre 2 - H2 - alternatif",
                    "block"   => "h2",
                    "wrapper" => false,
                    "classes" => "alt-title"
                ],
                [
                    "title"   => "Titre 2 - H2 - alternatif blanc",
                    "block"   => "h2",
                    "wrapper" => false,
                    "classes" => "alt-title alt-title--white"
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
                    "classes"  => "button button--fullwidth",
                    "wrapper"  => false
                ],
                [
                    "title"    => "Lien à crochets",
                    "selector" => "a",
                    "classes"  => "croched-link",
                    "wrapper"  => false
                ],
                [
                    "title"    => "Lien souligné",
                    "selector" => "a",
                    "classes"  => "underlined",
                    "wrapper"  => false
                ]
            ]
        ],



        /* ========= LISTES ========= */
        [
            "title"=> "Listes",
            "items" => [
                [
                    "title" => "Puces",
                    "items" => [
                        [
                            "title"    => "Carets",
                            "selector" => "ul",
                            "classes"  => "list list--carrets"
                        ],
                        [
                            "title"    => "Angles",
                            "selector" => "ul",
                            "classes"  => "list list--angles"
                        ],
                        [
                            "title"    => "Flêches",
                            "selector" => "ul",
                            "classes"  => "list list--arrows"
                        ],
                        [
                            "title"    => "Checks",
                            "selector" => "ul",
                            "classes"  => "list list--checks"
                        ],
                    ],
                ],
                [
                    "title" => "Dispositions",
                    "items" => [
                        [
                            "title"    => "Vertical centré",
                            "selector" => "ul",
                            "classes"  => "list list--vertical-centered"
                        ],
                        [
                            "title"    => "Horizontal",
                            "selector" => "ul",
                            "classes"  => "list list--horizontal"
                        ],
                    ],
                ],
                [
                    "title"    => "Liste sans style",
                    "selector" => "ul",
                    "classes"  => "unstyled"
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
add_editor_style("build/css/admin.css");

// ==================================================
// > CONFIG
// ==================================================
/**
 * TinyMCE Editor settings.
 * Copy-past it on this page :
 * @see /wp-admin/options-general.php?page=tinymce-advanced

{"settings":{"toolbar_1":"styleselect,fontsizeselect,bold,italic,underline,forecolor,bullist,numlist,aligncenter,alignleft,alignright,image,link,unlink,wp_adv","toolbar_2":"undo,redo,removeformat,pastetext,anchor,visualchars,nonbreaking,charmap,code,visualblocks,fullscreen,wp_help","toolbar_3":"","toolbar_4":"","options":"contextmenu,advlink,menubar,fontsize_formats","plugins":"anchor,code,insertdatetime,nonbreaking,print,searchreplace,table,visualblocks,visualchars,link,contextmenu"},"admin_settings":{"options":"no_autop","disabled_editors":""}}

*/
