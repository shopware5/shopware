;(function ($) {
    'use strict';

    /**
     * Shopware Collapse Panel Plugin.
     *
     * @example
     *
     * HTML:
     *
     * <div data-src="CAPTCHA_REFRESH_URL" data-captcha="true"></div>
     *
     * JS:
     *
     * $('*[data-captcha="true"]').captcha();
     *
     */
    $.plugin('collapsePanel', {

        /**
         * Default options for the collapse panel plugin.
         *
         * @public
         * @property defaults
         * @type {Object}
         */
        defaults: {

            /**
             * The selector of the target element which should be collapsed.
             *
             * @type {String|HTMLElement}
             */
            collapseTarget: null,

            /**
             * Additional class which will be added to the collapse target.
             *
             * @type {String}
             */
            collapseTargetCls: 'js--collapse-target',

            /**
             * Decide if sibling collapse panels should be closed when the target is collapsed.
             *
             * @type {Boolean}
             */
            closeSiblings: false,

            /**
             * The speed of the collapse animation in ms.
             *
             * @type {Number}
             */
            animationSpeed: 300
        },

        /**
         * Default plugin initialisation function.
         * Sets all needed properties, adds classes
         * and registers all needed event listeners.
         *
         * @public
         * @method init
         */
        init: function () {
            var me = this,
                options = me.opts;

            me.getDataAttributes();

            if (options.collapseTarget.length !== 0) {
                me.$targetEl = $(options.collapseTarget);
            } else {
                me.$targetEl = me.$el.next('.collapse--content');
            }

            me.$targetEl.addClass(options.collapseTargetCls);

            me.registerEvents();
        },

        /**
         * Registers all necessary event handlers.
         *
         * @public
         * @method registerEvents
         */
        registerEvents: function () {
            var me = this;

            me._on(me.$el, 'click', function (e) {
                e.preventDefault();
                me.toggleCollapse();
            });
        },

        /**
         * Toggles the collapse state of the element.
         *
         * @public
         * @method toggleCollapse
         */
        toggleCollapse: function () {
            var me = this,
                $targetEl = me.$targetEl;

            if ($targetEl.hasClass('is--active')) {
                me.closePanel();
                return;
            }

            me.openPanel();
        },

        /**
         * Opens the panel by sliding it down.
         *
         * @public
         * @method openPanel
         */
        openPanel: function () {
            var me = this,
                options = me.opts,
                $targetEl = me.$targetEl,
                siblings = $('.' + options.collapseTargetCls).not($targetEl);

            me.$el.toggleClass('is--active', true);

            $targetEl.slideDown(options.duration, function () {
                $targetEl.toggleClass('is--active', true);
            });

            if (options.closeSiblings) {
                siblings.slideUp(options.duration, function () {
                    siblings.removeClass('is--active');
                });
            }
        },

        /**
         * Closes the panel by sliding it up.
         *
         * @public
         * @method openPanel
         */
        closePanel: function () {
            var me = this,
                $targetEl = me.$targetEl;

            me.$el.toggleClass('is--active', false);

            $targetEl.slideUp(me.opts.duration, function () {
                $targetEl.toggleClass('is--active', false);
            });
        },

        /**
         * Destroys the initialized plugin completely, so all event listeners will
         * be removed and the plugin data, which is stored in-memory referenced to
         * the DOM node.
         *
         * @public
         * @method destroy
         */
        destroy: function () {
            var me = this;

            me.$el.removeClass('is--active');
            me.$targetEl.removeClass('is--active')
                .removeClass(me.opts.collapseTargetCls)
                .removeAttr('style');

            me._destroy();
        }
    });
})(jQuery);
