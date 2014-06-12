;(function ($) {
    $.plugin('ajaxModal', {
        init: function () {
            var me = this;

            me._on(me.$el, 'click', me.onClick.bind(me));
        },

        onClick: function (event) {
            var me = this,
                data = $.extend({
                    mode: 'ajax'
                }, me.$el.data());

            switch (data.mode) {
                case 'ajax':
                    $.modal.open(me.$el.attr('href'), data);
                    break;
                case 'iframe':
                    $.modal.open(me.$el.attr('src'), data);
                    break;
                case 'content':
                default:
                    $.modal.open(me.$el.html(), data);
                    break;
            }

            event.preventDefault();
        }
    });

    $.modal = {
        $modalBox: null,
        $header: null,
        $title: null,
        $content: null,
        $closeButton: null,

        defaults: {
            mode: 'local',
            width: 600,
            height: 600,
            overlay: true,
            nonClosing: true,
            animationSpeed: 500,
            title: ''
        },

        options: {},

        open: function (content, options) {
            var me = this,
                opts;

            me.options = opts = $.extend({}, me.defaults, options);

            if (opts.overlay) {
                $.overlay.open({
                    closeOnClick: !opts.nonClosing
                });

                $.overlay.removeListener('click.modal');

                if (!opts.nonClosing) {
                    $.overlay.addListener('click.modal', me.close.bind(me));
                }
            }

            if (me.$modalBox === null) {
                me._initModalBox();
            }

            me.setWidth(opts.width, false);
            me.setHeight(opts.height, false);
            me.setTitle(opts.title);

            switch (opts.mode) {
                case 'ajax':
                    me.$content.load(content);
                    break;
                case 'iframe':
                    me.setContent('<iframe src="' + content + '">');
                    break;
                case 'local':
                default: me.setContent(content);
            }

            me.$modalBox.fadeIn(opts.animationSpeed);
        },

        setTitle: function (title) {
            var me = this;

            me.$title.html(title);
        },

        setContent: function (content) {
            var me = this;

            me.$content.html(content);
        },

        setWidth: function (width, animate) {
            var me = this,
                css = {
                    width: ~~(width),
                    marginLeft: ~~(width / 2) * -1
                };

            me.options.width = ~~(width);

            if(animate !== false) {
                me.$modalBox.transition(css, me.options.animationSpeed, 'ease');
                return;
            }

            me.$modalBox.css(css);
        },

        setHeight: function (height, animate) {
            var me = this,
                css = {
                    height: ~~(height),
                    marginTop: ~~(height / 2) * -1
                };

            me.options.height = ~~(height);

            if(animate !== false) {
                me.setTransition(css);
                return;
            }

            me.$modalBox.css(css);
        },

        _initModalBox: function () {
            var me = this,
                opts = me.options;

            me.$modalBox = $('<div>', {
                class: 'js--modal'
            });

            me.$header = $('<div>', {
                class: 'header'
            }).appendTo(me.$modalBox);

            me.$title = $('<div>', {
                class: 'title'
            }).appendTo(me.$header);

            me.$closeButton = $('<div>', {
                class: 'btn icon--cross is--small btn--grey modal--close'
            }).appendTo(me.$header);

            me.$closeButton.on('click', me.close.bind(me));

            me.$content = $('<div>', {
                class: 'content'
            }).appendTo(me.$modalBox);

            $('body').append(me.$modalBox);

            StateManager.registerListener({
                type: 'smartphone',
                enter: function() {
                    me.enterFullScreen(true);
                },
                exit: function () {
                    me.leaveFullScreen();
                }
            });
        },

        enterFullScreen: function (animate) {
            var me = this,
                css = {
                    height: '100%',
                    width: '100%',
                    marginTop: 0,
                    marginLeft: 0,
                    top: 0,
                    left: 0
                };

            if(animate !== false) {
                me.setTransition(css);
                return;
            }

            me.$modalBox.css(css);
        },

        setTransition: function (css, duration, animation) {
            var me = this;

            me.$modalBox.transition(css, duration || me.options.animationSpeed, animation || 'ease');
        },

        leaveFullScreen: function (animate) {
            var me = this,
                opts = me.options,
                width = ~~(opts.width),
                height = ~~(opts.height),
                css = {
                    width: width,
                    marginLeft: ~~(width / 2) * -1,
                    height: height,
                    marginTop: ~~(height / 2) * -1,
                    top: '50%',
                    left: '50%'
                };

            if(animate !== false) {
                me.$modalBox.transition(css, me.options.animationSpeed, 'ease');
                return;
            }

            me.$modalBox.css(css);
        },

        close: function () {
            var me = this,
                opts = me.options;

            if (opts.overlay) {
                $.overlay.close();
            }

            if (me.$modalBox !== null) {
                me.$modalBox.fadeOut(opts.animationSpeed, function () {
                    me.$content.html('');
                });
            }
        }
    }
})(jQuery);

