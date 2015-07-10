Ext.define('Shopware.apps.ProductStream.view.list.List', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.product-stream-listing-grid',
    region: 'center',

    addButtonText: 'Add condition stream',

    configure: function () {
        return {
            detailWindow: 'Shopware.apps.ProductStream.view.condition_list.Window',
            columns: {
                name: null,
                description: null
            }
        };
    },

    createFeatures: function() {
        var me = this,
            features = me.callParent(arguments);

        features.push(me.createGroupingFeature());
        return features;
    },

    createGroupingFeature: function() {
        var me = this;

        return Ext.create('Ext.grid.feature.Grouping', {
            groupHeaderTpl: [
                '{literal}{name:this.formatName}{/literal}',
                {
                    formatName: function(type) {
                        if (type == 2) {
                            return 'Defined stream(s)';
                        } else {
                            return 'Condition stream(s)';
                        }
                    }
                }
            ]
        });
    },

    createToolbarItems: function () {
        var me = this, items;
        items = me.callParent(arguments);

        items = Ext.Array.insert(items, 1, [{
            xtype: 'button',
            iconCls: 'sprite-plus-circle-frame',
            text: 'Add defined stream',
            handler: function () {
                var record = Ext.create('Shopware.apps.ProductStream.model.Stream');
                record.set('type', 2);
                me.fireEvent('open-defined-list-window', record);
            }
        }]);

        return items;
    },

    createEditColumn: function () {
        var me = this,
            column = me.callParent(arguments);

        column.handler = function (view, rowIndex, colIndex, item, opts, record) {
            if (record.get('type') == 2) {
                record.reload({
                    callback: function (result) {
                        me.fireEvent('open-defined-list-window', result);
                    }
                });
            } else {
                me.fireEvent(me.eventAlias + '-edit-item', me, record, rowIndex, colIndex, item, opts);
            }
        };

        return column;
    }
});
