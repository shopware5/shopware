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

//{namespace name="backend/custom_search/translation"}

Ext.define('Shopware.form.field.CustomSortingGrid', {
    extend: 'Shopware.form.field.Grid',
    alias: 'widget.shopware-form-field-custom-sorting-grid',
    mixins: ['Shopware.model.Helper'],
    hideHeaders: false,

    initComponent: function() {
        var me = this;

        me.callParent(arguments);
        me.grid.view.on('drop', Ext.bind(me.onDrop, me));
    },

    createColumns: function() {
        var me = this;

        return [
            me.createSortingColumn(),
            me.applyBooleanColumnConfig({
                dataIndex: 'active',
                width: 90,
                header: '{s name="active"}{/s}'
            }),
            {
                header: '{s name="sorting_label"}{/s}',
                dataIndex: 'label',
                flex: 1
            },
            {
                header: '{s name="custom_sorting_default_sorting"}{/s}',
                renderer: Ext.bind(me.defaultColumnRenderer, me),
                flex: 1
            },
            me.createActionColumn()
        ];
    },

    removeItem: function(record) {
        var me = this;

        me.callParent(arguments);
        this.grid.reconfigure(this.grid.getStore());
    },

    defaultColumnRenderer: function(value, meta, record) {
        return this.booleanColumnRenderer(
            (this.store.indexOf(record) === 0),
            meta,
            record
        );
    },

    onDrop: function() {
        //refresh whole listing for `default sorting` column renderer
        this.grid.reconfigure(this.grid.getStore());
    }
});
