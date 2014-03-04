;(function($, window, document, undefined) {
    "use strict";

    /**
     * Off canvas menu plugin
     *
     * The plugin provides an lightweight way to use an off canvas pattern for all kind of content. The content
     * needs to be positioned off canvas using CSS3 `transform`. All the rest will be handled by the plugin.
     *
     * @example Simple usage
     * ```
     *     <a href="#" data-offcanvas="true">Menu</a>
     * ```
     *
     * @example Show the menu on the right side
     * ```
     *     <a href="#" data-offcanvas="true" data-direction="fromRight">Menu</a>
     * ```
     */
    var pluginName = 'offcanvasMenu',
        isTouch = (('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0)),
        clickEvt = (isTouch ? (window.navigator.msPointerEnabled ? 'MSPointerDown': 'touchstart') : 'click'),
        defaults = {
            container: '.off-canvas--container',
            content: '.off-canvas--content',
            pusher: '.off-canvas--pusher',

            effect: 'reveal',
            menuOpenCls: 'js--off-canvas--menu-open',
            direction: 'fromLeft',  // fromLeft or fromRight

            leftDirectionCls: 'js--direction--left',
            rightDirectionCls: 'js--direction--right',

            canvasContentCls: 'off-canvas--visible-content'
        },
        effects = [
            { name: 'reveal', push: false, cls: 'js--effect--reveal' }
        ];

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
        var me = this,
            opts = me.opts,
            effect = opts.effect,
            dirCls = (opts.direction === 'fromRight' ? opts.rightDirectionCls : opts.leftDirectionCls);

        me.effect = undefined;
        me._$container = $(opts.container);
        me._$content = $(opts.content);
        me._$pusher = $(opts.pusher);
        me._$holder = me.createHoldingContainer();

        // Terminate effect
        $.each(effects, function(i, item) {
            if(item.name === effect) {
                me.effect = item;
            }
        });

        // Throw error if we effect was not found
        if(!me.effect || !me.effect.name.length) {
            throw new Error('Effect "' + effect + '" is not supported.');
        }
        me._$container.addClass(me.effect.cls).addClass(dirCls);
        me._$holder.prependTo(me.effect.push ? me._$pusher : me._$container);

        if(me.hasOwnProperty('_$move')) {
            me._$move.appendTo(me._$holder);
        }

        me.$el.on('click.' + pluginName, function(event) {
            event.stopPropagation();
            event.preventDefault();

            me._$container.addClass(opts.menuOpenCls);
        });

        me._$container.on('click.' + pluginName, function(event) {
            event.stopPropagation();
            event.preventDefault();

            me._$container.removeClass(opts.menuOpenCls);
        });
    };

    /**
     * Creates the content element for the off canvas content
     *
     * @returns {jQuery} Created element
     */
    Plugin.prototype.createHoldingContainer = function() {
        return $('<div>', { 'class': this.opts.canvasContentCls });
    };

    /**
     * Helper method which opens the off canvas menu.
     *
     * @returns {void}
     */
    Plugin.prototype.open = function() {
        var me = this;

        me._$container.addClass(me.opts.menuOpenCls);
    };

    /**
     * Helper method which closes the off canvas menu.
     *
     * @returns {void}
     */
    Plugin.prototype.close = function() {
        var me = this;

        me._$container.removeClass(me.opts.menuOpenCls);
    };

    /**
     * Helper method which completely destroyes the plugin.
     *
     * @returns {void}
     */
    Plugin.prototype.destroy = function() {
        var me = this;

        me.$el.off('click.' + pluginName);
        me._$container.removeClass(me.opts.menuOpenCls).removeClass(me.effect.cls);
        me.$el.removeData('plugin_' + pluginName);
    };

    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
                new Plugin( this, options ));
            }
        });
    };

    // Register plugin
    $(function() {
        $('*[data-offcanvas="true"]').offcanvasMenu();
    });
})(jQuery, window, document);
