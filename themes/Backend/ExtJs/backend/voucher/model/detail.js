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
 * Shopware Model - Detail Form backend module.
 *
 * The Detail model of the voucher module represent a data row of the s_emarketing_vouchers or the
 * Shopware\Models\Voucher\Voucher doctrine model, with some additional data for the additional information panel.
 */
// {block name="backend/voucher/model/detail"}
Ext.define('Shopware.apps.Voucher.model.Detail', {
    /**
    * Extends the standard ExtJS 4
    * @string
    */
    extend: 'Ext.data.Model',
    /**
    * The fields used for this model
    * @array
    */
    fields: [
        // {block name="backend/voucher/model/detail/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'description', type: 'string' },
        { name: 'voucherCode', type: 'string' },
        { name: 'numberOfUnits', type: 'int', defaultValue: 1 },
        { name: 'value', type: 'double' },
        { name: 'minimumCharge', type: 'double' },
        { name: 'shippingFree' },
        { name: 'bindToSupplier', type: 'int', useNull: true },
        { name: 'validFrom', type: 'date', dateFormat: 'd.m.Y' },
        { name: 'validTo', type: 'date', dateFormat: 'd.m.Y' },
        { name: 'orderCode', type: 'string' },
        { name: 'modus', type: 'int' },
        { name: 'percental', type: 'int' },
        { name: 'numOrder', type: 'int', defaultValue: 1 },
        { name: 'customerGroup', type: 'int', useNull: true },
        { name: 'restrictArticles', type: 'string' },
        { name: 'strict', type: 'int' },
        { name: 'shopId', type: 'int', useNull: true },
        { name: 'customerStreamIds', type: 'string', useNull: true, defaultValue: null },
        { name: 'taxConfig', type: 'string', useNull: true }

    ],
    /**
    * If the name of the field is 'id' extjs assumes automagical that
    * this field is an unique identifier.
    */
    idProperty: 'id',
    /**
    * Configure the data communication
    * @object
    */
    proxy: {
        type: 'ajax',
        api: {
            read: '{url action=getVoucherDetail}',
            create: '{url action=saveVoucher}',
            update: '{url action=saveVoucher}',
            destroy: '{url action=deleteVoucher targetField=vouchers}'
        },
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'totalCount'
        }
    },
    /**
    * Rules to validate the input at the frontend side.
    */
    validations: [
        { field: 'description', type: 'length', min: 5 }
    ]
});
// {/block}
