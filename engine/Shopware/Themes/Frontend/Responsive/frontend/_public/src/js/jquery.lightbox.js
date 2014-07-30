;(function ($) {
    'use strict';

    /**
     * Shopware Lightbox Plugin.
     *
     * This plugin is based on the modal plugin.
     * It opens images in a modal window and sets the width and height
     * of the modal box automatically to the image size. If the image
     * size is bigger than the window size, the modal will be set to
     * 90% of the window size so there is little margin between the modal
     * and the window edge. It calculates always the correct aspect.
     *
     * Usage:
     * $.lightbox.open('http://url.to.my.image.de');
     *
     */
    $.lightbox = {

        open: function(imageURL) {
            var me = this,
                width, height,
                maxWidth = window.innerWidth * 0.9,
                maxHeight = window.innerHeight * 0.9;

            me.image =  new Image();
            me.imageEl = $('<img>', {
                'src': imageURL
            });

            me.image.onload = function() {
                width = me.image.width;
                height = me.image.height;

                me.aspect = me.image.width / me.image.height;

                if (width > maxWidth) {
                    width = maxWidth;
                    height = width / me.aspect;
                }

                if (height > maxHeight) {
                    height = maxHeight;
                    width = height * me.aspect;
                }

                $.modal.open(me.imageEl, {
                    'width': width,
                    'height': height
                })
            };

            me.image.src = imageURL;
        }
    }
})(jQuery);