;(function ($) {
    'use strict';

    $.plugin('imageGallery', {

        defaults: {
            imageContainerSelector: '.image-slider--container',

            imageSlideSelector: '.image-slider--slide',

            thumbnailContainerSelector: '.image-slider--thumbnails',

            imageGalleryClass: 'image--gallery',

            previousKeyCode: 37,

            nextKeyCode: 39,

            maxZoom: 'auto'
        },

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

        createZoomInButton: function () {
            return $('<div>', {
                'class': 'btn icon--plus3 is--small button--zoom-in'
            });
        },

        createZoomOutButton: function () {
            return $('<div>', {
                'class': 'btn icon--minus3 is--small button--zoom-out'
            });
        },

        createZoomResetButton: function () {
            return $('<div>', {
                'class': 'btn icon--resize-shrink is--small button--zoom-reset'
            });
        },

        registerEvents: function () {
            var me = this;

            me._on(me.opts.imageSlideSelector, 'click', $.proxy(me.onClick, me));
            $.subscribe('plugin/imageZoom/onLensClick', $.proxy(me.onClick, me));

            me._on(window, 'keydown', $.proxy(me.onKeyDown, me));
        },

        onResetZoom: function (event) {
            var me = this,
                plugin = me.$template.data('plugin_imageSlider');

            event.preventDefault();

            if (!plugin || me.$zoomResetBtn.hasClass('is--disabled')) {
                return;
            }

            me.disableButtons();

            plugin.resetTransformation(true, function () {
                me.enableButtons();
            });
        },

        onZoomIn: function (event) {
            var me = this,
                plugin = me.$template.data('plugin_imageSlider');

            event.preventDefault();

            if (!plugin || me.$zoomInBtn.hasClass('is--disabled')) {
                return;
            }

            me.disableButtons();

            plugin.scale(1, true, function () {
                me.enableButtons();
            });
        },

        onZoomOut: function (event) {
            var me = this,
                plugin = me.$template.data('plugin_imageSlider');

            event.preventDefault();

            if (!plugin || me.$zoomOutBtn.hasClass('is--disabled')) {
                return;
            }

            me.disableButtons();

            plugin.scale(-1, true, function () {
                me.enableButtons();
            });
        },

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

        disableButtons: function () {
            var me = this,
                disabledCls = 'is--disabled';

            me.$zoomResetBtn.addClass(disabledCls);
            me.$zoomOutBtn.addClass(disabledCls);
            me.$zoomInBtn.addClass(disabledCls);
        },

        enableButtons: function () {
            var me = this,
                disabledCls = 'is--disabled';

            me.$zoomResetBtn.removeClass(disabledCls);
            me.$zoomOutBtn.removeClass(disabledCls);
            me.$zoomInBtn.removeClass(disabledCls);
        },

        onClick: function () {
            var me = this,
                plugin = me.$el.data('plugin_imageSlider');

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
                maxZoom: me.opts.maxZoom,
                startIndex: plugin ? plugin.slideIndex : 0
            });
        },

        onCloseModal: function () {
            var me = this,
                plugin = me.$template.data('plugin_imageSlider');

            if (!plugin) {
                return;
            }

            plugin.destroy();
        },

        destroy: function () {
            var me = this;


        }
    });

    $(function () {
        $('*[data-image-gallery="true"]').imageGallery();
    });
})(jQuery);