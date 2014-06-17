;(function ($) {
    "use strict";

    $.modal = {
        $modalBox: null,
        $header: null,
        $title: null,
        $content: null,
        $closeButton: null,

        defaults: {
            mode: 'local',
            sizing: 'auto',
            width: 600,
            height: 600,
            overlay: true,
            closeOnOverlay: true,
            showCloseButton: true,
            animationSpeed: 500,
            title: '',
            src: ''
        },

        options: {},

        open: function (content, options) {
            var me = this,
                opts;

            me.options = opts = $.extend({}, me.defaults, options);

            if (opts.overlay) {
                $.overlay.open({
                    closeOnClick: opts.closeOnOverlay
                });

                $.overlay.removeListener('click.modal');

                if (opts.closeOnOverlay) {
                    $.overlay.addListener('click.modal', me.close.bind(me));
                }
            }

            if (me.$modalBox === null) {
                me._initModalBox();
            }

            me.$closeButton.toggle(opts.showCloseButton);

            me.$modalBox.toggleClass('fixed', opts.sizing === 'fixed');

            me.$modalBox.toggleClass('no--header', opts.title.length === 0);

            me.setWidth(opts.width);
            me.setHeight(opts.height);
            me.setTitle(opts.title);

            me.$modalBox.show();

            switch (opts.mode) {
                case 'ajax':
                    me.$content.load(content);
                    me.options.src = content;
                    break;
                case 'iframe':
                    me.setContent('<iframe src="' + content + '"></iframe>');
                    me.options.src = content;
                    break;
                default:
                    me.setContent(content);
                    break;
            }

            me.setTransition({
                opacity: 1
            }, me.options.animationSpeed, 'linear');
        },

        close: function () {
            var me = this,
                opts = me.options;

            if (opts.overlay) {
                $.overlay.close();
            }

            if (me.$modalBox !== null) {
                me.setTransition({
                    opacity: 0
                }, me.options.animationSpeed, 'linear', function () {
                    me.$content.html('');
                    me.$modalBox.hide();
                });
            }
        },

        _initModalBox: function () {
            var me = this;

            me.$modalBox = $('<div>', {
                'class': 'js--modal'
            });

            me.$header = $('<div>', {
                'class': 'header'
            }).appendTo(me.$modalBox);

            me.$title = $('<div>', {
                'class': 'title'
            }).appendTo(me.$header);

            me.$content = $('<div>', {
                'class': 'content'
            }).appendTo(me.$modalBox);

            me.$closeButton = $('<div>', {
                'class': 'btn icon--cross is--small btn--grey modal--close'
            }).appendTo(me.$modalBox);

            me.$closeButton.on('click.modal', me.close.bind(me));

            $('body').append(me.$modalBox);

            StateManager.registerListener({
                type: 'smartphone',
                enter: function() {
                    me.$modalBox.addClass('is--fullscreen');
                },
                exit: function () {
                    me.$modalBox.removeClass('is--fullscreen');
                }
            });
        },

        setTransition: function (css, duration, animation, callback) {
            var me = this,
                opts = $.extend({
                    animation: 'ease',
                    duration: me.options.animationSpeed
                }, {
                    animation: animation,
                    duration: duration
                });

            if (!$.support.transition) {
                me.$modalBox.animate(css, opts.duration, opts.animation, callback);
                return;
            }

            me.$modalBox.transition(css, opts.duration, opts.animation, callback);
        },

        setTitle: function (title) {
            var me = this;

            me.$title.html(title);
        },

        setContent: function (content) {
            var me = this;

            me.$content.html(content);
        },

        setWidth: function (width) {
            var me = this;

            me.$modalBox.css('width', ~~(width));
        },

        setHeight: function (height) {
            var me = this;

            me.$modalBox.css('height', ~~(height));
        }
    }
})(jQuery);

