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

//{namespace name=backend/config/view/plugin}

//{block name="backend/config/view/plugin/detail"}
Ext.define('Shopware.apps.Config.view.plugin.Detail', {
    extend: 'Shopware.apps.Config.view.base.Detail',
    alias: 'widget.config-plugin-detail',

	snippets: {
		detail: {
			title_information: '{s name=detail/title_information}Information{/s}',
			author: '{s name=detail/author}Author{/s}',
			version: '{s name=detail/version}Version{/s}',
			copyright: '{s name=detail/copyright}Copyright{/s}',
			title_description: '{s name=detail/description}Description{/s}',
			active: '{s name=detail/active}Active{/s}'
		}
	},

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: me.getItems()
        });

        me.callParent(arguments);
    },

    /**
     * @return Array
     */
    getItems: function() {
        var me = this;
        return [{
            xtype: 'fieldset',
            bodyPadding: 5,
            title: me.snippets.detail.title_information,
            items: [{
                xtype: 'displayfield',
                name: 'author',
                fieldLabel: me.snippets.detail.author
            },{
                xtype: 'displayfield',
                name: 'version',
                fieldLabel: me.snippets.detail.version
            },{
                xtype: 'displayfield',
                name: 'copyright',
                fieldLabel: me.snippets.detail.copyright
            }]
        },{
            xtype: 'fieldset',
            bodyPadding: 5,
            title: me.snippets.detail.title_description,
            items: {
                xtype: 'displayfield',
                name: 'description'
            }
        },{
            xtype: 'config-element-boolean',
            name: 'active',
            fieldLabel: me.snippets.detail.active
        }];
    }
});
//{/block}
