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
 * Shopware Model -  Voucher  list backend module.
 *
 * The voucher list model of the voucher module represent a data row of the s_emarketing_vouchers or the
 * Shopware\Models\Voucher\Voucher doctrine model, with some additional data for the additional information panel.
 */
//{block name="backend/voucher/model/main"}
Ext.define('Shopware.apps.Voucher.model.Main', {
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
        //{block name="backend/voucher/model/main/fields"}{/block}
        { name : 'id', type : 'int' },
        { name : 'description', type : 'string' },
        { name : 'voucherCode', type : 'string' },
        { name : 'modus', type : 'string' },
        { name : 'numberOfUnits', type : 'int' },
        { name : 'value', type : 'double' },
        { name : 'validFrom', type : 'date'},
        { name : 'validTo', type : 'date'},
        { name : 'percental', type : 'int' },
        { name : 'checkedIn', type : 'int' }
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
            read:   '{url action=getVoucher}',
            create: '{url action=createVoucher}',
            update: '{url action=updateVoucher}',
            destroy:'{url action=deleteVoucher targetField=vouchers}'
        },
        reader : {
            type : 'json',
            root : 'data',
            totalProperty: 'totalCount'
        }
    },
    /**
    * Rules to validate the input at the frontend side.
    */
    validations : [
        { field : 'description', type : 'length', min : 5 }
    ]
});
//{/block}
