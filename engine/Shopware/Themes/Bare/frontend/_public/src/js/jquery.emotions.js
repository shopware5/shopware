;(function($, window, document, undefined) {
    "use strict";

    var pluginName = 'emotions',
        isTouch = (('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0)),
        clickEvt = (isTouch ? (window.navigator.msPointerEnabled ? 'MSPointerDown': 'touchstart') : 'click'),
        defaults = {
            /** @string activeCls Class which will be added when the drop down was triggered */
            fullScreenCls: 'js--fullscreen-active',
            maxContainerWidth: 1260,
            cellHeightOffset: 25,
            baseFontSize: 16
        };

    /**
     * Plugin constructor which merges the default settings with the user settings
     * and parses the `data`-attributes of the incoming `element`.
     *
     * @param {HTMLElement} element - Element which should be used in the plugin
     * @param {Object} userOpts - User settings for the plugin
     * @returns {Void}
     * @constructor
     */
    function Plugin(element, userOpts) {
        var me = this;

        me.$el = $(element);
        me.opts = $.extend({}, defaults, userOpts);

        me._defaults = defaults;
        me._name = pluginName;

        me.init();
    }

    /**
     * Initializes the plugin, sets up event listeners and adds the necessary
     * classes to get the plugin up and running.
     *
     * @returns {Boolean}
     */
    Plugin.prototype.init = function() {
        var me = this;

        me.$fullscreenLink = me.$el.find('.header--link');
        me.registerEventListeners();
        me._fullscreenActive = false;

        me._baseWidth = me.opts.maxContainerWidth || 1260;
        me._lastRow = parseInt(me.$el.attr('data-last-row'), 10);
        me._cellHeight = parseInt(me.$el.attr('data-cell-height'), 10) + me.opts.cellHeightOffset;

        me.$list = me.$el.find('.emotion--list');
        me.$mappings  = me.$el.find('.element-banner--mapping');
        me.$videos = me.$el.find('video');
        me.$elements = me.$el.find('.emotion--element').each(function() {
            var $item = $(this);

            // Cache the inital width and height
            $item.data('width', $item.outerWidth());
            $item.data('height', $item.outerHeight());
        });

        // ...if a banner mapping is active, resize it to percentage values
        if(me.$mappings.length) {
            me.resizeBannerMapping();
        }

        if(me.$videos.length) {
            me.resizeVideoElement();
        }

        $(window).resize(function() {
            me.resizeElements();
        });
        me.resizeElements();

        if(me.$el.hasClass('emotion--horizontal')) {
            me.registerHorizontalEventListeners();
        }
    };

    Plugin.prototype.resizeBannerMapping = function () {
        var me = this;

        me.$mappings.each(function () {
            var $this = $(this),
                $parent = $(this).parent('.emotion--element-banner'),
                imgWidth = $parent.attr('data-width'),
                imgHeight = $parent.attr('data-height'),
                width = $this.outerWidth(),
                height = $this.outerHeight(),
                top = $this.css('top'),
                left = $this.css('left');

            // Remove the unit and cast the values to integer
            top = parseInt(top.substring(0, top.length - 2), 10);
            left = parseInt(left.substring(0, left.length - 2), 10);

            $this.css({
                left: (left / imgWidth) * 100 + '%',
                top: (top / imgHeight) * 100 + '%',
                width: (width / imgWidth) * 100 + '%',
                height: (height / imgHeight) * 100 + '%'
            });
        });
    };

    Plugin.prototype.resizeVideoElement = function() {
        var me = this,
            $videos = me.$el.find('video');

        var resizeElement = function($el) {
            var $parent = $el.parents('.emotion--element-video'),
                vidWidth = Math.floor($el.width()),
                vidHeight = Math.floor($el.height()),
                parentWidth = Math.floor($parent.width()),
                parentHeight = Math.floor($parent.height());

            $el.css({ 'height': (parentHeight - vidHeight) + vidHeight + 'px' });

            vidWidth = Math.floor($el.width());
            $el.css('margin-left', -(vidWidth - parentWidth) / 2 + 'px');
        };

        $videos.each(function() {
            var $this = $(this);

            $this.on('loadeddata', function() {
                resizeElement($(this));
            });
        });

        $(window).on('resize.' + pluginName, function() {
            $videos.each(function() {
                resizeElement($(this));
            });
        });
    };

    Plugin.prototype.registerHorizontalEventListeners = function() {
        var me = this;

        me._pageCounter = 0;
        me._maxPages = parseInt(me.$list.attr('data-pages'), 10);

        me.$list.on('swipeleft.' + pluginName, function() {
            me._pageCounter++;

            me.$list.transition({ translate: [ -(me._pageCounter * 100 / me._maxPages) + '%', 0 ] }, 450);
        });
    };

    Plugin.prototype.resizeElements = function() {
        var me = this;

        var containerWidth = me.$el.outerWidth(),
            percentage;

        if(me._baseWidth < containerWidth) {
            me.$list.removeAttr('style');

            me.$elements.each(function() {
                $(this).removeAttr('style');
            });
        }
        percentage = Math.floor((containerWidth / me._baseWidth) * 100);

        me.$elements.each(function() {
            var $item = $(this),
                itemHeight = $item.data('height');

            $item.css('height', (itemHeight / 100 ) * percentage);
        });

        me.$list.css('height', Math.floor((((me._cellHeight * me._lastRow) / 100) * percentage) / me.opts.baseFontSize) + 'em');
    };

    Plugin.prototype.registerEventListeners = function() {
        var me = this;

        me.$fullscreenLink.on(clickEvt + '.' + pluginName, function(event) {
            event.preventDefault();

            if(!me._fullscreenActive) {

                me.$el.addClass(me.opts.fullScreenCls);
                me.$fullscreenLink.find('.link--text').html(me.$fullscreenLink.attr('data-close'));
                me.launchFullscreen(me.$el[0]);

                // The ```fullscreenchange``` will be fired with a slighly delay
                window.setTimeout(function() {
                    me._fullscreenActive = true;
                }, 150);
            } else {
                me.exitFullscreen();

                me.$fullscreenLink.find('.link--text').html(me.$fullscreenLink.attr('data-open'));
                me.$el.removeClass(me.opts.fullScreenCls);

                me._fullscreenActive = false;
            }
        });

        // Catch the close event which can be triggered using the [ESC] key in fullscreen mode
        document.onwebkitfullscreenchange = function() {
            if(me._fullscreenActive) {
                me.$fullscreenLink.find('.link--text').html(me.$fullscreenLink.attr('data-open'));
                me.$el.removeClass(me.opts.fullScreenCls);
                me._fullscreenActive = false;
            }
        };
    };

    Plugin.prototype.launchFullscreen = function(element) {
        if(element.requestFullscreen) {
            element.requestFullscreen();
        } else if(element.mozRequestFullScreen) {
            element.mozRequestFullScreen();
        } else if(element.webkitRequestFullscreen) {
            element.webkitRequestFullscreen();
        } else if(element.msRequestFullscreen) {
            element.msRequestFullscreen();
        }
    };

    Plugin.prototype.exitFullscreen = function() {
        if(document.exitFullscreen) {
            document.exitFullscreen();
        } else if(document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if(document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        }
    };

    /**
     * Destroyes the initialized plugin completely, so all event listeners will
     * be removed and the plugin data, which is stored in-memory referenced to
     * the DOM node.
     *
     * @returns {Boolean}
     */
    Plugin.prototype.destroy = function() {
        var me = this;
    };

    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
                new Plugin( this, options ));
            }
        });
    };
})(jQuery, window, document);