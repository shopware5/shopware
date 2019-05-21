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
//{block name="backend/product_stream/view/selected_list/product"}
Ext.define('Shopware.apps.ProductStream.view.selected_list.Product', {
    extend: 'Shopware.apps.ProductStream.view.SearchGrid',
    alias: 'widget.product-stream-selected-list-grid',

    enable: function() {
        this.grid.enable();
        this.callParent(arguments);
    },

    initComponent: function() {
        var me = this;
        me.store = Ext.create('Shopware.apps.ProductStream.store.SelectedProducts');
        me.searchStore = me.createSearchStore();
        me.callParent(arguments);
    },

    createGrid: function() {
        var grid = this.callParent(arguments);
        grid.disabled = true;
        return grid;
    },

    createSearchStore: function() {
        return Ext.create('Shopware.store.Search', {
            model: 'Shopware.apps.Base.model.Article',
            pageSize: 20,
            configure: function() {
                return { entity: "Shopware\\Models\\Article\\Article" }
            }
        });
    },

    addRecord: function(record) {
        this.callParent(arguments);
        this.sendAjaxRequest(
            '{url controller=ProductStream action=addSelectedProduct}',
            { streamId: this.streamId, articleId: record.get('id') }
        );
    },

    removeRecord: function(record) {
        this.callParent(arguments);
        this.sendAjaxRequest(
            '{url controller=ProductStream action=removeSelectedProduct}',
            { streamId: this.streamId, articleId: record.get('id') }
        );
    },

    sendAjaxRequest: function(url, params, callback) {
        Ext.Ajax.request({
            url: url,
            params: params,
            method: 'POST',
            success: function(operation, opts) {
                var response = Ext.decode(operation.responseText);

                if (Ext.isFunction(callback)) {
                    callback(response);
                }
            }
        });
    }
});
//{/block}
