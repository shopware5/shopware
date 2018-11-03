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
//{block name="backend/order/view/detail/window"}
Ext.define('Shopware.apps.Order.view.detail.Window', {
    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'order-detail-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.order-detail-window',
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
     * Set fit layout for the window
     * @string
     */
    layout:'fit',
    /**
     * Define window width
     * @integer
     */
    width:900,
    /**
     * Define window height
     * @integer
     */
    height:'90%',
    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:true,
    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-order-detail-window',
    /**
     * Contains all snippets for this component
     */
    snippets: {
        title: '{s name=tab/title}Order details:{/s}',
        overview: '{s name=tab/overview}Overview{/s}',
        details: '{s name=tab/details}Details{/s}',
        communication: '{s name=tab/communication}Communication{/s}',
        document: '{s name=tab/document}Documents{/s}',
        position: '{s name=tab/position}Positions{/s}',
        history: '{s name=tab/history}Status history{/s}'
    },

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
        me.items = [ me.createTabPanel() ];
        me.title = me.snippets.title + ' ' + me.record.get('number');
        me.callParent(arguments);
    },

    /**
     * Creates the tab panel for the detail page.
     * @return Ext.tab.Panel
     */
    createTabPanel: function() {
        var me = this;

        return Ext.create('Ext.tab.Panel', {
            name: 'main-tab',
            items: [
                Ext.create('Shopware.apps.Order.view.detail.Overview', {
                    title: me.snippets.overview,
                    record: me.record,
                    orderStatusStore: me.orderStatusStore,
                    paymentStatusStore:  me.paymentStatusStore
                }), Ext.create('Shopware.apps.Order.view.detail.Detail',{
                    title: me.snippets.details,
                    record: me.record,
                    paymentsStore: me.paymentsStore,
                    dispatchesStore: me.dispatchesStore,
                    shopsStore: me.shopsStore,
                    countriesStore: me.countriesStore
                }), Ext.create('Shopware.apps.Order.view.detail.Communication',{
                    title: me.snippets.communication,
                    record: me.record
                }), Ext.create('Shopware.apps.Order.view.detail.Position', {
                    title: me.snippets.position,
                    record: me.record,
                    taxStore: me.taxStore,
                    statusStore: me.statusStore
                }), Ext.create('Shopware.apps.Order.view.detail.Document',{
                    record: me.record,
                    documentTypesStore: me.documentTypesStore
                }), Ext.create('Shopware.apps.Order.view.detail.OrderHistory', {
                    title: me.snippets.history,
                    historyStore: me.historyStore,
                    record: me.record,
                    orderStatusStore: me.orderStatusStore,
                    paymentStatusStore:  me.paymentStatusStore
                })
            ]
        });
    }
});
//{/block}
