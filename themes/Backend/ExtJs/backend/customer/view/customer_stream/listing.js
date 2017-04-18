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
 * @package    Customer
 * @subpackage CustomerStream
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/main}
// {block name="backend/customer/view/customer_stream/listing"}
Ext.define('Shopware.apps.Customer.view.customer_stream.Listing', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.customer-stream-listing',
    cls: 'stream-listing',

    configure: function() {
        var me = this;

        return {
            pagingbar: false,
            toolbar: false,
            displayProgressOnSingleDelete: false,
            columns: {
                name: {
                    renderer: me.nameRenderer
                }
            }
        };
    },

    createSelectionModel: function() {
        var me = this;

        me.selModel = Ext.create('Ext.selection.CheckboxModel', {
            mode: 'SINGLE',
            allowDeselect: true,
            listeners: {
                selectionchange: me.selectionChanged
            }
        });
        return me.selModel;
    },

    nameRenderer: function (value) {
        return '<span class="stream-name-column"><i>' + value + '</i></span>';
    }
});
// {/block}
