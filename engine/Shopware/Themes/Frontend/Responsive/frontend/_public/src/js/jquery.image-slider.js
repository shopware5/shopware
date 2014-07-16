;(function($) {
    'use strict';

    /**
     * Shopware Image Slider Plugin
     *
     * @example
     *
     * HTML:
     *
     * <div class="container">
     *     <div data-thumbnails="true">
     *          <a href="ORIGINAL_SRC" data-original-img="ORIGINAL_SRC" small-img="THUMBNAIL_SRC" data-title="ALT" class="is--active">
     *              <img src="THUMBNAIL_SRC">
     *          </a>
     *
     *          <a href="ORIGINAL_SRC" data-original-img="ORIGINAL_SRC" small-img="THUMBNAIL_SRC" data-title="ALT">
     *              <img src="THUMBNAIL_SRC">
     *          </a>
     *
     *          <!-- more thumbnails -->
     *     </div>
     *
     *     <div data-image-scroller="true">
     *         <ul class="image--list">
     *             <li>
     *                 <img src="">
     *             </li>
     *
     *             <li>
     *                 <img src="">
     *             </li>
     *
     *             <!-- more images -->
     *         </ul>
     *     </div>
     * </div>
     *
     * JS:
     *
     * $('.container').imageSlider();
     */
    $.plugin('imageSlider', {

        /**
         * Default options for the imageSlider plugin.
         *
         * @public
         * @property defaults
         * @type {Object}
         */
        defaults: {
            /**
             * Selector for the image list container to set the image scroller.
             *
             * @type {String}
             */
            'scrollerListSelector': '*[data-image-scroller="true"]',

            /**
             * Selector of the thumbnails for the thumbnail sliding.
             *
             * @type {String}
             */
            'thumbnailSelector': '*[data-thumbnails="true"] > a',

            /**
             * Class that will be applied when a thumbnail is active.
             *
             * @type {String}
             */
            'activeThumbnailClass': 'is--active',

            /**
             * Options for the lightbox..
             *
             * @type {Object}
             */
            'lightboxSettings': { },

            /**
             * Options for the image scroller..
             *
             * @type {Object}
             */
            'imageScrollerSettings': { }
        },

        /**
         * Default plugin initialisation function.
         * Sets all needed properties, creates the slider template
         * and registers all needed event listeners.
         *
         * @public
         * @method init
         */
        init: function () {
            var me = this,
                opts = me.opts,
                imageScrollerSettings = $.extend({
                    'pinchToZoom': false,
                    'onSlide': $.proxy(me.onScrollerSlide, me),
                    'onClick': $.proxy(me.onScrollerClick, me)
                }, opts.imageScrollerSettings);

            me._$thumbnails = me.$el.find(opts.thumbnailSelector);

            if (!me._$thumbnails.length) {
                return;
            }

            me._$imageScrollerEl = me.$el.find(opts.scrollerListSelector);
            me._$imageScrollerEl.imageScroller(imageScrollerSettings);

            me.imageScroller = me._$imageScrollerEl.data('plugin_imageScroller');
            me.imageScroller.$el.css('cursor', 'pointer');

            me.$el.lightbox(opts.lightboxSettings);

            me.lightbox = me.$el.data('plugin_lightbox');

            me.registerEvents();
        },

        /**
         * Registers the event listeners for changing the slides.
         *
         * @private
         * @method registerEvents
         */
        registerEvents: function () {
            var me = this;

            $.each(me._$thumbnails, function (index, el) {
                me._on(el, 'click', function (event) {
                    event.preventDefault();
                    me.imageScroller.slideTo(index);
                })
            });
        },

        /**
         * Called when the slider has changed the index.
         *
         * @private
         * @method onScrollerSlide
         */
        onScrollerSlide: function ($img, index) {
            var me = this,
                activeClass = me.opts.activeThumbnailClass;

            $.each(me._$thumbnails, function (i, el) {
                $(el).toggleClass(activeClass, i === index);
            });
        },

        /**
         * Called when the slider was clicked.
         *
         * @private
         * @method onScrollerClick
         */
        onScrollerClick: function () {
            var me = this;

            me.lightbox.open();
        },

        /**
         * Removed all listeners, classes and values from this plugin.
         *
         * @public
         * @method destroy
         */
        destroy: function () {
            var me = this;

            me.imageScroller.destroy();
            me.lightbox.destroy();

            me._$thumbnails.length = 0;

            me._destroy();
        }
    });
})(jQuery);