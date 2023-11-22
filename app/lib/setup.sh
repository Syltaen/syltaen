#!/bin/bash


# Download & Install WordPress
wp core download --locale=fr_FR


# Create wp-config.php with given db info
wp core config --dbuser=root --dbpass=root --dbhost=localhost --prompt=dbprefix,dbname


# Install WordPress, try and guess config based on current user
URL=$(pwd)

if [ $(whoami) = "stanley.lambot" ]; then
    URL=http://localhost/${URL/\/opt\/www\//}
    wp core install --url=${URL} --admin_user=Syltaen --admin_email=stanley.lambot@hungryminds.be --prompt=title
elif [ $(whoami) == "jerome.renders" ]; then
    URL=http://${URL/\/Users\/jerome.renders\//}
    wp core install --url=${URL} --admin_user=jerome.renders --admin_email=jerome.renders@hungryminds.be --prompt=title
else
    wp core install --prompt=title,url,admin_user,admin_email
fi

# Install composer vendors
cd ./syltaen/app/vendors
composer install


# Install npm modules
cd ./../../
npm install


# Remove all themes, create a symlink to this one and activate it
cd ./../
rm -rd wp-content/themes
mkdir wp-content/themes
cd wp-content/themes
ln -s ../../syltaen
wp theme activate syltaen

# Remove unused plugins and install suggested ones
wp plugin delete $(wp plugin list --status=inactive --field=name)
wp plugin install tinymce-advanced --activate
wp plugin install ninja-forms --activate
wp plugin install codepress-admin-columns --activate
wp plugin install admin-menu-editor --activate
wp plugin install all-in-one-wp-migration --activate
wp plugin install user-role-editor --activate
wp plugin install advanced-custom-fields-font-awesome --activate
wp plugin install duplicate-post --activate
wp plugin install wp-fastest-cache --activate
wp plugin install wordpress-seo --activate
wp plugin install analytics-insights --activate
wp plugin install cookie-law-info --activate
wp plugin install acf-extended --activate
wp plugin install wp-mail-catcher --activate
wp plugin install simple-custom-post-order --activate

# Create a symlink to the theme plugin
cd ../plugins
ln -s ../../syltaen/app/lib/plugin syltaen-plugin
wp plugin activate syltaen-plugin


# Launch Theme setup
cd ../../syltaen
wp syltaen setup