(function ($) {
    'use strict';

    var globals  = {
        gridLineColor: '#314252',
        shopColor: '#34DCDD',
        industryColor: '#6A63FC'
    };

    function BenchmarkChart(el) {
        this.el = el;
        this.name = el.getAttribute('data-name');
        this.time = el.getAttribute('data-time');
        this.includeIndustry = (el.getAttribute('data-include-industry') === 'true');
        this.chartType = el.getAttribute('data-chart-type');
        this.translations = window.benchmarkTranslations;

        this.init();
    }

    BenchmarkChart.prototype.init = function() {
        this.initDefaultConfig();
        this.initTimeRangeLabel();

        var uniqueChartString = this.name + this.time.charAt(0).toUpperCase() + this.time.slice(1) + 'Chart';
        window[uniqueChartString] = new Chart(this.el.getContext('2d'), this.buildConfig());
    };

    BenchmarkChart.prototype.initDefaultConfig = function () {
        Chart.defaults.global.defaultFontColor = '#798EA3';
        Chart.defaults.global.defaultFontFamily = 'Source Sans Pro';
    };

    BenchmarkChart.prototype.initTimeRangeLabel = function () {
        this.timeRangeLabel = '';

        if (this.time === 'weeks') {
            this.timeRangeLabel = this.translations[window.i18n.locale].timeUnitDays;
        }

        if (this.time === 'months') {
            this.timeRangeLabel = this.translations[window.i18n.locale].timeUnitDays;
        }

        if (this.time === 'years') {
            this.timeRangeLabel = this.translations[window.i18n.locale].timeUnitMonths;
        }
    };

    BenchmarkChart.prototype.buildConfig = function() {
        return {
            type: this.chartType,
            data: {
                labels: this.getTranslatedLabels(),
                datasets: this.getDatasets()
            },
            options: this.getOptions()
        };
    };

    BenchmarkChart.prototype.getDatasets = function() {
        var dataSets = [];

        // Shop data
        dataSets.push({
            label: this.translations[window.i18n.locale].shopTitle,
            backgroundColor: globals.shopColor,
            borderColor: globals.shopColor,
            borderWidth: '12',
            borderCapStyle: 'round',
            lineTension: 0,
            pointRadius: 0,
            data: window.benchmarkData['local'][this.time][this.name].values,
            fill: false
        });

        // Last year data
        dataSets.push({
            label: this.translations[window.i18n.locale].previousTitleShop,
            backgroundColor: globals.shopColor,
            borderColor: globals.shopColor,
            borderWidth: '3',
            borderCapStyle: 'round',
            lineTension: 0,
            pointRadius: 0,
            borderDash: [1, 8],
            data: window.benchmarkData['local']['lastYear'][this.time][this.name].values,
            fill: false
        });

        if (this.includeIndustry) {
            dataSets.push({
                label: this.translations[window.i18n.locale].industryTitle,
                backgroundColor: globals.industryColor,
                borderColor: globals.industryColor,
                borderWidth: '12',
                borderCapStyle: 'round',
                lineTension: 0,
                pointRadius: 0,
                data: window.benchmarkData['industry'][this.time][this.name].values,
                fill: false
            });

            dataSets.push({
                label: this.translations[window.i18n.locale].previousTitleIndustry,
                backgroundColor: globals.industryColor,
                borderColor: globals.industryColor,
                borderWidth: '3',
                borderCapStyle: 'round',
                lineTension: 0,
                pointRadius: 0,
                borderDash: [1, 8],
                data: window.benchmarkData['industry']['lastYear'][this.time][this.name].values,
                fill: false
            });
        }

        return dataSets;
    };

    BenchmarkChart.prototype.getOptions = function() {
        return {
            legend: {
                display: false
            },
            maintainAspectRatio: false,
            tooltips: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                xAxes: [{
                    offset: true,
                    scaleLabel: {
                        display: true,
                        labelString: this.timeRangeLabel
                    },
                    gridLines: {
                        color: globals.gridLineColor
                    },
                    ticks: {
                        fontSize: 16,
                        fontColor: '#fff',
                    }
                }],
                yAxes: [{
                    gridLines: {
                        drawBorder: false,
                        color: globals.gridLineColor,
                        zeroLineColor: globals.gridLineColor,
                    },
                    ticks: {
                        suggestedMin: 0,
                        fontSize: 14,
                        fontColor: '#798EA3',
                        maxTicksLimit: 6
                    }
                }]
            },
        };
    };

    BenchmarkChart.prototype.getTranslatedLabels = function () {
        var labels = window.benchmarkData['local'][this.time].labels,
            translations = window.benchmarkTranslations[window.i18n.locale],
            translatedLabels = [];

        labels.forEach(function (val) {
            if (!translations[val]) {
                translatedLabels.push(val);
                return;
            }

            translatedLabels.push(translations[val]);
        });

        return translatedLabels;
    };

    $.fn.benchmarkGraph = function() {
        return this.each(function() {
            var $el = $(this),
                plugin;

            if ($el.data('plugin_benchmarkGraph')) {
                return;
            }

            plugin = new BenchmarkChart(this);
            $el.data('plugin_benchmarkGraph', plugin);
        });
    };

    $(function() {
        $('*[data-benchmark-graph="true"]').benchmarkGraph();
    });
})(jQuery);
