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

/* {namespace name=backend/category/main} */

//{block name="backend/category/view/duplicate/settings"}
Ext.define('Shopware.apps.Category.view.main.DuplicateSettings', {
    extend: 'Enlight.app.Window',
    alias: 'widget.duplication-settings-window',
    width: 500,
    height: 180,
    layout: 'fit',
    modal: true,

    snippets: {
        title: '{s name=settings/duplicate/title}Duplicate settings{/s}',
        emptyText: '{s name=settings/duplicate/emptyText}Please select...{/s}',
        categoryName: '{s name=settings/duplicate/categoryName}Select the destination category{/s}',
        association: '{s name=settings/duplicate/association}Copy item associations{/s}',
        button: '{s name=settings/duplicate/button}Duplicate{/s}'
    },

    initComponent: function() {
        var me = this;

        me.form = me.createFormPanel();
        me.title = me.snippets.title;

        me.items = [ me.form ];
        me.dockedItems = [ me.createToolbar() ];
        me.callParent(arguments);
    },

    createFormPanel: function() {
        var me = this;

        return Ext.create('Ext.form.Panel', {
            layout: 'anchor',
            border: false,
            bodyPadding: 25,
            defaults: {
                anchor: '100%'
            },
            items: me.createFormPanelItems()
        });
    },

    createFormPanelItems: function() {
        var me = this;

        me.categoryNameField = Ext.create('Shopware.form.field.PagingComboBox', {
            name: 'categoryId',
            emptyText: me.snippets.emptyText,
            pageSize: 15,
            labelWidth: 155,
            fieldLabel: me.snippets.categoryName,
            store: Ext.create('Shopware.apps.Category.store.CategoryPath'),
            valueField: 'id',
            displayField: 'name',
            disableLoadingSelectedName: true
        });

        me.associationField = Ext.create('Ext.form.field.Checkbox', {
            inputValue: true,
            uncheckedValue: false,
            checked: true,
            labelWidth: 155,
            name: 'reassignArticleAssociations',
            fieldLabel: me.snippets.association
        });

        return [ me.categoryNameField, me.associationField ];
    },

    createToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: [ '->', {
                text: me.snippets.button,
                cls: 'primary',
                handler: function() {
                    me.fireEvent('start-duplication', me, me.treeRecord);
                }
            }]
        });
    }
});
//{/block}
