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
 * @package    Article
 * @subpackage Detail
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article detail page
 * The properties component contains the grid panel for the article properties.
 * The grid supports a multi selection over a grid combo box element.
 * On top of the grid, a combo box displayed where the user can select a property group.
 * After the selection of a property group, the group options will be displayed in the grid.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/detail/properties"}
Ext.define('Shopware.apps.Article.view.detail.Properties', {
    /**
     * Define that the billing field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend:'Ext.form.FieldSet',
    /**
     * The Ext.container.Container.layout for the fieldset's immediate child items.
     * @object
     */
    layout: 'anchor',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-properties-field-set',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-properties-field-set',
    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title:'{s name=detail/properties/title}Properties{/s}',
        notice:'{s name=detail/properties/notice}To configure an article property, please select a suitable property group via the selection box. Hereafter, a grid with all property values will be displayed. To change the values as well, you can click on the second grid column and modify the value via combo box (multiple selection possible).{/s}',
        property:'{s name=detail/properties/property}Select property{/s}',
        name:'{s name=detail/properties/name_column}Property{/s}',
        value:'{s name=detail/properties/value_column}Values{/s}',
        empty:'{s name=empty}Please select...{/s}'
    },
    /**
     * Contains the field set defaults.
     */
    defaults: {
        labelWidth: 155,
        anchor: '100%'
    },

    requires: [
        'Shopware.apps.Article.controller.Detail'
    ],

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
    initComponent:function () {
        var me = this;
        me.registerEvents();
        me.title = me.snippets.title;
        me.items = me.createElements();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user change the filter group in the property field set.
             *
             * @event
             * @param [string] - The new value
             * @param [Ext.grid.Panel] - The property grid
             */
            'propertySelected'
        );
    },

    /**
     * Creates the elements for the properties field set.
     * @return array
     */
    createElements: function () {
        var me = this;

        var notice = Ext.create('Ext.container.Container', {
            html: me.snippets.notice,
            style: 'font-style: italic; color: #999; margin: 0 0 8px 0; font-size: 11px;'
        });

        me.propertyGrid = me.createPropertyGrid();

        me.propertyCombo = Ext.create('Ext.form.field.ComboBox', {
            name: 'filterGroupId',
            store: me.propertyStore,
            labelWidth: 155,
            queryMode: 'local',
            valueField: 'id',
            displayField: 'name',
            emptyText: me.snippets.empty,
            fieldLabel: me.snippets.property,
            listeners: {
                change: function(field, newValue) {
                    me.fireEvent('propertySelected', newValue, me.propertyGrid, me.propertyStore)
                }
            }
        });
        return [ notice, me.propertyCombo, me.propertyGrid ];
    },

    /**
     * Creates the grid panel for the article properties.
     * @return Ext.grid.Panel
     */
    createPropertyGrid: function() {
        var me = this, store = null;

        if (me.article && me.article.get('filterGroupId')) {
            var property = me.propertyStore.getById(me.article.get('filterGroupId'));
            if (property) {
                store = property.getOptions();

                var selected = me.article.getPropertyValues();
                if (selected && selected.getCount() > 0) {
                    selected.each(function(item) {
                        var gridItem = store.getById(item.get('optionId'));
                        var propertyValues = gridItem.get('propertyValues');
                        if (!Ext.isArray(propertyValues)) {
                            propertyValues = [];
                        }
                        propertyValues.push(item.get('id'));
                        gridItem.set('propertyValues', propertyValues);
                    });
                }
            }
        }

        return Ext.create('Ext.grid.Panel', {
            hidden: (store === null),
            store: store,
            height: 155,
            name: 'property-grid',
            cls: Ext.baseCSSPrefix + 'free-standing-grid ' + Ext.baseCSSPrefix + 'article-properties-grid',
            title: '{s name=detail/property/title}Properties{/s}',
            plugins: [{
                ptype: 'cellediting',
                clicksToEdit: 1,
                cls: Ext.baseCSSPrefix + 'article-properties-grid-editor'
            }],
            selType: 'cellmodel',
            columns: [
                {
                    header: me.snippets.name,
                    dataIndex: 'name',
                    width: 150
                } , {
                    header: me.snippets.value,
                    dataIndex: 'propertyValues',
                    flex: 1,
                    editor: me.createCellEditor(),
                    renderer: me.getValueRenderer
                }
            ]
        });
    },

    /**
     * Creates the cell editor combo box for the value column
     *
     */
    createCellEditor: function() {
        var me = this;

        return Ext.create('Ext.ux.form.field.BoxSelect', {
            multiSelect: true,
            forceSelection: true,
            delimiter: ', ',
            typeAhead: true,
            displayField: 'value',
            valueField: 'id',
            queryMode: 'local',
            cls: Ext.baseCSSPrefix + 'article-properties-box-selection'
        });
    },

    /**
     * Renderer function of the value column. Converts the passed option value ids into the value names.
     * @param values
     * @param metadata
     * @param record
     * @param rowIndex
     * @param colIndex
     * @return array
     */
    getValueRenderer: function(values, metadata, record, rowIndex, colIndex) {
        var me = this,
            displayValues = [];

        if (!values) {
            return values;
        }
        if (values.length > 0 && record) {
            var valueStore = record.getValues();
            Ext.each(values, function(valueId) {
                var value = valueStore.getById(valueId);
                if (value) {
                    displayValues.push(value.get('value'));
                }
            });
            return displayValues;
        }
    }

});
//{/block}
