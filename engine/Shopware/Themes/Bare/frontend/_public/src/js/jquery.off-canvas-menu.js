;(function($, window, document, undefined) {
    "use strict";

    var pluginName = 'offcanvasMenu',
        defaults = {
            container: '.off-canvas--container',
            content: '.off-canvas--content',
            pusher: '.off-canvas--pusher',

            effect: 'reveal',
            menuOpenCls: 'js--off-canvas--menu-open'
        },
        effects = [
            { name: 'slideInOnTop', push: false, cls: 'js--effect--slide-in-on-top' },
            { name: 'reveal', push: false, cls: 'js--effect--reveal' },
            { name: 'push', push: true, cls: 'js--effect--push' },
            { name: 'slideAlong', push: false, cls: 'js--effect--slide-along' },
            { name: 'reverseSlideOut', push: false, cls: 'js--effect--reverse-slide-out' },
            { name: 'rotatePusher', push: true, cls: 'js--effect--rotate-pusher' },
            { name: '3DrotateIn', push: true, cls: 'js--effect--3d-rotate-in' },
            { name: '3DrotateOut', push: true, cls: 'js--effect--3d-rotate-out' },
            { name: 'scaleDownPusher', push: true, cls: 'js--effect--scale-down-pusher' },
            { name: 'scaleUp', push: false, cls: 'js--effect--scale-up' },
            { name: 'scaleRotatePusher', push: false, cls: 'js--effect--scale-rotate-pusher' },
            { name: 'openDoor', push: false, cls: 'js--effect--open-door' },
            { name: 'fallDown', push: false, cls: 'js--effect--fall-down' },
            { name: 'delayed3Drotate', push: false, cls: 'js--effect--delayed-3d-rotate' }
        ];

    function Plugin(element, userOpts) {
        var me = this;

        me.$el = $(element);
        me.opts = $.extend({}, defaults, userOpts);

        me._defaults = defaults;
        me._name = pluginName;

        me.init();
    }

    Plugin.prototype.init = function() {
        var me = this,
            opts = me.opts,
            effect = opts.effect;

        me.effect = undefined;
        me._$container = $(opts.container);
        me._$content = $(opts.content);
        me._$pusher = $(opts.pusher);

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
        me._$container.addClass(me.effect.cls);

        me.$el.on('touchstart.' + pluginName, function(event) {
            event.stopPropagation();
            event.preventDefault();

            me._$container.addClass(opts.menuOpenCls);
        });

        me._$container.on('touchstart.' + pluginName, function(event) {
            event.stopPropagation();
            event.preventDefault();

            me._$container.removeClass(opts.menuOpenCls);
        });
    };

    Plugin.prototype.destroy = function() {
        var me = this;

        me.$el.off('touchstart.' + pluginName);
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

    $(function() {
        $('.entry--menu-left').offcanvasMenu();
    });

})(jQuery, window, document);