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
 * @package    Emotion
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */
//{block name="backend/emotion/view/components/manufacturer_slider"}
//{namespace name=backend/emotion/view/components/manufacturer_slider}
Ext.define('Shopware.apps.Emotion.view.components.ManufacturerSlider', {
    extend: 'Shopware.apps.Emotion.view.components.Base',
    alias: 'widget.emotion-components-manufacturer-slider',

    /**
     * Snippets for the component.
     * @object
     */
    snippets: {
        'select_manufacturer': '{s name=select_manufacturer}Select manufacturer(s){/s}',
        'manufacturer_administration': '{s name=manufacturer_administration}Manufacturer administration{/s}',
        'name': '{s name=name}Name{/s}',
        'actions': '{s name=actions}Action(s){/s}',

        manufacturer_slider_title: '{s name=manufacturer_slider_title}Title{/s}',
        manufacturer_slider_arrows: '{s name=manufacturer_slider_arrows}Display arrows{/s}',
        manufacturer_slider_scrollspeed: '{s name=manufacturer_slider_scrollspeed}Scroll speed{/s}',
        manufacturer_slider_rotation: '{s name=manufacturer_slider_rotation}Rotate automatically{/s}',
        manufacturer_slider_rotatespeed: '{s name=manufacturer_slider_rotatespeed}Rotation speed{/s}',
        manufacturer_category: '{s name=manufacturer_category}Select category{/s}',

        no_border: {
            fieldLabel: '{s name="noBorder/label" namespace="backend/emotion/view/components/article"}{/s}',
            supportText: '{s name="noBorder/supportText" namespace="backend/emotion/view/components/article"}{/s}'
        }
    },

    /**
     * Initialize the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.callParent(arguments);

        me.manufacturerType = me.down('emotion-components-fields-manufacturer-type');
        me.categorySelect = me.down('emotion-components-fields-category-selection');
        me.manufacturerType.on('change', me.onChangeType, me);
        me.add(me.createSupplierFieldset());
        me.setDefaultValues();
        me.getGridData();
        me.refreshHiddenValue();
    },

    /**
     * Sets default values if the banner slider
     * wasn't saved previously.
     *
     * @public
     * @return void
     */
    setDefaultValues: function() {
        var me = this,
            numberfields =  me.query('numberfield');

        if(!me.manufacturerType.getValue() || me.manufacturerType.getValue() === 'selected_manufacturers') {
            me.categorySelect.hide().disable();
        }
        if(!me.manufacturerType.getValue() || me.manufacturerType.getValue() !== 'selected_manufacturers') {
            me.supplierFieldset.hide().disable();
        }

        Ext.each(numberfields, function(field) {
            if(!field.getValue()) {
                field.setValue(500);
            }
        });
    },

    /**
     * Creates the fieldset which holds the banner administration. The method
     * also creates the banner store and registers the drag and drop plugin
     * for the grid.
     *
     * @public
     * @return [object] Ext.form.FieldSet
     */
    createSupplierFieldset: function() {
        var me = this;

        me.searchStore = Ext.create('Shopware.apps.Base.store.Supplier');
        me.supplierCombo = Ext.create('Shopware.form.field.PagingComboBox', {
            fieldLabel: me.snippets.select_manufacturer,
            valueField: 'id',
            pageSize: 15,       // SW-4341 without pageSize being set no pagination is shown
            labelWidth: 155,
            minChars:0,
            displayField: 'name',
            store: me.searchStore,
            listeners: {
                scope: me,
                select: me.onSelectSupplier
            }
        });

        me.supplierStore = Ext.create('Ext.data.Store', {
            fields: [ 'position', 'name', 'supplierId' ]
        });

        me.ddGridPlugin = Ext.create('Ext.grid.plugin.DragDrop');

        me.supplierGrid = Ext.create('Ext.grid.Panel', {
            columns: me.createColumns(),
            autoScroll: true,
            store: me.supplierStore,
            height: 200,
            viewConfig: {
                plugins: [ me.ddGridPlugin ],
                listeners: {
                    scope: me,
                    drop: me.onRepositionSupplier
                }
            }
        });

        return me.supplierFieldset = Ext.create('Ext.form.FieldSet', {
            title: me.snippets.manufacturer_administration,
            layout: 'anchor',
            defaults: { anchor: '100%' },
            items: [ me.supplierCombo, me.supplierGrid ]
        });
    },

    /**
     * Helper method which creates the column model
     * for the banner administration grid panel.
     *
     * @public
     * @return [array] computed columns
     */
    createColumns: function() {
        var me = this, snippets = me.snippets;

        return [{
            header: '&#009868;',
            width: 24,
            hideable: false,
            renderer : me.renderSorthandleColumn
        }, {
            dataIndex: 'name',
            header: snippets.name,
            flex: 1
        }, {
            xtype: 'actioncolumn',
            header: snippets.actions,
            width: 60,
            items: [{
                iconCls: 'sprite-minus-circle',
                action: 'delete-banner',
                scope: me,
                handler: me.onDeleteSupplier
            }]
        }];
    },

    /**
     * Event listener method which will be fired when the user
     * repositions a supplier through drag and drop.
     *
     * Sets the new position of the supplier in the supplier store
     * and saves the data to an hidden field.
     *
     * @public
     * @event drop
     * @return void
     */
    onRepositionSupplier: function() {
        var me = this;

        var i = 0;
        me.supplierStore.each(function(item) {
            item.set('position', i);
            i++;
        });
        me.refreshHiddenValue();
    },

    /**
     * Event listener method which will be triggered when the user selects
     * the type of supplier slider.
     *
     * The method hides / shows the necessary components.
     *
     * @public
     * @event select
     * @param [object] field - Shopware.form.field.PagingComboBox
     * @param [string] newValue - selected value
     * @return void
     */
    onChangeType: function(field, newValue) {
        var me = this;

        if(newValue === 'manufacturers_by_cat') {
            me.categorySelect.show().enable();
            me.supplierFieldset.hide().disable();
        } else {
            me.categorySelect.hide().disable();
            me.supplierFieldset.show().enable();
        }
    },

    /**
     * Event listener method which will be triggered when the user selects a
     * supplier.
     *
     * Adds the selected supplier to the supplier grid.
     *
     * @public
     * @event select
     * @param [object] field - Shopware.form.field.PagingComboBox
     * @param [array] records - Array of the selected records
     * @return void
     */
    onSelectSupplier: function(field, records) {
        var me = this,
            store = me.supplierStore,
            record = records[0];

        var model = Ext.create('Shopware.apps.Emotion.model.ManufacturerSlider', {
            position: store.getCount(),
            name: record.get('name'),
            supplierId: record.get('id')
        });
        store.add(model);
        field.inputEl.dom.value = '';
        me.refreshHiddenValue();
    },

    /**
     * Renderer for sorthandle-column
     *
     * @param [string] value
     */
    renderSorthandleColumn: function() {
        return '<div style="cursor: move;">&#009868;</div>';
    },

    /**
     * Refreshes the mapping field in the model
     * which contains all suppliers in the grid.
     *
     * @public
     * @return void
     */
    refreshHiddenValue: function() {
        var me = this,
            store = me.supplierStore,
            cache = [];

        store.each(function(item) {
            cache.push(item.data);
        });
        var record = me.getSettings('record');
        record.set('mapping', cache);
    },

    /**
     * Refactor sthe mapping field in the global record
     * which contains all supplier in the grid.
     *
     * Adds all suppliers to the supplier administration grid
     * when the user opens the component.
     *
     * @return void
     */
    getGridData: function() {
        var me = this,
            elementStore = me.getSettings('record').get('data'), supplierSlider;

        Ext.each(elementStore, function(element) {
            if(element.key === 'selected_manufacturers') {
                supplierSlider = element;
                return false;
            }
        });

        if(supplierSlider && supplierSlider.value) {
            Ext.each(supplierSlider.value, function(item) {
                me.supplierStore.add(Ext.create('Shopware.apps.Emotion.model.ManufacturerSlider', item));
            });
        }
    },

    /**
     * Event listener method which will be triggered when the user
     * deletes a supplier from supplier administration grid panel.
     *
     * Removes the banner from the banner store.
     *
     * @event click#actioncolumn
     * @param [object] grid - Ext.grid.Panel
     * @param [integer] rowIndex - Index of the clicked row
     * @param [integer] colIndex - Index of the clicked column
     * @param [object] item - DOM node of the clicked row
     * @param [object] eOpts - additional event parameters
     * @param [object] record - Associated model of the clicked row
     */
    onDeleteSupplier: function(grid, rowIndex, colIndex, item, eOpts, record) {
        var me = this;
        var store = grid.getStore();
        store.remove(record);
        me.refreshHiddenValue();
    }
});
//{/block}
