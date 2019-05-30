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

//{namespace name=backend/config/view/main}

//{block name="backend/config/view/base/detail"}
Ext.define('Shopware.apps.Config.view.base.Detail', {
    extend: 'Ext.form.Panel',
    alias: 'widget.config-base-detail',

    region: 'east',
    layout: 'anchor',
    border: false,
    width: 450,

    title: '{s name=detail/title}Details{/s}',

    autoScroll: true,
    bodyPadding: '10 10 50 10',
    collapsible: true,
    disabled: true,

    defaults: {
        xtype: 'textfield',
        anchor: '100%',
        labelWidth: 120
    },

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: me.getItems(),
            buttons: me.getButtons()
        });

        me.callParent(arguments);
    },

    loadRecord: function(record) {
        var form = this.getForm();
        if(record) {
            form._record = record;
            form.setValues(record.data);
        } else {
            form._record = undefined;
            form.reset();
        }
        this.fireEvent('recordchange', this, record);
        form.fireEvent('recordchange', form, record);
    },

    updateRecord: function(record) {
        record = record || this.getRecord();
        var fields = record.fields.items,
            values = this.getForm().getFieldValues(),
            obj = {},
            i = 0,
            len = fields.length,
            name;

        for (; i < len; ++i) {
            name  = fields[i].name;

            if (values.hasOwnProperty(name)) {
                obj[name] = values[name];
            }
        }

        record.beginEdit();
        record.set(obj);
        record.endEdit();

        return this;
    },

    /**
     * @return array
     */
    getButtons: function() {
        var me = this;
        return [{
            text: '{s name=detail/reset_text}Reset{/s}',
            cls: 'secondary',
            action: 'reset'
        },{
            text: '{s name=detail/save_text}Save{/s}',
            cls: 'primary',
            tooltip:'{s name=detail/save_tooltip}Save (CTRL + S){/s}',
            action: 'save'
        }];
    },

    /**
     * @return array
     */
    getItems: function() {
        var me = this;
        return [];
    }
});
//{/block}
