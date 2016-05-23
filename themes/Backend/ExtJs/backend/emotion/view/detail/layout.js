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
            deviceLabel: '{s name="settings/deviceFieldset/title"}{/s}',
            gridLabel: '{s name="settings/gridFieldset/title"}{/s}'
        },
        fields: {
            fluidModeLabel: '{s name="grids/settings/fluid_effect"}{/s}',
            resizeModeLabel: '{s name="grids/settings/resize_effect"}{/s}',
            templateLabel: '{s name="settings/fieldset/select_template"}{/s}',
            templateEmptyText: '{s name="settings/fieldset/select_template_empty"}{/s}',
            modeLabel: '{s name="grids/settings/mode"}{/s}',
            fullscreenLabel: '{s name="settings/label/fullscreen"}{/s}',
            deviceDesktopLabel: '{s name="settings/device/desktop"}{/s}',
            deviceTabletLandscapeLabel: '{s name="settings/device/tabletLandscape"}{/s}',
            deviceTabletPortraitLabel: '{s name="settings/device/tabletPortrait"}{/s}',
            deviceMobileLandscapeLabel: '{s name="settings/device/mobileLandscape"}{/s}',
            deviceMobilePortraitLabel: '{s name="settings/device/mobilePortrait"}{/s}',
            gridColsLabel: '{s name="settings/grid/colsLabel"}{/s}',
            gridSpacingLabel: '{s name="settings/grid/spacingLabel"}{/s}',
            gridHeightLabel: '{s name="settings/grid/rowHeightLabel"}{/s}'
        },
        support: {
            fluidMode: '{s name="grid/settings/support/fluid_effect"}{/s}',
            resizeMode: '{s name="grid/settings/support/resize_effect"}{/s}'
        },
        alert: {
            deviceWarningTitle: '{s name="settings/device/warning_title"}{/s}',
            deviceWarningText: '{s name="settings/device/warning_text"}{/s}'
        }
    },

    initComponent: function() {
        var me = this;

        me.layoutFieldset = me.createLayoutFieldset();
        me.gridFieldset = me.createGridFieldset();
        me.deviceFieldset = me.createDeviceFieldset();

        me.items = [
            me.layoutFieldset,
            me.gridFieldset,
            me.deviceFieldset
        ];

        me.addEvents(
            'changeMode',
            'changeColumns',
            'updateGridByField'
        );

        me.callParent(arguments);
    },

    createLayoutFieldset: function() {
        var me = this;

        me.tplComboBox = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: me.snippets.fields.templateLabel,
            name: 'templateId',
            valueField: 'id',
            displayField: 'name',
            queryMode: 'remote',
            store: Ext.create('Shopware.apps.Emotion.store.Templates').load(),
            emptyText: me.snippets.fields.templateEmptyText,
            labelWidth: me.defaults.labelWidth
        });

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
            labelWidth: me.defaults.labelWidth,
            tpl: Ext.create('Ext.XTemplate',
                '{literal}<tpl for=".">',
                    '<div class="x-boundlist-item">',
                        '<h1>{display}</h1>{supportText}',
                    '</div>',
                '</tpl>{/literal}'
            ),
            listeners: {
                'change': {
                    scope: me,
                    fn: function(field, newValue) {
                        me.fireEvent('changeMode', me.emotion, newValue);
                    }
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
            labelWidth: me.defaults.labelWidth,
            listeners: {
                scope: me,
                change: function(field, value) {
                    // ToDo@PSC: Make it possible to show listing after fullscreen shopping world
                    //me.listingCheckbox.setVisible(!value);
                    //
                    //if (value) {
                    //    me.listingCheckbox.setValue(false);
                    //}
                }
            }
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
                change: function(field, value) {
                    me.fireEvent('changeColumns', me.emotion, value, field);
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
                change: function(field, value) {
                    me.fireEvent('updateGridByField', me.emotion, value, field);
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
                change: function(field, value) {
                    me.fireEvent('updateGridByField', me.emotion, value, field);
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

    createDeviceFieldset: function() {
        var me = this;

        me.deviceComboGroup = Ext.create('Ext.form.CheckboxGroup', {
            columns: 1,
            vertical: false,
            items: me.createDeviceData(),
            listeners: {
                scope: me,
                change: function(comp, newVal, oldVal) {
                    var values = comp.getValue();

                    if (!values.hasOwnProperty('device')) {
                        Ext.Msg.alert(me.snippets.alert.deviceWarningTitle, me.snippets.alert.deviceWarningText);
                        comp.setValue(oldVal);
                    }
                }
            }
        });

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.fieldSets.deviceLabel,
            defaults: me.defaults,
            items: [
                me.deviceComboGroup
            ]
        });
    },

    createDeviceData: function() {
        var me = this;

        return [{
            'inputValue': '0',
            'boxLabel': me.snippets.fields.deviceDesktopLabel,
            'checked': 1,
            'name': 'device'
        }, {
            'inputValue': '1',
            'boxLabel' : me.snippets.fields.deviceTabletLandscapeLabel,
            'checked': 1,
            'name': 'device'
        }, {
            'inputValue': '2',
            'boxLabel': me.snippets.fields.deviceTabletPortraitLabel,
            'checked': 1,
            'name': 'device'
        }, {
            'inputValue': '3',
            'boxLabel': me.snippets.fields.deviceMobileLandscapeLabel,
            'checked': 1,
            'name': 'device'
        }, {
            'inputValue': '4',
            'boxLabel': me.snippets.fields.deviceMobilePortraitLabel,
            'checked': 1,
            'name': 'device'
        }];
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
            }]
        });
    },

    setDevices: function() {
        var me = this,
            device = me.emotion.get('device') || '0,1,2,3,4';

        me.deviceComboGroup.setValue({
            'device': device.split(',')
        });
    }
});
//{/block}