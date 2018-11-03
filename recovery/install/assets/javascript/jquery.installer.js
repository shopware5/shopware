;
/**
 * Plugin to check the database configuration and automatically display the available database tables
 */
(function ($, window, document, undefined) {
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

    var pluginName = 'ajaxDatabaseSelection',
            defaults = {
                url: 'your-url.json'
            };

    function Plugin(element, options) {

        this.$el = $(element);
        this.opts = $.extend({}, defaults, options);

        this._defaults = defaults;
        this._name = pluginName;

        this.init();
    }

    Plugin.prototype.init = function () {
        var me = this,
                $el = me.$el;

        $el.on('focus', $.proxy(me.onFocus, me));
    };

    Plugin.prototype.onFocus = function () {
        var me = this,
                $el = me.$el,
                url = $el.attr('data-url') || me.opts.url;

        $.ajax({
            method: 'post',
            url: url,
            data: $el.parents('form').serialize(),
            dataType: 'json',
            success: $.proxy(me.onSuccess, me)
        });
    };

    Plugin.prototype.onSuccess = function (data) {
        if (data.length === 0) {
            return;
        }

        var me = this,
                oldValue = me.$el.val() || '',
                fieldName = me.$el.attr('name'),
                opts = me.createSelectOptions(data, oldValue),
                select;

        select = $('<select>', {
            'name': fieldName,
            'class': 'js--database-selection',
            'html': opts.join('')
        });

        me.$el.replaceWith(select);
        select.trigger('focus');
    };

    Plugin.prototype.createSelectOptions = function (data, oldValue) {
        var me = this,
                opts = [];

        $.each(data, function (i, item) {
            if (oldValue === item.value) {
                opts.push(format('<option selected value="%0">%1</option>', item.value, item.display));
            } else {
                opts.push(format('<option value="%0">%1</option>', item.value, item.display));
            }

        });

        return opts;
    };

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
                        new Plugin(this, options));
            }
        });
    };

    $(function () {
        $('*[data-ajaxDatabaseSelection="true"]').ajaxDatabaseSelection();
    })
})(jQuery, window, document);

/**
 * Plugin for the installation process to update the status and progressbar
 */
