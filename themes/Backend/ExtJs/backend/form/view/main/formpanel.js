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
 * @category   Shopware
 * @package    Form
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/form/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/form/view/main/formpanel"}
Ext.define('Shopware.apps.Form.view.main.Formpanel', {
    extend  : 'Ext.form.Panel',
    alias: 'widget.form-main-formpanel',
    title : '{s name=title_master}Master Data{/s}',
    autoScroll: true,
    monitorValid: true,
    bodyPadding: 10,

    // Fields will be arranged vertically, stretched to full width
    layout: 'anchor',
    defaults: {
        xtype: 'textfield',
        /*{if !{acl_is_allowed privilege=createupdate}}*/
        readOnly: true,
        /*{/if}*/
        layout: 'anchor',
        labelWidth: 155,
        anchor: '99%'
    },

    plugins: [{
        ptype: 'translation',
        translationType: 'forms'
    }],

    /**
     * Sets up the ui component
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = me.getItems();
        me.dockedItems = me.getButtons();

        me.callParent(arguments);

        if (me.record !== undefined) {
            me.loadRecord(me.record);
        }
    },

    /**
     * Creates buttons shown in form panel
     *
     * @return array
     */
    getButtons: function() {
        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            dock: 'bottom',
            cls: 'shopware-toolbar',
            items: ['->', /*{if {acl_is_allowed privilege=createupdate}}*/
            {
                text: '{s name=button_save_form}Save{/s}',
                action: 'save',
                cls: 'primary',
                formBind: true
            }
            /*{/if}*/]
        });
    },

    /**
     * Creates items shown in form panel
     *
     * @return array
     */
    getItems: function() {
        var me = this,
            record = me.record,
            linkToForm = '',
            variableHint = '',
            names = [];

        if (record !== undefined) {
            linkToForm =  '{s name=label_linktoform}Link to Form{/s}: ' + 'shopware.php?sViewport=forms&sFid=' + record.get('id');

            record.getFields().each(function(item) {
                /* {literal} */
                names.push("{sVars." + item.get('name') + "}");
                /* {/literal} */
            });

            variableHint = '{s name=support_text_variables}Available Variables{/s}: ' + names.join(', ');
        }

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            table: 's_cms_support_attributes',
            allowTranslation: true,
            margin: '20 0 0',
            disabled: false
        });
        if (me.record) {
            me.attributeForm.loadAttribute(me.record.get('id'));
        }

        return [{
            fieldLabel:'{s name=label_name}Name{/s}',
            name       : 'name',
            allowBlank: false,
            supportText : linkToForm,
            translatable: true
        }, {
            fieldLabel: '{s name=label_active}Active{/s}',
            xtype: 'checkbox',
            inputValue: true,
            uncheckedValue: false,
            name: 'active'
        },
        {
            fieldLabel:'{s name=label_email}Email{/s}',
            name: 'email',
            vtype: 'remote',
            validationUrl: '{url controller="base" action="validateEmail"}',
            validationErrorMsg: '{s name=invalid_email namespace=backend/base/vtype}The email address entered is not valid{/s}',
            allowBlank : false,
            translatable: true,
            supportText: '{s name="multipleReceivers"}{/s}'
        }, {
            fieldLabel:'{s name=label_subject}Subject{/s}',
            name       : 'emailSubject',
            allowBlank: false,
            translatable: true
        }, {
            xtype: 'codemirrorfield',
            mode: 'smarty',
            fieldLabel:'{s name=label_emailtemplate}Email template{/s}',
            name: 'emailTemplate',
            supportText: variableHint,
            translatable: true
        }, {
            /*{if !{acl_is_allowed privilege=createupdate}}*/
            readOnly: true,
            height: 350,
            /*{/if}*/
            fieldLabel:'{s name=label_headertext}Form-Header{/s}',
            name       : 'text',
            supportText : '{s name=support_text_headertext}Will be displayed above the form{/s}',
            xtype:'tinymce',
            translatable: true
        }, {
            /*{if !{acl_is_allowed privilege=createupdate}}*/
            readOnly: true,
            /*{/if}*/
            fieldLabel:'{s name=label_confirmationtext}Form-Confirmation{/s}',
            supportText:'{s name=support_text_confirmationtext}Will be displayed after a successful submission{/s}',
            name       : 'text2',
            height: 350,
            xtype:'tinymce',
            translatable: true
        },
            me.getShopSelector(),
        {
            fieldLabel: '{s name=label_metatitle}Meta title{/s}',
            name: 'metaTitle',
            translatable: true
        }, {
            fieldLabel: '{s name=label_metakeywords}Meta keywords{/s}',
            name: 'metaKeywords',
            translatable: true
        }, {
            fieldLabel: '{s name=label_metadescription}Meta description{/s}',
            name: 'metaDescription',
            translatable: true
        }, {
            xtype: 'hidden',
            name: 'id'
        }, me.attributeForm];
    },

    /**
     * @returns { Shopware.form.field.ShopGrid }
     */
    getShopSelector: function () {
        var selectionFactory = Ext.create('Shopware.attribute.SelectionFactory');

        return Ext.create('Shopware.form.field.ShopGrid', {
            name: 'shopIds',
            fieldLabel: '{s name=label_shop}Limit to shop(s){/s}',
            helpText: '{s name=shop_helper}Limit page visibility to the following shops. If left empty, page will be accessible in all shops.{/s}',
            editable: false,
            allowSorting: false,
            height: 130,
            labelWidth: 155,
            useSeparator: false,
            store: selectionFactory.createEntitySearchStore('Shopware\\Models\\Shop\\Shop'),
            searchStore: selectionFactory.createEntitySearchStore('Shopware\\Models\\Shop\\Shop')
        });
    }
});
//{/block}
