# Shopware 5 Responsive Theme

## Description
The Responsive Theme is the new default theme which is bundled with Shopware 5. It's based on Smarty 3, HTML5 and CSS3 using the LESS processor. The theme can be customized to your needs the Theme-Manager module in the Shopware 5 backend.

Additionally the theme supports [bower](http://bower.io/), so additional third-party web components can be easily added.

## Feature / technologies
* Predefined color presets
* Smarty 3 with a block and inheritance system
* Theme specific Smarty plugins
* Snippets can be bundled with the theme
* Completely customimazible inheritance system
* HTML5 with it' structual elements
* Rich data snippets based on [schema.org](http://http://schema.org)
* jQuery 2.x included with a state manager and plugin factory
* LESS support using the built-in preprocessor LESS compiler in Shopware 5
* CSS3 animation with a jQuery fallback using [jQuery.transit](http://ricostacruz.com/jquery.transit/)
* [bower](http://bower.io/) support for third-party web components

## Usage
Using the theme is as easy as selecting it in the Theme-Manager module and you're ready to go.

### Installing third-party components
1.) Open up the ```bower.json``` file which can be found in the root directory of the theme and insert your third-party component in the ```dependencies``` object, like this:

```
...
"dependencies": {
    "jquery": "2.1.1"
}
...
```

2.) Now install the development dependencies using ```npm install``` in the root directory of the theme.

3.) After installing the developement dependencies, you just have to install ```grunt``` and your newly added third-party component will be installed in the directory ```frontend/_public/vendors```.

### Using the javascript compressor
We'd implemented a basic javascript in Shopware 5 which concatenates all javascript files. It also strips out all whitespaces and inline comments.

Using it is as easy as adding your files to the ```$javascript``` array in your ```Theme.php```.

```
protected $javascript = array(
	'src/js/jquery.your-plugin.js'
);
```

### Using LESS for your stylesheets
The LESS compiler in Shopware 5 is very easy to use. When the storefront will be opened, we'll check if a LESS file named ```all.less``` is available in the directory ```frontend/_public/src/less/``` it will be automatically compiled to CSS.

### Using additional CSS files
We provide the ability adding additional CSS files to your theme. Using it is as easy as adding your files to the ```$css``` array in your ```Theme.php```.

```
protected $css = array(
	'src/css/my-style.css'
);
```


## Bug reporting
If you find any bugs in the theme, please use our public tracker at [https://issues.shopware.com](https://issues.shopware.com) to report them.


## License
The theme is licensed under the MIT License. Please see the included License File for more information.
