/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @subpackage Model
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Global Stores and Models
 *
 * The payment model represents a data row of the s_core_states or the
 * Shopware\Models\State\State doctrine model.
 */
//{namespace name=backend/static/payment_status}
//{block name="backend/base/model/payment_status"}
Ext.define('Shopware.apps.Base.model.PaymentStatus', {

    snippets: {
        //{block name="backend/base/model/payment_status/snippets"}{/block}
        state9: '{s name=partially_invoiced}Partially invoiced{/s}',
        state10: '{s name=completely_invoiced}Completely invoiced{/s}',
        state11: '{s name=partially_paid}Partially paid{/s}',
        state12: '{s name=completely_paid}Completely paid{/s}',
        state13: '{s name=1st_reminder}1st reminder{/s}',
        state14: '{s name=2nd_reminder}2nd reminder{/s}',
        state15: '{s name=3rd_reminder}3rd reminder{/s}',
        state16: '{s name=encashment}Encashment{/s}',
        state17: '{s name=open}Open{/s}',
        state18: '{s name=reserved}Reserved{/s}',
        state19: '{s name=delayed}Delayed{/s}',
        state20: '{s name=re_crediting}Re-crediting{/s}',
        state21: '{s name=review_necessary}Review necessary{/s}',
        state30: '{s name=no_credit_approved}No credit approved{/s}',
        state31: '{s name=the_credit_has_been_preliminarily_accepted}The credit has been preliminarily accepted{/s}',
        state32: '{s name=the_credit_has_been_accepted}The credit has been accepted{/s}',
        state33: '{s name=the_payment_has_been_ordered_by_hanseatic_bank}The payment has been ordered by Hanseatic Bank.{/s}',
        state34: '{s name=a_time_extension_has_been_registered}A time extension has been registered{/s}',
        state35: '{s name=the_process_has_been_cancelled}The process has been cancelled.{/s}'
    },

    /**
     * Defines an alternate name for this class.
     */
    alternateClassName: 'Shopware.model.PaymentStatus',

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
		//{block name="backend/base/model/payment_status/fields"}{/block}
        { name:'id', type: 'int' },
        {
            name:'description',
            type: 'string',
            convert: function(value, record) {
                var snippet = value;
                if (record && record.snippets) {
                    snippet = record.snippets['state' + record.get('id')];
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

