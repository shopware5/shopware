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
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/main}
// {block name="backend/customer/view/main/window"}
Ext.define('Shopware.apps.Customer.view.main.Window', {
    extend: 'Enlight.app.Window',
    cls: Ext.baseCSSPrefix + 'customer-list-window',
    alias: 'widget.customer-list-main-window',
    border: false,
    autoShow: true,
    layout: {
        type: 'fit'
    },
    width: '95%',
    height: '95%',
    title: '{s name=window_title}{/s}',

    createItems: function() {
        var me = this, tabs = [];

        me.quickView = Ext.create('Shopware.apps.Customer.view.main.QuickView');
        me.streamView = Ext.create('Shopware.apps.Customer.view.main.StreamView');

        tabs.push(me.quickView);

        /*{if {acl_is_allowed resource=customerstream privilege=read}}*/
            tabs.push(me.streamView);
        /*{/if}*/

        me.tabPanel = Ext.create('Ext.tab.Panel', {
            flex: 1,
            items: tabs,
            activeTab: (me.subApp.action && me.subApp.action === 'customer_stream') ? 1 : 0
        });

        me.on('afterrender', function() {
            if (me.subApp.action !== 'customer_stream' || !me.subApp.params || !me.subApp.params.streamId) {
                return;
            }

            Ext.defer(function() {
                var record = me.streamView.streamListing.getStore().getById(
                    window.parseInt(me.subApp.params.streamId)
                );

                me.streamView.streamListing.getSelectionModel().select([record]);
            }, 200);
        });

        return [me.tabPanel];
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function () {
        var me = this;

        Ext.suspendLayouts();

        me.items = me.createItems();

        Ext.resumeLayouts(true);

        me.callParent(arguments);
    }
});
// {/block}
