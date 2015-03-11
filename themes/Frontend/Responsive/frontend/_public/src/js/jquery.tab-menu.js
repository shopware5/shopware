;(function ($) {

    /**
     * Shopware Tab Menu Plugin
     *
     * This plugin sets up a menu with tabs you can switch between.
     */
    $.plugin('tabMenu', {
        defaults: {

            /**
             * Class that should be set on the plugin element when initializing
             *
             * @property pluginClass
             * @type {String}
             */
            'pluginClass': 'js--tab-menu',

            /**
             * Selector for the tab navigation list
             *
             * @property tabContainerSelector
             * @type {String}
             */
            'tabContainerSelector': '.tab--navigation',

            /**
             * Selector for a tab navigation item
             *
             * @property tabSelector
             * @type {String}
             */
            'tabSelector': '.tab--link',

            /**
             * Selector for the tab content list
             *
             * @property containerListSelector
             * @type {String}
             */
            'containerListSelector': '.tab--container-list',

            /**
             * Selector for the tab container in a tab container list.
             *
             * @property containerSelector
             * @type {String}
             */
            'containerSelector': '.tab--container',

            /**
             * Selector for the content element inside a tab container.
             *
             * @property contentSelector
             * @type {String}
             */
            'contentSelector': '.tab--content',

            'hasContentClass': 'has--content',

            /**
             * Class that should be set on an active tab navigation item
             *
             * @property activeTabClass
             * @type {String}
             */
            'activeTabClass': 'is--active',

            /**
             * Class that should be set on an active tab content item
             *
             * @property activeContainerClass
             * @type {String}
             */
            'activeContainerClass': 'is--active',

            /**
             * Starting index of the tabs
             *
             * @property startIndex
             * @type {Number}
             */
            'startIndex': 0
        },

        /**
         * Initializes the plugin and register its events
         *
         * @public
         * @method init
         */
        init: function () {
            var me = this,
                opts = me.opts,
                $el = me.$el,
                $container,
                $tab;

            me.applyDataAttributes();

            $el.addClass(opts.pluginClass);

            me.$tabContainer = $el.find(opts.tabContainerSelector);

            me.$containerList = $el.find(opts.containerListSelector);

            me.$tabs = me.$tabContainer.find(opts.tabSelector);

            me.$container = me.$containerList.find(opts.containerSelector);

            me.$container.each(function (i, el) {
                $container = $(el);
                $tab = $(me.$tabs.get(i));

                if ($container.find(opts.contentSelector).html().length) {
                    $container.addClass(opts.hasContentClass);
                    $tab.addClass(opts.hasContentClass);
                }
            });

            me._index = null;

            me.registerEventListeners();

            me.changeTab(opts.startIndex)
        },

        /**
         * This method registers the event listeners when when clicking
         * or tapping a tab navigation item.
         *
         * @public
         * @method registerEvents
         */
        registerEventListeners: function () {
            var me = this;

            me.$tabs.each(function (i, el) {
                me._on(el, 'click touchstart', $.proxy(me.changeTab, me, i));
            });
        },

        /**
         * This method switches to a new tab depending on the passed index
         * If the give index is the same as the current active one, nothing happens.
         *
         * @public
         * @method changeTab
         * @param {Number} index
         * @param {jQuery.Event} event
         */
        changeTab: function (index, event) {
            var me = this,
                opts = me.opts,
                activeTabClass = opts.activeTabClass,
                activeContainerClass = opts.activeContainerClass,
                $tab,
                $container;

            if (event) {
                event.preventDefault();
            }

            if (index === me._index) {
                return;
            }

            me._index = index;

            $tab = $(me.$tabs.get(index));
            $container = $(me.$container.get(index));

            me.$tabContainer
                .find('.' + activeTabClass)
                .removeClass(activeTabClass);

            $tab.addClass(activeTabClass);

            me.$containerList
                .find('.' + activeContainerClass)
                .removeClass(activeContainerClass);

            $container.addClass(activeContainerClass);

            $.each($container.find('.product-slider'), function(index, item) {
                $(item).data('plugin_productSlider').update();
            });

            if ($tab.attr('data-mode') === 'remote' && $tab.attr('data-url')) {
                $container.load($tab.attr('data-url'));
            }
        },

        /**
         * This method removes all plugin specific classes
         * and removes all registered events
         *
         * @public
         * @method destroy
         */
        destroy: function () {
            var me = this;

            me.$el.removeClass(me.opts.pluginClass);

            me._destroy();
        }
    });
})(jQuery);