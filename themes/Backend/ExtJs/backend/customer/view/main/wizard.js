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
 * @package    Customer
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/main}
// {block name="backend/customer/view/main/wizard"}
Ext.define('Shopware.apps.Customer.view.main.Wizard', {
    extend: 'Ext.window.Window',
    layout: 'fit',
    autoShow: false,
    modal: true,
    cls: 'plugin-manager-loading-mask customer-wizard',
    bodyPadding: 20,
    header: false,
    width: 1250,
    height: 540,

    initComponent: function() {
        var me = this;

        me.dockedItems = me.createDockedItems();

        me.items = me.createItems();

        me.callParent(arguments);
    },

    nextPage: function() {
        var me = this;
        var layout = me.cardContainer.getLayout();

        me.nextButton.setText('{s name="next"}{/s}');
        me.previousButton.show();

        if (layout.getNext()) {
            layout.next();

            if (!layout.getNext()) {
                me.nextButton.setText('{s name="finish"}{/s}');
            }
        } else {
            me.finish();
        }
    },

    previousPage: function() {
        var me = this;
        var layout = me.cardContainer.getLayout();

        me.nextButton.setText('{s name="next"}{/s}');

        if (layout.getPrev()) {
            layout.prev();
        }
        me.previousButton.show();
        if (!layout.getPrev()) {
            me.previousButton.hide();
        }
    },

    finish: function() {
        var me = this;
        me.fireEvent('finish');
        me.destroy();
    },

    createItems: function() {
        var me = this;

        me.cardContainer = Ext.create('Ext.container.Container', {
            region: 'center',
            layout: 'card',
            items: [
                me.createFirstPage(),
                me.createSecondPage(),
                me.createFinishPage()
            ]
        });
        return [me.cardContainer];
    },

    createFirstPage: function() {
        var image = '{link file="backend/_resources/images/customer_stream/quick_view.jpg"}';
        if (Ext.userLanguage === 'de') {
            image = '{link file="backend/_resources/images/customer_stream/quick_view_de.jpg"}';
        }

        return Ext.create('Ext.container.Container', {
            html: '' +
            '<h1 class="headline">' +
                '{s name="wizard_customer_quick_view_headline"}{/s}' +
            '</h1>' +
            '<div class="description">' +
                '{s name="wizard_customer_quick_view_text"}{/s}' +
            '</div>' +
            '<div class="image">' +
                '<img src="' + image + '" />' +
            '</div>'
        });
    },

    createSecondPage: function() {
        var image = '{link file="backend/_resources/images/customer_stream/stream_view.jpg"}';
        if (Ext.userLanguage === 'de') {
            image = '{link file="backend/_resources/images/customer_stream/stream_view_de.jpg"}';
        }

        return Ext.create('Ext.container.Container', {
            html: '' +
            '<h1 class="headline">' +
                '{s name="wizard_stream_view_headline"}{/s}' +
            '</h1>' +
            '<div class="description">' +
                '{s name="wizard_stream_view_text"}{/s}' +
            '</div>' +
            '<div class="image">' +
                '<img src="' + image + '" />' +
            '</div>'
        });
    },

    createFinishPage: function() {
        var image = '{link file="backend/_resources/images/customer_stream/ekw_usage.jpg"}';
        if (Ext.userLanguage === 'de') {
            image = '{link file="backend/_resources/images/customer_stream/ekw_usage_de.jpg"}';
        }

        return Ext.create('Ext.container.Container', {
            html: '' +
            '<h1 class="headline">' +
                '{s name="wizard_usage_headline"}{/s}' +
            '</h1>' +
            '<div class="description">' +
                '{s name="wizard_usage_text"}{/s}' +
            '</div>' +
            '<div class="image">' +
                '<img src="' + image + '" />' +
            '</div>'
        });
    },

    createDockedItems: function() {
        var me = this;

        me.nextButton = Ext.create('Ext.button.Button', {
            text: '{s name="next"}{/s}',
            cls: 'primary',
            handler: Ext.bind(me.nextPage, me)
        });

        me.previousButton = Ext.create('Ext.button.Button', {
            text: '{s name="back"}{/s}',
            cls: 'secondary',
            hidden: true,
            handler: Ext.bind(me.previousPage, me)
        });

        me.skipButton = Ext.create('Ext.button.Button', {
            text: '{s name="skip"}{/s}',
            cls: 'secondary',
            handler: Ext.bind(me.finish, me)
        });

        return [{
            xtype: 'toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            items: [me.skipButton, '->', me.previousButton, me.nextButton]
        }];
    }
});
// {/block}
