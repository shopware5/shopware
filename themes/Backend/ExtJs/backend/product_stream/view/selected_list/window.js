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
 * @package    ProductStream
 * @subpackage Window
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/product_stream/main}

Ext.define('Shopware.apps.ProductStream.view.selected_list.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.product-stream-selected-list-window',
    title : '{s name=detail_window_title}Product stream details{/s}',
    height: '90%',
    width: '90%',
    layout: { type: 'vbox', align: 'stretch'},
    bodyPadding: 10,

    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.dockedItems = [me.createToolbar()];

        me.callParent(arguments);
        me.loadRecord(me.record);
    },

    loadRecord: function(record) {
        this.settingsPanel.loadRecord(record);
        if (record.get('id')) {
            this.activateProductGrid(record);
        }
    },

    activateProductGrid: function(record) {
        this.productGrid.streamId = record.get('id');
        this.productGrid.store.getProxy().extraParams.streamId = record.get('id');
        this.productGrid.store.load();
        this.productGrid.enable();
    },

    createToolbar: function() {
        var me = this;

        me.saveButton = Ext.create('Ext.button.Button', {
            text: '{s name=save}Save{/s}',
            cls: 'primary',
            handler: function () {
                me.fireEvent('save-selection-stream', me.record);
                me.activateProductGrid(me.record);
            }
        });

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            items: ['->', me.saveButton],
            dock: 'bottom'
        });
        return me.toolbar;
    },

    createItems: function() {
        return [
            this.createSettingPanel(),
            this.createProductGrid()
        ];
    },

    createProductGrid: function() {
        this.productGrid = Ext.create('Shopware.apps.ProductStream.view.selected_list.Product', {
            flex: 1,
            disabled: true
        });
        return this.productGrid;
    },

    createSettingPanel: function() {
        this.settingsPanel = Ext.create('Shopware.apps.ProductStream.view.common.Settings');
        return this.settingsPanel;
    }
});
