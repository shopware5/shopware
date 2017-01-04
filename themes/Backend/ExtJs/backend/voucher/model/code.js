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
 * @package    Voucher
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model -  Code list backend module.
 *
 * The code model of the voucher module represent a data row of the s_emarketing_voucher_codes or the
 * Shopware\Models\Voucher\Code doctrine model, with some additional data for the additional information panel.
 */
//{block name="backend/voucher/model/code"}
Ext.define('Shopware.apps.Voucher.model.Code', {
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
    //{block name="backend/voucher/model/code/fields"}{/block}
        { name : 'id', type : 'int' },
        { name : 'voucherID', type : 'int' },
        { name : 'number', type : 'string' },
        { name : 'code', type : 'string' },
        { name : 'firstName', type : 'string' },
        { name : 'lastName', type : 'string' },
        { name : 'customerId', type : 'int' },
        { name : 'cashed', type : 'boolean' }
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
            read:   '{url controller="voucher" action="getVoucherCodes"}',
            create: '{url controller="voucher" action="createVoucherCodes"}',
            update: '{url controller="voucher" action="updateVoucherCodes"}'
        },
        reader : {
            type : 'json',
            root : 'data',
            totalProperty:'totalCount'
        }
    }
});
//{/block}
