;(function ($) {
    $.plugin('tabMenu', {
        defaults: {
            'pluginClass': 'js--tab-menu',

            'tabContainerSelector': '.tab--navigation',

            'tabSelector': '.tab--link',

            'contentContainerSelector': '.tab--container-list',

            'contentSelector': '.tab--container',

            'activeTabClass': 'is--active',

            'activeContainerClass': 'is--active',

            'startIndex': 0
        },

        init: function () {
            var me = this,
                opts = me.opts,
                $el = me.$el;

            me.applyDataAttributes();

            $el.addClass(opts.pluginClass);

            me.$tabContainer = $el.find(opts.tabContainerSelector);

            me.$contentContainer = $el.find(opts.contentContainerSelector);

            me.$tabs = me.$tabContainer.find(opts.tabSelector);

            me.$contents = me.$contentContainer.find(opts.contentSelector);

            me._index = null;

            me.registerEventListeners();

            me.changeTab(opts.startIndex)
        },

        registerEventListeners: function () {
            var me = this;

            me.$tabs.each(function (i, el) {
                me._on($(el), 'click touchstart', $.proxy(me.changeTab, me, i));
            });
        },

        changeTab: function (index, event) {
            var me = this,
                opts = me.opts,
                activeTabClass = opts.activeTabClass,
                activeContainerClass = opts.activeContainerClass,
                $tab,
                $content;

            if (event) {
                event.preventDefault();
            }

            if (index === me._index) {
                return;
            }

            me._index = index;

            $tab = $(me.$tabs.get(index));
            $content = $(me.$contents.get(index));

            me.$tabContainer
                .find('.' + activeTabClass)
                .removeClass(activeTabClass);

            $tab.addClass(activeTabClass);

            me.$contentContainer
                .find('.' + activeContainerClass)
                .removeClass(activeContainerClass);

            $content.addClass(activeContainerClass);

            if ($tab.attr('data-mode') === 'remote' && $tab.attr('data-url')) {
                $content.load($tab.attr('data-url'));
            }
        },

        destroy: function () {
            var me = this;

            me.$el.removeClass(me.opts.pluginClass);

            me._destroy();
        }
    });
})(jQuery);