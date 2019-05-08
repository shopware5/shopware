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

// {block name="backend/content_type_manager/controller/main"}
Ext.define('Shopware.apps.ContentTypeManager.controller.Main', {
    extend: 'Enlight.app.Controller',

    /**
     * Init controller
     */
    init: function() {
        this.mainWindow = this.getView('list.Window').create();

        this.control({
            'content-type-manager-detail-fields': {
                createNewField: this.createNewField,
                editField: this.editField,
                deleteField: this.deleteField
            }
        });

        Shopware.app.Application.on('content-type-manager-start-save-record', this.onStartSaveRecord);
    },

    createNewField: function (grid, store) {
        var record = Ext.create('Shopware.apps.ContentTypeManager.model.Field');

        Ext.create('Shopware.apps.ContentTypeManager.view.field.Window', {
            record: record,
            isNewField: true,
            fieldSelectionStore: this.subApplication.getStore('Fields'),
            fieldListStore: store
        });
    },

    editField: function (record, store) {
        Ext.create('Shopware.apps.ContentTypeManager.view.field.Window', {
            record: record,
            isNewField: false,
            fieldSelectionStore: this.subApplication.getStore('Fields'),
            fieldListStore: store
        });
    },

    deleteField: function (store, record) {
        Ext.Msg.confirm(
            'Feld löschen',
            'Möchtest du das Feld wirklich löschen?',
            function(answer) {
                if (answer === 'yes') {
                    store.remove(record);
                }
            }
        );
    },

    onStartSaveRecord: function (controller, window, record, form) {
        if (record.getFieldsStore.count() === 0) {
            Shopware.Notification.createGrowlMessage('Content Types', '{s name="error/emptyFields"}{/s}');
            return false;
        }
    }
});
// {/block}
