<?php

/*
    Loco Translate can't detect strings that are included in pug files.
    To fix that, Files::scanPugTranslations() generates this file
    to still include the found strings in the .pot template.
*/


//> 404.pug;
__("Erreur 404", "syltaen");
__("Cette page n'existe pas", "syltaen");
__("Retour à l'accueil", "syltaen");


//> _card-news.pug;
__("Lire plus", "syltaen");


//> search.pug;
__("Recherche", "syltaen");
__("Modifier votre recherche", "syltaen");