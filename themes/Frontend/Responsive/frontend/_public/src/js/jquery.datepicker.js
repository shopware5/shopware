(function ($, window) {

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
             * Wrap: see https://chmln.github.io/flatpickr/#strap
             */
            wrap: false,

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
            timeFormat: ' H:i',

            /**
             * Hides the original input and creates a new one for a different display value.
             */
            altInput: true,

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
             * Date parser that transforms a given string to a date object.
             */
            parseDate: null,

            /**
             * Date formatter that transforms a given date object to a string, according to passed format.
             */
            formatDate: null,

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

            if (me.opts.enableTime) {
                me.opts.dateFormat = me.opts.dateFormat + me.opts.timeFormat;
                me.opts.altFormat = me.opts.altFormat + me.opts.altTimeFormat;
            }

            me.initFlatpickr();

            $.publish('plugin/swDatePicker/onInit', [ me ]);
        },

        initFlatpickr: function () {
            var me = this,
                config = $.extend({}, me.opts);

            config['onReady'] = $.proxy(me.onPickerReady, me);
            config['onChange'] = $.proxy(me.onPickerChange, me);
            config['onOpen'] = $.proxy(me.onPickerOpen, me);
            config['onClose'] = $.proxy(me.onPickerClose, me);

            me.flatpickr = me.$el.flatpickr(config);

            $.publish('plugin/swDatePicker/onInitFlatpickr', [ me, me.flatpickr, config ]);
        },

        open: function () {
            var me = this;

            me.flatpickr.open();
        },

        close: function () {
            var me = this;

            me.flatpickr.close();
        },

        onPickerReady: function () {
            var me = this;

            $.publish('plugin/swDatePicker/onPickerReady', [ me ]);
        },

        onPickerChange: function () {
            var me = this;

            $.publish('plugin/swDatePicker/onPickerChange', [ me ]);
        },

        onPickerOpen: function () {
            var me = this;

            me.currentValue = me.$el.val();

            $.publish('plugin/swDatePicker/onPickerOpen', [ me ]);
        },

        onPickerClose: function () {
            var me = this;

            if (me.opts.autoSubmit && me.currentValue !== me.$el.val()) {
                me.$el.parents('form').submit();
            }

            $.publish('plugin/swDatePicker/onPickerClose', [ me ]);
        },

        destroy: function () {
            var me = this;

            me.flatpickr.destroy();

            me._destroy();

            $.publish('plugin/swDatePicker/onDestroy', [ me ]);
        }

    });

})(jQuery, window);