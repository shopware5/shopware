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

    initComponent: function() {
        var me = this;

        me.items = [
            {
                xtype: 'container',
                border: false,
                style: 'font-weight: 700; line-height: 20px;',
                html: '<h1>{s name=finish/content/almost_ready}Fast fertig!{/s}</h1>'
            },
            {
                xtype: 'container',
                border: false,
                style: 'margin-bottom: 20px;',
                html: '<p>{s name=finish/content/message}Erstelle Dir jetzt Deinen eigenen <a href=\"https://account.shopware.com/\">Shopware-Account</a>. Diesen benötigst Du, um Dich anschließend auf verschiedenen Shopware-Plattformen einzuloggen. Eine Übersicht der wichtigsten Shopware-Plattformen und deren Vorteile für Dich findest Du hier:{/s}</p>',
                width: '100%'
            },
            {
                xtype: 'container',
                border: false,
                width: '100%',
                html: me.createTiles()
            },
            {
                xtype: 'container',
                border: false,
                html: Ext.String.format(
                    '<p>{s name=finish/shopware_id/text}Lege Dir Deinen Shopware-Account in wenigen Schritten an: <a href=\"[0]\" target=\"_blank\">Jetzt Shopware-Account erstellen</a>{/s}</p>',
                    '{s name="finish/links/account"}https://account.shopware.com/{/s}'
                ),
                width: '100%'
            }
        ];

        me.callParent(arguments);
    },

    createTiles: function () {
        var tileData = [
            {
                'link': '{s name="finish/links/store"}https://store.shopware.com/{/s}',
                'icon': 'store',
                'text': '{s name="finish/tile/store"}Community Store{/s}',
                'description': '{s name="finish/tile/storeDescription"}Hierin findest Du tausende Plugins und Themes, mit denen Du Deinen Onlineshop sinnvoll erweitern kannst. Viele davon sind sogar kostenfrei.{/s}'
            },
            {
                'link': '{s name="finish/links/account"}https://account.shopware.com/{/s}',
                'icon': 'account',
                'text': '{s name="finish/tile/account"}Dein persönlicher Shopware-Account{/s}',
                'description': '{s name="finish/tile/accountDescription"}Dies ist Dein zentraler Dreh- und Angelpunkt für alle Serviceleistungen rund um Shopware und Deinen Shop. So kannst Du hier z.B. direkt von uns als Hersteller Support beantragen, Dein Newsletter-Interessensprofil pflegen oder Deine eingesetzten Erweiterungen aus dem Community Store verwalten.{/s}'
            },
            {
                'link': '{s name="finish/links/forum"}https://forum.shopware.com/?locale=de-DE{/s}',
                'icon': 'forum',
                'text': '{s name="finish/tile/forum"}Shopware-Forum{/s}',
                'description': '{s name="finish/tile/forumDescription"}Wir leben eine offene Community. Deswegen kannst Du Dich in unserem Forum mit unserer weltweiten Community zu Shopware oder zu allgemeinen eCommerce-Themen austauschen. Ein Archiv von tausenden Beiträgen liefert Dir wertvolle Infos und Antworten auf Deine Fragen.{/s}'
            },
            {
                'link': '{s name="finish/links/docs"}https://docs.shopware.com/en/shopware-5-en{/s}',
                'icon': 'help',
                'text': '{s name="finish/tile/docs"}Shopware-Dokumentation{/s}',
                'description': '{s name="finish/tile/docsDescription"}Von der Installation über die Anwendung bis hin zur Entwicklung, Anpassung und Erweiterung von Shopware findest Du in der Doku alles, was Du für Deine tägliche Arbeit mit Shopware benötigst.{/s}'
            }

        ],
        tiles = [];

        Ext.each(tileData, function (tile) {
            tiles.push(Ext.String.format(
                '<a class="tile-link" href="[0]" target="_blank">' +
                    '<span class="tile-icon icon-[1]"></span>' +
                    '<span class="tile-description">' +
                        '<h1>[2]</h1>' +
                        '<p>[3]</p>' +
                    '</span>' +
                '</a>',
                tile.link,
                tile.icon,
                tile.text,
                tile.description
            ));
        });

        return tiles.join('');
    },

    getButtons: function() {
        return {
            next: {
                text: '{s name=finish/buttons/finish}Abschließen{/s}'
            }
        };
    }
});

//{/block}
