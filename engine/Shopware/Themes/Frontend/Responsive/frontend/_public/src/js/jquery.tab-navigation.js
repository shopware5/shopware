;(function($, window, document, undefined) {
    "use strict";

    var pluginName = 'tabContent',
        clickEvt = 'click',
        defaults = {
            /** @string activeCls Class which will be added when the drop down was triggered */
            activeCls: 'is--active'
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
     * @returns {Void}
     */
    Plugin.prototype.init = function() {
        var me = this;

        me.$nav = me.$el.find('.tab--navigation');
        me.$content = me.$el.find('.tab--content');

        if(StateManager.isSmartphone()) {
            me.createMobileView();
        } else {
            me.createDesktopView();
        }

        $(window).on('resize', function() {
            if(StateManager.isSmartphone()) {
                me.createMobileView();
            } else {
                me.createDesktopView();
            }
        });

        me.$nav.find('.navigation--entry:first-child .navigation--link').trigger(clickEvt + '.' + pluginName);
    };

    Plugin.prototype.createMobileView = function() {
        var me = this;

        me.$el.addClass('js--mobile-tab-panel').removeClass('js--desktop-tab-panel');

        me.$nav.find('.navigation--link').each(function() {
            var $this = $(this),
                href = $this.attr('href').substring(1);

            me.$content.find('.' + href).insertAfter($this);
        });

        me.$nav.find('.navigation--link').on(clickEvt + '.' + pluginName, function(event) {
            var $this = $(this),
                href = $this.attr('href').substring(1);

            event.preventDefault();

            // Hide all content boxes
            me.$nav.find('li > div[class^="content--"]').hide().removeClass(me.opts.activeCls);
            me.$nav.find('.navigation--link').removeClass(me.opts.activeCls);

            // Activate the selected content
            $this.addClass(me.opts.activeCls).next().show();
        });
    };

    Plugin.prototype.createDesktopView = function() {
        var me = this;
        me.$el.removeClass('js--mobile-tab-panel').addClass('js--desktop-tab-panel');

        me.$nav.find('.navigation--link').each(function() {
            var $this = $(this),
                href = $this.attr('href').substring(1);

            me.$nav.find('.' + href).appendTo(me.$content);
        });

        me.$nav.find('.navigation--link').on(clickEvt + '.' + pluginName, function(event) {
            var $this = $(this),
                href = $this.attr('href').substring(1);

            event.preventDefault();

            // Hide all content boxes
            me.$content.children('div[class^="content--"]').hide().removeClass(me.opts.activeCls);
            me.$nav.find('.navigation--link').removeClass(me.opts.activeCls);

            // Activate the selected content
            me.$content.find('.' + href).show().addClass(me.opts.activeCls);
            $this.addClass(me.opts.activeCls);
        });
    };

    Plugin.prototype.changeTab = function(idx, scroll) {
        var me = this;

        scroll = scroll || false;

        // The index starting at 0, ```nth-child``` is starting at 1
        idx += 1;

        me.$nav.find('.navigation--entry:nth-child(' + idx + ') .navigation--link').trigger(clickEvt + '.' + pluginName);

        if(!scroll) {
            return;
        }

        $('body').animate({
            'scrollTop': me.$nav.offset().top - 50
        }, 500);
    };

    /**
     * Destroys the initialized plugin completely, so all event listeners will
     * be removed and the plugin data, which is stored in-memory referenced to
     * the DOM node.
     *
     * @returns {Boolean}
     */
    Plugin.prototype.destroy = function() {
        var me = this;

        me.$el.off(clickEvt + '.' + pluginName).removeData('plugin_' + pluginName);
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