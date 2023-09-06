/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 *
 * @category    Shopware
 * @package     Base
 * @subpackage  Attribute
 * @version     $Id$
 * @author      shopware AG
 */

//{namespace name="backend/attributes/fields"}

Ext.define('Shopware.form.field.ProductFeedSingleSelection', {
    extend: 'Shopware.form.field.SingleSelection',
    alias: 'widget.shopware-form-field-product-feed-single-selection',

    getComboConfig: function() {
        var me = this;
        var config = me.callParent(arguments);

        config.tpl = Ext.create('Ext.XTemplate',
            '<tpl for=".">',
            '<div class="x-boundlist-item">',

            '<tpl if="active">',
            '[{s name="active_single_selection"}{/s}]',
            '<tpl else>',
            '[{s name="inactive_single_selection"}{/s}]',
            '</tpl>',

            '<tpl if="fileName">',
            ' {literal}{name} <i>({fileName})</i>{/literal}',
            '<tpl else>',
            ' {literal}{name}{/literal}',
            '</tpl>',
            '</div>',
            '</tpl>'
        );
        config.displayTpl = Ext.create('Ext.XTemplate',
            '<tpl for=".">',
            '<tpl if="active">',
            '[{s name="active_single_selection"}{/s}]',
            '<tpl else>',
            '[{s name="inactive_single_selection"}{/s}]',
            '</tpl>',

            '<tpl if="fileName">',
            ' {literal}{name} ({fileName}){/literal}',
            '<tpl else>',
            ' {literal}{name}{/literal}',
            '</tpl>',
            '</tpl>'
        );
        return config;
    }
});