;(function ($, window, undefined) {
    "use strict";

    var backButtonBlocked = false,
        progressConfig = [
        {
            requestUrl: 'importDatabase',
            counterText: shopwareTranslations.counterTextMigrations
        },
        {
            requestUrl: 'importSnippets',
            counterText: shopwareTranslations.counterTextMigrations,
            finalFcnt: function () {
                $('.btn-primary, .counter-content').removeClass('is--hidden');
                $('#back').removeClass('disabled');
                $('.progress').removeClass('progress-info').addClass('progress-success').removeClass('active');
                $('.progress .progress-bar, .install-buttons').width("100%");
                $('#start-ajax, .counter-numbers').hide();
                $(window).unbind('beforeunload');
                refreshCounterText(2, shopwareTranslations.updateSuccess, false);
                backButtonBlocked = false;
            }
        }
    ], counter = 1, configLen = progressConfig.length;

    var format = function (str) {
        for (var i = 1; i < arguments.length; i++) {
            str = str.replace('%' + (i - 1), arguments[i]);
        }
        return str;
    };

    var refreshCounterText = function (step, stepText, showSuffix) {
        var len = configLen, suffix, container = $('.counter-container');

        showSuffix = (showSuffix !== undefined) ? showSuffix : true;
        suffix = (showSuffix) ? '...' : '';

        container.find('.counter-numbers').html(format('%0 / %1', step, len));
        container.find('.counter-content').html(stepText + suffix);

        return true;
    };

    var startProgress = function (config) {
        var currentConfig = config.shift(),
                progressBar = $('.progress .progress-bar');

        $('.progress').addClass('active');

        progressBar.width("0%");
        refreshCounterText(counter, currentConfig.counterText || '');
        counter++;

        currentConfig.maxCount = 0;
        doRequest(0, currentConfig, config);
    };

    var doRequest = function (offset, currentConfig, config) {
        var maxCount = currentConfig.maxCount,
                progressBar = $('.progress .progress-bar');

        $.ajax({
            url: currentConfig.requestUrl,
            data: { offset: offset, totalCount: currentConfig.maxCount }
        }).done(function (data) {
            if (!data.success) {
                $('.alert-error').show().html('<h2>Error</h2>');
                if (data.errorMsg) {
                    $('.alert-error').append("Received the following error message:<br/>" + data.errorMsg);
                }
                $('.alert-error').append("<br><br>Please try to fix this error and restart the update.");
                $('.alert-error').append("<h3>Response</h3><pre>" + JSON.stringify(data) + "</pre>");

                return;
            }

            offset = data.offset;
            if (data.totalCount) {
                maxCount = data.totalCount;
                currentConfig.maxCount = maxCount;
            }

            var progress = offset / maxCount * 100;

            progress = progress + "%";
            progressBar.width(progress);

            if (data.valid) {
                doRequest(offset, currentConfig, config);
            } else {
                if (config.length > 0) {
                    startProgress(config);
                } else {
                    currentConfig.finalFcnt();
                }
            }
        });
    };

    $(document).ajaxError(function (event, jqxhr, settings, exception) {
        $('.alert-error').show().html('<h2>Error</h2> Received an error message.<br><strong>URL:</strong> ' + settings.url + '<br><strong>Message:</strong> ' + exception + "<br><br>Please try to fix this error and restart the update.");
        $('.alert-error').append("<h3>Response</h3>");
        $('.alert-error').append("<pre>" + jqxhr.responseText + "</pre>");
        return;
    });

    $(document).ready(function () {
        // Set js class on the html tag
        $('html').removeClass('no-js').addClass('js');

        $('#start-ajax').click(function () {
            startProgress(progressConfig);
            $('#start-ajax').prop('disabled', true);
            $('#back').addClass('disabled');
            $('#back').on('click', function (event) {
                if (backButtonBlocked) {
                    event.preventDefault();
                }
            });
            backButtonBlocked = true;

            $('#skip-import').hide();

            $('.counter-container').removeClass('is--hidden').next('.progress-text').addClass('is--hidden');

            $(window).bind('beforeunload', function () {
                return 'A system update is running.';
            });
        });

        $('.language-selection').bind('change', function () {
            var $this = $(this),
                    form = $this.parents('form'),
                    action = form.find('.hidden-action').val();

            form.attr('action', action).trigger('submit');
        });

        $('.btn-primary').bind('click', function (event) {
            var $this = $(this),
                    form = $this.parents('form');

            form.addClass('is--submitted');
        });

        $('input').bind('keyup', function () {
            var required = $(this).attr('required');
            if (required) {
                var $this = $(this);

                if (!$this.val().length) {
                    $this.removeClass('inline-success').addClass('inline-error');
                } else {
                    $this.removeClass('inline-error').addClass('inline-success');
                }
            }
        });

        var changeLogo = function() {
            var win = $(window),
                winWidth = win.width(),
                logo = $('.header-logo');

            if(winWidth <= 360) {
                logo.attr('src', logo.attr('data-small'));
            } else {
                logo.attr('src', logo.attr('data-normal'));
            }
        };

        $(window).on('resize', changeLogo);
        changeLogo();
    });
})(jQuery, window);

(function ($, undefined) {
    "use strict";

    $('a[data-shown][data-hidden]').on('click.toggle', function () {
        var $element = $(this.hash),
            $this = $(this);

        switch (true) {
            case $element.hasClass('is--hidden'):
                $element.removeClass('is--hidden');
                $this.html($this.attr('data-shown'));
                break;

            case $element.hasClass('hide-successful'):
                $element.removeClass('hide-successful');
                $this.html($this.attr('data-shown'));
                break;

            case $element.attr('data-hide-successful'):
                $element.addClass('hide-successful');
                break;

            default:
                $element.addClass('is--hidden');
                $this.html($this.attr('data-hidden'));
        }

        $('html, body').animate({
            scrollTop: $element.offset().top - 50 // Offset to show headline
        }, 750);
    });

    $('input[type=checkbox].toggle, input[type=radio].toggle').on('change.toggle', function () {
        $($(this).attr('data-href')).toggleClass('is--hidden');
    });

    $('input[type=checkbox].removeElem, input[type=radio].removeElem').on('change.removeElem', function () {
        $($(this).attr('data-href-remove')).remove();
    });
})(jQuery);
