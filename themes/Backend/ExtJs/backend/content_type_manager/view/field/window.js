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
 */

// {namespace name="backend/content_type_manager/main"}
// {block name="backend/content_type_manager/view/field/window"}
Ext.define('Shopware.apps.ContentTypeManager.view.field.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.content-type-manager-field-window',
    title : '{s name="field/title"}{/s}',
    height: 600,
    width: 1000,
    autoShow: true,
    layout: 'fit',

    initComponent: function () {
        this.form = Ext.create('Shopware.apps.ContentTypeManager.view.field.Form', {
            record: this.record,
            fieldSelectionStore: this.fieldSelectionStore,
            fieldListStore: this.fieldListStore
        });
        this.items = this.form;

        this.dockedItems = [this.createToolbar()];

        this.callParent(arguments);
    },

    /**
     * @returns { Ext.toolbar.Toolbar }
     */
    createToolbar: function () {
        var me = this;
        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: me.getEditFormButtons()
        });
        return me.toolbar;
    },

    /**
     * Creates the save and cancel button for the form panel.
     *
     * @return Array - Contains the cancel button and the save button
     */
    getEditFormButtons: function () {
        var me = this,
            buttons = [];

        buttons.push('->');
        var cancelButton = Ext.create('Ext.button.Button', {
            text: '{s name="cancel"}{/s}',
            scope: me,
            cls: 'secondary',
            handler: function () {
                this.destroy();
            }
        });
        buttons.push(cancelButton);

        var saveButton = Ext.create('Ext.button.Button', {
            text: '{s name="save"}{/s}',
            action: 'save-order',
            cls: 'primary',
            scope: me,
            handler: function () {
                var form = this.down('form').getForm();

                if (!form.isValid()) {
                    Ext.Msg.show({
                        title: '{s name="error/invalidFields/title"}{/s}',
                        msg: '{s name="error/invalidFields/message"}{/s}',
                        buttons: Ext.Msg.OK
                    });
                    return;
                }

                form.updateRecord(this.record);
                this.fireEvent('saveField', this, this.record, this.isNewField);
            }
        });

        buttons.push(saveButton);
        return buttons;
    }
});
// {/block}
