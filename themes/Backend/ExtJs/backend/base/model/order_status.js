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
 * @package    Base
 * @subpackage Component
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Global Stores and Models
 *
 * The payment model represents a data row of the s_core_states or the
 * Shopware\Models\State\State doctrine model.
 */
//{namespace name=backend/static/order_status}
//{block name="backend/base/model/order_status"}
Ext.define('Shopware.apps.Base.model.OrderStatus', {

    snippets: {
        //{block name="backend/base/model/order_status/snippets"}{/block}
        cancelled: '{s name=cancelled}Cancelled{/s}',
        open: '{s name=open}Open{/s}',
        in_process: '{s name=in_process}In process{/s}',
        completed: '{s name=completed}Completed{/s}',
        partially_completed: '{s name=partially_completed}Partially completed{/s}',
        cancelled_rejected: '{s name=cancelled_rejected}Cancelled/rejected{/s}',
        ready_for_delivery: '{s name=ready_for_delivery}Ready for delivery{/s}',
        partially_delivered: '{s name=partially_delivered}Partially delivered{/s}',
        completely_delivered: '{s name=completely_delivered}Completely delivered{/s}',
        clarification_required: '{s name=clarification_required}Clarification required{/s}'
    },

    /**
     * Defines an alternate name for this class.
     */
    alternateClassName: 'Shopware.model.OrderStatus',

    /**
     * Extends the standard Ext Model
     * @string
     */
    extend:'Shopware.data.Model',

    /**
     * unique id
     * @int
     */
    idProperty:'id',

    /**
     * The fields used for this model
     * @array
     */
    fields:[
        //{block name="backend/base/model/order_status/fields"}{/block}
        { name:'id', type: 'int' },
        { name:'name', type: 'string' },
        {
            name:'description',
            type: 'string',
            convert: function(value, record) {
                var snippet = value;
                if (record && record.snippets) {
                    snippet = record.snippets[record.get('name')];
                }
                if (Ext.isString(snippet) && snippet.length > 0) {
                    return snippet;
                } else {
                    return value;
                }
            }
        }
    ]
});
//{/block}

