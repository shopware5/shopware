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
 * @subpackage Window
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/product_stream/main}
//{block name="backend/product_stream/view/condition_list/field/property_window"}
Ext.define('Shopware.apps.ProductStream.view.condition_list.field.PropertyWindow', {
    extend: 'Ext.window.Window',
    modal: true,
    bodyPadding: 10,
    width: 400,

    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    applyCallback: function(group) { },

    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.dockedItems = [me.createToolbar()];
        me.callParent(arguments);
    },

    createItems: function() {
        var me = this;

        me.propertyCombo = Ext.create('Ext.form.field.ComboBox', {
            name: 'property',
            fieldLabel: '{s name=select_group}Select group{/s}',
            pageSize: 20,
            store: me.createStore(),
            valueField: 'id',
            allowBlank: false,
            displayField: 'name'
        });

        me.notice = Ext.create('Ext.container.Container', {
            html:  '{s name=property/input_text}Please select a property group{/s}',
            height: 40
        });

        return [me.notice, me.propertyCombo];
    },

    createStore: function() {
        return Ext.create('Shopware.store.Search', {
            fields: ['id', 'name'],
            pageSize: 20,
            configure: function() {
                return { entity: "Shopware\\Models\\Property\\Option" }
            }
        });
    },

    createToolbar: function() {
        var me = this;
        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: ['->', {
                xtype: 'button',
                text: '{s name=apply}Apply{/s}',
                cls: 'primary',
                handler: function() {
                    if (me.propertyCombo.getValue()) {
                        var group = me.propertyCombo.getStore().getById(me.propertyCombo.getValue());
                        me.applyCallback(group);
                        me.destroy();
                    }
                }
            }]
        });
    }
});
//{/block}
