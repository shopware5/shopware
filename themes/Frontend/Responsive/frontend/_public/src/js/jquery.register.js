;(function ($) {
    'use strict';

    $.plugin('register', {
        defaults: {
            hiddenClass: 'is--hidden',

            errorCls: 'has--error',

            formSelector: '.register--form',

            submitBtnSelector: '.register--submit',

            typeFieldSelector: '.register--customertype select',

            skipAccountSelector: '.register--check input',

            altShippingSelector: '.register--alt-shipping input',

            companyFieldSelector: '.register--company',

            accountFieldSelector: '.register--account-information',

            shippingFieldSelector: '.register--account-information',

            countryFieldSelector: '.select--country',

            stateContainerSelector: '.register--state-selection',

            paymentMethodSelector: '.payment--method',

            inputSelector: '.is--required',

            errorMessageClass: 'register--error-msg'
        },

        init: function () {
            var me = this,
                opts = me.opts,
                $el = me.$el;

            me.$form = $el.find(opts.formSelector);
            me.$submitBtn = $el.find(opts.submitBtnSelector);

            me.$typeSelection = $el.find(opts.typeFieldSelector);
            me.$skipAccount = $el.find(opts.skipAccountSelector);
            me.$alternativeShipping = $el.find(opts.altShippingSelector);

            me.$companyFieldset = $el.find(opts.companyFieldSelector);
            me.$accountFieldset = $el.find(opts.accountFieldSelector);
            me.$shippingFieldset = $el.find(opts.shippingFieldSelector);

            me.$countySelectFields = $el.find(opts.countryFieldSelector);
            me.$stateSelectContainers = $(opts.stateContainerSelector);

            me.$paymentMethods = $el.find(opts.paymentMethodSelector);

            me.$inputs = $el.find(opts.inputSelector);

            me.checkType();
            me.checkSkipAccount();
            me.checkChangeShipping();

            me.registerEvents();
        },

        registerEvents: function () {
            var me = this;

            me._on(me.$typeSelection, 'change', $.proxy(me.checkType, me));
            me._on(me.$skipAccount, 'change', $.proxy(me.checkSkipAccount, me));
            me._on(me.$alternativeShipping, 'change', $.proxy(me.checkChangeShipping, me));
            me._on(me.$countySelectFields, 'change', $.proxy(me.onCountryChanged, me));
            me._on(me.$paymentMethods, 'change', $.proxy(me.onPaymentChanged, me));
            me._on(me.$inputs, 'blur', $.proxy(me.onValidateInput, me));
            me._on(me.$submitBtn, 'click', $.proxy(me.onSubmitBtn, me));
        },

        checkType: function () {
            var me = this,
                opts = me.opts,
                $fieldSet = me.$companyFieldset,
                hideCompanyFields = (me.$typeSelection.length && me.$typeSelection.val() !== 'business'),
                requiredFields = $fieldSet.find(opts.inputSelector),
                requiredMethod = (!hideCompanyFields) ? me.setHtmlRequired : me.removeHtmlRequired,
                classMethod = (!hideCompanyFields) ? 'removeClass' : 'addClass';

            requiredMethod(requiredFields);

            $fieldSet[classMethod](opts.hiddenClass);
        },

        checkSkipAccount: function () {
            var me = this,
                opts = me.opts,
                $fieldSet = me.$accountFieldset,
                isChecked = me.$skipAccount.is(':checked'),
                requiredFields = $fieldSet.find(opts.inputSelector),
                requiredMethod = (!isChecked) ? me.setHtmlRequired : me.removeHtmlRequired,
                classMethod = (isChecked) ? 'addClass' : 'removeClass';

            requiredMethod(requiredFields);

            $fieldSet[classMethod](opts.hiddenClass);
        },

        checkChangeShipping: function () {
            var me = this,
                opts = me.opts,
                $fieldSet = me.$shippingFieldset,
                isChecked = me.$alternativeShipping.is(':checked'),
                requiredFields = $fieldSet.find(opts.inputSelector),
                requiredMethod = (isChecked) ? me.setHtmlRequired : me.removeHtmlRequired,
                classMethod = (isChecked) ? 'removeClass' : 'addClass';

            requiredMethod(requiredFields);

            $fieldSet[classMethod](opts.hiddenClass);
        },

        onCountryChanged: function (event) {
            var me = this,
                opts = me.opts,
                hiddenClass = opts.hiddenClass,
                $select = $(event.currentTarget),
                selectId = $select.attr('id'),
                val = $select.val(),
                parent = $select.parents('.panel--body'),
                areaSelection = parent.find('#' + selectId + '_' + val + '_states'),
                select,
                plugin;

            parent.find(opts.stateContainerSelector).addClass(hiddenClass);
            select = areaSelection.find('select');
            areaSelection.addClass(hiddenClass);

            if (!(plugin = select.data('plugin_selectboxReplacement'))) {
                return;
            }

            if (!areaSelection.length)  {
                plugin.$el.addClass(hiddenClass);
                plugin.$wrapEl.addClass(hiddenClass);
                plugin.setDisabled();
                return;
            }

            plugin.$el.removeClass(hiddenClass);
            plugin.$wrapEl.removeClass(hiddenClass);
            areaSelection.removeClass(hiddenClass);
            plugin.setEnabled();
        },

        onPaymentChanged: function () {
            var me = this,
                opts = me.opts,
                inputClass = opts.inputSelector,
                hiddenClass = opts.hiddenClass,
                isChecked,
                requiredMethod,
                classMethod,
                fieldSet,
                radio,
                $el;

            $.each(me.$paymentMethods, function (index, el) {
                $el = $(el);

                radio = $el.find('.payment--selection-input input');
                isChecked = radio[0].checked;

                requiredMethod = (isChecked) ? me.setHtmlRequired : me.removeHtmlRequired;
                classMethod = (!isChecked) ? 'addClass' : 'removeClass';

                requiredMethod($el.find(inputClass));

                fieldSet = $el.find('.payment--content');
                fieldSet[classMethod](hiddenClass);
            });
        },

        onSubmitBtn: function () {
            var me = this,
                $input;

            me.$inputs.each(function () {
                $input = $(this);

                if (!$input.val()) {
                    me.setFieldAsError($input);
                }
            });
        },

        onValidateInput: function (event) {
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
            } else if ($el.attr('type') === 'checkbox' && !$el.is(':checked')) {
                me.setFieldAsError($el);
            } else if (action) {
                me.validateUsingAjax($el, action);
            } else {
                me.setFieldAsSuccess($el);
            }
        },

        setHtmlRequired: function ($inputs) {
            $inputs.attr({
                'required': 'required',
                'aria-required': 'true'
            });
        },

        removeHtmlRequired: function ($inputs) {
            $inputs.removeAttr('required aria-required');
        },

        setFieldAsError: function ($el) {
            var me = this,
                plugin;

            if ((plugin = $el.data('plugin_selectboxReplacement'))) {
                plugin.setError();
                return;
            }

            $el.addClass(me.opts.errorCls);
        },

        setFieldAsSuccess: function ($el) {
            var me = this,
                plugin;

            if ((plugin = $el.data('plugin_selectboxReplacement'))) {
                plugin.removeError();
                return;
            }

            $el.removeClass(me.opts.errorCls);
        },

        validateUsingAjax: function ($input, action) {
            var me = this,
                data = 'action=' + action + '&' + me.$el.find('form').serialize(),
                URL = $.controller.ajax_validate;

            if (!URL) {
                return;
            }

            $.ajax({
                'data': data,
                'type': 'post',
                'dataType': 'json',
                'url': URL,
                'success': $.proxy(me.onValidateSuccess, me, action, $input)
            });
        },

        onValidateSuccess: function (action, $input, result) {
            var me = this,
                errorFlags,
                errorMessages;

            $('#' + action + '--message').remove();

            if (!result) {
                return;
            }

            errorFlags = result.error_flags;
            errorMessages = result.error_messages;

            if (errorFlags) {
                me.updateFieldFlags(errorFlags);
            }

            if (errorMessages && errorMessages.length) {
                $('<div>', {
                    'html': '<p>' + me.collectMessages(result) + '</p>',
                    'id': action + '--message',
                    'class': me.opts.errorMessageClass
                }).insertAfter($input);

                me.setFieldAsError($input);
            }
        },

        updateFieldFlags: function (flags) {
            var me = this,
                $el = me.$el,
                keys = Object.keys(flags),
                len = keys.length,
                i = 0,
                flag,
                $input;

            for (; i < len; i++) {
                flag = keys[i];
                $input = $el.find('.' + flag);

                if (flags[flag]) {
                    me.setFieldAsError($input);
                    continue;
                }

                me.setFieldAsSuccess($input);
            }
        },

        collectMessages: function (result) {
            var messages = [],
                errorMessages = result.error_messages,
                len = errorMessages.length,
                i = 0;

            for (; i < len; i++) {
                messages.push(errorMessages[i]);
            }

            return messages.join('<br/>');
        },

        destroy: function () {
            var me = this;

            me._destroy();
        }
    });
})(jQuery);