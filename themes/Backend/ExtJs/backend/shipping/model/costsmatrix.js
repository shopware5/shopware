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
 * @package    Shipping
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Shipping
 *
 * todo@all: Documentation
 */
//{block name="backend/shipping/model/costmatrix"}
Ext.define('Shopware.apps.Shipping.model.Costsmatrix', {
    /**
     * Extends the standard ExtJS 4
     * @string
     */
    extend:'Ext.data.Model',
    /**
     * The fields used for this model
     * @array
     */
    fields:[
        //{block name="backend/shipping/model/costmatrix/fields"}{/block}
        { name:'id', type:'integer' },
        { name:'from', type:'float' },
        { name:'value', type:'float' },
        { name:'factor', type:'float' },
        { name:'dispatchId', type:'integer' },
        { name:'to', type:'float' }
    ],
    /**
     * Configure the data communication
     * @object
     */
    proxy:{
        type:'ajax',
        api:{
            read:'{url controller="shipping" action="getCostsMatrix"}',
            create:'{url controller="shipping" action="createCostsMatrix" targetField=costMatrix}',
            update:'{url controller="shipping" action="updateCostsMatrix" targetField=costMatrix}',
            destroy:'{url controller="shipping" action="deleteCostsMatrixEntry" targetField=costMatrix}'
        },
        reader:{
            type:'json',
            root:'data'
        }
    }
});
//{/block}
