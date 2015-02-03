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

//{namespace name=backend/theme/main}

//{block name="backend/theme/view/config_set/window"}

Ext.define('Shopware.apps.Theme.view.config_sets.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.theme-config-set-window',
    title : '{s name=config_sets}Configuration sets{/s}',
    width: 600,
    height: 250,
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    defaults: {
        flex: 1
    },
    cls: 'theme-config-set-window',

    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.dockedItems = [ me.createToolbar() ];

        me.callParent(arguments);
    },

    createItems: function() {
        var me = this, items = [];

        me.store.each(function(theme) {

            if (!theme.getConfigSets() instanceof Ext.data.Store) {
                return true;
            }
            if (!theme.getConfigSets().getCount() > 0) {
                return true;
            }
            var item = me.createFieldSet(theme);

            items.push(item);
        });

        me.formPanel = Ext.create('Ext.form.Panel', {
            items: items,
            bodyPadding: 15
        });

        return [ me.formPanel ];
    },


    createFieldSet: function(theme) {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            layout: 'fit',
            flex: 1,
            title: '{s name=config_set_title_prefix}Configuration sets{/s} : ' + theme.get('name'),
            items: [ me.createConfigSetCombo(theme) ]
        });
    },

    createConfigSetCombo: function(theme) {
        return Ext.create('Ext.form.field.ComboBox', {
            name: 'set' + theme.get('id'),
            store: theme.getConfigSets(),
            queryMode: 'local',
            valueField: 'id',
            displayField: 'name',
            listConfig: {
                getInnerTpl: function() {
                    var screen = theme.get('screen');

                    return '{literal}' +
                        '<div class="combo-item">' +
                            '<div>' +
                                '<h1>{name}</h1>' +
                                '<div>{description}</div>' +
                            '</div>' +
                            '<div class="x-clear" />' +
                        '</div>' +
                        '{/literal}';
                }
            }
        });
    },


    /**
     * Creates the window toolbar.
     *
     * @returns { Ext.toolbar.Toolbar }
     */
    createToolbar: function () {
        var me = this;

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            items: me.createToolbarItems(),
            dock: 'bottom'
        });

        return me.toolbar;
    },

    /**
     * Creates all toolbar elements.
     *
     * @returns { Array }
     */
    createToolbarItems: function() {
        var me = this, items = [];

        items.push({ xtype: 'tbfill' });

        items.push(me.createCancelButton());

        items.push(me.createSaveButton());

        return items;
    },


    /**
     * Creates the cancel button which will be displayed
     * in the bottom toolbar of the detail window.
     * The button handler will be raised to the internal
     * function me.onCancel
     *
     * @return Ext.button.Button
     */
    createCancelButton: function () {
        var me = this;

        me.cancelButton = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            name: 'cancel-button',
            text: '{s name=cancel}Cancel{/s}',
            handler: function () {
                me.destroy();
            }
        });
        return me.cancelButton;
    },

    /**
     * Creates the save button which will be displayed
     * in the bottom toolbar of the detail window.
     * The button handler will be raised to the internal
     * function me.onSave
     *
     * @return Ext.button.Button
     */
    createSaveButton: function () {
        var me = this;

        me.saveButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            name: 'detail-save-button',
            text: '{s name=load}Load{/s}',
            handler: function () {
                me.fireEvent(
                    'assign-config-sets',
                    me,
                    me.theme,
                    me.formPanel
                );
            }
        });
        return me.saveButton;
    }

});

//{/block}
