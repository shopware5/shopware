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
 * @package    Shopware_Config
 * @subpackage Config
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{block name="backend/config/model/form/shop"}
Ext.define('Shopware.apps.Config.model.form.Shop', {
    extend:'Ext.data.Model',

    fields: [
        //{block name="backend/config/model/form/shop/fields"}{/block}
        { name: 'id', type:'int', useNull: true, defaultValue: null },
        { name: 'typeSwitch',  persist: false, convert: function(v, record) {

            // The default value determines if a newly created shop is either a subshop (sub) or a language shop (lang).
            // record.phantom is set automatically on new, unsaved records by ExtJs.
            //{block name="backend/config/model/form/shop/fields/typeSwitch/defaultValue"}
            if(record.phantom){
                return 'lang';
            }
            //{/block}

            // The shop type is internally determined by the presence/absence of the mainId
            return record.raw && record.raw.main && record.raw.main.id ? 'lang' : 'sub';
        }},
        { name: 'name', type: 'string' },
        { name: 'title', type: 'string', useNull: true },
        { name: 'position', type: 'int' },
        { name: 'name', type: 'string' },
        { name: 'default', type: 'boolean', defaultValue: false },
        { name: 'active', type: 'boolean', defaultValue: true },
        { name: 'host', type: 'string', useNull: true },
        { name: 'basePath', type: 'string', useNull: true },
        { name: 'baseUrl', type: 'string', useNull: true },
        { name: 'secure', type: 'boolean' },
        { name: 'hosts', type: 'string', useNull: true },
        { name: 'customerScope', type: 'boolean' },

        // Use "convert" because "mapping" is not working with "useNull"
        { name: 'mainId', convert: function(v, record) {
            return v || record.raw && record.raw.main && record.raw.main.id;
        }, useNull: true, defaultValue: null },
        { name: 'localeId', convert: function(v, record) {
            return v || record.raw && record.raw.locale && record.raw.locale.id;
        }, useNull: true },
        { name: 'currencyId', convert: function(v, record) {
            return v || record.raw && record.raw.currency && record.raw.currency.id;
        }, useNull: true },
        { name: 'categoryId', convert: function(v, record) {
            return v || record.raw && record.raw.category && record.raw.category.id;
        }, useNull: true },
        { name: 'templateId', convert: function(v, record) {
            return v || record.raw && record.raw.template && record.raw.template.id;
        }, useNull: true },
        { name: 'documentTemplateId', convert: function(v, record) {
            return v || record.raw && record.raw.documentTemplate && record.raw.documentTemplate.id;
        }, useNull: true },
        { name: 'customerGroupId', convert: function(v, record) {
            return v || record.raw && record.raw.customerGroup && record.raw.customerGroup.id;
        }, useNull: true },
        { name: 'fallbackId', convert: function(v, record) {
            if (v || v === null) {
                return v;
            }
            return record.raw && record.raw.fallback && record.raw.fallback.id;
        }, useNull: true },
        { name: 'deletable', type: 'boolean', convert: function(v, r) { return r.data.id > 1; } }
    ],

    associations: [{
        type: 'hasMany',
        model: 'Shopware.apps.Base.model.Currency',
        name: 'getCurrencies',
        associationKey: 'currencies'
    }, {
        type: 'hasMany',
        model: 'Shopware.apps.Config.model.form.PageGroup',
        name: 'getPages',
        associationKey: 'pages'
    }]
});
//{/block}
