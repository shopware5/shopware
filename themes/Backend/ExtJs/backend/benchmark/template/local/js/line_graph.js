'use strict';

var globals  = {
    gridLineColor: '#314252',
    shopColor: '#34DCDD',
    businessColor: '#6A63FC'
};

function BenchmarkGraph(el) {
    this.el = el;
    this.name = el.getAttribute('data-name');
    this.time = el.getAttribute('data-time');
    this.includeBusiness = (el.getAttribute('data-include-business') === 'true');

    this.init();
}

BenchmarkGraph.prototype.init = function() {
    this.initDefaultConfig();
    this.initTimeRangeLabel();

    new Chart(this.el.getContext('2d'), this.buildConfig());
};

BenchmarkGraph.prototype.initDefaultConfig = function () {
    Chart.defaults.global.defaultFontColor = '#798EA3';
    Chart.defaults.global.defaultFontFamily = 'Brandon';
};

BenchmarkGraph.prototype.initTimeRangeLabel = function () {
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

BenchmarkGraph.prototype.buildConfig = function() {
    return {
        type: 'line',
        data: {
            labels: window.benchmarkData['local'][this.time].labels,
            datasets: this.getDatasets()
        },
        options: this.getOptions()
    };
};

BenchmarkGraph.prototype.getDatasets = function() {
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

    if (this.includeBusiness) {
        dataSets.push({
            //TODO: Translations?
            label: 'Branche',
            backgroundColor: globals.businessColor,
            borderColor: globals.businessColor,
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

BenchmarkGraph.prototype.getOptions = function() {
    return {
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
        new BenchmarkGraph(this);
    });
};

$(function() {
    $('*[data-benchmark-graph="true"]').benchmarkGraph();
});

