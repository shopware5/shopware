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
 * @package    RiskManagement
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Rule model
 *
 * This model contains a single rule, which is made of the different risks.
 */
//{block name="backend/risk_management/model/rule"}
Ext.define('Shopware.apps.RiskManagement.model.Rule', {
    /**
    * Extends the standard ExtJS 4
    * @string
    */
    extend: 'Ext.data.Model',
    idProperty: 'id',

    proxy: {
        type: 'ajax',
        /**
        * Configure the url mapping for the different
        * @object
        */
        api: {
            //create articles
            create: '{url controller="risk_management" action="createRule"}',
            //edit articles
            update: '{url controller="risk_management" action="editRule"}',
            //function to delete articles
            destroy: '{url controller="risk_management" action="deleteRule"}'
        },

        /**
        * Configure the data reader
        * @object
        */
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    },

    /**
    * The fields used for this model
    * @array
    */
    fields: [
        //{block name="backend/risk_management/model/rule/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'paymentId', type: 'string' },
        { name: 'rule1', type: 'string' },
        { name: 'value1', type: 'string' },
        { name: 'rule2', type: 'string' },
        { name: 'value2', type: 'string' }
    ]
});
//{/block}
