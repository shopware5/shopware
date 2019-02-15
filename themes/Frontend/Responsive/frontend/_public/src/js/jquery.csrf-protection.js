;(function($, window, document) {
    'use strict';

    /**
     * Get the value of a cookie with the given name
     * @param name
     * @returns {string|undefined}
     */
    $.getCookie = function(name) {
        var value = '; ' + document.cookie,
            parts = value.split('; ' + name + '=');

        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }
        return undefined;
    };

    /**
     * Remove a cookie with the provided name
     * @param name
     */
    $.removeCookie = function(name) {
        var basePath = window.csrfConfig.basePath || '/';
        document.cookie = name + '=; path=' + basePath + '; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    };

    var CSRF = {

        /**
         * Key including subshop and -path
         */
        storageKey: '__csrf_token-' + window.csrfConfig.shopId,

        /**
         * Temporary request callback store
         */
        pendingRequests: {},

        /**
         * List of domains, where the csrf token will be send
         */
        allowedDomains: [
            window.location.protocol + '//' + window.location.hostname
        ],

        /**
         * Returns the token
         * @returns {string}
         */
        getToken: function() {
            return $.getCookie(this.storageKey);
        },

        /**
         * Checks if the token needs to be requested
         * @returns {boolean}
         */
        checkToken: function() {
            return this.getToken() !== undefined;
        },

        /**
         * Creates a hidden input fields which holds the csrf information
         * @returns {HTMLElement}
         */
        createTokenField: function() {
            var me = this;

            return $('<input>', {
                'type': 'hidden',
                'name': '__csrf_token',
                'value': me.getToken()
            });
        },

        /**
         * Adds the token field to the given form
         * @param {HTMLElement} formElement
         */
        addTokenField: function(formElement) {
            formElement.append(CSRF.createTokenField());
            $.publish('plugin/swCsrfProtection/addTokenField', [ this, formElement ]);
        },

        /**
         *
         * @returns {HTMLElement[]}
         */
        getFormElements: function() {
            return $('form[method="post"]');
        },

        /**
         * Search all forms on the page and create or update their csrf input fields
         */
        updateForms: function() {
            var me = this,
                formElements = me.getFormElements();

            $.each(formElements, function(index, formElement) {
                var csrfInput;

                formElement = $(formElement);
                csrfInput = formElement.find('input[name="__csrf_token"]');

                if (!CSRF.isLocalLink(formElement.attr('action'))) {
                    return;
                }

                if (csrfInput.length > 0) {
                    csrfInput.val(me.getToken());
                } else {
                    me.addTokenField(formElement);
                }
            });

            $.publish('plugin/swCsrfProtection/updateForms', [ this, formElements ]);
        },

        /**
         * Registers handlers before sending an AJAX request & after it is completed.
         */
        setupAjax: function() {
            var me = this;

            $(document).ajaxSend($.proxy(me._ajaxBeforeSend, me));
            $(document).ajaxComplete($.proxy(me._ajaxAfterSend, me));

            $.publish('plugin/swCsrfProtection/setupAjax', [ me, me.getToken() ]);
        },

        /**
         * Update all forms in case a callback has replaced html parts and needs to be rebound
         *
         * @private
         */
        _ajaxAfterSend: function() {
            window.setTimeout(function() {
                this.updateForms();
            }.bind(this), 1);
        },

        /**
         * Append X-CSRF-Token header
         *
         * @param event
         * @param request
         * @param settings
         * @private
         */
        _ajaxBeforeSend: function(event, request, settings) {
            settings = settings || {};

            if (settings.hasOwnProperty('ignoreCSRFHeader') || settings.ignoreCSRFHeader === true || !this.isLocalLink(settings.url)) {
                return;
            }

            request.setRequestHeader('X-CSRF-Token', this.getToken());
        },

        /**
         * Calls the frontend to retrieve a new csrf token and executes the afterInit on success
         */
        requestToken: function() {
            var me = this;

            if (window.StateManager.hasCookiesAllowed() || window.cookieRemoval === 1) {
                $.ajax({
                    url: window.csrfConfig.generateUrl,
                    success: function(response, status, xhr) {
                        me.saveToken(xhr.getResponseHeader('x-csrf-token'));
                        $.publish('plugin/swCsrfProtection/requestToken', [ me, me.getToken() ]);
                        me.afterInit();
                    }
                });
            }
        },

        /**
         * Save token into a cookie
         * @param token
         */
        saveToken: function(token) {
            var me = this,
                basePath = window.csrfConfig.basePath || '/';

            document.cookie = me.storageKey + '=' + token + '; path=' + basePath;
        },

        /**
         * Initialize the CSRF protection
         */
        init: function() {
            var me = this;

            if (me.checkToken()) {
                me.afterInit();
                return;
            }

            me.requestToken();
        },

        /**
         * Runs after a valid token is set
         */
        afterInit: function() {
            var me = this;

            me.updateForms();
            me.setupAjax();

            $.publish('plugin/swCsrfProtection/init', [ me ]);
        },

        /**
         * @param {string} link
         * @returns {boolean}
         */
        isLocalLink: function (link) {
            if (link === undefined || link === null || link[0] === '/') {
                return true;
            }

            for (var index in this.allowedDomains) {
                if (link.indexOf(this.allowedDomains[index]) === 0) {
                    return true;
                }
            }

            return false;
        }

    };

    $(function() {
        CSRF.init();
    });

    window.CSRF = CSRF;
})(jQuery, window, document);
