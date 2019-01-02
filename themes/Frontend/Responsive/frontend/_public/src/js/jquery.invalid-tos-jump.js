;(function($, window) {
    'use strict';

    /**
     * Invalid ToS Jump plugin
     *
     * Triggers the has--error class for the sAGB checkbox (ToS-Checkbox) and scrolls up to display its unchecked state.
     * Especially made for iOS-Devices due to missing hints if AGB/TOS hasn't been checked
     *
     * @example The example shows the basic usage:
     *
     * ```
     * <form>
     *    <input type="checkbox" required="required" id="sAGB" data-invalid-agb-jump="true">
     * </form>
     * ```
     */
    $.plugin('swInvalidTosJump', {

        /**
         * Default settings for the plugin
         * @type {Object}
         */
        defaults: {
            /**
             * Selector, which is associated to the jumpElement to toggle an visual indicator / error-class
             */
            jumpLabelSelector: 'label[for="sAGB"]',

            /**
             * Class to add on invalid
             */
            errorClass: 'has--error'
        },

        /**
         * Initializes the plugin and sets up the necessary event listeners.
         */
        init: function () {
            var me = this;
            me.$jumpLabel = $(me.opts.jumpLabelSelector);

            me._on(me.$el, 'invalid', $.proxy(me.jumpToInvalid, me));
        },

        jumpToInvalid: function () {
            var me = this;

            window.scroll(0, me.$el.offset().top - (window.innerHeight/2));
            me.$jumpLabel.addClass(me.opts.errorClass);
        }
    });
})(jQuery, window);
