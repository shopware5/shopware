
//{namespace name=backend/update_wizard/translation}
Ext.define('Shopware.apps.UpdateWizard.view.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.update-wizard-window',
    cls: 'update-wizard-window',

    layout: {
        type: 'hbox',
        align: 'stretch'
    },
    defaults: {
        flex: 1
    },

    minWidth: 820,
    height: 467,
    title: '{s name="start_headline"}{/s}',

    initComponent: function() {
        var me = this;

        me.items = me.createItems();

        this.callParent(arguments);
    },

    createItems: function() {
        var me = this;

        me.startContainer = Ext.create('Shopware.apps.UpdateWizard.view.Start');
        me.loginContainer = Ext.create('Shopware.apps.UpdateWizard.view.Login');
        me.pluginsContainer = Ext.create('Shopware.apps.UpdateWizard.view.Plugins');

        me.cardContainer = Ext.create('Ext.container.Container', {
            items: [me.startContainer, me.loginContainer, me.pluginsContainer],
            layout: 'card',
            border: false,
            padding: 10,
            name: 'update-wizard-card-container',
            cls: 'update-wizard-card-container'
        });

        return [me.cardContainer];
    }
});
