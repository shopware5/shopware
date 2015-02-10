;(function ($) {
    'use strict';

    /**
     * Shopware Advanced Menu Plugin
     */
    $.plugin('advancedMenu', {
        /**
         * Default settings that will be used when the specific option was not specified.
         *
         * @type {Object}
         */
        defaults: {
            /**
             * Selector for the main navigation.
             *
             * @type {String}
             */
            'listSelector': '.navigation--list.container',

            /**
             * Selector for all navigation items that are not the home.
             *
             * @type {String}
             */
            'navigationItemSelector': '.navigation--entry:not(.is--home)',

            /**
             * Selector to get the close arrow.
             *
             * @type {String}
             */
            'closeButtonSelector': '.button--close',

            /**
             * Selector to get all menu container.
             *
             * @type {String}
             */
            'menuContainerSelector': '.menu--container',

            /**
             * Class that will be set for the currently active menu.
             *
             * @type {String}
             */
            'menuActiveClass': 'menu--is-active',

            /**
             * Class that will be set for the current hovered nav item.
             *
             * @type {String}
             */
            'itemHoverClass': 'is--hovered'
        },

        /**
         * @public
         * @method init
         */
        init: function () {
            var me = this;

            me.applyDataAttributes();

            /**
             * The navigation that the advanced menu should be applied to.
             * Wrapped by jQuery.
             *
             * @private
             * @property _$list
             * @type {jQuery}
             */
            me._$list = $(me.opts.listSelector);

            if (!me._$list.length) {
                return;
            }

            /**
             * Contains all list items of the navigation.
             * Wrapped by jQuery.
             *
             * @private
             * @property _$listItems
             * @type {jQuery}
             */
            me._$listItems = me._$list.find(me.opts.navigationItemSelector);

            /**
             * The arrow to close the advanced menu.
             * Wrapped by jQuery.
             *
             * @private
             * @property _$closeButton
             * @type {jQuery}
             */
            me._$closeButton = me.$el.find(me.opts.closeButtonSelector);

            // Register all needed events
            me.registerEvents();
        },

        /**
         * Registers the click / tap / mouseover events on the navigation items.
         * When one of them fires, the advanced menu will be opened.
         *
         * As long the mouse stays in the advanced menu, it stays opened.
         *
         * @public
         * @method registerEvents
         */
        registerEvents: function () {
            var me = this,
                $el;

            $.each(me._$listItems, function (i, el) {
                $el = $(el);

                me._on($el, 'mouseenter touchend MSPointerDown', $.proxy(me.onListItemClick, me, i, $el));

                me._on($el, 'mouseleave', $.proxy(me.onMouseLeave, me));
            });

            me._on(me.$el, 'mouseenter', $.proxy(me.onMouseEnter, me));
            me._on(me.$el, 'mouseleave', $.proxy(me.onMouseLeave, me));

            me._on(me._$closeButton, 'click touchstart MSPointerDown', $.proxy(me.onCloseButtonClick, me));
        },

        /**
         * Fired when the navigation list items were clicked / tapped or when the mouse enters them.
         *
         * @event onMouseEnter
         * @param {jQuery.Event} event
         * @param {jQuery} $el
         * @param {Number} index
         */
        onListItemClick: function (index, $el, event) {
            var me = this,
                opts = me.opts;

            event.stopPropagation();

            me.setMenuIndex(index);

            me._$list.find('.' + opts.itemHoverClass).removeClass(opts.itemHoverClass);

            $el.addClass(opts.itemHoverClass);

            me.onMouseEnter(event);
        },

        /**
         * Fired when the navigation list items were clicked / tapped or when the mouse enters them.
         *
         * @event onMouseEnter
         * @param {jQuery.Event} event
         */
        onMouseEnter: function (event) {
            event.preventDefault();

            this.openMenu();

            $.publish('plugin/advancedMenu/onOpenMenu');
        },

        /**
         * Fired when the mouse leaves the navigation list items or advanced menu.
         *
         * @event onMouseLeave
         * @param {jQuery.Event} event
         */
        onMouseLeave: function (event) {
            event.preventDefault();
            var me = this,
                target = event.toElement || event.relatedTarget;

            if($.contains(me.$el[0], target) || me._$listItems.has(target).length) {
                return;
            }

            me.closeMenu();

            $.publish('plugin/advancedMenu/onCloseMenu');
        },

        /**
         * Fired when the mouse leaves the navigation list items or advanced menu.
         *
         * @event onCloseButtonClick
         * @param {jQuery.Event} event
         */
        onCloseButtonClick: function (event) {
            event.preventDefault();

            this.closeMenu();

            $.publish('plugin/advancedMenu/onCloseWithButton');
        },

        /**
         * Sets the active menu index.
         * The index is ordered based on the menu containers.
         *
         * @public
         * @method setMenuIndex
         * @param index
         */
        setMenuIndex: function (index) {
            var me = this,
                menus = me.$el.find(me.opts.menuContainerSelector);

            menus.each(function (i, el) {
                $(el).toggleClass(me.opts.menuActiveClass, i === index);
            });
        },

        /**
         * Opens / shows the advanced menu.
         *
         * @public
         * @method openMenu
         */
        openMenu: function () {
            this.$el.show();
        },

        /**
         * Closes / hides the advanced menu.
         *
         * @public
         * @method closeMenu
         */
        closeMenu: function () {
            var me = this,
                opts = me.opts;

            me._$list.find('.' + opts.itemHoverClass).removeClass(opts.itemHoverClass);

            me.$el.hide();
        }
    });
})(jQuery);

/**
 * Call the plugin when the shop is ready
 */
$(function () {
    $('*[data-advanced-menu="true"]').advancedMenu();
});