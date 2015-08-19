
Ext.define('Shopware.apps.ProductStream.view.defined_list.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.product-stream-defined-list-window',
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
        this.settingsPanel.loadRecord(record);
        if (record.get('id')) {
            this.activateProductGrid(record);
        }
    },

    activateProductGrid: function(record) {
        this.productGrid.streamId = record.get('id');
        this.productGrid.store.getProxy().extraParams.streamId = record.get('id');
        this.productGrid.store.load();
        this.productGrid.enable();
    },

    createToolbar: function() {
        var me = this;

        me.saveButton = Ext.create('Ext.button.Button', {
            text: 'Save',
            cls: 'primary',
            handler: function () {
                me.fireEvent('save-defined-list', me.record);
            }
        });

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            items: ['->', me.saveButton],
            dock: 'bottom'
        });
        return me.toolbar;
    },

    createItems: function() {
        return [
            this.createSettingPanel(),
            this.createProductGrid()
        ];
    },

    createProductGrid: function() {
        this.productGrid = Ext.create('Shopware.apps.ProductStream.view.defined_list.Product', {
            flex: 1,
            disabled: true
        });
        return this.productGrid;
    },

    createSettingPanel: function() {
        this.settingsPanel = Ext.create('Shopware.apps.ProductStream.view.common.Settings');
        return this.settingsPanel;
    }
});
