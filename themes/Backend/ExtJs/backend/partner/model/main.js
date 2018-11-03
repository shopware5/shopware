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
 * @package    Partner
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model -  partner list backend module.
 *
 * The partner main model of the partner module represent a data row of the s_emarketing_partner or the
 * Shopware\Models\Partner\Partner doctrine model, with some additional data for the additional information panel.
 */
//{block name="backend/partner/model/main"}
Ext.define('Shopware.apps.Partner.model.Main', {
    /**
    * Extends the standard ExtJS 4
    * @string
    */
    extend : 'Ext.data.Model',
    /**
    * The fields used for this model
    * @array
    */
    fields : [
        //{block name="backend/partner/model/main/fields"}{/block}
        { name : 'id', type : 'int' },
        { name : 'company', type : 'string' },
        { name : 'date', type : 'date' },
        { name : 'active', type : 'int' },
        { name : 'idCode', type : 'string' },
        { name : 'monthlyAmount', type : 'float' },
        { name : 'yearlyAmount', type : 'float' }
    ],
    /**
    * If the name of the field is 'id' extjs assumes autmagical that
    * this field is an unique identifier.
    */
    idProperty : 'id',
    /**
    * Configure the data communication
    * @object
    */
    proxy : {
        type : 'ajax',
        api:{
            read:   '{url action=getList}',
            destroy:'{url action=deletePartner}'
        },
        reader : {
            type : 'json',
            root : 'data',
            totalProperty: 'totalCount'
        }
    }
});
//{/block}
