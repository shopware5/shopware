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
 * @category    Shopware
 * @package     Base
 * @subpackage  Attribute
 * @version     $Id$
 * @author      shopware AG
 */

//{namespace name="backend/attributes/fields"}

Ext.define('Shopware.form.field.ProductSingleSelection', {
    extend: 'Shopware.form.field.SingleSelection',
    alias: 'widget.shopware-form-field-product-single-selection',

    getComboConfig: function() {
        var me = this;
        var config = me.callParent(arguments);
        config.valueField = 'number';

        config.tpl = Ext.create('Ext.XTemplate',
            '<tpl for=".">',
                '<div class="x-boundlist-item">' +
                    //active renderer
                    '<tpl if="articleActive && variantActive">' +
                        '[{s name="active_single_selection"}{/s}]' +
                    '<tpl else>' +
                        '[{s name="inactive_single_selection"}{/s}]' +
                    '</tpl>' +

                    //number + data renderer
                    ' {literal}<b>{number}</b> - {name}{/literal}' +

                    //additional text renderer
                    '<tpl if="additionalText">' +
                        '<i>{literal} ({additionalText})</i>{/literal}' +
                    '</tpl>',
                '</div>',
            '</tpl>'
        );
        config.displayTpl = Ext.create('Ext.XTemplate',
            '<tpl for=".">',
                //active renderer
                '<tpl if="articleActive && variantActive">' +
                    '[{s name="active_single_selection"}{/s}]' +
                '<tpl else>' +
                    '[{s name="inactive_single_selection"}{/s}]' +
                '</tpl>' +

                //number + data renderer
                ' {literal}{number} - {name}{/literal}' +

                //additional text renderer
                '<tpl if="additionalText">' +
                    '{literal} ({additionalText}){/literal}' +
                '</tpl>',
            '</tpl>'
        );
        return config;
    }
});
