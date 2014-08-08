;(function ($) {
    'use strict';

    /**
     * Shopware Image Zoom Plugin.
     */
    $.plugin('imageZoom', {

        defaults: {

            /* The css class for the container element which contains the image */
            containerCls: 'js--img-zoom--container',

            /* The css class for the lens element which displays the current zoom viewport */
            lensCls: 'js--img-zoom--lens',

            /* The css class for the container where the zoomed image is viewed */
            flyoutCls: 'js--img-zoom--flyout',

            /* The selector for identifying the active image */
            activeSelector: '.is--active',

            /* The speed for animations in ms */
            animationSpeed: 300
        },

        init: function () {
            var me = this;

            me.applyDataAttributes();

            me.active = false;

            me.$container = me.$el.find('.image-slider--container');
            me.imageBox = me.$el.find('.image--box');
            me.$imageElements = me.$el.find('.image--element');
            me.$thumbnails = me.$el.find('.thumbnail--link');

            me.$flyout = me.createFlyoutElement();
            me.$lens = me.createLensElement();

            me.zoomImage = false;

            me.$activeImage = me.getActiveImageElement();

            me.flyoutWidth = me.$flyout.outerWidth();
            me.flyoutHeight = me.$flyout.outerHeight();

            me.registerEvents();
        },

        registerEvents: function() {
            var me = this;

            $('.page-wrap').on('scroll.imageZoom', $.proxy(me.stopZoom, me));

            me._on(me.$container, 'mousemove', $.proxy(me.onMouseMove, me));
            me._on(me.$container, 'mouseout', $.proxy(me.stopZoom, me));
            me._on(me.$lens, 'click', $.proxy(me.onLensClick, me));

            $.subscribe('plugin/imageSlider/onRightArrowClick', $.proxy(me.stopZoom, me));
            $.subscribe('plugin/imageSlider/onLeftArrowClick', $.proxy(me.stopZoom, me));
            $.subscribe('plugin/imageSlider/onClick', $.proxy(me.stopZoom, me));
            $.subscribe('plugin/imageSlider/onLightbox', $.proxy(me.stopZoom, me));
        },

        createLensElement: function() {
            var me = this;

            return $('<div>', {
                'class': me.opts.lensCls,
                'html': '&nbsp;'
            }).appendTo(me.$container);
        },

        createFlyoutElement: function() {
            var me = this;

            return $('<div>', {
                'class': me.opts.flyoutCls
            }).appendTo(me.$el);
        },

        getActiveImageThumbnail: function() {
            var me = this;

            return me.$thumbnails.filter(me.opts.activeSelector);
        },

        getActiveImageElement: function() {
            var me = this;

            me.$activeImageThumbnail = me.getActiveImageThumbnail();

            if (!me.$activeImageThumbnail.length) {
                return me.$imageElements.eq(0);
            }

            return me.$imageElements.eq(me.$activeImageThumbnail.index());
        },

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

        setLensPosition: function(x, y) {
            var me = this;

            me.$lens.css({
                'top': y,
                'left': x
            });
        },

        showLens: function() {
            var me = this;

            me.$lens.stop(true, true).fadeIn(me.opts.animationSpeed);
        },

        hideLens: function() {
            var me = this;

            me.$lens.stop(true, true).fadeOut(me.opts.animationSpeed);
        },

        setZoomPosition: function(x, y) {
            var me = this;

            me.$flyout.css('backgroundPosition', x+'px '+y+'px');
        },

        showZoom: function() {
            var me = this;

            me.$flyout.stop(true, true).fadeIn(me.opts.animationSpeed);
        },

        hideZoom: function() {
            var me = this;

            me.$flyout.stop(true, true).fadeOut(me.opts.animationSpeed);
        },

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

        setActiveImage: function() {
            var me = this;
            me.$activeImageElement = me.getActiveImageElement();
            me.$activeImage = me.$activeImageElement.find('img');

            me.imageWidth = me.$activeImage.innerWidth();
            me.imageHeight = me.$activeImage.innerHeight();
            me.imageOffset = me.$activeImage.offset();

            $.publish('plugin/imageZoom/onSetActiveImage');
        },

        activateZoom: function() {
            var me = this;

            me.setActiveImage();

            if (!me.zoomImage) {
                me.zoomImageUrl = me.$activeImageElement.attr('data-img-original');
                me.zoomImage = new Image();

                me.zoomImage.onload = function() {
                    me.factor = me.zoomImage.width / me.$activeImage.innerWidth();

                    /**
                     * Don't show the lens for small
                     * images where the original size
                     * is smaller as the lens.
                     */
                    if (me.factor <= 1.2) {
                        return;
                    }

                    me.setLensSize(me.factor);
                    me.$flyout.css('background', 'url(' + me.zoomImageUrl + ') 0px 0px no-repeat #fff');
                };

                me.zoomImage.src = me.zoomImageUrl;
            }

            $.publish('plugin/imageZoom/onActivateZoom');

            me.active = true;
        },

        stopZoom: function() {
            var me = this;

            me.hideLens();
            me.hideZoom();
            me.zoomImage = false;
            me.active = false;

            $.publish('plugin/imageZoom/onStopZoom');
        },

        onLensClick: function(event) {
            event.stopPropagation();
            $.publish('plugin/imageZoom/onLensClick');
        },

        clamp: function(number, min, max) {
            return Math.max(min, Math.min(max, number));
        },

        destroy: function () {
            var me = this;

            me.$lens.remove();
            me.$flyout.remove();
            me.$container.removeClass(me.opts.containerCls);

            $('.page-wrap').off('scroll.imageZoom');

            me._destroy();
        }
    });
})(jQuery);