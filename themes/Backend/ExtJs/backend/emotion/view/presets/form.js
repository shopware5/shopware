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

/**
 * Shopware Application
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
//{namespace name=backend/emotion/presets/presets}
//{block name="backend/emotion/presets/form"}
Ext.define('Shopware.apps.Emotion.view.presets.Form', {
    extend: 'Enlight.app.Window',
    alias: 'widget.emotion-presets-form-window',

    layout: 'fit',
    height: 475,
    width: 510,

    modal: true,
    title: '{s name=form_window/title}{/s}',

    initComponent: function() {
        var me = this;

        me.items = me.buildItems();
        me.dockedItems = me.buildDockedItems();

        me.callParent(arguments);
    },

    buildItems: function() {
        var me = this;

        me.form = Ext.create('Ext.form.Panel', {
            bodyPadding: '20 30 10 30',
            autoScroll: true,
            fieldDefaults: {
                anchor: '100%',
                labelWidth: 130
            },
            items: me.buildFormElements()
        });

        return [me.form];
    },

    buildFormElements: function() {
        var me = this;

        me.hiddenField = Ext.create('Ext.form.field.Hidden', {
            name: 'emotionId'
        });

        me.displayField = Ext.create('Ext.form.field.Display', {
            fieldStyle: 'font-style: italic; font-size: 11px; color: #999999',
            value: '{s name=form_window/info_text}{/s}'
        });

        me.radiogroup = Ext.create('Ext.form.RadioGroup', {
            columns: 1,
            hidden: true,
            vertical: true,
            margin: '10 0 0 0',
            items: [
                { boxLabel: '{s name=form_window/boxlabel_new}{/s}', name: 'save', checked: true, inputValue: 1, itemId: 'create' },
                { boxLabel: '{s name=form_window/boxlabel_override}{/s}', name: 'save', inputValue: 2, itemId: 'update' }
            ],
            listeners: {
                change: function(field, newValue) {
                    var combo = me.down('#presetselection'),
                        value = newValue.save;

                    combo.clearValue();
                    me.mediafield.reset();
                    me.down('textfield[name=name]').reset();
                    me.down('textarea[name=description]').reset();
                    combo.setDisabled(value !== 2);
                    combo.setVisible(value === 2);
                }
            }
        });

        me.presetSelection = Ext.create('Ext.form.field.ComboBox', {
            itemId: 'presetselection',
            hidden: true,
            disabled: true,
            fieldLabel: '{s name=form_window/select_preset}{/s}',
            allowBlank: false,
            valueField: 'id',
            displayField: 'label',
            store: Ext.create('Shopware.apps.Emotion.store.Presets', {
                autoLoad: true,
                filters: [
                    function(record) {
                        return record.get('custom');
                    }
                ],
                listeners: {
                    load: function(store) {
                        if (store.getCount() > 0) {
                            me.radiogroup.setVisible(true);
                        }
                    }
                }
            }),
            queryMode: 'local',
            triggerAction: 'all',
            forceSelection: true,
            name: 'id',
            listeners: {
                scope: me,
                select: function(combo, records) {
                    var record = records[0],
                        path = record.get('thumbnail');

                    me.mediafield.setValue(path);
                    me.down('textfield[name=name]').setValue(record.get('name'));
                    me.down('textarea[name=description]').setValue(record.get('description'));
                }
            }
        });

        me.mediafield = me.buildMediaSelectionField();

        return [
            me.hiddenField,
            me.displayField,
            me.radiogroup,
            me.presetSelection,
            {
                xtype: 'textfield',
                allowBlank: false,
                fieldLabel: '{s name=form_window/name}{/s}',
                name: 'name'
            },
            {
                xtype: 'textarea',
                fieldLabel: '{s name=form_window/description}{/s}',
                name: 'description'
            },
            me.mediafield
        ];
    },

    buildMediaSelectionField: function() {
        return Ext.create('Shopware.form.field.Media', {
            valueField: 'virtualPath',
            name: 'thumbnail',
            fieldLabel: '{s name=form_window/preview_image}{/s}',
            requestMediaData: function(value) {
                var me = this, params = {};

                if (!value) {
                    me.updatePreview(null);
                    return;
                }

                if (Ext.form.VTypes.url(value) || value.indexOf('data:image') !== -1) {
                    me.updatePreview(value);
                    return;
                }

                params['path'] = value;

                Ext.Ajax.request({
                    url: '{url controller=mediaManager action=getMedia}',
                    method: 'POST',
                    params: params,
                    success: function(response) {
                        var operation = Ext.decode(response.responseText);

                        if (operation.success == true) {
                            me.record = Ext.create('Shopware.apps.Base.model.Media', operation.data);
                            me.mediaId = me.record.get('id');
                            me.path = me.record.get('path');
                            me.updatePreview(me.path);
                        }
                    }
                });
            },
            createPreviewContainer: function() {
                var me = this;

                me.previewContainer = Ext.create('Ext.container.Container', {
                    flex: 1,
                    margin: '0 0 0 25',
                    items: [ me.createPreview() ]
                });
                return me.previewContainer;
            },
            createButtonContainer: function() {
                var me = this;

                me.buttonContainer = Ext.create('Ext.container.Container', {
                    width: 160,
                    layout: {
                        type: 'vbox',
                        align: 'stretch'
                    },
                    items: [
                        me.createSelectButton(),
                        me.createResetButton()
                    ]
                });
                return me.buttonContainer;
            },
            createPreview: function() {
                var me = this, value;

                if (!Ext.isDefined(me.value)) {
                    value = me.noMedia;
                } else if(Ext.form.VTypes.url(me.value) || me.value.indexOf('data:image') !== -1) {
                    value = me.value;
                } else {
                    value = me.mediaPath + me.value;
                }

                me.preview = Ext.create('Ext.Img', {
                    src: value,
                    height: 100,
                    width: 97,
                    maxHeight: 100,
                    padding: 5,
                    margin: 5,
                    style: "border-radius: 6px; border: 1px solid #c4c4c4;"
                });

                return me.preview;
            }
        });
    },

    buildDockedItems: function() {
        var me = this;

        me.bottomBar = Ext.create('Ext.Toolbar',  {
            ui: 'shopware-ui',
            dock: 'bottom',
            items: [
                '->',
                {
                    text: '{s name="form_window/title"}{/s}',
                    cls: 'primary',
                    handler: function() {
                        me.fireEvent('savepreset', me);
                    }
                }
            ]
        });

        return [me.bottomBar];
    }
});
//{/block}
