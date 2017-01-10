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
 * @package    Partner
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/partner/view/partner}

/**
 * Shopware Controller - Partner backend module
 *
 * The partner controller managed and controls all partner specific events and methods
 */
//{block name="backend/partner/controller/partner"}
Ext.define('Shopware.apps.Partner.controller.Partner', {
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend:'Ext.app.Controller',

    /**
     * all references to get the elements by the applicable selector
     */
    refs:[
        { ref:'detailWindow', selector:'partner-partner-window' },
        { ref:'attributeForm', selector:'partner-partner-window shopware-attribute-form' }
    ],

    /**
     * Property which holds the last filter of the store
     * @default null
     * @object
     */
    lastFilter: null,

    /**
     * Contains all snippets for the controller
     */
    snippets: {
        //save changes
        onSaveChangesSuccess: '{s name=message/on_save_changes_success}Changes saved successfully.{/s}',
        onSaveChangesError: '{s name=message/on_save_changes_error}There was an error while saving your changes.{/s}',
        confirmDeleteSingleItem: '{s name=message/confirm_delete_single_item}Delete this item{/s}',
        confirmDeleteSingle: '{s name=message/confirm_delete_single}Are you sure you want to delete this item? ([0]){/s}',
        deleteSingleItemSuccess : '{s name=tree/delete_success}Item has been deleted{/s}',
        deleteSingleItemFailure : '{s name=tree/delete_failure}Item could not be deleted{/s}',
        mappedToCustomer: '{s name=message/mappedToCustomer}Linked to:{/s}',
        growlMessage: '{s name=window/main_title}Affiliate program{/s}'
    },

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the sub-application
     *
     * @return void
     */
    init:function () {
        var me = this;

        me.control({
            'partner-partner-list': {
                deleteColumn: me.onDeleteSingleItem,
                editColumn: me.onEditItem,
                statistic: me.onShowStatistic
            },
            'partner-partner-list button[action=add]':{
                click:me.onAdd
            },
            'partner-partner-window button[action=save]':{
                click:me.onSave
            },
            'partner-partner-detail':{
                mapCustomerAccount:me.mapCustomerAccount
            }
        });
    },

    /**
     * Opens the Ext.window.window which displays
     * the Ext.form.Panel to create a new Detailpage
     *
     * @return void
     */
    onAdd:function () {
        var me = this,
            model = Ext.create('Shopware.apps.Partner.model.Detail');
        //reset the detail Record
        me.detailRecord = null;
        me.getView('partner.Window').create({
            record: model
        });
        me.getDetailWindow().formPanel.loadRecord(model);

    },

    /**
     * Opens the Ext.window.window which displays
     * the Ext.form.Panel to modify an existing partner
     *
     * @param [object]  view - The view. Is needed to get the right f
     * @param [object]  item - The row which is affected
     * @param [integer] rowIndex - The row number
     * @return void
     */
    onEditItem:function (view, rowIndex) {
        var me = this,
            store = me.subApplication.detailStore,
            record = me.subApplication.listStore.getAt(rowIndex);

        store.load({
            filters : [{
                property: 'id',
                value: record.get("id")
            }],
            callback: function(records, operation) {
                if (operation.success !== true || !records.length) {
                    return;
                }
                me.detailRecord = records[0];
                me.getView('partner.Window').create({
                    record: me.detailRecord
                });
                me.getDetailWindow().formPanel.loadRecord(me.detailRecord);
            }
        });
    },

    /**
     * Opens the Ext.window.window which displays
     * the partner statistic
     *
     * @return void
     */
    onShowStatistic:function (view, rowIndex) {
        var me = this,
        record = me.subApplication.listStore.getAt(rowIndex);
        //reset the detail Record
        me.subApplication.statisticListStore.getProxy().extraParams =
                me.subApplication.statisticChartStore.getProxy().extraParams = { partnerId: record.get('id') };

        me.getView('statistic.Window').create({
            statisticListStore: me.subApplication.statisticListStore,
            statisticChartStore: me.subApplication.statisticChartStore
        });

    },

    /**
     * Event listener which deletes a single item based on the passed
     * grid (e.g. the grid store) and the row index
     *
     * @param [object] grid - The grid on which the event has been fired
     * @param [integer] rowIndex - Position of the event
     * @return void
     */
    onDeleteSingleItem:function (grid, rowIndex) {
        var me = this,
            store = me.subApplication.listStore,
            record = store.getAt(rowIndex);
        // we do not just delete - we are polite and ask the user if he is sure.
        Ext.MessageBox.confirm(
            me.snippets.confirmDeleteSingleItem,
            Ext.String.format(me.snippets.confirmDeleteSingle, record.get('company')), function (response) {
            if (response !== 'yes') {
                return false;
            }
            record.destroy({
                callback:function (data, operation) {
                    var records = operation.getRecords(),
                        record = records[0],
                        rawData = record.getProxy().getReader().rawData;

                    if ( operation.success === true ) {
                        Shopware.Notification.createGrowlMessage('',me.snippets.deleteSingleItemSuccess, me.snippets.growlMessage);
                    } else {
                        Shopware.Notification.createGrowlMessage('',me.snippets.deleteSingleItemFailure + ' ' + rawData.message, me.snippets.growlMessage);
                    }
                }
            });
            store.load();
        });
    },

    /**
     * Event listener method which will be fired when the user
     * clicks the "save"-button in the edit-window.
     *
     * @event click
     * @param [object] btn - pressed Ext.button.Button
     * @return void
     */
    onSave: function (btn) {
        var me = this,
            formPanel = me.getDetailWindow().formPanel,
            form = formPanel.getForm(),
            listStore = me.subApplication.listStore,
            attributeForm = me.getAttributeForm(),
            record = form.getRecord();

        //check if all required fields are valid
        if (!form.isValid()) {
            return;
        }

        form.updateRecord(record);

        record.save({
            callback: function (self,operation) {
                if (operation.success) {
                    attributeForm.saveAttribute(record.get('id'));
                    listStore.load();
                    Shopware.Notification.createGrowlMessage('',me.snippets.onSaveChangesSuccess, me.snippets.growlMessage);
                    me.getDetailWindow().destroy();
                } else {
                    Shopware.Notification.createGrowlMessage('',me.snippets.onSaveChangesError, me.snippets.growlMessage);
                }
            }
        });
    },

    /**
     * Event listener method which will be fired when the user
     * changed the customerAccount field to return the right customerId into the field to save it
     *
     * @event change
     * @param [Ext.form.field.Field] this
     * @param [object] newValue
     * @param [object] oldValue
     * @param [object] eOpts
     * @return void
     */
    mapCustomerAccount: function (field, newValue, oldValue, eOpts) {
        var me = this;
            Ext.Ajax.request({
                url:'{url action="mapCustomerAccount"}',
                params:{
                    mapCustomerAccountValue: newValue
                },
                success:function (response) {
                    if (response.length != 0) {
                        var mappingData = response.responseText.split("|"),
                        mappingText = mappingData[0],
                        userID = mappingData[1];

                        if(userID && userID != "undefined") {
                            var template = new Ext.Template(
                                "{literal}<h1>"+me.snippets.mappedToCustomer+"</h1> {0} {/literal}"
                            );
                            field.supportTextEl.update(template.apply([mappingText]));
                            field.setRawValue(userID);
                        }
                        else {
                            field.supportTextEl.update("{s name=detail_general/supportText/noCustomerMapped}No customer account has been linked{/s}");
                        }

                    }
                }
            });
    },

    /**
     * just reloads the  grid to keep it up to date after closing the detail window
     *
     * @event click
     * @param [object] btn - pressed Ext.button.Button
     * @return void
     */
    onBeforeCloseWindow:function () {
        this.subApplication.listStore.load();
    }
});
//{/block}
