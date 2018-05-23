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
 * @package    MediaManager
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/media_manager/view/main}

/**
 * Shopware UI - Media Manager Album Add
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/media_manager/view/album/add"}
Ext.define('Shopware.apps.MediaManager.view.album.Add', {
    extend: 'Ext.window.Window',
    title: '{s name="addAlbumTitle"}Mediamanager - Add Album{/s}',
    alias: 'widget.mediamanager-album-add',
    border: false,
    width: 400,
    autoShow: true,
    parentId: null,
    snippets: {
        albumName: '{s name="addAlbum/albumName"}Album name{/s}',
        add: '{s name="addAlbum/add"}Add album{/s}',
        cancel: '{s name="addAlbum/cancel"}Cancel{/s}'
    },

    initComponent: function() {
        var me = this;

        me.nameField = Ext.create('Ext.form.field.Text', {
            fieldLabel: me.snippets.albumName,
            labelWidth: 200,
            name: 'text',
            allowBlank: false,
            enableKeyEvents: true,
            listeners: {
                scope: me,
                keypress: function(textfield, e) {
                    if (e.getKey() === Ext.EventObject.ENTER && textfield.isValid()) {
                        me.down('#savebtn').fireEvent('click', me.down('#savebtn'));
                    }
                }
            }
        });

        me.formPanel = Ext.create('Ext.form.Panel', {
            bodyPadding: 12,
            defaults: {
                labelStyle: 'font-weight: 700'
            },
            items: [ me.nameField ]
        });

        // If we're adding a sub album, we need the parent id
        if(me.parentId) {
            me.parentIdField = Ext.create('Ext.form.field.Hidden', {
                name: 'parentId',
                value: me.parentId
            });
            me.formPanel.add(me.parentIdField);
        }

        me.items = [ me.formPanel ];
        me.dockedItems = [{
           xtype: 'toolbar',
           dock: 'bottom',
           ui: 'shopware-ui',
           cls: 'shopware-toolbar',
           items: me.createActionButtons()
        }];

        me.on('show', function() {
            me.nameField.focus(false, 200);
        });

        me.callParent(arguments);
    },

    createActionButtons: function() {
        var me = this;

        me.closeBtn = Ext.create('Ext.button.Button', {
            text: me.snippets.cancel,
            cls: 'secondary',
            handler: function(btn) {
                var win = btn.up('window');
                win.destroy();
            }
        });

        this.addBtn = Ext.create('Ext.button.Button', {
            text: me.snippets.add,
            itemId: 'savebtn',
            action: 'mediamanager-album-add-add',
            cls: 'primary'
        });

        return [ '->', this.closeBtn, this.addBtn ];
    }
});
//{/block}
