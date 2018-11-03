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

/**
 * todo@all: Documentation
 */

//{namespace name=backend/config/view/main}

//{block name="backend/config/view/base/form"}
Ext.define('Shopware.apps.Config.view.base.Form', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.config-base-form',

    layout: 'border',
    border: false,

    deletable: true,
    addable: true,

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: me.getItems()
        });

        me.addEvents(
            'delete', 'edit'
        );

        me.callParent(arguments);
    },

    getDetail: function() {
        return this.down('config-base-detail');
    },

    getTable: function() {
        return this.down('config-base-table');
    },

    getDeleteButton: function() {
        return this.down('config-base-table button[action=delete]');
    },

    getItems: function() {
        return [];
    },

    getActionColumn: function() {
        var me = this,
            items = [];
        if(me.deletable) {
        /*{if {acl_is_allowed privilege=delete}}*/
            items.push({
                iconCls: 'sprite-minus-circle-frame',
                action: 'delete',
                tooltip:'{s name=form/delete_tooltip}Delete (ALT + DELETE){/s}',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('delete', me, record);
                },
                getClass: function(value, metadata, record, rowIdx) {
                    if (record.get('deletable') === false)  {
                        return 'x-hidden';
                    }
                }
            });
        /* {/if} */
        }
        if(true) {
        /*{if {acl_is_allowed privilege=update}}*/
            items.push({
                iconCls: 'sprite-pencil',
                action: 'edit',
                tooltip: '{s name=form/edit_tooltip}Edit{/s}',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('edit', me, record);
                }
            });
        /* {/if} */
        }
        return {
            xtype: 'actioncolumn',
            width: 70,
            items: items
        };
    }
});
//{/block}
