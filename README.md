- Block section : spacing, colors, conditional display (?)
- Blokcs pour les post types
- Rendu block ACF ?
- Rendu block Ninja Form ?
- classe pour les blocs non-acf
- Section obliée, puis contenu
- anciennes options tinyMCE ?
- auto-register des models, taxonomies, blocs, ...
- Couleurs auto-générées depuis varables.scss ?



# Syltaen

MVC framework and boilerplate for every Hungry Mind's WordPress projects.

## Installation

Make sure you have [WP-CLI](http://wp-cli.org/), [Composer](https://getcomposer.org/) and [npm](https://nodejs.org/) installed


```bash
# Clone the theme at the root of your new project
git clone https://github.com/Syltaen/syltaen

# Run the setup to install WordPress and all dependencies
./syltaen/app/lib/setup.sh
```


## CLI Usage
```bash
# Make : create a new class from a template
wp syltaen make (post|tax|controller|processor|helper|style|script) [name]
```