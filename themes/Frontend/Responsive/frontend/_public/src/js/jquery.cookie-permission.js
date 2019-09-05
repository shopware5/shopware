;(function($, window) {
    'use strict';

    var $body = $('body');

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
             * Class name added to body when cookiePermission element is being shown.
             *
             * @property cookieMessageShowingClass
             * @type {string}
             */
            cookieMessageShowingClass: 'cookie--permission--message',

            /**
             * Selector of the accept button for select the button and register events on it.
             *
             * @property acceptButtonSelector
             * @type {string}
             */
            acceptButtonSelector: '.cookie-permission--accept-button',

            /**
             * Selector of the decline button to select the button and register events on it.
             *
             * @property declineButtonSelector
             * @type {string}
             */
            declineButtonSelector: '.cookie-permission--decline-button',

            /**
             * Selector of the close button to select the button and register events on it.
             *
             * @property declineButtonSelector
             * @type {string}
             */
            closeButtonSelector: '.cookie-permission--close-button',

            /**
             * Selector of the privacy statement link "More information" to select and prepare the href property.
             *
             * @property privacyLinkSelector
             * @type {string}
             */
            privacyLinkSelector: '.cookie-permission--privacy-link',

            /**
             * The current shopId for create the storageKey
             *
             * @property shopId
             * @type {number}
             */
            shopId: 0,

            /**
             * The shop host url for creating the data privacy statement link
             *
             * @property host
             * @type {string}
             */
            urlPrefix: '',

            /**
             * Modal title
             */
            title: ''
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
            me.preparePrivacyLink();
            me.registerEvents();
            me.displayCookiePermission(function(display) {
                if (display) {
                    me.showElement();
                }
            });
        },

        /**
         * Calculates the height of the cookie permission element. Please keep in mind that the element has to be
         * visible to get the actual size.
         *
         * @returns {String} height value including the unit e.g. `64px`
         */
        calculatePermissionHeight: function() {
            return this.$el.css('height');
        },

        /**
         * Sets the height of the cookie permission messages on the body element as a padding, therefore the message
         * isn't blocking other content.
         *
         * @returns {void}
         */
        setPermissionHeight: function() {
            $body.css('padding-bottom', this.calculatePermissionHeight());
        },

        /**
         * Update method which will be automatically called when the user switches the defined breakpoints. The method
         * recalculates the height and updates the `padding-bottom` value of the "body" element
         *
         * @return {void}
         */
        update: function() {
            var me = this;

            this.displayCookiePermission(function(display) {
                if (display) {
                    me.setPermissionHeight();
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
            this.$privacyLink = this.$el.find(this.opts.privacyLinkSelector);
            this.$acceptButton = this.$el.find(this.opts.acceptButtonSelector);
            this.$declineButton = this.$el.find(this.opts.declineButtonSelector);
            this.storageKey = this.createStorageKey();
            this.storage = window.StorageManager.getLocalStorage();
        },

        /**
         * Create and set if required a full qualified url as prefix for the privacy link href attribute.
         *
         * @public
         * @method preparePrivacyLink
         */
        preparePrivacyLink: function() {
            var prefix = this.opts.urlPrefix,
                href;

            if (!this.$privacyLink) {
                return;
            }

            href = this.$privacyLink.attr('href') || '';

            if (href.match(/^(http:|https:)/)) {
                return;
            }

            if (href.match(/^\//)) {
                prefix = this.opts.urlPrefix.replace(/(\/)$/, '');
            }

            this.$privacyLink.attr('href', [
                prefix,
                href
            ].join(''));
        },

        /**
         * Subscribes all required events.
         *
         * @public
         * @method registerEvents
         */
        registerEvents: function() {
            this._on(this.$acceptButton, 'click', $.proxy(this.onAcceptButtonClick, this));
            this._on(this.$declineButton, 'click', $.proxy(this.onDeclineButtonClick, this));
        },

        /**
         * Validates if cookie permission hint should be shown
         *
         * @param {function} callback
         */
        displayCookiePermission: function(callback) {
            if ((window.cookieRemoval === 2 && document.cookie.indexOf('allowCookie') !== -1) || (window.cookieRemoval === 1 && document.cookie.indexOf('cookieDeclined') !== -1)) {
                callback(false);
                return;
            }

            callback(!this.storage.getItem(this.storageKey));
        },

        /**
         * Creates the storageKey from the prefix and the shopId like the following example:
         *
         * hide-cookie-permission-1
         *
         * @public
         * @method createStorageKey
         * @returns {string}
         */
        createStorageKey: function() {
            var delimiter = '-';

            return [
                this.cookieStorageKeyPrefix,
                delimiter,
                this.opts.shopId
            ].join('');
        },

        /**
         * Event handler for the acceptButton click.
         *
         * @public
         * @method onAcceptButtonClick
         */
        onAcceptButtonClick: function(event) {
            event.preventDefault();

            try {
                window.localStorage.setItem(this.storageKey, 'true');
            } catch (err) {}

            var d = new Date();
            d.setTime(d.getTime() + (180 * 24 * 60 * 60 * 1000));

            document.cookie = 'allowCookie=1; path=/;expires=' + d.toGMTString() + ';';

            this.hideElement();
        },

        /**
         * Event handler for the declineButton click.
         *
         * @public
         * @method onDeclineButtonClick
         */
        onDeclineButtonClick: function(event) {
            event.preventDefault();

            document.cookie = 'cookieDeclined=1; path=/;';

            this.hideElement();
        },

        /**
         * Event handler for the closeButton click.
         *
         * @public
         * @method onCloseButtonClick
         */
        onCloseButtonClick: function(event) {
            event.preventDefault();

            this.hideElement();
        },

        /**
         * Shows the cookiePermission element.
         *
         * @public
         * @method showElement
         */
        showElement: function() {
            if (window.cookieRemoval === 2) {
                $.modal.open(this.$el.html(), {
                    title: this.opts.title,
                    sizing: 'content',
                    width: 500
                });

                this.$acceptButton = $(this.opts.acceptButtonSelector);
                this.$closeButton = $(this.opts.closeButtonSelector);
                this._on(this.$acceptButton, 'click', $.proxy(this.onAcceptButtonClick, this));
                this._on(this.$closeButton, 'click', $.proxy(this.onCloseButtonClick, this));
            } else {
                this.$el.removeClass(this.opts.isHiddenClass);
                this.setPermissionHeight();
            }
        },

        /**
         * Hides the cookiePermission element.
         *
         * @public
         * @method hideElement
         */
        hideElement: function() {
            this.$el.addClass(this.opts.isHiddenClass);
            $body.css('padding-bottom', 0);
            $.modal.close();
        }
    });
}(jQuery, window));
