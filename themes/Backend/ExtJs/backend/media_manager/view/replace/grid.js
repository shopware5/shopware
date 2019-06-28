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
 */

//{namespace name=backend/media_manager/view/replace}
//{block name="backend/media_manager/view/replace/window"}
Ext.define('Shopware.apps.MediaManager.view.replace.Grid', {
    extend: 'Ext.panel.Panel',
    layout: 'vbox',
    border: 0,
    autoScroll: true,
    maxHeight: 305,
    margin: '0 0 0 10',
    bodyStyle: {
        background: '#F0F2F4'
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.selectedRecords = [];
        me.store = me.createStore();
        me.items = me.createItems();

        me.callParent(arguments);
    },

    /**
     * Creates a Ext.data.Store from the injected selected medias
     *
     * @return { Ext.data.Store }
     */
    createStore: function() {
        var me = this;

        return Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.MediaManager.model.Media',
            data: me.selectedMedias
        });
    },

    /**
     * Creates a container with all elements
     *
     * @return { Ext.container.Container }
     */
    createItems: function() {
        var me = this,
            items = [],
            lastIndex = me.store.getCount() - 1;

        me.store.each(function(record, index) {
            var rowElement = me.createRow(record);
            if (index == lastIndex) {
                rowElement.border = 0;
            }

            items.push(rowElement);
            me.registerEvent(rowElement);
            me.selectedRecords.push(rowElement);
        });

        me.rows = items;

        return Ext.create('Ext.container.Container', {
            items: items
        });
    },

    /**
     * Registers the required events on the given element
     *
     * @param { Ext.Component } element
     */
    registerEvent: function(element) {
        var me = this;

        element.on('upload-uploadReady', me.onUploadReady, me);
        element.on('upload-error', me.onError, me);
    },

    /**
     * Creates a single row with all required elements
     *
     * @param { Ext.data.Model } record
     * @return { Shopware.apps.MediaManager.view.replace.Row }
     */
    createRow: function(record) {
        var me = this;

        return Ext.create('Shopware.apps.MediaManager.view.replace.Row', {
            record: record,
            grid: me
        });
    },

    /**
     * return a array with all values
     *
     * @return { Array }
     */
    getValue: function() {
        var me = this,
            value = [];

        Ext.Array.each(me.selectedRecords, function(record) {
            var val = record.getValue();

            if (val) {
                value.push(val);
            }
        });

        return value;
    },

    /**
     * On upload ready fire the upload ready event
     *
     * @param { Shopware.apps.MediaManager.view.replace.Row } row
     * @param  { object } response
     */
    onUploadReady: function(row, response) {
        var me = this;

        me.fireEvent('uploadReady', me, row, response);
    },

    /**
     * Event handler was called if a error occurred
     */
    onError: function() {
        var me = this;

        me.fireEvent('upload-error', me);
    }
});
//{/block}
