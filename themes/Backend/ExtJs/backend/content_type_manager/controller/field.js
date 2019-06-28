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

// {block name="backend/content_type_manager/controller/field"}
Ext.define('Shopware.apps.ContentTypeManager.controller.Field', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'detailFields', selector: 'content-type-manager-detail-fields' }
    ],

    /**
     * Init controller
     */
    init: function() {
        var me = this;

        me.control({
            'content-type-manager-field-window': {
                saveField: me.saveField
            }
        });

        me.subApplication.getStore('Fields');
    },

    saveField: function (window, record, isNewField) {
        if (!record.isValid()) {
            return;
        }

        if (window.form.handlerFieldset) {
            record.set('options', window.form.handlerFieldset.getValues());
        }

        if (isNewField) {
            record.set('name', this.slugify(record.get('label')));
        }

        if (isNewField) {
            this.getDetailFields().store.add(record);
        }

        window.destroy();
    },

    slugify: function (text) {
        return text.toString().toLowerCase()
            .replace(/\s+/g, '_')           // Replace spaces with _
            .replace(/\-/g, '_')            // Replace - with _
            .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
            .replace(/\_\_+/g, '_')         // Replace multiple _ with single _
            .replace(/^_+/, '')             // Trim _ from start of text
            .replace(/_+$/, '');
    }
});
// {/block}
