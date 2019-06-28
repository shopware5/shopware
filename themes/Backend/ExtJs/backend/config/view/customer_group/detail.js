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

//{namespace name=backend/config/view/customer_group}

//{block name="backend/config/view/customer_group/detail"}
Ext.define('Shopware.apps.Config.view.customerGroup.Detail', {
    extend: 'Shopware.apps.Config.view.base.Detail',
    alias: 'widget.config-customergroup-detail',

    store: 'detail.CustomerGroup',

    snippets: {
        items:{
            name: '{s name=items/name}Name{/s}',
            key: '{s name=items/key}Key{/s}',
            taxInput: '{s name=items/tax_input}Tax input{/s}',
            taxOutput: '{s name=items/tax_output}Tax output{/s}',
            discountMode:'{s name=items/discount_mode}Discount mode{/s}',
            discount:'{s name=items/discount}Discount{/s}',
            minimumOrder:'{s name=items/minimum_order}Min. order value{/s}',
            minimumOrderSurcharge:'{s name=minimum_order_surcharge}Order surcharge{/s}'
        }
    },

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: me.getItems()
        });

        me.callParent(arguments);
    },

    /**
     * Override the loadRecord method of config-base-detail in order to
     * and make the default's customer group key "EK" non-editable
     * @param record
     */
    loadRecord: function(record) {
        var me = this,
            groupKeyField;

        groupKeyField = me.down('field[name=key]');

        if(record && record.get('id') === 1 && groupKeyField) {
            groupKeyField.setDisabled(true);
        }else if(groupKeyField) {
            groupKeyField.setDisabled(false);
        }

        me.callOverridden(arguments);
    },

    getItems: function() {
        var me = this;
        return [{
            name: 'name',
            fieldLabel: me.snippets.items.name,
            allowBlank: false
        },{
            name: 'key',
            fieldLabel: me.snippets.items.key,
            allowBlank: false,
            maxLength: 15
        },{
            xtype: 'config-element-boolean',
            name: 'taxInput',
            fieldLabel: me.snippets.items.taxInput
        },{
            xtype: 'config-element-boolean',
            name: 'tax',
            fieldLabel: me.snippets.items.taxOutput
        },{
            xtype: 'config-element-boolean',
            name: 'mode',
            fieldLabel: me.snippets.items.discountMode,
            handler: function(button, value) {
                var form = button.up('form'),
                    discount = form.down('field[name=discount]');
                if(value) {
                    discount.show();
                } else {
                    discount.hide();
                }
            }
        },{
            xtype: 'config-element-number',
            name: 'discount',
            decimalPrecision: 2,
            fieldLabel: me.snippets.items.discount,
            hidden: true
        },{
            xtype: 'config-element-number',
            name: 'minimumOrder',
            decimalPrecision: 2,
            fieldLabel: me.snippets.items.minimumOrder
        },{
            xtype: 'config-element-number',
            name: 'minimumOrderSurcharge',
            decimalPrecision: 2,
            fieldLabel: me.snippets.items.minimumOrderSurcharge
        }, {
            xtype: 'config-customergroup-discount'
        }];
    }
});
//{/block}
