/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package    Ticket
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
 */
//{namespace name=backend/ticket/main}
//{block name="backend/ticket/controller/locale"}
Ext.define('Shopware.apps.Ticket.controller.Locale', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.app.Controller',

    /**
     * Array of configs to build up references to views on page
     * @array
     */
    refs: [
        { ref: 'localePanel', selector: 'ticket-settings-locale' },
        { ref: 'localeWindow', selector: 'ticket-settings-add-locale-window' }
    ],

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return void
     */
    init:function () {
        var me = this;

        me.control({

            /** Submission settings */
            'ticket-settings-locale': {
                addLocale: me.onAddLocale,
                deleteLocale: me.onDeleteLocale
            },
            'ticket-settings-add-locale-window': {
                saveLocale: me.onSaveLocale
            }
        });
    },

    /**
     * Opens the "add locale" window.
     *
     * @public
     * @event click
     * @return void
     */
    onAddLocale: function() {
        var me = this;

        me.subApplication.unusedLocaleStore = me.subApplication.getStore('UnusedLocale');

        me.getView('settings.AddLocale').create({
            localeStore: me.subApplication.localeStore,
            unusedLocaleStore: me.subApplication.unusedLocaleStore
        });
    },

    /**
     * Event listener method which will be triggered when the user
     * clicks the "add shop specific submission" button.
     *
     * The method validates the form panel and sends an ajax request which duplicates
     * the submissions.
     *
     * @public
     * @event click
     * @return [boolean]
     */
    onSaveLocale: function() {
        var me = this,
            win = me.getLocaleWindow(),
            store = me.subApplication.localeStore,
            formPnl = win.formPanel,
            form = formPnl.getForm(),
            values = form.getValues();

        if(!form.isValid()) {
            Shopware.Notification.createGrowlMessage('{s name=window_title}Ticket system{/s}', '{s name=locale/error/forms_fill_all_fields}Please fill out all required fields (marked red) to save the form.{/s}');
            return false;
        }

        Ext.Ajax.request({
            url: '{url action="duplicateMails"}',
            params: values,
            callback: function() {
                store.load();
                win.destroy();
                me.subApplication.submissionStore.load();
            }
        });
    },

    /**
     * Event listener method which will be triggered when the user
     * clicks on the "delete submission" button.
     *
     * @public
     * @event click
     * @param [object] btn - Ext.button.Button
     * @param [object] view - Shopware.apps.Ticket.view.settings.Locale
     * @return [boolean]
     */
    onDeleteLocale: function(scope, record) {
        var me = this,
            store = me.subApplication.localeStore,
            grid =  me.getLocalePanel(),
            selModel = grid.getSelectionModel(),
            selection = selModel.getSelection(),
            shopId = 0;

        record = record || undefined;

        // If we don't have a record, terminate it over the selection model of the grid.
        if(!record) {
            if(!selection) {
                return false;
            }
            record = selection[0];
        }
        shopId = record.get('id');

        Ext.Ajax.request({
            url: '{url action="deleteMailSubmissionByShopId"}',
            params: {
                shopId: ~~(1 * shopId)
            },
            callback: function() {
                store.load();
                me.subApplication.submissionStore.load();
            }
        });
    }
});
//{/block}
