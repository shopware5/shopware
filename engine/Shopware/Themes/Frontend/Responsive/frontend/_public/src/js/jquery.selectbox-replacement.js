;(function ($, window, document, undefined) {
    "use strict";

    $.plugin('selectboxReplacement', {

        /** @property {Object} Default settings for the plugin **/
        defaults: {

            /** @property {String} Basic class name for the plugin. */
            baseCls: 'js--fancy-select',

            /** @property {String} Focus class. */
            focusCls: 'js--is--focused',

            /** @property {String} Text / html content for the trigger field. */
            triggerText: '<i class="icon--arrow-down"></i>',

            /** @property {String} Class which indicates that the field is disabled. */
            disabledCls: 'is--disabled',

            /** @property {String} Class which indicates that the field has an error. */
            errorCls: 'has--error',

            /** @property {boolean} Truthy to auto-submit the underlying form, otherwise falsy. */
            submit: true,

            /** @property {boolean} Truthy to set all the classes on the parent element to the wrapper element. */
            compatibility: true
        },

        /**
         * Initializes the plugin
         *
         * @returns {Plugin}
         */
        init: function () {
            var me = this;

            me.$wrapEl = me.createTemplate(me.$el);
            me.registerEventListeners();

            // Update the plugin configuration with the HTML5 data-attributes
            me.getDataAttributes();

            // Disable the select box
            if (me.$el.attr('disabled') !== undefined) {
                me.setDisabled();
            }

            // Support marking the field as error
            if (me.$el.hasClass(me.opts.errorCls)) {
                me.setError();
            }

            // Set the compatibility classes
            if (me.opts.compatibility) {
                me._setCompatibilityClasses();
            }

            return me;
        },

        /**
         * Creates the neccessary DOM structure and wraps the {@link me.$el} into the newly created
         * structure.
         * @param {jQuery} $el - HTMLElement which fires the plugin.
         * @returns {jQuery} wrapEl - jQuery object of the newly created structure
         */
        createTemplate: function ($el) {
            var me = this,
                wrapEl;

            wrapEl = me._formatString('<div class="{0}"></div>', me.opts.baseCls);
            wrapEl = $el.wrap(wrapEl).parents('.' + me.opts.baseCls);

            me.$textEl = $('<div>', { 'class': me.opts.baseCls + '-text' }).appendTo(wrapEl);
            me.$triggerEl =$('<div>', { 'class': me.opts.baseCls + '-trigger', 'html': me.opts.triggerText }).appendTo(wrapEl);

            me.selected = me.$el.find(':selected');
            me.$textEl.html(me.selected.html());

            return wrapEl;
        },

        /**
         * Disables the select box
         * @returns {jQuery|Plugin.$el|*|PluginBase.$el}
         */
        setDisabled: function () {
            var me = this;

            me.$wrapEl.addClass(me.opts.disabledCls);
            me.$el.attr('disabled', 'disabled');

            return me.$el;
        },

        /**
         * Enables the select box
         * @returns {jQuery|Plugin.$el|*|PluginBase.$el}
         */
        setEnabled: function () {
            var me = this;

            me.$wrapEl.removeClass(me.opts.disabledCls);
            me.$el.removeAttr('disabled');

            return me.$el;
        },

        /**
         * Marks the field as error.
         * @returns {jQuery}
         */
        setError: function () {
            var me = this;
            return me.$el.addClass(me.opts.errorCls);
        },

        /**
         * Removes the error mark of the field.
         * @returns {jQuery}
         */
        removeError: function () {
            var me = this;
            return me.$el.removeClass(me.opts.errorCls);
        },

        /**
         * Wrapper method for jQuery's ```val``` method.
         * @returns {jQuery}
         */
        val: function() {
            var me = this, val;

            val = me.$el.val.apply(me.$el, arguments);

            if(typeof arguments[0] !== 'function') {
                me.setSelectedOnTextElement();
            }

            return val;
        },

        /**
         * Wrapper method for jQuery's ```show``` method.
         * @returns {jQuery}
         */
        show: function() {
            var me = this;

            return me.$wrapEl.show.apply(me.$wrapEl, arguments);
        },

        /**
         * Wrapper method for jQuery's ```hide``` method.
         * @returns {jQuery}
         */
        hide: function() {
            var me = this;

            return me.$wrapEl.hide.apply(me.$wrapEl, arguments);
        },

        /**
         * Registers the neccessary event listeners for the plugin.
         *
         * @returns {boolean}
         */
        registerEventListeners: function () {
            var me = this;

            me._on(me.$el, 'change', $.proxy(me.onChange, me));
            me._on(me.$el, 'keyup', $.proxy(me.onKeyUp, me));
            me._on(me.$el, 'focus', $.proxy(me.onFocus, me));
            me._on(me.$el, 'blur', $.proxy(me.onBlur, me));

            return true;
        },

        /**
         * Helper method which reads out the selected entry from the "select" element
         * and writes it into the text element which is visible to the user.
         *
         * @returns {String} selected entry from the "select" element
         */
        setSelectedOnTextElement: function () {
            var me = this;

            me.selected = me.$el.find(':selected');
            me.$textEl.html(me.selected.html());

            return me.selected;
        },

        /**
         * Submits the underlying form element which holds
         * off the select box.
         *
         * @returns {Plugin}
         */
        submitField: function () {
            var me = this;

            me.$el.parents('form').submit();

            return me;
        },

        /**
         * Event listener method which will be fired when the user
         * changes the value of the select box.
         *
         * @event `change`
         * @param {Object} event - jQuery event eOpts
         * @returns {void}
         */
        onChange: function () {
            var me = this;

            me.setSelectedOnTextElement();

            if(!me.opts.submit) {
                return;
            }

            // We need to set a timeout to display the currently selected option in the text element of the select box.
            window.setTimeout(function () {
                me.submitField();
            }, 10);
        },

        /**
         * Event listener which fires on key up on the "select" element.
         *
         * Checks if the user presses the up or down key to update the
         * text element with the currently selected entry in the select box.
         *
         * @event `keyup`
         * @param {Object} event - jQuery event eOpts
         * @returns {boolean}
         */
        onKeyUp: function (event) {
            var me = this;

            // 38 = up arrow, 40 = down arrow
            if(event.which === 38 || event.which === 40) {
                me.setSelectedOnTextElement();
            }
            return false;
        },

        /**
         * Event listener which fires on focus on the "select" element.
         *
         * Just adds a class for styling purpose.
         *
         * @returns {void}
         */
        onFocus: function () {
            var me = this;

            me.$wrapEl.addClass(me.opts.focusCls);
        },

        /**
         * Event listener which fires on blur on the "select" element.
         *
         * Just removes a class which was set for styling purpose.
         *
         * @returns {void}
         */
        onBlur: function () {
            var me = this;

            me.$wrapEl.removeClass(me.opts.focusCls);
        },

        /**
         * Applies all the classes from the ```field--select``` parent element to the {@link me.$wrapEl}.
         *
         * @returns {boolean}
         * @private
         */
        _setCompatibilityClasses: function () {
            var me = this,
                $el = me.$el,
                $parent = $el.parents('.field--select'),
                classList;

            if(!$parent || !$parent.length) {
                return false;
            }
            classList = $parent.attr('class').split(/\s+/);
            $.each(classList, function () {
                me.$wrapEl.addClass(this);
            });

            return true;
        },

        /**
         * Allows you to define a tokenized string and pass an arbitrary number of arguments to replace the tokens.
         * Each token must be unique, and must increment in the format {0}, {1}, etc.
         *
         * @example Sample usage
         *    me._formatString('<div class="{0}">Text</div>', 'test');
         *
         * @param {String} str - The tokenized string to be formatted.
         * @returns {String} The formatted string
         * @private
         */
        _formatString: function (str) {
            var i = 1,
                len = arguments.length;

            for (; i < len; i++) {
                str = str.replace('{' + (i - 1) + '}', arguments[i]);
            }
            return str;
        }
    });
})(jQuery, window, document);