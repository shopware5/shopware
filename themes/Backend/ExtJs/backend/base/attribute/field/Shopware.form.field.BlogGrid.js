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

Ext.define('Shopware.form.field.BlogGrid', {
    extend: 'Shopware.form.field.Grid',
    alias: 'widget.shopware-form-field-blog-grid',
    mixins: ['Shopware.model.Helper'],

    createColumns: function() {
        var me = this;

        var displayColumn = { dataIndex: 'displayDate', flex: 1 };
        me.applyDateColumnConfig(displayColumn);

        return [
            me.createSortingColumn(),
            { dataIndex: 'authorName' },
            { dataIndex: 'title', flex: 1 },
            displayColumn,
            me.createActionColumn()
        ];
    },

    createSearchField: function() {
        return Ext.create('Shopware.form.field.BlogSingleSelection', this.getComboConfig());
    }
});
