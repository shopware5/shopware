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
 * Shopware First Run Wizard - Finish tab
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/first_run_wizard/main}
//{block name="backend/first_run_wizard/view/main/finish"}

Ext.define('Shopware.apps.FirstRunWizard.view.main.Finish', {
    extend: 'Ext.container.Container',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.first-run-wizard-finish',

    /**
     * Name attribute used to generate event names
     */
    name:'finish',

    snippets: {
        content: {
            title: '{s name=finish/content/title}Finished{/s}',
            message: '{s name=finish/content/message}The First Run Wizard is now complete and you are ready to start using your new Shopware shop. Information, help, and the latest Shopware news can be found in the following pages:{/s}'
        },
        shopware_id: {
            title: '{s name=finish/shopware_id/title}Shopware ID{/s}',
            descriptionMessage: '{s name=finish/shopware_id/description_message}Here you can create you personal Shopware ID. The Shopware ID will give you access to your Shopware account in our forum, wiki and other community resources. It will also grant you access to our plugin store, where you can find many more plugins that will help you easily customize your shop to your needs.{/s}',
        },
        buttons: {
            finish: '{s name=finish/buttons/finish}Finish{/s}'
        }
    },

    initComponent: function() {
        var me = this,
            content = me.snippets.content;

        me.items = [
            {
                xtype: 'container',
                border: false,
                style: 'font-weight: 700; line-height: 20px;',
                html: '<h1>' + content.title + '</h1>'
            },
            {
                xtype: 'container',
                border: false,
                style: 'margin-bottom: 20px;',
                html: '<p>' + content.message + '</p>',
                width: '100%'
            },
            {
                xtype: 'container',
                border: false,
                style: 'margin-bottom: 20px;',
                width: '100%',
                html: me.createTiles()
            },
            {
                xtype: 'container',
                border: false,
                style: 'font-weight: 700; line-height: 20px;',
                html: '<h1>' + me.snippets.shopware_id.title + '</h1>'
            },
            {
                xtype: 'container',
                border: false,
                width: '100%',
                html: '<p>' + me.snippets.shopware_id.descriptionMessage + '</p>'
            }
        ];

        me.callParent(arguments);
    },

    createTiles: function () {
        var tileData = [
            {
                'link': '{s name="finish/links/help"}{/s}',
                'icon': 'help',
                'text': '{s name="finish/tile/help"}{/s}'
            },
            {
                'link': '{s name="finish/links/templater"}{/s}',
                'icon': 'templater',
                'text': '{s name="finish/tile/templater"}{/s}'
            },
            {
                'link': '{s name="finish/links/developer"}{/s}',
                'icon': 'developer',
                'text': '{s name="finish/tile/developer"}{/s}'
            },
            {
                'link': '{s name="finish/links/forum"}{/s}',
                'icon': 'forum',
                'text': '{s name="finish/tile/forum"}{/s}'
            },
            {
                'link': '{s name="finish/links/account"}{/s}',
                'icon': 'account',
                'text': '{s name="finish/tile/account"}{/s}'
            },
            {
                'link': '{s name="finish/links/store"}{/s}',
                'icon': 'store',
                'text': '{s name="finish/tile/store"}{/s}'
            }
        ],
        tiles = [];

        Ext.each(tileData, function (tile) {
            tiles.push(Ext.String.format(
                '<a class="tile-link" href="[0]" target="_blank"><span class="tile-icon icon-[1]"></span>[2]</a>',
                tile.link,
                tile.icon,
                tile.text
            ));
        });

        return tiles.join('');
    },

    getButtons: function() {
        var me = this;

        return {
            next: {
                text: me.snippets.buttons.finish
            }
        };
    }
});

//{/block}
