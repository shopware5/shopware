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
Ext.define('Shopware.ModuleManager', {

    /**
     * Defines that the component is globally available and initialized it itself
     * @boolean
     */
    singleton: true,

    /**
     * Collections which contains all registered modules
     *
     * @Ext.util.MixedCollection
     */
    modules: Ext.create('Ext.util.MixedCollection'),

    /**
     * We're using a instance generator for the modules to generate a unique name for each module.
     *
     * @Ext.data.UuidGenerator
     */
    uuidGenerator: Ext.create('Ext.data.UuidGenerator'),

    prefix: 'swag.',

    /**
     * The method registers a global event listener for the message event to communicate with the frames in the
     * module window.
     *
     * @constructor
     */
    constructor: function() {
        var me = this;

        window.addEventListener('message', Ext.bind(me.onPostMessage, me), false);
    },

    /**
     * The method defines a new module and opens it up in the backend. The method loads the content for the frame
     * based on the `name` argument and registers the necessary event listeners.
     *
     * @example
     * Shopware.ModuleManager.createSimplifiedModule("ExampleModulePlainHtml", {
     *    "title": "Plain HTML Module",
     *    "modal": true,
     *    "width": "50%",
     *    "height": "500px"
     * });
     *
     * @param { String } name - Technical name of the module
     * @param { Object } args - User defined window configuration
     */
    createSimplifiedModule: function(name, args) {
        var me = this,
            instance = me.uuidGenerator.generate(),
            content = me.createContentFrame(name, instance),
            config = me.createWindowConfiguration(args),
            subApp = me.createSubApplication(instance),
            windows = Ext.create('Ext.util.MixedCollection'),
            contentWindow;

        // We need to inject a sub application into the window to get the correct window management
        config.subApp = subApp;
        subApp.app = Shopware.app.Application;
        config._isMainWindow = true;
        config.content = content;
        config.component = 'main';

        // Create the window and set it as the main window of the sub application
        contentWindow = Ext.create('Shopware.window.SimpleModule', config);
        contentWindow.show();
        contentWindow.setLoading(true);
        me.registerWindowEvents(contentWindow);
        subApp.setAppWindow(contentWindow);

        content.dom._window = contentWindow;

        // Append the content frame to the window
        contentWindow.body.appendChild(content);
        windows.add('main', contentWindow);

        me.modules.add(instance, {
            name: name,
            instance: instance,
            subApp: subApp,
            windows: windows
        });
    },

    /**
     * Creates the iframe element which will be used as the content area of the module. We have to add several event
     * listeners to increase the usability of the backend and the iframe.
     *
     * @param { String } name - Technical name of the module
     * @param { String } instance - Unique ID of the module
     * @param { Boolean } fullPath - Truthy to pass in the full url
     * @returns { Ext.dom.Element }
     */
    createContentFrame: function(name, instance, fullPath) {
        var me = this,
            frame;

        fullPath = fullPath || false;

        frame = Ext.get(Ext.DomHelper.createDom({
            'id': Ext.id(),
            'tag': 'iframe',
            'cls': 'module-frame',
            'width': '100%',
            'height': '100%',
            'border': '0',
            'src': (fullPath ? name : '{url module="backend" controller=""}' + name),
            'data-instance': instance
        }));

        // Set up event listener to push a message to the frame with instance to allow communication with the origin component
        frame.on('load', me.onFrameLoaded, me, { instance: instance });
        frame.on('mouseover', function() {
            Shopware.app.Application.fireEvent('global-close-menu');
        }, me);

        return frame;
    },

    /**
     * Merges the default window configuration and the user defined configuration of the window. Additionally we're
     * adding the refresh tool to the window header, which makes it possible to reload the iframe content.
     *
     * @param { Object } args - User defined window configuration
     * @returns { Object } Merged configuration
     */
    createWindowConfiguration: function(args) {
        var config;

        // Set up the window
        config = Ext.apply({ }, args);
        config.tools = [{
            type:'refresh',
            handler: function(event, tool, comp) {
                var ownerCt = comp.ownerCt;
                ownerCt.setLoading(true);
                ownerCt.content.dom.contentWindow.location.reload();
            }
        }];

        return config;
    },

    /**
     * Creates the sub application for the module which simplifies the window management and increases the compability
     * with the backend.
     *
     * @param { String } instance - Unique ID of the module
     * @returns { Enlight.app.SubApplication }
     */
    createSubApplication: function(instance) {
        var subApp = Ext.create('Enlight.app.SubApplication', { name: instance });
        subApp.onBeforeLaunch();

        return subApp;
    },

    /**
     * Registers additional event handler for the module window which increases the usability while resizing the window.
     *
     * @param { Enlight.app.Window } win - The window object of the module
     * @returns { Enlight.app.Window }
     */
    registerWindowEvents: function(win) {
        var me = this,
            resizer;

        // We need to hide the content before resizing due to event bubbling issues of the frame we're using
        resizer = win.resizer;
        resizer.on('beforeresize', function(resizer, width, height, event, eOpts) {
            eOpts.win.content.hide();
        }, me, { win: win });
        resizer.on('resize', function(resizer, width, height, event, eOpts) {
            eOpts.win.content.show();
        }, me, { win: win });

        return win;
    },

    /**
     * Event handler method which will be fired when the frame is loaded. The method will post the instance instance of the module
     * to the frame. The instance instance will be used to identify the module and redirect the messages from the frame to the
     * corresponding window and sub application
     *
     * @event `load`
     * @param { Ext.EventObject } event
     * @param { Ext.dom.Element } comp
     * @param { Object } eOpts
     */
    onFrameLoaded: function(event, comp, eOpts) {
        var instance = eOpts.instance,
            mainWindow = comp._window;

        mainWindow.setLoading(false);
        comp.contentWindow.postMessage(this.prefix + JSON.stringify({
            instance: instance,
            component: mainWindow.component
        }), window.location.origin);
    },

    /**
     * Creates sub windows for the module. The method will be called from the module itself and is not meant to be
     * called manually.
     *
     * @param { Object } payload - Payload from the module frame.
     * @returns { Boolean }
     */
    createSubWindow: function(payload) {
        var me = this,
            instance = payload.instance,
            content = me.createContentFrame(payload.url, instance, true),
            config = me.createWindowConfiguration(payload),
            module = me.modules.get(instance),
            contentWindow;

        if (!instance.length) {
            return false;
        }

        // We need to inject a sub application into the window to get the correct window management
        config.subApp = module.subApp;
        config._isMainWindow = false;
        config.content = content;

        // Delete id before passing the config object to the SimpleModule to avoid collision.
        // ExtJS generates a unique id if the id is missing
        delete config.id;

        // Create the window and set it as the main window of the sub application
        contentWindow = Ext.create('Shopware.window.SimpleModule', config);
        contentWindow.show();
        contentWindow.setLoading(true);
        me.registerWindowEvents(contentWindow);

        content.dom._window = contentWindow;

        // Append the content frame to the window
        contentWindow.body.appendChild(content);
        module.windows.add(payload.component, contentWindow);

        return true;
    },

    /**
     * Sends a message event to a subwindow of the module.
     *
     * @param { Object } payload
     * @returns { Boolean }
     */
    sendMessageToSubWindow: function(payload) {
        var me = this,
            instance = payload.instance,
            module, contentWindow;

        if (!instance.length) {
            return false;
        }

        module = me.modules.get(instance);
        if(!module) {
            return false;
        }

        contentWindow = module.windows.get(payload.component);
        if(contentWindow === null) {
            return false;
        }

        contentWindow.content.dom.contentWindow.postMessage(this.prefix + JSON.stringify({
            jsonrpc: '2.0',
            component: payload.component,
            result: payload.params,
            id: payload.id,
            instance: payload.instance
        }), window.location.origin);
    },

    /**
     * Event handler for the global post message event. It dispatches the message and secures the event handling.
     * @param { MessageEvent } event
     */
    onPostMessage: function(event) {
        var me = this,
            error = null,
            result = null,
            data,
            subModule,
            component;

        // Check if our prefix is at the beginning of the string
        if (event.data.indexOf(me.prefix) !== 0) {
            return;
        }

        // Strip out the prefix and get the JSON data
        data = JSON.parse(event.data.substring(me.prefix.length));

        // Do we trust the sender of this message?
        if (event.origin !== window.location.origin) {
            return;
        }

        // Was the message sent using our api?
        subModule = me.modules.get(data.instance);
        if (!data.instance || !subModule ||(subModule.instance !== data.instance)) {
            return;
        }
        component = subModule.windows.get(data.component);

        if (!data.params) {
            data.params = {
                instance: data.instance,
                id: data.id
            };
        } else {
            data.params.instance = data.instance;
            data.params.id = data.id;
        }

        if(data.async === true) {
            data.params._component = data.component;
        }

        try {
            // We're using `eval` instead of `new Function` cause `eval` executes the code in the local scope and
            // therefore we have access to the component window which holds off the iframe.
            result = eval(
                Ext.String.format('[0].[1]([2]);',
                    data.target,
                    data.method,
                    (data.params !== null ? JSON.stringify(data.params) : '')
                )
            );
        } catch(err) {
            // We're using a -32000 error code which means custom error + the error message of the JavaScript parser
            // due to the fact that an error occurs most of the times when calling an undefined method.
            // See: http://www.jsonrpc.org/specification#error_object
            error = { code: -32000, message: err.message };
        }

        // Window operations usually returning the component object itself for chaining support but that causes
        // "TypeError: Converting circular structure to JSON", so we replace the component with a boolean.
        if (result && result instanceof Enlight.app.Window) {
            result = true;
        }

        if (result === undefined) {
            result = true;
        }

        if (data.async === true) {
            return false;
        }

        me.sendMessageToFrame(result, error, data.id, data.instance,data.component);
    },

    /**
     * Proxy method for creating growl message. We're using this proxy method to make it easier to switch between
     * the different modes of the growl message.
     *
     * @param { Object } params - Params for the growl message
     */
    createGrowlMessage: function(params) {
        var sticky = params.sticky,
            opts;

        if (!sticky) {
            Shopware.Notification.createGrowlMessage(params.title, params.text, params.caller, 'growl', params.log);
        } else {
            opts = Ext.apply(params.opts, {
                title: params.title,
                text: params.text,
                log: params.log
            });
            Shopware.Notification.createStickyGrowlMessage(opts, params.caller, 'growl');
        }

        return true;
    },

    /**
     * Proxy method to create a confirm message which sends the response back to the frame.
     *
     * @param { Object } data
     */
    createConfirmMessage: function(data) {
        var me = this;

        Ext.Msg.confirm(data.title, data.msg, function(btn) {
            me.sendMessageToFrame(btn, null, data.id, data.instance, data._component);
        });
    },

    /**
     * Proxy method to create a prompt message which sends the response back to the frame.
     *
     * @param { Object } data
     */
    createPromptMessage: function(data) {
        var me = this;

        Ext.Msg.prompt(data.title, data.msg, function(btn, text) {
            me.sendMessageToFrame({ btn: btn, text: text }, null, data.id, data.instance, data._component);
        });
    },

    /**
     * Proxy method which creates an alert box.
     *
     * @param { Object } data
     * @returns { Boolean }
     */
    createAlertMessage: function(data) {
        Ext.Msg.alert(data.title, data.msg);

        return true;
    },

    /**
     * Sends a message to the frame using the postMessage API.
     *
     * @param { Mixed } result
     * @param { Mixed } error
     * @param { Integer } id
     * @param { String } instance
     * @param { String } comp
     */
    sendMessageToFrame: function(result, error, id, instance, comp) {
        var subModule, component;

        subModule = this.modules.get(instance);
        if (!instance || !subModule ||(subModule.instance !== instance)) {
            return;
        }
        component = subModule.windows.get(comp);

        // Always sending back a response to the frame when it's available
        if(!component.content.dom || !component.content.dom.contentWindow) {
            return;
        }

        component.content.dom.contentWindow.postMessage(this.prefix + JSON.stringify({
            jsonrpc: '2.0',
            result: result,
            error: error,
            id: id,
            instance: instance,
            component: comp
        }), window.location.origin);
    }
});

window.createSimpleModule = Shopware.ModuleManager.createSimplifiedModule.bind(Shopware.ModuleManager);
