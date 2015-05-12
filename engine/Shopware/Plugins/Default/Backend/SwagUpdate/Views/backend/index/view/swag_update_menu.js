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

//{namespace name=backend/swag_update/main}
//{block name="backend/index/view/menu" append}
Ext.define('Shopware.apps.Index.view.SwagUpdateMenu', {
    override: 'Shopware.apps.Index.view.Menu',

    /**
     * @Override
     */
    initComponent: function() {
        var me = this, result;

        /*{if {acl_is_allowed privilege=read resource=swagupdate}}*/
        me.on('menu-created', function(items) {
            window.setTimeout(function() {
                me.performVersionCheck();
            }, 500);
        });
        /*{/if}*/

        result = me.callParent(arguments);

        return result;
    },

    /**
     * Triggers the version check
     */
    performVersionCheck: function() {
        var me = this,
                snippets;

        snippets = {
            title: '{s name=growl/update/title}A new version of Shopware is available{/s}',
            button: '{s name=growl/update/button}Display info{/s}',
            messageSticky: '{s name=growl/update/message}Version [0] of Shopware is available.{/s}'
        };

        /**
         * Perform the actual version check
         */
        Ext.Ajax.request({
            url: '{url controller=SwagUpdate action=popup}',
            async: true,
            success: function(response) {
                if (!response || !response.responseText) {
                    return;
                }

                var result = Ext.decode(response.responseText);

                if (!result.success) {
                    return;
                }

                // add badge class for help menu
                Ext.each(me.items.items, function(item) {
                    if (!item.iconCls) {
                        return true;
                    }

                    if (item.iconCls.indexOf('shopware-help-menu') > -1) {
                        item.addClass('x-btn-badge');
                    }
                });

                // Check if popups disabled for this version
                var skipVersion = localStorage.getItem('skipVersion');
                if (result.data.name == skipVersion) {
                    return;
                }

                // Create growl notification for the update
                Shopware.Notification.createStickyGrowlMessage({
                    title: snippets.title,
                    text: Ext.String.format(snippets.messageSticky, result.data.name),
                    btnDetail: {
                        text: snippets.button,
                        callback: function() {
                            Shopware.app.Application.addSubApplication({
                                name: 'Shopware.apps.SwagUpdate'
                            });
                        }
                    },

                    onCloseButton: function() {
                        /*{if {acl_is_allowed privilege=skipUpdate resource=swagupdate}}*/
                        Ext.MessageBox.confirm('{s name="skip_update"}Aktualisierung überspingen{/s}', '{s name="skip_update_question"}Möchten Sie die Meldungen für diese Aktualisierung dauerhaft deaktivieren?{/s}', skipUpdate);
                        /*{/if}*/
                    }
                });

                function skipUpdate(button) {
                    if (button == 'yes') {
                        var version = result.data.name,
                            skipVersion = localStorage.getItem('skipVersion');

                        // No version skipped before or an old version
                        if(!skipVersion || skipVersion != version) {
                            localStorage.setItem('skipVersion', version);
                        }

                        Shopware.Notification.createGrowlMessage('{s name="popups_disabled"}Meldungen deaktiviert{/s}', Ext.String.format('{s name="no_more_popups"}Es werden keine weiteren Meldungen für die Shopware Version [0] angezeigt.{/s}', version));
                    }
                }
            }
        });
    }
});
//{/block}
