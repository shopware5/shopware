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
        Chart.defaults.global.defaultFontFamily = 'Brandon';
    };

    BenchmarkChart.prototype.initTimeRangeLabel = function () {
        this.timeRangeLabel = '';

        //TODO: Translations?
        if (this.time === 'weeks') {
            this.timeRangeLabel = 'Tage';
        }

        if (this.time === 'months') {
            this.timeRangeLabel = 'KW';
        }

        if (this.time === 'years') {
            this.timeRangeLabel = 'Monate';
        }
    };

    BenchmarkChart.prototype.buildConfig = function() {
        return {
            type: this.chartType,
            data: {
                labels: window.benchmarkData['local'][this.time].labels,
                datasets: this.getDatasets()
            },
            options: this.getOptions()
        };
    };

    BenchmarkChart.prototype.getDatasets = function() {
        var dataSets = [];

        // Shop data
        dataSets.push({
            //TODO: Translations?
            label: 'Shop',
            backgroundColor: globals.shopColor,
            borderColor: globals.shopColor,
            borderWidth: '12',
            borderCapStyle: 'round',
            lineTension: 0,
            pointRadius: 0,
            data: window.benchmarkData['local'][this.time][this.name].values,
            fill: false
        });

        if (this.includeIndustry) {
            dataSets.push({
                //TODO: Translations?
                label: 'Branche',
                backgroundColor: globals.industryColor,
                borderColor: globals.industryColor,
                borderWidth: '12',
                borderCapStyle: 'round',
                lineTension: 0,
                pointRadius: 0,
                data: window.benchmarkData['industry'][this.time][this.name].values,
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
                        fontSize: 14,
                        fontColor: '#798EA3',
                        maxTicksLimit: 6
                    }
                }]
            },
        };
    };

    $.fn.benchmarkGraph = function() {
        return this.each(function() {
            var $el = $(this);

            if ($el.data('plugin_benchmarkGraph')) {
                return;
            }

            var plugin = new BenchmarkChart(this);
            $el.data('plugin_benchmarkGraph', plugin);

        });
    };

    $(function() {
        $('*[data-benchmark-graph="true"]').benchmarkGraph();
    });
})(jQuery);

