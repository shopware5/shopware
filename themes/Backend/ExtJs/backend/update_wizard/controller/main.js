
//{namespace name=backend/update_wizard/translation}

Ext.define('Shopware.apps.UpdateWizard.controller.Main', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'cardContainer', selector: 'update-wizard-window container[name=update-wizard-card-container]' },
        { ref: 'pluginPage', selector: 'update-wizard-window update-wizard-plugins' },
        { ref: 'wizardWindow', selector: 'update-wizard-window' }
    ],

    init: function() {
        var me = this;

        me.control({
            'update-wizard-start': {
                'update-wizard-display-login': me.displayLogin
            },
            'update-wizard-login': {
                'close-update-wizard': me.closeUpdateWizard,
                'update-wizard-display-plugin-page': me.displayPluginPage
            },
            'update-wizard-plugins': {
                'close-update-wizard': me.closeUpdateWizard
            }
        });

        me.mainWindow = me.getView('Window').create().show();

        this.callParent(arguments);
    },

    closeUpdateWizard: function() {
        var me = this,
            window = me.getWizardWindow();

        window.destroy();
    },

    displayLogin: function() {
        var me = this,
            container = me.getCardContainer();

        container.getLayout().next();
        me.updateTitle('{s name="login_headline"}{/s}');
    },

    updateTitle: function(title) {
        var me = this,
            window = me.getWizardWindow();

        window.setTitle(title);
    },

    displayPluginPage: function() {
        var me = this,
            pluginPage = me.getPluginPage(),
            container = me.getCardContainer();

        container.setLoading(true);

        me.sendAjaxRequest(
            '{url controller=UpdateWizard action=update}',
            { },
            function(response) {
                container.setLoading(false);
                me.updateTitle('{s name="plugins_headline"}{/s}');
                pluginPage.refreshData(response);
                container.getLayout().next();
            },
            function(response) {
                container.setLoading(false);
                Shopware.Notification.createGrowlMessage('', response.message);
            }
        );
    },

    sendAjaxRequest: function(url, params, callback, errorCallback) {
        var me = this;

        Ext.Ajax.request({
            url: url,
            method: 'POST',
            params: params,
            success: function(operation, opts) {
                var response = Ext.decode(operation.responseText);
                if (response.success === false && Ext.isFunction(errorCallback)) {
                    errorCallback(response);
                } else if (response.success == false) {
                    Shopware.Notification.createGrowlMessage('', response.message);
                } else {
                    callback(response);
                }
            }
        });
    }
});
