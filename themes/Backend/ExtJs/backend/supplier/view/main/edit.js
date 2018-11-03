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
 * @package    Supplier
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/supplier/view/edit}*/

/**
 * Shopware View - Supplier
 *
 * Backend - Management for Suppliers. Create | Modify | Delete and Logo Management.
 * Create a edit supplier view
 */
//{block name="backend/supplier/view/main/edit"}
Ext.define('Shopware.apps.Supplier.view.main.Edit', {
    extend : 'Enlight.app.Window',
    alias : 'widget.supplier-main-edit',
    layout : 'fit',
    title : '{s name=title}Supplier - edit{/s}',
    width : '80%',
    height : '90%',
    stateful : true,
    stateId : 'shopware-supplier-edit',

    /**
     * Initialize the component
     * @return void
     */
    initComponent : function () {
        var me = this,
            oldTitle = me.title;

        me.title = oldTitle + ': ' + me.record.get('name');

        me.logo = me.getLogo();
        me.dropZone = me.getDropZone();
        me.htmlEditor = me.getHtmlField();
        me.hiddenFields = me.getHiddenFields();
        me.topForm = me.getFormTopPart();
        me.deleteButton = me.getDeleteButton();

        me.supplierInfoForm = me.getInfoForm();
        me.supplierInfoForm.getForm().loadRecord(this.record);
        me.attributeForm.loadAttribute(this.record.getId());

        me.items = [me.supplierInfoForm];

        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            cls: 'shopware-toolbar',
            ui: 'shopware-ui',
            items: me.getButtons()
        }];

        //removed me.callParent(me); because chrome could not handle this call
        me.callParent(arguments);
    },
    /**
     * Returns an array of buttons. Default buttons are cancel and save
     *
     * @return array of buttons
     */
    getButtons : function()
    {
        var me = this;
        return  ['->',
            {
                text :  '{s name=cancel}Cancel{/s}',
                cls: 'secondary',
                scope : me,
                handler : me.destroy
            },
            {
                text : '{s name=save}Save{/s}',
                action : 'saveSupplier',
                scale : 'large',
                cls : 'primary',
                formBind: true
            }
        ];
    },
    /**
     * Returns the delete logo button
     *
     * @return Ext.buttonButton
     */
    getDeleteButton : function() {
        var me = this;

        return {
            xtype   : 'button',
            cls: 'small secondary',
            name    : 'deleteLogo',
            scope   : me,
            text    : '{s name=delete_logo}Delete Logo{/s}',
            action  : 'deleteLogo',
            scale   : 'small',
            style   : 'float: right; margin: 0 35px 10px 0'
        };
    },



    /**
     * Return the entire form
     *
     *  @return Ext.form.Panel
     */
    getInfoForm : function()
    {
        var me = this,
            logoArray = [];

        //logoArray.push(me.logo);
        //remove delete button when there is no logo to delete
        if (me.record.get('image') !== "") {
            logoArray.push(me.logo);
            logoArray.push(me.deleteButton);
        }

        me.formPanel = Ext.create('Ext.form.Panel', {
            collapsible : false,
            split       : false,
            region      : 'center',
            width       : '100%',
            id          : 'supplierFormPanel',
            defaults : {
                anchor      : '100%'
            },
            bodyPadding : 10,
            border : 0,
            autoScroll: true,
            plugins: [{
                ptype: 'translation',
                pluginId: 'translation',
                translationType: 'supplier',
                translationMerge: false,
                translationKey: null
            }],
            items : [
                Ext.create('Ext.form.FieldSet', {
                    alias:'widget.supplier-base-field-set',
                    cls: Ext.baseCSSPrefix + 'supplier-base-field-set',
                    title : '{s name=panel_base}Basic information{/s}',
                    layout: 'form',
                    items : [
                        {
                            xtype : 'container',
                            layout : 'column',
                            border : 1,
                            items : [
                                {
                                    xtype       : 'container',
                                    layout      : 'anchor',
                                    columnWidth : 0.8,
                                    defaults : {
                                        labelWidth  : 155
                                    },
                                    items : me.topForm
                                },
                                {
                                    xtype : 'container',
                                    layout : 'anchor',
                                    columnWidth : 0.2,
                                    items : logoArray
                                },
                                {
                                    xtype : 'container',
                                    layout : 'anchor',
                                    columnWidth : 1,
                                    defaults : {
                                        anchor : '100%'
                                    },
                                    items : [
                                        me.htmlEditor,
                                        me.dropZone
                                    ]
                                },
                                me.hiddenFields
                            ]
                        }
                    ]
                }),
                Ext.create('Ext.form.FieldSet', {
                    alias:'widget.supplier-seo-field-set',
                    cls: Ext.baseCSSPrefix + 'supplier-seo-field-set',
                    collapsible: true,
                    collapsed: true,
                    defaults : {
                        labelWidth  : 155,
                        anchor      : '100%'
                    },
                    title : '{s name=panel_seo}SEO information{/s}',
                    items : [
                        {
                            xtype       : 'textfield',
                            name        : 'metaDescription',
                            translatable: true,
                            fieldLabel  : '{s name=seo_meta_description}Description{/s}',
                            supportText : '{s name=seo_meta_description_support}Description meta tag{/s}',
                            allowBlank  : true
                        },
                        {
                            xtype       : 'textfield',
                            name        : 'metaKeywords',
                            translatable: true,
                            fieldLabel  : '{s name=seo_meta_keywords}Keywords{/s}',
                            supportText : '{s name=seo_meta_keywords_support}Keywords meta tag{/s}',
                            allowBlank  : true
                        }
                    ]
                })
            ]
        });

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            table: 's_articles_supplier_attributes',
            allowTranslation: false,
            translationForm: me.formPanel
        });

        me.formPanel.add(me.attributeForm);
        return me.formPanel;
    },
    /**
     * Returns the HTML editor for the description
     *
     * @return Ext.form.field.HtmlEditor
     */
    getHtmlField : function()
    {
        return Ext.create('Shopware.form.field.TinyMCE', {
            name : 'description',
            translatable: true,
            fieldLabel : '{s name=description}Description{/s}',
            labelWidth  : 155
        });
    },
    /**
     * Returns a set of hidden fields
     * @return array of hidden fields
     */
    getHiddenFields : function()
    {
        return [{
            xtype : 'hidden',
            name : 'image',
            fieldLabel : 'image'
        }];
    },

    /**
     * Returns the two most upper form fields: name and url
     *
     * @return array of form fields
     */
    getFormTopPart : function() {
        return [
            {
                xtype : 'textfield',
                name : 'name',
                allowBlank  : false,
                anchor : '95%',
                fieldLabel  : '{s name=name}Supplier name{/s}',
                supportText : '{s name=name_support}Name of the supplier e.g. Shopware AG{/s}'
            },
            {
                xtype       : 'textfield',
                name        : 'metaTitle',
                translatable : true,
                anchor : '95%',
                fieldLabel  : '{s name=seo_meta_title}Page title{/s}',
                supportText : '{s name=seo_meta_title_support}Page title in the supplier page{/s}'
            },
            {
                xtype : 'textfield',
                vtype : 'url',
                name : 'link',
                anchor : '95%',
                fieldLabel  : '{s name=link}URL{/s}',
                supportText : '{s name=link_support}Link to suppliers website{/s}'
            }
        ];
    },
    /**
     * Returns the media selector for the supplier module
     *
     * @return Shopware.MediaManager.MediaSelection
     */
    getDropZone : function()
    {
        return Ext.create('Shopware.MediaManager.MediaSelection', {
            fieldLabel      : '{s name=form_logo}Logo{/s}',
            name            : 'media-manager-selection',
            supportText     : '{s name=logo_support}Supplier logo selection via Media Manager. The selection is limited to one media.{/s}',
            multiSelect     : false,
            albumId: -12, // Default supplier albumId
            labelWidth  : 155
        });
    },

    /**
     * Returns an element which contains the logo
     *
     * @return Ext.Img
     */
    getLogo : function() {
        var me = this,
            imageUrl = me.record.get('image'),
            image = Ext.create('Ext.Img', {
                    src         : imageUrl,
                    maxWidth    : 120,
                    width       : 120,
                    maxHeight   : 80,
                    margin      : '0 auto',
                    name        : 'image',
                    itemId      : 'supplierFormPanelLogo',
                    style       : 'height: 80px; width: 120px;'
                }
            );

        return image;
    }
});
//{/block}
