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

//{namespace name=backend/snippet/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/snippet/view/main/create_form"}
Ext.define('Shopware.apps.Snippet.view.main.CreateForm', {

    extend: 'Enlight.app.Window',
    alias: 'widget.snippet-main-createForm',

    layout: 'fit',
    width: 460,

    minimizable: false,
    maximizable: false,

    border:false,
    height: 280,

    defaultLocaleId: null,
    defaultShopId: null,
    defaultNamespace: '',

    /**
     * Contains all snippets for this view
     * @object
     */
    snippets: {
        titleCreateWindow: '{s name=window/create}Create new snippet{/s}',
        buttonSave:     '{s name=button_save}Save{/s}',
        labelLocale:    '{s name=label_locale}Locale{/s}',
        labelShop:      '{s name=label_shop}Shop{/s}',
        labelNamespace: '{s name=label_namespace}Namespace{/s}',
        labelName:      '{s name=label_name}Name{/s}',
        labelValue:     '{s name=label_value}Value{/s}'
    },

    /**
     * Sets up the ui component
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = me.snippets.titleCreateWindow;

        me.form = Ext.create('Ext.form.Panel', {
            monitorValid: true,
            bodyPadding: 10,

            defaultType: 'textfield',
            defaults: {
                labelWidth: 120,
                anchor: '100%'
            },

            bbar: me.getToolbar()
        });

        me.form.add(me.getItems());

        me.items = [ me.form ];

        me.callParent(arguments);
    },

    getToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            items: [{
                text: me.snippets.buttonSave,
                action: 'save',
                cls:'primary',
                formBind: true
            }]
        });
    },

    /**
     * Creates items shown in form panel
     *
     * @return array
     */
    getItems: function() {
        var me        = this,
            formItems = [];

        var localeStore =  Ext.create('Shopware.store.Locales').load({
            callback: function() {
                if (me.defaultShopId) {
                    me.form.getForm().findField('localeId').setValue(me.defaultLocaleId);
                } else {
                    me.form.getForm().findField('localeId').setValue(this.getAt('0').get('id'));
                }
            }
        });

        var shopStore =  Ext.create('Shopware.store.Shop').load({
            callback: function() {

                if (me.defaultShopId) {
                    me.form.getForm().findField('shopId').setValue(me.defaultShopId);
                } else {
                    me.form.getForm().findField('shopId').setValue(this.getAt('0').get('id'));
                }
            }
        });

        formItems.push({
            xtype: 'combobox',
            name: 'localeId',
            fieldLabel:  me.snippets.labelLocale,
            store: localeStore,
            valueField: 'id',
            displayField: 'locale',
            queryMode: 'local',
            required: true,
            editable: false,
            allowBlank: false
        });

        formItems.push({
            xtype:'combobox',
            name:'shopId',
            fieldLabel: me.snippets.labelShop,
            store: shopStore,
            valueField:'id',
            displayField:'name',
            queryMode: 'local',
            required: true,
            editable: false,
            allowBlank: false
        });

        formItems.push({
            fieldLabel: me.snippets.labelNamespace,
            name: 'namespace',
            value: me.defaultNamespace,
            allowBlank: false
        });

        formItems.push({
            fieldLabel: me.snippets.labelName,
            name: 'name',
            allowBlank: false
        });

        formItems.push({
            xtype: 'textarea',
            fieldLabel: me.snippets.labelValue,
            grow: true,
            growMin: 0,
            name: 'value'
        });

        return formItems;
    }
});
//{/block}
