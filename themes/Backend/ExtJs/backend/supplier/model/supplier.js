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
 * @package    Supplier
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Supplier
 *
 * Backend - Management for Suppliers. Create | Modify | Delete and Logo Management.
 * Standard supplier model
 */
//{block name="backend/supplier/model/supplier"}
Ext.define('Shopware.apps.Supplier.model.Supplier', {
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
        //{block name="backend/supplier/model/supplier/fields"}{/block}
        { name : 'id', type : 'int' },
        { name : 'name', type : 'string' },
        { name : 'count', type : 'int' },
        { name : 'image', type : 'string' },
        { name : 'media-manager-selection', type: 'string' },
        { name : 'link', type : 'string'},
        { name : 'description', type : 'string' },
        { name : 'metaTitle', type : 'string' },
        { name : 'metaDescription', type : 'string' },
        { name : 'metaKeywords', type : 'string' },
        { name : 'articleCounter', type : 'int' }
    ],
    /**
     * If the name of the field is 'id' extjs assumes autmagical that
     * this field is an unique identifier.
     * @integer
     */
    idProperty : 'id',
    /**
     * Configure the data communication
     * @object
     */
    proxy : {
        type : 'ajax',
        api : {
            read : '{url controller="supplier" action="getSuppliers"}',
            create : '{url controller="supplier" action="createSupplier"}',
            update : '{url controller="supplier" action="updateSupplier"}',
            destroy : '{url controller="supplier" action="deleteSupplier"}'
        },
        reader : {
            type : 'json',
            root : 'data'
        }
    },
    /**
     * Rules to validate the input at the frontend side.
     * @array of objects
     */
    validations : [
        { field : 'name', type : 'length', min : 1 }
    ]
});
//{/block}
