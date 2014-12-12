;(function ($, Modernizr, window, Math) {
    'use strict';

    /**
     * Image Slider Plugin.
     *
     * This plugin provides the functionality for an advanced responsive image slider.
     * It has support for thumbnails, arrow controls, touch controls and automatic sliding.
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

            /**
             * Set the speed of the slide animation in ms.
             *
             * @property animationSpeed
             * @type {Number}
             */
            animationSpeed: 350,

            /**
             * Turn thumbnail support on and off.
             *
             * @property thumbnails
             * @type {Boolean}
             */
            thumbnails: true,

            /**
             * Turn support for a small dot navigation on and off.
             *
             * @property dotNavigation
             * @type {Boolean}
             */
            dotNavigation: true,

            /**
             * Turn arrow controls on and off.
             *
             * @property arrowControls
             * @type {Boolean}
             */
            arrowControls: true,

            /**
             * Turn touch controls on and off.
             *
             * @property touchControls
             * @type {Boolean}
             */
            touchControls: true,

            /**
             * Whether or not the automatic slide feature should be active.
             *
             * @property autoSlide
             * @type {Boolean}
             */
            autoSlide: false,

            /**
             * Whether or not the pinch to zoom feature should be active.
             *
             * @property pinchToZoom
             * @type {Boolean}
             */
            pinchToZoom: true,

            /**
             * Whether or not the swipe to slide feature should be active.
             *
             * @property swipeToSlide
             * @type {Boolean}
             */
            swipeToSlide: true,

            /**
             * Whether or not the double tap/click should be used to zoom in/out..
             *
             * @property doubleTap
             * @type {Boolean}
             */
            doubleTap: true,

            /**
             * The minimal zoom factor an image can have.
             *
             * @property minZoom
             * @type {Number}
             */
            minZoom: 1,

            /**
             * The maximal zoom factor an image can have.
             * Can either be a number or 'auto'.
             *
             * If set to 'auto', you can only zoom to the original image size.
             *
             * @property maxZoom
             * @type {Number|String}
             */
            maxZoom: 'auto',

            /**
             * The distance you have to travel to recognize a swipe in pixels.
             *
             * @property swipeTolerance
             * @type {Number}
             */
            swipeTolerance: 100,

            /**
             * The image index that will be set when the plugin gets initialized.
             *
             * @property startIndex
             * @type {Number}
             */
            startIndex: 0,

            /**
             * Set the speed for the automatic sliding in ms.
             *
             * @property autoSlideInterval
             * @type {Number}
             */
            autoSlideInterval: 5000,

            /**
             * The selector for the container element holding the actual image slider.
             *
             * @property imageContainerSelector
             * @type {String}
             */
            imageContainerSelector: '.image-slider--container',

            /**
             * The selector for the slide element which slides inside the image container.
             *
             * @property imageSlideSelector
             * @type {String}
             */
            imageSlideSelector: '.image-slider--slide',

            /**
             * The selector fot the container element holding the thumbnails.
             *
             * @property thumbnailContainerSelector
             * @type {String}
             */
            thumbnailContainerSelector: '.image-slider--thumbnails',

            /**
             * The selector for the slide element which slides inside the thumbnail container.
             *
             * @property thumbnailSlideSelector
             * @type {String}
             */
            thumbnailSlideSelector: '.image-slider--thumbnails-slide',

            /**
             * The selector for the dot navigation container.
             *
             * @property dotNavSelector
             * @type {String}
             */
            dotNavSelector: '.image-slider--dots',

            /**
             * The selector for each dot link in the dot navigation.
             *
             * @property dotLinkSelector
             * @type {String}
             */
            dotLinkSelector: '.dot--link',

            /**
             * The css class for the left slider arrow.
             *
             * @property leftArrowCls
             * @type {String}
             */
            leftArrowCls: 'arrow is--left',

            /**
             * The css class for the right slider arrow.
             *
             * @property rightArrowCls
             * @type {String}
             */
            rightArrowCls: 'arrow is--right',

            /**
             * The css class for a top positioned thumbnail arrow.
             *
             * @property thumbnailArrowTopCls
             * @type {String}
             */
            thumbnailArrowTopCls: 'is--top',

            /**
             * The css class for a left positioned thumbnail arrow.
             *
             * @property thumbnailArrowLeftCls
             * @type {String}
             */
            thumbnailArrowLeftCls: 'is--left',

            /**
             * The css class for a right positioned thumbnail arrow.
             *
             * @property thumbnailArrowRightCls
             * @type {String}
             */
            thumbnailArrowRightCls: 'is--right',

            /**
             * The css class for a bottom positioned thumbnail arrow.
             *
             * @property thumbnailArrowBottomCls
             * @type {String}
             */
            thumbnailArrowBottomCls: 'is--bottom',

            /**
             * The css class for active states of the arrows.
             *
             * @property activeStateClass
             * @type {String}
             */
            activeStateClass: 'is--active',

            /**
             * Class that will be appended to the image container
             * when the user is grabbing an image
             *
             * @property grabClass
             * @type {String}
             */
            dragClass: 'is--dragging',

            /**
             * Class that will be appended to the thumbnail container
             * when no other thumbnails are available
             *
             * @property noThumbClass
             * @type {String}
             */
            noThumbClass: 'no--thumbnails'
        },

        /**
         * Initializes the plugin.
         */
        init: function () {
            var me = this,
                opts = me.opts;

            me.applyDataAttributes();

            me.$slideContainer = me.$el.find(opts.imageContainerSelector);
            me.$slide = me.$slideContainer.find(opts.imageSlideSelector);

            me.slideIndex = opts.startIndex;
            me.slideInterval = false;
            me.$currentImage = null;

            opts.maxZoom = parseFloat(opts.maxZoom) || 'auto';

            if (opts.thumbnails) {
                me.$thumbnailContainer = me.$el.find(opts.thumbnailContainerSelector);
                me.$thumbnailSlide = me.$thumbnailContainer.find(opts.thumbnailSlideSelector);
                me.thumbnailOrientation = me.getThumbnailOrientation();
                me.thumbnailOffset = 0;
                me.createThumbnailArrows();
            }

            if (opts.dotNavigation) {
                me.$dotNav = me.$el.find(opts.dotNavSelector);
                me.$dots = me.$dotNav.find(opts.dotLinkSelector);
            }

            me.trackItems();

            if (opts.thumbnails) {
                me.trackThumbnailControls();
                me.setActiveThumbnail(me.slideIndex);
            }

            me.setIndex(me.slideIndex);

            if (opts.arrowControls) {
                me.createArrows();
            }

            /**
             * Whether or not the user is grabbing the image with the mouse
             *
             * @private
             * @property grabImage
             * @type {Boolean}
             */
            me.grabImage = false;

            /**
             * First touch point position from touchstart event.
             * Will be used to determine the swiping gesture.
             *
             * @private
             * @property startTouchPoint
             * @type {Vector}
             */
            me.startTouchPoint = new Vector(0, 0);

            /**
             * @private
             * @property imageTranslation
             * @type {Vector}
             */
            me.imageTranslation = new Vector(0, 0);

            /**
             * @private
             * @property imageScale
             * @type {Number}
             */
            me.imageScale = 1;

            /**
             * @private
             * @property touchDistance
             * @type {Number}
             */
            me.touchDistance = 0;

            /**
             * @private
             * @property lastTouchTime
             * @type {Number}
             */
            me.lastTouchTime = 0;

            me.registerEvents();
        },

        /**
         * Registers all necessary event listeners.
         */
        registerEvents: function () {
            var me = this;

            if (me.opts.touchControls) {
                me._on(me.$slide, 'touchstart mousedown MSPointerDown', me.onTouchStart.bind(me));
                me._on(me.$slide, 'touchmove mousemove MSPointerMove', me.onTouchMove.bind(me));
                me._on(me.$slide, 'touchend mouseup MSPointerUp', me.onTouchEnd.bind(me));
                me._on(me.$slide, 'mouseleave', me.onMouseLeave.bind(me));

                if (me.opts.pinchToZoom) {
                    me._on(me.$slide, 'mousewheel', me.onScroll.bind(me));
                }

                me._on(me.$slide, 'dblclick', me.onDoubleClick.bind(me));
            }

            if (me.opts.arrowControls) {
                me._on(me.$arrowLeft, 'click touchstart', $.proxy(me.onLeftArrowClick, me));
                me._on(me.$arrowRight, 'click touchstart', $.proxy(me.onRightArrowClick, me));
            }

            if (me.opts.thumbnails) {
                me.$thumbnails.each($.proxy(me.applyClickEventHandler, me));

                me._on(me.$thumbnailArrowNext, 'click', $.proxy(me.slideThumbnailsNext, me));
                me._on(me.$thumbnailArrowPrev, 'click', $.proxy(me.slideThumbnailsPrev, me));

                if (me.opts.touchControls) {
                    me._on(me.$thumbnailSlide, 'touchstart', $.proxy(me.onThumbnailSlideTouch, me));
                    me._on(me.$thumbnailSlide, 'touchmove', $.proxy(me.onThumbnailSlideMove, me));
                }

                StateManager.on('resize', me.onResize, me);
            }

            if (me.opts.dotNavigation && me.$dots) {
                me.$dots.each($.proxy(me.applyClickEventHandler, me));
            }

            if (me.opts.autoSlide) {
                me.startAutoSlide();

                me._on(me.$el, 'mouseenter', $.proxy(me.stopAutoSlide, me));
                me._on(me.$el, 'mouseleave', $.proxy(me.startAutoSlide, me));
            }
        },

        /**
         * Will be called when the user starts touching the image slider
         *
         * @param {jQuery.Event} event
         */
        onTouchStart: function (event) {
            var me = this,
                pointers = me.getPointers(event),
                pointerA = pointers[0],
                currTime = Date.now(),
                startPoint = me.startTouchPoint,
                startX = startPoint.x,
                startY = startPoint.y,
                distance,
                deltaX,
                deltaY;

            startPoint.set(pointerA.clientX, pointerA.clientY);

            if (pointers.length === 1) {
                if (event.originalEvent instanceof MouseEvent) {
                    event.preventDefault();

                    me.grabImage = true;
                    me.$slideContainer.addClass(me.opts.dragClass);
                    return;
                }

                if (!me.opts.doubleTap) {
                    return;
                }

                deltaX = Math.abs(pointerA.clientX - startX);
                deltaY = Math.abs(pointerA.clientY - startY);

                distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);

                if (currTime - me.lastTouchTime < 500 && distance < 30) {
                    me.onDoubleClick(event);
                }

                me.lastTouchTime = currTime;
            } else {
                event.preventDefault();
            }
        },

        /**
         * Will be called when the user is moving the finger while touching
         * the image slider
         *
         * @param {jQuery.Event} event
         */
        onTouchMove: function (event) {
            var me = this,
                touches = me.getPointers(event),
                touchA = touches[0],
                touchB = touches[1],
                scale = me.imageScale,
                distance,
                deltaX,
                deltaY;

            if (touches.length > 2) {
                return;
            }

            if (touches.length === 1 && scale > 1) {
                // If the image is zoomed, move it
                if (event.originalEvent instanceof MouseEvent && !me.grabImage) {
                    return;
                }

                deltaX = touchA.clientX - me.startTouchPoint.x;
                deltaY = touchA.clientY - me.startTouchPoint.y;

                me.startTouchPoint.set(touchA.clientX, touchA.clientY);

                me.translate(deltaX / scale, deltaY / scale);

                event.preventDefault();
                return;
            }

            if (!me.opts.pinchToZoom || !touchB) {
                return;
            }

            deltaX = Math.abs(touchA.clientX - touchB.clientX);
            deltaY = Math.abs(touchA.clientY - touchB.clientY);

            distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);

            if (me.touchDistance === 0) {
                me.touchDistance = distance;
                return;
            }

            me.scale((distance - me.touchDistance) / 100);

            me.touchDistance = distance;
        },

        /**
         * Will be called when the user ends touching the image slider
         *
         * @param {jQuery.Event} event
         */
        onTouchEnd: function (event) {
            var me = this,
                touches = event.changedTouches,
                remaining = event.originalEvent.touches,
                touchA = (touches && touches[0]) || event.originalEvent,
                touchB = remaining && remaining[0],
                swipeTolerance = me.opts.swipeTolerance,
                deltaX,
                deltaY;

            me.touchDistance = 0;
            me.grabImage = false;
            me.$slideContainer.removeClass(me.opts.dragClass);

            if (touchB) {
                me.startTouchPoint.set(touchB.clientX, touchB.clientY);
                return;
            }

            deltaX = me.startTouchPoint.x - touchA.clientX;
            deltaY = me.startTouchPoint.y - touchA.clientY;

            if (Math.abs(deltaX) < swipeTolerance || Math.abs(deltaY) > swipeTolerance) {
                return;
            }

            event.preventDefault();

            if (deltaX < 0) {
                me.slidePrev();
                return;
            }

            me.slideNext();
        },

        /**
         * Will be called when the user scrolls the image by the mouse
         *
         * @param {jQuery.Event} event
         */
        onScroll: function (event) {
            var me = this;

            if (event.originalEvent.deltaY < 0) {
                me.scale(0.25);
            } else {
                me.scale(-0.25);
            }

            event.preventDefault();
        },

        /**
         * Will be called when the user double clicks
         * or double taps the image slider
         *
         * @param {jQuery.Event} event
         */
        onDoubleClick: function (event) {
            var me = this;

            if (!me.opts.doubleTap) {
                return;
            }

            if (me.imageScale <= 1) {
                me.scale(1, true);
            } else {
                me.setScale(1, true);
            }

            event.preventDefault();
        },

        /**
         * Will be called when the user leaves the image slide with the mouse
         */
        onMouseLeave: function () {
            var me = this;

            me.touchDistance = 0;
            me.grabImage = false;
            me.$slideContainer.removeClass(me.opts.dragClass);
        },

        /**
         * Will be called when the viewport is resized
         */
        onResize: function () {
            if (this.opts.thumbnails) {
                this.trackThumbnailControls();
            }
        },

        /**
         * Will be called when the user starts touching the thumbnails slider
         *
         * @param {jQuery.Event} event
         */
        onThumbnailSlideTouch: function (event) {
            var me = this,
                pointers = me.getPointers(event),
                pointerA = pointers[0];

            me.startTouchPoint.set(pointerA.clientX, pointerA.clientY);
        },

        /**
         * Will be called when the user is moving the finger while touching
         * the thumbnail slider
         *
         * @param {jQuery.Event} event
         */
        onThumbnailSlideMove: function (event) {
            event.preventDefault();

            var me = this,
                pointers = me.getPointers(event),
                pointerA = pointers[0],
                startPoint = me.startTouchPoint,
                isHorizontal = me.thumbnailOrientation === 'horizontal',
                posA = isHorizontal ? pointerA.clientX : pointerA.clientY,
                posB = isHorizontal ? startPoint.x : startPoint.y,
                delta = posA - posB;

            startPoint.set(pointerA.clientX, pointerA.clientY);

            me.setThumbnailSlidePosition(me.thumbnailOffset + delta, false);

            me.trackThumbnailControls();
        },

        /**
         * Returns either an array of touches or a single mouse event
         * This is a helper function to unify the touch/mouse gesture logic
         *
         * @param {jQuery.Event} event
         */
        getPointers: function (event) {
            var origEvent = event.originalEvent || event;

            return origEvent.touches || [origEvent];
        },

        /**
         * Calculates the new x/y coordinates for the image based by the
         * given scale value.
         *
         * @param {Number} x
         * @param {Number} y
         * @param {Number} scale
         */
        getTransformedPosition: function (x, y, scale) {
            var me = this,
                image = me.$currentImage,
                width = image.width(),
                height = image.height(),
                scaledWidth = width * scale,
                scaledHeight = height * scale,
                minX = (scaledWidth - width) / scale / 2,
                minY = (scaledHeight - height) / scale / 2;

            return new Vector(
                Math.max(minX * -1, Math.min(minX, x)),
                Math.max(minY * -1, Math.min(minY, y))
            );
        },

        /**
         * Sets the tranlation (position) of the current image.
         *
         * @param {Number} x
         * @param {Number} y
         */
        setTranslation: function (x, y) {
            var me = this,
                newPos = me.getTransformedPosition(x, y, me.imageScale);

            me.imageTranslation.set(newPos.x, newPos.y);

            me.updateTransform();
        },

        /**
         * Translates the current image relative to the current position.
         * The x/y values will be added together.
         *
         * @param {Number} x
         * @param {Number} y
         */
        translate: function (x, y) {
            var me = this,
                translation = me.imageTranslation;

            me.setTranslation(translation.x + x, translation.y + y);
        },

        /**
         * Scales the current image to the given scale value.
         * You can also pass the option if it should be animated
         * and if so, you can also pass a callback.
         *
         * @param {Number|String} scale
         * @param {Boolean} animate
         * @param {Function} callback
         */
        setScale: function (scale, animate, callback) {
            var me = this,
                opts = me.opts,
                $currImage = me.$currentImage,
                img = $currImage[0],
                minZoom = opts.minZoom,
                maxZoom = opts.maxZoom;

            if (typeof maxZoom !== 'number') {
                maxZoom = Math.max(img.naturalWidth, img.naturalHeight) / Math.max($currImage.width(), $currImage.height());
            }

            me.imageScale = Math.max(minZoom, Math.min(maxZoom, scale));

            me.updateTransform(animate, callback);
        },

        /**
         * Scales the current image relative to the current scale value.
         * The factor value will be added to the current scale.
         *
         * @param {Number} factor
         * @param {Boolean} animate
         * @param {Function} callback
         */
        scale: function (factor, animate, callback) {
            this.setScale(this.imageScale + factor, animate, callback);
        },

        /**
         * Updates the transformation of the current image.
         * The scale and translation will be considered into this.
         * You can also decide if the update should be animated
         * and if so, you can provide a callback function
         *
         * @param {Boolean} animate
         * @param {Function} callback
         */
        updateTransform: function (animate, callback) {
            var me = this,
                translation = me.imageTranslation,
                scale = me.imageScale,
                newPosition = me.getTransformedPosition(translation.x, translation.y, scale);

            translation.set(newPosition.x, newPosition.y);

            if (!animate || !Modernizr.csstransitions) {
                me.$currentImage.css('transform', 'scale(' + scale + ') translate(' + translation.x + 'px, ' + translation.y + 'px)');

                if (callback) {
                    callback.call(me);
                }
                return;
            }

            me.$currentImage.transition({
                'scale': scale,
                'x': translation.x,
                'y': translation.y
            }, me.opts.animationSpeed, 'cubic-bezier(.2,.76,.5,1)', callback);
        },

        /**
         * Applies a click event handler to the element
         * to slide the slider to the index of that element.
         *
         * @param index
         * @param el
         */
        applyClickEventHandler: function (index, el) {
            var me = this,
                $el = $(el),
                i = index || $el.index();

            me._on($el, 'click', function (event) {
                event.preventDefault();
                me.slide(i);
            });
        },

        /**
         * Creates the arrow controls for
         * the image slider.
         */
        createArrows: function () {
            var me = this;

            me.$arrowLeft = $('<a>', {
                'class': me.opts.leftArrowCls + (me.slideIndex <= 0 ? ' is--hidden' : '')
            }).appendTo(me.$slideContainer);

            me.$arrowRight = $('<a>', {
                'class': me.opts.rightArrowCls + (me.slideIndex >= me.itemCount - 1 ? ' is--hidden' : '')
            }).appendTo(me.$slideContainer);
        },

        /**
         * Creates the thumbnail arrow controls
         * for the thumbnail slider.
         */
        createThumbnailArrows: function () {
            var me = this,
                isHorizontal = (me.thumbnailOrientation === 'horizontal'),
                prevClass = isHorizontal ? me.opts.thumbnailArrowLeftCls : me.opts.thumbnailArrowTopCls,
                nextClass = isHorizontal ? me.opts.thumbnailArrowRightCls : me.opts.thumbnailArrowBottomCls;

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
        trackItems: function () {
            var me = this;

            me.$items = me.$slide.find('.image-slider--item');
            me.$images = me.$slide.find('.image--element');

            if (me.opts.thumbnails) {
                me.$thumbnails = me.$thumbnailContainer.find('.thumbnail--link');
                me.thumbnailCount = me.$thumbnails.length;

                if (me.thumbnailCount === 0) {
                    me.$el.addClass(me.opts.noThumbClass);
                    me.opts.thumbnails = false;
                }
            }

            me.itemCount = me.$items.length;

            if (me.itemCount <= 1) {
                me.opts.arrowControls = false;
            }
        },

        /**
         * Sets the position of the image slide
         * to the given image index.
         *
         * @param index
         */
        setIndex: function (index) {
            var me = this,
                i = index || me.slideIndex;

            me.$slide.css('left', (i * 100 * -1) + '%');
            me.$currentImage = $(me.$images[index]);
        },

        /**
         * Returns the orientation of the thumbnail container.
         *
         * @returns {string}
         */
        getThumbnailOrientation: function () {
            var $container = this.$thumbnailContainer;

            return ($container.innerWidth() > $container.innerHeight()) ? 'horizontal' : 'vertical';
        },

        /**
         * Sets the active state for the thumbnail
         * at the given index position.
         *
         * @param index
         */
        setActiveThumbnail: function (index) {
            var me = this,
                isHorizontal = me.thumbnailOrientation === 'horizontal',
                orientation = isHorizontal ? 'left' : 'top',
                $thumbnail = me.$thumbnails.eq(index),
                $container = me.$thumbnailContainer,
                thumbnailPos = $thumbnail.position(),
                slidePos = me.$thumbnailSlide.position(),
                slideOffset = slidePos[orientation],
                posA = thumbnailPos[orientation] * -1,
                posB = thumbnailPos[orientation] + (isHorizontal ? $thumbnail.outerWidth() : $thumbnail.outerHeight()),
                containerSize = isHorizontal ? $container.width() : $container.height(),
                newPos;

            if (posA < slideOffset && posB * -1 < slideOffset + (containerSize * -1)) {
                newPos = containerSize - Math.max(posB, containerSize);
            } else {
                newPos = Math.max(posA, slideOffset);
            }

            me.$thumbnails.removeClass(me.opts.activeStateClass);
            $thumbnail.addClass(me.opts.activeStateClass);

            me.setThumbnailSlidePosition(newPos, true);
        },

        /**
         * Sets the active state for the dot
         * at the given index position.
         *
         * @param index
         */
        setActiveDot: function (index) {
            var me = this,
                i = index || me.slideIndex;

            if (me.opts.dotNavigation && me.$dots) {
                me.$dots.removeClass(me.opts.activeStateClass);
                me.$dots.eq(i).addClass(me.opts.activeStateClass);
            }
        },

        /**
         * Sets the position of the thumbnails slider
         * If the offset exceeds the minimum/maximum position, it will be culled
         *
         * @param {Number} offset
         * @param {Boolean} animate
         */
        setThumbnailSlidePosition: function (offset, animate) {
            var me = this,
                $slide = me.$thumbnailSlide,
                $container = me.$thumbnailContainer,
                isHorizontal = me.thumbnailOrientation === 'horizontal',
                sizeA = isHorizontal ? $container.innerWidth() : $container.innerHeight(),
                sizeB = isHorizontal ? $slide.outerWidth(true) : $slide.outerHeight(true),
                min = Math.min(0, sizeA - sizeB),
                css = {};

            me.thumbnailOffset = Math.max(min, Math.min(0, offset));

            css[isHorizontal ? 'left' : 'top'] = me.thumbnailOffset;

            if (!animate) {
                $slide.css(css);
                return;
            }

            $slide[Modernizr.csstransitions ? 'transition' : 'animate'](css, 400, me.trackThumbnailControls.bind(me));
        },

        /**
         * Checks which thumbnail arrow controls have to be shown.
         */
        trackThumbnailControls: function () {
            var me = this,
                $container = me.$thumbnailContainer,
                $slide = me.$thumbnailSlide,
                $prevArr = me.$thumbnailArrowPrev,
                $nextArr = me.$thumbnailArrowNext,
                activeCls = me.opts.activeStateClass,
                pos = $slide.position();

            if (me.thumbnailOrientation === 'horizontal') {
                $prevArr.toggleClass(activeCls, pos.left < 0);
                $nextArr.toggleClass(activeCls, ($slide.innerWidth() + pos.left) > $container.innerWidth());
                return;
            }

            $prevArr.toggleClass(activeCls, pos.top < 0);
            $nextArr.toggleClass(activeCls, ($slide.innerHeight() + pos.top) > $container.innerHeight());
        },

        /**
         * Starts the auto slide interval.
         */
        startAutoSlide: function () {
            var me = this;

            me.slideInterval = window.setInterval(function () {
                me.slideNext();
            }, me.opts.autoSlideInterval);
        },

        /**
         * Stops the auto slide interval.
         */
        stopAutoSlide: function () {
            window.clearInterval(this.slideInterval);
        },

        /**
         * Slides the thumbnail slider one position forward.
         */
        slideThumbnailsNext: function () {
            var me = this,
                $container = me.$thumbnailContainer,
                size = me.thumbnailOrientation === 'horizontal' ? $container.innerWidth() : $container.innerHeight();

            me.setThumbnailSlidePosition(me.thumbnailOffset - (size / 2), true);
        },

        /**
         * Slides the thumbnail slider one position backwards.
         */
        slideThumbnailsPrev: function () {
            var me = this,
                $container = me.$thumbnailContainer,
                size = me.thumbnailOrientation === 'horizontal' ? $container.innerWidth() : $container.innerHeight();

            me.setThumbnailSlidePosition(me.thumbnailOffset + (size / 2), true);
        },

        /**
         * Slides the image slider to the given index position.
         *
         * @param index
         * @param callback
         */
        slide: function (index, callback) {
            var me = this,
                newPosition = (index * 100 * -1) + '%',
                method = (Modernizr.csstransitions) ? 'transition' : 'animate';

            me.slideIndex = index;

            if (me.opts.thumbnails) {
                me.setActiveThumbnail(index);
                me.trackThumbnailControls();
            }

            if (me.opts.dotNavigation && me.$dots) {
                me.setActiveDot(index);
            }

            me.resetTransformation(true, function () {
                me.$slide[method]({
                    'left': newPosition,
                    'easing': 'cubic-bezier(.2,.89,.75,.99)'
                }, me.opts.animationSpeed, $.proxy(callback, me));
            });

            me.$currentImage = $(me.$images[index]);

            me.$arrowLeft.toggleClass('is--hidden', index <= 0);
            me.$arrowRight.toggleClass('is--hidden', index >= me.itemCount - 1);
        },

        resetTransformation: function (animate, callback) {
            var me = this,
                translation = me.imageTranslation;

            me.touchDistance = 0;

            if (me.imageScale !== 1 || translation.x !== 0 || translation.y !== 0) {

                me.imageScale = 1;

                me.imageTranslation.set(0, 0);

                me.updateTransform(animate, callback);

            } else if (callback) {
                callback.call(me);
            }
        },

        /**
         * Slides the image slider one position forward.
         */
        slideNext: function () {
            var me = this,
                newIndex = me.slideIndex + 1;

            if (newIndex >= me.itemCount) {
                return;
            }

            me.slide(newIndex);

            $.publish('plugin/imageSlider/slideNext');
        },

        /**
         * Slides the image slider one position backwards.
         */
        slidePrev: function () {
            var me = this,
                newIndex = me.slideIndex - 1;

            if (newIndex < 0) {
                return;
            }

            me.slide(newIndex);

            $.publish('plugin/imageSlider/slidePrev');
        },

        /**
         * Is triggered when the left arrow
         * of the image slider is clicked.
         */
        onLeftArrowClick: function (event) {
            event.preventDefault();

            this.slidePrev();

            $.publish('plugin/imageSlider/onLeftArrowClick');
        },

        /**
         * Is triggered when the right arrow
         * of the image slider is clicked.
         */
        onRightArrowClick: function (event) {
            event.preventDefault();

            this.slideNext();

            $.publish('plugin/imageSlider/onRightArrowClick');
        },

        /**
         * Destroys the plugin and removes
         * all elements created by the plugin.
         */
        destroy: function () {
            var me = this,
                opts = me.opts;

            if (opts.dotNavigation && me.$dots) me.setActiveDot(0);

            if (opts.arrowControls) {
                me.$arrowLeft.remove();
                me.$arrowRight.remove();
            }

            if (opts.thumbnails) {
                me.setActiveThumbnail(0);
                me.$thumbnailArrowPrev.remove();
                me.$thumbnailArrowNext.remove();

                StateManager.off('resize', me.onResize, me);
            }

            if (opts.autoSlide) me.stopAutoSlide();

            me.resetTransformation(false);

            me._destroy();
        }
    });

    function Vector(x, y) {
        var me = this;

        me.x = x || 0;
        me.y = y || 0;
    }

    Vector.prototype = {

        set: function (x, y) {
            var me = this;

            me.x = typeof x === 'number' ? x : me.x;
            me.y = typeof y === 'number' ? y : me.y;
        }
    };
})(jQuery, Modernizr, window, Math);