# Syltaen

MVC framework and boilerplate for every Hungry Mind's WordPress projects.

## Installation

Make sure you have [WP-CLI](http://wp-cli.org/), [Composer](https://getcomposer.org/) and [npm](https://nodejs.org/) installed

### Download WordPress core

```bash
wp core download --locale=fr_FR
```

### Create wp-config.php

```bash
wp core config --prompt
```

### Install database

```bash
wp core install --prompt
```

### Remove all unused themes & plugins

```bash
rm -rd wp-content/themes
wp plugin delete $(wp plugin list --status=inactive --field=name)
```

### Install and activate this one from the GitHub repository

```bash
wp theme install https://github.com/Syltaen/syltaen/archive/master.zip --activate
```

### Install the theme dependencies from Composer

```bash
cd wp-content/themes/syltaen/app/vendors
composer install
```

### Install the theme dependencies from npm

```bash
cd ../../
npm install
```


### Install usefull plugins

```bash
# TinyMCE Advanced
wp plugin install tinymce-advanced --activate

# Ninja Forms
wp plugin install ninja-forms --activate

# Admin Columns
wp plugin install codepress-admin-columns --activate

# Admin Menu Editor
wp plugin install admin-menu-editor --activate

# All-in-One WP Migration
wp plugin install all-in-one-wp-migration --activate

# User Role Editor
wp plugin install user-role-editor --activate

# ACF Pro
# ! require manual install from https://www.advancedcustomfields.com/my-account/
```

### Move the forms modules autoloader in the plugin directory and activate it

```bash
mv app/Forms/_syltaen_ninjaforms_autoload.php ../../plugins
wp plugin activate _syltaen_ninjaforms_autoload
```

## Everything in one command

Note : Don't forget to replace the ##### and your credentials

```bash
# Download & Install WordPress
wp core download --locale=fr_FR
wp core config --dbname=##### --dbuser=root --dbpass=root --dbprefix=wp_
wp core install --url=http://localhost/##### --title=Temp --admin_user=Syltaen --admin_email=stanley.lambot@hungryminds.be

# Remove themes and install the right one and its dependencies
rm -rd wp-content/themes
wp theme install https://github.com/Syltaen/syltaen/archive/master.zip --activate
cd wp-content/themes/syltaen/app/vendors
composer install
cd ../..
npm install

# Remove unused plugins and install required ones
wp plugin delete $(wp plugin list --status=inactive --field=name) &&
wp plugin install tinymce-advanced --activate
wp plugin install ninja-forms --activate
wp plugin install codepress-admin-columns --activate
wp plugin install admin-menu-editor --activate
wp plugin install all-in-one-wp-migration --activate
wp plugin install user-role-editor --activate
mv app/Forms/_syltaen_ninjaforms_autoload.php ../../plugins
wp plugin activate _syltaen_ninjaforms_autoload
```