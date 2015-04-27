;(function ($) {
    'use strict';

    var $body = $('body');

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
             * Selector for the category link
             *
             * @type {String}
             */
            'navigationLinkSelector': '.navigation--link',

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


            /**
             * PreviousTarget will be used for pointer events to prevent
             * the default behaviour on the first click of the category.
             *
             * @type {null}
             * @private
             */
            me._previousTarget = null;

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

                me._on($el, 'mouseenter', $.proxy(me.onListItemEnter, me, i, $el));

                if (window.navigator.pointerEnabled || window.navigator.msPointerEnabled) {
                    me._on($el, 'click', $.proxy(me.onClickNavigationLink, me, i, $el))
                } else {
                    me._on($el, 'click', $.proxy(me.onClick, me, i, $el));
                }
            });

            $body.on('mousemove touchstart', $.proxy(me.onMouseMove, me));

            me._on(me._$closeButton, 'click', $.proxy(me.onCloseButtonClick, me));
        },

        /**
         * Called when a click event is triggered.
         * If touch is available preventing default behaviour.
         *
         * @param event
         */
        onClick: function () {
            var me = this;

            if(me.isTouchDevice()) {
                event.preventDefault();
            }
        },

        /**
         * Detecting touch device.
         *
         * @returns {boolean}
         */
        isTouchDevice: function() {
            return true == ("ontouchstart" in window || window.DocumentTouch && document instanceof DocumentTouch);
        },

        /**
         * Fired when the navigation list items were clicked / tapped or when the mouse enters them.
         *
         * @event onMouseEnter
         * @param {jQuery.Event} event
         * @param {jQuery} $el
         * @param {Number} index
         */
        onListItemEnter: function (index, $el, event) {
            var me = this,
                opts = me.opts;

            event.stopPropagation();

            if (!(event.originalEvent instanceof MouseEvent)) {
                event.preventDefault();
                return;
            }

            me.setMenuIndex(index);

            me._$list.find('.' + opts.itemHoverClass).removeClass(opts.itemHoverClass);

            $el.addClass(opts.itemHoverClass);

            me.onMouseEnter(event);
        },

        onClickNavigationLink: function (index, $el, event) {
            var me = this;

            me._inProgress = true;

            if(me._previousTarget !== $el[0]) {
                event.preventDefault();
                event.stopPropagation();
            }

            me._previousTarget = $el[0];
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
        },

        /**
         * Fired when the mouse leaves the navigation list items or advanced menu.
         *
         * @event onMouseLeave
         * @param {jQuery.Event} event
         */
        onMouseMove: function (event) {
            var me = this,
                target = event.target,
                pluginEl = me.$el[0];

            if (pluginEl === target || $.contains(me.$el[0], target) || me._$listItems.has(target).length) {
                return;
            }

            me.closeMenu();
        },

        /**
         * Fired when the mouse leaves the navigation list items or advanced menu.
         *
         * @event onCloseButtonClick
         * @param {jQuery.Event} event
         */
        onCloseButtonClick: function (event) {
            var me = this;

            event.preventDefault();

            me.closeMenu();

            $.publish('plugin/advancedMenu/onCloseWithButton', me);
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

            $.publish('plugin/advancedMenu/onSetMenuIndex', [ me, index ]);
        },

        /**
         * Opens / shows the advanced menu.
         *
         * @public
         * @method openMenu
         */
        openMenu: function () {
            var me = this;

            me.$el.show();

            $.publish('plugin/advancedMenu/onOpenMenu', me);
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

            me._previousTarget = null;

            $.publish('plugin/advancedMenu/onCloseMenu', me);
        }
    });
})(jQuery);

/**
 * Call the plugin when the shop is ready
 */
$(function () {
    $('*[data-advanced-menu="true"]').advancedMenu();
});