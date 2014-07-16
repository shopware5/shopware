;(function($, undefined) {
    "use strict";

    var progressConfig = [
        {
            requestUrl: 'importDatabase',
            counterText: shopwareTranslations.counterTextMigrations
        },
        {
            requestUrl: 'importSnippets',
            counterText: shopwareTranslations.counterTextMigrations,
            finalFcnt: function() {
                $('.primary').removeClass('invisible');
                $('.progress').removeClass('progress-info').addClass('progress-success').removeClass('active');
                $('.progress .bar').width("100%");
                $('#start-ajax').hide();
                $(window).unbind('beforeunload');
                refreshCounterText(2, shopwareTranslations.updateSuccess, false);
            }
        }
    ], counter = 1, configLen = progressConfig.length;

    var format = function(str) {
        for (var i = 1; i < arguments.length; i++) {
            str = str.replace('%' + (i - 1), arguments[i]);
        }
        return str;
    };

    var refreshCounterText = function(step, stepText, showSuffix) {
        var len = configLen, suffix, container = $('.counter-text');

        showSuffix = (showSuffix !== undefined) ? showSuffix : true;
        suffix = (showSuffix) ? '...' : '';

        container.find('.counter-numbers').html(format('%0 / %1', step, len));
        container.find('.counter-content').html(stepText + suffix);

        return true;
    };

    var startProgress = function(config) {
        var currentConfig = config.shift(),
            progressBar = $('.progress .bar');

        $('.progress').addClass('active');
        progressBar.width("0%");
        refreshCounterText(counter, currentConfig.counterText || '');
        counter++;

        currentConfig.maxCount = 0;
        doRequest(0, currentConfig, config);
    };

    var doRequest = function(offset, currentConfig, config) {
        var maxCount = currentConfig.maxCount,
            progressBar = $('.progress .bar');

        $.ajax({
            url: currentConfig.requestUrl,
            data: { offset: offset, totalCount: currentConfig.maxCount }
        }).done(function(data) {
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

    $(document).ajaxError(function(event, jqxhr, settings, exception) {
        $('.alert-error').show().html('<h2>Error</h2> Received an error message.<br><strong>URL:</strong> ' + settings.url + '<br><strong>Message:</strong> ' + exception + "<br><br>Please try to fix this error and restart the update.");
        $('.alert-error').append("<h3>Response</h3>");
        $('.alert-error').append("<pre>" + jqxhr.responseText + "</pre>");
        return;
    });

    $(document).ready(function() {
        // Set js class on the html tag
        $('html').removeClass('no-js').addClass('js');

        $('#start-ajax').click(function() {
            startProgress(progressConfig);
            $('#start-ajax').hide();
            $('.secondary').hide();
            $('.counter-text').removeClass('hidden').next('.progress-text').addClass('hidden');

            $(window).bind('beforeunload', function() {
                return 'A system update is running.';
            });
        });

        $('.language-selection').bind('change', function() {
            var $this = $(this),
                form = $this.parents('form'),
                action = form.find('.hidden-action').val();

            form.attr('action', action).trigger('submit');
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
})(jQuery);
