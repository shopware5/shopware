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
 * @package    Voucher
 * @subpackage Controller
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
        { ref:'codePatternField', selector:'voucher-code-list textfield[name=patternField]' },
        { ref:'progressBar', selector:'voucher-code-progress-window progressbar' },
        { ref:'progressBarWindow', selector:'voucher-code-progress-window' },
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
                tabchange:me.onChangeTab
            },
            'voucher-code-list':{
                openCustomerAccount:me.onOpenCustomerAccount,
                edit: me.onEditCode
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
            countCodes = me.getVoucherCodeGrid().getStore().data.items.length,
            codePatternField = me.getCodePatternField(),
            codePatternFieldValue = codePatternField.getValue();

        if(countCodes > 0) {
            Ext.MessageBox.confirm(
                me.snippets.confirmCreateNewVoucherCodesTitle,
                me.snippets.confirmCreateNewVoucherCodes, function (response) {
                    if (response !== 'yes') {
                        return false;
                    }
                    me.generateCodes(codePatternFieldValue);
                }
            );
        }else{
            me.generateCodes(codePatternFieldValue);
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
     * @param codePattern | this is the based codePattern to generate the voucher code with
     * @return void
     */
    generateCodes:function(codePattern){
        var me = this,
            form = me.getVoucherBaseConfiguration().getForm(),
            values = form.getValues(),
            voucherId = parseInt(values.id),
            numberOfUnits = parseInt(values.numberOfUnits);

        me.getView('code.Progress').create();
        if (voucherId != 0) {

            me.getProgressBar().updateText('{s name=progress/text/delete_old_voucher_codes}Deleting old voucher codes{/s}');
            me.batchProcessing(voucherId, codePattern, numberOfUnits, numberOfUnits, true, 0);
        }
    },

    /**
     * Called when a voucher code was edited.
     * Syncs the store inside the grid with the server.
     */
    onEditCode: function (editor, e, eOpts) {
        this.getVoucherCodeGrid().getStore().sync();
    },

    /**
     * helper method which is executes several times to send the ajax request to generate a bunch of voucher codes
     *
     * @param voucherId
     * @param codePattern
     * @param numberOfCodesToGenerate
     * @param numberOfAllCodes
     * @param deletePreviousVoucherCodes
     * @param overAllTimeToGenerate
     */
    batchProcessing:function(voucherId, codePattern, numberOfCodesToGenerate, numberOfAllCodes, deletePreviousVoucherCodes, overAllTimeToGenerate){
        var me = this,
            progressBar = me.getProgressBar(),
            startTime = new Date().getTime(),
            timeLeft = 0,
            timeString = "";

        numberOfCodesToGenerate = numberOfCodesToGenerate > 50000 ? 50000 : numberOfCodesToGenerate;
        Ext.Ajax.request({
            url:'{url action="createVoucherCodes"}',
            params:{
                voucherId: voucherId,
                numberOfUnits: numberOfCodesToGenerate,
                deletePreviousVoucherCodes: deletePreviousVoucherCodes,
                codePattern: codePattern
            },
            success:function (record) {
                var status = Ext.decode(record.responseText);
                if (status.success) {
                    var cycleTime =  (new Date().getTime() - startTime) / 1000;
                    overAllTimeToGenerate = overAllTimeToGenerate + cycleTime;
                    numberOfCodesToGenerate = numberOfAllCodes - status.generatedVoucherCodes;
                    timeLeft = (numberOfAllCodes - status.generatedVoucherCodes) * overAllTimeToGenerate / status.generatedVoucherCodes;

                    if(!deletePreviousVoucherCodes) {
                        var hours   = Math.floor(timeLeft / 3600);
                        var minutes = Math.floor((timeLeft - (hours * 3600)) / 60);
                        var seconds = timeLeft - (hours * 3600) - (minutes * 60);
                        timeString =  + Math.round(minutes) + " {s name=progress/text/time_minutes_and}minute(s) and{/s} "+ Math.round(seconds) +" {s name=progress/text/time_seconds_remaining}second(s) remaining{/s}";
                    }
                    progressBar.updateProgress(status.generatedVoucherCodes / numberOfAllCodes, status.generatedVoucherCodes + " {s name=progress/text/out_of}out of{/s} " + numberOfAllCodes + " {s name=progress/text/voucher_code_created}voucher codes created{/s} " + timeString, true);

                    if(numberOfCodesToGenerate > 0) {
                        me.batchProcessing(voucherId, codePattern, numberOfCodesToGenerate, numberOfAllCodes, false, overAllTimeToGenerate);
                    }
                    else {
                        me.subApplication.getStore("Code").load({
                            callback: function(records, operation) {
                                me.getProgressBarWindow().hide();
                                me.getVoucherCodeGrid().down('button[action=downloadCodes]').enable();
                            }
                        });
                    }
                }
                else {
                    me.getProgressBarWindow().hide();
                    Shopware.Notification.createGrowlMessage('{s name=progress/text/voucher_validation_failure_title}Voucher codes could not be generated.{/s}', '{s name=progress/text/voucher_validation_failure}The Voucher codes could not be generated. Maybe the voucher code pattern is not complex enough{/s}');
                }
            }
        });
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
