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
 * @package    RiskManagement
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/risk_management/main}

/**
 * Shopware UI - RiskManagement view container
 *
 * This is one container, which contains two comboboxes with the risks and per combobox one textfield to enter a value
 * to the risk.
 * One payment creates several containers with different datas.
 */
//{block name="backend/risk_management/view/risk_management/container"}
Ext.define('Shopware.apps.RiskManagement.view.risk_management.Container', {

    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.container.Container',
    layout: 'column',
    defaults: {
        columnWidth: 0.2
    },
    alias: 'widget.risk_management-main-container',

    /**
    * Sets up the ui component
    * @return void
    */
    initComponent: function() {
        var me = this;

        me.addEvents('onChangeRisk');

        me.items = me.createItems();

        me.callParent(arguments);
    },

    /**
     * Creates the items for the container
     * @return Array
     */
    createItems: function(){
        var me = this,
            newContainer,
            window = me.up('window');

        if(!me.hasOwnProperty('values')){
            newContainer = true;
        }
        var comboBox1 = Ext.create('Ext.form.field.ComboBox', {
            store: Ext.create('Shopware.apps.RiskManagement.store.Risks'),
            displayField: 'description',
            valueField: 'value',
            editable: false,
            value: (me.values && me.values.rule1) ? me.values.rule1 : '',
            listeners: {
                'change': function(comboBox, newValue){
                    me.fireEvent('onChangeRisk', this, newValue, 1)
                }
            }
        }),

        comboBox2 = Ext.create('Ext.form.field.ComboBox', {
            store: Ext.create('Shopware.apps.RiskManagement.store.Risks'),
            displayField: 'description',
            valueField: 'value',
            editable: false,
            value: (me.values && me.values.rule2) ? me.values.rule2 : '',
            listeners: {
                'change': function(comboBox, newValue){
                    me.fireEvent('onChangeRisk', this, newValue, 4)
                }
            }
        });
        if(me.values && ['ZONEIS', 'ZONEISNOT', 'BILLINGZONEIS', 'BILLINGZONEISNOT'].indexOf(me.values.rule1) >= 0){
            var field1 = Ext.create('Ext.form.field.ComboBox', {
                store: me.areasStore,
                displayField: 'name',
                valueField: 'name',
                editable: false,
                value: (me.values && me.values.value1) ? me.values.value1 : '',
                columnWidth: 0.1,
                style: {
                    marginLeft: '10px'
                }
            });
        }else if(me.values && (me.values.rule1 == 'SUBSHOP' || me.values.rule1 == 'SUBSHOPNOT')){
            var field1 = Ext.create('Ext.form.field.ComboBox', {
                store: me.subShopStore,
                displayField: 'name',
                valueField: 'id',
                editable: false,
//                value: (me.values && me.values.value1) ? me.subShopStore.findRecord('id', me.values.value1).get('name') : '',
                columnWidth: 0.1,
                style: {
                    marginLeft: '10px'
                }
            });
            // Use 'select' in order to be able to extract the shop-id later
            if(me.values && me.values.value1) {
                field1.select( me.subShopStore.findRecord('id', me.values.value1));
            }else{
                field1.setValue('');
            }
        }else{
            var field1 = Ext.create('Ext.form.field.Text', {
                columnWidth: 0.1,
                style: {
                    marginLeft: '10px'
                },
                value: (me.values && me.values.value1) ? me.values.value1 : ''
            });
        }
        if(me.values && me.values.rule1 == 'INKASSO'){
            field1.setValue('1');
            field1.hide();
        }else{
            field1.show();
        }

        if(me.values && ['ZONEIS', 'ZONEISNOT', 'BILLINGZONEIS', 'BILLINGZONEISNOT'].indexOf(me.values.rule2) >= 0){
            var field2 = Ext.create('Ext.form.field.ComboBox', {
                store: me.areasStore,
                displayField: 'name',
                valueField: 'name',
                editable: false,
                value: (me.values && me.values.value2) ? me.values.value2 : '',
                columnWidth: 0.1,
                style: {
                    marginLeft: '10px'
                }
            });
        }else if(me.values && (me.values.rule2 == 'SUBSHOP' || me.values.rule2 == 'SUBSHOPNOT')){
            var field2 = Ext.create('Ext.form.field.ComboBox', {
                store: me.subShopStore,
                displayField: 'name',
                valueField: 'id',
                editable: false,
//                value: (me.values && me.values.value2) ?  me.subShopStore.findRecord('id', me.values.value2).get('name') : '',
                columnWidth: 0.1,
                style: {
                    marginLeft: '10px'
                }
            });
            // Use 'select' in order to be able to extract the shop-id later
            if(me.values && me.values.value2) {
                field2.select( me.subShopStore.findRecord('id', me.values.value2));
            }else{
                field2.setValue('');
            }
        }else{
            var field2 = Ext.create('Ext.form.field.Text', {
                columnWidth: 0.1,
                style: {
                    marginLeft: '10px'
                },
                value: (me.values && me.values.value2) ? me.values.value2 : ''
            });
        }
        if(me.values && me.values.rule2 == 'INKASSO'){
            field2.setValue('1');
            field2.hide();
        }else{
            field2.show();
        }

        var items = [
            comboBox1,
            field1,
            {
                xtype: 'container',
                html: '<b>{s name=container_and}AND{/s}</b>',
                columnWidth: 0.1,
                style: {
                    height: '20px',
                    textAlign: 'center',
                    paddingTop: '2px',
                    paddingBottom: '2px'
                }
            },
            comboBox2,
            field2,
            {
                xtype: 'hidden',
                value: (me.values && me.values.id) ? me.values.id : ''
            }
        ];
        if(!newContainer){
            /*{if {acl_is_allowed privilege=delete}}*/
            items.push({
                xtype: 'button',
                iconCls: 'sprite-minus-circle',
                columnWidth: 0.09,
                text: '{s name=container/deleteButton}Delete{/s}',
                action: 'delete',
                rowIndex: (me.values && me.values.id) ? me.values.id : ''
            });
            /*{/if}*/
        }else{
            /*{if {acl_is_allowed privilege=save}}*/
            items.push({
                xtype: 'button',
                cls: 'primary',
                columnWidth: 0.22,
                text: '{s name=container/saveButton}Save{/s}',
                action: 'saveRules'
            });
            /*{/if}*/
            comboBox1.setValue("");
            comboBox2.setValue("");
        }

        return items;
    }
});
//{/block}
