# Syltaen
A custom-made WordPress theme using an MVC approach with Jade, SASS, CoffeeScript and Timber

## Step-by-step installation & Configuration using WP-CLI

### 1 - WordPress core

Download
```
wp core download --locale=fr_FR
````
Create wp-config.php
```
wp core config --prompt
```
Install database
```
wp core install --prompt
```
-
### 2 - Themes
Removal of unused themes
```
rm -rd wp-content/themes
```
Install and activate this one from the GitHub repository
```
wp theme install https://github.com/Syltaen/syltaen/archive/master.zip --activate
```
-
### 3 - Theme dependencies
Install dependencies via Composer
>```
>cd wp-content/themes/syltaen/_1_functions/_2_vendors
>composer install
>```
-
### 4 - Plugins
Removal of unused plugins
```
wp plugin delete $(wp plugin list --status=inactive --field=name)
````
Installation of some usefull plugins

>Timber
>```
>wp plugin install timber-library --activate
>```
-
ACF Pro
>```
! require manual install from https://www.advancedcustomfields.com/my-account/
>```
-
TinyMCE Advanced
>```
>wp plugin install tinymce-advanced --activate
>```
-
> Ninja Forms
>```
>wp plugin install ninja-forms --activate
>```
-
Admin Columns
>```
>wp plugin install codepress-admin-columns --activate
>```
-
Admin Menu Editor
>```
>wp plugin install admin-menu-editor --activate
>```
-
All-in-One WP Migration
>```
>wp plugin install all-in-one-wp-migration --activate
>```
-
User Role Editor
>```
>wp plugin install user-role-editor --activate
>```


## Do everything at the same time:
Note : Don't forget to replace the ***
```
# Download & Install WordPress
wp core download --locale=fr_FR;
wp core config --dbname=***** --dbuser=root --dbpass=root --dbprefix=wp_ --extra-php --force;
wp core install --url=http://localhost/***** --title=Temp --admin_user=Syltaen --admin_email=stanley.lambot@hungryminds.be:
# Remove themes and install the right one and its dependencies
rm -rd wp-content/themes;
wp theme install https://github.com/Syltaen/syltaen/archive/master.zip --activate;
cd wp-content/themes/syltaen/_1_functions/_2_vendors && composer install;
# Remove unused plugins and install required ones
wp plugin delete $(wp plugin list --status=inactive --field=name);
wp plugin install timber-library --activate;
```