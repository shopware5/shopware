
Ext.define('Shopware.apps.ProductStream.view.condition_list.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.product-stream-detail-window',

    title : '{s name=title}Product Stream details{/s}',
    height: '90%',
    width: '90%',
    layout: { type: 'vbox', align: 'stretch'},
    bodyPadding: 10,

    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.dockedItems = [me.createToolbar()];

        me.callParent(arguments);
        me.loadRecord(me.record);
    },

    loadRecord: function(record) {
        var me = this;

        me.settingsPanel.loadRecord(record);
        me.conditionPanel.removeAll();
        me.conditionPanel.loadConditions(record);
    },

    createToolbar: function() {
        var me = this;

        me.saveButton = Ext.create('Ext.button.Button', {
            text: 'Save',
            cls: 'primary',
            handler: function () {
                me.fireEvent('save-filtered-stream', me.record);
            }
        });

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            items: ['->', me.saveButton],
            dock: 'bottom'
        });
        return me.toolbar;
    },

    createItems: function() {
        var me = this,
            items = [];

        items.push(me.createSettingPanel());

        me.previewGrid = Ext.create('Shopware.apps.ProductStream.view.condition_list.PreviewGrid', {
            flex: 3
        });

        me.conditionPanel = Ext.create('Shopware.apps.ProductStream.view.condition_list.ConditionPanel', {
            flex: 2,
            margin: '0 10 0 0'
        });

        var container = Ext.create('Ext.container.Container', {
            layout: { type: 'hbox', align: 'stretch'},
            flex: 1,
            items: [
                me.conditionPanel,
                me.previewGrid
            ]
        });

        items.push(container);
        return items;
    },

    createSettingPanel: function() {
        this.settingsPanel = Ext.create('Shopware.apps.ProductStream.view.common.Settings');
        return this.settingsPanel;
    }
});
