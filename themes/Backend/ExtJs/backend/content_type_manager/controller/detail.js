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

// {block name="backend/content_type_manager/controller/detail"}
Ext.define('Shopware.apps.ContentTypeManager.controller.Detail', {
    extend: 'Shopware.detail.Controller',

    refs: [
        { ref: 'detailContainer', selector: 'content-type-manager-detail-container' },
        { ref: 'grid', selector: 'content-type-manager-detail-fields' }
    ],

    init: function() {
        var me = this;

        Shopware.app.Application.on('content-type-manager-save-successfully', function (controller, result, window, record, form, operation) {
            var newRecord = record.getProxy().reader.read(operation.response).records[0];
            me.getDetailContainer().seoUrlGrid.reconfigure(newRecord.getUrls());
        });

        this.control({
            'content-type-manager-detail-window': {
                'content-type-manager-after-tab-changed': this.onTabChange
            }
        });

        this.callParent(arguments);
    },

    onSave: function () {
        this.getGrid().store.clearFilter();
        this.callParent(arguments);
    },

    configure: function () {
        return {
            detailWindow: 'Shopware.apps.ContentTypeManager.view.detail.Window',
            eventAlias: 'content-type-manager'
        }
    },

    onTabChange: function () {
        this.getGrid().store.clearFilter();
    },
});
// {/block}
