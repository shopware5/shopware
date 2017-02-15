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
 * @subpackage Model
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
        partially_invoiced: '{s name=partially_invoiced}Partially invoiced{/s}',
        completely_invoiced: '{s name=completely_invoiced}Completely invoiced{/s}',
        partially_paid: '{s name=partially_paid}Partially paid{/s}',
        completely_paid: '{s name=completely_paid}Completely paid{/s}',
        '1st_reminder': '{s name=1st_reminder}1st reminder{/s}',
        '2nd_reminder': '{s name=2nd_reminder}2nd reminder{/s}',
        '3rd_reminder': '{s name=3rd_reminder}3rd reminder{/s}',
        encashment: '{s name=encashment}Encashment{/s}',
        open: '{s name=open}Open{/s}',
        reserved: '{s name=reserved}Reserved{/s}',
        delayed: '{s name=delayed}Delayed{/s}',
        re_crediting: '{s name=re_crediting}Re-crediting{/s}',
        review_necessary: '{s name=review_necessary}Review necessary{/s}',
        no_credit_approved: '{s name=no_credit_approved}No credit approved{/s}',
        the_credit_has_been_preliminarily_accepted: '{s name=the_credit_has_been_preliminarily_accepted}The credit has been preliminarily accepted{/s}',
        the_credit_has_been_accepted: '{s name=the_credit_has_been_accepted}The credit has been accepted{/s}',
        the_payment_has_been_ordered_by_hanseatic_bank: '{s name=the_payment_has_been_ordered_by_hanseatic_bank}The payment has been ordered by Hanseatic Bank.{/s}',
        a_time_extension_has_been_registered: '{s name=a_time_extension_has_been_registered}A time extension has been registered{/s}',
        the_process_has_been_cancelled: '{s name=the_process_has_been_cancelled}The process has been cancelled.{/s}'
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

