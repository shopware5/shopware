;(function($, window, document, undefined) {
    "use strict";

    /**
     * Formats a string and replaces the placeholders.
     *
     * @example format('<div class="%0"'>%1</div>, [value for %0], [value for %1], ...)
     *
     * @param {String} str
     * @param {Mixed}
     * @returns {String}
     */
    var format = function (str) {
        for (var i = 1; i < arguments.length; i++) {
            str = str.replace('%' + (i - 1), arguments[i]);
        }
        return str;
    };

    var pluginName = 'imageSlider',
        isTouch = (('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0)),
        clickEvt = (isTouch ? (window.navigator.msPointerEnabled ? 'MSPointerDown': 'touchstart') : 'click'),
        defaults = {
            /** @string activeCls Class which will be added when the drop down was triggered */
            activeCls: 'is--active',
            iconArrowOpen: 'icon--arrow-right',
            iconArrowClose: 'icon--arrow-left'
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

        me._thumbnailSelector = me.$el.attr('data-thumbnail-selector') || '';
        me.$thumbnails = me.$el.find(me._thumbnailSelector);
        me.$thumbnailsContainer = me.$el.find('div[data-thumbnails="true"]');
        me.$img = me.$el.find('.image--element');

        // We need thumbnails to create
        if(!me.$thumbnails.length) {
            return false;
        }

        me.initThumbnails(me.$thumbnailsContainer);

        me.$slider = me.createSlider();
        me.$img.replaceWith(me.$slider);

        me._glide = me.$el.find('.slider').glide({
            navigationClass: 'panel--dot-nav',
            navigationCurrentItemClass: 'is--active',
            arrowMainClass: 'panel--arrow',
            arrowRightClass: 'right--arrow',
            arrowLeftClass: 'left--arrow',
            arrowRightText: '',
            arrowLeftText: '',
            autoplay: false
        }).data('api_glide');
    };

    Plugin.prototype.initThumbnails = function($container) {
        var me = this, $arrow;

        $arrow = $container.find('.thumbnails--arrow i');
        $container.show();
        $container.css('left', -$container.outerWidth() + 43 + 'px');

        $(window).resize(function() {
            $container.css('left', -$container.outerWidth() + 43);
        });

        $container.on('click', function(event) {
            event.preventDefault();

            var $target = $(event.target),
                $link = $target.parent('a');

            if($target.hasClass('thumbnail--image')) {
                var id = $link.attr('data-slider-index');

                me._glide.jump(id);
                me.$thumbnailsContainer.find('a').removeClass('is--active');

                $link.addClass('is--active');
                return false;
            }

            if(!$container.hasClass(me.opts.activeCls)) {
                if(!Modernizr.csstransitions) {
                    $container.css('left', 0);
                } else {
                    $container.transition({
                        'left': 0
                    }, 500, 'snap');
                }
                $arrow.removeClass(me.opts.iconArrowOpen).addClass(me.opts.iconArrowClose);
                $container.addClass(me.opts.activeCls);
            } else {
                if(!Modernizr.csstransitions) {
                    $container.css('left', -$container.outerWidth() + 43);
                } else {
                    $container.transition({
                        'left': -$container.outerWidth() + 43
                    }, 500, 'snap');
                }
                $arrow.removeClass(me.opts.iconArrowClose).addClass(me.opts.iconArrowOpen);
                $container.removeClass(me.opts.activeCls);
            }
        });
    };

    Plugin.prototype.createSlider = function() {
        var me = this,
            imgs = [];

        me.$thumbnails.each(function() {
            var $this = $(this),
                src = $this.attr('data-xlarge-img'),
                alt = $this.attr('title');

            imgs.push(format('<li class="slide"><img src="%0" alt="%1"></li>', src, alt));
        });

        return [
            '<div class="slider">',
                '<ul class="slides">',
                    imgs.join(''),
                '</ul>',
            '</div>'
        ].join('');
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