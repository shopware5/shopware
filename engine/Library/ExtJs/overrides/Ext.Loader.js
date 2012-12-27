/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @package    Enlight_ExtJs
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Custom Ext.loader getPath method
 *
 * This bends the controller/model/store/view paths
 * to our shopware default paths.
 *
 * @category   Enlight
 * @package    Enlight_ExtJs
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
Ext.Loader.getPath = function(className, prefix) {
	var path = '',
		paths = this.config.paths,
		suffix = this.config.suffixes[prefix] !== undefined ? this.config.suffixes[prefix] : '.js';

	if (prefix.length > 0) {
		if (prefix === className) {
			return paths[prefix];
		}

		path = paths[prefix];
		className = className.substring(prefix.length + 1);
	}

	if (path.length > 0) {
        path = path.replace(/\/+$/, '') + '/';
	}

	return [path.replace(/\/\.\//g, '/'), className.replace(/\./g, "/") + suffix];
};

Ext.Loader.config.disableCaching = false;
Ext.Loader.config.caching = true;

Ext.Loader.require = function(expressions, fn, scope, excludes) {
    var Manager = Ext.ClassManager;
    var filePath, expression, exclude, className, excluded = {},
        excludedClassNames = [],
        possibleClassNames = [],
        possibleClassName, classNames = [],
        namespaces = {}, namespace, prefix,
        i, j, ln, subLn;

    if(!this.getConfig('disableCachingValue')) {
        this.setConfig('disableCachingValue', Ext.Date.now());
    }
    if(this.getConfig('disableCaching')) {
        this.setConfig('caching', false);
        this.setConfig('disableCaching', false);
    }

    var disableCachingValue = this.getConfig('disableCachingValue'),
        disableCaching = !this.getConfig('caching');

    expressions = Ext.Array.from(expressions);
    excludes = Ext.Array.from(excludes);

    fn = fn || Ext.emptyFn;

    scope = scope || Ext.global;

    for (i = 0, ln = excludes.length; i < ln; i++) {
        exclude = excludes[i];

        if (typeof exclude === 'string' && exclude.length > 0) {
            excludedClassNames = Manager.getNamesByExpression(exclude);

            for (j = 0, subLn = excludedClassNames.length; j < subLn; j++) {
                excluded[excludedClassNames[j]] = true;
            }
        }
    }

    for (i = 0, ln = expressions.length; i < ln; i++) {
        expression = expressions[i];

        if (typeof expression === 'string' && expression.length > 0) {
            possibleClassNames = Manager.getNamesByExpression(expression);

            for (j = 0, subLn = possibleClassNames.length; j < subLn; j++) {
                possibleClassName = possibleClassNames[j];

                if (!excluded.hasOwnProperty(possibleClassName) && !Manager.isCreated(possibleClassName)) {
                    Ext.Array.include(classNames, possibleClassName);
                }
            }
        }
    }

    // If the dynamic dependency feature is not being used, throw an error
    // if the dependencies are not defined
    if (!this.config.enabled) {
        if (classNames.length > 0) {
            Ext.Error.raise({
                sourceClass: "Ext.Loader",
                sourceMethod: "require",
                msg: "Ext.Loader is not enabled, so dependencies cannot be resolved dynamically. " +
                     "Missing required class" + ((classNames.length > 1) ? "es" : "") + ": " + classNames.join(', ')
            });
        }
    }

    if (classNames.length === 0) {
        fn.call(scope);
        return this;
    }

    this.queue.push({
        requires: classNames,
        callback: fn,
        scope: scope
    });

    classNames = classNames.slice();


    for (i = 0, ln = classNames.length; i < ln; i++) {
        className = classNames[i];

        if (!this.isFileLoaded.hasOwnProperty(className)) {
            this.isFileLoaded[className] = false;

            prefix = this.getPrefix(className);
            namespace = this.getPath(className, prefix);
            filePath = namespace.join('');

            this.classNameToFilePathMap[className] = filePath;

            this.numPendingFiles++;

            if(this.config.bulk[prefix]) {
                if(!namespaces[prefix]) {
                   namespaces[prefix] = { 'prefix': prefix, 'path': namespace[0], 'files': [], 'classNames': [] };
                }
                namespaces[prefix]['files'].push(namespace[1]);
                namespaces[prefix]['classNames'].push(className);
//                if(namespaces[prefix]['files'].length >= 20) {
//                    namespaces[className] = namespaces[prefix];
//                    delete namespaces[prefix];
//                }
            } else {
                if(disableCaching) {
                    filePath += filePath.indexOf('?') === -1 ? '?' : '&';
                    filePath += this.getConfig('disableCachingParam') + '=' + disableCachingValue;
                }
                this.loadScriptFile(
                    filePath,
                    Ext.Function.pass(this.onFileLoaded, [className, filePath], this),
                    Ext.Function.pass(this.onFileLoadError, [className, filePath]),
                    this,
                    this.syncModeEnabled
                );
            }
        }
    }

    Ext.iterate(namespaces, function(key, namespace){
//        var files, path;
//        files = namespace.files.join('&file[]=');
//        path = namespace.path + '?file[]=' + files;
//        if(disableCaching) {
//            path += '&' + this.getConfig('disableCachingParam') + '=' + disableCachingValue;
//        }
//        this.loadScriptFile(
//            path,
//            Ext.Function.pass(this.onFilesLoaded, [namespace.classNames], this),
//            Ext.Function.pass(this.onFileLoadError, [namespace.classNames]),
//            this,
//            this.syncModeEnabled
//        );

        var path = namespace.path,
            cacheId, cacheKey;

        if(disableCaching) {
           path += '?' + this.getConfig('disableCachingParam') + '=' + disableCachingValue;
        }
        
        try {
	        if(typeof(Storage)!=="undefined" && typeof(localStorage)!=="undefined") {
	            cacheId = disableCachingValue;
	            cacheKey = path + ',' + namespace.files.join(',');
	            if(typeof(localStorage.cacheId)!=="undefined"
	              && localStorage.cacheId != cacheId) {
	                localStorage.clear();
	            }
	            localStorage.cacheId = cacheId;
	            if(localStorage[cacheKey]) {
	                Ext.globalEval(localStorage[cacheKey] + "\n//@ sourceURL=" + path);
	                this.onFilesLoaded(namespace.classNames);
	                return;
	            }
	        }
        } catch(e) { }

        Ext.Ajax.request({
            url: path,
            disableCaching: false,
            async: false,
            params: {
                'file[]': namespace.files
            },
            scope: this,
            success: function(response){
                Ext.globalEval(response.responseText + "\n//@ sourceURL=" + path);
                this.onFilesLoaded(namespace.classNames);
                if(cacheKey) {
                    localStorage[cacheKey] = response.responseText;
                }
            }
        });

    }, this);

    return this;
};


/**
 * @private
 * @param { String } classNames
 */
Ext.Loader.onFilesLoaded = function(classNames) {
    var me = this;

    Ext.iterate(classNames, function(className){
        me.numLoadedFiles++;
        me.isFileLoaded[className] = true;
        me.numPendingFiles--;
    });

    if (this.numPendingFiles === 0) {
        this.refreshQueue();
    }
};

/**
 * Sets the path of a namespace. For Example:
 *
 *     Ext.Loader.setPath('Ext', '.');
 *
 * @param { String/Object } name See { @link Ext.Function#flexSetter flexSetter }
 * @param { String } path See { @link Ext.Function#flexSetter flexSetter }
 * @return { Ext.Loader } this
 * @method
 */
Ext.Loader.setPath = function(name, path, suffix, bulk) {
    this.config.paths[name] = path;
    if(this.config.suffixes === undefined) {
        this.config.suffixes = [];
    }
    if(suffix !== undefined) {
        this.config.suffixes[name] = suffix;
    }
    if(this.config.bulk === undefined) {
        this.config.bulk = [];
    }
    if(bulk !== undefined) {
        this.config.bulk[name] = bulk;
    }
    return this;
};
