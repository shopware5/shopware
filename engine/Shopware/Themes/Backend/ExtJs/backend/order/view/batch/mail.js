/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/order/main}

/**
 * Shopware UI - Order batch window
 *
 * todo@all: Documentation
 */
//{block name="backend/order/view/batch/mail"}
Ext.define('Shopware.apps.Order.view.batch.Mail', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend:'Ext.form.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.batch-mail-panel',

    /**
     * An optional extra CSS class that will be added to this component's Element.
     */
    cls: Ext.baseCSSPrefix + 'batch-mail-panel',

    /**
     * Component layout definition
     * @object
     */
    layout: {
        align: 'stretch',
        type: 'vbox'
    },
    /**
     * Set flex value
     * @int
     */
    flex: 1,

    /**
     * A shortcut for setting a padding style on the body element. The value can either be a number to be applied to all sides, or a normal css string describing padding.
     * @int
     */
    bodyPadding: 10,

    /**
     * Enable collapse mode.
     * @boolean
     */
    collapsible: true,

    /**
     * Define that the panel collapse to right
     * @string
     */
    collapseDirection: 'right',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title: '{s name=mail/title}Send an email to the customer{/s}',
        subject: '{s name=subject}Subject{/s}',
        to: '{s name=to}To{/s}',
        button: '{s name=button}Send mail{/s}'
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

        me.items = me.getFormItems();
        me.collapsible = (me.mode !== 'single');

        me.title = me.snippets.title;
        me.callParent(arguments);
    },

    /**
     *
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the "generate documents" button which is
             * displayed within the form field set.
             *
             * @event
             * @param [Ext.form.Panel] - This component
             */
            'sendMail'
        );
    },

    /**
     *
     */
    getFormItems: function() {
        var me = this;


        return [
            {
                xtype: 'textfield',
                name: 'to',
                fieldLabel: me.snippets.to
            },
            {
                xtype: 'textfield',
                name: 'subject',
                fieldLabel: me.snippets.subject
            },
            {
                xtype: 'textarea',
                name: 'content',
                minHeight: 90,
                flex: 1
            },
            {
                xtype: 'button',
                text: me.snippets.button,
                handler: function() {
                    me.fireEvent('sendMail', me)
                }
            }
        ];
    }

});
//{/block}
