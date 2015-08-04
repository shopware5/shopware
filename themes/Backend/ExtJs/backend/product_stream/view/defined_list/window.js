
Ext.define('Shopware.apps.ProductStream.view.defined_list.Window', {
    extend: 'Ext.window.Window',
    alias: 'widget.product-stream-defined-list-window',
    title : '{s name=title}ProductStream details{/s}',
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

    save: function(record) {
        if (!this.settingsPanel.getForm().isValid()) {
            return;
        }
        this.settingsPanel.getForm().updateRecord(record);
        this.saveRecord(record);
    },

    activateProductGrid: function(record) {
        this.productGrid.streamId = record.get('id');
        this.productGrid.store.getProxy().extraParams.streamId = record.get('id');
        this.productGrid.store.load();
        this.productGrid.enable();
    },

    saveRecord: function(record) {
        var me = this;

        record.save({
            callback: function() {
                Shopware.Notification.createGrowlMessage('Product stream', 'Stream saved');
                record.reload({
                    callback: function(result) {
                        me.activateProductGrid(result);
                    }
                });
            }
        });
    },

    createToolbar: function() {
        var me = this;

        me.saveButton = Ext.create('Ext.button.Button', {
            text: 'Save',
            cls: 'primary',
            handler: function () {
                me.save(me.record);
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
        this.settingsPanel = Ext.create('Shopware.apps.ProductStream.view.defined_list.Settings');
        return this.settingsPanel;
    }
});
