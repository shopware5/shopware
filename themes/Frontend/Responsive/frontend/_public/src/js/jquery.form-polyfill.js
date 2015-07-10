;(function($) {
    'use strict';

    $.plugin('swFormPolyfill', {

        defaults: {
            eventType: 'click'
        },

        /**
         * Initializes the plugin and sets up all necessary event listeners.
         */
        init: function() {
            var me = this;

            // If the browser supports the feature, we don't need to take action
            if(!me.isIE()) {
                return false;
            }

            me.applyDataAttributes();
            me.registerEvents();
        },

        /**
         * Registers all necessary event listener.
         */
        registerEvents: function() {
            var me = this;

            me._on(me.$el, me.opts.eventType, $.proxy(me.onSubmitForm, this));

            $.publish('plugin/swFormPolyfill/onRegisterEvents', me);
        },

        /**
         * Checks if we're dealing with the internet explorer.
         *
         * @private
         * @returns {Boolean} Truthy, if the browser supports it, otherwise false.
         */
        isIE: function() {
            var myNav = navigator.userAgent.toLowerCase();
            return myNav.indexOf('msie') != -1 || !!navigator.userAgent.match(/Trident.*rv[ :]*11\./);
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

            $.publish('plugin/swFormPolyfill/onSubmitForm', [me, $form]);
        },

        /**
         * Destroy method of the plugin.
         * Removes attached event listener.
         */
        destroy: function() {
            var me = this;

            me._destroy();
        }
    });
})(jQuery);
