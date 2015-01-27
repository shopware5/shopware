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
 * Shopware UI - Administration
 * Basic configuration for the newsletter manager
 */
//{block name="backend/newsletter_manager/view/tabs/admin"}
Ext.define('Shopware.apps.NewsletterManager.view.tabs.Admin', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.newsletter-manager-tabs-admin',
    title: '{s name=admin}Administration{/s}',
    layout: 'fit',
    bodyBorder: 0,
    border: false,
    defaults: {
        bodyBorder: 0
    },

    /**
     * Initializes the component, sets up toolbar and pagingbar and and registers some events
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        // Create the items of the container
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
     * Creates the admin tab
     * @return Array
     */
    getTabs: function(){
        var me = this;

        return [{
            xtype: 'newsletter-manager-tabs-sender',
            store: me.senderStore
        },
        {
            xtype: 'newsletter-manager-tabs-recipient-groups',
            store: me.recipientGroupStore
        },
        {
            xtype: 'newsletter-manager-tabs-recipients',
            store: me.recipientStore,
            newsletterGroupStore: me.newsletterGroupStore,
            mailingStore: me.mailingStore
        }
        ];
    }


});
//{/block}
