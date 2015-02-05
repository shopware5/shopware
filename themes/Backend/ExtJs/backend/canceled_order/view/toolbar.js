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
 * @package    CanceledOrder
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/canceled_order/view/main}

/**
 * Shopware UI - Basic toolbar with search and from-to widgets
 * used by basket, orders and statistics-view
 */
//{block name="backend/canceled_order/view/toolbar"}
Ext.define('Shopware.apps.CanceledOrder.view.Toolbar', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.canceled-order-toolbar',
    ui: 'shopware-ui',

    snippets: {
        date: {
            from: '{s name=date/from}From{/s}',
            to: '{s name=date/to}To{/s}'
        },
        filter: {
            button: '{s name=filter/button}Filter{/s}',
            tooltip: '{s name=filter/tooltip}Filter by selected dates{/s}'
        }
    },


    /**
     * Initializes the component and registers some events
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = me.getItems();

        // register search event
        me.addEvents('search', 'filter', 'dateEnter');

        me.callParent(arguments);
    },

    /**
     * Creates the items for the toolbar (fromDate, toDate, filter-Button and searchField)
     * @return Array
     */
    getItems: function() {
        var me = this,
            firstOfMonth = new Date(),
            today    = new Date();

        firstOfMonth.setDate(1);
        firstOfMonth.setMonth(0);

        me.searchField = me.createSearchField();

        me.fromDate = Ext.create('Ext.form.field.Date', {
            fieldLabel: me.snippets.date.from,
            name: 'fromdate',
            labelWidth: 40,
            maxValue: today,
            value: firstOfMonth,
            listeners: {
                specialkey: function(field, event) {
                    if( event.getKey() == event.ENTER) {
                        me.fireEvent('dateEnter', me.fromDate.getValue(), me.toDate.getValue());
                    }
                }
            }
        });

        me.toDate = Ext.create('Ext.form.field.Date', {
            fieldLabel: me.snippets.date.to,
            labelWidth: 40,
            name: 'todate',
            maxValue: today,
            value: today,
            listeners: {
                specialkey: function(field, event) {
                    if( event.getKey() == event.ENTER) {
                        me.fireEvent('dateEnter', me.fromDate.getValue(), me.toDate.getValue());
                    }
                }
            }
        });
        return [
            me.fromDate,
            me.toDate,
            {
                xtype: 'button',
                iconCls: 'sprite-filter',
                text: me.snippets.filter.button,
                tooltip : me.snippets.filter.tooltip,
                handler: function(button) {
                    me.fireEvent('filter', me.fromDate.getValue(), me.toDate.getValue());
                }
            },
            '->',
            me.searchField
        ];
    },

    /**
     * Creates the search field for the toolbar
     * @return Object
     */
    createSearchField: function() {
        var me = this;

        return {
            xtype : 'textfield',
            name : 'searchfield',
            action : 'searchSupplier',
            width: 170,
            cls: 'searchfield',
            enableKeyEvents : true,
            emptyText : '{s name=search_empty}Search...{/s}',
            checkChangeBuffer: 700,
            listeners: {
                scope: me,
                change: function (value){
                    me.fireEvent('search', value)
                }
             }
        }
    }
});
//{/block}
