;(function($, window) {
    'use strict';

    /**
     * AJAX wishlist plugin
     *
     * The plugin provides the ability to add products to the notepad using AJAX. The benefit
     * using AJAX is that the user doesn't get a page reload and therefor remains at the
     * exact same spot on the page.
     *
     * @example
     * <div class="container" data-ajax-wishlist="true">
     *     ...lots of data
     *     <a href="action--note" data-text="Saved">Note it</a>
     * </div>
     */
    $.plugin('ajaxWishlist', {

        /** @object Default configuration */
        defaults: {
            iconCls: 'icon--check',
            counterSelector: '.notes--quantity',
            savedCls: 'js--is-saved',
            text: 'Gemerkt'
        },

        /**
         * Initializes the plugin
         */
        init: function() {
            var me = this;

            me.applyDataAttributes();

            me.$counter = $(me.opts.counterSelector);

            me.registerEvents();
        },

        /**
         * Registers the necessary event listeners for the plugin
         */
        registerEvents: function() {
            var me = this;

            me.$el.on(me.getEventName('click'), '.action--note, .link--notepad', $.proxy(me.triggerRequest, me));
        },

        /**
         * Event listener handler which will be called when the user clicks on the associated element.
         *
         * The handler triggers an AJAX call to add a product to the notepad.
         *
         * @param {object} event - event object
         */
        triggerRequest: function(event) {
            var me = this,
                $target = $(event.currentTarget),
                href = $target.attr('href');

            if ($target.hasClass(me.opts.savedCls)) {
                return true;
            }

            event.preventDefault();

            $.getJSON(href, $.proxy(me.responseHandler, me, $target));
        },

        /**
         * Handles the server response and terminates if the AJAX was successful,
         * updates the counter in the head area of the store front and
         * triggers the animation of the associated element.
         *
         * @param {object} $target - The associated element
         * @param {object} response - The ajax response as a object
         * @returns {boolean}
         */
        responseHandler: function($target, response) {
            var me = this;

            if (!response.success) {
                return false;
            }

            me.$counter.html(response.notesCount);
            me.animateElement($target);
        },

        /**
         * Animates the element when the AJAX request was successful.
         *
         * @param {object} $target - The associated element
         */
        animateElement: function($target) {
            var me = this,
                $icon = $target.find('i'),
                originalIcon = $icon[0].className,
                $text = $target.find('.action--text');

            $target.addClass('js--is-animating ' + me.opts.savedCls);
            $text.html($target.attr('data-text') || me.opts.text);
            $icon.removeClass(originalIcon).addClass(me.opts.iconCls);
        },

        /**
         * Destroys the plugin
         */
        destroy: function() {
            var me = this;

            me.$el.off(me.getEventName('click'));
        }
    });
})(jQuery, window);