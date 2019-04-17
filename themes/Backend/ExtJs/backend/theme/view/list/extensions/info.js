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
 * Shopware Application
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/theme/main}

//{block name="backend/theme/view/list/extensions/info"}

Ext.define('Shopware.apps.Theme.view.list.extensions.Info', {
    extend: 'Shopware.listing.InfoPanel',
    alias: 'widget.theme-listing-info-panel',
    cls: 'theme-info-panel',
    width: 225,

    configure: function() {
        return {
            model: 'Shopware.apps.Theme.model.Theme',
            fields: {
                screen: '{literal}<div class="screen"><img src="{screen}" alt="{name}" /></div>{/literal}',
                name: '<div class="info-item"> <p class="label">{s name=name}Name{/s}:</p> <p class="value">{literal}{name}{/literal}</p> </div>',
                author: '<div class="info-item"> <p class="label">{s name=author}Author{/s}:</p> <p class="value">{literal}{author}{/literal}</p> </div>',
                license: '<div class="info-item"> <p class="label">{s name=license}License{/s}:</p> <p class="value">{literal}{license}{/literal}</p> </div>',
                path: '<div class="info-item"> <p class="label">{s name=path}Path{/s}:</p> <p class="value" style="word-wrap:break-word;">{literal}{path}{/literal}</p> </div>',
                description: '<div class="info-item"> <p class="label">{s name=description}Description{/s}:</p> <p class="value">{literal}{description}{/literal}</p> </div>'
            }
        };
    },

    initComponent: function() {
        var me = this;

        /*{if {acl_is_allowed privilege=changeTheme} || {acl_is_allowed privilege=preview} || {acl_is_allowed privilege=configureTheme}}*/
        me.dockedItems = [ me.createToolbar() ];
        /*{/if}*/

        me.callParent(arguments);
    },

    createInfoView: function() {
        var panel = this.callParent(arguments);
        panel.padding = 15;
        return panel;
    },

    createToolbar: function() {
        var me = this;

        return Ext.create('Ext.panel.Panel', {
            unstyled: true,
            dock: 'bottom',
            bodyPadding: '7 0',
            defaults: {
                margin: '4 20'
            },
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [
                /*{if {acl_is_allowed privilege=changeTheme}}*/
                me.createAssignButton(),
                /*{/if}*/
                /*{if {acl_is_allowed privilege=preview}}*/
                me.createPreviewButton(),
                /*{/if}*/
                /*{if {acl_is_allowed privilege=configureTheme}}*/
                me.createConfigureButton()
                /*{/if}*/
            ]
        });
    },

    createAssignButton: function () {
        var me = this;

        me.assignButton = Ext.create('Ext.button.Button', {
            text: '{s name=assign}Select theme{/s}',
            cls: 'small primary',
            disabled: true,
            handler: function() {
                me.fireEvent('assign-theme', me);
            }
        });


        return me.assignButton;
    },

    createPreviewButton: function () {
        var me = this;

        me.previewButton = Ext.create('Ext.button.Button', {
            text: '{s name=preview}Preview theme{/s}',
            disabled: true,
            cls: 'small',
            handler: function() {
                me.fireEvent('preview-theme', me);
            }
        });

        return me.previewButton;
    },

    createConfigureButton: function() {
        var me = this;

        me.configureButton = Ext.create('Ext.button.Button', {
            text: '{s name=configure}Configure theme{/s}',
            disabled: true,
            cls: 'small',
            handler: function() {
                me.fireEvent('configure-theme', me);
            }
        });

        return me.configureButton;
    },

    checkRequirements: function() { },
    addEventListeners: function() { }

});

//{/block}
