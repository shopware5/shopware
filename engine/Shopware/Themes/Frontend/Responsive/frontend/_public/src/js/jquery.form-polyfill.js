;(function($, window, document, undefined) {
    'use strict';

    $.plugin('formPolyfill', {

        defaults: {
            eventType: 'click'
        },

        /**
         * Initializes the plugin and sets up all necessary event listeners.
         * @returns {boolean}
         */
        init: function() {
            var me = this,
                isIE = me.isIE();

            // If the browser supports the feature, we don't need to take action
            if(!isIE) {
                return false;
            }

            me._on(me.$el, me.opts.eventType, $.proxy(me.onSubmitForm, this));
        },

        /**
         * Checks if we're dealing with the internet explorer.
         *
         * @private
         * @returns {Boolean} Truthy, if the browser supports it, otherwise false.
         */
        isIE: function() {
            var myNav = navigator.userAgent.toLowerCase();
            return myNav.indexOf('msie') != -1;
        },

        /**
         * Event listener method which is necessary when the browser
         * doesn't support the ```form``` attribute on ```input``` elements.
         * @returns {boolean}
         */
        onSubmitForm: function() {
            var me = this,
                id = '#' + me.$el.attr('form'),
                $form = $(id);

            // We can't find the form
            if(!$form.length) {
                return false;
            }

            $form.submit();
        },

        destroy: function() {

        }
    });
})(jQuery, window, document);