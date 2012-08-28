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
 * @package    Voucher
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/voucher/view/code}

/**
 * Shopware Controller - Code list backend module
 *
 * Code controller of the voucher module. Handles all action around to
 * create and download voucher-codes.
 */
//{block name="backend/voucher/controller/code"}
Ext.define('Shopware.apps.Voucher.controller.Code', {
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend:'Ext.app.Controller',
    /**
     * all references to get the elements by the applicable selector
     */
    refs:[
        { ref:'voucherBaseConfiguration', selector:'window voucher-voucher-base_configuration' },
        { ref:'voucherCodeGrid', selector:'voucher-code-list' }
    ],
    /**
     * Contains all snippets for the controller
     */
    snippets: {
        confirmCreateNewVoucherCodesTitle: '{s name=message/confirmCreateNewVoucherCodesTitle}Create new voucher codes{/s}',
        confirmCreateNewVoucherCodes: '{s name=message/confirmCreateNewVoucherCodes}Creating new voucher codes will delete existing ones including all assigned information. Are you sure you want to create new voucher codes?{/s}'
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
            'voucher-code-list button[action=generateCodes]':{
                click:me.onGenerateCodes
            },

            'voucher-code-list button[action=downloadCodes]':{
                click:me.onDownloadCodes
            },
            'voucher-code-list textfield[action=searchVoucherCode]':{
                change:me.onSearchVoucherCode
            },
            'voucher-voucher-window tabpanel':{
                scope:me,
                tabchange:me.onChangeTab
            },
            'voucher-code-list':{
                openCustomerAccount:me.onOpenCustomerAccount
            }
        });
    },
    /**
     * Listener method to generate all needed voucher codes
     *
     * @return void
     */
    onGenerateCodes:function () {
        var me = this,
            countCodes = me.getVoucherCodeGrid().getStore().data.items.length;
        if(countCodes > 0) {
            Ext.MessageBox.confirm(
                me.snippets.confirmCreateNewVoucherCodesTitle,
                me.snippets.confirmCreateNewVoucherCodes, function (response) {
                    if (response !== 'yes') {
                        return false;
                    }
                    me.generateCodes();
                }
            );
        }else{
            me.generateCodes();
        }
    },
    /**
     * Listener Method for the download codes button
     * to get access to the download window
     *
     * @return void
     */
    onDownloadCodes:function () {
        var me = this;
        var form = me.getVoucherBaseConfiguration().getForm(),
            record = form.getRecord();
        window.open(' {url action="exportVoucherCode"}?voucherId='+record.data.id);
    },
    /**
     * helper method to send the request to the controller to generate new voucher codes
     *
     * @return void
     */
    generateCodes:function(){
        var me = this,
            form = me.getVoucherBaseConfiguration().getForm(),
            values = form.getValues(),
            id = parseInt(values.id),
            units = parseInt(values.numberOfUnits);

        //grid.setLoading(true);

        if (id != 0) {
            Ext.Ajax.request({
               url:'{url action="createVoucherCodes"}',
               params:{
                   voucherId: id,
                   numberOfUnits: units
               },
               success:function (record) {
                   if (record.length != 0) {
                       me.subApplication.getStore("Code").load();
                       me.getVoucherCodeGrid().down('button[action=downloadCodes]').enable();
                   }
               }
            });
        }
    },

    /**
     * Listener method for any fucntion that have been called when the tab is changed
     * Loads and reloads the code store
     *
     * @param tabPanel
     * @param newCard
     * @return void
     */
    onChangeTab:function (tabPanel, newCard) {
        var me = this;

        //only on the code panel
        if(newCard.alias[0] == "widget.voucher-code-list"){

            var store = me.subApplication.getStore('Code'),
                formRecord = me.getVoucherBaseConfiguration().getForm().getRecord();

            store.getProxy().extraParams = {
                voucherID:formRecord.data.id
            };

            store.load({
                callback: function(record, options, success) {

                    if (record.length != 0) {
                        me.getVoucherCodeGrid().down('button[action=downloadCodes]').enable();
                    } else {
                        me.getVoucherCodeGrid().down('button[action=downloadCodes]').disable();
                    }
                }
            })
        }
    },

    /**
     * Filters the grid with the passed search value to find the right voucher
     *
     * @param field
     * @param value
     * @return void
     */
    onSearchVoucherCode:function (field, value) {

        var me = this,
            searchString = Ext.String.trim(value),
            store = me.subApplication.getStore('Code');
        store.filter('filter',searchString);
        store.filters.clear();
    },

    /**
     * open the specific voucher modul page
     *
     * @param field
     * @param value
     * @return void
     */
    onOpenCustomerAccount:function (view, rowIndex) {
        var me = this;
        var record = me.subApplication.getStore('Code').getAt(rowIndex);
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Customer',
            action: 'detail',
            params: {
                customerId: record.get("customerId")
            }
        });
    }
});
//{/block}
