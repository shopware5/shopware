
//{namespace name=backend/plugin_manager/translation}
Ext.define('Shopware.apps.PluginManager.view.list.LicencePage', {
    extend: 'Shopware.grid.Panel',
    cls: 'plugin-manager-licence-page',
    alias: 'widget.plugin-manager-licence-page',

    configure: function() {
        return {
            deleteButton: false,
            addButton: false,
            deleteColumn: false,
            editColumn: false,
            columns: {
                label: {
                    header: '{s name="plugin_name"}{/s}'
                },
                shop: {
                    header: '{s name="shop"}{/s}'
                },
                creationDate: {
                    header: '{s name="creation_date"}{/s}',
                    renderer: this.dateRenderer
                },
                expirationDate: {
                    header: '{s name="valid_to"}{/s}',
                    renderer: this.dateRenderer
                },
                priceColumn: {
                    header: '{s name="version"}{/s}',
                    renderer: this.priceRenderer
                },
                binaryVersion: {
                    header: '{s name="binary_version"}{/s}'
                }
            }
        };
    },

    mixins: {
        events: 'Shopware.apps.PluginManager.view.PluginHelper'
    },

    createToolbarItems: function() {
        var me = this,
            items = me.callParent(arguments);

        me.on('licence-selection-changed', function(grid, selModel, selection) {
            if (selection.length > 0) {
                me.downloadButton.enable();
                me.importLicenceButton.enable();
            } else {
                me.downloadButton.disable();
                me.importLicenceButton.disable();
            }
        });

        me.downloadButton = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-inbox-download',
            text: '{s name="download_selected_plugins"}{/s}',
            disabled: true,
            handler: function() {
                var selModel = me.getSelectionModel();

                me.queueRequests(
                    'download-plugin-licence',
                    selModel.getSelection(),
                    function() {
                        Shopware.app.Application.fireEvent('reload-local-listing');
                        me.hideLoadingMask();
                    }
                );
            }
        });

        me.importLicenceButton = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-key',
            text: '{s name="import_selected_licences"}{/s}',
            disabled: true,
            handler: function() {
                var selModel = me.getSelectionModel();

                me.queueRequests(
                    'import-plugin-licence',
                    selModel.getSelection(),
                    function() {
                        me.hideLoadingMask();
                    }
                );
            }
        });

        items = Ext.Array.insert(items, 0, [ me.downloadButton, me.importLicenceButton ]);

        return items;
    },

    queueRequests: function(event, records, callback) {
        var me = this;

        if (records.length <= 0) {
            if (Ext.isFunction(callback)) {
                callback();
            }
            return;
        }

        var record = records.shift();

        Shopware.app.Application.fireEvent(
            event,
            record,
            function() {
                me.queueRequests(event, records, callback);
            }
        );
    },

    dateRenderer: function(value) {
        if (!value || !value.hasOwnProperty('date')) {
            return value;
        }
        var date = this.formatDate(value.date);
        return Ext.util.Format.date(date);
    },

    priceRenderer: function(value, metaData, record) {
        var me = this;

        var price = record['getPriceStore'];

        if (price && price.first()) {
            price = price.first();
            return me.getTextForPriceType(price.get('type'));
        } else {
            return value;
        }
    },

    createActionColumnItems: function() {
        var me = this,
            items = me.callParent(arguments);

        items.push({
            iconCls: 'sprite-key',
            tooltip: '{s name="import_licence"}{/s}',
            getClass: function(value, metaData, record) {
                if (!record.get('licenseKey')) {
                    return Ext.baseCSSPrefix + 'hidden';
                }
            },
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                Shopware.app.Application.fireEvent(
                    'import-plugin-licence',
                    record,
                    function() {
                        me.hideLoadingMask();
                    }
                );
            }
        });

        items.push({
            iconCls: 'sprite-inbox-download',
            tooltip: '{s name="download_plugin"}{/s}',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                Shopware.app.Application.fireEvent(
                    'download-plugin-licence',
                    record,
                    function() {
                        Shopware.app.Application.fireEvent('reload-local-listing');
                        me.hideLoadingMask();
                    }
                );
            },
            getClass: function(value, metaData, record) {
                if (!record.get('binaryLink')) {
                    return Ext.baseCSSPrefix + 'hidden';
                }
            }
        });

        return items;
    }
});