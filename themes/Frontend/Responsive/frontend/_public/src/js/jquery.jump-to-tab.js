;(function($, window) {
    'use strict';

    /**
     * Tab Switcher Plugin
     *
     * This Plugin switches to the correct content tab when the user adds
     * a product review which causes a page reload. The Plugin also
     * scrolls to the correct page position where the alert messages
     * are shown.
     */
    $.plugin('swJumpToTab', {

        defaults: {
            contentCls: 'has--content',
            tabDetail: '.tab-menu--product',
            tabCrossSelling: '.tab-menu--cross-selling'
        },

        init: function () {
            var me = this,
                param = decodeURI((new RegExp('action=(.+?)(&|$)').exec(location.search) || [null, ''])[1]);

            me.$htmlBody = $('body, html');
            me.tabMenuProduct = me.$el.find(me.opts.tabDetail).data('plugin_swTabMenu');
            me.$tabMenuCrossSelling = me.$el.find(me.opts.tabCrossSelling);

            me.resizeCrossSelling();
            me.registerEvents();

            if (param.length === 0) {
                me.onHashChange(window.location.hash.replace('#', ''));
            }

            /**
             * Listen to when hash value changed on url and change to desired tab
             */
            $(window).on('hashchange', function() {
                me.onHashChange(window.location.hash.replace('#', ''));
            });

        },

        resizeCrossSelling: function () {
            var me = this,
                $container;

            if (StateManager.isCurrentState(['xs', 's']) && me.$tabMenuCrossSelling.length) {
                me.$tabMenuCrossSelling.find('.tab--container').each(function (i, el) {
                    $container = $(el);

                    if ($container.find('.tab--content').html().trim().length) {
                        $container.addClass('has--content');
                    }
                });
            }
        },

        registerEvents: function () {
            var me = this;

            me.$el.on(me.getEventName('click'), '.product--rating-link, .link--publish-comment', $.proxy(me.onJumpToTab, me));

            $.publish('plugin/swJumpToTab/onRegisterEvents', [ me ]);
        },

        onHashChange: function (param) {
            var me = this, $tab, index;

            if (window.StateManager.getCurrentState() === 's' || window.StateManager.getCurrentState() === 'xs') {
                if (param.length > 0) {
                    $tab = $('[data-containerName="' + param + '"]');

                    if ($tab.length > 0) {
                        me.openContainerInMenu($tab);
                    }
                }

                return;
            }

            if (param.length > 0) {
                $tab = $('[data-tabName="' + param + '"]');
                index = $tab.index() || 0;

                if ($tab.length > 0) {
                    me.jumpToTab(index, $tab);
                }
            }
        },

        onJumpToTab: function (event) {
            var me = this,
                $tab = $('[data-tabName="rating"]'),
                index = $tab.index() || 1;

            event.preventDefault();

            me.jumpToTab(index, $tab);

            $.publish('plugin/swJumpToTab/onClick', [ me, event ]);
        },

        jumpToTab: function (tabIndex, jumpTo) {
            var me = this;

            if (!me.$el.hasClass('is--ctl-blog')) {
                me.tabMenuProduct.changeTab(tabIndex);
            }

            $.publish('plugin/swJumpToTab/onChangeTab', [ me, tabIndex, jumpTo ]);

            if (!jumpTo || !jumpTo.length) {
                return;
            }

            me.$htmlBody.animate({
                scrollTop: $(jumpTo).offset().top
            }, 1000);

            $.publish('plugin/swJumpToTab/onJumpToTab', [ me, tabIndex, jumpTo ]);
        },

        openContainerInMenu: function ($tab) {
            var plugin = $tab.data('plugin_swOffcanvasButton'),
                canvasMenu = plugin.$el.data('plugin_swOffcanvasMenu');

            canvasMenu.openMenu();
        }
    });
})(jQuery, window);
