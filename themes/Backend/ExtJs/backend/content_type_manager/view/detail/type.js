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

// {namespace name="backend/content_type_manager/main"}
// {block name="backend/content_type_manager/view/detail/type"}
Ext.define('Shopware.apps.ContentTypeManager.view.detail.Type', {
    extend: 'Shopware.model.Container',
    alias: 'widget.content-type-manager-detail-container',
    padding: 10,

    constructor: function(opts) {
        this.record = opts.record;

        this.callParent(arguments);
    },

    /**
     * configure the fields
     * @returns { Object }
     */
    configure: function () {
        return {
            controller: 'ContentTypeManager',
            splitFields: false,
            fieldSets: [
                {
                    title: '{s name="type/title"}{/s}',
                    fields: {
                        name: {
                            allowBlank: false,
                            fieldLabel: '{s name="type/name"}{/s}',
                            supportText: this.labelSupportText(this.record),
                            labelWidth: 150,
                        },
                        menuIcon: {
                            allowBlank: false,
                            fieldLabel: '{s name="type/menuIcon"}{/s}',
                            supportText: '{s name="type/menuIconSupportText"}{/s}',
                            labelWidth: 150
                        }
                    }
                },
                {
                    title: '{s name="detail/view"}{/s}',
                    fields: {
                        showInFrontend: {
                            fieldLabel: '{s name="view/showInFrontend"}{/s}',
                            inputValue: true,
                            uncheckedValue: false,
                            xtype: 'checkbox',
                            labelWidth: 150,
                            listeners: {
                                change: this.frontendVisibilityListener,
                                scope: this
                            }
                        },
                        viewTitleFieldName: {
                            fieldLabel: '{s name="view/titleField"}{/s}',
                            xtype: 'combobox',
                            valueField: 'name',
                            displayField: 'label',
                            queryMode: 'local',
                            labelWidth: 150,
                            forceSelection: true,
                            listeners: {
                                expand: this.resetFilters
                            }
                        },
                        viewDescriptionFieldName: {
                            fieldLabel: '{s name="view/descriptionField"}{/s}',
                            xtype: 'combobox',
                            valueField: 'name',
                            displayField: 'label',
                            queryMode: 'local',
                            labelWidth: 150,
                            forceSelection: true,
                            listeners: {
                                expand: this.resetFilters
                            }
                        },
                        viewImageFieldName: {
                            fieldLabel: '{s name="view/imageField"}{/s}',
                            xtype: 'combobox',
                            valueField: 'name',
                            displayField: 'label',
                            queryMode: 'local',
                            labelWidth: 150,
                            forceSelection: true,
                            listeners: {
                                expand: this.resetFilters
                            }
                        },
                    }
                },
                {
                    title: '{s name="detail/seo"}{/s}',
                    fields: {
                        viewMetaTitleFieldName: {
                            fieldLabel: '{s name="seo/metaTitleField"}{/s}',
                            xtype: 'combobox',
                            valueField: 'name',
                            displayField: 'label',
                            queryMode: 'local',
                            labelWidth: 150,
                            supportText: '{s name="seo/metaTitleFieldHelpText"}{/s}',
                            listeners: {
                                change: this.comboboxResetListener,
                                expand: this.resetFilters
                            }
                        },
                        viewMetaDescriptionFieldName: {
                            fieldLabel: '{s name="seo/metaDescriptionField"}{/s}',
                            xtype: 'combobox',
                            valueField: 'name',
                            displayField: 'label',
                            queryMode: 'local',
                            labelWidth: 150,
                            supportText: '{s name="seo/metaDescriptionFieldHelpText"}{/s}',
                            listeners: {
                                change: this.comboboxResetListener,
                                expand: this.resetFilters
                            }
                        },
                        seoUrlTemplate: {
                            fieldLabel: '{s name="seo/seoUrlTemplate"}{/s}',
                            xtype: 'ace-editor',
                            labelWidth: 150,
                            height: 20
                        },
                        seoRobots: {
                            fieldLabel: '{s name="seo/seoRobots"}{/s}',
                            xtype: 'combobox',
                            displayField: 'name',
                            valueField: 'value',
                            labelWidth: 150,
                            store: Ext.create('Shopware.apps.ContentTypeManager.store.Robots')
                        }
                    }
                }
            ]
        };
    },

    initComponent: function() {
        var me = this;
        this.callParent(arguments);

        var fields = [
            this.down('[name="viewTitleFieldName"]'),
            this.down('[name="viewDescriptionFieldName"]'),
            this.down('[name="viewImageFieldName"]'),
            this.down('[name="viewMetaTitleFieldName"]'),
            this.down('[name="viewMetaDescriptionFieldName"]'),
        ];

        fields.forEach(function (field) {
            field.bindStore(me.record.getFields());
        });
    },

    createItems: function() {
        var me = this,
            items = this.callParent(arguments);

        this.seoUrlGrid = Ext.create('Ext.grid.Panel', {
            disabled: !me.record.get('showInFrontend'),
            plugins: [
                Ext.create('Ext.grid.plugin.RowEditing', {
                    clicksToMoveEditor: 1,
                    autoCancel: true
                })
            ],
            columns: [
                {
                    header: '{s name="type/seoUrls/shop"}{/s}',
                    dataIndex: 'name',
                    flex: 1
                },
                {
                    header: '{s name="type/seoUrls/url"}{/s}',
                    dataIndex: 'url',
                    flex: 1,
                    editor: {
                        readOnly: true,
                        selectOnFocus: true
                    },
                    renderer: function (value) {
                        if (!me.record.get('showInFrontend')) {
                            return '';
                        }

                        return value
                    }
                }
            ],
            store: this.record.getUrls()
        });

        var element = Ext.create('Ext.form.FieldContainer', {
            fieldLabel: '{s name="type/seoUrls"}{/s}',
            labelWidth: 150,
            items: [
                this.seoUrlGrid
            ]
        });

        if (this.record.getUrls().count() === 0) {
            var store = Ext.create('Shopware.apps.Base.store.ShopLanguage');

            store.load(function () {
                store.each(function (record) {
                    me.seoUrlGrid.store.add({
                        name: record.get('name')
                    });
                });
            });
        }

        items[2].items.items[0].insert(0, element);

        return items;
    },

    getModelName: function () {
        return '{s name="type/title"}{/s}';
    },

    frontendVisibilityListener: function (combobox, value) {
        var fields = [
            this.down('[name="viewTitleFieldName"]'),
            this.down('[name="viewDescriptionFieldName"]'),
            this.down('[name="viewImageFieldName"]')
        ];

        fields.forEach(function (field) {
            field.allowBlank = !value;
            field.validate();
        });

        this.seoUrlGrid[value ? 'enable' : 'disable']();
        this.record.set('showInFrontend', value);
        this.seoUrlGrid.getView().refresh();
    },

    labelSupportText: function (record) {
        if (!record.get('showInFrontend') || !record.get('controllerName')) {
            return '';
        }

        return '{s name="link_to_frontend"}{/s}' + record.get('controllerName');
    },

    comboboxResetListener: function () {
        if (!this.getValue() || this.getValue().length === 0) {
            this.setValue('');
        }
    },

    resetFilters: function () {
        this.store.clearFilter();
    }
});
// {/block}
