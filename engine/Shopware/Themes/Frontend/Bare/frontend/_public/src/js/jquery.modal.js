/**
 * AJAX Login Plugin
 * for Shopware
 *
 * Shopware AG (c) 2010
 */
(function ($) {

    //Default settings
    var config = {
        dataType: 'text',
        container: '.modal',
        headline: '',
        viewport: '',
        register: '',
        checkout: '',
        target: '',
        type: 'POST'
    };

    //Extends jQuery's function namespace
    $.fn.checkout = function (settings) {
        if (settings) {
            $.extend(config, settings);
        }

        $(config.container + ' form').live('submit', function (event) {
            if (!$(this).hasClass('new_customer_form')) {
                //event.preventDefault();
                return $.checkout.loginUser(this);
            }
        });
        $(config.container + ' .existing_customer input[type^=submit]').live('click', function (event) {
            //event.preventDefault();
            var form = $(config.container + ' form[name^=existing_customer]');
            return $.checkout.loginUser(form);
        });

        this.live('click', function (event) {
            event.preventDefault();
            $.checkout.checkUser(this.href);
        });

        return this;
    };


    //Extends jQuery's namespace
    $.checkout = {};

    //Checks the user
    $.checkout.checkUser = function (target, event) {
        config.target = target;
        $.ajax({
            'url': config.viewport,
            'dataType': 'jsonp',
            'type': config.type,
            'success': function (result) {
                if (result.length) {
                    var width = 530;
                    var position = 'fixed';

                    if ($.browser.msie && parseInt($.browser.version, 10) === 6) {
                        width = 530;
                        position = 'absolute';
                    }

                    $.modal(result, config.headline, {
                        'position': position,
                        'textClass': '',
                        'textContainer': '<div>',
                        'width': width + 'px'
                    }).find('.close').remove();

                    if ($.browser.msie) {
                        buttons = $('.modal').find('input[type^=submit]');
                        buttons.each(function () {
                            this.fireEvent('onmove');
                        });
                    }

                    // user is logged in
                } else {
                    window.location.href = target;
                }
            }
        });
    };

    //Checks if the user is logged in
    $.checkout.loginUser = function (form) {
        config.register = $.controller.register;
        var location = window.location.protocol + '//' + window.location.host;

        // Fix same origin miss match
        if (config.viewport.indexOf(location) !== 0 && $.browser.msie) {
            return;
        }
        $.ajax({
            url: config.viewport,
            data: $(form).serialize(),
            type: config.type,
            xhrFields: {
                withCredentials: true
            },
            success: function (result) {
                if (result.length) {
                    $(config.container).empty().html(result);
                } else {
                    window.location.href = config.target ? config.target : config.checkout;
                }
            },
            error: function (result) {
                window.location.href = config.target ? config.target : config.checkout;
            }
        });
        return false;
    };
})(jQuery);

/**
 * Modal Plugin
 * for Shopware
 *
 * Shopware AG (c) 2010
 */
