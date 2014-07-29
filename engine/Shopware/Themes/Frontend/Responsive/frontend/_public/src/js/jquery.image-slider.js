;(function($) {
    'use strict';

    /**
     * Image Slider Plugin.
     *
     * This plugin provides the functionality for an advanced responsive image slider.
     * It has support for thumbnails, arrow controls, touch controls, automatic sliding and a lightbox view.
     *
     * Example DOM Structure:
     *
     * <div class="image-slider" data-image-slider="true">
     *      <div class="image-slider--container">
     *          <div class="image-slider--slide">
     *              <div class="image-slider--item"></div>
     *              <div class="image-slider--item"></div>
     *              <div class="image-slider--item"></div>
     *          </div>
     *      </div>
     *      <div class="image-slider--thumbnails">
     *          <div class="image-slider--thumbnails-slide">
     *              <a class="thumbnail--link"></a>
     *              <a class="thumbnail--link"></a>
     *              <a class="thumbnail--link"></a>
     *          </div>
     *      </div>
     * </div>
     */
    $.plugin('imageSlider', {

        defaults: {
            // Set the speed of the slide animation in ms.
            animationSpeed: 300,

            // Turn thumbnail support on and off.
            thumbnails: true,

            // Turn arrow controls on and off.
            arrowControls: true,

            // Turn touch controls on and off.
            touchControls: true,

            // Turn the lightbox on and off.
            lightbox: true,

            // Turn automatic sliding on and off.
            autoSlide: false,

            // Set the speed for the automatic sliding in ms.
            autoSlideInterval: 5000,

            // The selector for the container element holding the actual image slider.
            imageContainerSelector: '.image-slider--container',

            // The selector for the slide element which slides inside the image container.
            imageSlideSelector: '.image-slider--slide',

            // The selector fot the container element holding the thumbnails.
            thumbnailContainerSelector: '.image-slider--thumbnails',

            // The selector for the slide element which slides inside the thumbnail container.
            thumbnailSlideSelector: '.image-slider--thumbnails-slide',

            // The css class for the left slider arrow.
            leftArrowCls: 'arrow is--left',

            // The css class for the right slider arrow.
            rightArrowCls: 'arrow is--right',

            // The css class for a top positioned thumbnail arrow.
            thumbnailArrowTopCls: 'is--top',

            // The css class for a left positioned thumbnail arrow.
            thumbnailArrowLeftCls: 'is--left',

            // The css class for a right positioned thumbnail arrow.
            thumbnailArrowRightCls: 'is--right',

            // The css class for a bottom positioned thumbnail arrow.
            thumbnailArrowBottomCls: 'is--bottom',

            // The css class for active states of the arrows.
            activeStateClass: 'is--active'
        },

        /**
         * Initializes the plugin.
         */
        init: function () {
            var me = this;

            me.applyDataAttributes();

            me.$slideContainer = me.$el.find(me.opts.imageContainerSelector);
            me.$slide = me.$slideContainer.find(me.opts.imageSlideSelector);

            if (me.opts.thumbnails) {
                me.$thumbnailContainer = me.$el.find(me.opts.thumbnailContainerSelector);
                me.$thumbnailSlide = me.$thumbnailContainer.find(me.opts.thumbnailSlideSelector);
                me.thumbnailSlideIndex = 0;
                me.getThumbnailOrientation();
                me.createThumbnailArrows();
            }

            me.trackItems();
            me.setSizes();

            if (me.opts.arrowControls) {
                me.createArrows();
            }

            me.slideIndex = 0;
            me.slideInterval = false;
            me.activeImage = false;

            me.registerEvents();
        },

        /**
         * Registers all necessary event listeners.
         */
        registerEvents: function() {
            var me = this;

            $(window).on('resize.imageSlider', function() {
                me.setSizes();
                me.setPosition(me.slideIndex);
            });

            if (me.opts.lightbox) {
                me.$images.each(function(index, el) {
                    me._on($(el), 'click', $.proxy(me.onSliderClick, me));
                });
            }

            if (me.opts.touchControls) {
                me._on(me.$slideContainer, 'swipeleft', $.proxy(me.slideNext, me));
                me._on(me.$slideContainer, 'swiperight', $.proxy(me.slidePrev, me));

                // Touch scrolling fix
                me._on(me.$el, 'movestart', function(e) {
                    if ((e.distX > e.distY && e.distX < -e.distY) ||
                        (e.distX < e.distY && e.distX > -e.distY)) {
                        e.preventDefault();
                    }
                });
            }

            if (me.opts.arrowControls) {
                me._on(me.$arrowLeft, 'click', $.proxy(me.onLeftArrowClick, me));
                me._on(me.$arrowRight, 'click', $.proxy(me.onRightArrowClick, me));
            }

            if (me.opts.thumbnails) {
                me.$thumbnails.each(function(index, el) {
                    me._on($(el), 'click', function(event) {
                        event.preventDefault();
                        me.slide(index);
                    });
                });

                me._on(me.$thumbnailArrowNext, 'click', $.proxy(me.slideThumbnailsNext, me));
                me._on(me.$thumbnailArrowPrev, 'click', $.proxy(me.slideThumbnailsPrev, me));

                if (me.opts.touchControls) {
                    me._on(me.$thumbnailContainer, 'swipeleft', $.proxy(me.slideThumbnailsNext, me));
                    me._on(me.$thumbnailContainer, 'swiperight', $.proxy(me.slideThumbnailsPrev, me));
                }
            }

            if (me.opts.autoSlide) {
                me.startAutoSlide();

                me._on(me.$el, 'mouseenter', $.proxy(me.stopAutoSlide, me));
                me._on(me.$el, 'mouseleave', $.proxy(me.startAutoSlide, me));
            }
        },

        /**
         * Creates the arrow controls for
         * the image slider.
         */
        createArrows: function() {
            var me = this;

            me.$arrowLeft = $('<a>', {
                'class': me.opts.leftArrowCls
            }).appendTo(me.$slideContainer);

            me.$arrowRight = $('<a>', {
                'class': me.opts.rightArrowCls
            }).appendTo(me.$slideContainer);
        },

        /**
         * Creates the thumbnail arrow controls
         * for the thumbnail slider.
         */
        createThumbnailArrows: function() {
            var me = this,
                prevClass = (me.thumbnailOrientation == 'horizontal') ? me.opts.thumbnailArrowLeftCls : me.opts.thumbnailArrowTopCls,
                nextClass = (me.thumbnailOrientation == 'horizontal') ? me.opts.thumbnailArrowRightCls : me.opts.thumbnailArrowBottomCls;

            me.$thumbnailArrowPrev = $('<a>', {
                'class': 'thumbnails--arrow ' + prevClass
            }).appendTo(me.$thumbnailContainer);

            me.$thumbnailArrowNext = $('<a>', {
                'class': 'thumbnails--arrow ' + nextClass
            }).appendTo(me.$thumbnailContainer);
        },

        /**
         * Tracks and counts the image elements
         * and the thumbnail elements.
         */
        trackItems: function() {
            var me = this;

            me.$items = me.$slide.find('.image-slider--item');
            me.$images = me.$slide.find('.image--element');

            if (me.opts.thumbnails) {
                me.$thumbnails = me.$thumbnailContainer.find('.thumbnail--link');
                me.thumbnailCount = me.$thumbnails.length;
            }

            me.itemCount = me.$items.length;

            if (me.itemCount <= 1) {
                me.opts.arrowControls = false;
            }
        },

        /**
         * Sets the correct sizes for the slide elements.
         */
        setSizes: function() {
            var me = this;

            me.itemsWidth = me.$slideContainer.innerWidth();
            me.slideWidth = me.itemsWidth * me.itemCount;

            me.$slide.css('width', me.slideWidth);
            me.$items.css('width', me.itemsWidth);

            if (me.opts.thumbnails) {
                me.setThumbnailSizes();
            }
        },

        /**
         * Sets the position of the image slide
         * to the given image index.
         *
         * @param index
         */
        setPosition: function(index) {
            var me = this,
                i = index || me.slideIndex;

            me.$slide.css('left', -(i * me.itemsWidth));
        },

        /**
         * Sets and returns the orientation of
         * the thumbnail container.
         *
         * @returns {string}
         */
        getThumbnailOrientation: function() {
            var me = this,
                containerWidth = me.$thumbnailContainer.innerWidth(),
                containerHeight = me.$thumbnailContainer.innerHeight();

            if (containerHeight > containerWidth) {
                me.thumbnailOrientation = 'vertical';
            } else {
                me.thumbnailOrientation = 'horizontal';
            }

            return me.thumbnailOrientation;
        },

        /**
         * Sets the correct sizes of the thumbnails and
         * the container based on the orientation.
         */
        setThumbnailSizes: function() {
            var me = this,
                containerWidth = me.$thumbnailContainer.innerWidth(),
                containerHeight = me.$thumbnailContainer.innerHeight(),
                thumbnailWidth = me.$thumbnails.outerWidth(),
                thumbnailHeight = me.$thumbnails.outerHeight(),
                thumbnailCount = me.$thumbnails.length,
                slideWidth = thumbnailCount * (thumbnailWidth + 10),
                slideHeight = thumbnailCount * (thumbnailHeight + 10),
                orientation = me.thumbnailOrientation;

            me.thumbnailSize = thumbnailWidth + 10;

            if (orientation === 'vertical') {
                me.thumbnailControls = (slideHeight > containerHeight);
                me.maxViewable = Math.floor(containerHeight / me.thumbnailSize);
                me.$thumbnailSlide.css({
                    'width': containerWidth,
                    'height': slideHeight,
                    'left': 0
                });
            } else {
                me.thumbnailControls = (slideWidth > containerWidth);
                me.maxViewable = Math.floor(containerWidth / me.thumbnailSize);
                me.$thumbnailSlide.css({
                    'width': slideWidth,
                    'height': containerHeight,
                    'top': 0
                });
            }

            me.trackThumbnailControls();
        },

        /**
         * Sets the active state for the thumbnail
         * at the given index position.
         *
         * @param index
         */
        setActiveThumbnail: function(index) {
            var me = this,
                activeThumbnail = me.$thumbnails.eq(index),
                pageLast = me.thumbnailSlideIndex + me.maxViewable - 1;

            me.$thumbnails.removeClass(me.opts.activeStateClass);
            activeThumbnail.addClass(me.opts.activeStateClass);

            if (index > pageLast || index < me.thumbnailSlideIndex) {
                me.slideThumbnails(index);
            }
        },

        /**
         * Checks which thumbnail arrow controls have to be shown.
         */
        trackThumbnailControls: function() {
            var me = this;

            if (!me.thumbnailControls) {
                return;
            }

            var leftPosition = me.$thumbnailSlide.position().left,
                topPosition = me.$thumbnailSlide.position().top,
                slideWidth = me.$thumbnailSlide.innerWidth(),
                slideHeight = me.$thumbnailSlide.innerHeight(),
                containerWidth = me.$thumbnailContainer.innerWidth(),
                containerHeight = me.$thumbnailContainer.innerHeight(),
                orientation = me.thumbnailOrientation;

            if (orientation == 'vertical') {
                me.$thumbnailArrowNext.toggleClass(me.opts.activeStateClass, slideHeight + topPosition > containerHeight);
                me.$thumbnailArrowPrev.toggleClass(me.opts.activeStateClass, topPosition < 0);
            } else {
                me.$thumbnailArrowNext.toggleClass(me.opts.activeStateClass, slideWidth + leftPosition > containerWidth);
                me.$thumbnailArrowPrev.toggleClass(me.opts.activeStateClass, leftPosition < 0);
            }
        },

        /**
         * Starts the auto slide interval.
         */
        startAutoSlide: function() {
            var me = this;

            me.slideInterval = window.setInterval(function(){
                me.slideNext();
            }, me.opts.autoSlideInterval);
        },

        /**
         * Stops the auto slide interval.
         */
        stopAutoSlide: function() {
            var me = this;

            window.clearInterval(me.slideInterval);
        },

        /**
         * Slides the thumbnail slider one position forward.
         */
        slideThumbnailsNext: function() {
            var me = this,
                newIndex = me.thumbnailSlideIndex + 1;

            if (newIndex < me.thumbnailCount - me.maxViewable + 1) {
                me.slideThumbnails(newIndex);
            }
        },

        /**
         * Slides the thumbnail slider one position backwards.
         */
        slideThumbnailsPrev: function() {
            var me = this,
                newIndex = me.thumbnailSlideIndex - 1;

            if (newIndex >= 0) {
                me.slideThumbnails(newIndex);
            }
        },

        /**
         * Slides the thumbnail slider to the given index position.
         *
         * @param index
         */
        slideThumbnails: function(index) {
            var me = this,
                orientation = me.thumbnailOrientation,
                newPosition = -(index * me.thumbnailSize),
                param = (orientation == 'vertical') ? { 'top': newPosition } : { 'left': newPosition },
                method = (Modernizr.csstransitions) ? 'transition' : 'animate';

            me.thumbnailSlideIndex = index;

            me.$thumbnailSlide[method](param, me.opts.animationSpeed, 'ease', function() {
                me.trackThumbnailControls();
            });
        },

        /**
         * Slides the image slider to the given index position.
         *
         * @param index
         * @param callback
         */
        slide: function(index, callback) {
            var me = this,
                newPosition = -(index * me.itemsWidth),
                method = (Modernizr.csstransitions) ? 'transition' : 'animate';

            me.slideIndex = index;

            if (me.opts.thumbnails) me.setActiveThumbnail(index);

            me.$slide[method]({ 'left': newPosition }, me.opts.animationSpeed, 'ease', $.proxy(callback, me));

            me.trackThumbnailControls();
        },

        /**
         * Slides the image slider one position forward.
         */
        slideNext: function() {
            var me = this,
                newIndex = me.slideIndex + 1;

            if (newIndex == me.itemCount) {
                newIndex = 0;
            }

            me.slide(newIndex);

            $.publish('plugin/imageSlider/slideNext');
        },

        /**
         * Slides the image slider one position backwards.
         */
        slidePrev: function() {
            var me = this,
                newIndex = me.slideIndex - 1;

            if (newIndex < 0) {
                newIndex = me.itemCount - 1;
            }

            me.slide(newIndex);

            $.publish('plugin/imageSlider/slidePrev');
        },

        /**
         * Is triggered when the left arrow
         * of the image slider is clicked.
         */
        onLeftArrowClick: function() {
            var me = this;

            me.slidePrev();
            $.publish('plugin/imageSlider/onLeftArrowClick');
        },

        /**
         * Is triggered when the right arrow
         * of the image slider is clicked.
         */
        onRightArrowClick: function() {
            var me = this;

            me.slideNext();
            $.publish('plugin/imageSlider/onRightArrowClick');
        },

        /**
         * Is triggered when the user clicks on the image slider.
         */
        onSliderClick: function() {
            var me = this;

            me.openLightBox();
            $.publish('plugin/imageSlider/onClick');
        },

        /**
         * Opens the original size of the
         * current image in a lightbox view.
         */
        openLightBox: function() {
            var me = this,
                image = me.$images.eq(me.slideIndex).attr('data-img-original');

            $.publish('plugin/imageSlider/onLightbox');

            $.lightbox.open(image);
        },

        /**
         * Destroys the plugin and removes
         * all elements created by the plugin.
         */
        destroy: function() {
            var me = this;

            $(window).off('resize.imageSlider');

            if (me.opts.arrowControls) {
                me.$arrowLeft.remove();
                me.$arrowRight.remove();
            }

            if (me.thumbnailControls) {
                me.$thumbnailArrowPrev.remove();
                me.$thumbnailArrowNext.remove();
            }

            if (me.opts.autoSlide) {
                me.stopAutoSlide();
            }

            me._destroy();
        }

    });
})(jQuery, Modernizr, window);
