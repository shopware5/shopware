;(function($, window, document, undefined) {
    "use strict";

    $.plugin('emotion', {

        defaults: {
            mode: 'resize',
            fullscreen: false,

            gridSizerSelector: '.grid--sizer',
            itemSelector: '.emotion--element',
            transitionDuration: '0.25s'
        },

        init: function() {
            var me = this;

            // Get the configured data attributes to extend the user configuration
            me.applyDataAttributes();

            if(me.opts.mode === 'masonry') {
                me.createMasonryView();
            }

            me.videos = me.$el.find('*[data-video-resize="true"]');
            if(me.videos.length) {
                me.createVideoResizing(me.videos);
            }

            me.bannerMapping = me.$el.find('*[data-banner-mapping="true"]');
            if(me.bannerMapping.length) {
                me.createBannerMapping(me.bannerMapping);
            }
        },

        createMasonryView: function() {
            var me = this;

            me.$el.masonry({
                'gutter': 0, // we're using css to set the gutter
                'columnWidth': me.$el.find(me.opts.gridSizerSelector)[0],
                'itemSelector': me.opts.itemSelector,
                'transitionDuration': me.opts.transitionDuration
            });
        },

        createBannerMapping: function(elements) {
            var me = this;

            var getImageSize = function($el) {
                return {
                    width: window.parseInt($el.attr('data-width'), 10),
                    height: window.parseInt($el.attr('data-height'), 10)
                };
            };

            var getParentSize = function($el) {
                return {
                    width: window.parseFloat($el.css('width')) - window.parseFloat($el.css('padding-right')),
                    height: window.parseFloat($el.css('height')) - window.parseFloat($el.css('padding-bottom'))
                }
            };

            var isImageLoaded = function(img) {
                var isReady = false;

                if(img.complete || img.readyState === 4) {
                    isReady = true;
                }

                return isReady;
            };

            var resizeImage = function($item) {
                if(!isImageLoaded($item.find('img')[0])) {
                    resizeImage($item);
                    return;
                }

                var $img = $item.find('img'),
                    imgSize = getImageSize($item),
                    $parent = $item.parents('.banner-element'),
                    parentSize = getParentSize($parent),
                    imgRatio = imgSize.width / imgSize.height,
                    parentRatio = parentSize.width / parentSize.height,
                    $container = $parent.find('*[data-banner-mapping="true"]'),
                    imageStyle = (imgRatio > parentRatio) ? { 'height': '100%' } : { 'width': '100%' };

                $container.removeAttr('style');
                $img.removeAttr('style').css(imageStyle);

                var containerWidth = $img.outerWidth(),
                    containerHeight = $img.outerHeight(),
                    marginLeft = -(containerWidth / 2),
                    marginTop = -(containerHeight / 2);

                $container.css({
                    'height': containerHeight,
                    'width': containerWidth,
                    'top': '50%',
                    'left': '50%',
                    'margin-left': marginLeft,
                    'margin-top': marginTop
                });
            };

            $.each(elements, function(i, item) {
                resizeImage($(item));
            });

            $(window).on(me.getEventName('resize'), function() {
                window.setTimeout(function() {
                    $.each(elements, function(i, item) {
                        resizeImage($(item));
                    });
                }, 200);
            });
        },

        createVideoResizing: function(videos) {
            var me = this,
                videoRatio = 16 / 9,
                resizeTimeout;

            var setVideoTransform = function($item, scale, offset) {
                var transform;
                offset = $.extend({ x: 0, y: 0 }, offset);

                transform = 'translate(' + Math.round(offset.x) + 'px,' + Math.round(offset.y) + 'px) scale(' + scale  + ')';
                $item.css({
                    '-webkit-transform': transform,
                    'transform': transform
                });
            };

            var setVideoOrigin = function($item, x, y) {
                var origin = x + '% ' + y + '%';

                $item.css({
                    '-webkit-transform-origin': origin,
                    'transform-origin': origin
                });
            };

            var positionVideo = function($item, scale, width, height) {
                var x = window.parseInt($item.attr('data-origin-x'), 10),
                    y = window.parseInt($item.attr('data-origin-y'), 10);

                setVideoOrigin($item, x, y);

                var viewportRatio = width / height,
                    scaledHeight = scale * height,
                    scaledWidth = scale * width,
                    percentFromX = (x - 50) / 100,
                    percentFromY = (y - 50) / 100,
                    offset = {};

                if (videoRatio < viewportRatio) {
                    offset.x = (scaledWidth - width) * percentFromX;
                } else if (viewportRatio < videoRatio) {
                    offset.y = (scaledHeight - height) * percentFromY;
                }

                return offset;
            };

            var resize = function() {
                $.each(videos, function(i, item) {
                    var $item = $(item),
                        $parent = $item.parents('.emotion--element-video'),
                        height = window.parseFloat($parent.css('height')),
                        width = window.parseFloat($parent.css('width')),
                        viewportRatio = width / height,
                        scale = 1;

                    if(videoRatio < viewportRatio) {
                        scale = viewportRatio / videoRatio;
                    } else if(viewportRatio < videoRatio) {
                        scale = videoRatio / viewportRatio;
                    }
                    scale = scale * window.parseFloat($item.attr('data-scale'));

                    var offset = positionVideo($item, scale, width, height);
                    setVideoTransform($item, scale, offset);
                });
            };

            $(window).on(me.getEventName('resize'), function() {
                window.clearTimeout(resizeTimeout);
                resizeTimeout = window.setTimeout(resize, 200);
            });

            $.each(videos, function(i, item) {
                var $item = $(item),
                    $parent = $item.parent(),
                    $player = $parent.find('video'),
                    player = $player.get(0),
                    $playBtn = $parent.find('.play--video'),
                    $icn = $playBtn.find('i');

                player.addEventListener('canplay', function() {
                    if(!player.paused || $player.attr('autoplay')) {
                        $icn.removeClass($playBtn.attr('data-play')).addClass($playBtn.attr('data-pause'));
                    } else {
                        $icn.addClass($playBtn.attr('data-play')).removeClass($playBtn.attr('data-pause'));
                    }
                    player.removeEventListener('canplay');
                }, false);

                $playBtn.on(me.getEventName('click'), function(event) {
                    var $item = $(this),
                        $parent = $item.parent(),
                        $player = $parent.find('video'),
                        player = $player.get(0),
                        $icn = $item.find('i');

                    event.preventDefault();

                    if(player.paused) {
                        $icn.removeClass($item.attr('data-play')).addClass($item.attr('data-pause'));
                        player.play();
                    } else {
                        $icn.addClass($item.attr('data-play')).removeClass($item.attr('data-pause'));
                        player.pause();
                    }
                });
            });

            // We have to start the video playback using a little workaround
            // here due to the fact that iOS's Mobile Safari blocks the ```autoplay```
            // attribute on the ```<video>``` element.
            $(document).on('touchmove', function() {
                $.each(videos, function(i, item) {
                    if(!$(item).attr('autoplay')) {
                        return;
                    }
                    item.play();
                });
            });

            resize();
        },

        destroy: function() {
            var me = this;

            me._destroy();
        }
    });
})(jQuery, window, document);