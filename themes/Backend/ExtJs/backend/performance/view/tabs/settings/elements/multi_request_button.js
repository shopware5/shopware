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
 * @subpackage Order
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/performance/main}

/**
 * Button which triggers the multi request dialog
 */
//{block name="backend/performance/view/tabs/settings/elements/multi_request_button"}
Ext.define('Shopware.apps.Performance.view.tabs.settings.elements.MultiRequestButton', {
    /**
     * Extend from our base grid
     * @string
     */
    extend:'Ext.container.Container',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.performance-multi-request-button',

    /**
     * Event and title needs to be passed as config params
     */
    event: '',
    title: '',
    showEvent: 'showMultiRequestDialog',

    /**
     * Initialize the button
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.items = [ me.createButton() ];

        me.callParent(arguments);
    },

    createButton: function() {
        var me = this;

        return {
            xtype: 'button',
            cls: 'primary',
            margin: '0 0 10 0',
            text: me.title,
            handler: function() {
                me.fireEvent(me.showEvent, me.event, me.up('fieldset'));
            }
        };
    }

});
//{/block}
