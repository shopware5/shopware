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

//{namespace name=backend/custom_search/translation}

//{block name="backend/config/view/custom_search/facet/listing"}

Ext.define('Shopware.apps.Config.view.custom_search.facet.Listing', {
    extend: 'Shopware.apps.Config.view.custom_search.common.Listing',
    alias: 'widget.config-custom-facet-listing',
    changePositionUrl: '{url controller=customFacet action=changePosition}',

    configure: function() {
        return {
            deleteButton: false,
            editColumn: false,
            pagingbar: false,
            addButton: false,
            displayProgressOnSingleDelete: false,
            columns: {
                name: {
                    header: '{s name="name"}{/s}',
                    sortable: false
                },
                active: {
                    header: '{s name="active"}{/s}',
                    sortable: false
                },
                displayInCategories: {
                    header: '{s name="display_in_categories"}{/s}',
                    sortable: false,
                    renderer: this.displayInAllCategoriesRenderer
                }
            }
        };
    },

    createToolbarItems: function() {
        var me = this,
            items = me.callParent(arguments);

        items = Ext.Array.insert(items, 0, [
            {
                xtype: 'button',
                text: '{s name="add_attribute_facet"}{/s}',
                iconCls: 'sprite-plus-circle-frame',
                handler: Ext.bind(me.addAttributeFacet, me)
            },
            {
                xtype: 'button',
                text: '{s name="add_combined_condition_facet"}{/s}',
                iconCls: 'sprite-plus-circle-frame',
                handler: Ext.bind(me.addCombinedFacet, me)
            }
        ]);

        return items;
    },

    createDeleteColumn: function() {
        var me = this;
        var column = me.callParent(arguments);

        column.getClass = function(value, metadata, record) {
            if (!record.get('deletable')) {
                return 'x-hidden';
            }
        };
        return column;
    },

    addAttributeFacet: function() {
        var me = this;

        me.facetForm.setDisabled(false);

        var facet = {};
        facet['Shopware\\Bundle\\SearchBundle\\Facet\\ProductAttributeFacet'] = {
            label: ''
        };

        me.facetForm.loadFacet(
            Ext.create('Shopware.apps.Base.model.CustomFacet', {
                active: 1,
                displayInCategories: 1,
                deletable: true,
                facet: Ext.JSON.encode(facet)
            })
        );
    },

    addCombinedFacet: function() {
        var me = this;

        me.facetForm.setDisabled(false);

        var facet = {};
        facet['Shopware\\Bundle\\SearchBundle\\Facet\\CombinedConditionFacet'] = {
            label: ''
        };

        me.facetForm.loadFacet(
            Ext.create('Shopware.apps.Base.model.CustomFacet', {
                active: 1,
                deletable: true,
                displayInCategories: 1,
                facet: Ext.JSON.encode(facet)
            })
        );
    },

    onSelectionChange: function(selModel, selection) {
        var me = this;

        if (selection.length <= 0) {
            me.facetForm.setDisabled(true);
            return;
        }
        me.facetForm.loadFacet(selection[0]);
    },

    displayInAllCategoriesRenderer: function (value, element, record) {
        if (record.get('facet').indexOf('CategoryFacet') !== -1) {
            value = false;
        }

        return this.booleanColumnRenderer(value);
    }
});

//{/block}
