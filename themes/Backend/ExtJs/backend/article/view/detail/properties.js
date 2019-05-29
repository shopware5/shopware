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
 * @package    Article
 * @subpackage Detail
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
    extend: 'Ext.form.Panel',

    /**
     * The Ext.container.Container.layout for the fieldset's immediate child items.
     * @object
     */
    layout: {
        type: 'vbox',
        align : 'stretch',
        pack  : 'start'
    },

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.article-properties-panel',

    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-esd-list',

    style: 'background: #ebedef',

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
        empty:'{s name=empty}Please select...{/s}',
        comboset_label: '{s name=detail/properties/comboset_label}Choose set{/s}',
        combogroup_label: '{s name=detail/properties/combogroup_label}Assign properties{/s}',
        button_text: '{s name=detail/properties/button_text}Create{/s}',
        description: '{s name=detail/properties/description}You have the ability to use the keyboard shortcuts Tab and Shift-Tab to navigate between the fields and CTRL+ENTER to add the selected options.{/s}'
    },
    /**
     * Contains the field set defaults.
     */
    defaults: {
        labelWidth: 155,
        anchor: '100%'
    },

    bodyPadding: 10,

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
        var me = this,
            mainWindow = me.subApp.articleWindow;

        mainWindow.on('storesLoaded', me.onStoresLoaded, me);

        me.items = [ me.createElements(), me.createGrid() ];
        me.callParent(arguments);
    },

    /**
     * Creates the form panel at the top of the component containing instructions on how to use the component
     * and the necessary fields
     *
     * @returns { Ext.form.FieldSet }
     */
    createElements: function() {
        var me = this;

        me.setComboBox = Ext.create('Shopware.form.field.Search', {
            fieldLabel: me.snippets.comboset_label,
            name: 'filterGroupId',
            emptyText: me.snippets.empty,
            labelWidth: 155,
            forceSelection: true,
            pageSize: 10,
            allowBlank: true,
            listeners: {
                scope: me,
                change: function(element, value) {
                    me.propertyGroupStore.getProxy().extraParams.setId = value;
                    me.propertyGroupStore.load();
                    me.groupComboBox.setDisabled(false);

                    me.groupComboBox.setValue('');
                    me.groupComboBox.setDisabled(value === null || value === '');

                    me.article.set('filterGroupId', value);
                }
            }
        });

        me.groupComboBox = Ext.create('Shopware.form.field.Search', {
            fieldLabel: me.snippets.combogroup_label,
            name: 'groupId',
            emptyText: me.snippets.empty,
            flex: 1,
            forceSelection: true,
            labelWidth: 155,
            pageSize: 10,
            disabled: true,
            listeners: {
                scope: me,
                change: function(component, value) {
                    me.valueComboBox.getStore().currentPage = 1;
                    me.propertyValueStore.getProxy().extraParams.groupId = value;
                    me.propertyValueStore.load();
                    me.valueComboBox.setValue('');
                    me.valueComboBox.setDisabled(value === null || value === '');
                }
            }
        });

        me.valueComboBox = Ext.create('Shopware.form.field.Search', {
            name: 'values',
            emptyText: me.snippets.empty,
            forceSelection: false,
            disabled: true,
            flex: 1,
            pageSize: 10,
            multiSelect: true,
            enableKeyEvents: true,
            listeners: {
                scope: me,
                select: function(field, value) {
                    var record = value.shift();
                    me.addValue(
                        me.groupComboBox.getValue(),
                        { id: record.get('id'), value: record.get('name') }
                    );
                    field.setValue('');
                    field.getStore().load();
                },
                specialkey: function(field, event) {
                    if(event.getKey() === Ext.EventObject.ENTER && event.ctrlKey === true) {
                        event.stopEvent();
                        me.assignValuesToStore();
                    }
                }
            }
        });

        me.addButton = Ext.create('Ext.button.Button', {
            text: me.snippets.button_text,
            cls: 'small primary',
            margin: '2 0 0',
            handler: Ext.bind(me.assignValuesToStore, me)
        });

        me.fieldContainer = Ext.create('Ext.container.Container', {
            layout: 'hbox',
            defaults: {
                labelWidth: 155
            },
            items: [ me.groupComboBox, me.valueComboBox, me.addButton ]
        });

        me.noticeContainer = Ext.create('Ext.container.Container', {
            html: me.snippets.notice,
            style: 'font-style: italic; color: #999; margin: 0 0 15px 0; font-size: 11px;'
        });

        me.keyboardNoticeContainer = Ext.create('Ext.container.Container', {
            html: me.snippets.description,
            style: 'font-style: italic; color: #999; margin: 8px 0 0 0; font-size: 11px;'
        });

        me.fieldset = Ext.create('Ext.form.FieldSet', {
            title: me.snippets.title,
            layout: 'anchor',
            defaults: {
                labelWidth: 155,
                anchor: '100%'
            },
            items: [
                me.noticeContainer,
                me.setComboBox,
                me.fieldContainer,
                me.keyboardNoticeContainer
            ]
        });

        return me.fieldset;
    },

    /**
     * Helper method which sets a value form the combobox to the field set
     */
    assignValuesToStore: function() {
        var me = this,
            field = me.valueComboBox,
            value = field.getValue(),
            element = me.getValueByValue(value, field.getStore());
        if (!value) {
            return;
        }
        if (element) {
            me.addValue({
                id: element.get('id'),
                value: element.get('name')
            });
            return;
        }
        me.createValue(
            me.groupComboBox.getValue(),
            value
        );
        field.setValue('');
        field.getStore().load();
    },

    /**
     * Helper method which checks if an entered value is already created.
     *
     * @param { Number } value
     * @param { Ext.data.Store } store
     * @returns { Boolean|Null }
     */
    getValueByValue: function(value, store) {
        var found = null;
        store.each(function(record) {
            if (record.get('name') == value) {
                found = record;
                return false;
            }
        });
        return found;
    },

    /**
     * Creates a new property value using an AJAX requests and adds
     * the newly created value to the store.
     *
     * @param { Number } groupId
     * @param { String } newValue
     */
    createValue: function(groupId, newValue) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=Article action=createPropertyValue}',
            method: 'POST',
            params: {
                value: newValue,
                groupId: groupId
            },
            success: function(operation, opts) {
                var response = Ext.decode(operation.responseText);

                if (response.success == false) {
                    Shopware.Notification.createGrowlMessage('', response.message);
                } else {
                    me.addValue(groupId, response.data);
                }
            }
        });
    },

    /**
     * Adds an value to the grid store.
     *
     * @param { Number } groupId
     * @param { String } newValue
     */
    addValue: function(groupId, newValue) {
        var me = this;
        var store = me.propertyGrid.getStore();

        store.each(function(record) {
            if (record.get('id') == groupId) {
                var values = record.get('value');
                if (!Ext.isArray(values)) {
                    values = [];
                }

                if (!me.valueExist(values, newValue)) {
                    values.push(newValue);
                    record.set('value', values);
                    me.propertyGrid.reconfigure(store);
                }
                return false;
            }
        });
    },

    /**
     * Checks if an value was already assigned to the grid.
     *
     * @param { Array } values
     * @param { String } newValue
     * @returns { Boolean }
     */
    valueExist: function(values, newValue) {
        var exist = false;

        Ext.each(values, function(value) {
            if (value.id == newValue.id) {
                exist = true;
                return false;
            }
        });

        return exist;
    },

    /**
     * Creates the grid component which displays the associated property values.
     *
     * @returns { Ext.grid.Panel }
     */
    createGrid: function() {
        var me = this;

        me.propertyGrid = Ext.create('Ext.grid.Panel', {
            name: 'property-grid',
            cls: Ext.baseCSSPrefix + 'free-standing-grid ' + Ext.baseCSSPrefix + 'article-properties-grid',
            title: me.snippets.title,
            hidden: true,
            flex: 1,
            autoScroll: true,
            listeners: {
                scope: me,
                itemclick: Ext.bind(me.onDeleteElement, me)
            },
            columns: [
                {
                    header: me.snippets.name,
                    dataIndex: 'name',
                    width: 155
                } , {
                    header: me.snippets.value,
                    dataIndex: 'value',
                    flex: 1,
                    renderer: Ext.bind(me.valueRenderer, me)
                }
            ]
        });

        return me.propertyGrid;
    },

    /**
     * Renders the values and wraps them into a "<ul>" list.
     *
     * @param { Array } values
     * @param { String } style
     * @param { Ext.data.Model } model
     * @returns { String }
     */
    valueRenderer: function(values, style, model) {
        var me = this,
            result = [ Ext.String.format('<ul class="[0]item-bubble-list">', Ext.baseCSSPrefix) ];

        Ext.each(values, function(value) {
            if(!value) {
                return;
            }

            result.push(Ext.String.format(
                '<li><span class="[0]item-bubble" data-value-id="[1]" data-row-id="[2]">[3]<span class="cross-btn">x</span></span></li>',
                Ext.baseCSSPrefix, value.id, model.data.id, value.value
            ));
        });
        result.push('</ul>');

        return result.join(' ');
    },

    /**
     * Event listener method which will be called when all necessary stores of the product module are loaded. The method
     * will also be used to change the product in the module using the split view functionality.
     *
     * @param { Ext.data.Model } article
     * @param { Array } stores
     */
    onStoresLoaded: function(article, stores) {
        var me = this;

        me.article = article;
        me.store = Ext.data.StoreManager.lookup('Property');
        me.propertyGrid.reconfigure(me.store);

        me.propertySetStore = Ext.create('Shopware.store.Search', {
            fields: ['id', 'name'],
            pageSize: 10,
            configure: function() {
                return { entity: 'Shopware\\Models\\Property\\Group' };
            }
        });

        me.propertyGroupStore = Ext.create('Shopware.store.Search', {
            fields: ['id', 'name'],
            pageSize: 10,
            configure: function() {
                return { entity: 'Shopware\\Models\\Property\\Option' };
            }
        });
        me.propertyValueStore = Ext.create('Shopware.store.Search', {
            fields: [
                { name: 'id', type: 'string' },
                { name: 'name', type: 'string', mapping: 'value' }
            ],
            pageSize: 10,
            configure: function() {
                return { entity: 'Shopware\\Models\\Property\\Value' };
            }
        });


        if (me.article.get('filterGroupId')) {
            me.propertySetStore.load({
                id: me.article.get('filterGroupId')
            });
        }

        me.groupComboBox.bindStore(me.propertyGroupStore);
        me.setComboBox.bindStore(me.propertySetStore);
        me.valueComboBox.bindStore(me.propertyValueStore);

        me.loadRecord(me.article);
    },

    /**
     * Event listener method which will be called when the user interacts with a property value in the grid. The method
     * removes the property value from the grid and therefore from product.
     *
     * @param { Ext.EventImpl } event
     * @param { Ext.dom.Element } item
     */
    onDeleteElement: function(comp, record, dom, index, event) {
        var me = this,
            store = me.store,
            valueId, rowId, values,
            element = Ext.get(event.target),
            notFound = true;

        if (element.hasCls('cross-btn')) {
            notFound = false;
        }

        if (element.hasCls('x-item-bubble')) {
            notFound = false;
        }

        if(notFound) {
            return;
        }

        if (element.hasCls('cross-btn')) {
            element = element.parent();
        }

        valueId = ~~(1 * element.getAttribute('data-value-id'));
        rowId = ~~(1 * element.getAttribute('data-row-id'));

        if(!valueId) {
            for(var i in element.dom.attributes) {
                var attr = element.dom.attributes[i];
                if(attr.name == 'data-value-id') {
                    valueId = parseInt(attr.value, 10);
                    break;
                }
            }
        }

        if(!rowId) {
            for(var i in element.dom.attributes) {
                var attr = element.dom.attributes[i];
                if(attr.name == 'data-row-id') {
                    rowId = parseInt(attr.value, 10);
                    break;
                }
            }
        }

        record = store.findRecord('id', rowId, 0, false, false, true );
        values = record.get('value');

        var newValues = [];
        Ext.each(values, function(value) {
            if (~~(1 * value.id) !== valueId) {
                newValues.push(value);
            }
        });

        record.set('value', newValues);
        me.propertyGrid.reconfigure(me.store);
    }
});
//{/block}
