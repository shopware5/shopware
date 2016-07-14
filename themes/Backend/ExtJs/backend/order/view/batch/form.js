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
 * @package    Order
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/order/main}

/**
 * Shopware UI - Order batch window
 *
 * todo@all: Documentation
 */
//{block name="backend/order/view/batch/form"}
Ext.define('Shopware.apps.Order.view.batch.Form', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend:'Ext.form.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.batch-settings-panel',

    /**
     * An optional extra CSS class that will be added to this component's Element.
     */
    cls: Ext.baseCSSPrefix + 'batch-settings-panel',

    autoScroll: true,

    layout: {
        align: 'stretch',
        type: 'vbox'
    },

    bodyPadding: 10,

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        info: '{s name=settings/info}Caution: If you have selected orders with an already existing receipt, these will be regenerated.{/s}',
        mode: {
            label: '{s name=settings/mode}Mode{/s}',
            override: '{s name=settings/override}Recreate all documents{/s}',
            notExist: '{s name=settings/not_exist}Create only not existing documents{/s}'
        },
        documentType: '{s name=settings/document_type}Document type{/s}',
        paymentStatus: '{s name=settings/payment_status}Payment status{/s}',
        orderStatus: '{s name=settings/order_status}Order status{/s}',
        mail: '{s name=settings/auto_send}Send emails automatically{/s}',
        generate: '{s name=settings/process}Process changes{/s}',
        gridTitle: '{s name=settings/grid_title}Selected orders{/s}',
        settingsFieldSetLabel: '{s name=settings/field_set_label}Settings to generate{/s}',
        oneDocument: '{s name=settings/one_document}Create single document{/s}'
    },

    /**
	 * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
	 *
	 * @return void
	 */
    initComponent:function () {
        var me = this;

        me.registerEvents();

        me.modeData = [
            [0, me.snippets.mode.override],
            [1, me.snippets.mode.notExist]
        ];
        me.items = [
            me.createInfoContainer(),
            me.createSettingsContainer(),
            me.createOrderGrid()
        ];
        me.addCls('layout-expert');
        me.callParent(arguments);
    },

    registerEvents: function() {
        this.addEvents(

            /**
             * Event will be fired when the user clicks the "generate documents" button which is
             * displayed within the form field set.
             *
             * @event
             * @param [Ext.form.Panel] - This component
             */
            'processChanges'
        );
    },

    /**
     * Creates the info container which is displayed on top of the form.
     * @return Ext.container.Container
     */
    createInfoContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            html: me.snippets.info,
            style: 'color: #999; font-style: italic; margin: 0 0 15px 0;'
        });
    },

    createSettingsContainer: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.settingsFieldSetLabel,
            layout: 'anchor',
            flex: 1,
            defaults: {
                labelWidth: 155,
                xtype: 'combobox',
                anchor: '100%'
            },
            items: [
                {
                    name: 'documentType',
                    triggerAction: 'all',
                    fieldLabel: me.snippets.documentType,
                    store: Ext.create('Shopware.apps.Order.store.DocType'),
                    displayField: 'name',
                    valueField: 'id'
                },
                {
                    name: 'mode',
                    triggerAction: 'all',
                    fieldLabel: me.snippets.mode.label,
                    store:new Ext.data.SimpleStore({
                        fields:['value', 'description'], data: me.modeData
                    }),
                    displayField: 'description',
                    valueField: 'value'
                },
                {
                    name: 'orderStatus',
                    triggerAction: 'all',
                    queryMode: 'local',
                    fieldLabel: me.snippets.orderStatus,
                    store: me.orderStatusStore,
                    displayField: 'description',
                    valueField: 'id'
                },
                {
                    xtype: 'pagingcombo',
                    pageSize: 5,
                    name: 'paymentStatus',
                    triggerAction: 'all',
                    fieldLabel: me.snippets.paymentStatus,
                    store: Ext.create('Shopware.store.PaymentStatus', { pageSize: 5 }),
                    displayField: 'description',
                    valueField: 'id'
                },
                {
                    xtype: 'checkbox',
                    fieldLabel: me.snippets.mail,
                    checked: true,
                    inputValue: true,
                    uncheckedValue: false,
                    name: 'autoSendMail'
                },
                {
                    xtype: 'checkbox',
                    fieldLabel: me.snippets.oneDocument,
                    checked: false,
                    inputValue: true,
                    uncheckedValue: false,
                    name: 'createSingleDocument'
                },
                {
                    xtype: 'button',
                    margin: '15 0',
                    cls: 'primary',
                    text: me.snippets.generate,
                    handler: function() {
                        me.fireEvent('processChanges', me)
                    }
                }
            ]
        });
    },

    /**
     * Creates the grid which contains all selected orders and display if the mail is already sent and the current order
     * and payment status.
     */
    createOrderGrid: function() {
        var me = this;

        var store = Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.Order.model.Order',
            data: me.records
        });

        return Ext.create('Shopware.apps.Order.view.batch.List', {
            flex: 1,
            title: me.snippets.gridTitle,
            store: store
        });
    }

});
//{/block}
