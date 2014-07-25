;(function ($) {
    'use strict';

    $.lightbox = {

        open: function(imageURL, options) {
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