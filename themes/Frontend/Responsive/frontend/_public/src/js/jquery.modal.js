;(function ($, window) {
    'use strict';

    var emptyFn = function () {},
        $html = $('html');

    /**
     * Shopware Modal Module
     *
     * The modalbox is "session based".
     * That means, that an .open() call will completely override the settings of the previous .open() calls.
     *
     * @example
     *
     * Simple content / text:
     *
     * $.modal.open('Hello World', {
     *     title: 'My title'
     * });
     *
     * Ajax loading:
     *
     * $.modal.open('account/ajax_login', {
     *     mode: 'ajax'
     * });
     *
     * Iframe example / YouTube Video:
     *
     * $.modal.open('http://www.youtube.com/embed/5dxVfU-yerQ', {
     *     mode: 'iframe'
     * });
     *
     * To close the modal box simply call:
     *
     * $.modal.close();
     *
     * @type {Object}
     */
    $.modal = {
        /**
         * The complete template wrapped in jQuery.
         *
         * @private
         * @property _$modalBox
         * @type {jQuery}
         */
        _$modalBox: null,

        /**
         * Container for the title wrapped in jQuery.
         *
         * @private
         * @property _$header
         * @type {jQuery}
         */
        _$header: null,

        /**
         * The title element wrapped in jQuery.
         *
         * @private
         * @property _$title
         * @type {jQuery}
         */
        _$title: null,

        /**
         * The content element wrapped in jQuery.
         *
         * @private
         * @property _$content
         * @type {jQuery}
         */
        _$content: null,

        /**
         * The close button wrapped in jQuery.
         *
         * @private
         * @property _$closeButton
         * @type {jQuery}
         */
        _$closeButton: null,

        /**
         * Default options of a opening session.
         *
         * @public
         * @property defaults
         * @type {jQuery}
         */
        defaults: {
            /**
             * The mode in which the lightbox should be showing.
             *
             * 'local':
             *
             * The given content is either text or HTML.
             *
             * 'ajax':
             *
             * The given content is the URL from what it should load the HTML.
             *
             * 'iframe':
             *
             * The given content is the source URL of the iframe.
             *
             * @type {String}
             */
            mode: 'local',

            /**
             * Sizing mode of the modal box.
             *
             * 'auto':
             *
             * Will set the given width as max-width so the container can shrink.
             * Fullscreen mode on small mobile devices.
             *
             * 'fixed':
             *
             * Will use the width and height as static sizes and will not change to fullscreen mode.
             *
             * 'content':
             *
             * Will use the height of its content instead of a given height.
             * The 'height' option will be ignored when set.
             *
             * 'full':
             *
             * Will set the modalbox to fullscreen.
             *
             * @type {String}
             */
            sizing: 'auto',

            /**
             * The width of the modal box window.
             *
             * @type {Number}
             */
            width: 600,

            /**
             * The height of the modal box window.
             *
             * @type {Number}
             */
            height: 600,

            /**
             * Whether or not the overlay should be shown.
             *
             * @type {Boolean}
             */
            overlay: true,

            /**
             * Whether or not the modal box should be closed when the user clicks on the overlay.
             *
             * @type {Boolean}
             */
            closeOnOverlay: true,

            /**
             * Whether or not the closing button should be shown.
             *
             * @type {Boolean}
             */
            showCloseButton: true,

            /**
             * Speed for every CSS transition animation
             *
             * @type {Number}
             */
            animationSpeed: 500,

            /**
             * The window title of the modal box.
             * If empty, the header will be hidden.
             *
             * @type {String}
             */
            title: '',

            /**
             * Will be overridden by the current URL when the mode is 'ajax' or 'iframe'.
             * Can be accessed by the options object.
             *
             * @type {String}
             */
            src: '',

            /**
             * Array of key codes the modal box can be closed.
             *
             * @type {Array}
             */
            closeKeys: [27],

            /**
             * Whether or not it is possible to close the modal box by the keyboard.
             *
             * @type {Boolean}
             */
            keyboardClosing: true,

            /**
             * Function which will be called when the modal box is closing.
             *
             * @type {Function}
             */
            onClose: emptyFn,

            /**
             * Whether or not the picturefill function will be called when setting content.
             *
             * @type {Boolean}
             */
            updateImages: false,

            /**
             * Class that will be added to the modalbox.
             *
             * @type {String}
             */
            additionalClass: ''
        },

        /**
         * The current merged options of the last .open() call.
         *
         * @public
         * @property options
         * @type {Object}
         */
        options: {},

        /**
         * Opens the modal box.
         * Sets the given content and applies the given options to the current session.
         * If given, the overlay options will be passed in its .open() call.
         *
         * @public
         * @method open
         * @param {String|jQuery|HTMLElement} content
         * @param {Object} options
         */
        open: function (content, options) {
            var me = this,
                $modalBox = me._$modalBox,
                opts;

            me.options = opts = $.extend({}, me.defaults, options);

            if (opts.overlay) {
                $.overlay.open($.extend({}, {
                    closeOnClick: opts.closeOnOverlay,
                    onClose: $.proxy(me.onOverlayClose, me)
                }));
            }

            if (!$modalBox) {
                me.initModalBox();
                me.registerEvents();

                $modalBox = me._$modalBox;
            }

            me._$closeButton.toggle(opts.showCloseButton);

            $modalBox.toggleClass('sizing--auto', opts.sizing === 'auto');
            $modalBox.toggleClass('sizing--fixed', opts.sizing === 'fixed');
            $modalBox.toggleClass('sizing--content', opts.sizing === 'content');
            $modalBox.toggleClass('no--header', opts.title.length === 0);

            $modalBox.addClass(opts.additionalClass);

            if (opts.sizing === 'content') {
                opts.height = 'auto';
            } else {
                $modalBox.css('top', 0);
            }

            me.setWidth(opts.width);
            me.setHeight(opts.height);
            me.setTitle(opts.title);

            // set display to block instead of .show() for browser compatibility
            $modalBox.css('display', 'block');

            switch (opts.mode) {
                case 'ajax':
                    $.ajax(content, {
                        data: {
                            isXHR: 1
                        },
                        success: function (result) {
                            me.setContent(result);
                            $.publish('plugin/modal/onOpenAjax', me);
                        }
                    });
                    me.options.src = content;
                    break;
                case 'iframe':
                    me.setContent('<iframe class="content--iframe" src="' + content + '" width="100%" height="100%"></iframe>');
                    me.options.src = content;
                    break;
                default:
                    me.setContent(content);
                    break;
            }

            me.setTransition({
                opacity: 1
            }, me.options.animationSpeed, 'linear');

            $html.addClass('no--scroll');

            $.publish('plugin/modal/onOpen', me);

            return me;
        },

        /**
         * Closes the modal box and the overlay if its enabled.
         * if the fading is completed, the content will be removed.
         *
         * @public
         * @method close
         */
        close: function () {
            var me = this,
                opts = me.options,
                $modalBox = me._$modalBox;

            if (opts.overlay) {
                $.overlay.close();
            }

            $html.removeClass('no--scroll');

            if ($modalBox !== null) {
                me.setTransition({
                    opacity: 0
                }, opts.animationSpeed, 'linear', function () {
                    $modalBox.removeClass(opts.additionalClass);

                    // set display to none instead of .hide() for browser compatibility
                    $modalBox.css('display', 'none');

                    opts.onClose.call(me);

                    me._$content.empty();
                });
            }

            $.publish('plugin/modal/onClose', me);

            return me;
        },

        /**
         * Sets the title of the modal box.
         *
         * @public
         * @method setTransition
         * @param {Object} css
         * @param {Number} duration
         * @param {String} animation
         * @param {Function} callback
         */
        setTransition: function (css, duration, animation, callback) {
            var me = this,
                $modalBox = me._$modalBox,
                opts = $.extend({
                    animation: 'ease',
                    duration: me.options.animationSpeed
                }, {
                    animation: animation,
                    duration: duration
                });

            if (!$.support.transition) {
                $modalBox.stop(true).animate(css, opts.duration, opts.animation, callback);
                return;
            }

            $modalBox.stop(true).transition(css, opts.duration, opts.animation, callback);

            $.publish('plugin/modal/onSetTransition', [me, css, opts]);
        },

        /**
         * Sets the title of the modal box.
         *
         * @public
         * @method setTitle
         * @param {String} title
         */
        setTitle: function (title) {
            var me = this;

            me._$title.html(title);

            $.publish('plugin/modal/onSetTitle', [me, title]);
        },

        /**
         * Sets the content of the modal box.
         *
         * @public
         * @method setContent
         * @param {String|jQuery|HTMLElement} content
         */
        setContent: function (content) {
            var me = this,
                opts = me.options;

            me._$content.html(content);

            if (opts.sizing === 'content') {
                // initial centering
                me.center();

                // centering again to fix some styling/positioning issues
                window.setTimeout(me.center.bind(me), 25);
            }

            if (opts.updateImages) {
                picturefill();
            }

            $.publish('plugin/modal/onSetContent', me);
        },

        /**
         * Sets the width of the modal box.
         * If a string was passed containing a only number, it will be parsed as a pixel value.
         *
         * @public
         * @method setWidth
         * @param {Number|String} width
         */
        setWidth: function (width) {
            var me = this;

            me._$modalBox.css('width', (typeof width === 'string' && !(/^\d+$/.test(width))) ? width : parseInt(width, 10));

            $.publish('plugin/modal/onSetWidth', me);
        },

        /**
         * Sets the height of the modal box.
         * If a string was passed containing a only number, it will be parsed as a pixel value.
         *
         * @public
         * @method setHeight
         * @param {Number|String} height
         */
        setHeight: function (height) {
            var me = this;

            me._$modalBox.css('height', (typeof height === 'string' && !(/^\d+$/.test(height))) ? height : parseInt(height, 10));

            $.publish('plugin/modal/onSetHeight', me);
        },

        /**
         * Creates the modal box and all its elements.
         * Appends it to the body.
         *
         * @public
         * @method initModalBox
         */
        initModalBox: function () {
            var me = this;

            me._$modalBox = $('<div>', {
                'class': 'js--modal'
            });

            me._$header = $('<div>', {
                'class': 'header'
            }).appendTo(me._$modalBox);

            me._$title = $('<div>', {
                'class': 'title'
            }).appendTo(me._$header);

            me._$content = $('<div>', {
                'class': 'content'
            }).appendTo(me._$modalBox);

            me._$closeButton = $('<div>', {
                'class': 'btn icon--cross is--small btn--grey modal--close'
            }).appendTo(me._$modalBox);

            $('body').append(me._$modalBox);

            $.publish('plugin/modal/onInit', me);
        },

        /**
         * Registers all needed event listeners.
         *
         * @public
         * @method registerEvents
         */
        registerEvents: function () {
            var me = this,
                $window = $(window);

            me._$closeButton.on('click.modal touchstart.modal', $.proxy(me.close, me));

            $window.on('keydown.modal', $.proxy(me.onKeyDown, me));
            StateManager.on('resize', me.onWindowResize, me);

            StateManager.registerListener({
                state: 'xs',
                enter: function() {
                    me._$modalBox.addClass('is--fullscreen');
                },
                exit: function () {
                    me._$modalBox.removeClass('is--fullscreen');
                }
            });

            $.publish('plugin/modal/onRegisterEvents', me);
        },

        /**
         * Called when a key was pressed.
         * Closes the modal box when the keyCode is mapped to a close key.
         *
         * @public
         * @method onKeyDown
         */
        onKeyDown: function (event) {
            var me = this,
                keyCode = event.which,
                keys = me.options.closeKeys,
                len = keys.length,
                i = 0;

            if (!me.options.keyboardClosing) {
                return;
            }

            for (; i < len; i++) {
                if (keys[i] === keyCode) {
                    me.close();
                }
            }

            $.publish('plugin/modal/onKeyDown', [me, event, keyCode]);
        },

        /**
         * Called when the window was resized.
         * Centers the modal box when the sizing is set to 'content'.
         *
         * @public
         * @method onWindowResize
         */
        onWindowResize: function (event) {
            var me = this;

            if (me.options.sizing === 'content') {
                me.center();
            }

            $.publish('plugin/modal/onWindowResize', [me, event]);
        },

        /**
         * Sets the top position of the modal box to center it to the screen
         *
         * @public
         * @method centerModalBox
         */
        center: function () {
            var me = this,
                $modalBox = me._$modalBox;

            $modalBox.css('top', ($(window).height() - $modalBox.height()) / 2);

            $.publish('plugin/modal/onCenter', me);
        },

        /**
         * Called when the overlay was clicked.
         * Closes the modalbox when the 'closeOnOverlay' option is active.
         *
         * @public
         * @method onOverlayClose
         */
        onOverlayClose: function () {
            var me = this;

            if (!me.options.closeOnOverlay) {
                return;
            }

            me.close();

            $.publish('plugin/modal/onOverlayClick', me);
        },

        /**
         * Removes the current modalbox element from the DOM and destroys its items.
         * Also clears the options.
         *
         * @public
         * @method destroy
         */
        destroy: function () {
            var me = this,
                p;

            me._$modalBox.remove();

            me._$modalBox = null;
            me._$header = null;
            me._$title = null;
            me._$content = null;
            me._$closeButton = null;

            for (p in me.options) {
                if (!me.options.hasOwnProperty(p)) {
                    continue;
                }
                delete me.options[p];
            }

            StateManager.off('resize', me.onWindowResize, me);
        }
    };

    /**
     * Shopware Modalbox Plugin
     *
     * This plugin opens a offcanvas menu on click.
     * The content of the offcanvas can either be passed to the plugin
     * or the target element will be used as the content.
     */
    $.plugin('swModalbox', {

        alias: 'modalbox',

        defaults: {

            /**
             * Selector for the target when clicked on.
             * If no selector is passed, the element itself will be used.
             * When no content was passed, the target will be used as the content.
             *
             * @property targetSelector
             * @type {String}
             */
            targetSelector: '',

            /**
             * Optional content for the modal box.
             *
             * @property content
             * @type {String}
             */
            content: '',

            /**
             * Fetch mode for the modal box
             *
             * @property mode
             * @type {String}
             */
            mode: 'local'
        },

        /**
         * Initializes the plugin, applies addition data attributes and
         * registers events for clicking the target element.
         *
         * @public
         * @method init
         */
        init: function () {
            var me = this,
                opts;

            me.opts = $.extend({}, Object.create($.modal.defaults), me.opts);

            me.applyDataAttributes();

            opts = me.opts;

            me.$target = opts.targetSelector && (me.$target = me.$el.find(opts.targetSelector)).length ? me.$target : me.$el;

            me._isOpened = false;

            me._on(me.$target, 'click', $.proxy(me.onClick, me));

            $.subscribe('plugin/modal/onClose', $.proxy(me.onClose, me));

            $.publish('plugin/modalbox/onRegisterEvents', me);
        },

        /**
         * This method will be called when the target element was clicked.
         * Opens the actual modal box and uses the provided content.
         *
         * @public
         * @method onClick
         * @param {jQuery.Event} event
         */
        onClick: function (event) {
            event.preventDefault();

            var me = this;

            $.modal.open(me.opts.content || (me.opts.mode !== 'local' ? me.$target.attr('href') : me.$target), me.opts);

            me._isOpened = true;

            $.publish('plugin/modalbox/onClick', [me, event]);
        },

        /**
         * This method will be called when the plugin specific modal box was closed.
         *
         * @public
         * @method onClick
         */
        onClose: function () {
            var me = this;

            me._isOpened = false;

            $.publish('plugin/modalbox/onClose', me);
        },

        /**
         * This method closes the modal box when its opened, destroys
         * the plugin and removes all registered events
         *
         * @public
         * @method destroy
         */
        destroy: function () {
            var me = this;

            if (me._isOpened) {
                $.modal.close();
            }

            $.unsubscribe('plugin/modal/onClose', $.proxy(me.onClose, me));

            me._destroy();
        }
    });
})(jQuery, window);

