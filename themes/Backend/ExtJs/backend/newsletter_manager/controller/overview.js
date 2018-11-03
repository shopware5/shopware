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
 * Shopware Controller - Overview controller
 * For events and actions fired in the overview tab
 */
// {block name="backend/newsletter_manager/controller/overview"}
Ext.define('Shopware.apps.NewsletterManager.controller.Overview', {

    extend: 'Ext.app.Controller',

    snippets: {
        deleteNewsletter: {
            successTitle: '{s name=deleteNewsletter/successTitle}Successfully deleted{/s}',
            successMessage: '{s name=deleteNewsletter/successMessage}Successfully deleted the newsletter{/s}',
            errorTitle: '{s name=deleteNewsletter/errorTitle}Error{/s}',
            errorMessage: '{s name=deleteNewsletter/errorMessage}An error occured while deleting the newsletter{/s}'
        },
        growl: '{s name=title}Newsletter Manager{/s}',
        grid: {
            activated: '{s name=grid/activated}Newsletter activated{/s}',
            activatedTitle: '{s name=grid/activated_title}Released{/s}',
            deactivated: '{s name=grid/deactivated}Newsletter deactivated{/s}',
            deactivatedTitle: '{s name=grid/deactivated_title}Deactivated{/s}'
        }
    },

    refs: [
        { ref: 'newsletterEditor', selector: 'newsletter-manager-newsletter-editor' },
        { ref: 'newsletterSettings', selector: 'newsletter-manager-newsletter-settings' },
        { ref: 'overviewGrid', selector: 'newsletter-manager-tabs-overview' }
    ],

    /**
     * A template method that is called when your application boots. It is called before the Application's
     * launch function is executed so gives a hook point to run any code before your Viewport is created.
     */
    init: function() {
        var me = this;

        me.control({
            'newsletter-manager-tabs-overview': {
                'createNewNewsletter': me.onCreateNewNewsletter,
                'editNewsletter': me.onEditNewsletter,
                'startSendingNewsletter': me.onStartSendingNewsletter,
                'deleteNewsletter': me.onDeleteNewsletter,
                'duplicateNewsletter': me.onDuplicateNewsletter,
                'searchNewsletter': me.onSearchNewsletter,
                'releaseNewsletter': me.onReleaseNewsletter
            }
        });

        me.callParent(arguments);
    },

    /**
     * Called when the user types into the serach newsletter field
     */
    onSearchNewsletter: function(field) {
        if (!field) {
            return;
        }

        var me = this,
            searchString = Ext.String.trim(field.getValue()),
            store = me.subApplication.mailingStore;

        // scroll the store to first page
        store.currentPage = 1;

        // If the search-value is empty, reset the filter
        if (searchString.length === 0) {
            store.clearFilter();
        } else {
            // This won't reload the store
            store.filters.clear();
            // Loads the store with a special filter
            store.filter('filter', searchString);
        }
    },

    /**
     * Called when the user clicks the 'duplicate' action button
     * @param record
     */
    onDuplicateNewsletter: function(record) {},

    /**
     * Called when the user clicked the "delete" action button in the newsletter overview
     * @param record
     */
    onDeleteNewsletter: function(record) {
        var me = this,
            store = me.subApplication.mailingStore;

        Ext.MessageBox.confirm('{s name=deleteNewsletter}Delete newsletter(s){/s}', '{s name=delteNewsletterMessage}Do you really want to delete the selected newsletter?{/s}', function (response) {
            if (response !== 'yes') {
                return;
            }
            store.remove([record]);
            Shopware.Notification.createGrowlMessage(me.snippets.deleteNewsletter.successTitle, me.snippets.deleteNewsletter.successMessage, me.snippets.growl);
            store.save();
        });
    },

    /**
     * Called when the user clicks the 'start sending' newsletter button in the overview
     * @param record
     */
    onStartSendingNewsletter: function(record) {
        var pos = location.href.search('/backend'),
            url = location.href.substr(0, pos) + '/backend/Newsletter/cron';

        Ext.MessageBox.confirm('{s name=startSendingNewsletter/title}Start sending{/s}', '{s name=startSendingNewsletter/message}Do you really want to start sending this newsletter?{/s}', function (response) {
            if (response !== 'yes') {
                return;
            }
            record.set('status', 1);
            record.set('publish', 1);
            record.save();
            Ext.Msg.show({
                title: '{s name=startSendingNewsletter/title}Start sending{/s}',
                //
                msg: '{s name=startSendingNewsletterInfo/message}The newsletter is now queued for sending.<br />Please make sure, that you have set up the newsletter-script as a cron job or run it manually.<br /><br />Do you want to open the newsletter-script in a new window now?{/s}',
                buttons: Ext.Msg.YESNO,
                icon: Ext.Msg.QUESTION,
                fn: function(response) {
                    if (response !== 'yes') {
                        return;
                    }
                    window.open(url);
                }
            });
        });
    },

    /**
     * Called when the edit button in the action column was clicked
     * Will open the newsletter-editor window and load the existing newsletter
     */
    onEditNewsletter: function(record) {
        var me = this,
            settings = Ext.create('Shopware.apps.NewsletterManager.model.Settings');
        
        me.getView('newsletter.Window').create({
            senderStore: me.subApplication.senderStore,                     // available senders
            recipientGroupStore: me.subApplication.recipientGroupStore,     // available newsletter groups + available customer groups
            newsletterGroupStore: me.subApplication.newsletterGroupStore,   // available newsletter groups
            customerGroupStore: me.subApplication.customerGroupStore,        // available customer groups
            shopStore: me.subApplication.shopStore,
            customerStreamStore: me.subApplication.customerStreamStore,
            dispatchStore:  me.getStore('MailDispatch'),
            title: Ext.String.format("{s name=newsletterWindowEditTitle}Editing newsletter '{literal}{0}{/literal}{/s}'", record.get('subject')),
            record: record
        });

        // As the existing database table holds some strings where IDs would be needed, we have a additional
        // settings model, which translates between the Newsletter-Model ("Mailing") and the structure needed
        // to set the form up properly.
        settings.set('subject', record.get('subject'));
        settings.set('customerGroup', record.get('customerGroup'));
        settings.set('languageId', record.get('languageId'));
        if (record.get('plaintext') == true) {
            settings.set('dispatch', 2);
        } else {
            settings.set('dispatch', 1);
        }

        var editor = me.getNewsletterEditor(), form = me.getNewsletterSettings(),
            senderMail = record.get('senderMail'),
            containers, text, content = '', senderRecord;

        containers = record.getContainers();
        if (containers instanceof Ext.data.Store && containers.first() instanceof Ext.data.Model) {
            text = containers.first().getText();
            if (text instanceof Ext.data.Store && text.first() instanceof Ext.data.Model) {
                content = text.first().get('content');
            }
        }
        settings.set('content', content);

        // sender is saved as plain text. need to get the id from senderStore
        senderRecord = me.subApplication.senderStore.findRecord('email', senderMail);
        if (!(senderRecord instanceof Ext.data.Model)) {
            settings.set('senderId', null);
        } else {
            settings.set('senderId', senderRecord.get('id'));
        }

        form.loadRecord(settings);

        // TinyMCE will be loaded last - it hast some getDoc() us undefined issues
        setTimeout(function() {
            editor.loadRecord(settings);
        }, 500);
    },

    /**
     * Called when the "create new newsletter" button in the toolbar was clicked
     * Will open an empty newsletter-editor
     */
    onCreateNewNewsletter: function() {
        var me = this,
            settings = Ext.create('Shopware.apps.NewsletterManager.model.Settings'),
            form = me.getNewsletterSettings();

        me.getView('newsletter.Window').create({
            senderStore: me.subApplication.senderStore,                     // available senders
            recipientGroupStore: me.subApplication.recipientGroupStore,     // available newsletter groups + available customer groups
            newsletterGroupStore: me.subApplication.newsletterGroupStore,   // available newsletter groups
            customerGroupStore: me.subApplication.customerGroupStore,        // available customer groups
            shopStore: me.subApplication.shopStore,
            customerStreamStore: me.subApplication.customerStreamStore,
            dispatchStore:  me.getStore('MailDispatch')
        });

        var editor = me.getNewsletterEditor(), form = me.getNewsletterSettings();
        settings.set('customerGroup', me.subApplication.customerGroupStore.first().get('key'));
        form.loadRecord(settings);
    },

    /**
     * If the user clicks on the active actioncolumn it will update the active flag
     * @param record
     * @param grid
     */
    onReleaseNewsletter: function(record, grid) {
        var id = record.get('id'),
            me = this;
        // inverts and converts the active flag
        var status = (record.get('status') > 0 ? 0 : 1);

        Ext.Ajax.request({
            url: '{url controller="newsletterManager" action="releaseNewsletter"}',
            method: 'GET',
            params: {
                status: status,
                id: id
            },
            success: function() {
                record.set('status', status);
                grid.getStore().reload();
                if (status) {
                    Shopware.Notification.createGrowlMessage(me.snippets.grid.activatedTitle, me.snippets.grid.activated);
                } else {
                    Shopware.Notification.createGrowlMessage(me.snippets.grid.deactivatedTitle, me.snippets.grid.deactivated);
                }
            }
        });
    }

});
// {/block}
