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
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/attributes/main"}

Ext.define('Shopware.apps.Attributes.model.Types', {
    extend: 'Shopware.data.Model',

    snippets: {
        string: '{s name="type_string"}{/s}',
        text: '{s name="type_text"}{/s}',
        html: '{s name="type_html"}{/s}',
        integer: '{s name="type_integer"}{/s}',
        float: '{s name="type_float"}{/s}',
        date: '{s name="type_date"}{/s}',
        datetime: '{s name="type_datetime"}{/s}',
        boolean: '{s name="type_boolean"}{/s}',
        combobox: '{s name="type_combobox"}{/s}',
        single_selection: '{s name="type_single_selection"}{/s}',
        multi_selection: '{s name="type_multi_selection"}{/s}',
    },

    fields: [
        { name: 'label', type: 'string', convert: function(value, record) {
            return record.getLabel();
        } },
        { name: 'unified', type: 'string' },
        { name: 'dbal', type: 'string' },
        { name: 'sql', type: 'string' }
    ],

    getLabel: function() {
        var name = this.get('unified');

        if (this.snippets.hasOwnProperty(name)) {
            return this.snippets[name];
        } else {
            return '';
        }
    }
});
