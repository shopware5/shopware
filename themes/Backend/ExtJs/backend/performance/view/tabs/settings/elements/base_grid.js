/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

//{namespace name=backend/performance/main}

/**
 * Basic grid with cellediting plugin
 */
//{block name="backend/performance/view/tabs/settings/elements/base_grid_time"}
Ext.define('Shopware.apps.Performance.view.tabs.settings.elements.BaseGrid', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.grid.Panel',

    /**
     * The view needs to be scrollable
     * @string
     */
    autoScroll: true,

    viewConfig: {
        markDirty: false
    },

    /**
     * Initialize the Shopware.apps.Customer.view.main.List and defines the necessary
     * default configuration
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.cellEditingPlugin = me.getEditingPlugin();
        me.plugins = [ me.cellEditingPlugin ];

        me.toolbar = me.getToolbar();
        me.dockedItems = [ me.toolbar ];
        me.columns = me.getColumns();

        me.callParent(arguments);
    },

    /**
     * Sets up the editing plugin and registers some related events
     * @returns Ext.grid.plugin.RowEditing
     */
    getEditingPlugin: function() {
        var me = this;

        me.on('canceledit', function(editor, e) {
            var record = e.record;

            me.store.remove(record);
        });

        return Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2,
            errorSummary: false,
            pluginId: 'rowEditing'
        });
    },

    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function () {
        var me = this,
            textField;

        textField = Ext.create('Ext.form.field.Text', {
            name: 'value',
            flex: 1,
            listeners: {
                specialkey: function(field, event){
                    if (event.getKey() == event.ENTER) {
                        var record = Ext.create('Shopware.apps.Performance.model.KeyValue'),
                            key = field.getValue(),
                            split = key.split(' '),
                            value = '';

                        if (split[1]) {
                            key = split[0];
                            value = split[1];
                        }

                        record.set('key', key);
                        record.set('value', value);
                        me.store.add(record);
                        field.setValue('');
                        me.cellEditingPlugin.startEdit(record, 1);
                    }
                }
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock:'top',
            items:[
                textField,
                {
                    iconCls:'sprite-plus-circle-frame',
                    text:'{s name=grid/addEntry}Add entry{/s}',
                    cls: 'secondary small',
                    action:'add-entry',
                    handler: function() {
                        var record = Ext.create('Shopware.apps.Performance.model.KeyValue'),
                            field = textField,
                            key = field.getValue(),
                            split = key.split(' '),
                            value = '';

                        if (split[1]) {
                            key = split[0];
                            value = split[1];
                        }

                        record.set('key', key);
                        record.set('value', value);
                        me.store.add(record);
                        field.setValue('');
                        me.cellEditingPlugin.startEdit(record, 1);
                    }
                }
            ]
        });
    }


});
//{/block}
