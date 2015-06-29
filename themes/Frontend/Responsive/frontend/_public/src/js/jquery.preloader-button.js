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

            me.opts.checkFormIsValid = me.checkForValiditySupport();

            me._on(me.$el, 'click', $.proxy(me.onShowPreloader, me));

            $.publish('plugin/preloaderButton/onRegisterEvents', me);
        },

        /**
         * Checks if the browser supports HTML5 form validation
         * on form elements.
         *
         * @returns {boolean}
         */
        checkForValiditySupport: function() {
            var me = this,
                element = document.createElement('input'),
                valid = (typeof element.validity === 'object');

            $.publish('plugin/preloaderButton/onCheckForValiditySupport', [me, valid]);

            return valid;
        },

        /**
         * Event handler method which will be called when the user clicks on the
         * associated element.
         */
        onShowPreloader: function() {
            var me = this;

            if(me.opts.checkFormIsValid) {
                var $form = $('#' + me.$el.attr('form')) || me.$el.parents('form');

                if (!$form.length || !$form[0].checkValidity()) {
                    return;
                }
            }

            //... we have to use a timeout, otherwise the element will not be inserted in the page.
            window.setTimeout(function() {
                me.$el.html(me.$el.text() + '<div class="' + me.opts.loaderCls + '"></div>').attr('disabled', 'disabled');

                $.publish('plugin/preloaderButton/onShowPreloader', me);
            }, 25);
        }
    });
})(jQuery, window);