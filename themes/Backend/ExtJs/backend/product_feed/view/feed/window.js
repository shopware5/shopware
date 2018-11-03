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
 * @package    ProductFeed
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/product_feed/view/feed}

/**
 * Shopware UI - ProductFeed main window.
 *
 * Displays all Detail Information
 */
//{block name="backend/product_feed/view/feed/window"}
Ext.define('Shopware.apps.ProductFeed.view.feed.Window', {
    extend: 'Enlight.app.Window',
    title: '{s name=window/detail_title}Feed - Configuration{/s}',
    alias: 'widget.product_feed-feed-window',
    border: false,
    autoShow: true,
    layout: 'fit',
    height: '90%',
    /**
     * Display no footer button for the detail window
     * @boolean
     */
    footerButton:false,
    width: 925,

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        // Create our form panel and assign it to the namespace for later usage
        me.formPanel = me.createFormPanel();
        me.items = me.formPanel;

        me.dockedItems = [{
            dock: 'bottom',
            xtype: 'toolbar',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: me.createFormButtons()
        }];
        me.callParent(arguments);

        // Load form data if we're having a record
        if(me.record){
            //set the default value
            if(me.record.data.encodingId == 0) me.record.data.encodingId = 1;
            if(me.record.data.formatId == 0) me.record.data.formatId = 1;

            me.formPanel.loadRecord(me.record);
            me.attributeForm.loadAttribute(me.record.get('id'));
        }
    },

    /**
     * creates the form panel
     */
    createFormPanel: function(){
        var me = this;

        me.tabPanel = Ext.create('Ext.tab.Panel', {
            minHeight: 320,
            flex: 1,
            items: [
                {
                    xtype:'product_feed-feed-tab-format'
                },
                {
                    xtype:'product_feed-feed-tab-header'
                },
                {
                    xtype:'product_feed-feed-tab-body'
                },
                {
                    xtype:'product_feed-feed-tab-footer'
                },
                {
                    xtype:'product_feed-feed-tab-category',
                    availableCategoriesTree: me.availableCategoriesTree,
                    record: me.record
                },
                {
                    xtype:'product_feed-feed-tab-supplier',
                    supplierStore: me.supplierStore,
                    record: me.record
                },
                {
                    xtype:'product_feed-feed-tab-article',
                    articleStore: me.articleStore,
                    record: me.record
                },
                {
                    xtype:'product_feed-feed-tab-filter'
                }
            ]
        });
        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            table: 's_export_attributes',
            title: '{s namespace="backend/attributes/main" name="attribute_form_title"}{/s}',
            bodyPadding: 20,
            autoScroll: true,
            tabPanel: me.tabPanel
        });

        me.tabPanel.add(me.attributeForm);

        return Ext.create('Ext.form.Panel', {
            layout: {
                type: 'vbox',
                align : 'stretch'
            },
            defaults: { flex: 1 },
            unstyled: true,
            items: [
                {
                    xtype:'product_feed-feed-detail',
                    record: me.record,
                    comboTreeCategoryStore: me.comboTreeCategoryStore,
                    shopStore: me.shopStore,
                    availableCategoriesTree: me.availableCategoriesTree,
                    style:'padding: 10px'
                },
                me.tabPanel
            ]
        });
    },
    /**
     * creates the form buttons cancel and save
     */
    createFormButtons: function(){
        var me = this;
        return ['->',
            {
                text:'{s name=detail_general/button/cancel}Cancel{/s}',
                scope:me,
                cls: 'secondary',
                handler:function () {
                    this.destroy();
                }
            },
            {
                text:'{s name=detail_general/button/save}Save{/s}',
                action:'update',
                disabled:true,
                cls:'primary'
            },
            {
                text:'{s name=detail_general/button/save_and_close}Save and Close{/s}',
                action:'save',
                disabled:true,
                cls:'primary'
            }
        ];
    }
});
//{/block}
