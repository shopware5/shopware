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
 * @package    App
 * @subpackage Growl
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware.app.Growl
 *
 * Create our growl controller
 * which reprents the application wide
 * notification system.
 */
//{block name="backend/growl/growl"}
Ext.define('Shopware.app.Growl', {
    extend: 'Shopware.app.Controller',
    singleton: true,

    /** Time (in Milliseconds) to fade out the growl message */
    timeout: 5000,

    /** Message counter */
    count: 0,

    /** Offset (in pixels) between the growl messages */
    offsetTop: 55,

    /** Holds the navigation height of the main menu (Shopware.app.Menu) */
    naviHeight: null,

    /** Suffix for the data attribute */
    dataSuffix: 'growl-index',

    /**
     * Opens a new growl message based on the passed parameters.
     *
     * @param title
     * @param text
     */
    open: function(title, text, iconCls) {
        var message, task, me = this, top;

        iconCls = iconCls || 'growl';

        me.naviHeight = me.naviHeight || Shopware.app.Application.navigationHeight;

        top = me.naviHeight + (me.offsetTop * me.count);
        me.count++;

        message = me.createDomElement(title, text, iconCls, top);
        message = Ext.get(message);

        // Create a new delayed task to fade out the growl message
        task = new Ext.util.DelayedTask(function() {
            me.close(message);
        });
        task.delay(this.timeout);
    },

    /**
     * Creates the neccessary DOM structure for the growl message
     *
     * @param title - Title of the message
     * @param text - Text of the message
     * @param iconCls - The icon class
     * @param top - Offset top
     * @return message - Ext.Element
     */
    createDomElement: function(title, text, iconCls, top) {
        var me = this;

        var message = Ext.DomHelper.append(
            Ext.getBody(),
            [{
                tag: 'div',
                cls: 'sw4-growl',
                style: 'top:' + top +'px',
                children: [{
                    tag: 'div',
                    cls: 'icon' + iconCls
                }, {
                    tag: 'div',
                    cls: 'alert',
                    children: [{
                        tag: 'div',
                        cls: 'title',
                        html: title
                    }, {
                        tag: 'div',
                        cls: 'text',
                        html: text
                    }]
                }]
            }]
        );

        message.setAttribute('data-' + me.dataSuffix, me.count);

        return message;
    },

    /**
     * Close the passed growl message.
     *
     * Note that this method needs the DOM element to remove the message
     *
     * @param message
     */
    close: function(message) {
        this.count--;

        message.fadeOut({
            duration: 350,
            callback: function() {
                message.destroy();
            }
        });
    }
});
//{/block}
