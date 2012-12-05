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
//{block name="backend/ticket/controller/submission"}
Ext.define('Shopware.apps.Ticket.controller.Submission', {

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
        { ref: 'submissionPanel', selector: 'ticket-settings-submission' }
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
            'ticket-settings-submission': {
                saveSubmission: me.onSaveSubmission,
                addSubmission: me.onAddSubmission,
                deleteSubmission: me.onDeleteSubmission
            },
            'ticket-settings-submission grid': {
                selectionchange: me.onSubmissionSelectionChange
            }
        });
    },

    /**
     * Event listener method which will be triggered when the user
     * changes the active submission in the submission navigation.
     *
     * The method simply locks / unlocks the del
     * @param selModel
     * @param selection
     */
    onSubmissionSelectionChange: function(selModel, selection) {
        var me = this,
            submissionPnl = me.getSubmissionPanel();

        if(!selection.length) {
            return false;
        }

        var record = selection[0];
        submissionPnl.deleteButton.setDisabled(record.get('systemDependent'));

        if(submissionPnl.submissionForm.isDisabled()) {
            submissionPnl.submissionForm.setDisabled(false);
        }
        submissionPnl.submissionForm.loadRecord(record);
    },

    /**
     * Event listener method which will be triggered when the user
     * clicks on the "save submission" button.
     *
     * Saves the record which is associated with the submission form.
     *
     * @public
     * @event click
     * @return boolean
     */
    onSaveSubmission: function() {
        var me = this,
            submissionPnl = me.getSubmissionPanel(),
            formPnl = submissionPnl.submissionForm,
            form = formPnl.getForm(),
            record = formPnl.getRecord(),
            values = form.getValues();

        if(!form.isValid()) {
            Shopware.Notification.createGrowlMessage('{s name=window_title}Ticket system{/s}', '{s name=error/submission_fill_all_fields}Please fill out all required fields (marked red) to save the selected submission.{/s}');
            return false;
        }
        form.updateRecord(record);

        record.set('isHTML', values.isHTML == 'on');
        record.save({
            callback: function (self, operation) {
                if (operation.success) {
                    Shopware.Notification.createGrowlMessage('{s name=window_title}Ticket system{/s}', '{s name=success/save_submission}The submission was successfully saved.{/s}');
                } else {
                    Shopware.Notification.createGrowlMessage('{s name=window_title}Ticket system{/s}', '{s name=error/save_submission}The submission could not be saved successfully.{/s}');
                }
                me.subApplication.submissionStore.load();
            }
        });
    },

    /**
     * Event listener method which will be triggered when the user
     * clicks on the "add submission" button in the navigation panel.
     *
     * The method loads a record in the form panel and clears the
     * invalid fields.
     *
     * @public
     * @event click
     * @return void
     */
    onAddSubmission: function() {
        var me = this,
            submissionPnl = me.getSubmissionPanel(),
            formPnl = submissionPnl.submissionForm,
            form = formPnl.getForm(),
            record = Ext.create('Shopware.apps.Ticket.model.Submission');

        if(formPnl.isDisabled()) {
            formPnl.setDisabled(false);
        }
        formPnl.loadRecord(record);
        form.clearInvalid();

        // Set focus in the first form element
        var textfield = formPnl.query('textfield');
        textfield = textfield[0];
        textfield.focus();
    },

    /**
     * Event listener method which will be triggered when the user
     * clicks on the "delete submission" button which is located
     * under the navigation grid.
     *
     * The method deletes the selected record and resets the form panel
     * if an record was loaded.
     *
     * @public
     * @event click
     * @param [object] btn - Ext.button.Button
     * @param [object] view - Shopware.apps.Ticket.view.settings.Submission
     * @return [boolean]
     */
    onDeleteSubmission: function(btn, view) {
        var me = this,
            store = me.subApplication.submissionStore,
            selModel = view.navigationGrid.selModel,
            form = view.submissionForm.getForm(),
            selection = selModel.getSelection();

        if(!selection.length) {
            return false;
        }
        var record = selection[0],
            formRecord = view.submissionForm.getRecord();

        if(formRecord && record.get('id') === formRecord.get('id')) {
            form.reset();
            form.clearInvalid();
            view.submissionForm.setDisabled(true);
        }

        record.destroy();
        store.load();
    }
});
//{/block}
