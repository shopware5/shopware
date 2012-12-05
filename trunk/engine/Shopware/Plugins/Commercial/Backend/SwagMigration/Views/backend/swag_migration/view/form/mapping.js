/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package    Migration
 * @subpackage Form
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Migration database form
 * Form for the database and import settings
 */
//{namespace name=backend/swag_migration/main}
//{block name="backend/swag_migration/view/form/mapping"}
Ext.define('Shopware.apps.SwagMigration.view.form.Mapping', {
    /**
     * Define that the billing field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend: 'Ext.form.Panel',
    bodyStyle: 'padding:10px',

    layout: {
        type: 'hbox',
        align: 'stretch'
    },

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.migration-form-mapping',
    /**
     * Set css class for this component
     * @string
     */
    cls: 'shopware-form',

    defaults: {
        style: {
            margin: '0 10px'
        },
        flex: 1,
        border: 1
    },

    snippets: {
        group: {
            group: '{s name=group/group}Group{/s}',
            language: '{s name=group/language}Language{/s}',
            shop: '{s name=group/shop}Shops{/s}',
            customer_group: '{s name=group/customerGroup}Customer group{/s}',
            price_group: '{s name=group/priceGroup}Price group{/s}',
            payment_mean: '{s name=group/paymentMean}Payment mean{/s}',
            order_status: '{s name=group/orderStatus}Order status{/s}',
            tax_rate: '{s name=group/taxRate}Tax rate{/s}',
            attribute: '{s name=group/attribute}Attribute{/s}',
            other: '{s name=group/other}Other{/s}'
        }
    },

    /**
	 * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
	 *
	 * @return void
	 */
    initComponent: function () {
        var me = this;

        me.items = me.createItems();

        me.addEvents(
            /**
             * Fired when a combo editor is expanded and the store needs to be loaded
             */
            'beforequery',

            /**
             * Fired when a combobox entry changed in order to enable the "next" button
             */
            'validate'
        );

        me.callParent(arguments);
    },

    /**
     * Returns an array with the grids available in this view
     * @return Array
     */
    createItems: function() {
        var me = this;

        me.leftGrid = me.createGrid(me.mappingStoreLeft);
        me.rightGrid = me.createGrid(me.mappingStoreRight);

        return [
            me.leftGrid,
            me.rightGrid
        ];
    },

    /**
     * Create the grids for the mappings
     * @param gridStore Ext.data.Store
     */
    createGrid: function(gridStore) {
        var me = this;

        return Ext.create('Ext.grid.Panel', {
            anchor: '95%',
            store: gridStore,
            plugins: [
                Ext.create('Ext.grid.plugin.CellEditing', {
                    clicksToEdit: 1
                })
            ],
            features: [ me.createGroupingFeature() ],
            columns: me.getGridColumns()
        });
    },

    /**
     * create the grouping feature for the grid
     * @return Ext.grid.feature.GroupingSummary
     */
    createGroupingFeature: function() {
        var me = this;

        return Ext.create('Ext.grid.feature.GroupingSummary', {
            groupHeaderTpl: Ext.create('Ext.XTemplate',
                '<span>{ name:this.formatHeader }</span>',
                {
                    formatHeader: function(field) {
                        if(me.snippets.group[field]) {
                            return Ext.String.format("[0]: [1]", me.snippets.group.group, me.snippets.group[field]);
                        }
                        return field;
                    }
                }
            )
        });
    },

    getGridColumns: function() {
        var me = this;

        return [
            {
                dataIndex: 'name',
                header: 'Name',
                sortable: false,
                flex: 3
            }, {
                dataIndex: 'mapping',
                header: 'Mapping',
                sortable: false,
                flex: 3,
                renderer: function (value, col, record) {
                    return record.data.mapping_name;
                },

                editor: {
                    xtype: 'combo',
                    allowBlank: false,
                    mode: 'remote',
                    valueField: 'id',
                    displayField: 'name',
                    editable: false,
                    store: Ext.create('Ext.data.Store', {
                        model: 'Shopware.apps.SwagMigration.model.MappingValue',
                        proxy: {
                            type: 'ajax',
                            url: '{url action="mappingValuesList"}',
                            reader: {
                                type: 'json',
                                root: 'data',
                                totalProperty: 'count'
                            }
                        }

                    }),
                    listeners: {
                        'select': { fn:function(combo, records, index) {
                            var record = records[0];
                            combo.ownerCt.editingPlugin.context.record.set('mapping_name', record.data.name);
                            combo.ownerCt.editingPlugin.completeEdit();
                            me.fireEvent('validate');
//                            var disableButton = !this.areAllRequiredItemsMapped();
//                            this.buttons[1].setDisabled(disableButton);

                        }, scope: this },
                        'beforequery': { fn:function(e){
                            me.fireEvent('beforequery', e, me);
                        }, scope:this },
                        'beforeexpand': { fn:function(){
                            return true;
                        }, scope:this }
                    }
                }
            }, {
                dataIndex: 'validate',
                header: '',
                sortable: false,
                flex: 1,
                renderer: function(val, p, r){
                    if(r.data.mapping_name && r.data.mapping_name != 'keine Auswahl') {
                        return Ext.String.format('<span data-qtip="[0]" class="sprite-tick-circle-frame" ' +
                                'style="width: 25px; height: 25px; display: inline-block;">&nbsp;</span>',
                                "This property has been mapped");
                    } else if(r.data.required) {
                        return Ext.String.format('<span data-qtip="[0]" class="sprite-minus-circle-frame" ' +
                                'style="width: 25px; height: 25px; display: inline-block;">&nbsp;</span>',
                                "Mapping of this property is required");
                    } else {
                        return Ext.String.format('<span data-qtip="[0]" class="sprite-exclamation--frame" ' +
                                'style="width: 25px; height: 25px; display: inline-block;">&nbsp;</span>',
                                "Properties not being mapped will be skipped during the migration");
                    }
                }
            }, {
                dataIndex: 'group',
                header: "Gruppe",
                flex: 1,
                sortable: false,
                hidden: true
            }
        ];
    },

    /**
     * Helper function to check if all required mappings have been set
     * @return  Boolean
     */
    areAllRequiredItemsMapped: function() {
        var allMapped = true;

        this.mappingStoreLeft.each(function(record){
            if(record.data.required) {
                if(record.data.mapping_name == '' || record.data.mapping_name == 'keine Auswahl' || record.data.mapping == '') {
                    allMapped = false;
                }
            }
        }, this);

        this.mappingStoreRight.each(function(record){
            if(record.data.required) {
                if(record.data.mapping_name == '' || record.data.mapping_name == 'keine Auswahl' || record.data.mapping == '') {
                    allMapped = false;
                }
            }
        }, this);

        return allMapped;
    },


    /**
     * Helper function that will return a unified store for the left and the right grid.
     * @return Ext.data.Store
     */
    getGridStore: function() {
        var me = this,
            totalStore = Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.SwagMigration.model.Mapping'
        });

        totalStore.removeAll();
        me.mappingStoreLeft.each(function(record){
            totalStore.add(record);
        }, this);
        me.mappingStoreRight.each(function(record){
            totalStore.add(record);
        }, this);

        return totalStore;
    },

    /**
     * Helper function called by the controller to determine if the form is valid. As getForm().isValid() is not
     * a option in any case, this method was implemented
     * @return Boolean
     */
    validate: function() {
        return this.areAllRequiredItemsMapped();
    }


});
//{/block}
