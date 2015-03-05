;(function ($) {
    'use strict';

    /**
     * Shopware Image Zoom Plugin.
     *
     * Creates a zoomed view of a product image.
     * You can move a lens object over the original image to
     * see the zoomed view of the hovered area.
     */
    $.plugin('imageZoom', {

        defaults: {

            /* Setting for showing the image title in the zoom view */
            showTitle: true,

            /* The css class for the container element which contains the image */
            containerCls: 'js--img-zoom--container',

            /* The css class for the lens element which displays the current zoom viewport */
            lensCls: 'js--img-zoom--lens',

            /* The css class for the container where the zoomed image is viewed */
            flyoutCls: 'js--img-zoom--flyout',

            /* The css class for the container if the image title */
            titleContainerCls: 'js--img-zoom--title',

            /* The selector for identifying the active image */
            activeSelector: '.is--active',

            /* The speed for animations in ms */
            animationSpeed: 300
        },

        /**
         * Initializes the plugin.
         */
        init: function () {
            var me = this;

            me.applyDataAttributes();

            me.active = false;

            me.$container = me.$el.find('.image-slider--slide');
            me.imageBox = me.$el.find('.image--box');
            me.$imageElements = me.$el.find('.image--element');
            me.$thumbnails = me.$el.find('.thumbnail--link');

            me.$flyout = me.createFlyoutElement();
            me.$lens = me.createLensElement();

            if (me.opts.showTitle) {
                me.$title = me.createTitleContainer();
            }

            me.zoomImage = false;

            me.$activeImage = me.getActiveImageElement();

            me.flyoutWidth = me.$flyout.outerWidth();
            me.flyoutHeight = me.$flyout.outerHeight();

            me.registerEvents();
        },

        /**
         * Registers all necessary event listeners.
         */
        registerEvents: function() {
            var me = this;

            $('body').on('scroll.imageZoom', $.proxy(me.stopZoom, me));

            me._on(me.$container, 'mousemove', $.proxy(me.onMouseMove, me));
            me._on(me.$container, 'mouseout', $.proxy(me.stopZoom, me));
            me._on(me.$lens, 'click', $.proxy(me.onLensClick, me));

            $.subscribe('plugin/imageSlider/onRightArrowClick', $.proxy(me.stopZoom, me));
            $.subscribe('plugin/imageSlider/onLeftArrowClick', $.proxy(me.stopZoom, me));
            $.subscribe('plugin/imageSlider/onClick', $.proxy(me.stopZoom, me));
            $.subscribe('plugin/imageSlider/onLightbox', $.proxy(me.stopZoom, me));
        },

        /**
         * Creates the dom element for the lens.
         *
         * @returns {*}
         */
        createLensElement: function() {
            var me = this;

            return $('<div>', {
                'class': me.opts.lensCls,
                'html': '&nbsp;'
            }).appendTo(me.$container);
        },

        /**
         * Creates the flyout element in
         * which the zoomed image will be shown.
         *
         * @returns {*}
         */
        createFlyoutElement: function() {
            var me = this;

            return $('<div>', {
                'class': me.opts.flyoutCls
            }).appendTo(me.$el);
        },

        /**
         * Creates the container element
         * for the image title in the zoom view.
         *
         * @returns {*}
         */
        createTitleContainer: function() {
            var me = this;

            if (!me.$flyout.length || !me.opts.showTitle) {
                return;
            }

            return $('<div>', {
                'class': me.opts.titleContainerCls
            }).appendTo(me.$flyout);
        },

        /**
         * Returns the thumbnail of the
         * current active image.
         *
         * @returns {*|Array}
         */
        getActiveImageThumbnail: function() {
            var me = this;

            return me.$thumbnails.filter(me.opts.activeSelector);
        },

        /**
         * Returns the image element of
         * the current active image.
         *
         * @returns {*}
         */
        getActiveImageElement: function() {
            var me = this;

            me.$activeImageThumbnail = me.getActiveImageThumbnail();

            if (!me.$activeImageThumbnail.length) {
                return me.$imageElements.eq(0);
            }

            return me.$imageElements.eq(me.$activeImageThumbnail.index());
        },

        /**
         * Computes and sets the size of
         * the lens element based on the factor
         * between the image and the zoomed image.
         *
         * @param factor
         */
        setLensSize: function(factor) {
            var me = this;

            me.lensWidth = me.flyoutWidth / factor;
            me.lensHeight = me.flyoutHeight / factor;

            if (me.lensWidth > me.imageWidth) {
                me.lensWidth = me.imageWidth;
            }

            if (me.lensHeight > me.imageHeight) {
                me.lensHeight = me.imageHeight;
            }

            me.$lens.css({
                'width': me.lensWidth,
                'height': me.lensHeight
            });
        },

        /**
         * Sets the lens position over
         * the original image.
         *
         * @param x
         * @param y
         */
        setLensPosition: function(x, y) {
            var me = this;

            me.$lens.css({
                'top': y,
                'left': x
            });
        },

        /**
         * Makes the lens element visible.
         */
        showLens: function() {
            var me = this;

            me.$lens.stop(true, true).fadeIn(me.opts.animationSpeed);
        },

        /**
         * Hides the lens element.
         */
        hideLens: function() {
            var me = this;

            me.$lens.stop(true, true).fadeOut(me.opts.animationSpeed);
        },

        /**
         * Sets the position of the zoomed image area.
         *
         * @param x
         * @param y
         */
        setZoomPosition: function(x, y) {
            var me = this;

            me.$flyout.css('backgroundPosition', x+'px '+y+'px');
        },

        /**
         * Makes the zoom view visible.
         */
        showZoom: function() {
            var me = this;

            me.$flyout.stop(true, true).fadeIn(me.opts.animationSpeed);
        },

        /**
         * Hides the zoom view.
         */
        hideZoom: function() {
            var me = this;

            me.$flyout.stop(true, true).fadeOut(me.opts.animationSpeed);
        },

        /**
         * Sets the title of the zoom view.
         *
         * @param title
         */
        setImageTitle: function(title) {
            var me = this,
                title = title || me.imageTitle;

            if (!me.opts.showTitle || !me.$title.length) {
                return;
            }

            me.$title.html('<span>' + title + '</span>');
        },

        /**
         * Eventhandler for handling the
         * mouse movement on the image container.
         *
         * @param event
         */
        onMouseMove: function(event) {
            var me = this;

            if (!me.zoomImage) {
                me.activateZoom();
                return;
            }

            var containerOffset = me.$container.offset(),
                mouseX = event.pageX,
                mouseY = event.pageY,
                containerX = mouseX - containerOffset.left,
                containerY = mouseY - containerOffset.top,
                lensX = containerX - (me.lensWidth / 2),
                lensY = containerY - (me.lensHeight / 2),
                minX = me.imageOffset.left - containerOffset.left,
                minY = me.imageOffset.top - containerOffset.top,
                maxX = minX + me.imageWidth - me.$lens.outerWidth(),
                maxY = minY + me.imageHeight - me.$lens.outerHeight(),
                lensLeft = me.clamp(lensX, minX, maxX),
                lensTop = me.clamp(lensY, minY, maxY),
                zoomLeft = -(lensLeft - minX) * me.factor,
                zoomTop = -(lensTop - minY) * me.factor;

            if (minX >= maxX) {
                zoomLeft = zoomLeft + (me.flyoutWidth / 2) - (me.zoomImage.width / 2);
            }

            if (minY >= maxY) {
                zoomTop = zoomTop + (me.flyoutHeight / 2) - (me.zoomImage.height / 2);
            }

            if (mouseX > me.imageOffset.left && mouseX < me.imageOffset.left + me.imageWidth &&
                mouseY > me.imageOffset.top && mouseY < me.imageOffset.top + me.imageHeight) {
                me.showLens();
                me.showZoom();
                me.setLensPosition(lensLeft, lensTop);
                me.setZoomPosition(zoomLeft, zoomTop);
            } else {
                me.stopZoom();
            }
        },

        /**
         * Sets the active image element
         * for the zoom view.
         */
        setActiveImage: function() {
            var me = this;
            me.$activeImageElement = me.getActiveImageElement();
            me.$activeImage = me.$activeImageElement.find('img');

            me.imageTitle = me.$activeImageElement.attr('data-alt');
            me.imageWidth = me.$activeImage.innerWidth();
            me.imageHeight = me.$activeImage.innerHeight();
            me.imageOffset = me.$activeImage.offset();

            $.publish('plugin/imageZoom/onSetActiveImage', this);
        },

        /**
         * Activates the zoom view.
         */
        activateZoom: function() {
            var me = this;

            me.setActiveImage();

            if (!me.zoomImage) {
                me.zoomImageUrl = me.$activeImageElement.attr('data-img-original');
                me.zoomImage = new Image();

                me.zoomImage.onload = function() {
                    me.factor = me.zoomImage.width / me.$activeImage.innerWidth();

                    me.setLensSize(me.factor);
                    me.$flyout.css('background', 'url(' + me.zoomImageUrl + ') 0px 0px no-repeat #fff');

                    if (me.opts.showTitle) {
                        me.setImageTitle(me.title);
                    }
                };

                me.zoomImage.src = me.zoomImageUrl;
            }

            $.publish('plugin/imageZoom/onActivateZoom', this);

            me.active = true;
        },

        /**
         * Stops the zoom view.
         */
        stopZoom: function() {
            var me = this;

            me.hideLens();
            me.hideZoom();
            me.zoomImage = false;
            me.active = false;

            $.publish('plugin/imageZoom/onStopZoom', this);
        },

        /**
         * Handles click events on the lens.
         * Used for legacy browsers to handle
         * click events on the original image.
         *
         * @param event
         */
        onLensClick: function(event) {
            $.publish('plugin/imageZoom/onLensClick', this);
        },

        /**
         * Clamps a number between
         * a max and a min value.
         *
         * @param number
         * @param min
         * @param max
         * @returns {number}
         */
        clamp: function(number, min, max) {
            return Math.max(min, Math.min(max, number));
        },

        /**
         * Destroys the plugin and removes
         * all created elements of the plugin.
         */
        destroy: function () {
            var me = this;

            me.$lens.remove();
            me.$flyout.remove();
            me.$container.removeClass(me.opts.containerCls);

            $('body').off('scroll.imageZoom');

            me._destroy();
        }
    });
})(jQuery);