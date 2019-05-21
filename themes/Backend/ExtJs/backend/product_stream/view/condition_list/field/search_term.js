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
 * @package    ProductStream
 * @subpackage Window
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/product_stream/main}
//{block name="backend/product_stream/view/condition_list/field/search_term"}
Ext.define('Shopware.apps.ProductStream.view.condition_list.field.SearchTerm', {

    extend: 'Ext.form.FieldContainer',
    layout: { type: 'hbox', align: 'stretch' },
    mixins: [ 'Ext.form.field.Base' ],
    height: 30,
    value: undefined,

    initComponent: function() {
        var me = this;
        me.items = me.createItems();
        me.callParent(arguments);
    },

    createItems: function() {
        var me = this;
        return [
            me.createTerm()
        ];
    },

    createTerm: function() {
        var me = this;

        me.term = Ext.create('Ext.form.field.Text', {
            labelWidth: 150,
            fieldLabel: '{s name=term}Term{/s}',
            allowBlank: false,
            padding: '0 0 0 10',
            flex: 1
        });
        return me.term;
    },

    getValue: function() {
        return this.value;
    },

    setValue: function(value) {
        var me = this;

        me.value = value;

        if (!Ext.isObject(value)) {
            me.term.setValue(1);
            return;
        }

        if (value.hasOwnProperty('term')) {
            me.term.setValue(value.term);
        }
    },

    getSubmitData: function() {
        var value = {};

        value[this.name] = {
            term: this.term.getValue()
        };
        return value;
    }
});
//{/block}
