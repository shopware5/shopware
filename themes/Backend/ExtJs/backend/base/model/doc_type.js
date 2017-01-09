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
 * @package    Order
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Order list backend module.
 * The document type represents a row the s_core_documents table. It
 * contains all data about a supported document type.
 */
//{namespace name=backend/static/doc_type}
//{block name="backend/base/model/type"}
Ext.define('Shopware.apps.Base.model.DocType', {

    snippets: {
        //{block name="backend/base/model/type/snippets"}{/block}
        type1: '{s name=invoice}Invoice{/s}',
        type2: '{s name=delivery_note}Delivery note{/s}',
        type3: '{s name=credit_note}Credit note{/s}',
        type4: '{s name=reversal_invoice}Reversal invoice{/s}'
    },

    /**
     * Extends the standard Ext Model
     * @string
     */
    extend:'Ext.data.Model',
    /**
     * The fields used for this model
     * @array
     */
    fields:[
        //{block name="backend/order/model/type/fields"}{/block}
        { name: 'id', type: 'int' },
        {
            name:'name',
            type: 'string',
            convert: function(value, record) {
                var snippet = value;
                var internalId = 'type' + record.get('id');

                if (record && record.snippets) {
                    snippet = record.snippets[internalId];
                }
                if (Ext.isString(snippet) && snippet.length > 0) {
                    return snippet;
                } else {
                    return value;
                }
            }
        },
        { name: 'template', type: 'string' },
        { name: 'numbers', type: 'string' },
        { name: 'left', type: 'int' },
        { name: 'right', type: 'int' },
        { name: 'top', type: 'int' },
        { name: 'bottom', type: 'int' },
        { name: 'pageBreak', type: 'int' }
    ]

});
//{/block}
