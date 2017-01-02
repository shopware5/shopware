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

//{namespace name=backend/custom_search/translation}

//{block name="backend/config/view/custom_search/sorting/includes/create_window"}

Ext.define('Shopware.apps.Config.view.custom_search.sorting.includes.CreateWindow', {
    extend: 'Enlight.app.Window',
    height: 150,
    width: 380,
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    maximizable: false,
    minimizable: false,
    modal: true,

    initComponent: function () {
        var me = this;

        if (!Ext.isFunction(me.callback)) {
            throw 'Create window requires a provided callback function';
        }

        me.formPanel = me.createFormPanel();
        me.items = [me.formPanel];

        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            items: [me.createCancelButton(), '->', me.createSaveButton()]
        }];

        me.callParent(arguments);
    },

    createFormPanel: function() {
        var me = this;

        return Ext.create('Ext.form.Panel', {
            items: me.items,
            flex: 1,
            border: false,
            layout: 'anchor',
            bodyPadding: 20,
            defaults: {
                anchor: '100%'
            }
        });
    },

    createCancelButton: function () {
        var me = this;

        me.cancelButton = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            text: '{s name="cancel_button"}{/s}',
            handler: Ext.bind(me.onCancel, me)
        });
    },

    createSaveButton: function () {
        var me = this;

        me.saveButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            text: '{s name="apply_button"}{/s}',
            handler: Ext.bind(me.onSave, me)
        });

        return me.saveButton;
    },

    onCancel: function () {
        this.destroy();
    },

    onSave: function () {
        var me = this,
            values = me.formPanel.getForm().getValues();

        if (!me.formPanel.getForm().isValid()) {
            return;
        }

        me.callback(values);
        me.destroy();
    }
});

//{/block}
