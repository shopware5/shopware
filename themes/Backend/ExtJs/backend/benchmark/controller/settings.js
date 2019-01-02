
//{namespace name="backend/benchmark/main"}
//{block name="backend/benchmark/controller/settings"}
Ext.define('Shopware.apps.Benchmark.controller.Settings', {
    extend: 'Ext.app.Controller',

    refs: [
        { ref: 'settingsPanel', selector: 'form[name=benchmark-settings-panel]' },
        { ref: 'activationContainer', selector: 'container[name=activationContainer]' },
        { ref: 'deActivationContainer', selector: 'container[name=deActivationContainer]' },
        { ref: 'industryField', selector: 'industryfield[name=industry]' },
        { ref: 'shopSelection', selector: 'combo[name=shopSelection]' },
        { ref: 'typeRadios', selector: 'radiogroup[name=typeRadios]' },
        { ref: 'typeFieldSet', selector: 'fieldset[name=typeFieldSet]' },
    ],

    init: function () {
        this.control({
            'benchmark-settings-window': {
                shopConfigLoaded: this.onShopConfigsLoaded,
                configSelected: this.onConfigSelect,
                activateBenchmark: this.activateBenchmark,
                deactivateBenchmark: this.deactivateBenchmark,
                saveType: this.onSaveType
            },
            'industryfield': {
                changeIndustry: this.onChangeIndustry
            },
            'benchmark-settings-industry-window': {
                saveIndustry: this.onSaveIndustry
            }
        });

        this.callParent(arguments);
    },

    /**
     * @param { Shopware.apps.Benchmark.store.ShopConfigs } store
     * @param { Ext.form.field.ComboBox } comboBox
     */
    onShopConfigsLoaded: function (store, comboBox) {
        var firstRecord = store.first();

        comboBox.select(firstRecord);
        comboBox.fireEvent('select', comboBox, [firstRecord]);
    },

    /**
     * @param { Shopware.apps.Benchmark.view.settings.Window } settingsWin
     * @param { Ext.form.field.ComboBox } combo
     * @param { Shopware.apps.Benchmark.model.ShopConfig } record
     */
    onConfigSelect: function (settingsWin, combo, record) {
        this.getSettingsPanel().loadSettingsRecord(record);
    },

    onChangeIndustry: function () {
        this.getView('settings.IndustryWindow').create().show();
    },

    /**
     * @param { Shopware.apps.Benchmark.view.settings.IndustryWindow } win
     * @param { integer } val
     * @param { Function } callback
     */
    onSaveIndustry: function (win, val, callback) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=Benchmark action=saveIndustry}',
            params: {
                industry: val,
                shopId: me.getShopSelection().getValue()
            },
            success: function (response) {
                var responseData = Ext.decode(response.responseText);

                win.destroy();
                me.getIndustryField().setValue(val);
                me.getSettingsPanel().getRecord().set('industry', val);

                if (responseData.success) {
                    Shopware.Notification.createGrowlMessage(
                        '{s name="growlMessage/industry_window/success/title"}Save successful{/s}',
                        '{s name="growlMessage/industry_window/success/message"}The chosen industry was saved successfully{/s}',
                        'BenchmarkIndustryWindow'
                    );

                    callback();

                    return;
                }

                Shopware.Notification.createGrowlMessage(
                    '{s name="growlMessage/industry_window/error/title"}Error saving the industry{/s}',
                    responseData.message ,
                    'BenchmarkIndustryWindow'
                );
            }
        });
    },

    activateBenchmark: function () {
        var me = this,
            record = this.getSettingsPanel().getRecord();

        if (record.get('industry') === 0) {
            this.getView('settings.IndustryWindow').create({
                additionalText: '{s name="settings/industry_window/choose_industry"}Please choose an industry first:{/s}',
                customCallback: function () {
                    me.sendBenchmarkActiveStatus(1, {
                        successTitle: '{s name="growlMessage/activation/success/title"}Success{/s}',
                        successMessage: '{s name="growlMessage/activation/success/message"}You\'re now participating{/s}',
                        errorTitle: '{s name="growlMessage/activation/error/title"}Error{/s}',
                        confirmationTitle: '{s name="growlMessage/activation/confirmation/title"}Participate{/s}',
                        confirmationMessage: '{s name="growlMessage/activation/confirmation/message"}Do you really wish to participate?{/s}'
                    })
                }
            }).show();

            return;
        }

        this.sendBenchmarkActiveStatus(1, {
            successTitle: '{s name="growlMessage/activation/success/title"}Success{/s}',
            successMessage: '{s name="growlMessage/activation/success/message"}You\'re now participating{/s}',
            errorTitle: '{s name="growlMessage/activation/error/title"}Error{/s}',
            confirmationTitle: '{s name="growlMessage/activation/confirmation/title"}Participate{/s}',
            confirmationMessage: '{s name="growlMessage/activation/confirmation/message"}Do you really wish to participate?{/s}'
        });
    },

    deactivateBenchmark: function () {
        this.sendBenchmarkActiveStatus(0, {
            successTitle: '{s name="growlMessage/deactivation/success/title"}Success{/s}',
            successMessage: '{s name="growlMessage/deactivation/success/message"}You\'re not participating any more{/s}',
            errorTitle: '{s name="growlMessage/deactivation/error/title"}Error{/s}',
            confirmationTitle: '{s name="growlMessage/deactivation/confirmation/title"}Signing off{/s}',
            confirmationMessage: '{s name="growlMessage/deactivation/confirmation/message"}Do you really wish to stop the service?{/s}'
        });
    },

    /**
     * @param { integer } active
     * @param { Object } snippets
     */
    sendBenchmarkActiveStatus: function (active, snippets) {
        var me = this;

        Ext.MessageBox.confirm(snippets.confirmationTitle, snippets.confirmationMessage, function (response) {
            if (response !== 'yes') {
                return false;
            }

            Ext.Ajax.request({
                url: '{url controller=Benchmark action=setActive}',
                params: {
                    active: active,
                    shopId: me.getShopSelection().getValue()
                },
                success: function (response) {
                    var responseData = Ext.decode(response.responseText);

                    if (responseData.success) {
                        Shopware.Notification.createGrowlMessage(
                            snippets.successTitle,
                            snippets.successMessage,
                            'BenchmarkSettings'
                        );

                        me.getActivationContainer()[active ? 'hide' : 'show']();
                        me.getDeActivationContainer()[active ? 'show' : 'hide']();
                        me.getShopSelection().getStore().load();

                        return;
                    }

                    Shopware.Notification.createGrowlMessage(
                        snippets.errorTitle,
                        responseData.message,
                        'BenchmarkSettings'
                    );
                }
            });
        });
    },

    onSaveType: function () {
        var me = this,
            radios = this.getTypeRadios(),
            values = radios.getValue(),
            selectedValue = values.type,
            typeFieldSet = me.getTypeFieldSet();

        typeFieldSet.setLoading(true);

        Ext.Ajax.request({
            url: '{url controller=Benchmark action=saveType}',
            params: {
                type: selectedValue,
                shopId: me.getShopSelection().getValue()
            },
            success: function (response) {
                var responseData = Ext.decode(response.responseText);

                typeFieldSet.setLoading(false);

                if (responseData.success) {
                    Shopware.Notification.createGrowlMessage(
                        '{s name="growlMessage/type/success/title"}Save successful{/s}',
                        '{s name="growlMessage/type/success/message"}The business plan was successfully saved{/s}',
                        'BenchmarkSettings'
                    );

                    me.getShopSelection().getStore().load();

                    return;
                }

                Shopware.Notification.createGrowlMessage(
                    '{s name="growlMessage/type/error/title"}Error{/s}',
                    responseData.message,
                    'BenchmarkSettings'
                );
            }
        });
    }
});
//{/block}
