(function ($) {
    'use strict';

    function TimeSwitcher(el) {
        this.$el = $(el);
        this.timeScaleElements = this.$el.find('.timescale');
        this.graphWrapper = this.$el.parent('form').find('.graph-wrapper');
        this.valuesKey = this.$el.attr('data-values-key');
        this.computedDataContainer = this.$el.parent('form').find('.computed-data');
        this.buttonContainer = this.$el.parent('form').find('.button-container');
        this.translations = window.benchmarkTranslations;

        this.init();
    }

    TimeSwitcher.prototype.init = function () {
        this.timeScaleElements.on('click', $.proxy(this.onClickEl, this));
    };

    /**
     * @param { Event } event
     */
    TimeSwitcher.prototype.onClickEl = function (event) {
        var currentTarget = $(event.currentTarget);

        if (currentTarget.hasClass('is-active')) {
            return;
        }

        this.timeScaleElements.removeClass('is-active');
        currentTarget.addClass('is-active');

        this.initCanvas(currentTarget);
        this.initSwitchButtons(currentTarget);

        this.computedDataContainer.find('.time-container').hide();
        this.computedDataContainer.find('.time-container.' + currentTarget.attr('name')).show();
    };

    TimeSwitcher.prototype.initCanvas = function (target) {
        var $previousCanvasEl = this.graphWrapper.find('canvas'),
            includeIndustry = ($previousCanvasEl.attr('data-include-industry') === 'true'),
            newCanvasTpl = this.getCanvas(this.valuesKey, target.attr('name'), $previousCanvasEl.attr('data-chart-type'), includeIndustry);

        this.graphWrapper.empty();
        this.graphWrapper.append(newCanvasTpl);

        newCanvasTpl.benchmarkGraph();
    };

    TimeSwitcher.prototype.initSwitchButtons = function (target) {
        var shopButton = this.buildButtonTemplate(this.translations[window.i18n.locale].shopTitle, false, false, this.valuesKey, target.attr('name')),
            hasIndustry = !!(this.buttonContainer.find('.switch-industry').length),
            industryButton,
            lastYearButton = this.buildButtonTemplate(this.translations[window.i18n.locale].previousTitle, false, true, this.valuesKey, target.attr('name'));

        this.buttonContainer.empty();
        this.buttonContainer.append(shopButton);

        if (hasIndustry) {
            industryButton = this.buildButtonTemplate(this.translations[window.i18n.locale].industryTitle, true, false, this.valuesKey, target.attr('name'));
            this.buttonContainer.append(industryButton);

            industryButton.switchButton();
        }

        this.buttonContainer.append(lastYearButton);

        shopButton.switchButton();
        lastYearButton.switchButton();
    };

    /**
     * @param { string } dataKey
     * @param { string } timeSpan
     * @param { string } chartType
     * @param { bool } includeIndustry
     * @returns { jQuery }
     */
    TimeSwitcher.prototype.getCanvas = function (dataKey, timeSpan, chartType, includeIndustry) {
        return $('<canvas>', {
            'height': 260,
            'data-benchmark-graph': true,
            'data-name': dataKey,
            'data-include-industry': includeIndustry,
            'data-time': timeSpan,
            'data-chart-type': chartType
        });
    };

    /**
     * @param { string } label
     * @param { bool } industry
     * @param { bool } lastYear
     * @param { string } valuesKey
     * @param { string } time
     * @returns { jQuery }
     */
    TimeSwitcher.prototype.buildButtonTemplate = function (label, industry, lastYear, valuesKey, time) {
        var className = 'switch-shop',
            $rootEl,
            $labelEl,
            $inputEl,
            $sliderEl,
            $buttonLabel;

        if (industry) {
            className = 'switch-industry';
        }

        if (lastYear) {
            className = 'switch-last-year';
        }

        $rootEl = $('<div>', {
            'class': 'button-wrapper ' + className,
            'data-switch-button': 'true',
            'data-industry': industry + '',
            'data-values-key': valuesKey,
            'data-last-year': lastYear + '',
            'data-time': time
        });

        $inputEl = $('<input>', {
            'type': 'checkbox',
            'checked': 'checked'
        });

        $sliderEl = $('<span>', {
            'class': 'slider round'
        });

        $labelEl = $('<label>', {
            'class': 'switch'
        });

        $buttonLabel = $('<div>', {
            'class': 'buttonLabel',
            'html': label
        });

        $labelEl.append($inputEl);
        $labelEl.append($sliderEl);

        $rootEl.append($labelEl);
        $rootEl.append($buttonLabel);


        return $rootEl;
    };

    $.fn.timeSwitcher = function() {
        return this.each(function() {
            var $el = $(this);

            if ($el.data('plugin_timeSwitcher')) {
                return;
            }

            var plugin = new TimeSwitcher(this);
            $el.data('plugin_timeSwitcher', plugin);
        });
    };

    $(function() {
        $('*[data-time-switcher="true"]').timeSwitcher();
    });
})(jQuery);
