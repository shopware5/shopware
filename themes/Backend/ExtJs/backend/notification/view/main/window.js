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
 * @package    Notification
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/notification/view/main}

/**
 * Shopware UI - Notification detail main window.
 *
 * Displays the Notification statistic
 */
//{block name="backend/notification/view/main/window"}
Ext.define('Shopware.apps.Notification.view.main.Window', {
    extend: 'Enlight.app.Window',
    alias : 'widget.notification-main-window',
    title : '{s name=title}Notification{/s}',
    layout:{
        type:'border'
    },
    width: 950,
    height: '90%',
    autoShow: true,
    stateful: true,
    stateId: 'shopware-notification-main-window',
    defaults: {
        layout:'fit',
        border: false,
        split:true
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.items = [ me.getLeftPanel(), me.getRightPanel() ];
        me.callParent(arguments);
    },

    /**
     * helper function to return the articlePanel
     *
     * @public
     * @return void
     */
    getLeftPanel: function() {
        var me = this;
        return Ext.create('Ext.panel.Panel', {
            title: '{s name=notification/article/panel/title}Articles with notifications{/s}',
            items:[
                {
                    xtype:'notification-notification-article',
                    articleStore:me.articleStore
                }
            ],
            layout:'fit',
            border: false,
            split:true,
            region: 'center'
        });
    },

    /**
     * helper function to return the customerPanel
     *
     * @public
     * @return void
     */
    getRightPanel: function() {
        var me = this;
        return Ext.create('Ext.panel.Panel', {
            title: '{s name=notification/customer/panel/title}Customer registered for Notification{/s}',
            items:[
                {
                    xtype:'notification-notification-customer',
                    customerStore:me.customerStore
                }
            ],
            layout:'fit',
            border: false,
            split:true,
            region: 'east',
            width:475
        });
    }
});
//{/block}
