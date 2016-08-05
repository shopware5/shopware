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
 * @package    Order
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/order/main}

/**
 * Shopware UI - Order detail page
 */
//{block name="backend/order/view/detail/dispatch"}
Ext.define('Shopware.apps.Order.view.detail.Dispatch', {
    /**
     * Define that the dispatch field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend: 'Ext.form.FieldSet',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.order-dispatch-field-set',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'dispatch-field-set',
    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets: {
        title:'{s name=dispatch/title}Dispatch data{/s}',
        dispatchMethod:'{s name=dispatch/fields/dispatch_method}Dispatch method{/s}'
    },

    /**
     * @return void
     */
    initComponent:function () {
        var me = this;

        me.title = me.snippets.title;
        me.items = me.createItems();

        me.callParent(arguments);
    },

    /**
     * @return Ext.Component[]
     */
    createItems: function() {
        var me = this;

        return [{
            xtype: 'container',
            layout: 'anchor',
            items: [
                me.createDispatchCombo()
            ]
        }];
    },

    /**
     * @return Ext.form.field.ComboBox
     */
    createDispatchCombo: function() {
        var me = this;

        me.dispatchCombo = Ext.create('Ext.form.field.ComboBox', {
            name: 'dispatchId',
            fieldLabel: me.snippets.dispatchMethod,
            store: me.dispatchesStore,
            queryMode: 'local',
            valueField: 'id',
            displayField: 'name',
            triggerAction: 'all',
            required: true,
            allowBlank: false,
            editable: false,
            anchor: '97.5%',
            labelWidth: 155,
            listeners: {
                change: function(field, newValue) {
                    me.fireEvent('changeDispatch', me, newValue);
                }
            }
        });

        return me.dispatchCombo;
    }
});
//{/block}
