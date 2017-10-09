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

// {namespace name="backend/newsletter_manager/main"}

/**
 * Shopware UI - Subwindow for creating a new newsletter or edit an existing one
 */
// {block name="backend/newsletter_manager/view/newsletter/window"}
Ext.define('Shopware.apps.NewsletterManager.view.newsletter.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.newsletter-manager-newsletter-window',
    autoShow: true,
    layout: 'fit',
    width: 860,
    height: '90%',
    stateful: true,
    stateId: 'shopware-newsletter-manager-newsletter-window',
    title: '{s name=titleCreateNewsletter}Create new newsletter{/s}',
    cls: Ext.baseCSSPrefix + 'newsletter-window',
    defaults: {
        bodyBorder: 0
    },
    record: null,

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
        me.bbar = me.getBottomToolbar();

        me.addEvents(
                /**
                 * Fired when the data needs to be loaded
                 */
                'loadData'
        );

        if (me.record === null) {
            me.record = Ext.create('Shopware.apps.NewsletterManager.model.Mailing');
        }

//        me.dockedItems = me.createNoticeContainer();
        me.items = me.createTab();

        me.callParent(arguments);
    },

    /**
     * Creates the toolbar for this view which allows the user to set his mail, sent a testmail and preview the mail
     *
     * @return [Ext.toolbar.Toolbar] toolbar
     */
    getBottomToolbar: function() {
        var me = this;

        me.toolbar = Ext.create('widget.newsletter-manager-bottom-toolbar');

        return me.toolbar;
    },
    /**
     * Creates the tab panel for the main window
     */
    createTab: function() {
        var me = this;

        me.tabPanel = Ext.create('Ext.tab.Panel', {
            deferredRender: false,
            items: me.getTabs()
        });
        return me.tabPanel;
    },

    /**
     * Creates the main tab
     * internal titles needed in the main controller to tell apart the different tabs
     * @return Array
     */
    getTabs: function() {
        var me = this;

        return [{
            xtype: 'newsletter-manager-newsletter-editor',
            record: me.record
        }, {
            xtype: 'newsletter-manager-newsletter-settings',
            senderStore: me.senderStore,
            recipientGroupStore: me.recipientGroupStore,
            newsletterGroupStore: me.newsletterGroupStore,
            customerGroupStore: me.customerGroupStore,
            shopStore: me.shopStore,
            dispatchStore: me.dispatchStore,
            record: me.record
        }];
    }
});
// {/block}
