(function ($, window) {
    /**
     * Global date picker component.
     * Renders a calender dialog to select a single date, multiple dates or a date range.
     * Is used on an input element which holds the value of the picker.
     * By default there will be generated a display value field and a hidden input field.
     * The display value field holds the alternate date format for better usability.
     */
    $.plugin('swDatePicker', {

        defaults: {

            /**
             * Modes:
             * single - A single date selection
             * multiple - Select multiple dates in one picker
             * range - Select a date range in one picker
             */
            mode: 'single',

            /**
             * If true, dates will be parsed, formatted, and displayed in UTC.
             * Pre loading date strings with timezones is recommended but not necessary.
             */
            utc: false,

            /**
             * Wrap: see https://chmln.github.io/flatpickr/options/
             */
            wrap: false,

            /**
             * Position the calendar inside the wrapper and next to the input element.
             */
            'static': false,

            /**
             * Enables week numbers
             */
            weekNumbers: false,

            /**
             * Allow manual datetime input
             */
            allowInput: false,

            /**
             * Clicking on input opens the date picker.
             * Disable if you wish to open the calendar manually with the open() method.
             */
            clickOpens: true,

            /**
             * Display time picker in 24 hour mode
             */
            time_24hr: true,

            /**
             * Enables the time picker functionality
             */
            enableTime: false,

            /**
             * Set to true to hide the calendar.
             * Use for a time picker along with enableTime.
             */
            noCalendar: false,

            /**
             * More date format chars at https://chmln.github.io/flatpickr/#dateformat
             */
            dateFormat: 'Y-m-d',

            /**
             * The date format for the time.
             * Is added to dateFormat when enableTime option is set to true.
             */
            timeFormat: ' H:i:S',

            /**
             * Hides the original input and creates a new one for a different display value.
             */
            altInput: true,

            /**
             * The name attribute of an additional input field for storing the single start value of a range.
             * Only working with mode "range".
             */
            rangeStartInput: null,

            /**
             * The name attribute of an additional input field for storing the single end value of a range.
             * Only working with mode "range".
             */
            rangeEndInput: null,

            /**
             * The created altInput element will have this class.
             */
            altInputClass: 'flatpickr-input form-control input',

            /**
             * Used as the displayed value when altInput is set to true.
             */
            altFormat: 'F j, Y',

            /**
             * Used as the displayed value when altInput is set to true.
             */
            altTimeFormat: ' - H:i',

            /**
             * Define the symbol which is used to separate multiple dates.
             * Only necessary for mode "multiple".
             * The default separator of the flatpickr is ";".
             */
            multiDateSeparator: null,

            /**
             * Either a date string or a date object.
             * Used for initial value.
             */
            defaultDate: null,

            /**
             * The minimum date that user can pick (inclusive).
             */
            minDate: null,

            /**
             * The maximum date that user can pick (inclusive).
             */
            maxDate: null,

            /**
             * Define an array of dates which can be selected.
             * You can also pass a coma separated list via data attribute.
             * All other dates are disabled.
             */
            enabledDates: null,

            /**
             * Date parser that transforms a given string to a date object.
             */
            parseDate: null,

            /**
             * Submit the parent form of the date picker input on date change.
             */
            autoSubmit: false
        },

        init: function (el, options) {
            var me = this,
                globalConfig = window.datePickerGlobalConfig || {};

            /**
             * The defaults are additionally set by global configs including localization.
             */
            me.opts = $.extend({}, me.defaults, globalConfig, options);

            me.applyDataAttributes(true);

            /**
             * Holds the suspend events status.
             */
            me.suspended = false;

            /**
             * Fix for the flatpickr plugin to handle datetime formatting correctly.
             */
            if (me.opts.enableTime) {
                me.opts.dateFormat = me.opts.dateFormat + me.opts.timeFormat;
                me.opts.altFormat = me.opts.altFormat + me.opts.altTimeFormat;
            }

            /**
             * On range mode the min and max values can be stored separately in additional hidden inputs.
             */
            if (me.opts.mode === 'range' && me.opts.rangeStartInput !== null) {
                me.$rangeStartInput = $('[name="' + me.opts.rangeStartInput + '"]');
            }

            if (me.opts.mode === 'range' && me.opts.rangeEndInput !== null) {
                me.$rangeEndInput = $('[name="' + me.opts.rangeEndInput + '"]');
            }

            me.initFlatpickr();
            me.registerEvents();

            $.publish('plugin/swDatePicker/onInit', [ me ]);
        },

        /**
         * Prepares the config for the flatpickr plugin and initializes it.
         */
        initFlatpickr: function () {
            var me = this,
                config = $.extend({}, me.opts);

            // Set basic value
            me.currentValue = me.$el.val();

            /**
             * Convert the initial value to flatpickr friendly format if custom separator is used.
             */
            if (me.opts.mode === 'multiple' && me.opts.multiDateSeparator !== null) {
                me.$el.val(me.convertMultiSeparatorToFlatpickr(me.$el.val()));
            }

            /**
             * Prepares the enabled dates.
             * You can also pass a coma separated list via data attribute.
             */
            if (me.opts.enabledDates !== null) {
                if (typeof me.opts.enabledDates === 'string') {
                    me.opts.enabledDates = me.opts.enabledDates.split(',');
                }

                config['enable'] = me.opts.enabledDates;
            }

            /**
             * Event handler api of the flatpickr plugin.
             */
            config['onReady'] = $.proxy(me.onPickerReady, me);
            config['onChange'] = $.proxy(me.onPickerChange, me);
            config['onOpen'] = $.proxy(me.onPickerOpen, me);
            config['onClose'] = $.proxy(me.onPickerClose, me);

            me.flatpickr = me.$el.flatpickr(config);

            /**
             * Convert value to custom separator after flatpickr was integrated.
             */
            if (me.opts.mode === 'multiple' && me.opts.multiDateSeparator !== null) {
                me.$el.val(me.convertMultiSeparator(me.$el.val()));
            }

            /**
             * Set the flatpickr range value from the separate min and max inputs.
             */
            if (me.opts.mode === 'range') {
                me.setDatePickerValFromInputs();
                me.setStartInputVal();
                me.setEndInputVal();
            }

            $.publish('plugin/swDatePicker/onInitFlatpickr', [ me, me.flatpickr, config ]);
        },

        registerEvents: function () {
            var me = this;

            me._on(me.$el, 'clear', $.proxy(me.onInputClear, me));
            me._on(me.$el, 'change', $.proxy(me.onInputChange, me));

            if (me.$rangeStartInput) {
                me._on(me.$rangeStartInput, 'clear', $.proxy(me.onRangeInputClear, me, me.$rangeStartInput));
                me._on(me.$rangeStartInput, 'change', $.proxy(me.onInputChange, me));
            }

            if (me.$rangeEndInput) {
                me._on(me.$rangeEndInput, 'clear', $.proxy(me.onRangeInputClear, me, me.$rangeEndInput));
                me._on(me.$rangeEndInput, 'change', $.proxy(me.onInputChange, me));
            }

            $.subscribe(me.getEventName('plugin/swOffcanvasMenu/onCloseMenu'), $.proxy(me.close, me));
            $.subscribe(me.getEventName('plugin/swOffcanvasMenu/onBeforeOpenMenu'), $.proxy(me.close, me));

            $.publish('plugin/swDatePicker/onRegisterEvents', [ me ]);
        },

        open: function () {
            var me = this;

            me.flatpickr.open();
        },

        close: function () {
            var me = this;

            me.flatpickr.close();
        },

        onInputClear: function () {
            var me = this;

            me.flatpickr.clear();

            $.publish('plugin/swDatePicker/onInputClear', [ me ]);
        },

        /**
         * Prevents change events on the inputs when events are suspended.
         */
        onInputChange: function (event) {
            var me = this;

            if (me.suspended) {
                event.preventDefault();
                event.stopPropagation();
                return false;
            }

            $.publish('plugin/swDatePicker/onInputChange', [ me ]);
        },

        /**
         * Clears the separate range input fields and resets the picker values.
         * Especially used via the clear event by the filter facets.
         */
        onRangeInputClear: function ($input) {
            var me = this;

            $input.val('');
            me.$el.trigger('change');

            me.setDatePickerValFromInputs();

            $.publish('plugin/swDatePicker/onRangeInputClear', [ me ]);
        },

        onPickerReady: function () {
            var me = this;

            $.publish('plugin/swDatePicker/onPickerReady', [ me ]);
        },

        onPickerChange: function () {
            var me = this;

            if (me.opts.mode === 'range') {
                me.setStartInputVal();
                me.setEndInputVal();
            }

            /**
             * Convert value to custom separator.
             */
            if (me.opts.mode === 'multiple' && me.opts.multiDateSeparator !== null) {
                me.$el.val(me.convertMultiSeparator(me.$el.val()));
            }

            $.publish('plugin/swDatePicker/onPickerChange', [ me ]);
        },

        onPickerOpen: function () {
            var me = this;

            me.currentValue = me.$el.val();

            $.publish('plugin/swDatePicker/onPickerOpen', [ me ]);
        },

        onPickerClose: function () {
            var me = this;

            /**
             * Submits the parent form when the autoSubmit option is set.
             */
            if (me.opts.autoSubmit && me.currentValue !== me.$el.val()) {
                me.$el.parents('form').submit();
            }

            me.$el.parent().find('input.flatpickr-input').blur();

            $.publish('plugin/swDatePicker/onPickerClose', [ me ]);
        },

        setStartInputVal: function (value) {
            var me = this;

            if (me.$rangeStartInput) {
                var val = value || me.flatpickr.selectedDates[0] || '',
                    altVal = val;

                if (val && val !== '') {
                    val = me.formatDate(val);
                    altVal = me.formatDate(altVal, me.opts.altFormat);
                }

                me.$rangeStartInput.val(val);

                /**
                 * Stores the visual display value in an additional data attribute.
                 */
                if (me.opts.altFormat) {
                    me.$rangeStartInput.attr('data-display-value', altVal);
                }
            }

            $.publish('plugin/swDatePicker/onSetStartInputVal', [ me ]);
        },

        setEndInputVal: function (value) {
            var me = this;

            if (me.$rangeEndInput) {
                var val = value || me.flatpickr.selectedDates[1] || '',
                    altVal = val;

                if (val && val !== '') {
                    val = me.formatDate(val);
                    altVal = me.formatDate(altVal, me.opts.altFormat);
                }

                me.$rangeEndInput.val(val);

                /**
                 * Stores the visual display value in an additional data attribute.
                 */
                if (me.opts.altFormat) {
                    me.$rangeEndInput.attr('data-display-value', altVal);
                }
            }

            $.publish('plugin/swDatePicker/onSetEndInputVal', [ me ]);
        },

        setDatePickerValFromInputs: function () {
            var me = this,
                values = [];

            if (me.$rangeStartInput && me.$rangeStartInput.val().length > 0) {
                values.push(me.$rangeStartInput.val());
            }

            if (me.$rangeEndInput && me.$rangeEndInput.val().length > 0) {
                values.push(me.$rangeEndInput.val());
            }

            me.flatpickr.setDate(values);

            $.publish('plugin/swDatePicker/onSetDatePickerValFromInputs', [ me ]);
        },

        getRangeStartValue: function () {
            var me = this;

            if (!me.$rangeStartInput) {
                return null;
            }

            $.publish('plugin/swDatePicker/onGetRangeStartValue', [ me ]);

            return me.$rangeStartInput.val();
        },

        getRangeEndValue: function () {
            var me = this;

            if (!me.$rangeEndInput) {
                return null;
            }

            $.publish('plugin/swDatePicker/onGetRangeEndValue', [ me ]);

            return me.$rangeEndInput.val();
        },

        /**
         * Suspend change events from firing on all picker input fields.
         */
        suspendEvents: function () {
            var me = this;

            me.suspended = true;

            $.publish('plugin/swDatePicker/onSuspendEvents', [ me ]);
        },

        /**
         * Resume change events firing on all picker input fields.
         */
        resumeEvents: function () {
            var me = this;

            me.suspended = false;

            $.publish('plugin/swDatePicker/onResumeEvents', [ me ]);
        },

        /**
         * Converts the submit value format from flatpickr with multi selection to system friendly format.
         */
        convertMultiSeparator: function (value) {
            var me = this;

            if (me.opts.multiDateSeparator === null) {
                return value;
            }

            var convertValue = value.split('; ').join(me.opts.multiDateSeparator);

            $.publish('plugin/swDatePicker/onConvertMultiSeparator', [ me, convertValue ]);

            return convertValue;
        },

        /**
         * Converts the submit value with multi selection back to flatpickr friendly format.
         */
        convertMultiSeparatorToFlatpickr: function (value) {
            var me = this;

            if (me.opts.multiDateSeparator === null) {
                return value;
            }

            var convertValue = value.split(me.opts.multiDateSeparator).join('; ');

            $.publish('plugin/swDatePicker/onConvertMultiSeparatorToFlatpickr', [ me, convertValue ]);

            return convertValue;
        },

        formatDate: function (date, dateFormat) {
            var me = this;

            if (!date) {
                return false;
            }

            dateFormat = dateFormat || me.opts.dateFormat;

            var formattedDate = me.flatpickr.formatDate(date, dateFormat);

            $.publish('plugin/swDatePicker/onFormatDate', [ me, formattedDate, dateFormat, date ]);

            return formattedDate;
        },

        destroy: function () {
            var me = this;

            $.unsubscribe(me.getEventName('plugin/swOffcanvasMenu/onCloseMenu'));
            $.unsubscribe(me.getEventName('plugin/swOffcanvasMenu/onBeforeOpenMenu'));

            me.flatpickr.destroy();

            me._destroy();

            $.publish('plugin/swDatePicker/onDestroy', [ me ]);
        }

    });
})(jQuery, window);
