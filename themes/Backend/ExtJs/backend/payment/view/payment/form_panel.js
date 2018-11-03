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
 * @package    Payment
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/payment/payment}

/**
 * Shopware UI - The general-formpanel to edit general payment-information
 *
 * todo@all: Documentation
 *
 */
//{block name="backend/payment/view/payment/formpanel"}
Ext.define('Shopware.apps.Payment.view.payment.FormPanel', {
    extend : 'Ext.form.Panel',
    autoShow: true,
    alias : 'widget.payment-main-formpanel',
    region: 'center',
    layout: 'anchor',
    autoScroll: true,
    bodyPadding: '10px',
    name:  'formpanel',
    preventHeader: true,
    border: 0,
    defaults:{
        labelStyle:'font-weight: 700; text-align: right;',
        labelWidth:130,
        anchor:'100%'
    },
    plugins: [{
        pluginId: 'translation',
        ptype: 'translation',
        translationType: 'config_payment',
        translationMerge: true
    }],

    /**
     * This function is called, when the component is initiated
     * It creates the columns of the grid
     */
    initComponent: function(){
        var me = this;
        me.items = me.getItems();
        me.callParent(arguments);
    },

    /**
     * This function creates the columns of the grid
     * @return Array
     */
    getItems: function(){
        var items = [{
            xtype: 'textfield',
            fieldLabel: '{s name=formpanel_description_label}Description{/s}',
            name: 'description',
            translatable: true
        },{
            xtype: 'textfield',
            fieldLabel: '{s name=formpanel_name_label}Name{/s}',
            name: 'name'
        },{
            xtype: 'textfield',
            hidden: true,
            name: 'id'
        },{
            xtype: 'textfield',
            fieldLabel: '{s name=formpanel_template_label}Template{/s}',
            name: 'template'
        },{
            xtype: 'textfield',
            fieldLabel: '{s name=formpanel_class_label}Class{/s}',
            name: 'class'
        },{
            xtype: 'textfield',
            fieldLabel: '{s name=formpanel_table_label}General surcharge{/s}',
            name: 'table'
        },{
            xtype: 'textarea',
            fieldLabel: '{s name=formpanel_additional-description_label}Additional description{/s}',
            name: 'additionalDescription',
            translatable: true
        },{
            xtype: 'textfield',
            fieldLabel: '{s name=formpanel_surcharge_label}Surcharge in %{/s}',
            name: 'debitPercent'
        },{
            xtype: 'textfield',
            fieldLabel: '{s name=formpanel_generalSurcharge_label}General Surcharge{/s}',
            name: 'surcharge',
            supportText: '{s name=payment/surcharge/supportText}Use \',\' or \'.\' for decimal numbers{/s}'
        },{
            xtype: 'textfield',
            fieldLabel: '{s name=formpanel_position_surcharge}Position{/s}',
            name: 'position'
        },{
            xtype: 'checkbox',
            fieldLabel: '{s name=formpanel_active_label}Active{/s}',
            inputValue: 1,
            uncheckedValue: 0,
            name: 'active'
        },{
            xtype: 'checkbox',
            fieldLabel: '{s name=formpanel_esdActive_label}Active for ESD products{/s}',
            inputValue: 1,
            uncheckedValue: 0,
            name: 'esdActive'
        },{
            xtype: 'checkbox',
            fieldLabel: '{s name=formpanel_mobileInactive_label}Disable for smartphones{/s}',
            inputValue: 1,
            uncheckedValue: 0,
            name: 'mobileInactive'
        },{
            xtype: 'textfield',
            fieldLabel: '{s name=formpanel_urlIFrame_label}URL for iFrame{/s}',
            name: 'embedIFrame'
        },{
            xtype: 'textfield',
            fieldLabel: '{s name=formpanel_action_label}Action{/s}',
            name: 'action',
            disabled: true
        },{
            xtype: 'textfield',
            fieldLabel: '{s name=formpanel_pluginID_label}PluginID{/s}',
            name: 'pluginId',
            disabled: true
        }];

        return items;
    }
});
//{/block}