(function ($) {
    var width = 660;
    var position = 'fixed';
    var modalConfig = {
        'position': position,
        'animationSpeed': 200,
        'width': width + 'px',
        'textContainer': '<div>',
        'textClass': 'ajax_add_article_container'
    };

    $(document).ready(function() {
        $('.modal_open a').on('click');
        $('.modal_open a').modal();

        $('body').on('click', '.modal_close', function() {
            event.preventDefault();
            $.modalClose();
        })

        $('.buybox--form').on('submit', function (event) {
            event.preventDefault();

            $.ajax({
                'data': $(this).serialize(),
                'dataType': 'jsonp',
                'url': $(this).attr('action'),
                'success': function (result) {

                    $.modal(result, '', modalConfig);
                    $('#lbOverlay').css('opacity', '0').show().fadeTo('fast', '0.3');

                    $('.modal .close').hide();
                }
            });
        });

        $('.action--buynow').on('click', function (event) {
            event.preventDefault();

            $.ajax({
                'dataType': 'jsonp',
                'url': $(this).attr('href'),
                'success': function (result) {
                    $.modal(result, '', modalConfig);
                    $('#lbOverlay').css('opacity', '0').show().fadeTo('fast', '0.3');

                    $('.modal .close').hide();
                }
            });
        });
    });

    //Default settings
    var config = {
        animationSpeed: 500,
        frameHeight: '500px',
        textClass: '',
        textContainer: '<p>',
        overlay: '#lbOverlay',
        overlayOpacity: '0.6',
        useOverlay: true,
        width: 500,
        height: 500
    };

    $.fn.modal = function () {
        return this.each(function () {
            var $this = $(this);

            $this.bind('click', function (event) {
                event.preventDefault();
                var me = $(this),
                    width = me.attr('data-modal-width'),
                    height = me.attr('data-modal-height');

                width = width || config.width;
                height = height || config.height;

                $.post(me.attr('href'), function (data) {
                    var modal = $.modal(data, '', {
                        'position':'fixed',
                        'width': ~~(1 * width),
                        'height': ~~(1 * height)
                    });

                    // Remove close icon
                    modal.find('.close').remove();
                });
            });
        });
    };

    //creates an modal window with text and headline
    $.modal = function (text, headline, settings) {
        if (settings) $.extend(config, settings);
        if ($('.modal')) $('.modal').remove();
        var modal = $('<div>', {
            'class': 'modal',
            'css': {
                'width': config.width,
                'top': '10%',
                'left': '50%',
                'background': '#fff',
                'border': '1px solid #c7c7c7'
            }
        });

        if(settings.width) { modal.css('width', settings.width); }

        if (headline.length) {
            var h2 = $('<h2>', {
                'html': headline
            }).appendTo(modal);
        }
        if (text.length) {
            var container = $(config.textContainer, {
                'html': text
            });

            if (config.textClass.length) {
                container.addClass(config.textClass)
            }
            container.appendTo(modal);
        }

        //get css properties
        modal.show();
        if(!config.position) {
            config.position = modal.css('position');
        }
        config.top = modal.css('top');
        modal.hide();


        modal.appendTo('body');

        if(settings.height) {
            modal.find('.inner_container').css('height', settings.height + 'px');
        }

        modal.show().css('marginLeft', -(modal.width()/2)).hide();

        if (config.useOverlay == true) {
            $.modal.overlay.fadeIn();

            $(config.overlay).bind('click', function (event) {
                $.modalClose();
            });
        }
        $('.modal').fadeIn('fast');

        if (config.position == 'absolute') {
            modal.css({
                'position': config.position,
                'bottom': 'auto'
            }).fadeIn(config.animationSpeed);
        } else if (config.position == 'fixed') {
            $('.modal').fadeIn();
            modal.css({
                'position': config.position,
                'top': -(modal.height() + 100) + 'px',
                'display': 'block'
            }).animate({
                'top': '40px'
            }, config.animationSpeed)
        }

        return modal
    };

    //Cloeses the current modal window
    $.modalClose = function () {
        if (config.useOverlay == true) {
            $.modal.overlay.fadeOut()
        }

        if (config.position == 'absolute') {
            $('.modal').fadeOut(config.animationSpeed)
        } else if (config.position == 'fixed') {
            $('.modal').animate({
                'top': -($('.modal').height() + 100) + 'px'
            }, config.animationSpeed)
        }
        $('.modal').fadeOut();
    };

    //Creates a modal window with an iframe and a headline
    $.modalFrame = function (url, headline, settings) {
        if (settings) $.extend(config, settings);
        config.animationSpeed = parseInt(config.animationSpeed);
        if ($('.modal')) $('.modal').remove();
        var modal = $('<div>', {
            'class': 'modal',
            'css': {
                'width': config.width,
                'left': '50%',
                'border': config.border,
                'padding': config.padding,
                'background': config.background,
                'display': 'none',
                'margin-left': -(parseInt(config.width) / 2)
            }
        });

        if (headline && headline.length) {
            var h2 = $('<h2>', {
                'text': headline
            }).appendTo(modal)

            if(settings.headlineCls) {
                h2.addClass(settings.headlineCls);
            }
        }
        if (url.length) {
            var div = $('<iframe>', {
                'src': url,
                'css': {
                    'height': config.frameHeight
                }
            }).appendTo(modal)
        }
        var close = $('<a>', {
            'html': 'Schlie&szlig;en',
            'class': 'close'
        }).appendTo(modal);
        close.bind('click', function (event) {
            event.preventDefault();
            if (config.position == 'absolute') {
                modal.fadeOut(config.animationSpeed)
            } else if (config.position == 'fixed') {
                modal.animate({
                    'top': -(modal.height() + 100) + 'px'
                }, config.animationSpeed)
            }
            if(config.useOverlay) {
                $.modal.overlay.fadeOut();
            }
        });

        if (config.useOverlay == true) {
            $.modal.overlay.fadeIn();

            $(config.overlay).bind('click', function (event) {
                $.modalClose();
            });
        }

        modal.appendTo('body');
        modal.fadeIn('fast');

        if($.browser.msie && parseInt($.browser.version) == 6) {
            $.ie6fix.open(modal, config);
        } else {
            if (config.position == 'absolute') {
                modal.css({
                    'position': config.position,
                    'bottom': 'auto'
                }).fadeIn(config.animationSpeed);
            } else if (config.position == 'fixed') {
                $('.modal').fadeIn();
                modal.css({
                    'position': config.position,
                    'top': -(modal.height() + 100) + 'px',
                    'display': 'block'
                }).animate({
                    'top': '40px'
                }, config.animationSpeed)
            }
        }

        return modal;
    };

    //Handles the modal overlay
    $.modal.overlay = {
        fadeIn: function () {
            $(config.overlay).css({
                'display': 'block',
                'opacity': '0'
            }).animate({
                'opacity': '0.4'
            }, 500)
        },
        fadeOut: function () {
            $(config.overlay).animate({
                'opacity': '0'
            }, 500).hide()
        }
    };
})(jQuery);

