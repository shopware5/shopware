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
 * @author     Oliver Denter
 * @author     $Author$
 */

//{namespace name=backend/order/main}

/**
 * Shopware UI - Order detail page
 *
 * todo@all: Documentation
 */
//{block name="backend/order/view/detail/heidelpay"}
Ext.define('Shopware.apps.Order.view.detail.Heidelpay', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend:'Ext.form.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.order-heidelpay-panel',

    /**
     * An optional extra CSS class that will be added to this component's Element.
     */
    cls: Ext.baseCSSPrefix + 'heidelpay-panel',

    /**
     * A shortcut for setting a padding style on the body element. The value can either be a number to be applied to all sides, or a normal css string describing padding.
     */
    bodyPadding: 0,

    /**
     * True to use overflow:'auto' on the components layout element and show scroll bars automatically when necessary, false to clip any overflowing content.
     */
    autoScroll: true,

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title: '{s name=tab/heidelpay}Heidelpay{/s}',
        noheidelpay: '{s name=tab/heidelpay/noheidelpay}This order was not paid over Heidelpay.{/s}',
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
        
        me.items = [
            me.createIFrameContainer(),
        ];
        me.callParent(arguments);
        me.title = me.snippets.title;
        //me.loadRecord(me.record);
    },

    createIFrameContainer: function() {
        var me = this;

        var myFrame = Ext.create('Ext.panel.Panel', {
          title: false,// 'Heidelpay Tools',
          padding: 0,
          bodyPadding: 0,
          border: 0,
          margin: 0,
          preventHeader: true,
          width: '100%',
          height: 525,
          top: 0,
          html: '<iframe src="backend/HeidelBooking?uid='+me.record.get('attribute2')+'&cid='+me.record.get('attribute5')+'" frameborder="0"></iframe>',
          //renderTo: Ext.getBody()
        });

        if (me.record.get('attribute6') == 'HEIDELPAY') {
          return myFrame;
        }

        return Ext.create('Ext.panel.Panel', {
            layout: 'column',
            html: '<br><br><h2>'+me.snippets.noheidelpay+'</h2><br><br>',
            renderTo: Ext.getBody()
        });
    }


});
//{/block}
