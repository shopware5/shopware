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
 * @package    NewsletterManager
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/newsletter_manager/main"}

/**
 * Shopware UI - Main window of this app
 * Main window will be displayed after the user starts the application
 */
//{block name="backend/newsletter_manager/view/main/window"}
Ext.define('Shopware.apps.NewsletterManager.view.main.Window', {
    extend: 'Enlight.app.Window',
    alias : 'widget.newsletter-manager-main-window',
    layout: 'fit',
    width: 860,
    height: '90%',
    stateful: true,
    stateId: 'shopware-newsletter-manager-main-window',
    title: '{s name=title}Newsletter Manager{/s}',

    /**
     * Init the component, add noticeContainer and Tabs
     */
    initComponent: function() {
        /**
         * Initializes the component, adds NoticeContainer and tabs
         *
         * @return void
         */
        var me = this;
//        me.dockedItems = me.createNoticeContainer();
        me.items = me.createTab();
        me.callParent(arguments);
    },


    /**
     * Creates the tab panel for the main window
     */
    createTab: function() {
        var me = this;

        me.tabPanel =  Ext.create('Ext.tab.Panel', {
            items: me.getTabs()
        });
        return me.tabPanel;
    },

    /**
     * Creates the main tab
     * internal titles needed in the main controller to tell apart the different tabs
     * @return Array
     */
    getTabs: function(){
        var me = this;

        return [{
            xtype:'newsletter-manager-tabs-overview',
            store: me.mailingStore
        },
        {
            xtype:'newsletter-manager-tabs-admin',
            senderStore: me.senderStore,
            recipientStore: me.recipientStore,
            recipientGroupStore: me.recipientGroupStore,
            newsletterGroupStore: me.newsletterGroupStore,
            mailingStore: me.mailingStore
        }
        ];

    }
});
//{/block}
