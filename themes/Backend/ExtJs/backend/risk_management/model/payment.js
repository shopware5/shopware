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
 * Shopware UI - Payment model
 *
 * This model contains a single payment and its ruleSets.
 */
//{block name="backend/risk_management/model/payment"}
Ext.define('Shopware.apps.RiskManagement.model.Payment', {
    /**
    * Extends the standard ExtJS 4
    * @string
    */
    extend: 'Shopware.apps.Base.model.Payment',
    /**
    * The fields used for this model
    * @array
    */
    fields : [
        //{block name="backend/risk_management/model/payment/fields"}{/block}
         { name : 'template',       type: 'string' },
         { name : 'class',       type: 'string' },
         { name : 'table',       type: 'string' },
         { name : 'hide',       type: 'int' },
         { name : 'additionalDescription',       type: 'string' },
         { name : 'debitPercent',       type: 'string' },
         { name : 'surcharge',       type: 'string' },
         { name : 'surchargeString',       type: 'string' },
         { name : 'esdActive',       type: 'boolean' },
         { name : 'embedIFrame',       type: 'string' },
         { name : 'hideProspect',       type: 'boolean' },
         { name : 'action',       type: 'string' },
         { name : 'pluginId',       type: 'int' },
         { name : 'iconCls',  type: 'string' },
         { name : 'surcharge', type: 'double' },
         { name: 'source', type: 'int' }
     ],

    associations: [
        { type:'hasMany', model:'Shopware.apps.RiskManagement.model.Rule',  name:'getRuleSets', associationKey:'ruleSets' }
    ]
});
//{/block}
