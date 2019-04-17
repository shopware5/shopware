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
 * Plain pub / sub pattern based on davidwalsh pattern:
 * http://davidwalsh.name/pubsub-javascript
 *
 * The pattern is written in vanilla JavaScript and therefore has no dependencies on other libraries. We're using it for
 * custom events in the `postMessageApi`.
 *
 * @type {Object}
 */
window.events = (function() {
    var topics = {},
        hOP = topics.hasOwnProperty;

    return {

        /**
         * Subscribes an event listener to an event.
         *
         * @example
         * var subscription = windows.events.subscribe('/initialized', function(obj) {
         *     // Do something now that the event has occurred
         * });
         *
         * // ...sometime later where I no longer want subscription...
         * subscription.remove();
         *
         * @param {String} topic
         * @param {Function} listener
         * @returns {Object}
         */
        subscribe: function(topic, listener) {
            // Create the topic's object if not yet created
            if(!hOP.call(topics, topic)) topics[topic] = [];

            // Add the listener to queue
            var index = topics[topic].push(listener) -1;

            // Provide handle back for removal of topic
            return {
                remove: function() {
                    delete topics[topic][index];
                }
            };
        },

        /**
         * Publishes an event with additional data. Catch the event with the subscribe function
         *
         * @example
         * win.events.publish('/initialized', {
         *     some: 'data'
         * });
         *
         * @param {String} topic
         * @param {Object} info
         */
        publish: function(topic, info) {
            // If the topic doesn't exist, or there's no listeners in queue, just leave
            if(!hOP.call(topics, topic)) return;

            // Cycle through topics queue, fire!
            topics[topic].forEach(function(item) {
                item(info != undefined ? info : {});
            });
        }
    };
})();

/**
 * postMessageAPI
 *
 * The API provides an easy-to-use way to communicate with the Shopware backend and accessing window operations. It's
 * based on the defined HTML5 Web Messaging standard (http://www.w3.org/TR/2012/WD-webmessaging-20120313/) and the
 * communication is based on JSON-RPC 2.0 (http://www.jsonrpc.org/specification) for remote procedure calls.
 *
 * @type {Object}
 */
