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
 * @package    UserManager
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/emotion/view/detail}
Ext.define('Shopware.apps.Emotion.view.components.Base', {
    extend: 'Ext.form.Panel',
    bodyBorder: 0,
    layout: 'anchor',
    cls: 'shopware-form',
    autoScroll: true,
    modal: true,
    margin: 4,
    border: 0,
    bodyPadding: 26,
    alias: 'widget.emotion-components-base',
    defaults: {
        anchor: '100%'
    },

    initComponent: function() {
        var me = this;

        // If we're having items already, don't override them
        if(!me.items) {
            me.items = [];
        }

        // Holder fielset which contains the element settings
        me.elementFieldset = Ext.create('Ext.form.FieldSet', {
            title: '{s name=base/fieldset_title}Element settings{/s}',
            defaults: me.defaults,
            items: me.createFormElements()
        });

        if(me.getSettings('component', true).description.length) {
            me.items.push(me.createDescriptionContainer());
        }
        me.items.push(me.elementFieldset);
        me.callParent(arguments);
        me.loadElementData(me.getSettings('record').get('data'));
    },

    loadElementData: function(data) {
        var me = this, fields = [];
        Ext.each(data, function(item) {
            var field = me.down('field[name='+ item.key +']');
            if (field !== null) {
                try {
                    field.setValue(item.value);
                } catch(e) { }
            }
        });
    },


    afterRender: function() {
        var me = this;
        me.callParent(arguments);

        // We need to force the first call to set the initial value of the display field
        me.onUpdateSizeDisplay();
    },

    createFormElements: function() {
        var me = this, items = [], store, name, fieldLabel, snippet, supportText;

        Ext.each(me.getSettings('fields', true), function(item) {
            name = item.get('name');
            fieldLabel = item.get('fieldLabel');
            supportText = item.get('supportText');

            if (me.snippets && me.snippets[name]) {
                snippet = me.snippets[name];

                if (Ext.isObject(snippet)) {
                    if (snippet.hasOwnProperty('supportText')) supportText = snippet.supportText;
                    if (snippet.hasOwnProperty('fieldLabel')) fieldLabel = snippet.fieldLabel;
                } else {
                    fieldLabel = snippet;
                }
            }
            
            store = null;
            if (item.get('store')) {
                store = Ext.create(item.get('store'));
            }

            items.push({
                xtype: item.get('xType'),
                helpText: item.get('helpText') || '',
                fieldLabel: fieldLabel || '',
                fieldId: item.get('id'),
                valueType: item.get('valueType'),
                queryMode: 'remote',
                name: item.get('name') || '',
                store: store,
                displayField: item.get('displayField'),
                valueField: item.get('valueField'),
                checkedValue: true,
                uncheckedValue: false,
                supportText: supportText || '',
                allowBlank: (item.get('allowBlank') ? true : false),
                value: item.get('defaultValue') || ''
            });
        });

        items.push(me.createSizingFields());
        return items;
    },

    /**
     * @private
     * @return [object] Ext.container.Container which contains the sizing fields
     */
    createSizingFields: function() {
        var me = this, grid = me.getSettings('grid', true), record = me.getSettings('record', true),
            colStoreData, colStore, rowStoreData, rowStore,
            cols = record.endCol - record.startCol + 1,
            rows = record.endRow - record.startRow + 1;

        // Create column store
        colStoreData = [];
        for(var i = 1; grid.cols >= i; i++) {
            colStoreData.push({ display: i + ' {s name=base/columns}Column(s){/s}', value: i });
        }
        colStore = Ext.create('Ext.data.Store', {
            fields: [ 'display', 'value' ],
            data: colStoreData
        });

        // Create row store
        rowStoreData = [];
        for(var i = 1; grid.rows >= i; i++) {
            rowStoreData.push({ display: i + ' {s name=base/rows}Row(s){/s}', value: i });
        }
        rowStore = Ext.create('Ext.data.Store', {
            fields: [ 'display', 'value' ],
            data: rowStoreData
        });

        me.colComboBox = Ext.create('Ext.form.field.ComboBox', {
            store: colStore,
            fieldLabel: '{s name=base/width}Width{/s}',
            disabled: true,
            valueField: 'value',
            displayField: 'display',
            value: cols || 1,
            listeners: {
                scope: me,
                change: me.onUpdateSizeDisplay
            }
        });

        me.rowComboBox = Ext.create('Ext.form.field.ComboBox', {
            store: rowStore,
            fieldLabel: '{s name=base/height}Height{/s}',
            valueField: 'value',
            disabled: true,
            displayField: 'display',
            value: rows || 1,
            listeners: {
                scope: me,
                change: me.onUpdateSizeDisplay
            }
        });

        me.displayField = Ext.create('Ext.form.field.Display', {
            fieldLabel: '{s name=base/height_frontend}Frontend height{/s}',
            labelWidth: 135,
            supportText: '{s name=base/height_frontend_info}Width x Height in Pixel{/s}'
        });

        return Ext.create('Ext.container.Container', {
            layout: 'hbox',
            items: [{
                xtype: 'container',
                defaults: me.defaults,
                layout: 'anchor',
                flex: 1,
                items: [ me.colComboBox, me.rowComboBox ]
            }, {
                xtype: 'container',
                flex: 1,
                margin: '0 0 0 15',
                defaults: me.defaults,
                items:  [ me.displayField ]
            }]
        })
    },

    /**
     * Creates a fieldset with the element description.
     *
     * @private
     * @return [object] Ext.form.FielSet
     */
    createDescriptionContainer: function() {
        var me = this, component = me.getSettings('component', true);

        return Ext.create('Ext.form.FieldSet', {
            title: '{s name=base/element_description}Element description{/s}',
            items: [{
                xtype: 'container',
                html: component.description
            }]
        });
    },

    /**
     * Helper method which returns the settings or if
     * the type parameter is set, the part of the settings
     * object.
     *
     * @public
     * @param [string] type - Type of the settings (fields, component, grid)
     * @param [boolean] data - Should the method return the data object
     * @return [object|boolean] settings or false
     */
    getSettings: function(type, data) {
        if(type) {
            var settings = this.settings[type];
            if(data) {
                return (!settings) ? false : (this.settings[type].data.items) ? this.settings[type].data.items : this.settings[type].data;
            }
            return this.settings[type];
        }
        return this.settings;
    },

    /**
     * Updates the displayed size of the element
     * in the frontend.
     *
     * @public
     * @return void
     */
    onUpdateSizeDisplay: function() {
        var me = this,
            cols = ~~(1 * me.colComboBox.getValue()),
            rows = ~~(1 * me.rowComboBox.getValue()),
            grid = me.getSettings('grid', true),
            rowHeight = grid.cellHeight,
            colWidth = grid.containerWidth / grid.cols,
            field = me.displayField,
            offset = 10,
            width = (cols * colWidth) - offset + '',
            height = (rows * rowHeight) - offset + '';

        width = width.replace('.', ',');
        height = height.replace('.', ',');
        width += 'px';
        height += 'px';
        field.setValue(width + ' x '  + height);
    }
});