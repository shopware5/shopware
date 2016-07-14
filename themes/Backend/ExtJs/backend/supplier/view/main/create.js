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

/*{namespace name=backend/supplier/view/create}*/

/**
 * Shopware View - Supplier
 *
 * Backend - Management for Suppliers. Create | Modify | Delete and Logo Management.
 * Create a new supplier view
 */
//{block name="backend/supplier/view/main/create"}
Ext.define('Shopware.apps.Supplier.view.main.Create', {
    extend : 'Enlight.app.Window',
    alias : 'widget.supplier-main-create',
    layout : 'fit',
    title : '{s name=title}Supplier - Create{/s}',
    width : '80%',
    height : '90%',
    autoScroll: true,
    stateful : true,
    stateId : 'shopware-supplier-create',

    /**
     * Initialize the component
     * @return void
     */
    initComponent : function () {
        var me = this;
        me.items = [ me.getFormPanel() ];

        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            cls: 'shopware-toolbar',
            ui: 'shopware-ui',
            items: me.getButtons()
        }];

        me.callParent(arguments);
    },

    /**
     * Returns a new fieldset containing an attribute form
     * @returns Ext.form.FieldSet
     */
    createAttributeForm: function () {
        var me = this;

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            table: 's_articles_supplier_attributes',
            allowTranslation: false
        });
        return me.attributeForm;
    },

    /**
     * Returns the whole form to edit the supplier
     *
     * @returns Ext.form Panel
     */
    getFormPanel : function()
    {
        var me = this;
        return Ext.create('Ext.form.Panel', {
            collapsible : false,
            split       : false,
            region      : 'center',
            width       : '100%',
            autoScroll: true,
            defaults : {
                labelWidth  : 155,
                anchor      : '100%'
            },
            bodyPadding : 10,
            items : [
                Ext.create('Ext.form.FieldSet', {
                    alias:'widget.supplier-base-field-set',
                    title : '{s name=panel_base}Basic information{/s}',
                    defaults : {
                        labelWidth  : 155,
                        anchor      : '100%'
                    },
                    items : [
                        {
                            xtype       : 'textfield',
                            name        : 'name',
                            fieldLabel  : '{s name=name}Supplier name{/s}',
                            supportText : '{s name=name_support}Name of the supplier e.g. Shopware AG{/s}',
                            allowBlank  : false
                        },
                        {
                            xtype       : 'textfield',
                            name        : 'metaTitle',
                            fieldLabel  : '{s name=seo_meta_title}Page title{/s}',
                            supportText : '{s name=seo_meta_title_support}Page title in the supplier page{/s}'
                        },
                        {
                            xtype       : 'textfield',
                            vtype       : 'url',
                            name        : 'link',
                            fieldLabel  : '{s name=link}URL{/s}',
                            supportText : '{s name=link_support}Link to suppliers website{/s}'
                        },
                        {
                            xtype : 'container',
                            layout : 'anchor',
                            defaults : {
                                anchor : '100%'
                            },
                            items : [
                                me.getHtmlField(),
                                me.getDropZone(),
                                {
                                    xtype       : 'hidden',
                                    name        : 'image',
                                    fieldLabel  : 'image'
                                }
                            ]
                        }
                    ]
                }),
                Ext.create('Ext.form.FieldSet', {
                    alias:'widget.supplier-seo-field-set',
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
                            fieldLabel  : '{s name=seo_meta_description}Description{/s}',
                            supportText : '{s name=seo_meta_description_support}Description meta tag{/s}',
                            allowBlank  : true
                        },
                        {
                            xtype       : 'textfield',
                            name        : 'metaKeywords',
                            fieldLabel  : '{s name=seo_meta_keywords}Keywords{/s}',
                            supportText : '{s name=seo_meta_keywords_support}Keywords meta tag{/s}',
                            allowBlank  : true
                        }
                    ]
                }),
                me.createAttributeForm()
            ]
        });
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
            fieldLabel : '{s name=description}Description{/s}',
            labelWidth: 155
        });
    },
    /**
     * Button Array - the event listener will be set in the controller control method
     *
     * @return array of objects
     */
    getButtons : function()
    {
        var me = this;
        return ['->',
            {
                text    : '{s name=cancel}Cancel{/s}',
                scope   : me,
                cls: 'secondary',
                handler : me.destroy
            },
            {
                text    : '{s name=save}Save{/s}',
                action  : 'saveSupplier',
                cls     : 'primary',
                formBind: true
            }
        ];
    },
    /**
     * Returns a media drop field.
     *
     * @return Shopware.MediaManager.MediaSelection
     */
    getDropZone : function()
    {
        return Ext.create('Shopware.MediaManager.MediaSelection', {
            fieldLabel      : '{s name=logo}Logo{/s}',
            name            : 'media-manager-selection',
            supportText     : '{s name=logo_support}Supplier logo selection via Media Manager. The selection is limited to one media.{/s}',
            multiSelect     : false,
            albumId: -12, // Default supplier albumId
            labelWidth: 155
        });
    }
});
//{/block}
