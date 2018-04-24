'use strict';

function TimeSwitcher(el) {
    this.$el = $(el);
    this.timeScaleElements = this.$el.find('.timescale');
    this.graphContainer = this.$el.parent('form').find('.graph-container');
    this.valuesKey = this.$el.attr('data-values-key');
    this.computedDataContainer = this.$el.parent('form').find('.computed-data');

    this.init();
}

TimeSwitcher.prototype.init = function () {
    this.timeScaleElements.on('click', $.proxy(this.onClickEl, this));
};

/**
 * @param { Event } event
 */
TimeSwitcher.prototype.onClickEl = function (event) {
    var currentTarget = $(event.currentTarget),
        newCanvasTpl,
        $previousCanvasEl;
    if (currentTarget.hasClass('is-active')) {
        return;
    }

    this.timeScaleElements.removeClass('is-active');
    currentTarget.addClass('is-active');

    $previousCanvasEl = this.graphContainer.find('canvas');
    newCanvasTpl = this.getCanvas(this.valuesKey, currentTarget.attr('name'), $previousCanvasEl.attr('data-chart-type'));

    this.graphContainer.empty();
    this.graphContainer.append(newCanvasTpl);
    newCanvasTpl.benchmarkGraph();

    this.computedDataContainer.find('.time-container').hide();
    this.computedDataContainer.find('.time-container.' + currentTarget.attr('name')).show();
};

/**
 * @param { string } dataKey
 * @param { string } timeSpan
 * @returns { jQuery }
 */
TimeSwitcher.prototype.getCanvas = function (dataKey, timeSpan, chartType) {
    return $('<canvas></canvas>')
        .attr('height', 260)
        .attr('data-benchmark-graph', 'true')
        .attr('data-name', dataKey)
        .attr('data-include-business', false)
        .attr('data-time', timeSpan)
        .attr('data-chart-type', chartType);
};

$.fn.timeSwitcher = function() {
    return this.each(function() {
        new TimeSwitcher(this);
    });
};

$(function() {
    $('*[data-time-switcher="true"]').timeSwitcher();
});