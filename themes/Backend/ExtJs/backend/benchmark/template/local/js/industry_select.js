(function ($) {
    'use strict';

    function IndustrySelect(el) {
        this.$el = $(el);

        this.initElements();
        this.initEvents();

        this.config = {};
    }

    IndustrySelect.prototype.initElements = function () {
        this.$shopEntries = this.$el.find('.shop-list--entry');
        this.$configOverlay = this.$el.find('.shop-config--overlay');
        this.$cancelButton = this.$el.find('.cancel-button--ct');
        this.$resetButton = this.$el.find('.reset-button--ct');
        this.$industrySelect = this.$el.find('.industry-select');
        this.$industryOption = this.$industrySelect.find('.industry-option');
        this.$industrySelectHeader = this.$industrySelect.find('.select-header');
        this.$industryValueInput = this.$industrySelect.find('.industry-value');
        this.$typeRadios = this.$configOverlay.find('*[name=type]');
        this.$submitButton = this.$configOverlay.find('.shop-config--submit-button');
        this.$saveButton = this.$el.find('.industry--save-button');
    };

    IndustrySelect.prototype.initEvents = function () {
        this.$shopEntries.on('click', $.proxy(this.onClickShopEntry, this));
        this.$configOverlay.on('click', $.proxy(this.onClickOverlay, this));
        this.$cancelButton.on('click', $.proxy(this.onClickCancel, this));
        this.$resetButton.on('click', $.proxy(this.onClickReset, this));
        this.$industrySelect.on('click', $.proxy(this.onOpenIndustrySelect, this));
        this.$industryOption.on('click', $.proxy(this.onClickIndustryOption, this));
        this.$submitButton.on('click', $.proxy(this.onSubmit, this));
        this.$saveButton.on('click', $.proxy(this.onSaveIndustry, this));

        $('*[data-language-switch="true"]')[0].addEventListener('languageSwitch', $.proxy(this.onLanguageSwitch, this));
    };

    IndustrySelect.prototype.onClickShopEntry = function (event) {
        var $clickedEl = $(event.currentTarget),
            shopName = $clickedEl.find('.shop-list--name').html(),
            shopId = $clickedEl.find('.entry-shop-id').val();

        this.fillConfigMask(shopName, this.getConfig(shopId));
        this.$configOverlay.show();
    };

    IndustrySelect.prototype.fillConfigMask = function (shopName, config) {
        var $headlineEl = this.$configOverlay.find('.config-headline');

        $headlineEl.html(shopName);

        if (config.shopId) {
            this.$configOverlay.find('.overlay-shop-id').val(config.shopId);
        }

        if (config.industry) {
            this.selectIndustry(config.industry);
        }

        if (config.type) {
            this.selectType(config.type);
        }
    };

    IndustrySelect.prototype.onClickOverlay = function (event) {
        var $target = $(event.target);

        if ($target.hasClass('shop-config--overlay')) {
            this.closeOverlay();
        }

        if ($target.hasClass('shop-list--config')) {
            this.$industrySelect.removeClass('open');
        }
    };

    IndustrySelect.prototype.closeOverlay = function () {
        this.$configOverlay.hide();
        this.resetOverlay();
    };

    IndustrySelect.prototype.resetOverlay = function () {
        this.$industrySelect.removeClass('open');
        this.$industrySelect.removeClass('active');
        this.$industrySelectHeader.html(window.i18n.t('defaultIndustryText'));
        this.$industryValueInput.val(null);

        this.$typeRadios.filter(':checked').prop('checked', false);
    };

    IndustrySelect.prototype.onClickCancel = function () {
        this.closeOverlay();
    };

    IndustrySelect.prototype.onClickReset = function () {
        this.resetOverlay();
    };

    IndustrySelect.prototype.onOpenIndustrySelect = function () {
        this.$industrySelect.toggleClass('open');
    };

    IndustrySelect.prototype.onClickIndustryOption = function (event) {
        var $currentTarget = $(event.currentTarget),
            optionName = $currentTarget.html(),
            optionValue = $currentTarget.attr('data-industry-value');

        this.$industrySelectHeader.html(optionName);
        this.$industrySelect.addClass('active');
        this.$industryValueInput.val(optionValue);
    };

    IndustrySelect.prototype.onSubmit = function () {
        var shopId = this.$configOverlay.find('.overlay-shop-id').val(),
            industryVal = this.$industryValueInput.val(),
            typeVal = this.$typeRadios.filter(':checked').val();

        this.config[shopId] = {
            industry: industryVal,
            type: typeVal,
            shopId: shopId
        };

        this.resetEntry(shopId);
        if (industryVal && typeVal) {
            this.updateEntry(shopId);
        }

        this.closeOverlay();
    };

    IndustrySelect.prototype.getConfig = function (shopId) {
        if (!this.config[shopId]) {
            return {
                shopId: shopId
            };
        }

        return this.config[shopId];
    };

    IndustrySelect.prototype.selectIndustry = function (industry) {
        var $industryEntry = this.$industryOption.filter('[data-industry-value=' + industry + ']');
        this.onOpenIndustrySelect();
        $industryEntry.click();
    };

    IndustrySelect.prototype.selectType = function (type) {
        var $radio = this.$typeRadios.filter('*[value=' + type + ']');
        
        $radio.prop('checked', true);
    };

    IndustrySelect.prototype.resetEntry = function (shopId) {
        var $entry = this.$shopEntries.find('input[value=' +  shopId + ']').parent(),
            $infoText = $entry.find('.shop-list--info-text');

        $entry.removeClass('configured');
        $infoText.html(window.i18n.t('emptyShopConfigText'));

        this.updateSaveButton()
    };

    IndustrySelect.prototype.updateEntry = function (shopId) {
        var $entry = this.$shopEntries.find('input[value=' +  shopId + ']').parent(),
            $infoText = $entry.find('.shop-list--info-text'),
            industryText = this.$industryOption.filter('*[data-industry-value=' + this.config[shopId].industry + ']').html();

        $entry.addClass('configured');
        $infoText.html(industryText + ' | ' + this.config[shopId].type.toUpperCase());

        this.updateSaveButton();
    };

    IndustrySelect.prototype.updateSaveButton = function () {
        var shopCount = this.$el.find('.configured').length;

        this.$saveButton.find('.save-button--shop-counter').html(shopCount);

        this.$saveButton[shopCount > 0 ? 'show' : 'hide']();
    };

    IndustrySelect.prototype.onSaveIndustry = function () {
        var me = this;

        $.ajax({
            url: this.$saveButton.attr('data-save-url'),
            data: { config: this.config },
            success: function (response) {
                if (!response.success) {
                    return;
                }

                window.location.href = me.$saveButton.attr('data-success-url').replace('placeholder', window.i18n.locale);
            }
        });
    };

    IndustrySelect.prototype.onLanguageSwitch = function () {
        this.translateShopEntries();
        this.translateIndustryValue();
    };

    IndustrySelect.prototype.translateShopEntries = function () {
        var me = this,
            $shopEntry, shopId,
            $infoText,
            name, translation;


        this.$shopEntries.each(function (index, shopEntry) {
            $shopEntry = $(shopEntry);
            shopId = $shopEntry.find('.entry-shop-id').val();
            $infoText = $shopEntry.find('.shop-list--info-text');

            // Is configured
            if ($shopEntry.hasClass('configured')) {
                translation = me.getTranslationForIndustryId(me.config[shopId].industry);
                name = translation + ' | ' + me.config[shopId].type.toUpperCase();

                $infoText.html(name);

                return;
            }

            $infoText.html(window.i18n.t('emptyShopConfigText'));
        });
    };

    IndustrySelect.prototype.translateIndustryValue = function () {
        var industryValue = this.$configOverlay.find('.industry-value').val();

        if (industryValue) {
            this.$industrySelectHeader.html(this.getTranslationForIndustryId(industryValue));

            return;
        }

        this.$industrySelectHeader.html(window.i18n.t('defaultIndustryText'));
    };

    IndustrySelect.prototype.getTranslationForIndustryId = function (industryId) {
        var $industryOption = this.$industryOption.filter('*[data-industry-value=' + industryId + ']'),
            translationKey = $industryOption.attr('data-translation-name');

        return window.i18n.t(translationKey);
    };

    $.fn.industrySelect = function() {
        return this.each(function() {
            var $el = $(this);

            if ($el.data('plugin_industrySelect')) {
                return;
            }

            var plugin = new IndustrySelect(this);
            $el.data('plugin_industrySelect', plugin);
        });
    };

    $(function() {
        $('*[data-industry-select="true"]').industrySelect();
    });
})(jQuery);
