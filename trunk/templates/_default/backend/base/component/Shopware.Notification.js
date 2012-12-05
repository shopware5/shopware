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
 * @package    Base
 * @subpackage Component
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Central Notification
 *
 * This class represents all used user response elements and notifications.
 *
 * The notifications are based on Twitter's bootstrap CSS toolkit (http://twitter.github.com/bootstrap/)
 * and are placed in the upper right corner of the user's viewport (except the block messages).
 */
Ext.define('Shopware.Notification', {
    extend: 'Ext.app.Controller',
    singleton: true,

    alternateClassName: [ 'Shopware.Messages', 'Shopware.Msg' ],

    requires: [ 'Ext.container.Container', 'Ext.panel.Panel', 'Ext.XTemplate' ],

    /**
     * Default type of the alert and block messages.
     *
     * Types include:
     * - notice: yellow message (default)
     * - info: blue message
     * - success: green message
     * - error: red message
     *
     * @string
     */
    baseMsgType: 'notice',

    /**
     * Duration after the alert and growl messages are hide (ms)
     *
     * @integer
     */
    hideDelay: 2500,

    /**
     * Used easing type for the fade in and fade out animation
     *
     * @string
     */
    easingType: 'easeIn',

    /**
     * Default animation speed for the alert and growl messages (ms)
     *
     * @integer
     */
    animationDuration: 200,

    /**
     * Default width of the alert messages (px)
     *
     * @integer
     */
    alertWidth: 350,

    /**
     * Default CSS class of the alert messsages.
     *
     * @string
     */
    alertMsgCls: 'alert-message',

    /**
     * Default CSS class of the block messages.
     *
     * @string
     */
    blockMsgCls: 'block-message',

    /**
     * Default CSS class of the growl messages.
     *
     * @string
     */
    growlMsgCls: 'growl-msg',

    /**
     * XTemplate for the alert message
     *
     * @array
     */
    alertMsgTpl: [
        '{literal}<tpl for=".">',
            '<div class="{type}">',
                '<tpl if="closeBtn">',
                    '<a href="#" class="close close-alert">x</a>',
                '</tpl>',
                '<p>',
                    '<tpl if="title">',
                        '<strong>{title}</strong>&nbsp;',
                    '</tpl>',
                    '{text}',
                '</p>',
            '</div>',
        '</tpl>{/literal}'
    ],

    /**
     * XTemplate for the block message
     *
     * @array
     */
    blockMsgTpl: [
        '{literal}<tpl for=".">',
            '<p>',
                '{text}',
            '</p>',
        '</tpl>{/literal}'
    ],

    /**
     * XTemplate for the growl messages
     *
     * @array
     */
    growlMsgTpl: [
        '{literal}<tpl for=".">',
            '<div class="growl-icon {iconCls}"></div>',
            '<div class="alert">',
                '<tpl if="title">',
                    '<div class="title">{title}</div>',
                '</tpl>',
                '<p class="text">{text}</p>',
            '</div>',
        '</tpl>{/literal}'
    ],

    /**
     * RegEx of the valid types for the alert and block messages
     *
     * @private
     * @string
     */
    _validTypes: /(notice|info|success|error)/i,

    /**
     * Sets the default type of the alert and block message.
     *
     * @param [string] type
     * @return [boolean]
     */
    setBaseMsgType: function(type) {
        if(!this.validBaseMsgType(type)) {
            return false;
        }

        this.baseMsgType = type;
        return true;
    },

    /**
     * Returns the default type of the alert and block message
     *
     * @return [string]
     */
    getBaseMsgType: function() {
        return this.baseMsgType;
    },

    /**
     * Checks if the passed message type is allowed
     *
     * @param [string] type
     * @return [null|string]
     */
    validBaseMsgType: function(type) {
        return type.match(this._validTypes);
    },

    /**
     * Sets the CSS class which is used by the alert message
     *
     * @param [string] cls - CSS class which is used by the alert messages
     */
    setAlertMsgCls: function(cls) {
      this.alertMsgCls = cls;
    },

    /**
     * Returns the CSS class of the alert message
     *
     * @return [string]
     */
    getAlertMsgCls: function() {
        return this.alertMsgCls;
    },

    /**
     * Sets the CSS class which is used by the block message
     *
     * @param [string] cls - CSS class which is used by the block messages
     */
    setBlockMsgCls: function(cls) {
      this.blockMsgCls = cls;
    },

    /**
     * Returns the CSS class of the block message
     *
     * @return [string]
     */
    getBlockMsgCls: function() {
        return this.blockMsgCls;
    },

    /**
     * Sets the CSS class which is used by the growl message
     *
     * @param [string] cls - CSS class which is used by the growl like messages
     */
    setGrowlMsgCls: function(cls) {
        this.growlMsgCls = cls;
    },

    /**
     * Returns the CSS class of the growl message
     *
     * @return [string]
     */
    getGrowlMsgCls: function() {
        return this.growlMsgCls;
    },

    /**
     * Creates an alert message based on the passed parameter's
     * and returns it
     *
     * @param [string] title - title of the message
     * @param [string] text - text of the message (HTML allowed)
     * @param [string] type - type of the message (see baseMsgType)
     * @param [boolean] closeBtn - show or hide close button<
     * @return [object] - Instance of the alert message
     */
    createMessage: function(title, text, type, closeBtn) {
        var me = this, alertMsg, msgData;

        if(!me.validBaseMsgType(type)) {
            type = false;
        }

        // Collect message data
        msgData = {
            title: title || false,
            text: text,
            type: type || this.baseMsgType,
            closeBtn: closeBtn || false
        };

        // Create message box
        alertMsg = Ext.create('Ext.container.Container', {
            ui: [ 'default', 'shopware-ui' ],
            data: msgData,
            cls: me.alertMsgCls,
            tpl: me.alertMsgTpl,
            width: me.alertWidth,
            renderTo: Ext.getBody(),
            style: 'opacity: 0'
        });
        alertMsg.update(msgData);

        // Fade out the alert message after the given delay
        var task = new Ext.util.DelayedTask(function() {
            me.closeAlertMessage(alertMsg, me, null);
        });
        task.delay(this.hideDelay);

        // Add close event to the close button
        if(closeBtn) {
            Ext.getBody().on('click', function(event) {
                me.closeAlertMessage(this, me, task);
            }, alertMsg, {
                delegate: '.close-alert'
            });
        }

        // Show the alert message
        alertMsg.getEl().fadeIn({
            opacity: 1,
            easing: me.easingType,
            duration: me.animationDuration
        });

        return alertMsg;
    },

    /**
     * Creates an error message
     *
     * @param [string] title - title of the message
     * @param [string] text - text of the message (HTML allowed)
     * @param [boolean] closeBtn - show or hide close button
     */
    createErrorMessage: function(title, text, closeBtn) {
        closeBtn = closeBtn || false;

        return this.createMessage(title, text, 'error', closeBtn);
    },

    /**
     * Creates a success message
     *
     * @param [string] title - title of the message
     * @param [string] text - text of the message (HTML allowed)
     * @param [boolean] closeBtn - show or hide close button
     */
    createSuccessMessage: function(title, text, closeBtn) {
        closeBtn = closeBtn || false;

        return this.createMessage(title, text, 'success', closeBtn);
    },

    /**
     * Creates a notice message
     *
     * @param [string] title - title of the message
     * @param [string] text - text of the message (HTML allowed)
     * @param [boolean] closeBtn - show or hide close button
     */
    createNoticeMessage: function(title, text, closeBtn) {
        closeBtn = closeBtn || false;

        return this.createMessage(title, text, 'notice', closeBtn);
    },

    /**
     * Creates an info message
     *
     * @param [string] title - title of the message
     * @param [string] text - text of the message (HTML allowed)
     * @param [boolean] closeBtn - show or hide close button
     */
    createInfoMessage: function(title, text, closeBtn) {
        closeBtn = closeBtn || false;

        return this.createMessage(title, text, 'info', closeBtn);
    },

    /**
     * Fades out the passed alert message and removes it from the DOM if the animation
     * is complete.
     *
     * @param [object] alertMsg - Instance of the alert message
     * @param [object] scope - Shopware.app.Notification
     * @param [object] task - Ext.util.DelayedTask
     * @return [boolean]
     */
    closeAlertMessage: function(alertMsg, scope, task) {
        if(task && Ext.isObject(task)) {
            task.cancel();
        }
        alertMsg.getEl().fadeOut({
            remove: true,
            easing: scope.easingType,
            duration: scope.animationDuration
        });

        return true;
    },

    /**
     * Creates a block message based on the passed parameter's
     * and returns it
     *
     * @param [string] title - Title of the message
     * @param [string] text - Text of the message (HTML allowed)
     * @param [string] type - Type of the message (default: notice, possible values: info = blue, notice = yellow, success = green, error = red)
     * @param [boolean] closeBtn - show or hide the close button
     * @return [object] - Instance of the block message
     */
    createBlockMessage: function(text, type) {
        var me = this, pnl, msgData, innerPnl;

        if(!me.validBaseMsgType(type)) {
            type = me.baseMsgType;
        }

        msgData = {
            text: text,
            type: type || me.baseMsgType
        };

        innerPnl = Ext.create('Ext.container.Container', {
            cls: [ me.blockMsgCls + '-inner' , type || me.baseMsgType ] ,
            data: msgData,
            margin: 1,
            padding: 7,
            plain: true,
            tpl: me.blockMsgTpl
        });

        pnl = Ext.create('Ext.container.Container', {
            cls: me.blockMsgCls  ,
            ui: 'shopware-ui',
            bodyCls: type || me.baseMsgType,
            items: [ innerPnl ]
        });
        innerPnl.update(msgData);

        return pnl;
    },

    /**
     * Creates a growl like message based on the passed parameter's
     * and returns it
     *
     * @param [string] title - Title of the message
     * @param [string] text - Text of the message
	 * @param [string] caller - The module, which called this function
     * @param [string] iconCls - Used icon class (default growl)
     * @param [boolean] log - If the growlMessage should be logged
     */
    createGrowlMessage: function(title, text, caller, iconCls, log) {
        var me = this, msgData, growlMsg;

		if(log != false){
			Ext.Ajax.request({
				url: '{url controller="Log" action="createLog"}',
				params: {
					type: 'backend',
					key: caller,
					text: text,
					user: userName,
					value4: ''
				},
				scope:this
			});
		}

        // Collect message data
        msgData = {
            title: title || false,
            text: text,
            iconCls: iconCls || 'growl'
        };

        // Create message box
        growlMsg = Ext.create('Ext.container.Container', {
            ui: [ 'default', 'shopware-ui' ],
            data: msgData,
            cls: me.growlMsgCls,
            tpl: me.growlMsgTpl,
            renderTo: Ext.getBody()
        });
        growlMsg.update(msgData);
        growlMsg.getEl().setStyle('opacity', 1);

        // Fade out the growl like message after the given delay
        var task = new Ext.util.DelayedTask(function() {
            me.closeGrowlMessage(growlMsg, me, task);
        });
        task.delay(this.hideDelay);

        return growlMsg;
    },

    /**
     * Fades out the passed growl like message and removes
     * it from the DOM
     *
     * @param [object] msg - Instance of the growl message
     * @param [object] scope - Instance of Shopware.app.Notification
     * @param [object] task - Instance of Ext.util.DelayedTask
     * @return [boolean]
     */
    closeGrowlMessage: function(msg, scope, task) {
        if(task && Ext.isObject(task)) {
            task.cancel();
        }

        msg.getEl().setStyle('opacity', 0);
        Ext.defer(function() {
            msg.destroy();
        }, 210);

        return true;
    }
});