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
 * @package    SwagMigration
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/swag_migration/main"}

/**
 * Shopware UI - Main Wizard
 */
//{block name="backend/swag_migration/view/wizard"}
Ext.define('Shopware.apps.SwagMigration.view.Wizard', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.migration-wizard',
//    title: '{s name=admin}Administration{/s}',
    layout: 'card',
    bodyBorder: 0,
    border: false,
    defaults: {
        bodyBorder: 0
    },

    /**
     * Initializes the component, sets up toolbar and pagingbar and and registers some events
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        // Create the items of the container
        me.items = me.createItems();
        me.bbar = me.createBottomBar();

        me.addEvents([
            /**
            * Fired when one of the buttons in the bottom toolbar was clicked
            */
            'navigate'
        ]);

        me.callParent(arguments);
    },

    /**
     * Creates the items for the card layout
     * @return Array
     */
    createItems: function() {
        var me = this;

        return [{
            internalId: 0,
            xtype: 'migration-form-database',
            profileStore: me.profileStore,
            databaseStore: me.databaseStore
        }, {
            internalId: 1,
            xtype: 'migration-form-mapping',
            mappingStoreLeft: me.mappingStoreLeft,
            mappingStoreRight: me.mappingStoreRight
        }, {
            internalId: 2,
            xtype: 'migration-form-import'
        }]

    },

    /**
     * Creates the bottom toolbar which will enable the user to switch between the different migration steps
     */
    createBottomBar: function() {
        var me = this;

        me.buttonPrev = Ext.create(Ext.button.Button, {
            text: '{s name=prevBtn}Back{/s}',
            cls: 'secondary',
            handler: function(btn) {
                me.fireEvent('navigate', btn.up("panel"), "prev");
            },
            disabled: true
        });

        me.buttonNext = Ext.create(Ext.button.Button, {
            text: '{s name=nextBtn}Next{/s}',
            cls: 'primary',
            handler: function(btn) {
                me.fireEvent('navigate', btn.up("panel"), "next");
            }
        });

        return [
            me.buttonPrev,
            '->', // greedy spacer so that the buttons are aligned to each side
            me.buttonNext
        ]
    }


});
//{/block}