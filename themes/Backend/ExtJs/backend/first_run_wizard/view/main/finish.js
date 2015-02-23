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
                width: '100%',
                html: me.createTiles()
            }
        ];

        me.callParent(arguments);
    },

    createTiles: function () {
        var tileData = [
            {
                'link': '{s name="finish/links/help"}http://en.wiki.shopware.com/{/s}',
                'icon': 'help',
                'text': '{s name="finish/tile/help"}Shopware Help{/s}'
            },
            {
                'link': '{s name="finish/links/templater"}http://en.wiki.shopware.com/Designer-s-Guide_cat_884.html{/s}',
                'icon': 'templater',
                'text': '{s name="finish/tile/templater"}Shopware for templaters{/s}'
            },
            {
                'link': '{s name="finish/links/developer"}http://en.wiki.shopware.com/Developer-s-Guide_cat_888.html{/s}',
                'icon': 'developer',
                'text': '{s name="finish/tile/developer"}Shopware for developers{/s}'
            },
            {
                'link': '{s name="finish/links/forum"}http://en.forum.shopware.com/{/s}',
                'icon': 'forum',
                'text': '{s name="finish/tile/forum"}Shopware Forum{/s}'
            },
            {
                'link': '{s name="finish/links/account"}http://account.shopware.com/{/s}',
                'icon': 'account',
                'text': '{s name="finish/tile/account"}Shopware Account{/s}'
            },
            {
                'link': '{s name="finish/links/store"}http://store.shopware.com/en/{/s}',
                'icon': 'store',
                'text': '{s name="finish/tile/store"}Shopware Store{/s}'
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