window.postMessageApi = function (api, win) {
    var info = {
            name: 'postMessageApi',
            version: '1.0.0'
        },
        callbackQueue = [],
        id = 1,
        instance = null,
        component = null,
        initialized = false;

    /**
     * Extends an function object with a object to create a new object which inheritances from the function object which
     * calls the method.
     *
     * @example
     * var Pirate = Person.extend({
     *    // your object implementation
     * });
     *
     * @param {Object} proto
     * @returns {Function}
     */
    Function.prototype.extend = function extend(proto) {
        var superclass = this,
            constructor;

        var getOwnPropertyDescriptors = function(obj) {
            var descriptors = {};
            for (var prop in obj) {
                if (obj.hasOwnProperty(prop)) {
                    descriptors[ prop ] = Object.getOwnPropertyDescriptor(obj, prop);
                }
            }
            return descriptors;
        };

        if (!proto.hasOwnProperty('constructor')) {
            Object.defineProperty(proto, 'constructor', {
                value: function () {
                    // Default call to superclass as in maxmin classes
                    superclass.apply(this, arguments);
                },
                writable: true,
                configurable: true,
                enumerable: false
            });
        }
        constructor = proto.constructor;

        constructor.prototype = Object.create(this.prototype, getOwnPropertyDescriptors(proto));

        return constructor;
    };

    /**
     * Represents the base for all RPC classes. It sets the basic options for every JSON-RPC call.
     */
    var RpcBaseObject = Object.extend({

        /**
         * Prefix for the encoding of the request.
         * @type {String}
         */
        prefix: 'swag.',

        /**
         * Constructor of the RPC object which sets the basic settings.
         */
        constructor: function () {
            this.opts = {};
            this.opts.jsonrpc = '2.0';
        },

        /**
         * Encodes the options of the object using JSON.stringify and prefixes the string.
         * @returns {string}
         */
        encode: function () {
            return this.prefix + JSON.stringify(this.opts);
        }
    });

    /**
     * Request object which should be used for every request. The JSON-RPC 2.0 implement was extended to match the
     * special behavior of the purpose to control a window and calling methods in the Shopware backend. Therefore
     * we added the new properties `instance`, `target` and `component`.
     * The `instance` represents a unique id which will be assigned by the module manager and needs to be given every
     * request, so we can make sure what module requested the data.
     * `target` defines the parent object for calling the method which will be provided by the property `method` and
     * `components` defines the window which has sent the request, so it's possible to send messages between multiple
     * windows of the same module.
     */
    var RpcRequestObject = RpcBaseObject.extend({

        /**
         * Provides the options of a RPC call by sending a Request object to a server.
         *
         * @param {String} target - Parent object of the method you want to call
         * @param {String} methodName - Name of the method which should be called
         * @param {Object=} params - Optional parameters of the call
         * @param {String=} component - Component name. Default: main
         * @returns {RpcRequestObject}
         */
        constructor: function (target, methodName, params, componentName) {
            Object.getPrototypeOf(RpcRequestObject.prototype).constructor.apply(this, arguments);

            params = params || {};

            if(!componentName) {
                componentName = component || 'main';
            }


            if (params.async) {
                this.opts.async = params.async;
                delete params.async;
            } else {
                this.opts.async = false;
            }

            this.opts.method = methodName;
            this.opts.params = params;
            this.opts.id = id++;

            // Special attributes for the communication
            this.opts.instance = instance;
            this.opts.target = target;
            this.opts.component = componentName;

            return this;
        },

        /**
         * Provides the ability to set up a callback for the Request object.
         *
         * @param {Function} callback - Callback method
         * @param {Object=} scope - The value of `this` provided for the call to the callback function.
         * @param {Object=} eOpts - Optional event parameter.
         * @returns {RpcRequestObject}
         */
        on: function(callback, scope, eOpts) {
            var opts = this.opts;

            scope = scope || this;
            eOpts = eOpts || {};

            callbackQueue.push({
                id: opts.id,
                method: opts.method,
                params: opts.params,
                target: opts.component,
                callback: callback,
                scope: scope,
                eOpts: eOpts
            });

            return this;
        },

        /**
         * Provides all available Request options.
         * @returns {Object}
         */
        getRequestOptions: function() {
            return this.opts;
        },

        /**
         * Sends the Request object to the server using the `window.postMessage` API.
         * @returns {RpcRequestObject}
         */
        send: function () {
            if (this.opts.id === null || this.opts.instance === null) {
                throw "Instance Uuid not defined for communication. Probably the handshake wasn't successful.";
            }

            win.parent.postMessage(this.encode(), window.location.origin);

            win.events.publish('send-rpc-request-object', this.opts);

            return this;
        }
    });

    /**
     * When a RPC call is made, the server must reply with a Response. The Response is expressed as a single JSON Object
     */
    var RpcResponseObject = RpcBaseObject.extend({

        /**
         * Sets up the Response object of a RPC call. It transforms a JSON string into a Object which provides additional
         * methods.
         *
         * @param {String} response - Plain JSON string from the server
         * @returns {RpcResponseObject}
         */
        constructor: function (response) {
            Object.getPrototypeOf(RpcResponseObject.prototype).constructor.apply(this, arguments);
            response = this.decode(response);

            this.opts.instance = response.instance;
            this.opts.component = response.component;

            this.opts.result = (response.result !== null ? response.result : null);
            this.opts.error = response.error || null;
            this.opts.id = response.id || null;

            return this;
        },

        /**
         * Returns the Response of the server as a JavaScript object.
         * @returns {Object}
         */
        getResponse: function() {
            return this.opts;
        },

        /**
         * Decodes the JSON string and removes the prefix.
         * @param response
         */
        decode: function (response) {
            return JSON.parse(response.substring(this.prefix.length));
        }
    });

    win.addEventListener('message', function (event) {
        var response = new RpcResponseObject(event.data).getResponse(),
            callback = {};

        // Do we trust the sender of this message?
        if (event.origin !== window.location.origin) {
            return false
        }

        // Cache the instance UUID at the first handshake
        if (instance === null) {
            instance = response.instance;
        }

        if (component === null) {
            component = response.component;
        }

        if (instance.length && component.length && !initialized) {
            initialized = true;

            win.events.publish('initialized-api', response);

            return false;
        }

        if (instance !== response.instance) {
            return false;
        }

        if (component !== response.component) {
            return false;
        }

        win.events.publish('get-post-message', response);

        // Only call the callback when we have a result value
        if (response.result === undefined) {
            return false;
        }

        callbackQueue.forEach(function (item, index) {
            if (item.id === response.id) {
                callback = item;
                callbackQueue.splice(index, 1);
                return false;
            }
        });

        if (callback && typeof callback.callback === 'function') {
            callback.callback.call(callback.scope, response.result, response, callback.eOpts);
        }
    }, false);

    return {

        /**
         * Returns information about the API
         * @returns {{name: string, version: string}}
         */
        getInfo: function() {
            return info;
        },

        /**
         * Returns the version string of the API
         * @returns {string}
         */
        getVersion: function () {
            return info.version;
        },

        /**
         * Returns the name of the api
         * @returns {string}
         */
        getName: function () {
            return info.name;
        },

        /**
         * Returns the instance uuid which is used for the communication of an app
         * @returns {string|null}
         */
        getInstance: function () {
            return instance || null;
        },

        /**
         * Returns the techName of the module window.
         * @returns {string|null}
         */
        getComponentName: function () {
            return component || null;
        },

        /**
         * Returns if the api is initialized.
         * @returns {boolean}
         */
        isInitialized: function () {
            return initialized;
        },

        /**
         * Opens a module in the shopware backend.
         *
         * @example
         * postMessageApi.openModule({
         *     name: 'Shopware.apps.Article'
         * });
         *
         * @param {Object} payload
         */
        openModule: function(payload) {
            var request = new RpcRequestObject('Shopware.app.Application', 'addSubApplication', payload).send();

            win.events.publish('open-module', request.getRequestOptions());

            return request;
        },

        /**
         * Creates a subwindow for the module.
         *
         * @example
         * postMessageApi.createSubWindow({
         *     width: 500,
         *     height: 500,
         *     component: 'customSubWindow',
         *     url: 'your/url',
         *     title: 'Plugin Konfiguration'
         * });
         *
         * @param {Object} payload
         */
        createSubWindow: function (payload) {
            var request = new RpcRequestObject('Shopware.ModuleManager', 'createSubWindow', payload, payload.component).send();

            win.events.publish('open-subwindow', request.getRequestOptions());

            return request;
        },

        /**
         * Sends a message to a subwindow
         *
         * @example
         * postMessageApi.sendMessageToSubWindow({
         *     name: 'customSubWindow',
         *     params: {
         *         msg: 'Your message',
         *         foo: [ 'bar', 'batz' ]
         *     }
         * });
         *
         * @param {Object} payload
         * @return {RpcRequestObject}
        */
        sendMessageToSubWindow: function(payload) {
            var request = new RpcRequestObject('Shopware.ModuleManager', 'sendMessageToSubWindow', payload, payload.component).send();

            win.events.publish('send-message-to-subwindow', request.getRequestOptions());

            return request;
        },

        /**
         * Provides the ability to create growl messages. The method can create normal or sticky messages.
         *
         * @param {String} title - Title of the growl message
         * @param {String} text - Text of the growl message
         * @param {Boolean=} sticky - Truthy to get a sticky growl message, default: false
         * @param {Boolean=} log - Enable logging the message of the message, default: true
         * @param {Object=} opts - Additional configuration params for the sticky growl message, please see {@link Shopware.Notification.createStickyGrowlMessage}
         * @returns {RpcRequestObject}
         */
        createGrowlMessage: function(title, text, sticky, log, opts) {
            var request;

            sticky = sticky || false;
            log = (log !== 'undefined' ? log : true);
            opts = opts || {};

            request = new RpcRequestObject('Shopware.ModuleManager', 'createGrowlMessage', {
                title: title,
                text: text,
                log: log,
                sticky: sticky,
                caller: 'Shopware.ModuleManager',
                opts: opts
            }).send();

            win.events.publish('create-growl-message', request.getRequestOptions());

            return request;
        },

        /**
         * Displays a confirmation message box with Yes and No buttons (comparable to JavaScript's confirm).
         *
         * @param {String} title
         * @param {String} msg
         * @param {Function} callback
         * @param {Object} scope
         * @param {Object=} eOpts
         * @return {RpcRequestObject}
         */
        createConfirmMessage: function(title, msg, callback, scope, eOpts) {
            var request = new RpcRequestObject('Shopware.ModuleManager', 'createConfirmMessage', {
                async: true,
                title: title,
                msg: msg
            }).on(callback, scope, eOpts).send();

            win.events.publish('create-confirm-message', request.getRequestOptions());

            return request;
        },

        /**
         * Displays a message box with OK and Cancel buttons prompting the user to enter some text (comparable
         * to JavaScript's prompt).
         *
         * @param {String} title
         * @param {String} msg
         * @param {Function} callback
         * @param {Object} scope
         * @param {Object=} eOpts
         * @return {RpcRequestObject}
         */
        createPromptMessage: function(title, msg, callback, scope, eOpts) {
            var request = new RpcRequestObject('Shopware.ModuleManager', 'createPromptMessage', {
                async: true,
                title: title,
                msg: msg
            }).on(callback, scope, eOpts).send();

            win.events.publish('create-prompt-message', request.getRequestOptions());

            return request;
        },

        /**
         * Displays a standard read-only message box with an OK button (comparable to the basic JavaScript alert prompt).
         *
         * @param {String} title
         * @param {String} msg
         * @return {RpcRequestObject}
         */
        createAlertMessage: function(title, msg) {
            var request = new RpcRequestObject('Shopware.ModuleManager', 'createAlertMessage', {
                title: title,
                msg: msg
            }).send();

            win.events.publish('create-alert-message', request.getRequestOptions());

            return request;
        },

        /**
         * Provides window specific functions
         * @type {Object}
         */
        window: {

            /**
             * Sets the window title
             *
             * @example
             * postMessageApi.window.setTitle('Your title');
             *
             * @param {String} title
             * @return {RpcRequestObject}
             */
            setTitle: function(title) {
                var request = new RpcRequestObject('component', 'setTitle', title).send();

                win.events.publish('set-title', request.getRequestOptions());

                return request;
            },

            /**
             * Gets the window width from the backend and fires the callback method.
             *
             * @example
             * postMessageApi.window.getWidth(function(width) {
             *     console.log(width);
             * });
             *
             * @param {Function} callback
             * @param {Object} scope
             * @param {Object=} eOpts
             * @return {RpcRequestObject}
             */
            getWidth: function (callback, scope, eOpts) {
                var request = new RpcRequestObject('component', 'getWidth').on(callback, scope, eOpts).send();

                win.events.publish('component/get-width', request.getRequestOptions());

                return request;
            },

            /**
             * Sets the width of the backend window.
             *
             * @example
             * postMessageApi.window.setWidth(500);
             *
             * @param {String|Number} width
             * @return {RpcRequestObject}
             */
            setWidth: function (width) {
                var request = new RpcRequestObject('component', 'setWidth', ~~(1 * width)).send();

                win.events.publish('component/set-width', request.getRequestOptions());

                return request;
            },

            /**
             * Gets the window height from the backend and fires the callback method.
             *
             * @example
             * postMessageApi.window.getHeight(function(height) {
             *     console.log(height);
             * });
             *
             * @param {Function} callback
             * @param {Object} scope
             * @param {Object=} eOpts
             * @return {RpcRequestObject}
             */
            getHeight: function (callback, scope, eOpts) {
                var request = new RpcRequestObject('component', 'getHeight').on(callback, scope, eOpts).send();

                win.events.publish('component/get-height', request.getRequestOptions());

                return request;
            },


            /**
             * Sets the height of the backend window.
             *
             * @example
             * postMessageApi.window.setHeight(800);
             *
             * @param {String|Number} height
             * @return {RpcRequestObject}
             */
            setHeight: function (height) {
                var request = new RpcRequestObject('component', 'setHeight', ~~(1 * height)).send();

                win.events.publish('component/set-height', request.getRequestOptions());

                return request;
            },

            /**
             * Shows the backend window.
             *
             * @example
             * postMessageApi.window.show();
             *
             * @return {RpcRequestObject}
             */
            show: function () {
                var request = new RpcRequestObject('component', 'show').send();

                win.events.publish('component/show', request.getRequestOptions());

                return request;
            },

            /**
             * Hides the backend window
             *
             * @example
             * postMessageApi.window.hide();
             *
             * @return {RpcRequestObject}
             */
            hide: function () {
                var request = new RpcRequestObject('component', 'hide').send();

                win.events.publish('component/hide', request.getRequestOptions());

                return request;
            },

            /**
             * Destroys the window and the application.
             *
             * @example
             * postMessageApi.window.destroy();
             *
             * @return {RpcRequestObject}
             */
            destroy: function () {
                var request = new RpcRequestObject('component', 'destroy').send();

                win.events.publish('component/destroy', request.getRequestOptions());

                return request;
            },

            /**
             * Minimizes the window to the task bar
             *
             * @example
             * postMessageApi.window.minimize();
             */
            minimize: function () {
                var request = new RpcRequestObject('component', 'minimize').send();

                win.events.publish('component/minimize', request.getRequestOptions());

                return request;
            },

            /**
             * Maximizes the window to the full width and height of the window.
             *
             * @example
             * postMessageApi.window.maximize();
             */
            maximize: function () {
                var request = new RpcRequestObject('component', 'maximize').send();

                win.events.publish('component/maximize', request.getRequestOptions());

                return request;
            },

            /**
             * Restores a maximized window back to its original size and position prior to being maximized.
             *
             * @example
             * postMessageApi.window.restore();
             */
            restore: function () {
                var request = new RpcRequestObject('component', 'restore').send();

                win.events.publish('component/restore', request.getRequestOptions());

                return request;
            },

            /**
             * A shortcut method for toggling between maximize and restore based on the current maximized state of the window.
             *
             * @example
             * postMessageApi.window.toggleMaximize();
             */
            toggleMaximize: function () {
                var request = new RpcRequestObject('component', 'toggleMaximize').send();

                win.events.publish('component/toggle-maximize', request.getRequestOptions());

                return request;
            },

            /**
             * Sets the body style according to the passed parameters.
             *
             * @example
             * postMessageApi.window.setBodyStyle({
             *     border: '1px solid red',
             *     padding: '20px 10px'
             * });
             *
             * @param {Object} payload
             */
            setBodyStyle: function (payload) {
                var request = new RpcRequestObject('component', 'setBodyStyle', payload).send();

                win.events.publish('component/set-body-style', request.getRequestOptions());

                return request;
            }
        }
    };
}(window.postMessageApi || {}, window);
