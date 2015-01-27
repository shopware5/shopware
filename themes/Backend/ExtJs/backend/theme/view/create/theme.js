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

//{namespace name=backend/theme/main}

//{block name="backend/theme/view/create/theme"}

Ext.define('Shopware.apps.Theme.view.create.Theme', {

    extend: 'Shopware.model.Container',
    padding: 20,
    layout: 'anchor',

    configure: function() {
        var me = this;

        return {
            fieldSets: [
                {
                    title: '{s name=theme_data}Theme data{/s}',
                    padding: 10,
                    layout: 'fit',
                    fields: {
                        parentId: me.createExtendCombo,
                        template: {
                            fieldLabel: '{s name=name}Name{/s}',
                            allowBlank: false,
                            vtype: 'alpha',
                            supportText: '{s name=name_support}Source code name{/s}'
                        },
                        name: {
                            allowBlank: false,
                            fieldLabel: '{s name=short_description}Short description{/s}',
                            supportText: '{s name=short_description_support}Readable name, displayed in listing{/s}'
                        },
                        description: {
                            fieldLabel: '{s name=long_description}Long description{/s}',
                            xtype: 'textarea',
                            height: 120
                        },
                        author: '{s name=author}Author{/s}',
                        license: '{s name=license}License{/s}'
                    }
                }
            ]
        };
    },

    createExtendCombo: function() {
        var me = this;

        me.extendStore = Ext.create('Shopware.apps.Theme.store.Theme', {
            filters: [
                { property: 'version', value: 3 }
            ]
        }).load();

        me.extendCombo = Ext.create('Ext.form.field.ComboBox', {
            store: me.extendStore,
            labelWidth: 130,
            queryMode: 'local',
            editable: false,
            name: 'parentId',
            displayField: 'name',
            anchor: '100%',
            valueField: 'id',
            allowBlank: false,
            fieldLabel: '{s name=extension_of}Extension of{/s}',
            supportText: '{s name=extension_of_support}Select a theme as a building block{/s}'
        });

        return me.extendCombo;
    },

    createModelFieldSet: function() {
        var me = this,
            fieldSet = me.callParent(arguments);

        fieldSet.items.items[0].padding = 0;
        return fieldSet;
    }
});

//{/block}
