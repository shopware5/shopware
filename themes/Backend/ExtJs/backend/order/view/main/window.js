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
 * Shopware UI - Order list main window.
 *
 * todo@all: Documentation
 */
//{block name="backend/order/view/main/window"}
Ext.define('Shopware.apps.Order.view.main.Window', {
    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'order-list-main-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.order-list-main-window',
    /**
     * Set no border for the window
     * @boolean
     */
    border:false,
    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow:true,
    /**
     * Set border layout for the window
     * @string
     */
    layout:'border',
    /**
     * Define window width
     * @integer
     */
    width:1300,
    /**
     * Define window height
     * @integer
     */
    height:'90%',
    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable:true,
    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable:true,
    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:true,
    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-order-main-window',
    /**
     * Set window title which is displayed in the window header
     * @string
     */
    title:'{s name=window_title}Order list{/s}',


    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent:function () {
      var me = this;

      //add the order list grid panel and set the store
      me.items = [
            {
                xtype: 'container',
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                region: 'center',
                items: [Ext.create('Shopware.apps.Order.view.list.List', {
                    flex: 2,
                    listStore: me.listStore,
                    orderStatusStore: me.orderStatusStore,
                    paymentStatusStore: me.paymentStatusStore,
                    statusStore: me.statusStore,
                    taxStore: me.taxStore
                }), Ext.create('Shopware.apps.Order.view.list.Position', {
                    collapsed: true,
                    collapsible: true,
                    flex: 1,
                    title:'{s name=position_grid_title}Order positions{/s}',
                    region: 'south',
                    statusStore: me.statusStore,
                    taxStore: me.taxStore
                })]
            }, Ext.create('Shopware.apps.Order.view.list.Navigation', {
                region: 'west',
                orderStatusStore: me.orderStatusStore,
                paymentStatusStore: me.paymentStatusStore
            })
      ];

      me.callParent(arguments);
  }

});
//{/block}
