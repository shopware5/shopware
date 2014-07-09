;(function ($, window, modernizr) {
    'use strict';

    var emptyFn = function () {};

    /**
     * Shopware Menu Scroller Plugin
     *
     * @example
     *
     * HTML:
     *
     * <div class="container">
     *     <ul class="my--list">
     *         <li>
     *             <!-- Put any element you want in here -->
     *         </li>
     *
     *         <li>
     *             <!-- Put any element you want in here -->
     *         </li>
     *
     *         <!-- More li elements -->
     *     </ul>
     * </div>
     *
     * JS:
     *
     * $('.container').menuScroller();
     */
    $.plugin('menuScroller', {

        /**
         * Default options for the menu scroller plugin
         *
         * @public
         * @property defaults
         * @type {Object}
         */
        defaults: {
            /**
             * CSS selector for the element listing
             *
             * @type {String}
             */
            listSelector: '*[class$="--list"]',

            /**
             * CSS class which will be added to the wrapper / this.$el
             *
             * @type {String}
             */
            wrapperClass: 'js--menu-scroller',

            /**
             * CSS class which will be added to the listing
             *
             * @type {String}
             */
            listClass: 'js--menu-scroller--list',

            /**
             * CSS class which will be added to every list item
             *
             * @type {String}
             */
            itemClass: 'js--menu-scroller--item',

            /**
             * CSS class(es) which will be set for the left arrow
             *
             * @type {String}
             */
            leftArrowClass: 'js--menu-scroller--arrow left--arrow',

            /**
             * CSS class(es) which will be set for the right arrow
             *
             * @type {String}
             */
            rightArrowClass: 'js--menu-scroller--arrow right--arrow',

            /**
             * CSS Class for the arrow content to center the arrow text.
             *
             * @type {String}
             */
            arrowContentClass: 'arrow--content',

            /**
             * Content of the left arrow.
             * Default it's an arrow pointing left.
             *
             * @type {String}
             */
            leftArrowContent: '&#58897;',

            /**
             * Content of the right arrow.
             * Default it's an arrow pointing right.
             *
             * @type {String}
             */
            rightArrowContent: '&#58895;',

            /**
             * Amount of pixels the plugin should scroll per arrow click.
             *
             * There is also a additional option:
             *
             * 'auto': the visible width will be taken.
             *
             * @type {String|Number}
             */
            scrollStep: 'auto',

            /**
             * Function override which will be called when a menu item was clicked.
             *
             * @type {Function}
             * @param {jQuery} $el - the menu item wrapped in jQuery
             * @param {Number} index - the current item index.
             * @param {jQuery.Event} event - the jquery click event.
             */
            onItemClick: emptyFn
        },

        /**
         * Default plugin initialisation function.
         * Sets all needed properties, creates the slider template
         * and registers all needed event listeners.
         *
         * @public
         * @method init
         */
        init: function () {
            var me = this;

            me.applyDataAttributes();

            /**
             * Current left offset in px.
             *
             * @private
             * @property _offset
             * @type {Number}
             */
            me._offset = 0;

            /**
             * Current summed width of all elements in the list.
             *
             * @private
             * @property _width
             * @type {Number}
             */
            me._width = 0;

            me.initTemplate();

            me.updateResize();

            me.registerEvents();
        },

        /**
         * Creates all needed control items and adds plugin classes
         *
         * @public
         * @method initTemplate
         */
        initTemplate: function () {
            var me = this,
                opts = me.opts;

            me.$el.addClass(opts.wrapperClass);

            me.$list = me.$el.find(opts.listSelector);
            me.$list.addClass(opts.listClass);

            $.each(me.$list.children(), function (index, el) {
                $(el).addClass(opts.itemClass);
            });

            me.$leftArrow = $('<div>', {
                'html': $('<span>', {
                    'class': opts.arrowContentClass,
                    'html': opts.leftArrowContent
                }),
                'class': opts.leftArrowClass
            }).appendTo(me.$el);

            me.$rightArrow = $('<div>', {
                'html': $('<span>', {
                    'class': opts.arrowContentClass,
                    'html': opts.rightArrowContent
                }),
                'class': opts.rightArrowClass
            }).appendTo(me.$el);
        },

        /**
         * Registers all needed events.
         *
         * @public
         * @method registerEvents
         */
        registerEvents: function () {
            var me = this;

            me.refreshListeners();

            me._on(window, 'resize', $.proxy(me.updateResize, me));
        },

        /**
         * Sets all template event listeners.
         *
         * @public
         * @method refreshListeners
         */
        refreshListeners: function () {
            var me = this;

            $.each(me.$list.children(), function (index, el) {
                me._on(el, 'click touchstart MSPointerDown', function (event) {
                    event.preventDefault();

                    me.onItemClick($(el), index, event);
                });
            });

            me._on(me.$leftArrow, 'click touchstart MSPointerDown', $.proxy(me.onLeftArrowClick, me));
            me._on(me.$rightArrow, 'click touchstart MSPointerDown', $.proxy(me.onRightArrowClick, me));
        },

        /**
         * Will be called when the window resizes.
         * Calculates the new width and scroll step.
         * Refreshes the button states.
         *
         * @public
         * @method updateResize
         */
        updateResize: function () {
            var me = this,
                listWidth = me.$list.width();

            me._step = me.opts.scrollStep === 'auto' ? listWidth : me.opts.scrollStep;

            me._width = me.calculateWidth();

            me.updateButtons();
        },

        /**
         * Returns the sum of all item widths.
         *
         * @public
         * @method calculateWidth
         * @returns {Number}
         */
        calculateWidth: function () {
            var me = this,
                width = 0;

            $.each(me.$list.children(), function (index, el) {
                width += $(el).outerWidth(true);
            });

            return width;
        },

        /**
         * Called when left arrow was clicked / touched.
         * Adds the negated offset step to the offset.
         *
         * @public
         * @param {jQuery.Event} event
         * @method onLeftArrowClick
         */
        onLeftArrowClick: function (event) {
            var me = this;

            event.preventDefault();

            me.addOffset(me._step * -1);
        },

        /**
         * Called when right arrow was clicked / touched.
         * Adds the offset step to the offset.
         *
         * @public
         * @method onRightArrowClick
         * @param {jQuery.Event} event
         */
        onRightArrowClick: function (event) {
            var me = this;

            event.preventDefault();

            me.addOffset(me._step);
        },

        /**
         * Adds the given offset relatively to the current offset.
         *
         * @public
         * @method addOffset
         * @param {Number} offset
         */
        addOffset: function (offset) {
            var me = this;

            me.setOffset(me._offset + offset);
        },

        /**
         * Sets the absolute scroll offset.
         * Min / Max the offset so the menu stays in bounds.
         *
         * @public
         * @method setOffset
         * @param {Number} offset
         */
        setOffset: function (offset) {
            var me = this,
                maxWidth = me._width - me.$list.width();

            me._offset = Math.max(0, Math.min(maxWidth, offset));

            me.updateButtons();

            if (modernizr.csstransitions) {
                me.$list.css({
                    left: me._offset * -1
                });
            } else {
                me.$list.animate({
                    left: me._offset * -1
                }, 500);
            }
        },

        /**
         * Updates the buttons status and toggles their visibility.
         *
         * @public
         * @method updateButtons
         */
        updateButtons: function () {
            var me = this,
                listWidth = me.$list.width(),
                maxWidth = me._width - me.$list.width();

            if (listWidth >= me._width) {
                me.toggleLeftArrow(false);
                me.toggleRightArrow(false);
                return;
            }

            me.toggleLeftArrow(me._offset > 0);
            me.toggleRightArrow(me._offset < maxWidth);
        },

        /**
         * Toggles the visibility of the left arrow and the left gradient (:before)
         *
         * @public
         * @method toggleLeftArrow
         * @param {Boolean} visible
         */
        toggleLeftArrow: function (visible) {
            var me = this;

            me.$leftArrow.toggle(visible);
            me.$el.toggleClass('is--left', !visible);
        },

        /**
         * Toggles the visibility of the right arrow and the right gradient (:after)
         *
         * @public
         * @method toggleRightArrow
         * @param {Boolean} visible
         */
        toggleRightArrow: function (visible) {
            var me = this;

            me.$rightArrow.toggle(visible);
            me.$el.toggleClass('is--right', !visible);
        },

        /**
         * Called when a menu item was clicked.
         * Calls the options onItemClick function.
         *
         * @public
         * @method onItemClick
         * @param {jQuery} $el
         * @param {Number} index
         * @param {jQuery.Event} event
         */
        onItemClick: function ($el, index, event) {
            var me = this;

            me.opts.onItemClick.call(me, $el, index, event);
        },

        /**
         * Removed all listeners, classes and values from this plugin.
         *
         * @public
         * @method destroy
         */
        destroy: function () {
            var me = this;

            me.$el.removeClass(me.opts.wrapperClass);
            me.$list.removeClass(me.opts.listClass);

            $.each(me.$list.children(), function (index, el) {
                $(el).removeClass(me.opts.itemClass);
            });

            me.$leftArrow.remove();
            me.$rightArrow.remove();

            me._destroy();
        }
    });
}(jQuery, window, Modernizr));

