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
 * Shopware UI - Order detail page
 *
 * todo@all: Documentation
 */
//{block name="backend/order/view/detail/communication"}
Ext.define('Shopware.apps.Order.view.detail.Communication', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend:'Ext.form.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.order-communication-panel',

    /**
     * An optional extra CSS class that will be added to this component's Element.
     */
    cls: Ext.baseCSSPrefix + 'communication-panel shopware-form',

    /**
     * A shortcut for setting a padding style on the body element. The value can either be a number to be applied to all sides, or a normal css string describing padding.
     */
    bodyPadding: 10,

    /**
     * True to use overflow:'auto' on the components layout element and show scroll bars automatically when necessary, false to clip any overflowing content.
     */
    autoScroll: true,

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title: '{s name=communication/window_title}Communication{/s}',
        internal: {
            title: '{s name=communication/internal/title}Internal communication{/s}',
            text: '{s name=communication/internal/text}This comment box is for internal communication. The field is not visible in the frontend and for the customer at any given time.{/s}',
            label: '{s name=communication/internal/label}Internal comment{/s}',
            button: '{s name=communication/internal/button}Save internal comment{/s}'
        },
        external: {
            title: '{s name=communication/external/title}Communication with the customer{/s}',
            text: '{s name=communication/external/text}This comment box is for internal communication. The field is not visible in the frontend and for the customer at any given time.{/s}',
            customerLabel: '{s name=communication/external/customer_label}Customer comment{/s}',
            externalLabel: '{s name=communication/external/external_label}Your comment{/s}',
            button: '{s name=communication/external/button}Save external comments{/s}'
        }
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
        me.items = [
            me.createInternalFieldSet(),
            me.createExternalFieldSet()
        ];
        me.title = me.snippets.title;
        me.callParent(arguments);
        me.loadRecord(me.record);
    },

    /**
     * Registers the custom component events.
     * @return void
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the "Save internal comment" button
             * which is placed in the communication panel at the bottom of the internal field set.
             *
             * @event
             * @param [Ext.data.Model] record - The current form record
             * @param [Ext.form.Panel] form - The communication form panel
             */
            'saveInternalComment',

            /**
             * Event will be fired when the user clicks the "Save external comment" button
             * which is placed in the communication panel at the bottom of the external field set.
             *
             * @event
             * @param [Ext.data.Model] record - The current form record
             * @param [Ext.form.Panel] form - The communication form panel
             */
            'saveExternalComment'
        );
    },

    /**
     * Creates the container for the internal communication fields
     * @return Ext.form.FieldSet
     */
    createInternalFieldSet: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.internal.title,
            defaults: {
                labelWidth: 155,
                labelStyle: 'font-weight: 700;'
            },
            layout: 'anchor',
            minWidth:250,
            items: me.createInternalElements()
        });
    },

    /**
     * Creates the container for the external communication fields
     * @return Ext.form.FieldSet
     */
    createExternalFieldSet: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.external.title,
            defaults: {
                labelWidth: 155,
                labelStyle: 'font-weight: 700;'
            },
            layout: 'anchor',
            minWidth:250,
            items: me.createExternalElements()
        });
    },

    /**
     * Creates the elements for the internal communication field set which is displayed on
     * top of the communication tab panel.
     * @return Array - Contains the description container, the text area for the internal comment and the save button.
     */
    createInternalElements: function() {
        var me = this;

        me.internalDescriptionContainer = Ext.create('Ext.container.Container', {
            style: 'color: #999; font-style: italic; margin: 0 0 15px 0;',
            html: me.snippets.internal.text
        });

        me.internalTextArea = Ext.create('Ext.form.field.TextArea', {
            name: 'internalComment',
            height: 90,
            anchor: '100%',
            cols: 4,
            allowBlank: true,
            grow: true
        });

        me.internalButton = Ext.create('Ext.button.Button', {
            style: 'margin: 8px 0;',
            cls: 'small primary',
            text: me.snippets.internal.button,
            handler: function() {
                me.record.set('internalComment', me.internalTextArea.getValue());
                me.fireEvent('saveInternalComment', me.record, me, {
                    callback: function (order) {
                        me.fireEvent('updateForms', order, me.up('window'));
                    },
                });
            }
        });

        return [me.internalDescriptionContainer, me.internalTextArea, me.internalButton];
    },

    /**
     * Creates the elements for the external communication field set which is displayed on
     * bottom of the communication tab panel.
     * @return Array - Contains the description container, the text area for the external and the customer comment and the save button.
     */
    createExternalElements: function() {
        var me = this;

        me.externalDescriptionContainer = Ext.create('Ext.container.Container', {
            style: 'color: #999; font-style: italic; margin: 0 0 15px 0;',
            html: me.snippets.external.text
        });

        me.externalTextArea = Ext.create('Ext.form.field.TextArea', {
            name: 'comment',
            height: 90,
            anchor: '100%',
            cols: 4,
            allowBlank: true,
            grow: true,
            fieldLabel: me.snippets.external.externalLabel
        });

        me.customerTextArea = Ext.create('Ext.form.field.TextArea', {
            height: 90,
            anchor: '100%',
            cols: 4,
            name: 'customerComment',
            allowBlank: true,
            grow: true,
            fieldLabel: me.snippets.external.customerLabel
        });

        me.externalButton = Ext.create('Ext.button.Button', {
            style: 'margin: 8px 0;',
            cls: 'small primary',
            text: me.snippets.external.button,
            handler: function() {
                me.record.set('customerComment', me.customerTextArea.getValue());
                me.record.set('comment', me.externalTextArea.getValue());

                me.fireEvent('saveExternalComment', me.record, me, {
                    callback: function (order) {
                        me.fireEvent('updateForms', order, me.up('window'));
                    },
                });
            }
        });

        return [me.externalDescriptionContainer, me.customerTextArea, me.externalTextArea, me.externalButton];
    }

});
//{/block}
