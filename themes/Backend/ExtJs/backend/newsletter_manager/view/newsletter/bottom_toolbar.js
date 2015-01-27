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
 * @package    NewsletterManager
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/newsletter_manager/main"}

/**
 * Shopware UI - Basic bottom toolbar showing close and save buttons in 'settings' and 'editor' view
 */
//{block name="backend/newsletter_manager/view/toolbar"}
Ext.define('Shopware.apps.NewsletterManager.view.newsletter.BottomToolbar', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.newsletter-manager-bottom-toolbar',

    /**
     * Initializes the component and registers some events
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = me.getItems();

        me.addEvents(
            /**
             * Fired when the user clicks the "back to overview" button
             */
            'backToOverview',

            /**
             * Fired when the users chooses to save the mail
             */
            'saveMail'
        );

        me.callParent(arguments);

    },

    /**
     * Creates the items for the toolbar
     * @return Array
     */
    getItems: function() {
        var me = this;

        me.saveButton = Ext.create('Ext.button.Button', {
            text: '{s name=save}Save{/s}',
            cls: 'primary',
            handler: function() {
                me.fireEvent('saveMail');
            },
            disabled: true
        });

        return [
                '->',
                {
                    xtype: 'button',
                    cls: 'secondary',
                    text: '{s name=backToOverview}Back to overview{/s}',
                    handler: function() {
                        me.fireEvent('backToOverview');
                    }
                },
                me.saveButton
            ];
    }

});
//{/block}
