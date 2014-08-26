;(function ($, window, document, undefined) {
    "use strict";

    var pluginName = 'tabContent',
	    clickEvt = 'click',
	    defaults = {
            /** @string activeCls Class which will be added when the drop down was triggered */
            activeCls: 'is--active',

            /** @string mode The mode which should be used by the plugin */
            mode: 'local'
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

        if (me.$el.attr('data-mode') && me.$el.attr('data-mode').length) {
            me.opts.mode = me.$el.attr('data-mode');
        }

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

        me.currentState = StateManager.getCurrent();

        me.$additionalTriggers = $('*[data-show-tab="true"]');

        me.targets = [];

	    if (StateManager.isSmartphone()) {
            me.createMobileView();
        } else {
            me.createDesktopView();
        }

	    $(window).on('resize.' + pluginName, function () {

            if (StateManager.getCurrent() == me.currentState) {
                return;
            }

            if (StateManager.isSmartphone()) {
                me.createMobileView();
            } else {
                me.createDesktopView();
            }

            me.currentState = StateManager.getCurrent();
        });

        me.$additionalTriggers.each(function() {
            var trigger = $(this),
                target = trigger.attr('href').substring(1);

            if (me.targets.indexOf(target) > -1) {
                trigger.on(clickEvt + '.' + pluginName, function(e) {
                    e.preventDefault();
                    me.changeTab($('.' + target).index(), true);
                })
            }
        });

        if (me.opts.mode == 'local') {
            me.$nav.find('.navigation--entry:first-child .navigation--link').trigger(clickEvt + '.' + pluginName);
        }
    };

    Plugin.prototype.createMobileView = function () {
        var me = this;

        me.$el.addClass('js--mobile-tab-panel').removeClass('js--desktop-tab-panel');

        if (me.opts.mode !== 'remote') {
            me.$nav.find('.navigation--link').each(function () {
                var $this = $(this),
                    href = $this.attr('href').substring(1),
                    content = me.$el.find('.' + href);

                if (content.html().length <= 1) {

                    if (me.$el.find('.navigation--entry').length > 1) {
                        $this.parent('.navigation--entry').remove();
                        content.remove();
                    } else {
                        me.$el.remove();
                    }
                } else {
                    content.insertAfter($this);
                }

                me.targets.push(href);
            });

            me.$nav.find('.navigation--link').on(clickEvt + '.' + pluginName, function (event) {
                var $this = $(this),
                    href = $this.attr('href').substring(1);

                event.preventDefault();

                // Hide all content boxes
                me.$el.find('li > div[class^="content--"]').hide().removeClass(me.opts.activeCls);
                me.$el.find('.navigation--link').removeClass(me.opts.activeCls);

                // Activate the selected content
                $this.addClass(me.opts.activeCls).next().show();

                $.publish('plugin/tabContent/onChangeTab');
            });
        } else {
            var active = me.$nav.find('.is--active');
            me.$el.find('.content--custom').insertAfter(active);
        }
    };

    Plugin.prototype.createDesktopView = function () {
        var me = this;
        me.$el.removeClass('js--mobile-tab-panel').addClass('js--desktop-tab-panel');

        if (me.opts.mode !== 'remote') {
            me.$nav.find('.navigation--link').each(function () {
                var $this = $(this),
                    href = $this.attr('href').substring(1),
                    content = me.$el.find('.' + href);

                if (content.html().length <= 1) {

                    if (me.$el.find('.navigation--entry').length > 1) {
                        $this.parent('.navigation--entry').remove();
                        content.remove();
                    } else {
                        me.$el.remove();
                    }

                } else {
                    content.appendTo(me.$content);
                }

                me.targets.push(href);
            });

            me.$nav.find('.navigation--link').on(clickEvt + '.' + pluginName, function (event) {
                var $this = $(this),
                    href = $this.attr('href').substring(1);

                event.preventDefault();

                // Hide all content boxes
                me.$content.children('div[class^="content--"]').hide().removeClass(me.opts.activeCls);
                me.$nav.find('.navigation--link').removeClass(me.opts.activeCls);

                // Activate the selected content
                me.$content.find('.' + href).show().addClass(me.opts.activeCls);
                $this.addClass(me.opts.activeCls);

                $.publish('plugin/tabContent/onChangeTab');
            });
        } else {
            me.$nav.find('.content--custom').appendTo(me.$content);
        }
    };

    Plugin.prototype.changeTab = function (idx, scroll) {
        var me = this;

        scroll = scroll || false;

        // The index starting at 0, ```nth-child``` is starting at 1
        idx += 1;

        me.$nav.find('.navigation--entry:nth-child(' + idx + ') .navigation--link').trigger(clickEvt + '.' + pluginName);

	    if (scroll) {
            $('body').animate({
                'scrollTop':  me.$nav[0].offsetTop
            }, 500);
        }

        $.publish('plugin/tabContent/onChangeTab');
    };

    /**
     * Destroys the initialized plugin completely, so all event listeners will
     * be removed and the plugin data, which is stored in-memory referenced to
     * the DOM node.
     *
     * @returns {Boolean}
     */
    Plugin.prototype.destroy = function () {
        var me = this;

        me.$additionalTriggers.off(clickEvt + '.' + pluginName);

        $(window).off('resize.' + pluginName);

        me.$nav.find('.navigation--link').off(clickEvt + '.' + pluginName);
    };

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
			new Plugin(this, options));
            }
        });
    };
})(jQuery, window, document);