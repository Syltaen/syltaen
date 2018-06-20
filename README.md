# Syltaen

MVC framework and boilerplate for every Hungry Mind's WordPress projects.

## Installation

Make sure you have [WP-CLI](http://wp-cli.org/), [Composer](https://getcomposer.org/) and [npm](https://nodejs.org/) installed
Note : Don't forget to replace the ##### and your credentials

```bash
# Download & Install WordPress
wp core download --locale=fr_FR
wp core config --dbname=##### --dbuser=root --dbpass=root --dbprefix=wp_
wp core install --url=http://localhost/##### --title=Temp --admin_user=Syltaen --admin_email=stanley.lambot@hungryminds.be

# Remove themes and install this one and its dependencies
rm -rd wp-content/themes
wp theme install https://github.com/Syltaen/syltaen/archive/master.zip --activate
cd wp-content/themes/syltaen/app/vendors
composer install
cd ../..
npm install

# Remove unused plugins and install suggested ones
wp plugin delete $(wp plugin list --status=inactive --field=name)
wp plugin install tinymce-advanced --activate
wp plugin install ninja-forms --activate
wp plugin install codepress-admin-columns --activate
wp plugin install admin-menu-editor --activate
wp plugin install all-in-one-wp-migration --activate
wp plugin install user-role-editor --activate
wp plugin install advanced-custom-fields-font-awesome
wp plugin install duplicate-post
wp plugin install wp-fastest-cache
wp plugin install wordpress-seo
wp plugin install google-analytics-dashboard-for-wp

mv app/Forms/_syltaen_ninjaforms_autoload.php ../../plugins
wp plugin activate _syltaen_ninjaforms_autoload
```