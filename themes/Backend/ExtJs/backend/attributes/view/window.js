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
 *
 * @category   Shopware
 * @package    ProductStream
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/attributes/main"}

Ext.define('Shopware.apps.Attributes.view.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.attributes-window',
    cls: 'attributes-detail-window',

    layout: {
        type: 'hbox',
        align: 'stretch'
    },

    title: '{s name="window_title"}{/s}',

    width: '85%',
    height: '85%',

    initComponent: function() {
        var me = this;
        me.items = me.createItems();
        me.callParent(arguments);
    },

    createItems: function() {
        var me = this;

        me.listingStore = Ext.create('Shopware.apps.Attributes.store.Column');
        me.listingStore.getProxy().extraParams.raw = 1;
        
        me.listing = Ext.create('Shopware.apps.Attributes.view.Listing', {
            region: 'center',
            store: me.listingStore,
            flex: 1,
            table: me.table
        });

        return [
            me.listing,
            me.createDetailForm()
        ];
    },

    createDetailForm: function() {
        var me = this;

        me.detail = Ext.create('Shopware.apps.Attributes.view.Detail', {
            flex: 1
        });

        me.detailForm = Ext.create('Ext.form.Panel', {
            items: [me.detail],
            region: 'east',
            plugins: {
                ptype: 'snippet-translation',
                namespace: 'backend/attribute_columns',
                getSnippetName: function(field) {
                    var record = me.detailForm.getRecord();
                    return record.get('tableName') + '_' + record.get('columnName') + '_' + field.name;
                }
            },
            disabled: true,
            bodyPadding: 20,
            cls: 'shopware-form',
            layout: { type: 'hbox', align: 'stretch' },
            width: 600,
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                items: [
                '->',
                /*{if {acl_is_allowed privilege=update}}*/
                {
                    xtype: 'button',
                    cls: 'primary',
                    text: '{s name="save_button"}{/s}',
                    handler: function() {
                        me.fireEvent('save-column', me.detailForm);
                    }
                }
                /*{/if}*/
                ]
            }]
        });

        return me.detailForm;
    }
});
