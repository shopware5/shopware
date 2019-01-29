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
 */

/**
 * todo@all: Documentation
 */
Ext.define('Shopware.apps.Config.view.element.Button', {
    extend: 'Ext.button.Button',
    alias: [
        'widget.config-element-button',
        'widget.config-element-controllerbutton'
    ],
    initComponent:function () {
        var me = this;

        // Add support of open action button
        if (me.controller) {
            me.handler = function() {
                window.openAction(me.controller);
            }
        // Add support of own button handler
        } else if (typeof me.handler === 'string' && me.handler.indexOf('function') !== -1) {
            eval('me.handler =' + me.handler + ';');
        }

        me.disabled = false;

        // Move field label to button text
        if(me.fieldLabel) {
            me.text = me.fieldLabel;
            delete me.fieldLabel;
        }

        me.callParent(arguments);
    }
});
