;(function($, undefined) {
    "use strict";

    $(document).ready(function() {
        // Set js class on the html tag
        $('html').removeClass('no-js').addClass('js');

        $('.language-selection').bind('change', function() {
            var $this = $(this),
                form = $this.parents('form'),
                action = form.find('.hidden-action').val();

            form.attr('action', action).trigger('submit');
        });

        /**
         * Due to the fact that the IE <= 9 are having problems
         * with the $.loading-plugin, we're providing the plugin
         * to non-IE users and IE users just get an disabled button
         * with an decreased opacity.
         */
        if(!$.browser.msie) {
            $('*[data-loading]').click(function(event) {
                event.preventDefault();

                var $this = $(this);

                if($this.attr('data-loading') === 'false') {
                    return false;
                }

                var text = $this.attr('data-loading-text');
                $.loading(text);
                $this.parents("form").trigger("submit");
            });
        } else {
            $('.step4 input:submit').bind('click', function() {
                var $this = $(this);

                $this.attr('disabled', 'disabled').css('opacity', '0.5');
            })
        }

        // Bind the tabs plugin to our tab navigation
        $('.navi-tabs').fancyTabs({
            content: 'section.content',
            innerContainer: 'section.content > .inner-container',
            scrollSpeed: 500
        });

        $('.primary').bind('click', function(event) {
            var $this = $(this),
                form = $this.parents('form');

            if(!$.checkForm(form)) {
                event.preventDefault();
                return false;
            }
        });

        $('.secondary').bind('click', function() {
            var active = $('.navi-tabs li.active'),
                prev = active.prev('li');

            prev.addClass('active');
        });

        $('input').bind('keyup', function() {
            var required = $(this).attr('required');
            if(required) {
                var $this = $(this);

                if(!$this.val().length) {
                    $this.removeClass('inline-success').addClass('inline-error');
                } else {
                    $this.removeClass('inline-error').addClass('inline-success');
                }
            }

            var active = $('.navi-tabs li.active'),
                next = active.next('li');

            next.removeClass('disabled');
        });
        $('select').bind('change', function() {
            if(!$.checkForm($(this).parents('form'))) {
                return false;
            }
            var active = $('.navi-tabs li.active'),
                next = active.next('li');

            next.removeClass('disabled');
        });

    });

    $.loading = function(text) {
        var loadingDiv = $('<div>', {
            'class': 'loading-mask',
            'html': text
        }).hide();

        var overlay = $('<div>', { 'class': 'overlay' }).hide();
        overlay.css('opacity', 0);

        loadingDiv.css({
            'width': 200,
            'margin-left': -100,
            'top': '50%',
            'left': '50%',
            'display': 'none',
            'opacity': 0,
            'position': 'fixed'
        });

        loadingDiv.close = function() {
            loadingDiv.fadeOut().hide();
            overlay.fadeOut().hide();
        };

        overlay.appendTo($('body')).show();
        overlay.animate({
            opacity: 0.2
        }, 350);

        loadingDiv.appendTo($('body')).show();
        loadingDiv.animate({
            opacity: 1
        }, 350);
    };

    $.checkForm = function(form) {
        var inputs = form.find('input'),
            selects = form.find('select'),
            success = true;

        $.each(inputs, function(i, input) {
            var $input = $(input);

            if(!success) { return false; }

            if($input.hasClass('allowBlank')) {
                return success;
            }

            if($input.val().length === 0) {
                success = false;
            }
        });

        $.each(selects, function(i, select) {
            var $select = $(select);

            if(!success) { return false; }

            if($select.hasClass('allowBlank')) {
                return false;
            }

            if($select.val().length === 0) {
                success = false;
            }
        });

        return success;
    };

    $.fn.fancyTabs = function(settings) {
        var config = {};

        /** Extend the default config with the passed user setting */
        if(settings) { $.extend(config, settings); }

        /** Return this to support jQuery's chaining */
        return this.each(function() {
            var $this = $(this);

            $this.find('li > a').bind('click', function(event) {
                var $link = $(this),
                    $item = $link.parent('li');

                if($item.hasClass('disabled')) {
                    event.preventDefault();
                    return false;
                }

                $this.find('li').removeClass('active');
                $item.addClass('active');
            });
        });
    };
})(jQuery);