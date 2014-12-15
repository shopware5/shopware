;(function($, window, undefined) {
    'use strict';

    /**
     * Simple plugin which replaces the button with a loading indicator to prevent multiple clicks on the
     * same button.
     *
     * @example
     * <button type="submit" data-preloader-button="true">Submit me!</button>
     */
    $.plugin('preloaderButton', {

        /** @object Default configuration */
        defaults: {

            /** @string CSS class for the loading indicator */
            loaderCls: 'js--loading',

            /** @boolean Truthy, if the button is attached to a form which needs to be valid before submitting  */
            checkFormIsValid: true
        },

        /**
         * Initializes the plugin
         */
        init: function() {
            var me = this;

            me.applyDataAttributes();

            me._on(me.$el, 'click', $.proxy(me.onShowPreloader, me));
        },

        /**
         * Event handler method which will be called when the user clicks on the
         * associated element.
         *
         * @returns {boolean}
         */
        onShowPreloader: function(event) {
            var me = this;

            if(me.opts.checkFormIsValid) {
                var $form = $('#' + me.$el.attr('form')) || me.$el.parents('form');

                if (!$form.length || !$form[0].checkValidity()) {
                    return;
                }
            }

            //... we have to use a timeout, otherwise the element will not be inserted in the page.
            window.setTimeout(function() {
                me.$el.replaceWith('<div class="' + me.opts.loaderCls + '"></div>');
            }, 50);
        }
    });
})(jQuery, window);