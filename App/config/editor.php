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
                ],
            ]
        ],

        /* ========= CALLS ========= */
        [
            "title"=> "Liens",
            "items" => [
                [
                    "title" => "Bouton",
                    "selector" => "a",
                    "classes" => "button",
                    "wrapper" => false
                ],
                [
                    "title" => "Bouton foncé",
                    "selector" => "a",
                    "classes" => "button _dark",
                    "wrapper" => false
                ],
                [
                    "title" => "Lien à crochets",
                    "selector" => "a",
                    "classes" => "croched-link",
                    "wrapper" => false
                ]
            ]
        ],

        /* ========= TEXTES ========= */
        [
            "title"=> "Textes",
            "items" => [
                [
                    "title" => "Couleur : Principale",
                    "inline" => "span",
                    "classes" => "main-color"
                ],
                [
                    "title" => "Couleur : Secondaire",
                    "inline" => "span",
                    "classes" => "secondary-color"
                ],

                [
                    "title" => "Fonte : Light",
                    "inline" => "span",
                    "classes" => "font-light"
                ],
                [
                    "title" => "Fonte : Black",
                    "inline" => "span",
                    "classes" => "font-black"
                ],

                [
                    "title" => "Nombre incrémenté",
                    "inline" => "span",
                    "classes" => "incrementor"
                ],

                [
                    "title" => "Citation",
                    "block" => "blockquote",
                    "wrapper" => true
                ],
                [
                    "title" => "Citation - Source",
                    "block" => "cite",
                    "wrapper" => true
                ]
            ]
        ],

        /* ========= LISTES ========= */
        [
            "title"=> "Listes",
            "items" => [
                [
                    "title" => "Liste sans style",
                    "selector" => "ul",
                    "classes" => "unstyled"
                ]
            ]
        ],

        /* ========= IMAGES ========= */
        [
            "title"=> "Images",
            "items" => [
                [
                    "title" => "Bord blanc",
                    "selector" => "img",
                    "classes" => "white-border",
                    "wrapper" => false
                ],
                [
                    "title" => "Rond",
                    "selector" => "img",
                    "classes" => "round",
                    "wrapper" => false
                ],
                [
                    "title" => "Taille - Toute la largueur",
                    "selector" => "img",
                    "classes" => "full-width",
                    "wrapper" => false
                ],
                [
                    "title" => "Taille - Toute la hauteur",
                    "selector" => "img",
                    "classes" => "full-height",
                    "wrapper" => false
                ],
            ]
        ],
    ];
    $init_array["style_formats_merge"] = false;
    $init_array["style_formats"] = json_encode( $style_formats );
    return $init_array;

} );


// ==================================================
// > EDITOR STYLESHEET
// ==================================================
add_editor_style("src/css/styles.min.css");