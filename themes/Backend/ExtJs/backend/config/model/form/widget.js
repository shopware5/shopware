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
 * @package    Shopware_Config
 * @subpackage Config
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{namespace name=backend/static/widgets}
//{block name="backend/config/model/form/widget"}
Ext.define('Shopware.apps.Config.model.form.Widget', {

    snippets: {
        //{block name="backend/widget/widget"}{/block}
        swag_sales_widget: '{s name=swag_sales_widget}Yesterdays and todays sales{/s}',
        swag_upload_widget: '{s name=swag_upload}Drag and Drop Upload{/s}',
        swag_visitors_customers_widget: '{s name=swag_visitors_customers_widget}Customers online{/s}',
        swag_last_orders_widget: '{s name=swag_last_orders_widget}Recent orders{/s}',
        swag_notice_widget: '{s name=swag_notice_widget}Notepad{/s}',
        swag_merchant_widget: '{s name=swag_merchant_widget}Merchant clearing{/s}'
    },

    extend: 'Ext.data.Model',
    fields: [
        //{block name="backend/config/model/form/widget/fields"}{/block}
        { name: 'id', type: 'int' },
        {
            name:'label',
            type: 'string',
            convert: function(value, record) {
                var snippet = value;
                if (record && record.snippets) {
                    snippet = record.snippets[record.get('name').replace(/-/g, '_')];
                }
                if (Ext.isString(snippet) && snippet.length > 0) {
                    return snippet;
                } else {
                    return value;
                }
            }
        },
        { name: 'name', type: 'string' }
    ]
});
//{/block}
