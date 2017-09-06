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
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name="backend/newsletter_manager/main"}

/**
 * Shopware Controller - Main controller
 * The main controller creates the main window
 */
// {block name="backend/newsletter_manager/controller/main"}
Ext.define('Shopware.apps.NewsletterManager.controller.Main', {

    extend: 'Ext.app.Controller',

    /**
     * Class property which holds the main application if it is created
     *
     * @default null
     * @object
     */
    mainWindow: null,

    /**
     * A template method that is called when your application boots. It is called before the Application's
     * launch function is executed so gives a hook point to run any code before your Viewport is created.
     */
    init: function() {
        var me = this;

        me.subApplication.mailingStore = me.getStore('Mailing').load();
        me.subApplication.senderStore = me.getStore('Sender').load();
        me.subApplication.recipientStore = me.getStore('Recipient').load();
        me.subApplication.recipientGroupStore = me.getStore('RecipientGroup').load();
        me.subApplication.newsletterGroupStore = me.getStore('NewsletterGroup').load();
        me.subApplication.customerGroupStore = me.getStore('Shopware.apps.Base.store.CustomerGroup').load();

        // Don't do the default filtering - get all shops
        me.subApplication.shopStore = Ext.create('Shopware.apps.Base.store.Shop', {
            filters: []
        }).load();

        // Create main window, pass stores
        me.mainWindow = me.getView('main.Window').create({
            mailingStore: me.subApplication.mailingStore,
            senderStore: me.subApplication.senderStore,
            recipientStore: me.subApplication.recipientStore,
            recipientGroupStore: me.subApplication.recipientGroupStore,
            newsletterGroupStore: me.subApplication.newsletterGroupStore
        });

        me.subApplication.mainWindow = me.mainWindow;
        me.mainWindow.show();
        me.callParent(arguments);
    }
});
// {/block}
