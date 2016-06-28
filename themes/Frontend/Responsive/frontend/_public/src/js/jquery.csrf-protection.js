;(function($, window, document) {
    'use strict';

    /**
     * Get the value of a cookie with the given name
     * @param name
     * @returns {string|undefined}
     */
    $.getCookie = function(name) {
        var value = "; " + document.cookie,
            parts = value.split("; " + name + "=");

        if (parts.length == 2) {
            return parts.pop().split(";").shift();
        }
        return undefined;
    };

    /**
     * Remove a cookie with the provided name
     * @param name
     */
    $.removeCookie = function(name) {
        document.cookie = name + '=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    };

    var CSRF = {

        /**
         * Key including subshop and -path
         */
        storageKey: 'X-CSRF-Token--' + window.csrfConfig.shopId + '-' + window.csrfConfig.baseUrl,

        /**
         * Temporary request callback store
         */
        pendingRequests: {},

        /**
         * Returns the token
         * @returns {string}
         */
        getToken: function() {
            return StorageManager.getItem('local', this.storageKey);
        },

        /**
         * Checks if the token needs to be requested
         * @returns {boolean}
         */
        checkToken: function() {
            return $.getCookie('invalidate-xcsrf-token') === undefined
                   && this.getToken() !== null;
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
        },

        /**
         *
         * @returns {HTMLElement[]}
         */
        getFormElements: function() {
            return $('form');
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

                if (csrfInput.length > 0) {
                    csrfInput.val(me.getToken());
                } else {
                    me.addTokenField(formElement);
                }
            });
        },

        /**
         * Modify every ajax request to add the X-CSRF-Token header
         */
        setupAjax: function() {
            var me = this,
                afterAjaxRequest = function() {
                    if (me.pendingRequests[this.url]) {
                        var request = me.pendingRequests[this.url];
                        request.callback.apply(request.context, arguments);
                    }

                    // to prevent timing issues, delay the update
                    window.setTimeout(function() {
                        me.updateForms();
                    }, 1);
                };

            $.ajaxSetup({
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-CSRF-Token', me.getToken());

                    if (typeof this.complete === 'function') {
                        me.pendingRequests[this.url] = {
                            context: this,
                            callback: this.complete
                        };
                    }

                    this.complete = afterAjaxRequest;
                }
            });
        },

        /**
         * Calls the frontend to retrieve a new csrf token and executes the afterInit on success
         */
        requestToken: function() {
            var me = this;

            $.ajax({
                url: window.csrfConfig.generateUrl,
                success: function(response, status, xhr) {
                    StorageManager.setItem('local', me.storageKey, xhr.getResponseHeader('x-csrf-token'));
                    $.removeCookie('invalidate-xcsrf-token');
                    me.afterInit();
                }
            });
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
        }

    };

    $(function() {
        CSRF.init();
    });

    window.CSRF = CSRF;

})(jQuery, window, document);