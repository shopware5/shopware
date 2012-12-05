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
 * @package    SwagAboCommerce
 * @subpackage ExtJs
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     shopware AG
 */

/**
 * Shopware ExtJs controller
 */
//{namespace name="backend/abo_commerce/view/main"}
//{block name="backend/abo_commerce/controller/abo_commerce"}
Ext.define('Shopware.apps.Article.controller.AboCommerce', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.app.Controller',

    refs: [
        { ref: 'detailWindow', selector: 'article-detail-window' },
        { ref: 'aboCommerceTabSettings', selector: 'article-detail-window abo-commerce-tab-settings' },
        { ref: 'aboCommerceConfiguration', selector: 'article-detail-window abo-commerce-configuration' },
        { ref: 'aboCommercePriceListing', selector: 'article-detail-window abo-commerce-price-listing' }
    ],

    /**
     * Custom component property, contains a flag to identify if events fired
     * while loading a record into the view.
     */
    onLoadRecord: false,

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     */
    init: function () {
        var me = this;

        me.control({
            'article-detail-window': {
                aboCommerceTabActivated: me.onAboCommerceTabActivated,
                aboCommerceTabDeactivated: me.onAboCommerceTabDeactivated,
                saveAboCommerce: me.onSaveAboCommerce
            },

            'article-detail-window abo-commerce-price-listing': {
                addPrice: me.onAddPrice,
                deletePrice: me.onDeletePrice,
                changePrice: me.onChangePrice
            },
            'article-detail-window abo-commerce-price-listing grid': {
                edit: me.onAfterEditPrice,
                beforeedit: me.onBeforeEditPrice
            }
        });
    },

    /**
     * Event listener function which fired when the user
     * starts the edit of a price row.
     *
     * @param plugin
     * @param event
     * @return
     */
    onBeforeEditPrice: function(plugin, event) {
        console.log("OnBeforeEditPrice");

        var store = event.grid.store,
            maxValue = null,
            minValue = 1,
            record = event.record,
            editor = event.column.getEditor(event.record),
            previousRecord = store.getAt(event.rowIdx -1),
            nextRecord = store.getAt(event.rowIdx + 1);

        //check if the current row is the last row
        if (event.field === "durationFrom") {
            //if the current row isn't the last row, we want to cancel the edit.
            if (nextRecord) {
                maxValue = nextRecord.get('durationFrom') - 1;
            }

            //check if the current row has a previous row.
            if (previousRecord) {
                minValue = previousRecord.get('durationFrom') + 1;
            } else {
                minValue = 1;
                maxValue = 1;
            }

            editor.setMinValue(minValue);
            editor.setMaxValue(maxValue);

            return;
        }

        if (event.field === "to") {
            //if the current row isn't the last row, we want to cancel the edit.
            if (nextRecord) {
                return false;
            }

            if (previousRecord) {
                minValue = record.get('durationFrom') + 1;
            }

            editor.setMinValue(minValue);
            return;
        }
    },

    /**
     * Event listener function which fired when the user
     * edit a column of the price grid.
     * This function handles the calculation for the
     * prices and discounts.
     *
     * @param editor
     * @param event
     */
    onAfterEditPrice: function(editor, event) {
        var me = this,
            record = event.record,
            store = event.grid.store,
            nextRecord = store.getAt(event.rowIdx + 1);

        //user changed the "to" field?
        if (event.field === 'to') {
            //check if the user insert a numeric to value
            if (Ext.isNumeric(event.value)) {
                //if this is the case we need to check if the current row is the last row.
                if (!nextRecord) {
                    //if the current row is the last row, we need to add a new row with "to any"
                    var newRecord = Ext.create('Shopware.apps.Article.model.abo_commerce.Price', {
                        durationFrom: event.value + 1,
                        customerGroupKey: record.get('customerGroupKey', null)
                    });
                    console.log("durationfrom", event.value);
                    console.log("newRec", newRecord.data);

                    store.add(newRecord);
                } else {
                    //if the current row is not the last row we have to increase the from value of the next row
                    nextRecord.set('durationfrom', event.value + 1);
                }
            }
        }

        if (event.field === 'dicountAbsolute') {
            record.set('dicountPercent', null);
        }

        if (event.field === 'dicountPercent') {
            record.set('dicountAbsolute', null);
        }

        event.grid.reconfigure(event.grid.getStore());
    },

    /**
     * @EventListener
     * Event listener function of the article detail window.
     * Fired when the user activate the abo commerce tab.
     *
     * @param window Shopware.apps.Article.view.detail.Window
     */
    onAboCommerceTabActivated: function(window) {
        var me = this,
            record = null;

        window.aboCommerceSaveButton.show();
        window.saveButton.hide();
        window.configuratorSaveButton.hide();

        me.aboCommerceDetailStore.load({
            callback: function(records, operation) {
                if (operation.wasSuccessful() && records.length > 0) {
                    record = records[0];
                } else {
                    record = Ext.create('Shopware.apps.Article.model.abo_commerce.AboCommerce');
                }

                me.getAboCommerceTabSettings().loadRecord(record);
                me.getAboCommerceConfiguration().loadRecord(record);
                me.getAboCommercePriceListing().setRecord(record);
            }
        });
    },

    /**
     * @EventListener
     * Event listener function of the article detail window.
     * Fired when the user change the active tab of the main tab panel from abo commerce to
     * another tab.
     * @param window Shopware.apps.Article.view.detail.Window
     */
    onAboCommerceTabDeactivated: function(window) {
        window.aboCommerceSaveButton.hide();
    },

    /**
     * @EventListener
     * Event listener function of the article detail window.
     * Fired when the user activate the abo commerce tab.
     *
     * @param window Shopware.apps.Article.view.detail.Window
     */
    onSaveAboCommerce: function(window) {
        var me = this;

        var configurationFormPanel = me.getAboCommerceConfiguration();
        var configurationForm = configurationFormPanel.getForm();
        var settingsFormPanel = me.getAboCommerceTabSettings();
        var settingsForm = settingsFormPanel.getForm();

        if (!settingsForm.isValid()) {
            return;
        }

        var record = settingsForm.getRecord();

        if (record === undefined) {
            record = Ext.create('Shopware.apps.Article.model.abo_commerce.AboCommerce');
        }

        settingsForm.updateRecord(record);
        configurationForm.updateRecord(record);

//        settingsFormPanel.setLoading(true);
        record.save({
            callback: function(record) {
//                settingsFormPanel.setLoading(false);
                // todo@bc growl
                settingsForm.loadRecord(record);
                configurationForm.loadRecord(record);
                me.getAboCommercePriceListing().setRecord(record);
            }
        });
    },

    /**
     * @EventListener
     * Event listener function of the abo commerce list.
     * Fired when the user clicks on a listing row. This function loads
     * the abo commerce detail data and load the data into the form panel.
     */
    onSelectAboCommerce: function(listRecord) {
        var me = this;
        var detailStore = Ext.create('Shopware.apps.Article.store.abo_commerce.Detail');
        var detailContainer = me.getAboCommerceDetailContainer();

        if (!(listRecord instanceof Ext.data.Model)) {
            return false;
        }

        if (listRecord.get('id') === null) {
            var newRecord = me.createDefaultAboCommerceRecord();
            me.loadRecordIntoView(newRecord);
            return true;
        }

        detailContainer.setLoading(true);
        detailStore.getProxy().extraParams.id = listRecord.get('id');

        detailStore.load({
            callback: function(records, operation) {
                detailContainer.setLoading(false);
                if (operation.wasSuccessful() && records.length > 0) {
                    me.loadRecordIntoView(records[0]);
                } else {
                    //todo@dr: Message
                }
            }
        });
        return true;
    },


});
//{/block}
