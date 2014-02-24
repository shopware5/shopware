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
 */

/**
 * todo@all: Documentation
 */

//{namespace name=backend/config/view/form}

//{block name="backend/config/view/template/detail"}
Ext.define('Shopware.apps.Config.view.template.Detail', {
    extend: 'Ext.form.Panel',
    alias: 'widget.config-template-detail',

    region: 'east',
    layout: 'form',
    border: false,
    width: 300,

    title: '{s name=template/detail/title}Details{/s}',

    autoScroll: true,
    collapsible: true,
    bodyPadding: 10,

    defaults: {
        xtype: 'displayfield',
        labelWidth: 120
    },

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: me.getItems()
        });

        me.callParent(arguments);
    },

    getImageField: function() {
        return {
            xtype: 'field', //imagefield
            cls: Ext.baseCSSPrefix + 'preview-image',
            hideEmptyLabel: true,
            name: 'previewThumb',
            basePath: '', //'{link file="templates/"}',
            setRawValue: function(value) {
                var me = this;
                value = Ext.value(value, '');
                me.rawValue = value;
                if (me.inputEl) {
                    me.inputEl.dom.src = value ? me.basePath + value : null;
                }
                return value;
            },
            fieldSubTpl: [
                '{literal}<div class="thumb"><div class="inner-thumb">',
                '<img id="{id}" type="{type}" ',
                '<tpl if="name">name={name}" </tpl>',
                'class="" />',
                '</div></div>{/literal}',
                {
                    compiled: true,
                    disableFormats: true
                }
            ]
        };
    },

    /**
     * @return array
     */
    getItems: function() {
        var me = this;
        return [me.getImageField(), {
            fieldLabel: '{s name=template/detail/name_label}Name{/s}',
            name: 'name',
            htmlEncode: true
        }, {
            fieldLabel: '{s name=template/detail/author_label}Author{/s}',
            name: 'author',
            htmlEncode: true
        }, {
            fieldLabel: '{s name=template/detail/license_label}License{/s}',
            name: 'license',
            htmlEncode: true
        }, {
            xtype: 'checkbox',
            fieldLabel: '{s name=template/detail/esi_label}Esi support{/s}',
            name: 'esi',
            disabled: true,
            disabledCls: ''
        }, {
            xtype: 'checkbox',
            fieldLabel: '{s name=template/detail/style_label}Style assistant{/s}',
            name: 'styleSupport',
            disabled: true,
            disabledCls: ''
        }, {
            xtype: 'checkbox',
            fieldLabel: '{s name=template/detail/emotion_label}Emotion support{/s}',
            name: 'emotion',
            disabled: true,
            disabledCls: ''
        }];
    }
});
//{/block}
