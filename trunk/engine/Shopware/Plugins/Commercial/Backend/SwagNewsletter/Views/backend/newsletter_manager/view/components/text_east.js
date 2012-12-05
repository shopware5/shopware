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
 * @package    NewsletterManager
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/swag_newsletter/main"}

//{block name="backend/newsletter_manager/view/components/text_east"}
Ext.define('Shopware.apps.NewsletterManager.components.TextEast', {
    extend: 'Ext.panel.Panel',
    collapsed: true,
    collapsible: true,
    title: '{s name=personalizeNewsletter}Personalize Newsletter{/s}',
    region: 'east',
    width: 300,
    alias: 'widget.newsletter-components-text-east',
    autoScroll:true,
    /**
     * Init the main detail component, add components
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = [ me.createContainer() ];
        me.callParent(arguments);
    },

    /**
     * Creates the main container, sets layout and adds the components needed
     * @return Ext.container.Container
     */
    createContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            border: false,
            padding: 10,

            layout: {
                type: 'vbox',
                align : 'stretch',
                pack  : 'start'
            },
            items: [
                me.createInfoText()
            ]
        });
    },

    /**
     * Creates and returns a simple label which will later inform the user about the possible options (voucher / mail)
     * @return Ext.form.Label
     */
    createInfoText: function()  {
        var me = this,
            html;

        html = "{s name=personalizeNewsletterContent}{literal}Personalize your newsletter using these variables:<br />\
        <br />\
        <b>Recipient's address:</b><br />\
        {$sUser.newsletter}<br >\
        <br />\
        <b>Recipient's first name:</b><br />\
        {$sUser.firstname}<br />\
        <br />\
        <b>Recipient's last name:</b><br />\
        {$sUser.lastname}<br />\
        <br />\
        <b>Recipient's salutation:</b><br />\
        {$sUser.salutation}<br />\
        <br />\
        <b>Recipient's street:</b><br />\
        {$sUser.street}<br />\
        <br />\
        <b>Recipient's street number:</b><br />\
        {$sUser.streetnumber}<br />\
        <br />\
        <b>Recipient's zip code:</b><br />\
        {$sUser.zipcode}<br />\
        <br />\
        <b>Recipient's city:</b><br />\
        {$sUser.city}<br />\
        <br />\
        In order to greet your customer depending on his/her sex, you might want to use:<br />\
        <br />\
        {if $sUser.salutation == 'mr'}Mr{/if}{if $sUser.salutation == 'ms'}Ms{/if}\
                {/literal}{/s}";

        me.infoLabel = Ext.create('Ext.form.Label', {
            html: html,
            padding: '0 0 20 0'
        });
        return me.infoLabel;
    }
});
//{/block}