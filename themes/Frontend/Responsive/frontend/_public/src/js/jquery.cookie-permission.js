;(function($, window) {
    'use strict';

    $.plugin('swCookiePermission', {

        defaults: {

            /**
             * Class name to show and hide the cookiePermission element.
             *
             * @property isHiddenClass
             * @type {string}
             */
            isHiddenClass: 'is--hidden',

            /**
             * Selector of the accept button for select the button and register events on it.
             *
             * @property acceptButtonSelector
             * @type {string}
             */
            acceptButtonSelector: '.cookie-permission--accept-button',

            /**
             * The current shopId for create the storageKey
             *
             * @property shopId
             * @type {number}
             */
            shopId: 0,

            /**
             * The basePath of the current shop to create the storageKey
             *
             * @property basePath
             * @type {string}
             */
            basePath: ''
        },

        /**
         * The key for the local storage. By this key we save the acceptance of the user.
         *
         * @property cookieStorageKeyPrefix
         * @type {string}
         */
        cookieStorageKeyPrefix: 'hide-cookie-permission',

        /**
         * Default plugin initialisation function.
         * Sets all needed properties, adds classes and registers all needed event listeners.
         *
         * @public
         * @method init
         */
        init: function() {
            var me = this;

            me.applyDataAttributes();

            me.createProperties();
            me.registerEvents();
            me.displayCookiePermission(function(display) {
                if (display) {
                    me.showElement();
                }
            });
        },

        /**
         * Creates the required plugin properties
         *
         * @public
         * @method createProperties
         */
        createProperties: function() {
            var me = this;

            me.$acceptButton = me.$el.find(me.opts.acceptButtonSelector);
            me.storageKey = me.createStorageKey();
            me.storage = window.StorageManager.getLocalStorage();
        },

        /**
         * Subscribes all required events.
         *
         * @public
         * @method registerEvents
         */
        registerEvents: function() {
            var me = this;

            me._on(me.$acceptButton, 'click', $.proxy(me.onAcceptButtonClick, me));
        },

        /**
         * Validates if cookie permission hint should be shown
         *
         * @param {function} callback
         */
        displayCookiePermission: function(callback) {
            var me = this;

            callback(!me.storage.getItem(me.storageKey));
        },

        /**
         * Creates the storageKey from the prefix, shopId and the basePath like the following example:
         *
         * hide-cookie-permission-1-en
         *
         * @public
         * @method createStorageKey
         * @returns {string}
         */
        createStorageKey: function() {
            var me = this,
                delimiter = '-';

            return [
                me.cookieStorageKeyPrefix,
                delimiter,
                me.opts.shopId,
                delimiter,
                me.opts.basePath
            ].join('');
        },

        /**
         * Event handler for the acceptButton click.
         *
         * @public
         * @method onAcceptButtonClick
         */
        onAcceptButtonClick: function() {
            var me = this;

            me.storage.setItem(me.storageKey, 'true');
            me.hideElement();
        },

        /**
         * Shows the cookiePermission element.
         *
         * @public
         * @method showElement
         */
        showElement: function() {
            var me = this;

            me.$el.removeClass(me.opts.isHiddenClass);
        },

        /**
         * Hides the cookiePermission element.
         *
         * @public
         * @method hideElement
         */
        hideElement: function() {
            var me = this;

            me.$el.addClass(me.opts.isHiddenClass);
        }
    });

}(jQuery, window));