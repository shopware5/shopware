;(function ($) {
    'use strict';

    /**
     * Image Gallery Plugin.
     *
     * This plugin opens a clone of an existing image slider in a lightbox.
     * This image slider clone provides three control buttons (zoom in, zoom out
     * and reset zoom) and also enables advanced features of the
     * image slider plugin like pinch-to-zoom, double-tap, moving scaled images.
     */
    $.plugin('imageGallery', {

        defaults: {

            /**
             *
             * @property imageContainerSelector
             * @type {String}
             */
            imageContainerSelector: '.image-slider--container',

            /**
             *
             * @property imageSlideSelector
             * @type {String}
             */
            imageSlideSelector: '.image-slider--slide',

            /**
             *
             * @property thumbnailContainerSelector
             * @type {String}
             */
            thumbnailContainerSelector: '.image-slider--thumbnails',

            /**
             *
             * @property imageGalleryClass
             * @type {String}
             */
            imageGalleryClass: 'image--gallery',

            /**
             *
             * @property previousKeyCode
             * @type {Number}
             */
            previousKeyCode: 37,

            /**
             *
             * @property nextKeyCode
             * @type {Number}
             */
            nextKeyCode: 39,

            /**
             *
             * @property maxZoom
             * @type {Number|String}
             */
            maxZoom: 'auto',

            /**
             *
             * @property disabledCls
             * @type {String}
             */
            disabledCls: 'is--disabled'
        },

        /**
         * Method for the plugin initialisation.
         * Merges the passed options with the data attribute configurations.
         * Creates and references all needed elements and properties.
         * Calls the registerEvents method afterwards.
         *
         * @public
         * @method init
         */
        init: function () {
            var me = this,
                $el,
                img;

            me.applyDataAttributes();

            me.$imageContainer = me.$el.find(me.opts.imageContainerSelector);
            me.$thumbContainer = me.$el.find(me.opts.thumbnailContainerSelector);
            me.$imageContainerClone = me.$imageContainer.clone();
            me.$thumbContainerClone = me.$thumbContainer.clone();
            me.$zoomOutBtn = me.createZoomOutButton().appendTo(me.$imageContainerClone);
            me.$zoomResetBtn = me.createZoomResetButton().appendTo(me.$imageContainerClone);
            me.$zoomInBtn = me.createZoomInButton().appendTo(me.$imageContainerClone);

            me.opened = false;

            me.$imageContainerClone.find('span[data-img-original]').each(function (i, el) {
                $el = $(el);

                img = $('<img>', {
                    'class': 'image--element',
                    'src': $el.attr('data-img-original')
                });

                $el.replaceWith(img);
            });

            me.$template = $('<div>', {
                'class': me.opts.imageGalleryClass,
                'html': [
                    me.$imageContainerClone,
                    me.$thumbContainerClone
                ]
            });

            me.registerEvents();
        },

        /**
         * Creates and returns the zoom in ( [+] ) button.
         *
         * @private
         * @method createZoomInButton
         */
        createZoomInButton: function () {
            return $('<div>', {
                'class': 'btn icon--plus3 is--small button--zoom-in'
            });
        },

        /**
         * Creates and returns the zoom out ( [-] ) button.
         *
         * @private
         * @method createZoomOutButton
         */
        createZoomOutButton: function () {
            return $('<div>', {
                'class': 'btn icon--minus3 is--small button--zoom-out'
            });
        },

        /**
         * Creates and returns the zoom reset ( [-><-] ) button.
         *
         * @private
         * @method createZoomResetButton
         */
        createZoomResetButton: function () {
            return $('<div>', {
                'class': 'btn icon--resize-shrink is--small button--zoom-reset'
            });
        },

        /**
         * Registers all needed events of the plugin.
         *
         * @private
         * @method registerEvents
         */
        registerEvents: function () {
            var me = this;

            me._on(me.opts.imageSlideSelector, 'click', $.proxy(me.onClick, me));
            $.subscribe('plugin/imageZoom/onLensClick', $.proxy(me.onClick, me));

            me._on(window, 'keydown', $.proxy(me.onKeyDown, me));
        },

        /**
         * Will be called when the zoom reset button was clicked.
         * Resets the current image scaling of the image slider.
         *
         * @event onResetZoom
         * @param {jQuery.Event} event
         */
        onResetZoom: function (event) {
            var me = this,
                plugin = me.$template.data('plugin_imageSlider');

            event.preventDefault();

            if (!plugin || me.$zoomResetBtn.hasClass(me.opts.disabledCls)) {
                return;
            }

            me.disableButtons();

            plugin.resetTransformation(true, me.enableButtons.bind(me));
        },

        /**
         * Will be called when the zoom in button was clicked.
         * Zooms the image slider in by the factor of 1.
         *
         * @event onZoomIn
         * @param {jQuery.Event} event
         */
        onZoomIn: function (event) {
            var me = this,
                plugin = me.$template.data('plugin_imageSlider');

            event.preventDefault();

            if (!plugin || me.$zoomInBtn.hasClass(me.opts.disabledCls)) {
                return;
            }

            me.disableButtons();

            plugin.scale(1, true, me.enableButtons.bind(me));
        },

        /**
         * Will be called when the zoom out button was clicked.
         * Zooms the image slider out by the factor of 1.
         *
         * @event onZoomOut
         * @param {jQuery.Event} event
         */
        onZoomOut: function (event) {
            var me = this,
                plugin = me.$template.data('plugin_imageSlider');

            event.preventDefault();

            if (!plugin || me.$zoomOutBtn.hasClass(me.opts.disabledCls)) {
                return;
            }

            me.disableButtons();

            plugin.scale(-1, true, me.enableButtons.bind(me));
        },

        /**
         * Will be called when an keyboard key was pressed.
         * If the previous/next keycode was pressed, it will slide to
         * the previous/next image.
         *
         * @event onKeyDown
         * @param {jQuery.Event} event
         */
        onKeyDown: function (event) {
            var me = this,
                opts = me.opts,
                plugin = me.$template.data('plugin_imageSlider');

            if (!plugin) {
                return;
            }

            if (event.which === opts.previousKeyCode) {
                plugin.slidePrev();
            }

            if (event.which === opts.nextKeyCode) {
                plugin.slideNext();
            }
        },

        /**
         * Will be called when the detail page image slider was clicked..
         * Opens the lightbox with an image slider clone in it.
         *
         * @event onClick
         */
        onClick: function () {
            var me = this,
                plugin = me.$el.data('plugin_imageSlider');

            if (me.opened) {
                return;
            }
            me.opened = true;

            $.modal.open(me.$template, {
                width: '100%',
                height: '100%',
                animationSpeed: 350,
                additionalClass: 'no--border-radius',
                onClose: me.onCloseModal.bind(me)
            });

            me._on(me.$zoomInBtn, 'click touchstart', $.proxy(me.onZoomIn, me));
            me._on(me.$zoomOutBtn, 'click touchstart', $.proxy(me.onZoomOut, me));
            me._on(me.$zoomResetBtn, 'click touchstart', $.proxy(me.onResetZoom, me));

            picturefill();

            me.$template.imageSlider({
                dotNavigation: false,
                swipeToSlide: true,
                swipeTolerance: 50,
                pinchToZoom: true,
                doubleTap: true,
                maxZoom: me.opts.maxZoom,
                startIndex: plugin ? plugin.slideIndex : 0
            });
        },

        /**
         * Will be called when the modal box was closed.
         * Destroys the imageSlider plugin instance of the lightbox template.
         *
         * @event onCloseModal
         */
        onCloseModal: function () {
            var me = this,
                plugin = me.$template.data('plugin_imageSlider');

            me.opened = false;

            if (!plugin) {
                return;
            }

            plugin.destroy();
        },

        /**
         * This function disables all three control buttons.
         * Will be called when an animation begins.
         *
         * @public
         * @method disableButtons
         */
        disableButtons: function () {
            var me = this,
                disabledClass = me.opts.disabledCls;

            me.$zoomResetBtn.addClass(disabledClass);
            me.$zoomOutBtn.addClass(disabledClass);
            me.$zoomInBtn.addClass(disabledClass);
        },

        /**
         * This function enables all three control buttons.
         * Will be called when an animation has finished.
         *
         * @public
         * @method enableButtons
         */
        enableButtons: function () {
            var me = this,
                disabledClass = me.opts.disabledCls;

            me.$zoomResetBtn.removeClass(disabledClass);
            me.$zoomOutBtn.removeClass(disabledClass);
            me.$zoomInBtn.removeClass(disabledClass);
        },

        /**
         *
         * @public
         * @method destroy
         */
        destroy: function () {
            var me = this,
                plugin = me.$template.data('plugin_imageSlider');

            if (plugin) {
                plugin.destroy();
            }

            me.$template.remove();
            me.$template = null;

            me.$zoomOutBtn.remove();
            me.$zoomResetBtn.remove();
            me.$zoomInBtn.remove();

            me.$imageContainer = null;
            me.$thumbContainer = null;
            me.$imageContainerClone = null;
            me.$thumbContainerClone = null;
        }
    });

    $(function () {
        $('*[data-image-gallery="true"]').imageGallery();
    });
})(jQuery);