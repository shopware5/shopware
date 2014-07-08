# Shopware 5 Bare Theme

- **License**: [New BSD](http://opensource.org/licenses/BSD-3-Clause)
- **Issue-Tracker**: [http://jira.shopware.de/jira](http://jira.shopware.de/jira)

## Description
The bare theme is a simple boilerplate template, which contains the necessary HTML and Smarty structure. All provided javascript files are not included by default, so you can pick whatever you want for your next project.

## Installation
The theme supports [Grunt](http://gruntjs.com/) and [Bower](http://bower.io/), so you need to install the tools.

First of all, please install [node.js](http://nodejs.org/), [npm](https://www.npmjs.org/) and [grunt-cli](https://github.com/gruntjs/grunt-cli).

After that, head over to ```/engine/Shopware/Themes/Bare``` and execute the following command:

```
npm install
bower install
```

Now you're ready to go and can start installing additional web components with [Bower](http://bower.io/) and validate your LESS, CSS and Javascript source code using ```grunt test```.

## Watch mode
We'd included a watch mode into ```grunt```, so you can continously work on your source files and each time you save any of the files, ```grunt``` validates your sources and compiles your LESS files.

If you want to use the mode, please execute the following command in your terminal:

```
grunt watch
```