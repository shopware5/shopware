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
 * @package     Emotion
 * @subpackage  View
 * @version     $Id$
 * @author      shopware AG
 */

//{namespace name=backend/emotion/view/detail}
//{block name="backend/emotion/view/detail/layout"}
Ext.define('Shopware.apps.Emotion.view.detail.Layout', {

    extend: 'Ext.form.Panel',
    alias: 'widget.emotion-detail-layout',

    title: '{s name="title/layout_tab"}{/s}',

    bodyPadding: 20,
    border: 0,
    bodyBorder: 0,
    autoScroll: true,
    style: 'background: #f0f2f4',

    defaults: {
        labelWidth: 120,
        anchor: '100%',
        eventBuffer: 600
    },

    snippets: {
        fieldSets: {
            layoutLabel: '{s name="settings/layoutFieldset/title"}{/s}',
            gridLabel: '{s name="settings/gridFieldset/title"}{/s}'
        },
        fields: {
            fluidModeLabel: '{s name="grids/settings/fluid_effect"}{/s}',
            resizeModeLabel: '{s name="grids/settings/resize_effect"}{/s}',
            rowsModeLabel: '{s name="grids/settings/rows_effect"}{/s}',
            templateLabel: '{s name="settings/fieldset/select_template"}{/s}',
            templateEmptyText: '{s name="settings/fieldset/select_template_empty"}{/s}',
            modeLabel: '{s name="grids/settings/mode"}{/s}',
            fullscreenLabel: '{s name="settings/label/fullscreen"}{/s}',
            gridColsLabel: '{s name="settings/grid/colsLabel"}{/s}',
            gridSpacingLabel: '{s name="settings/grid/spacingLabel"}{/s}',
            gridHeightLabel: '{s name="settings/grid/rowHeightLabel"}{/s}'
        },
        support: {
            fluidMode: '{s name="grid/settings/support/fluid_effect"}{/s}',
            resizeMode: '{s name="grid/settings/support/resize_effect"}{/s}',
            rowsMode: '{s name="grid/settings/support/rows_effect"}{/s}'
        }
    },

    initComponent: function() {
        var me = this;

        me.layoutFieldset = me.createLayoutFieldset();
        me.gridFieldset = me.createGridFieldset();

        me.items = [
            me.layoutFieldset,
            me.gridFieldset
        ];

        me.addEvents(
            'changeMode',
            'changeColumns',
            'updateGridByField'
        );

        me.callParent(arguments);
    },

    onModeChange: function (modeField, mode) {
        var me = this;

        me.fireEvent('changeMode', me.emotion, mode);

        if (mode === 'rows') {
            me.cellHeightField.setValue(240);
            me.disableCellHeightField(true);
        }

        if (mode === 'fluid' || mode === 'resize') {
            me.cellHeightField.setValue(185);
            me.disableCellHeightField(false);
        }
    },

    createLayoutFieldset: function() {
        var me = this,
            layoutLabelWidth = 80;

        me.tplStore = Ext.create('Shopware.apps.Emotion.store.Templates').load();

        me.tplComboBox = Ext.create('Shopware.form.field.PagingComboBox', {
            fieldLabel: me.snippets.fields.templateLabel,
            name: 'templateId',
            valueField: 'id',
            displayField: 'name',
            queryMode: 'remote',
            store: me.tplStore,
            emptyText: me.snippets.fields.templateEmptyText,
            labelWidth: layoutLabelWidth
        });

        me.modeSelectionTpl = new Ext.XTemplate(
            '{literal}<tpl for=".">',
                '<div class="x-layout-mode-selection-item x-boundlist-item">',
                    '<div class="x-selection-item-icon icon--{value}"></div>',
                    '<div class="x-selection-item-label">{display}</div>',
                    '<div class="x-selection-item-description">{supportText}</div>',
                '</div>',
            '</tpl>{/literal}'
        );

        me.responsiveModeField = Ext.create('Ext.form.field.ComboBox', {
            name: 'mode',
            fieldLabel: me.snippets.fields.modeLabel,
            store: me.createResponsiveModeStore(),
            queryMode: 'local',
            displayField: 'display',
            valueField: 'value',
            allowBlank: false,
            editable: false,
            forceSelection: true,
            labelWidth: layoutLabelWidth,
            tpl: me.modeSelectionTpl,
            listeners: {
                'change': {
                    scope: me,
                    fn: me.onModeChange
                }
            }
        });

        me.fullscreenField = Ext.create('Ext.form.field.Checkbox', {
            name: 'fullscreen',
            boxLabel: me.snippets.fields.fullscreenLabel,
            inputValue: 1,
            uncheckedValue: 0,
            hideEmptyLabel: false,
            margin: '10 0 5 0',
            labelWidth: layoutLabelWidth
        });

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.fieldSets.layoutLabel,
            defaults: me.defaults,
            items: [
                me.responsiveModeField,
                me.tplComboBox,
                me.fullscreenField
            ]
        });
    },

    createGridFieldset: function() {
        var me = this;

        me.colsField = Ext.create('Ext.form.field.Number', {
            name: 'cols',
            fieldLabel: me.snippets.fields.gridColsLabel,
            minValue: 1,
            maxValue: 12,
            step: 1,
            allowBlank: false,
            labelWidth: me.defaults.labelWidth,
            listeners: {
                scope: me,
                buffer: me.defaults.eventBuffer,
                change: function(field, value, oldValue) {
                    if (Ext.isDefined(oldValue)) {
                        me.fireEvent('changeColumns', me.emotion, value, field);
                    }
                }
            }
        });

        me.spacingField = Ext.create('Ext.form.field.Number', {
            name: 'cellSpacing',
            fieldLabel: me.snippets.fields.gridSpacingLabel,
            minValue: 0,
            step: 1,
            allowBlank: false,
            labelWidth: me.defaults.labelWidth,
            listeners: {
                scope: me,
                buffer: me.defaults.eventBuffer,
                change: function(field, value, oldValue) {
                    if (Ext.isDefined(oldValue)) {
                        me.fireEvent('updateGridByField', me.emotion, value, field);
                    }
                }
            }
        });

        me.cellHeightField = Ext.create('Ext.form.field.Number', {
            name: 'cellHeight',
            fieldLabel: me.snippets.fields.gridHeightLabel,
            minValue: 10,
            step: 1,
            allowBlank: false,
            labelWidth: me.defaults.labelWidth,
            listeners: {
                scope: me,
                buffer: me.defaults.eventBuffer,
                change: function(field, value, oldValue) {
                    if (Ext.isDefined(oldValue)) {
                        me.fireEvent('updateGridByField', me.emotion, value, field);
                    }
                }
            }
        });

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.fieldSets.gridLabel,
            defaults: me.defaults,
            items: [
                me.colsField,
                me.spacingField,
                me.cellHeightField
            ]
        });
    },

    createResponsiveModeStore: function() {
        var me = this;

        return me.responsiveModeStore = Ext.create('Ext.data.Store', {
            fields: [ 'value', 'display', 'supportText' ],
            data: [{
                'value': 'fluid',
                'display': me.snippets.fields.fluidModeLabel,
                'supportText': me.snippets.support.fluidMode
            }, {
                'value': 'resize',
                'display': me.snippets.fields.resizeModeLabel,
                'supportText': me.snippets.support.resizeMode
            }, {
                'value': 'rows',
                'display': me.snippets.fields.rowsModeLabel,
                'supportText': me.snippets.support.rowsMode
            }]
        });
    },

    disableCellHeightField: function(disable) {
        var me = this;

        if (!Ext.isDefined(disable)) {
            disable = true;
        }

        me.cellHeightField.setReadOnly(disable);
        me.cellHeightField[disable ? 'addCls' : 'removeCls']('x-form-readonly');
        me.cellHeightField[disable ? 'addCls' : 'removeCls']('x-item-disabled');
    }
});
//{/block}
