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
 * @package    Premium
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/premium/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/premium/view/premium/detail"}
Ext.define('Shopware.apps.Premium.view.premium.Detail', {
    extend:'Enlight.app.Window',
    alias:'widget.premium-main-detail',
    cls:'createWindow',
    modal: true,

    layout:'border',
    autoShow:true,
    title:'{s name=form/title}Premium artice details{/s}',
    border:0,
    width:600,
    height:270,
    stateful:true,
    stateId:'shopware-premium-detail',
    footerButton: false,

    initComponent:function () {
        var me = this;

        me.premiumForm = me.createFormPanel();
        /*{if {acl_is_allowed privilege=update}}*/
        me.dockedItems = [{
            xtype: 'toolbar',
            ui: 'shopware-ui',
            dock: 'bottom',
            cls: 'shopware-toolbar',
            items: me.createButtons()
        }];
        /*{/if}*/

        me.items = [me.premiumForm];
        me.callParent(arguments);
    },

    createFormPanel: function(){
        var me = this;
        var premiumForm = Ext.create('Ext.form.Panel', {
            collapsible:false,
            split:false,
            region:'center',
            defaults:{
                labelStyle:'font-weight: 700; text-align: right;',
                labelWidth:130,
                anchor:'100%'
            },
            bodyPadding:10,
            items:[
                {
                    xtype:'articlesearch',
                    searchFieldName: 'orderNumber',
                    returnValue: 'number',
                    name:'orderNumber',
                    fieldLabel:'{s name=form_ordernumber}Order number{/s}',
                    supportText:'{s name=form_ordernumber/supporttext}The order number of the article that will be added as premium article.{/s}',
                    allowBlank:false,
                    required: true,
                    formFieldConfig: {
                        labelStyle:'font-weight: 700; text-align: right;',
                        labelWidth:130,
                        fieldStyle: 'width: 435px'
                    }
                },
                {
                    xtype:'textfield',
                    name:'orderNumberExport',
                    fieldLabel:'{s name=form_export_ordernumber}Export order number{/s}',
                    supportText:'{s name=form_export_ordernumber/supporttext}This number is not required. You may leave this field blank.{/s}',
                    allowBlank:true
                },
                {
                    xtype:'combobox',
                    name:'shopId',
                    fieldLabel:'{s name=form_subshop}Subshop{/s}',
                    store: Ext.create('Shopware.apps.Premium.store.Subshops').load(),
                    valueField:'id',
                    displayField:'name',
                    emptyText:'{s name=form_subshop/emptytext}Please select{/s}',
                    allowBlank:false
                },
                {
                    xtype:'numberfield',
                    name:'startPrice',
                    fieldLabel:'{s name=form_startprice}Minimum order value{/s}',
                    supportText:'{s name=form_startprice/supporttext}The minimum order value for the premium article.{/s}',
                    hideTrigger:true,
                    allowBlank:false,
                    keyNavEnabled:false,
                    mouseWheelEnabled:false
                },
                {
                    xtype: 'hidden',
                    name: 'id'
                }
            ]
        });

        if(me.record){
            premiumForm.loadRecord(me.record);
        }

        return premiumForm;
    },

    createButtons: function(){
        var me = this;
        var buttons = ['->',
            {
                text:'{s name=detail_cancel}Cancel{/s}',
                cls: 'secondary',
                scope:me,
                handler:me.destroy
            },
            {
                text:'{s name=detail_save}Save{/s}',
                action:'savePremium',
                cls:'primary'
            }
        ];

        return buttons;
    }
});
//{/block}
