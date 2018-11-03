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
 * @category    Shopware
 * @package     Base
 * @subpackage  Attribute
 * @version     $Id$
 * @author      shopware AG
 */

//{namespace name="backend/attributes/main"}
//{block name="backend/base/attribute/window"}
Ext.define('Shopware.attribute.Window', {
    extend: 'Enlight.app.Window',
    table: null,
    layout: 'border',
    iconCls: 'sprite-attributes',
    allowTranslation: true,
    footerButton: false,

    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.dockedItems = me.createDockedItems();
        me.callParent(arguments);

        if (me.record) {
            me.loadAttribute(me.record);
        }
    },

    loadAttribute: function(record) {
        var me = this;
        me.record = record;
        me.attributeForm.loadAttribute(record.get('id'));
        me.setTitle(this.getRecordTitle(record));
    },

    saveAttribute: function() {
        var me = this;
        me.attributeForm.saveAttribute(
            me.record.get('id'),
            function() {
                me.destroy();
            }
        );
    },

    createItems: function() {
        return [this.createForm()];
    },

    createDockedItems: function() {
        return [this.createToolbar()];
    },

    createToolbar: function() {
        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: this.createToolbarItems()
        });
    },

    createToolbarItems: function() {
        return [
            '->',
            this.createCancelButton(),
            this.createSaveButton()
        ];
    },

    createSaveButton: function() {
        var me = this;
        me.saveButton = Ext.create('Ext.button.Button', {
            text: '{s name="save_button"}{/s}',
            cls: 'primary',
            handler: function() {
                me.saveAttribute();
            }
        });
        return me.saveButton;
    },

    createCancelButton: function() {
        var me = this;
        me.cancelButton = Ext.create('Ext.button.Button', {
            text: '{s name="cancel_button"}{/s}',
            cls: 'secondary',
            handler: function() {
                me.destroy();
            }
        });
        return me.cancelButton;
    },

    createForm: function() {
        var me = this;

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            table: me.table,
            bodyPadding: 20,
            region: 'center',
            autoScroll: true,
            allowTranslation: me.allowTranslation
        });

        return me.attributeForm;
    },

    getRecordTitle: function(record) {
        var me = this;

        if (me.title) {
            return me.title;
        }

        var plain = '{s name="attribute_window_title"}{/s}';
        if (!record) {
            return plain;
        }

        var prefix = '{s name="attribute_window_title"}{/s}' + ': ';
        if (record.get('name')) {
            return prefix + record.get('name');
        }
        if (record.get('label')) {
            return prefix + record.get('label');
        }
        if (record.get('description')) {
            return prefix + record.get('description');
        }
        if (record.get('number')) {
            return prefix + record.get('number');
        }
        return plain;
    }
});
//{/block}
