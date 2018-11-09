/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Custom Ext.loader getPath method
 *
 * This bends the controller/model/store/view paths
 * to our shopware default paths.
 */
//{block name="extjs/overrides/loader"}
(function() {
    /**
     * Currently open loader requests
     *
     * @type { Object }
     */
    var requestMap = {};

    /**
     * Create and return a requestKey (cacheKey) for the given path and files
     *
     * @param { string } path
     * @param { string|array } files
     * @return { string }
     */
    var getRequestKey = Ext.bind(function(path, files) {
        files = (typeof files == 'string') ? [files] : files;

        return path + ',' + files.join(',');
    }, Ext.Loader);

    /**
     * Abort the currently running async request with the given requestInfo
     *
     * @param { object }
     */
    var abortAsyncRequest = Ext.bind(function(requestInfo) {
        switch(requestInfo.mode) {
            case 'xhr':
                Ext.Ajax.abort(requestInfo.identifier);
                this.numPendingFiles -= requestInfo.fileCount;
                break;
            default:
                Ext.Error.raise("Unknown requestInfo type. Can not abort");
        }
    }, Ext.Loader);

    /**
     * Take a requestKey and stop a possibly running asynchronous request for it
     *
     * @param { string } requestKey
     */
    var cleanIfAsyncRequestExists = Ext.bind(function(requestKey) {
        if (requestMap.hasOwnProperty(requestKey)) {
            abortAsyncRequest(requestMap[requestKey]);
            delete requestMap[requestKey];
        }
    }, Ext.Loader);

    /**
     * Load all the classes using bulk load specified by the given namespace array
     *
     * @param { array } namespaces
     */
    var loadNamespacedClasses = Ext.bind(function(namespaces) {

        var host = window.location.protocol + "//" + window.location.hostname;

        Ext.iterate(namespaces, function(key, namespace){
            var path = namespace.path,
                cacheKey,
                disableCachingValue = this.getConfig('disableCachingValue'),
                requestMethod = "post",
                tmpPath,
                maxLength = maxParameterLength - 50,
                files = [];

            if (maxLength <= 0) {
                maxLength = 1950;
            }

            // Get request of main subapplication app.js
            if (namespace.files.length <= 1 && namespace.files[0].indexOf('?file') !== -1) {
                path += namespace.files[0];
                requestMethod = "get";
            } else {
                // If BulkRequest check if GET-Request is possible, if not POST-Fallback is used
                tmpPath = path;
                tmpPath += "?f=";

                files = [];
                Ext.each(namespace.files, function (file) {
                    // shrink filenames, will be expanded in ScriptRenderer-Plugin
                    file = file.replace(/^model\//, 'm/');
                    file = file.replace(/^controller\//, 'c/');
                    file = file.replace(/^view\//, 'v/');

                    files.push(file);
                });

                tmpPath += files.join('|');

                // see: http://stackoverflow.com/questions/417142/what-is-the-maximum-length-of-a-url-in-different-browsers
                // see: http://www.hardened-php.net/suhosin/configuration.html#suhosin.get.max_value_length
                // 2000 - 50 Chars Buffer for disableCachingParam etc.
                if (tmpPath.length + host.length < maxLength) {
                    requestMethod = "get";
                    path = tmpPath;
                }
            }

            if (!this.getConfig('caching')) {
                path += (requestMethod === 'get') ? '&' : '?';
                path += this.getConfig('disableCachingParam') + '=' + disableCachingValue;
            }

            cacheKey = getRequestKey(path, namespace.files);

            if (this.syncModeEnabled) {
                cleanIfAsyncRequestExists(cacheKey);
            } else {
                // An asynchrounous request for the given requestKey may already be running and waiting for completion.
                // In this case loading it a second time should not be done.
                if (requestMap.hasOwnProperty(cacheKey)) {
                    return;
                }
            }

            var xhr = Ext.Ajax.request({
                url: path,
                method: requestMethod,
                disableCaching: false,
                async: !this.syncModeEnabled,
                params: (requestMethod === 'get')
                        ? (null)
                        : ({ 'file[]': namespace.files }),
                scope: this,
                success: function(response) {
                    try {
                        Ext.globalEval(response.responseText + "\n//# sourceURL=" + path);
                    } catch(err) {
                        Shopware.app.Application.fireEvent('Ext.Loader:evalFailed', err, response, namespace, requestMethod);
                    }

                    this.onFilesLoaded(namespace.classNames);

                    // Remove handled request from requestMap
                    if (requestMap.hasOwnProperty(cacheKey)) {
                        delete requestMap[cacheKey];
                    }
                },
                failure: function(xhr) {
                    Shopware.app.Application.fireEvent('Ext.Loader:xhrFailed', xhr, namespace, requestMethod);

                    cleanIfAsyncRequestExists(cacheKey);
                }
            });

            if (!this.syncModeEnabled) {
                requestMap[cacheKey] = {
                    mode: 'xhr',
                    identifier: xhr,
                    fileCount: namespace.files.length
                };
            }
        }, this);
    }, Ext.Loader);

    /**
     * Get the loader path for the given classname and prefix
     *
     * @param { string } className
     * @param { string } prefix
     * @return { array }
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

    Ext.Loader.requestQueue = [];

    Ext.Loader.require = function(expressions, fn, scope, excludes) {
        var Manager = Ext.ClassManager;
        var expression, exclude, className, excluded = {},
            excludedClassNames = [],
            possibleClassNames = [],
            possibleClassName, classNames = [],
            namespaces = {},
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


        // If the request is synchronous pushing into the queue is not needed
        if (!this.syncModeEnabled) {
            this.queue.push({
                requires: classNames,
                callback: fn,
                scope: scope
            });
        }

        classNames = classNames.slice();

        for (i = 0, ln = classNames.length; i < ln; i++) {
            className = classNames[i];

            if (!this.isFileLoaded.hasOwnProperty(className) || !this.isFileLoaded[className]) {
                namespaces = this.loadNamespaces(className, namespaces);
            } else {
                // Asynchrounous request, with already loaded deoendency:
                // Process queue to ensure the callback is executed.
                if (!this.syncModeEnabled) {
                    this.refreshQueue();
                }
            }
        }

        loadNamespacedClasses(namespaces);

        // Callbacks are not executed through the queue in syncmode, therefore it needs to be done manually
        if (this.syncModeEnabled) {
            fn.call(scope);
        }

        return this;
    };

    /**
     * @param { string } className
     * @param { Object } namespaces
     * @returns { Object }
     */
    Ext.Loader.loadNamespaces = function (className, namespaces) {
        this.isFileLoaded[className] = false;

        var prefix = this.getPrefix(className),
            separatedPath = this.getPath(className, prefix),
            substring = className.substring(prefix.length + 1);

        // If Shopware.apps.Example.store.Foo is requested, but Shopware.apps.Example was never loaded before
        if (this.isMainAppMissing(substring, prefix)) {
            var rightPart = substring.split('.')[0];

            // Load main app
            namespaces = this.loadNamespaces('Shopware.apps.' + rightPart, namespaces);
            loadNamespacedClasses(namespaces);

            // Reset all variables like main app has already existed, since it does now
            namespaces = {};
            prefix = this.getPrefix(className);
            separatedPath = this.getPath(className, prefix);
        }

        this.numPendingFiles++;

        // Collect and sort bulk loaded files by prefix
        if (!namespaces[prefix]) {
            namespaces[prefix] = { 'prefix': prefix, 'path': separatedPath[0], 'files': [], 'classNames': [] };
        }

        namespaces[prefix]['files'].push(separatedPath[1]);
        namespaces[prefix]['classNames'].push(className);

        return namespaces;
    };

    /**
     * @param { string } substring
     * @param { string } prefix
     * @returns { boolean }
     */
    Ext.Loader.isMainAppMissing = function (substring, prefix) {
        if (prefix === 'Shopware.apps' && substring.split('.').length > 1) {
            return true;
        }

        return false;
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

        me.refreshQueue();
    };

    /**
     * Sets the path of a namespace. For Example:
     *
     *     Ext.Loader.setPath('Ext', '.');
     *
     * @param { String/Object } name See { @link Ext.Function#flexSetter flexSetter }
     * @param { String } path See { @link Ext.Function#flexSetter flexSetter }
     * @param bulk
     * @param suffix
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
        return this;
    };
})();
//{/block}