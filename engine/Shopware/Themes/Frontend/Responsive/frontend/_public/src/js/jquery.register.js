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

        me.$form = me.$el.find('.register--form');
        me.$submitBtn = me.$el.find('.register--submit');

        me.$typeSelection = me.$el.find('.register--customertype select');
        me.$skipAccount = me.$el.find('.register--check input');
        me.$alternativeShipping = me.$el.find('.register--alt-shipping input');

        me.$companyFieldset = me.$el.find('.register--company');
        me.$accountFieldset = me.$el.find('.register--account-information');
        me.$shippingFieldset = me.$el.find('.register--shipping');

        me.$countySelectFields = me.$el.find('.select--country');
        me.$stateSelectContainers = $('.register--state-selection');

        me.$paymentMethods = me.$el.find('.payment--method');

        me.$inputs = me.$el.find('.is--required');

        me.checkType();
        me.checkSkipAccount();
        me.checkChangeShipping();

        me.registerEvents();
    };

    Plugin.prototype.registerEvents = function () {
        var me = this;

        me.$typeSelection.on('change.' + pluginName, $.proxy(me.checkType, me));
        me.$skipAccount.on('change.' + pluginName, $.proxy(me.checkSkipAccount, me));
        me.$alternativeShipping.on('change.' + pluginName, $.proxy(me.checkChangeShipping, me));
        me.$countySelectFields.on('change.' + pluginName, $.proxy(me.onCountryChanged, me));
        me.$paymentMethods.on('change.' + pluginName, $.proxy(me.onPaymentChanged, me));
        me.$inputs.on('blur.' + pluginName, $.proxy(me.onValidateInput, me));
        me.$submitBtn.on('click.' + pluginName, $.proxy(me.onSubmitBtn, me));
    };

    Plugin.prototype.checkType = function () {
        var me = this,
            hideCompanyFields = (me.$typeSelection.length && me.$typeSelection.val() !== 'business'),
            requiredFields = me.$companyFieldset.find('.is--required'),
            requiredMethod = (!hideCompanyFields) ? me.setHtmlRequired : me.removeHtmlRequired,
            classMethod = (!hideCompanyFields) ? 'removeClass' : 'addClass';
        requiredMethod(requiredFields);

        me.$companyFieldset[classMethod](me.opts.hiddenCls);
    };

    Plugin.prototype.checkSkipAccount = function () {
        var me = this,
            isChecked = me.$skipAccount.is(':checked'),
            requiredFields = me.$accountFieldset.find('.is--required'),
            requiredMethod = (!isChecked) ? me.setHtmlRequired : me.removeHtmlRequired,
            classMethod = (isChecked) ? 'addClass' : 'removeClass';

        requiredMethod(requiredFields);

        me.$accountFieldset[classMethod](me.opts.hiddenCls);
    };

    Plugin.prototype.checkChangeShipping = function () {
        var me = this,
            isChecked = me.$alternativeShipping.is(':checked'),
            requiredFields = me.$shippingFieldset.find('.is--required'),
            requiredMethod = (isChecked) ? me.setHtmlRequired : me.removeHtmlRequired,
            classMethod = (isChecked) ? 'removeClass' : 'addClass';

        requiredMethod(requiredFields);

        me.$shippingFieldset[classMethod](me.opts.hiddenCls);
    };

    Plugin.prototype.onCountryChanged = function(event) {
        var $select = $(event.currentTarget),
            selectId = $select.attr('id'),
            val = $select.val(),
            parent = $select.parents('.panel--body'),
            areaSelection = parent.find('#' + selectId + '_' + val + '_states'),
            select, plugin;

        parent.find('.register--state-selection').addClass('is--hidden');
        select = areaSelection.find('select');
        plugin = select.data('plugin_selectboxReplacement');

        plugin.$el.addClass('is--hidden');
        plugin.$wrapEl.addClass('is--hidden');
        areaSelection.addClass('is--hidden');
        plugin.setDisabled();

        if (areaSelection.length) {
            // We have a state selection
            select = areaSelection.find('select');
            plugin = select.data('plugin_selectboxReplacement');

            plugin.$el.removeClass('is--hidden');
            plugin.$wrapEl.removeClass('is--hidden');
            areaSelection.removeClass('is--hidden');
            plugin.setEnabled();
        }
    };

    Plugin.prototype.onPaymentChanged = function() {
        var me = this,
            isChecked,
            requiredFields,
            requiredMethod,
            classMethod;

        $.each(me.$paymentMethods, function( index, value ) {
            var radio = $(value).find('.payment--selection-input input');
            isChecked = radio[0].checked;

            requiredFields = $(value).find('.is--required');
            requiredMethod = (isChecked) ? me.setHtmlRequired : me.removeHtmlRequired;
            classMethod = (!isChecked) ? 'addClass' : 'removeClass';

            requiredMethod(requiredFields);

            var fieldset = $(value).find('.payment--content');

            fieldset[classMethod](me.opts.hiddenCls);
        });
    };

    Plugin.prototype.onSubmitBtn = function(event) {
        var me = this,
            input,
            valid = true;

        me.$inputs.each(function() {
            input = $(this);

            if (!input.val()) {
                valid = false;
                me.setFieldAsError(input);
            }
        });
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
                break;
        }

        if (!$el.val()) {
            me.setFieldAsError($el);
            return;
        } else if ($el.attr('type') === 'checkbox' && !$el.is(':checked')) {
            me.setFieldAsError($el);
            return;
        } else if (action) {
            me.validateUsingAjax($el, action);
            return;
        } else {
            me.setFieldAsSuccess($el);
        }
    };

    Plugin.prototype.setHtmlRequired = function($inputs) {
        $inputs.attr({
            'required': 'required',
            'aria-required': 'true'
        });
    };

    Plugin.prototype.removeHtmlRequired = function($inputs) {
        $inputs.removeAttr('required aria-required');
    };

    Plugin.prototype.setFieldAsError = function ($el) {
        var me = this;

        if ($el.is(':plugin-selectboxreplacement')) {
            var plugin = $el.data('plugin_selectboxReplacement');
            plugin.setError();
        } else {
            $el.addClass(me.opts.errorCls);
        }
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

        if ($el.is(':plugin-selectboxreplacement')) {
            var plugin = $el.data('plugin_selectboxReplacement');
            plugin.removeError();
        } else {
            $el.removeClass(me.opts.errorCls);
        }
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
        me.$countySelectFields.off('change.' + pluginName);
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