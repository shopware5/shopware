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
                param = decodeURI((RegExp('action=(.+?)(&|$)').exec(location.search) || [, null])[1]);

            me.$htmlBody = $('body, html');
            me.tabMenuProduct = me.$el.find(me.opts.tabDetail).data('plugin_swTabMenu');
            me.$tabMenuCrossSelling = me.$el.find(me.opts.tabCrossSelling);

            me.resizeCrossSelling();
            me.registerEvents();

            if (param === 'rating') {
                me.jumpToTab(1, '#detail--product-reviews');
            }

            if (me.$el.hasClass('is--ctl-blog')) {
                param = window.location.href.indexOf("sConfirmation");

                if (param === -1) {
                    return;
                }

                me.jumpToTab(1, '#blog--comments-start');
            }
        },

        resizeCrossSelling: function () {
            var me = this,
                $container;

            if (StateManager.isCurrentState(['xs', 's']) && me.$tabMenuCrossSelling.length) {
                me.$tabMenuCrossSelling.find('.tab--container').each(function (i, el) {
                    $container = $(el);

                    if ($container.find('.tab--content').html().length) {
                        $container.addClass('has--content');
                    }
                });
            }
        },

        registerEvents: function () {
            var me = this;

            me.$el.find('.product--rating-link, .link--publish-comment').on(me.getEventName('click'), $.proxy(me.onJumpToTab, me));

            $.publish('plugin/swJumpToTab/onRegisterEvents', [ me ]);
        },

        onJumpToTab: function (event) {
            event.preventDefault();

            this.jumpToTab(1);

            $.publish('plugin/swJumpToTab/onClick', [ this, event ]);
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
            }, 0);

            $.publish('plugin/swJumpToTab/onJumpToTab', [ me, tabIndex, jumpTo ]);
        }
    });

})(jQuery, window);
