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

//{namespace name="backend/customer_stream/translation"}

Ext.define('Shopware.apps.CustomerStream.controller.Main', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'listing', selector: 'customer-stream-listing' }
    ],

    init: function() {
        var me = this;

        me.control({
            'customer-stream-detail-window': {
                'stream-saved': me.streamSaved
            },
            'customer-stream-listing': {
                'customerstream-edit-item': me.editStream,
                'customerstream-add-item': me.addStream
            }
        });
        me.mainWindow = me.getView('list.Window').create({ }).show();
    },

    streamSaved: function() {
        var me = this;
        me.getListing().getStore().load();
    },

    editStream: function(grid, record) {
        var me = this;
        var window = me.getView('detail.Window').create().show();
        window.loadRecord(record);
    },

    addStream: function() {
        var me = this;
        var window = me.getView('detail.Window').create().show();

        window.loadRecord(
            Ext.create('Shopware.apps.CustomerStream.model.CustomerStream', {
                active: true
            })
        );
    }
});