;(function ($, window, document, undefined) {
    "use strict";

    $.plugin('selectboxReplacement', {

        /** @property {Object} Default settings for the plugin **/
        defaults: {
            baseCls: 'js--fancy-select',
            focusCls: 'js--is--fo3cused',
            submit: true
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
            wrapEl = $el.wrap(wrapEl).parents('.' + me.options.baseCls);

            me.$textEl = $('<div>', { 'class': me.options.baseCls + '-text' }).appendTo(wrapEl);
            me.$triggerEl =$('<div>', { 'class': me.options.baseCls + '-trigger' }).appendTo(wrapEl);

            me.selected = me.$el.find(':selected');
            me.$textEl.html(me.selected.html());

            return wrapEl;
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