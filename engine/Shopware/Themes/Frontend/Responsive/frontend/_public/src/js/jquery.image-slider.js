;(function($) {
    'use strict';

    $.plugin('imageSlider', {

        defaults: {

            animationSpeed: 300,

            thumbnails: true,

            arrowControls: true,

            autoSlide: false,

            autoSlideInterval: 5000
        },

        init: function () {
            var me = this;

            me.applyDataAttributes();

            me.$slideContainer = me.$el.find('.image-slider--container');
            me.$slide = me.$slideContainer.find('.image-slider--slide');

            if (me.opts.thumbnails) {
                me.$thumbnailContainer = me.$el.find('.image-slider--thumbnails');
            }

            me.trackItems();
            me.setSizes();

            if (me.opts.arrowControls) me.createArrows();

            me.slideIndex = 0;
            me.slideInterval = false;
            me.activeImage = false;

            me.registerEvents();
        },

        registerEvents: function() {
            var me = this;

            $(window).on('resize', function() {
                me.setSizes();
                me.setPosition(0);
            });

            me.$images.on('click.imageSlider', $.proxy(me.onSliderClick, me));

            if (me.opts.arrowControls) {
                me.$arrowLeft.on('click.imageSlider', $.proxy(me.onLeftArrowClick, me));
                me.$arrowRight.on('click.imageSlider', $.proxy(me.onRightArrowClick, me));
            }

            if (me.opts.thumbnails) {
                me.$thumbnails.each(function(index, el) {
                    $(el).on('click.imageSlider', function(event) {
                        event.preventDefault();
                        me.slide(index);
                    });
                });
            }
        },

        createArrows: function() {
            var me = this;

            me.$arrowLeft = $('<a>', {
                'class': 'arrow is--left'
            }).appendTo(me.$slideContainer);

            me.$arrowRight = $('<a>', {
                'class': 'arrow is--right'
            }).appendTo(me.$slideContainer);
        },

        trackItems: function() {
            var me = this;

            me.$items = me.$slide.find('.image-slider--item');
            me.$images = me.$slide.find('.image--element');

            if (me.opts.thumbnails) {
                me.$thumbnails = me.$thumbnailContainer.find('.thumbnail--link');
            }

            me.itemCount = me.$items.length;
        },

        setSizes: function() {
            var me = this;

            me.itemsWidth = me.$slideContainer.innerWidth();
            me.slideWidth = me.itemsWidth * me.itemCount;

            me.$slide.css({ 'width': me.slideWidth });
            me.$items.css({ 'width': me.itemsWidth });
        },

        setPosition: function(index) {
            var me = this,
                i = index || me.slideIndex;

            me.$slide.css({ 'left': - ( i * me.itemsWidth ) });
        },

        setActiveThumbnail: function(index) {
            var me = this;

            me.$thumbnails.removeClass('is--active');
            me.$thumbnails.eq(index).addClass('is--active');
        },

        startAutoSlide: function() {
            var me = this;

            me.slideInterval = window.setInterval(function(){
                me.slideNext();
            }, me.opts.autoSlideInterval);
        },

        stopAutoSlide: function() {
            var me = this;

            window.clearInterval(me.slideInterval);
        },

        slide: function(index, callback) {
            var me = this,
                newPosition = -(index * me.itemsWidth),
                method = (Modernizr.csstransitions) ? 'transition' : 'animate';

            me.slideIndex = index;

            if (me.opts.thumbnails) me.setActiveThumbnail(index);

            me.$slide[method]({ 'left': newPosition }, me.opts.animationSpeed, 'ease', $.proxy(callback, me));
        },

        slideNext: function() {
            var me = this,
                newIndex = me.slideIndex + 1;

            if (newIndex == me.itemCount) {
                newIndex = 0;
            }

            me.slide(newIndex);
        },

        slidePrev: function() {
            var me = this,
                newIndex = me.slideIndex - 1;

            if (newIndex < 0) {
                newIndex = me.itemCount - 1;
            }

            me.slide(newIndex);
        },

        onLeftArrowClick: function() {
            var me = this;

            me.slidePrev();
            $.publish('/plugin/imageSlider/onLeftArrowClick');
        },

        onRightArrowClick: function() {
            var me = this;

            me.slideNext();
            $.publish('/plugin/imageSlider/onRightArrowClick');
        },

        onSliderClick: function() {
            var me = this;

            me.openLightBox();
            $.publish('/plugin/imageSlider/onClick');
        },

        openLightBox: function() {
            var me = this,
                image = me.$images.eq(me.slideIndex).attr('data-img-original');

            $.lightbox.open(image);
        },

        destroy: function() {
            var me = this;
        }

    });
})(jQuery, Modernizr, window);
