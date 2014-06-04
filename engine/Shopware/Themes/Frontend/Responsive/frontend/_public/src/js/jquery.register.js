;(function($, window, document, undefined) {
    "use strict";

    var pluginName = 'register',
        defaults = {
            hiddenCls: 'is--hidden',
            errorCls: 'has--error'
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

        me.$inputs = me.$el.find('.is--required:input');

        me.registerEvents();
    };

    Plugin.prototype.registerEvents = function () {
        var me = this;

        me.$typeSelection.on('change.' + pluginName, $.proxy(me.onChangeType, me));
        me.$skipAccount.on('change.' + pluginName, $.proxy(me.onSkipAccount, me));
        me.$alternativeShipping.on('change.' + pluginName, $.proxy(me.onChangeShipping, me));
        me.$inputs.on('blur.' + pluginName, $.proxy(me.onValidateInput, me));
    };

    Plugin.prototype.onChangeType = function (event) {
        var me = this,
            $target = $(event.currentTarget),
            method = ($target.val() === 'business') ? 'removeClass' : 'addClass';

        me.$companyFieldset[method](me.opts.hiddenCls);
    };

    Plugin.prototype.onSkipAccount = function () {
        var me = this,
            $target = $(event.currentTarget),
            isChecked = $target.is(':checked'),
            method = (isChecked) ? 'addClass' : 'removeClass';

        me.$accountFieldset[method](me.opts.hiddenCls);
    };

    Plugin.prototype.onChangeShipping = function (event) {
        var me = this,
            $target = $(event.currentTarget),
            isChecked = $target.is(':checked'),
            method = (isChecked) ? 'removeClass' : 'addClass';

        me.$shippingFieldset[method](me.opts.hiddenCls);
    };

    Plugin.prototype.onValidateInput = function (event) {
        var me = this,
            $el = $(event.currentTarget),
            id = $el.attr('id'),
            action;

        switch (id) {
            case 'register_personal_skipLogin':
            case 'register_personal_email':
            case 'register_personal_emailConfirmation':
                action = 'ajax_validate_email';
                break;
            case 'register_billing_ustid':
                action = 'ajax_validate_billing';
                break;
            case 'register_personal_password':
            case 'register_personal_passwordConfirmation':
                action = 'ajax_validate_password';
                break;
            default:
                return false;
                break;
        }

        if (!$el.val()) {
            me.setFieldAsError($el);
            return;
        } else if (action) {
            me.validateUsingAjax($el, action);
            return;
        } else {
            me.setFieldAsSuccess($el);
        }
    };

    Plugin.prototype.setFieldAsError = function ($el) {
        var me = this;

        $el.addClass(me.opts.errorCls);
    };

    Plugin.prototype.validateUsingAjax = function ($el, action) {
        var me = this,
            data = 'action=' + action + '&' + me.$el.find('form').serialize();

        var collectMessages = function (result) {
            var messages = [];
            for (var error_key in result.error_messages) {
                if(result.error_messages.length) {
                    messages.push(result.error_messages[error_key]);
                }
            }
            return messages.join('<br/>');
        };

        var onSuccess = function (result, data) {
            if (result && result.error_flags) {
                for (var error_flag in result.error_flags) {
                    if (result.error_flags[error_flag]) {
                        me.setFieldAsError(me.$el.find('.' + error_flag));
                    } else {
                        me.setFieldAsSuccess(me.$el.find('.' + error_flag));
                    }
                }
            }

            $('#' + action + '--message').remove();
            if (result && result.error_messages && result.error_messages.length) {
                $('<div>', {
                    'html': '<p>' + collectMessages(result) + '</p>',
                    'id': action + '--message',
                    'class': 'register--error-msg'
                }).insertAfter($el);
                me.setFieldAsError($el);
            }
        };

        $.ajax({
            'data': data,
            'type': 'post',
            'dataType': 'json',
            'url': $.controller.ajax_validate,
            'success': onSuccess
        });
    };

    Plugin.prototype.setFieldAsSuccess = function ($el) {
        var me = this;

        $el.removeClass(me.opts.errorCls);
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

        me.$typeSelection.off('change.' + pluginName);
        me.$skipAccount.off('change.' + pluginName);
        me.$alternativeShipping.off('change.' + pluginName);
        me.$inputs.off('blur.' + pluginName);
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