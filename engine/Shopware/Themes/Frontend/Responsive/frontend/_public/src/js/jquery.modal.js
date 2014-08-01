;(function ($) {
    'use strict';

    var emptyFn = function () { };

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
            onClose: emptyFn
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
         * @param {Object} overlayOptions
         */
        open: function (content, options, overlayOptions) {
            var me = this,
                $modalBox = me._$modalBox,
                opts;

            me.options = opts = $.extend({}, me.defaults, options);

            if (opts.overlay) {
                $.overlay.open($.extend({}, overlayOptions, {
                    closeOnClick: opts.closeOnOverlay,
                    onClick: $.proxy(me.onOverlayClick, me)
                }));
            }

            if (!$modalBox) {
                me.initModalBox();
                me.registerEvents();

                $modalBox = me._$modalBox;
            }

            me._$closeButton.toggle(opts.showCloseButton);

            $modalBox.toggleClass('sizing--fixed', opts.sizing === 'fixed');
            $modalBox.toggleClass('sizing--content', opts.sizing === 'content');
            $modalBox.toggleClass('no--header', opts.title.length === 0);

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
                    me._$content.load(content);
                    me.options.src = content;
                    break;
                case 'iframe':
                    me.setContent('<iframe src="' + content + '" width="100%" height="100%"></iframe>');
                    me.options.src = content;
                    break;
                default:
                    me.setContent(content);
                    break;
            }

            me.setTransition({
                opacity: 1
            }, me.options.animationSpeed, 'linear');

            $.publish('plugin/modal/onOpen');

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
                opts = me.options;

            if (opts.overlay) {
                $.overlay.close();
            }

            if (me._$modalBox !== null) {
                me.setTransition({
                    opacity: 0
                }, opts.animationSpeed, 'linear', function () {
                    me._$content.empty();

                    // set display to none instead of .hide() for browser compatibility
                    me._$modalBox.css('display', 'none');

                    opts.onClose.call(me);
                });
            }

            $.publish('plugin/modal/onClose');

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
                opts = $.extend({
                    animation: 'ease',
                    duration: me.options.animationSpeed
                }, {
                    animation: animation,
                    duration: duration
                });

            if (!$.support.transition) {
                me._$modalBox.stop(true).animate(css, opts.duration, opts.animation, callback);
                return;
            }

            me._$modalBox.stop(true).transition(css, opts.duration, opts.animation, callback);
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
        },

        /**
         * Sets the content of the modal box.
         *
         * @public
         * @method setContent
         * @param {String|jQuery|HTMLElement} content
         */
        setContent: function (content) {
            var me = this;

            me._$content.html(content);

            if (me.options.sizing === 'content') {
                me.center();
            }
            
            $.publish('plugin/modal/onSetContent');
        },

        /**
         * Sets the width of the modal box.
         *
         * @public
         * @method setWidth
         * @param {Number} width
         */
        setWidth: function (width) {
            var me = this;

            me._$modalBox.css('width', width);

            $.publish('plugin/modal/onSetWidth');
        },

        /**
         * Sets the height of the modal box.
         *
         * @public
         * @method setHeight
         * @param {Number} height
         */
        setHeight: function (height) {
            var me = this;

            me._$modalBox.css('height', height);

            $.publish('plugin/modal/onSetHeight');
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

            $.publish('plugin/modal/onInit');
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

            me._$closeButton.on('click.modal', $.proxy(me.close, me));

            $window.on('keydown', $.proxy(me.onKeyDown, me));
            $window.on('resize', $.proxy(me.onWindowResize, me));

            StateManager.registerListener({
                type: 'smartphone',
                enter: function() {
                    me._$modalBox.addClass('is--fullscreen');
                },
                exit: function () {
                    me._$modalBox.removeClass('is--fullscreen');
                }
            });
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
        },

        /**
         * Called when the overlay was clicked.
         * Closes the modalbox when the 'closeOnOverlay' option is active.
         *
         * @public
         * @method onOverlayClick
         */
        onOverlayClick: function () {
            var me = this;

            if (!me.options.closeOnOverlay) {
                return;
            }

            me.close();

            $.publish('plugin/modal/onOverlayClick');
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
        }
    }
})(jQuery);

