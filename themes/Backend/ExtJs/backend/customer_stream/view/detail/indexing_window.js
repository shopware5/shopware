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
Ext.define('Shopware.apps.CustomerStream.view.detail.IndexingWindow', {
    extend: 'Shopware.window.BatchRequests',
    title: '{s name="indexing_window"}{/s}',
    cls: 'customer-stream-indexing-window',

    prepareRequest: function(request, requests) {
        var me = this;

        if (request.name === 'search_index') {
            Ext.Ajax.request({
                url: '{url controller=CustomerStream action=getCustomerCount}',
                params: request.params,
                success: function(operation) {
                    var response = Ext.decode(operation.responseText);
                    request.params.total = response.total;
                    me.send(request, requests);
                }
            });
        } else {
            Ext.Ajax.request({
                url: '{url controller=CustomerStream action=loadStream}',
                params: request.params,
                success: function(operation) {
                    var response = Ext.decode(operation.responseText);
                    request.params.total = response.total;
                    me.send(request, requests);
                }
            });
        }
    }
});