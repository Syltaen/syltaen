# Syltaen
A custom-made WordPress theme using an MVC approach with Jade, SASS, CoffeeScript and Timber

## Step-by-step installation & Configuration using WP-CLI
### WordPress core
>Download
```
wp core download --locale=fr_FR
````
>Create wp-config.php
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
???
```
Install and activate "Syltaen"
```
wp theme install https://github.com/Syltaen/syltaen/archive/master.zip --active
```
