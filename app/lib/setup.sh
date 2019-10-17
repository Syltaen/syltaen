#!/bin/bash


# Download & Install WordPress
wp core download --locale=fr_FR


# Create wp-config.php with given db info
wp core config --dbuser=root --dbpass=root --prompt=dbprefix,dbname


# Install WordPress, try and guess config based on current user
URL=$(pwd)

if [ $(whoami) = "stanley.lambot" ]; then
    URL=http://${URL/\/Users\/stanley.lambot\//}
    wp core install --url=${URL} --admin_user=Syltaen --admin_email=stanley.lambot@hungryminds.be --prompt=title
elif [ $(whoami) == "jerome.renders" ]; then
    URL=http://${URL/\/Users\/jerome.renders\//}
    wp core install --url=${URL} --admin_user=jerome.renders --admin_email=jerome.renders@hungryminds.be --prompt=title
else
    wp core install --prompt=title,url,admin_user,admin_email
fi


# Remove all themes, create a symlink to this one and activate it
rm -rd wp-content/themes
mkdir wp-content/themes
cd wp-content/themes
ln -s ../../syltaen
wp theme activate syltaen


# Install composer vendors
cd ../../syltaen
composer install -d=app/vendors


# Install npm modules
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
wp plugin install https://github.com/Hungry-Minds/hungryminds-cookies --activate


# Create a symlink to the theme plugin
cd ../wp-content/plugins
ln -s ../../syltaen/app/lib/plugin syltaen-plugin
wp plugin activate syltaen-plugin


# Launch Theme setup
cd ../../syltaen
wp syltaen setup