;(function($, window, document, undefined) {
    'use strict';

    var $window = $(window),
        $document = $(document);

    /**
     * Rounds the given value to the chosen base.
     *
     * Example: 5.46 with a base of 0.5 will round to 5.5
     *
     * @param value
     * @param base
     * @param method | round / floor / ceil
     * @returns {number}
     */
    function round(value, base, method) {
        var rounding = method || 'round',
            b = base || 1,
            factor = 1 / b;

        return Math[rounding](value * factor) / factor;
    }

    /**
     * Rounds an integer to the next 5er brake
     * based on the sum of digits.
     *
     * @param value
     * @param method
     * @returns {number}
     */
    function roundPretty(value, method) {
        var rounding = method || 'round',
            digits = countDigits(value),
            step = (digits > 1) ? 2 : 1,
            base = 5 * Math.pow(10, digits - step);

        return round(value, base, rounding);
    }

    /**
     * Get the sum of digits before the comma of a number.
     *
     * @param value
     * @returns {number}
     */
    function countDigits(value) {
        return ~~(Math.log(Math.floor(value)) / Math.LN10 + 1);
    }

    /**
     * Clamps a number between a min and a max value.
     *
     * @param value
     * @param min
     * @param max
     * @returns {number}
     */
    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    /**
     * Converts a value to an integer.
     *
     * @param value
     * @returns {Number}
     */
    function int(value) {
        return parseFloat(value);
    }

    $.plugin('rangeSlider', {

        defaults: {
            /**
             * The css class for the range slider container element.
             */
            sliderContainerCls: 'range-slider--container',

            /**
             * The css class for the range bar element.
             */
            rangeBarCls: 'range-slider--range-bar',

            /**
             * The css class for the handle elements at the start and end of the range bar.
             */
            handleCls: 'range-slider--handle',

            /**
             * The css class for the handle element at the min position.
             */
            handleMinCls: 'is--min',

            /**
             * The css class for the handle element at the max position.
             */
            handleMaxCls: 'is--max',

            /**
             * The css class for active handle elements which get dragged.
             */
            activeDraggingCls: 'is--dragging',

            /**
             * The selector for the hidden input field which holds the min value.
             */
            minInputElSelector: '*[data-range-input="min"]',

            /**
             * The selector for the hidden input field which holds the max value.
             */
            maxInputElSelector: '*[data-range-input="max"]',

            /**
             * The selector for the label which displays the min value.
             */
            minLabelElSelector: '*[data-range-label="min"]',

            /**
             * The selector for the label which displays the max value.
             */
            maxLabelElSelector: '*[data-range-label="max"]',

            /**
             * The selector for the element which holds the currency format.
             */
            currencyHelperSelector: '*[data-range-currency]',

            /**
             * The min value which the slider should show on start.
             */
            startMin: 20,

            /**
             * The max value which the slider should show on start.
             */
            startMax: 80,

            /**
             * The minimal value you can slide to.
             */
            rangeMin: 0,

            /**
             * The maximum value you can slide to.
             */
            rangeMax: 100,

            /**
             * The number of steps the slider is divided in.
             */
            stepCount: 20
        },

        init: function() {
            var me = this;

            me.applyDataAttributes();

            me.$minInputEl = me.$el.find(me.opts.minInputElSelector);
            me.$maxInputEl = me.$el.find(me.opts.maxInputElSelector);

            me.$minLabel = me.$el.find(me.opts.minLabelElSelector);
            me.$maxLabel = me.$el.find(me.opts.maxLabelElSelector);

            me.$currencyHelper = me.$el.find(me.opts.currencyHelperSelector);
            me.currencyFormat = me.$currencyHelper.attr('data-range-currency');

            me.dragState = false;
            me.dragType = 'min';

            me.createSliderTemplate();

            me.computeBaseValues();
            me.registerEvents();
        },

        registerEvents: function() {
            var me = this;

            me._on(me.$minHandle, 'mousedown touchstart', $.proxy(me.onStartDrag, me, 'min', me.$minHandle));
            me._on(me.$maxHandle, 'mousedown touchstart', $.proxy(me.onStartDrag, me, 'max', me.$maxHandle));

            me._on($document, 'mouseup touchend', $.proxy(me.onEndDrag, me));
            me._on($document, 'mousemove', $.proxy(me.slide, me));
            me._on($document, 'touchmove', $.proxy(me.slide, me));
        },

        createSliderTemplate: function() {
            var me = this;

            me.$rangeBar = me.createRangeBar();
            me.$container = me.createRangeContainer();

            me.$minHandle = me.createHandle('min');
            me.$maxHandle = me.createHandle('max');

            me.$minHandle.appendTo(me.$rangeBar);
            me.$maxHandle.appendTo(me.$rangeBar);
            me.$rangeBar.appendTo(me.$container);
            me.$container.prependTo(me.$el);
        },

        createRangeContainer: function() {
            var me = this;

            return $('<div>', {
                'class': me.opts.sliderContainerCls
            });
        },

        createRangeBar: function() {
            var me = this;

            return $('<div>', {
                'class': me.opts.rangeBarCls
            });
        },

        createHandle: function(type) {
            var me = this,
                typeClass = (type == 'max') ? me.opts.handleMaxCls : me.opts.handleMinCls;

            return $('<div>', {
                'class': me.opts.handleCls + ' ' + typeClass
            });
        },

        computeBaseValues: function() {
            var me = this;

            me.minRange = roundPretty(int(me.opts.rangeMin), 'floor');
            me.maxRange = roundPretty(int(me.opts.rangeMax), 'ceil');

            me.range = me.maxRange - me.minRange;
            me.stepSize = me.range / int(me.opts.stepCount);
            me.stepWidth = 100 / int(me.opts.stepCount);

            me.minValue = (me.opts.startMin == me.opts.rangeMin || me.opts.startMin <= me.minRange) ? me.minRange : int(me.opts.startMin);
            me.maxValue = (me.opts.startMax == me.opts.rangeMax || me.opts.startMax >= me.maxRange) ? me.maxRange : int(me.opts.startMax);

            me.setRangeBarPosition(me.minValue, me.maxValue);
            me.updateLayout();
        },

        setRangeBarPosition: function(minValue, maxValue) {
            var me = this,
                min = minValue || me.minValue,
                max = maxValue || me.maxValue,
                left = 100 / me.range * (min - me.minRange),
                width = 100 / me.range * (max - min);

            me.$rangeBar.css({
                'left': left + '%',
                'width': width + '%'
            });
        },

        getMin: function() {
            return this.minValue;
        },

        getMax: function() {
            return this.maxValue;
        },

        setMin: function(min, updateInput) {
            var me = this,
                update = updateInput || false;

            me.minValue = min;

            if (update) {
                me.updateMinInput(min);
            }

            me.setRangeBarPosition();
            me.updateLayout();

            $.publish('plugin/rangeSlider/changeMin', min);
        },

        setMax: function(max, updateInput) {
            var me = this,
                update = updateInput || false;

            me.maxValue = max;

            if (update) {
                me.updateMaxInput(max);
            }

            me.setRangeBarPosition();
            me.updateLayout();

            $.publish('plugin/rangeSlider/changeMax', max);
        },

        reset: function(param) {
            var me = this;

            if (param == 'max') {
                me.maxValue = me.maxRange;
                me.$maxInputEl.attr('disabled', 'disabled')
                    .val(me.maxRange)
                    .trigger('change');
            } else {
                me.minValue = me.minRange;
                me.$minInputEl.attr('disabled', 'disabled')
                    .val(me.minRange)
                    .trigger('change');
            }

            me.setRangeBarPosition();
            me.updateLayout();

            $.publish('plugin/rangeSlider/reset');
        },

        onStartDrag: function(type, $handle) {
            var me = this;

            $handle.addClass(me.opts.activeDraggingCls);

            me.dragState = true;
            me.dragType = type;
        },

        onEndDrag: function() {
            var me = this;

            if (me.dragState) {
                me.dragState = false;

                me.updateLayout();

                me.$minHandle.removeClass(me.opts.activeDraggingCls);
                me.$maxHandle.removeClass(me.opts.activeDraggingCls);

                if (me.dragType == 'max') {
                    me.updateMaxInput(me.maxValue);
                } else {
                    me.updateMinInput(me.minValue);
                }

                $(me).trigger('rangeChange', me);

                $.publish('plugin/rangeSlider/onChange', me);
            }
        },

        slide: function(event) {
            var me = this;

            if (me.dragState) {
               var pageX = (event.originalEvent.touches) ? event.originalEvent.touches[0].pageX : event.pageX,
                   offset = me.$container.offset(),
                   width = me.$container.innerWidth(),
                   mouseX = pageX - offset.left,
                   xPercent = clamp(round((100 / width * mouseX), me.stepWidth, 'round'), 0, 100),
                   value = (me.range / 100 * xPercent) + me.minRange;

               event.preventDefault();

               if (me.dragType == 'max') {
                   me.setMax(clamp(value, me.minValue + me.stepSize * 2, me.maxRange));
               } else {
                   me.setMin(clamp(value, me.minRange, me.maxValue - me.stepSize * 2));
               }
            }
        },

        updateMinInput: function(value) {
            var me = this;

            if (me.$minInputEl.length) {
                me.$minInputEl.val(value)
                    .removeAttr('disabled')
                    .trigger('change');
            }
        },

        updateMaxInput: function(value) {
            var me = this;

            if (me.$maxInputEl.length) {
                me.$maxInputEl.val(value)
                    .removeAttr('disabled')
                    .trigger('change');
            }
        },

        updateMinLabel: function(value) {
            var me = this;

            if (me.$minLabel.length) {
                me.$minLabel.html(me.formatPrice(value));
            }
        },

        updateMaxLabel: function(value) {
            var me = this;

            if (me.$maxLabel.length) {
                me.$maxLabel.html(me.formatPrice(value));
            }
        },

        updateLayout: function(minValue, maxValue) {
            var me = this,
                min = minValue || me.minValue,
                max = maxValue || me.maxValue;

            me.updateMinLabel(min);
            me.updateMaxLabel(max);
        },

        formatPrice: function(value) {
            var me = this;

            if (me.currencyFormat == '') {
                return value;
            }

            value = Math.round(value * 100) / 100;
            value = value.toFixed(2);

            if (me.currencyFormat.indexOf('0.00') > 0) {
                value = me.currencyFormat.replace('0.00', value);
            } else {
                value = value.replace('.', ',');
                value = me.currencyFormat.replace('0,00', value);
            }

            return value;
        },

        destroy: function() {
            var me = this;

            me._destroy();
        }
    });
})(jQuery, window, document, undefined);