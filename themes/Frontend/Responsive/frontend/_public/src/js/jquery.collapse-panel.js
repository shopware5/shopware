;(function ($) {
    'use strict';

    /**
     * Shopware Collapse Panel Plugin.
     */
    $.plugin('swCollapsePanel', {

        alias: 'collapsePanel',

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
             * Selector for the content sibling when no collapseTargetCls was passed.
             *
             * @type {String}
             */
            contentSiblingSelector: '.collapse--content',

            /**
             * Additional class which will be added to the collapse target.
             *
             * @type {String}
             */
            collapseTargetCls: 'js--collapse-target',

            /**
             * The class which triggers the collapsed state.
             *
             * @type {String}
             */
            collapsedStateCls: 'is--collapsed',

            /**
             * The class for the active state of the trigger element.
             *
             * @type {String}
             */
            activeTriggerCls: 'is--active',

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
            animationSpeed: 400,

            /**
             * Prefix for the URL hash to prevent it from being interpreted as an anchor
             * jumpmark
             */
            hashPrefix: 'show-',

            /**
             * Action which will be executed if the element is clicked
             */
            action: 'toggle'
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
                opts = me.opts;

            me.applyDataAttributes();

            if (opts.collapseTarget) {
                me.$targetEl = $(opts.collapseTarget);
            } else {
                me.$targetEl = me.$el.next(opts.contentSiblingSelector);
            }

            me.$targetEl.addClass(opts.collapseTargetCls);

            me.handleHashParameter();

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
                switch (me.opts.action) {
                    case 'open':
                        me.openPanel();
                        break;
                    case 'close':
                        me.closePanel();
                        break;
                    default:
                        me.toggleCollapse();
                }
            });

            $.publish('plugin/swCollapsePanel/onRegisterEvents', [ me ]);
        },

        /**
         * Toggles the collapse state of the element.
         *
         * @public
         * @method toggleCollapse
         */
        toggleCollapse: function () {
            var me = this;

            if (me.$targetEl.hasClass(me.opts.collapsedStateCls)) {
                me.closePanel();
            } else {
                me.openPanel();
            }

            $.publish('plugin/swCollapsePanel/onToggleCollapse', [ me ]);
        },

        /**
         * Opens the panel by sliding it down.
         *
         * @public
         * @method openPanel
         */
        openPanel: function () {
            var me = this,
                opts = me.opts,
                $targetEl = me.$targetEl,
                $siblings = $('.' + opts.collapseTargetCls).not($targetEl),
                tabId = $targetEl.parent().attr('data-tab-id');

            me.$el.addClass(opts.activeTriggerCls);

            if ($targetEl.hasClass(opts.collapsedStateCls)) {
                return;
            }

            $targetEl.finish().slideDown(opts.animationSpeed, function () {
                $.publish('plugin/swCollapsePanel/onOpen', [ me ]);
            }).addClass(opts.collapsedStateCls);

            if (opts.closeSiblings) {
                $siblings.finish().slideUp(opts.animationSpeed, function () {
                    $siblings.removeClass(opts.collapsedStateCls);
                    $siblings.prev().removeClass(opts.activeTriggerCls);
                });
            }

            if (tabId !== undefined) {
                $.publish('onShowContent-' + tabId, [ me ]);
            }

            $.publish('plugin/swCollapsePanel/onOpenPanel', [ me ]);
        },

        /**
         * Closes the panel by sliding it up.
         *
         * @public
         * @method openPanel
         */
        closePanel: function () {
            var me = this,
                opts = me.opts;

            me.$el.removeClass(opts.activeTriggerCls);

            if (!me.$targetEl.hasClass(opts.collapsedStateCls)) {
                return;
            }

            me.$targetEl.finish().slideUp(opts.animationSpeed, function() {
                me.$targetEl.removeClass(opts.collapsedStateCls);
                $.publish('plugin/swCollapsePanel/onClose', [ me ]);
            });

            $.publish('plugin/swCollapsePanel/onClosePanel', [ me ]);
        },

        handleHashParameter: function () {
            var me = this,
                hash = window.location.hash,
                prefixLength = me.opts.hashPrefix.length;

            if (hash.indexOf(me.opts.hashPrefix) !== 1) {
                return;
            }
            if (!me.$targetEl.is('#' + hash.substr(prefixLength + 1))) {
                return;
            }

            me.openPanel();
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
            var me = this,
                opts = me.opts;

            me.$el.removeClass(opts.activeTriggerCls);
            me.$targetEl.removeClass(opts.collapsedStateCls)
                .removeClass(opts.collapseTargetCls)
                .prop('style', '');

            me._destroy();
        }
    });
})(jQuery);
