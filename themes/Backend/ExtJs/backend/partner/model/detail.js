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
 * Shopware Model -  partner detail
 * The partner detail model of the partner module represent a data row of the s_emarketing_partner or the
 * Shopware\Models\Partner\Partner doctrine model, with some additional data for the additional information panel.
 */
//{block name="backend/partner/model/detail"}
Ext.define('Shopware.apps.Partner.model.Detail', {
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
        //{block name="backend/partner/model/detail/fields"}{/block}
        { name : 'id', type : 'int' },
        { name : 'idCode', type : 'string' },
        { name : 'date', type : 'date' },
        { name : 'customerId', type : 'int' },
        { name : 'company', type : 'string' },
        { name : 'contact', type : 'string' },
        { name : 'street', type : 'string' },
        { name : 'zipCode', type : 'string' },
        { name : 'city', type : 'string' },
        { name : 'phone', type : 'string' },
        { name : 'fax', type : 'string' },
        { name : 'countryName', type : 'string' },
        { name : 'email', type : 'string' },
        { name : 'web', type : 'string' },
        { name : 'profile', type : 'string' },
        { name : 'fix', type : 'float' },
        { name : 'percent', type : 'float' },
        { name : 'cookieLifeTime', type : 'int' },
        { name : 'active', type : 'int' }
    ],
    /**
    * If the name of the field is 'id' ExtJs assumes automatically that
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
            read:   '{url action=getDetail}',
            create: '{url action=savePartner}',
            update: '{url action=savePartner}',
            destroy:'{url action=deletePartner}'
        },
        reader : {
            type : 'json',
            root : 'data'
        }
    }
});
//{/block}
