# Syltaen
A custom-made WordPress theme using an MVC approach with Jade, SASS, CoffeeScript and Timber

## Step-by-step installation & Configuration using WP-CLI
>### WordPress core
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

### Themes
Remove unused themes
```
rm -rd wp/content/themes
```
Installation and activatation
```
wp theme install https://github.com/Syltaen/syltaen/archive/master.zip --activate
```

### Plugins
Removal of unused plugins
```
wp plugin delete $(wp plugin list --status=inactive --field=name)
````

Installation of some usefull plugins
> Ninja forms
```
wp plugin install ninja-forms --activate
```
