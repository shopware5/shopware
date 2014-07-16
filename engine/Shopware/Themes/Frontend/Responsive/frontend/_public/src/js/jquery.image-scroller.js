;(function ($, window, modernizr, math) {
    'use strict';

    var emptyFn = function () {};

    /**
     * Shopware Image Scroller Plugin
     *
     * @example
     *
     * HTML:
     *
     * <div class="container">
     *     <ul class="image--list">
     *         <li>
     *             <img src="">
     *         </li>
     *
     *         <li>
     *             <img src="">
     *         </li>
     *
     *         <!-- more images -->
     *     </ul>
     * </div>
     *
     * JS:
     *
     * $('.container').imageScroller();
     */
    $.plugin('imageScroller', {

        /**
         * Default options for the image scroller plugin.
         *
         * @public
         * @property defaults
         * @type {Object}
         */
        defaults: {
            /**
             * CSS selector for the image listing
             *
             * @type {String}
             */
            'listSelector': '*[class$="--list"]',

            /**
             * CSS class which will be added to the wrapper / this.$el
             *
             * @type {String}
             */
            'wrapperClass': 'js--image-scroller',

            /**
             * CSS class which will be added to the listing
             *
             * @type {String}
             */
            'listClass': 'js--image-scroller--list',

            /**
             * CSS class which will be added to every list item
             *
             * @type {String}
             */
            'itemClass': 'js--image-scroller--slide',

            /**
             * CSS class which will be added to every slide image
             *
             * @type {String}
             */
            'imageClass': 'slide--image',

            /**
             * CSS class(es) which will be set for the left arrow
             *
             * @type {String}
             */
            'leftArrowClass': 'control--left',

            /**
             * CSS class(es) which will be set for the right arrow
             *
             * @type {String}
             */
            'rightArrowClass': 'control--right',

            /**
             * Option whether or not it is possible to zoom with pinch gesture.
             *
             * @type {Boolean}
             */
            'pinchToZoom': true,

            /**
             * Option whether or not it is possible to slide with swipe gesture.
             *
             * @type {Boolean}
             */
            'swipeToSlide': true,

            /**
             * Amount of pixels that need to be swiped to slide.
             *
             * @type {Number}
             */
            'swipeTolerance': 200,

            /**
             * Option whether or not it is possible to slide with keyboard keys.
             *
             * @type {Boolean}
             */
            'keyboardNavigation': true,

            /**
             * The keyboard mapping for the slide navigation.
             * Every action name has an array of keyCodes it will listen to.
             *
             * @type {Object}
             */
            'keyboardMapping': {
                // Left and A key
                'left': [37, 65],

                // Right and D key
                'right': [39, 68]
            },

            /**
             * Number in ms or false to turn it off.
             * Slides the images automatically in the given time.
             *
             * @type {Number|Boolean}
             */
            'autoPlay': false,

            /**
             * Whether or not it should pause the auto play loop on mouse hover.
             *
             * @type {Boolean}
             */
            'pauseOnHover': true,

            /**
             * Whether or not the image scroller is currently active.
             * If set to false it will ignore all events and scrolling.
             *
             * @type {Boolean}
             */
            'active': true,

            /**
             * Function override which will be called when the plugin slides.
             *
             * @type {Function}
             * @param {jQuery} $currentImage - the current image wrapped in jQuery
             * @param {Number} index - the current image index.
             */
            'onSlide': emptyFn,

            /**
             * Function override which will be called when a slides was clicked.
             *
             * @type {Function}
             * @param {jQuery.Event} event - the current image index.
             */
            'onClick': emptyFn
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

            me.applyDataAttributes();

            /**
             * Current image scaling which will be applied to x and y axis.
             *
             * @private
             * @property _scale
             * @type {Number}
             */
            me._scale = 1;

            /**
             * Last changed size to calculate the scaling delta.
             *
             * @private
             * @property _previousScale
             * @type {Number}
             */
            me._previousScale = 1;

            /**
             * Current image translation (position).
             *
             * @private
             * @property _translation
             * @type {Object}
             */
            me._translation = {
                'x': 0,
                'y': 0
            };

            /**
             * First touch point position from touchstart event.
             * Will be used to determine the swiping gesture.
             *
             * @private
             * @property _startTouchPoint
             * @type {Object}
             */
            me._startTouchPoint = {
                'x': 0,
                'y': 0
            };

            /**
             * Last distance between two touch points in pixel.
             *
             * @private
             * @property _lastTouchDistance
             * @type {Number}
             */
            me._lastTouchDistance = 0;

            /**
             * Current slide index.
             *
             * @private
             * @property _index
             * @type {Number}
             */
            me._index = 0;

            /**
             * Current image element wrapped by jQuery
             *
             * @private
             * @property _$currentImage
             * @type {jQuery}
             */
            me._$currentImage = null;

            /**
             * Left arrow element wrapped in jQuery.
             *
             * @private
             * @property _$leftArrow
             * @type {jQuery}
             */
            me._$leftArrow = null;

            /**
             * Right arrow element wrapped in jQuery.
             *
             * @private
             * @property _$rightArrow
             * @type {jQuery}
             */
            me._$rightArrow = null;

            /**
             * Slides list wrapped in jQuery.
             *
             * @private
             * @property _$list
             * @type {jQuery}
             */
            me._$list = null;

            /**
             * Interval id from setInterval used for auto play.
             *
             * @private
             * @property _autoPlayInterval
             * @type {Number}
             */
            me._autoPlayInterval = 0;

            /**
             * Flag if the image scroller is currently active and can scroll.
             *
             * @private
             * @property _active
             * @type {Boolean}
             */
            me._active = !!me.opts.active;

            // Create the template.
            me.initTemplate();

            // Add all needed event listeners.
            me.registerEvents();

            if (me._active) {
                // If autoPlay is available, start the loop.
                me.play();
            }
        },

        /**
         * Creates all needed control items and adds plugin classes
         *
         * @public
         * @method _initTemplate
         */
        initTemplate: function () {
            var me = this,
                slideCount = 0,
                $el;

            me.$el.addClass(me.opts.wrapperClass);

            me._$list = me.$el.find(me.opts.listSelector);
            me._$list.addClass(me.opts.listClass);

            $.each(me._$list.children(), function (index, el) {
                $el = $(el);

                $el.addClass(me.opts.itemClass);

                $el.find('img').addClass(me.opts.imageClass);

                slideCount++;
            });

            me._$leftArrow = $('<div>', {
                'class': me.opts.leftArrowClass
            }).appendTo(me.$el).toggle(slideCount > 1);

            me._$rightArrow = $('<div>', {
                'class': me.opts.rightArrowClass
            }).appendTo(me.$el).toggle(slideCount > 1);
        },

        /**
         * Registers all needed events.
         *
         * @private
         * @method registerEvents
         */
        registerEvents: function () {
            var me = this;

            me.refreshListeners();

            me._on(window, 'keydown', $.proxy(me.onKeyDown, me));
        },

        /**
         * Sets all template event listeners.
         *
         * @public
         * @method refreshListeners
         */
        refreshListeners: function () {
            var me = this;

            me._on(me._$leftArrow, 'click touchstart', $.proxy(me.onLeftArrowClick, me));
            me._on(me._$rightArrow, 'click touchstart', $.proxy(me.onRightArrowClick, me));

            me._on(me.$el, 'touchstart MSPointerDown', $.proxy(me.onTouchStart, me));
            me._on(me.$el, 'touchmove MSPointerMove', $.proxy(me.onTouchMove, me));
            me._on(me.$el, 'touchend MSPointerUp', $.proxy(me.onTouchEnd, me));
            me._on(me.$el, 'click', $.proxy(me.onControlsWrapperClick, me));

            me._on(me.$el, 'mouseover', $.proxy(me.onMouseOver, me));
            me._on(me.$el, 'mouseout', $.proxy(me.onMouseOut, me));
        },

        onControlsWrapperClick: function (event) {
            var me = this;

            if (me.isTargetArrow(event.target)) {
                return;
            }

            $.publish('/plugin/' + me._name + '/onClick', [ me ]);

            me.opts.onClick.call(me);
        },

        /**
         * Called when left arrow was clicked / touched.
         * Calls the previous() function.
         *
         * @public
         * @method onLeftArrowClick
         * @param {jQuery.Event} event
         */
        onLeftArrowClick: function (event) {
            var me = this;

            event.preventDefault();

            $.publish('/plugin/' + me._name + '/onLeftArrowClick', [ me ]);

            me.previous();
        },

        /**
         * Called when right arrow was clicked / touched.
         * Calls the next() function.
         *
         * @public
         * @method onRightArrowClick
         * @param {jQuery.Event} event
         */
        onRightArrowClick: function (event) {
            var me = this;

            event.preventDefault();

            $.publish('/plugin/' + me._name + '/onRightArrowClick', [ me ]);

            me.next();
        },

        /**
         * Called when a finger has started touching.
         * Sets the starting point to determine the swiping gesture.
         *
         * @public
         * @method onTouchStart
         * @param {jQuery.Event} event
         */
        onTouchStart: function (event) {
            var me = this,
                touches = event.changedTouches,
                touch = touches[0];

            if (!me._active || me._$leftArrow.is(event.target) || me._$rightArrow.is(event.target)) {
                return;
            }

            me._startTouchPoint.x = touch.clientX;
            me._startTouchPoint.y = touch.clientY;

            if (touches.length > 1) {
                me._previousScale = me._scale;
            }

            event.preventDefault();
        },

        /**
         * Called when a finger was moved.
         * Used to determine a pinch to zoom or move gesture.
         *
         * @public
         * @method onTouchMove
         * @param {jQuery.Event} event
         */
        onTouchMove: function (event) {
            var me = this,
                $target = $(event.currentTarget),
                targetOffset = $target.offset(),
                touches = event.originalEvent.touches,
                touchA = touches[0],
                touchB = touches[1],
                scale = me._scale,
                deltaX,
                deltaY,
                distance;

            if (!me._active) {
                return;
            }

            event.preventDefault();

            // only one finger present
            if (touches.length === 1) {
                // move image when zoomed in
                if (scale > 1) {
                    deltaX = touchA.clientX - me._startTouchPoint.x;
                    deltaY = touchA.clientY - me._startTouchPoint.y;

                    me.translate(deltaX / scale, deltaY / scale);

                    me._startTouchPoint.x = touchA.clientX;
                    me._startTouchPoint.y = touchA.clientY;
                }
                return;
            }

            if (!me.opts.pinchToZoom) {
                return;
            }

            // space between the x and y positions
            deltaX = math.abs(touchA.clientX - touchB.clientX + targetOffset.left);
            deltaY = math.abs(touchA.clientY - touchB.clientY + targetOffset.top);

            // distance between the two fingers in pixels
            distance = math.sqrt(math.pow(deltaX, 2) + math.pow(deltaY, 2));

            if (me._lastTouchDistance === 0) {
                me._lastTouchDistance = distance;
                return;
            }

            me.scale((distance - me._lastTouchDistance) / 100);

            me._lastTouchDistance = distance;
        },

        isTargetArrow: function (target) {
            var me = this;
            return me._$leftArrow.is(target) || me._$rightArrow.is(target)
        },

        /**
         * Called when a finger is released.
         * Used to determine a swiping gesture.
         *
         * @public
         * @method onTouchEnd
         * @param {jQuery.Event} event
         */
        onTouchEnd: function (event) {
            var me = this,
                touches = event.changedTouches,
                deltaX;

            me._lastTouchDistance = 0;

            if (!me._active || math.abs(me._scale - me._previousScale) > 0.5 || me.isTargetArrow(event.target)) {
                return;
            }

            event.preventDefault();

            if (touches.length > 1) {
                return;
            }

            if (me._startTouchPoint.x === touches[0].clientX && me._startTouchPoint.y === touches[0].clientY) {
                me.$el.trigger('click');
                return;
            }

            deltaX = me._startTouchPoint.x - touches[0].clientX;

            if (!me.opts.swipeToSlide || math.abs(deltaX) < me.opts.swipeTolerance) {
                return;
            }

            if (deltaX <= 0) {
                me.previous();
                return;
            }

            me.next();
        },

        /**
         * Called when the mouse is inside the plugin element.
         *
         * @public
         * @method onMouseOver
         */
        onMouseOver: function () {
            var me = this;

            if (me.opts.pauseOnHover) {
                me.pause();
            }
        },

        /**
         * Called when the mouse is outside the plugin element.
         *
         * @public
         * @method onMouseOut
         */
        onMouseOut: function () {
            var me = this;

            if (me.opts.pauseOnHover) {
                me.play();
            }
        },

        /**
         * Starts the current auto playing loop.
         *
         * @public
         * @method play
         */
        play: function () {
            var me = this;

            if (!me._active || !me.opts.autoPlay) {
                return;
            }

            me.pause();

            me._autoPlayInterval = setInterval(function () {
                me.next();
            }, me.opts.autoPlay);
        },

        /**
         * Pauses the current auto playing loop.
         *
         * @public
         * @method pause
         */
        pause: function () {
            var me = this;

            if (!me._active || !me.opts.autoPlay) {
                return;
            }

            clearInterval(me._autoPlayInterval);
        },

        /**
         * Will be called when a key was pressed.
         * Used for keyboard sliding.
         *
         * @public
         * @method onKeyDown
         * @param {jQuery.Event} event
         */
        onKeyDown: function (event) {
            var me = this,
                keyCode = event.which;

            if (!me._active || !me.opts.keyboardNavigation) {
                return;
            }

            if (me.isKeyDown('left', keyCode)) {
                me.previous();
            }

            if (me.isKeyDown('right', keyCode)) {
                me.next();
            }
        },

        /**
         * Returns / Checks if the given keyCode belongs to the key mapping.
         *
         * @public
         * @method isKeyDown
         * @param {String} mappingName
         * @param {Number} keyCode
         * @returns {Boolean}
         */
        isKeyDown: function (mappingName, keyCode) {
            var me = this,
                keyMapping = me.opts.keyboardMapping,
                mapping = ((mappingName in keyMapping) && keyMapping[mappingName]) || [],
                len = mapping.length,
                i = 0;

            for (; i < len; i++) {
                if (mapping[i] === keyCode) {
                    return true;
                }
            }

            return false;
        },

        /**
         * Function to slide to the previous image.
         *
         * @public
         * @method previous
         */
        previous: function () {
            var me = this,
                len = me._$list.children().length,
                index = me._index - 1;

            if (!me._active) {
                return;
            }

            if (index < 0) {
                index = len - 1;
            }

            me.slideTo(index);
        },

        /**
         * Function to slide to the next image.
         *
         * @public
         * @method next
         */
        next: function () {
            var me = this,
                len = me._$list.children().length,
                index = me._index + 1;

            if (!me._active) {
                return;
            }

            if (index > len - 1) {
                index = 0;
            }

            me.slideTo(index);
        },

        /**
         * The central function for sliding the images.
         * Will use CSS transitions for sliding with an .animate() fallback for older browsers.
         *
         * @public
         * @method slideTo
         * @param {Number} index
         */
        slideTo: function (index) {
            var me = this;

            if (!me._active) {
                return;
            }

            if (me._$currentImage) {
                me.resetImageTransformation();
            }

            if (modernizr.csstransitions) {
                me._$list.css({
                    'left': (index * 100 * -1) + '%'
                });
            } else {
                me._$list.animate({
                    'left': (index * 100 * -1) + '%'
                }, 500);
            }

            me._index = index;

            me._$currentImage = $(me._$list.children()[index]).find('img');

            me.opts.onSlide.call(me, me._$currentImage, index);
        },

        /**
         * Resets the current image transformation (scale and translation).
         *
         * @public
         * @method resetImageTransformation
         */
        resetImageTransformation: function () {
            var me = this;

            me.setScale(1);

            me.setTranslation(0, 0);

            me._startTouchPoint.x = 0;
            me._startTouchPoint.y = 0;

            me._lastTouchDistance = 0;
        },

        /**
         * Sets the active flag.
         *
         * @public
         * @method setActive
         * @param {Boolean} active
         */
        setActive: function (active) {
            var me = this;

            me._active = !!active;
        },

        /**
         * Scales the current image relative to the current scale.
         *
         * @public
         * @method scale
         * @param {Number} scale
         */
        scale: function (scale) {
            var me = this;

            me.setScale(me._scale + scale);
        },

        /**
         * Sets the absolute image scale value
         *
         * @public
         * @method setScale
         * @param {Number} scale
         */
        setScale: function (scale) {
            var me = this;

            me._scale = math.max(0.5, math.min(5, scale));

            me.updateTransform();
        },

        /**
         * Translates the image position relatively.
         *
         * @public
         * @method translate
         * @param {Number} x
         * @param {Number} y
         */
        translate: function (x, y) {
            var me = this,
                translation = me._translation;

            me.setTranslation(translation.x + x, translation.y + y)
        },

        /**
         * Sets the absolute image translation.
         *
         * @public
         * @method setTranslation
         * @param {Number} x
         * @param {Number} y
         */
        setTranslation: function (x, y) {
            var me = this,
                scale = me._scale,
                image = me._$currentImage,
                width = image.width(),
                height = image.height(),
                scaledWidth = width * scale,
                scaledHeight = height * scale,
                minX = (scaledWidth - width) / scale,
                minY = (scaledHeight - height) / scale;

            me._translation.x = math.max(minX / 2 * -1, math.min(minX / 2, x));
            me._translation.y = math.max(minY / 2 * -1, math.min(minY / 2, y));

            me.updateTransform();
        },

        /**
         * takes the current scaling and translation and sets the image transformation to it in CSS.
         *
         * @public
         * @method updateTransform
         */
        updateTransform: function () {
            var me = this,
                scale = me._scale,
                translation = me._translation;

            me._$currentImage.css({
                'transform': 'scale(' + me._scale + ') translate(' + translation.x + 'px, ' + translation.y + 'px)'
            });
        },

        /**
         * Removed all listeners, classes and values from this plugin.
         *
         * @public
         * @method destroy
         */
        destroy: function () {
            var me = this,
                $el;

            me.$el.removeClass(me.opts.wrapperClass);
            me._$list.removeClass(me.opts.listClass);

            $.each(me._$list.children(), function (index, el) {
                $el = $(el);
                $el.removeClass(me.opts.itemClass);
                $el.find('img').removeClass(me.opts.imageClass);
            });

            me._$leftArrow.remove();
            me._$rightArrow.remove();

            me._destroy();
        }
    });
}(jQuery, window, Modernizr, Math));
