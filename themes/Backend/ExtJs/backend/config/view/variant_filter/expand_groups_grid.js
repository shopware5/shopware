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
 */

//{namespace name=backend/config/view/variant_filter}

//{block name="backend/config/view/variant_filter/expand_groups_grid"}
Ext.define('Shopware.apps.Config.view.variantFilter.ExpandGroupsGrid', {
    extend: 'Shopware.form.field.Grid',
    alias: 'widget.variant-filter-expand-group-grid',
    mixins: ['Shopware.model.Helper'],
    hideHeaders: false,

    /**
     * @type { Shopware.apps.Config.view.variantFilter.ExpandGroupsHiddenField }
     */
    hiddenField: null,

    initComponent: function() {
        var me = this,
            factory = Ext.create('Shopware.attribute.SelectionFactory');

        me.store = factory.createEntitySearchStore("Shopware\\Models\\Article\\Configurator\\Group");
        me.searchStore = factory.createEntitySearchStore("Shopware\\Models\\Article\\Configurator\\Group");

        me.store.getProxy().setReader(Ext.create('Shopware.apps.Config.view.variantFilter.DynamicVariantReader', {
            groupsGrid: me
        }));

        me.callParent(arguments);
    },

    createGrid: function () {
        var grid = this.callParent(arguments),
            gridColumns = grid.columns;

        Ext.each(gridColumns, function (column) {
           Ext.apply(column, {
               hideable: false,
               sortable: false
           });
        });

        return grid;
    },

    /**
     * @return { Shopware.apps.Config.view.variantFilter.ExpandGroupsHiddenField|null }
     */
    getHiddenField: function () {
        if (this.hiddenField === null) {
            var panel = this.up('panel'),
                hiddenField;

            if (!panel) {
                return null;
            }

            hiddenField = panel.down('variant-filter-expand-groups-hidden-field');

            if (hiddenField.length === 0) {
                return null;
            }

            this.hiddenField = hiddenField;
        }

        return this.hiddenField;
    },

    /**
     * @returns { Ext.column.Column[] }
     */
    createColumns: function() {
        var me = this,
            columns,
            labelColumn;

        columns = me.callParent(arguments);

        labelColumn = columns.find(function (column) {
            if (column.dataIndex === 'label') {
                return column;
            }

            return;
        });

        Ext.apply(labelColumn, {
            header: '{s name="variant_facet/header/label"}Group{/s}'
        });

        columns = Ext.Array.insert(columns, columns.length -1, [{
            xtype: 'actioncolumn',
            name: 'expandGroupIds',
            dataIndex: 'expandGroup',
            align: 'center',
            width: 90,
            header: '{s name="variant_facet/header/expandable"}Expand{/s}',
            items: [
                {
                    tooltip: '{s name="variant_facet/header/expandale/tooltip"}Activate / Deactivate{/s}',
                    handler: function (grid, rowIndex, colIndex, item, eOpts, record) {
                        var previousStatus = record.get('expandGroup'),
                            newStatus = !previousStatus,
                            expandGroupIds = [];

                        record.set('expandGroup', newStatus);
                        record.commit();

                        grid.getStore().each(function (record) {
                            if (record.get('expandGroup') === true) {
                                expandGroupIds.push(record.get('id'));
                            }
                        });

                        me.getHiddenField().setValue(me.joinExpandedGroupIds(expandGroupIds));
                    },
                    getClass: function(value, metaData, record) {
                        if (record.get('expandGroup')) {
                            return 'sprite-ui-check-box';
                        } else {
                            return 'sprite-ui-check-box-uncheck';
                        }
                    }
                }
            ]
        }]);

        return columns;
    },

    /**
     * @param { Array } value
     * @returns { String }
     */
    joinExpandedGroupIds: function (value) {
        if (value.length === 0) {
            return null;
        }

        return this.separator + value.join(this.separator) + this.separator;
    }
});
//{/block}
