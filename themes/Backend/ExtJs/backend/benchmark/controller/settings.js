
//{namespace name="backend/benchmark/main"}
//{block name="backend/benchmark/controller/settings"}
Ext.define('Shopware.apps.Benchmark.controller.Settings', {
    extend: 'Ext.app.Controller',

    refs: [
        { ref: 'settingsPanel', selector: 'form[name=benchmark-settings-panel]' },
        { ref: 'activationFieldSet', selector: 'fieldset[name=activationFieldSet]' },
        { ref: 'deactivationFieldSet', selector: 'fieldset[name=deactivationFieldSet]' },
        { ref: 'businessField', selector: 'businessfield[name=business]' }
    ],

    init: function () {
        this.control({
            'benchmark-settings-window': {
                saveSettings: this.onSaveSettings,
                activateBenchmark: this.activateBenchmark,
                deactivateBenchmark: this.deactivateBenchmark
            },
            'businessfield': {
                changeBusiness: this.onChangeBusiness
            },
            'benchmark-settings-business-window': {
                saveBusiness: this.onSaveBusiness
            }
        });

        this.callParent(arguments);
    },

    onSaveSettings: function () {
        var settingsPanel = this.getSettingsPanel(),
            values = settingsPanel.getValues();

        settingsPanel.setLoading(true);
        Ext.Ajax.request({
            url: '{url controller=Benchmark action=saveSettings}',
            params: {
                ordersBatchSize: values.ordersBatchSize
            },
            success: function (response) {
                var responseData = Ext.decode(response.responseText);

                settingsPanel.setLoading(false);

                if (responseData.success) {
                    Shopware.Notification.createGrowlMessage(
                        '{s name="growlMessage/settings/success/title"}Save successful{/s}',
                        '{s name="growlMessage/settings/success/message"}The benchmark settings were successfully saved{/s}',
                        'BenchmarkBusinessWindow'
                    );

                    return;
                }

                Shopware.Notification.createGrowlMessage(
                    '{s name="growlMessage/settings/error/title"}Error saving the settings{/s}',
                    responseData.message ,
                    'BenchmarkBusinessWindow'
                );
            }
        });
    },

    onChangeBusiness: function () {
        this.getView('settings.BusinessWindow').create().show();
    },

    /**
     * @param { Shopware.apps.Benchmark.view.settings.BusinessWindow } win
     * @param { integer } val
     */
    onSaveBusiness: function (win, val) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=Benchmark action=saveBusiness}',
            params: {
                business: val
            },
            success: function (response) {
                var responseData = Ext.decode(response.responseText);

                win.destroy();
                me.getBusinessField().setValue(val);

                if (responseData.success) {
                    Shopware.Notification.createGrowlMessage(
                        '{s name="growlMessage/business_window/success/title"}Save successful{/s}',
                        '{s name="growlMessage/business_window/success/message"}The chosen business was saved successfully{/s}',
                        'BenchmarkBusinessWindow'
                    );

                    return;
                }

                Shopware.Notification.createGrowlMessage(
                    '{s name="growlMessage/business_window/error/title"}Error saving the business{/s}',
                    responseData.message ,
                    'BenchmarkBusinessWindow'
                );
            }
        });
    },

    activateBenchmark: function () {
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
                    active: active
                },
                success: function (response) {
                    var responseData = Ext.decode(response.responseText);

                    if (responseData.success) {
                        Shopware.Notification.createGrowlMessage(
                            snippets.successTitle,
                            snippets.successMessage,
                            'BenchmarkSettings'
                        );

                        me.getActivationFieldSet()[active ? 'hide' : 'show']();
                        me.getDeactivationFieldSet()[active ? 'show' : 'hide']();

                        return;
                    }

                    Shopware.Notification.createGrowlMessage(
                        snippets.errorTitle,
                        responseData.message ,
                        'BenchmarkSettings'
                    );
                }
            });
        });
    }
});
//{/block}
