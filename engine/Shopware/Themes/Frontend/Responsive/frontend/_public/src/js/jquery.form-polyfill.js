;(function($, window, document, undefined) {
    'use strict';

    $.plugin('formPolyfill', {
        /**
         * Feature detection for the ```form```-attribute of
         * input elements.
         * The W3C spec describes that the attributes contains
         * the ID of the form which needs to be send to the server
         * side.
         *
         * @private
         * @returns {Boolean} Truthy, if the browser supports it, otherwise false.
         */
        supportFormAttribute: function() {
            var input = document.createElement('input');
            return 'form' in input;
        },

        /**
         * Initializes the plugin and sets up all necessary event listeners.
         * @returns {boolean}
         */
        init: function() {
            var me = this,
                hasSupport = me.supportFormAttribute();

            // If the browser supports the feature, we don't need to take action
            if(hasSupport) {
                return false;
            }

            me._on(me.$el, 'click', $.proxy(me.onSubmitForm, this));
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
        }
    });
})(jQuery, window, document);