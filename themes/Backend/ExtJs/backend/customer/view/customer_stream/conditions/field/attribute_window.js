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
 * @package    Customer
 * @subpackage CustomerStream
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/main}

// {block name="backend/customer/view/customer_stream/conditions/field/attribute_window"}

Ext.define('Shopware.apps.Customer.view.customer_stream.conditions.field.AttributeWindow', {
    extend: 'Enlight.app.Window',
    modal: true,
    width: 600,
    height: 145,
    title: '{s name=attribute/input_text}{/s}',
    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    applyCallback: function(field) { },

    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.dockedItems = [me.createToolbar()];
        me.callParent(arguments);
    },

    createItems: function() {
        var me = this;

        var store = Ext.create('Shopware.store.AttributeConfig');
        store.getProxy().extraParams.table = 's_user_attributes';

        me.attributeCombo = Ext.create('Ext.form.field.ComboBox', {
            name: 'attribute',
            labelWidth: 180,
            fieldLabel: '{s name=select_attribute}{/s}',
            pageSize: 20,
            store: store,
            valueField: 'columnName',
            allowBlank: false,
            displayField: 'label'
        });

        return [{
            xtype: 'form',
            bodyPadding: 20,
            border: false,
            items: [me.attributeCombo],
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            flex: 1
        }];
    },

    createToolbar: function() {
        var me = this;
        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: ['->', {
                xtype: 'button',
                text: '{s name=apply}{/s}',
                cls: 'primary',
                handler: function() {
                    if (me.attributeCombo.getValue()) {
                        me.applyCallback(me.attributeCombo.getValue());
                        me.destroy();
                    }
                }
            }]
        });
    }
});
// {/block}
