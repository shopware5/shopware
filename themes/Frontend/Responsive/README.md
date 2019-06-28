# Shopware 5 Responsive Theme

## Description
The Responsive theme is the new default theme which is bundled with Shopware 5. It's based on Smarty 3, HTML5 and CSS3 using the LESS pre-processor. The theme can be customized to your needs with the Theme-Manager module in the Shopware 5 backend.

Additionally third-party web components can be easily added using `npm`.

## Feature / technologies
* Predefined color presets
* Smarty 3 with a block and inheritance system
* Theme specific Smarty plugins
* Snippets can be bundled with the theme
* Completely customizable inheritance system
* HTML5 with structural elements
* Rich data snippets based on [schema.org](http://http://schema.org)
* jQuery 2.x included with a state manager and plugin factory
* LESS support using the built-in preprocessor LESS compiler in Shopware 5
* CSS3 animation with a jQuery fallback using [jQuery.transit](http://ricostacruz.com/jquery.transit/)
* [bower](http://bower.io/) support for third-party web components

## Usage
Using the theme is as easy as selecting it in the Theme-Manager module and you're ready to go.

### Installing third-party components
Before installation new third-party components, please run `npm install` in the Responsive theme directory to download all defined dependencies.

1.) To install new dependencies, switch to the Responsive theme directory in your Shopware installation, e.g. `themes/Frontend/Responsive`

2.) Install and save the dependency using `npm install --save <dependency>`

3.) After installation, please modify the `Gruntfile.js` and map the necessary files from the dependency to the corresponding folder inside the `public/vendors` directory.

4.) Run `npm run build` to move the files accordingly. 

### Using the javascript compressor
We'd implemented a basic javascript in Shopware 5 which concatenates all javascript files. It also strips out all whitespaces and inline comments.

Using it is as easy as adding your files to the ```$javascript``` array in your ```Theme.php```.

```
protected $javascript = [
    'src/js/jquery.your-plugin.js'
];
```

### Using LESS for your stylesheets
The LESS pre-processor in Shopware 5 is very easy to use. When the storefront will be opened, we'll check if a LESS file named ```all.less``` is available in the directory ```frontend/_public/src/less/``` it will be automatically translated to CSS.

### Using additional CSS files
We provide the ability adding additional CSS files to your theme. Using it is as easy as adding your files to the ```$css``` array in your ```Theme.php```.

```
protected $css = [
    'src/css/my-style.css'
];
```

## Bug reporting
If you find any bugs in the theme, please use our public tracker at [https://issues.shopware.com](https://issues.shopware.com) to report them.

## License
The theme is licensed under the MIT license. Please see the included license file for more information.
