(function ($) {
    'use strict';

    function LanguageSwitch(el) {
        this.$el = $(el);

        this.init();
    }

    LanguageSwitch.prototype.init = function () {
        this.$timeSwitchers = this.$el.find('.language');
        this.$timeSwitchers.on('click', $.proxy(this.onClickLanguage, this));
    };

    LanguageSwitch.prototype.onClickLanguage = function (event) {
        var $clickedEl = $(event.currentTarget);

        window.i18n.locale = $clickedEl.attr('data-language-key');
        this.$timeSwitchers.removeClass('active');
        $clickedEl.addClass('active');

        this.updateGraphs();
        this.updateLanguageInputs();

        this.createEvent();
    };

    /**
     * Updates the graphs since those need to be "re-rendered" once the labels have changed due to language switch.
     */
    LanguageSwitch.prototype.updateGraphs = function () {
        var me = this,
            timeUnits = ['weeks', 'months', 'years'],
            graphNames = [ 'turnOver', 'visitors', 'totalOrders', 'conversions' ],
            uniqueChartString,
            snippetName, translation, chart;

        timeUnits.forEach(function (time) {
            graphNames.forEach(function (name) {
                uniqueChartString = name + time.charAt(0).toUpperCase() + time.slice(1) + 'Chart';
                if (!window[uniqueChartString]) {
                    return;
                }
                chart = window[uniqueChartString];

                snippetName = 'timeUnitMonths';

                if (time === 'weeks' || time === 'months') {
                    snippetName = 'timeUnitDays'
                }

                translation = window.benchmarkTranslations[window.i18n.locale][snippetName];
                me.translateLabels(chart, window.benchmarkData['local'][time].labels);

                chart.scales['x-axis-0'].options.scaleLabel.labelString = translation;
                chart.update();
            });
        });
    };

    /**
     * @param { Chart } chart
     * @param { Array } labels
     */
    LanguageSwitch.prototype.translateLabels = function (chart, labels) {
        var translations = window.benchmarkTranslations[window.i18n.locale],
            translatedLabels = [];

        labels.forEach(function (val) {
            if (!translations[val]) {
                translatedLabels.push(val);
                return;
            }

            translatedLabels.push(translations[val]);
        });

        chart.data.labels = translatedLabels;
    };

    LanguageSwitch.prototype.updateLanguageInputs = function () {
        var $inputElements = $('input[name=lang]');

        $inputElements.each(function (index, item) {
            $(item).val(window.i18n.locale);
        });
    };

    LanguageSwitch.prototype.createEvent = function () {
        var event = new Event('languageSwitch');

        this.$el[0].dispatchEvent(event);
    };

    $.fn.languageSwitch = function () {
        return this.each(function() {
            var $el = $(this);

            if ($el.data('plugin_languageSwitch')) {
                return;
            }

            var plugin = new LanguageSwitch(this);
            $el.data('plugin_languageSwitch', plugin);
        });
    };

    $(function () {
        $('*[data-language-switch="true"]').languageSwitch();
    });

    var defaultLang = window.benchmarkDefaultLanguage,
        fallBack = defaultLang === 'de' ? 'en' : 'de';

    window.i18n = new VueI18n({
        fallbackLocale: fallBack,
        locale: defaultLang,
        messages: window.benchmarkTranslations
    });
})(jQuery);
