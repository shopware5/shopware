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
 * @package    Mail
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/mail/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/mail/view/main/window"}
Ext.define('Shopware.apps.Mail.view.main.Window', {
    extend: 'Enlight.app.Window',
    alias : 'widget.mail-main-window',
    title : '{s name=title}Email templates{/s}',
    layout: 'border',
    width: '70%',
    height: '90%',
    stateful: true,
    stateId: 'shopware-mail-window',

    /**
     * Initializes the component and builds up the main interface
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = [{
            xtype: 'mail-main-navigation',
            region: 'west',
            store: me.treeStore
        }, {
            xtype: 'mail-main-form',
            region: 'center',
            attachmentStore: me.attachmentStore
        }, {
            xtype: 'mail-main-info',
            region: 'east'
        }];

        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'top',
            ui: 'shopware-ui',
            items: me.createTopToolbar()
        }, {
            xtype: 'toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            items: me.createBottomToolbar()
        }];

        me.callParent(arguments);
    },

    /**
     * Creates the top toolbar.
     *
     * @return [object] generated Ext.toolbar.Toolbar
     */
    createTopToolbar: function() {
        var items = [];

        /*{if {acl_is_allowed privilege=create}}*/
            items.push({
                xtype: 'button',
                iconCls: 'sprite-mail--plus',
                text: '{s name=button_add_template}Add template{/s}',
                action: 'mail-window-add'
            });
        /*{/if}*/

        /*{if {acl_is_allowed privilege=delete}}*/
            items.push({
                xtype: 'button',
                iconCls: 'sprite-mail--minus',
                text: '{s name=button_delete_templates}Delete selected templates{/s}',
                action: 'mail-window-delete',
                disabled: true
            });
        /*{/if}*/

        /*{if {acl_is_allowed privilege=create}}*/
            items.push({
                xtype: 'button',
                iconCls: 'sprite-blue-document-copy',
                text: '{s name=button_duplicate_templates}Duplicate selected templates{/s}',
                action: 'mail-window-copy',
                disabled: true
            });
        /*{/if}*/

        items.push('->');

        items.push({
            xtype : 'textfield',
            name : 'searchfield',
            action : 'search',
            width: 170,
            cls: 'searchfield',
            enableKeyEvents : true,
            checkChangeBuffer: 300,
            emptyText : '{s name=toolbar_search}Search...{/s}'
        });

        items.push({
            xtype: 'tbspacer',
            width: 6
        });

        return items;
    },

    /**
     * Creates the bottom toolbar.
     *
     * @return [object] generated Ext.toolbar.Toolbar
     */
    createBottomToolbar: function() {
        var items  = [];

        items.push('->');

        /*{if {acl_is_allowed privilege=create} || {acl_is_allowed privilege=update}}*/
            items.push({
                xtype: 'button',
                text: '{s name=button_reset_template}Reset template{/s}',
                action: 'mail-window-reset',
                cls: 'secondary',
                disabled: true
            });
        /*{/if}*/

        /*{if {acl_is_allowed privilege=create} || {acl_is_allowed privilege=update}}*/
            items.push(Ext.create('Ext.button.Button', {
                text: '{s name=button_save_template}Save template{/s}',
                action: 'mail-window-save',
                cls: 'primary',
                formBind: true
            }));
        /*{/if}*/

        return items;
    }
});
//{/block}
