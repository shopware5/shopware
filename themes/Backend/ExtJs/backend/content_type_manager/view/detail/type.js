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
                    }
                },
                {
                    title: '{s name="detail/view"}{/s}',
                    fields: {
                        'showInFrontend': {
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
                        'viewTitleFieldName': {
                            fieldLabel: '{s name="view/titleField"}{/s}',
                            xtype: 'combobox',
                            valueField: 'name',
                            displayField: 'label',
                            queryMode: 'local',
                            labelWidth: 150,
                        },
                        'viewDescriptionFieldName': {
                            fieldLabel: '{s name="view/descriptionField"}{/s}',
                            xtype: 'combobox',
                            valueField: 'name',
                            displayField: 'label',
                            queryMode: 'local',
                            labelWidth: 150,
                        },
                        'viewImageFieldName': {
                            fieldLabel: '{s name="view/imageField"}{/s}',
                            xtype: 'combobox',
                            valueField: 'name',
                            displayField: 'label',
                            queryMode: 'local',
                            labelWidth: 150,
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
        ];

        fields.forEach(function (field) {
            field.bindStore(me.record.getFields());
        });
    },

    getModelName: function () {
        return '{s name="type/title"}{/s}';
    },

    frontendVisibilityListener: function (combobox, value) {
        var fields = [
            this.down('[name="viewTitleFieldName"]'),
            this.down('[name="viewDescriptionFieldName"]'),
            this.down('[name="viewImageFieldName"]'),
        ];

        fields.forEach(function (field) {
            field.allowBlank = !value;
            field.validate();
        });
    },

    labelSupportText: function (record) {
        if (!record.get('showInFrontend') || !record.get('internalName')) {
            return '';
        }

        return '{s name="link_to_frontend"}{/s}' + record.get('internalName');
    }
});
// {/block}
