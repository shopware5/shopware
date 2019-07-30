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

// {namespace name=backend/swag_update/main}
// {block name="backend/swag_update/view/security_update_window"}
Ext.define('Shopware.apps.SwagUpdate.view.SecurityUpdateWindow', {
    extend: 'Shopware.window.ExpiredPluginWarning',
    title: '{s name="security_update_window/title"}{/s}',

    getText: function() {
        return Ext.String.format('{s name="security_update_window/text"}{/s}', this.getChangelogUrl());
    },

    getChangelogUrl: function() {
        return Ext.String.format('https://issues.shopware.com/?swversion=[0]&status=5', this.updateVersion);
    },

    getWindowButtons: function() {
        var me = this;

        return [
            '->',
            {
                xtype: 'button',
                text: '{s name="security_update_window/cancel"}{/s}',
                cls: 'secondary',
                handler: function () {
                    me.onCloseButton();
                    me.destroy();
                }
            },
            {
                xtype: 'button',
                text: '{s name="security_update_window/update"}{/s}',
                cls: 'primary',
                handler: function () {
                    Shopware.app.Application.addSubApplication({
                        name: 'Shopware.apps.SwagUpdate',
                    });

                    me.destroy();
                }
            }
        ];
    },
});
// {/block}
