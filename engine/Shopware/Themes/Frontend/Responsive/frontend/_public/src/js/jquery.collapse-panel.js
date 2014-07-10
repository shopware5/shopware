;(function ($) {
    'use strict';

    /**
     * Shopware Collapse Panel Plugin.
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
            collapseTarget: false,

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
            var me = this;

            me.applyDataAttributes();

            if (me.opts.collapseTarget.length) {
                me.$targetEl = $(me.opts.collapseTarget);
            } else {
                me.$targetEl = me.$el.next('.collapse--content');
            }

            me.$targetEl.addClass(me.opts.collapseTargetCls);

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
            var me = this;

            if (me.$targetEl.hasClass('is--collapsed')) {
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

            me.$el.addClass('is--active');

            $targetEl.slideDown(options.duration).addClass('is--collapsed');

            if (options.closeSiblings) {
                siblings.slideUp(options.duration, function () {
                    siblings.removeClass('is--collapsed');
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
            var me = this;

            me.$el.removeClass('is--active');
            me.$targetEl.slideUp(me.opts.duration).removeClass('is--collapsed');
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
            me.$targetEl.removeClass('is--collapsed')
                .removeClass(me.opts.collapseTargetCls)
                .removeAttr('style');

            me._destroy();
        }
    });
})(jQuery);
