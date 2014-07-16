;(function ($, window) {
    'use strict';

    /**
     * Shopware Lightbox Plugin
     *
     * @example
     *
     * HTML:
     *
     * <div class="container">
     *     <div class="thumbnails">
     *          <a href="ORIGINAL_SRC" data-original-img="ORIGINAL_SRC" small-img="THUMBNAIL_SRC" data-title="ALT" class="is--active">
     *              <img src="THUMBNAIL_SRC">
     *          </a>
     *
     *          <a href="ORIGINAL_SRC" data-original-img="ORIGINAL_SRC" small-img="THUMBNAIL_SRC" data-title="ALT">
     *              <img src="THUMBNAIL_SRC">
     *          </a>
     *
     *          <!-- Add more thumbnails here -->
     *     </div>
     * </div>
     *
     * JS:
     *
     * $('.container').lightbox({
     *     'thumbnailSelector': '.thumbnails a'
     * });
     */
    $.plugin('lightbox', {

        /**
         * Default options for the lightbox plugin.
         *
         * @public
         * @property defaults
         * @type {Object}
         */
        defaults: {
            /**
             * Selector for the thumbnails inside the container.
             *
             * @type {String}
             */
            'thumbnailSelector': '.image--thumbnails a',

            /**
             * Class that will be added to the container.
             *
             * @type {String}
             */
            'templateClass': 'js--lightbox',

            /**
             * Class that will be added to the image wrapper.
             *
             * @type {String}
             */
            'imageWrapperClass': 'image--wrapper',

            /**
             * Class that will be added to the image list.
             *
             * @type {String}
             */
            'imageListClass': 'image--list',

            /**
             * Class that will be added to the thumbnails wrapper.
             *
             * @type {String}
             */
            'thumbnailWrapperClass': 'thumbnails--wrapper',

            /**
             * Class that will be added to the thumbnails list.
             *
             * @type {String}
             */
            'thumbnailListClass': 'thumbnail--list',

            /**
             * Class that will be added to the thumbnail item / wrapper.
             *
             * @type {String}
             */
            'thumbnailItemClass': 'thumbnail',

            /**
             * Class that will be added to the thumbnail link.
             *
             * @type {String}
             */
            'thumbnailLinkClass': 'thumbnail--link',

            /**
             * Class that will be added to the thumbnail image.
             *
             * @type {String}
             */
            'thumbnailImageClass': 'thumbnail--image',

            /**
             * Class that will be added to a thumbnail if its active.
             *
             * @type {String}
             */
            'thumbnailActiveClass': 'is--active',

            /**
             * Class that will be added to the main element to display the image in full size in the wrapper.
             * It's only used when only one thumbnail is available so you don't need control elements.
             *
             * @type {String}
             */
            'fullSizeImageClass': 'full--image',

            /**
             * Selector of the element that opens the lightbox on click.
             * If the string is empty, the lightbox container (this.$el) will be used.
             *
             * @type {String|jQuery|HTMLElement}
             */
            'clickEl': '',

            /**
             * The lightbox opening width.
             *
             * @type {String|Number}
             */
            lightboxWidth: '90%',

            /**
             * The lightbox opening height.
             *
             * @type {String|Number}
             */
            lightboxHeight: '90%'
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
            var me = this;

            /**
             * Complete template with all components.
             *
             * @private
             * @property _$template
             * @type {jQuery}
             */
            me._$template = null;

            /**
             * Wrapper that contains the image list.
             *
             * @private
             * @property _$imageWrapper
             * @type {jQuery}
             */
            me._$imageWrapper = null;

            /**
             * Wrapper that contains the image list.
             *
             * @private
             * @property _$imageList
             * @type {jQuery}
             */
            me._$imageList = null;

            /**
             * Wrapper that contains the thumbnail list.
             *
             * @private
             * @property _$thumbnailWrapper
             * @type {jQuery}
             */
            me._$thumbnailWrapper = null;

            /**
             * List with all thumbnail elements.
             *
             * @private
             * @property _$thumbnailList
             * @type {jQuery}
             */
            me._$thumbnailList = null;

            /**
             * Element that opens the lightbox on click.
             *
             * @private
             * @property _$clickEl
             * @type {jQuery}
             */
            me._$clickEl = null;

            /**
             * List of all thumbnails in object format.
             *
             * @example
             * {
             *     imageUrl: '',
             *     thumbnailUrl: '',
             *     alt: ''
             * }
             *
             * @private
             * @property _thumbnails
             * @type {Array}
             */
            me._thumbnails = [];

            /**
             * Flag whether or not the listeners of the image and menu scroller should be refreshed
             *
             * @private
             * @property _refreshListeners
             * @type {boolean}
             */
            me._refreshListeners = false;

            // Create plugin template
            me.initTemplate();

            // Create thumbnail structure for the menu scroller
            me.initThumbnails();

            // Create image slide structure for the image scroller
            me.initSlides();

            // Registers all needed event listeners
            me.registerEvents();

            me._$imageWrapper.imageScroller({
                'onSlide': $.proxy(me.onSlide, me),
                'active': false
            });

            me._$thumbnailWrapper.menuScroller({
                'onItemClick': $.proxy(me.onThumbnailClick, me)
            });

            me.imageScroller = me._$imageWrapper.data('plugin_imageScroller');
            me.menuScroller = me._$thumbnailWrapper.data('plugin_menuScroller');
        },

        /**
         * Creates the template, all needed control items and adds plugin classes.
         *
         * @public
         * @method initTemplate
         */
        initTemplate: function () {
            var me = this;

            me._$template = $('<div>', {
                'class': me.opts.templateClass
            });

            me._$imageWrapper = $('<div>', {
                'class': me.opts.imageWrapperClass
            }).appendTo(me._$template);

            me._$imageList = $('<ul>', {
                'class': me.opts.imageListClass
            }).appendTo(me._$imageWrapper);

            me._$thumbnailWrapper = $('<div>', {
                'class': me.opts.thumbnailWrapperClass
            }).appendTo(me._$template);

            me._$thumbnailList = $('<ul>', {
                'class': me.opts.thumbnailListClass
            }).appendTo(me._$thumbnailWrapper);
        },

        /**
         * Loops through all thumbnails and creates custom ones in the template.
         *
         * @public
         * @method initThumbnails
         */
        initThumbnails: function () {
            var me = this,
                opts = me.opts,
                $thumbnails = me.$el.find(opts.thumbnailSelector),
                imageUrl,
                thumbUrl,
                imgAlt,
                $el;

            $.each($thumbnails, function (index, el) {
                $el = $(el);

                imageUrl = $el.data('original-img');
                thumbUrl = $el.data('small-img');
                imgAlt = $el.data('title');

                me._thumbnails.push({
                    'imageUrl': imageUrl,
                    'thumbnailUrl': thumbUrl,
                    'alt': imgAlt
                });

                me._$thumbnailList.append($('<li>', {
                    'class': opts.thumbnailItemClass + ($el.hasClass(opts.thumbnailActiveClass) ? ' ' + opts.thumbnailActiveClass : ''),
                    'html': $('<a>', {
                        'class': opts.thumbnailLinkClass,
                        'href': imageUrl,
                        'html': $('<img>', {
                            'class': opts.thumbnailImageClass,
                            'src': thumbUrl,
                            'alt': imgAlt
                        })
                    })
                }));
            });

            me._$selectedThumbnails = $thumbnails;

            if ($thumbnails.length <= 1) {
                me._$template.addClass(opts.fullSizeImageClass);
            }
        },

        /**
         * Loops through all thumbnails and creates the image slides.
         *
         * @public
         * @method initSlides
         */
        initSlides: function () {
            var me = this,
                thumbnails = me._thumbnails,
                len = thumbnails.length,
                i = 0;

            for (; i < len; i++) {
                me._$imageList.append($('<li>', {
                    'html': $('<img>', {
                        'src': thumbnails[i].imageUrl,
                        'alt': thumbnails[i].alt
                    })
                }));
            }
        },

        /**
         * Registers all needed events.
         *
         * @public
         * @method registerEvents
         */
        registerEvents: function () {
            var me = this,
                clickEl = me.opts.clickEl,
                $el;

            if (typeof clickEl === 'string' && clickEl.length > 0) {
                $el = me.$el.find(clickEl);
            } else {
                $el = $(clickEl);
            }

            if (!$el) {
                return;
            }

            $el.css('cursor', 'pointer');

            me._on($el, 'click', $.proxy(me.onImageContainerClick, me));
        },

        /**
         * Will be called when the given clickEl was clicked on.
         * Opens the lightbox and prevents the click event.
         *
         * @public
         * @method onImageContainerClick
         * @param event
         */
        onImageContainerClick: function (event) {
            var me = this;

            event.preventDefault();

            me.open();
        },

        /**
         * Will be called when a thumbnail was clicked.
         * Calls the slideTo() function of the image scroller.
         *
         * @public
         * @method onThumbnailClick
         * @param {jQuery} $el
         * @param {Number} index
         * @param {jQuery.Event} event
         */
        onThumbnailClick: function ($el, index, event) {
            var me = this;

            me.imageScroller.slideTo(index);
        },

        /**
         * Checks which thumbnail is active and opens the lightbox with the template.
         *
         * @public
         * @method open
         */
        open: function () {
            var me = this,
                opts = me.opts,
                index = 0,
                $el;

            $.each(me._$selectedThumbnails, function (i, el) {
                $el = $(el);

                if ($el.hasClass(opts.thumbnailActiveClass)) {
                    index = i;
                    return false;
                }
                return true;
            });

            me.imageScroller.setActive(true);

            me.imageScroller.slideTo(index);

            $.modal.open(me._$template, {
                'width': opts.lightboxWidth,
                'height': opts.lightboxHeight,
                'onClose': $.proxy(me.onClose, me)
            });

            me.menuScroller.updateResize();

            if (me._refreshListeners) {
                me.imageScroller.refreshListeners();
                me.menuScroller.refreshListeners();

                me._refreshListeners = false;
            }
        },

        /**
         * Will be called when the image scroller slides.
         * Toggles the thumbnail active class.
         *
         * @public
         * @method onSlide
         * @param {jQuery} $img
         * @param {Number} index
         */
        onSlide: function ($img, index) {
            var me = this,
                $thumbnails = me._$thumbnailList.children();

            $.each($thumbnails, function (i, el) {
                $(el).toggleClass(me.opts.thumbnailActiveClass, i === index);
            });
        },

        /**
         * Will be called when the modal box is closing.
         * Deactivates the image scroller.
         *
         * @public
         * @method onClose
         */
        onClose: function () {
            var me = this;

            me.imageScroller.setActive(false);

            me._refreshListeners = true;
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
            me.menuScroller.destroy();

            me._$template.remove();

            me._destroy();
        }
    });
}(jQuery, window));