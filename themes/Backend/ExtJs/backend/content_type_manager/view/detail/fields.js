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

// {namespace name="backend/content_type_manager/main"}
// {block name="backend/content_type_manager/view/detail/fields"}
Ext.define('Shopware.apps.ContentTypeManager.view.detail.Fields', {
    alias: 'widget.content-type-manager-detail-fields',
    extend: 'Ext.grid.Panel',

    viewConfig: {
        plugins: {
            ptype: 'gridviewdragdrop'
        },
    },

    initComponent: function () {
        this.tbar = this.createTopToolbar();
        this.columns = this.getColumns();

        this.callParent(arguments);
    },

    getColumns: function () {
        var me = this;

        return [
            {
                header: 'Label',
                dataIndex: 'label',
                flex: 1
            },
            {
                header: 'Typ',
                dataIndex: 'type',
                flex: 0.5,
                renderer: this.renderTypeField
            },
            {
                xtype: 'actioncolumn',
                width: 60,
                items: [
                    {
                        iconCls: 'sprite-pencil',
                        handler: function (view, rowIndex, colIndex, item, opts, record) {
                            me.fireEvent('editField', record, me.store);
                        }
                    },
                    {

                        iconCls: 'sprite-minus-circle-frame',
                        handler: function (view, rowIndex, colIndex, item, opts, record) {
                            me.fireEvent('deleteField', me.store, record);
                        }
                    }
                ]
            }
        ]
    },

    createTopToolbar: function() {
        var me = this;

        me.createButton = Ext.create('Ext.button.Button', {
            text: '{s name="detail/addNewField"}{/s}',
            iconCls: 'sprite-plus-circle-frame',
            handler: function () {
                me.fireEvent('createNewField', me, me.store);
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [me.createButton]
        });
    },

    renderTypeField: function (value) {
        var record = this.fieldSelectionStore.findRecord('id', value);

        if (record) {
            return record.get('label');
        }

        return value;
    }
});
// {/block}
