/*
 * @category   Shopware
 * @package    Order
 * @subpackage View
 * @copyright  Copyright (c) 2012, Heidelberger Payment GmbH (http://www.heidelpay.de)
 * @version    12.07
 * @author     Jens Richter
 * @author     $Author$
 */

//{extends file="[default]backend/order/view/detail/window.js"}
//{block name="backend/order/view/detail/window"}
//{namespace name=backend/order/main}
	Ext.define('Shopware.apps.Order.view.detail.Window-HeidelActions', {
	
    /**
     * Defines an override applied to a class.
     * @string
     */
    override: 'Shopware.apps.Order.view.detail.Window',
 
    /**
     * List of classes that have to be loaded before instantiating this class.
     * @array
     */
 
    requires: [ 'Shopware.apps.Order.view.detail.Window' ],
    
    /**
     * Creates the tab panel for the detail page.
     * @return Ext.tab.Panel
     */
    createTabPanel: function() {
        var me = this;

        return Ext.create('Ext.tab.Panel', {
            region: 'center',
            items: [
                {
                    xtype: 'order-overview-panel',
                    title: me.snippets.overview,
                    record: me.record
                }, {
                    xtype: 'order-detail-panel',
                    title: me.snippets.details,
                    record: me.record
                }, {
                    xtype: 'order-communication-panel',
                    title: me.snippets.communication,
                    record: me.record
                }, {
                    xtype: 'order-position-panel',
                    title: me.snippets.position,
                    record: me.record,
                    taxStore: me.taxStore,
                    statusStore: me.statusStore
                }, {
                    xtype: 'order-document-panel',
                    record: me.record
                }, {
                    xtype: 'order-history-list',
                    title: me.snippets.history,
                    historyStore: me.historyStore,
                    record: me.record
                }
                // Heidelpay Start
                , {
                    xtype: 'order-heidelpay-panel',
                    title: 'Heidelpay',
                    record: me.record
                }
                // Heidelpay End

                
            ]
        });
    }
 
});
//{/block}
