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

Ext.define('Shopware.form.field.OrderDetailSingleSelection', {
    extend: 'Shopware.form.field.SingleSelection',
    alias: 'widget.shopware-form-field-oder-detail-single-selection',

    getComboConfig: function() {
        var me = this;
        var config = me.callParent(arguments);

        config.tpl = Ext.create('Ext.XTemplate',
            '<tpl for=".">',
                '<div class="x-boundlist-item">{literal}<b>{quantity}x {articleNumber}</b> ({[this.formatPrice(values.price)]}) - {articleName}{/literal}</div>',
            '</tpl>',
            {
                formatPrice: function(value) {
                    if ( value === Ext.undefined ) {
                        return value;
                    }
                    return Ext.util.Format.currency(value);
                }
            }
        );
        config.displayTpl = Ext.create('Ext.XTemplate',
            '<tpl for=".">',
                    '{literal}{quantity}x {articleNumber}</b> ({[this.formatPrice(values.price)]}) - {articleName}{/literal}',
            '</tpl>',
            {
                formatPrice: function(value) {
                    if ( value === Ext.undefined ) {
                        return value;
                    }
                    return Ext.util.Format.currency(value);
                }
            }
        );
        return config;
    },
});