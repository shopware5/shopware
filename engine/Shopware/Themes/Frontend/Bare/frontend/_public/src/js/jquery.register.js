;(function($, window, document, undefined) {
    "use strict";

    var pluginName = 'register',
        defaults = {
            /** @string activeCls Class which will be added when the drop down was triggered */
            activeCls: 'is--active'
        };

    /**
     * Plugin constructor which merges the default settings with the user settings
     * and parses the `data`-attributes of the incoming `element`.
     *
     * @param {HTMLElement} element - Element which should be used in the plugin
     * @param {Object} userOpts - User settings for the plugin
     * @returns {Void}
     * @constructor
     */
    function Plugin(element, userOpts) {
        var me = this;

        me.$el = $(element);
        me.opts = $.extend({}, defaults, userOpts);

        me._defaults = defaults;
        me._name = pluginName;

        me.init();
    }

    /**
     * Initializes the plugin, sets up event listeners and adds the necessary
     * classes to get the plugin up and running.
     *
     * @returns {Void}
     */
    Plugin.prototype.init = function() {
        var me = this;

        me.$typeSelection = me.$el.find('.register--customertype select');
        me.$skipAccount = me.$el.find('.register--check input');
        me.$alternativeShipping = me.$el.find('.register--alt-shipping input');

        me.$companyFieldset = me.$el.find('.register--company');
        me.$accountFieldset = me.$el.find('.register--account-information');
        me.$shippingFieldset = me.$el.find('.register--shipping');

        me.registerEvents();
    };

    Plugin.prototype.registerEvents = function () {
        var me = this;

        me.$typeSelection.on('change.' + pluginName, $.proxy(me.onChangeType, me));
        me.$skipAccount.on('change.' + pluginName, $.proxy(me.onSkipAccount, me));
        me.$alternativeShipping.on('change.' + pluginName, $.proxy(me.onChangeShipping, me));
    };

    Plugin.prototype.onChangeType = function (event) {
        var me = this,
            $target = $(event.currentTarget),
            method = ($target.val() === 'business') ? 'removeClass' : 'addClass';

        me.$companyFieldset[method]('is--hidden');
    };

    Plugin.prototype.onSkipAccount = function () {
        var me = this,
            $target = $(event.currentTarget),
            isChecked = $target.is(':checked'),
            method = (isChecked) ? 'addClass' : 'removeClass';

        me.$accountFieldset[method]('is--hidden');
    };

    Plugin.prototype.onChangeShipping = function () {
        var me = this,
            $target = $(event.currentTarget),
            isChecked = $target.is(':checked'),
            method = (isChecked) ? 'removeClass' : 'addClass';

        me.$shippingFieldset[method]('is--hidden');
    };

    /**
     * Destroyes the initialized plugin completely, so all event listeners will
     * be removed and the plugin data, which is stored in-memory referenced to
     * the DOM node.
     *
     * @returns {Boolean}
     */
    Plugin.prototype.destroy = function() {
        var me = this;
    };

    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
                new Plugin( this, options ));
            }
        });
    };
})(jQuery, window, document);